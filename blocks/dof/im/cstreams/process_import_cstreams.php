<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
// Copyright (C) 2008-2999  Alex Djachenko (Алексей Дьяченко)             //
// alex-pub@my-site.ru                                                    //
// Copyright (C) 2008-2999  Evgenij Cigancov (Евгений Цыганцов)           //
// Copyright (C) 2008-2999  Ilia Smirnov (Илья Смирнов)                   // 
// Copyright (C) 2008-2999  Mariya Rojayskaya (Мария Рожайская)           // 
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Класс для импорта предмето-потоков
 */

class dof_im_cstreams_import_process
{
    /**
     * @var dof_control
     */
    protected $dof;
    private $ageid;
    
    /** Конструктор
     * @param dof_control $dof - идентификатор действия, которое должно быть совершено
     * @access public
     */
    public function __construct($dof, $ageid)
    {
        $this->dof = $dof;
        $this->ageid = $ageid;
    }
    
    /** Импорт файла потоков
     * @param string $filename - имя файла
     * @param bool $write - создавать ли потоки
     * true - создать потоки
     * false - только проверить данные
     * @return array массив с ошибками или в случае успеха проверки - пустой массив,
     * при создании потоков массив с id созданными потоками
     */
    public function import_cstreams($filename, $write=false)
    {
		$file = fopen($filename, "r");//открыли файл для чтения
		// устанавливаем переменные
        $rezfile = array();
        $rezfile[] = array_merge(array('№ '.$this->dof->modlib('ig')->igs('strings'),
                     $this->dof->modlib('ig')->igs('errors'),'cstreamid'),fgetcsv($file, 4096, ";"));
        $i = 1;
        while (($row = fgetcsv($file, 4096, ";")) !== FALSE)
        {// читаем файл по строкам - экономим память
            $i++;
            $rez = array();
            $rez[0] = $i;
            // получаем строку для импорта
            $cstream = $this->get_string_for_import($row);
            // обрабатываем строку
            $cstreamid = $this->import_one_cstream($cstream,$write);
            if ( ! $cstreamid )
            {// если вернулось пусто - переходим к следующему элементу
                continue;
            }
            // сливаем полученные результаты
            $rez = array_merge(($rez + $cstreamid),$row);
            // добавляем в общий массив
            $rezfile[] = $rez;
        }
        return $rezfile;
    }
    
    /** Импорт одной строки файла
     * @param object $cstream - строка файла обращенная в объект
     * @param bool $write - создавать ли потоки
     * true - создать потоки
     * false - только проверить данные
     * @return array массив с ошибками или в случае успеха проверки - пустой массив,
     * при создании потоков массив с id созданного потока
     */
    private function import_one_cstream($cstream, $write=false)
    {
        $errors = array();
        //print_object($cstream);
        $cstream->ageid = $this->ageid;
        // делаем роверку данных
        if ( ! $this->dof->storage('programmitems')->get_records(array('id'=>$cstream->programmitemid,
                                        'status'=>array('active','suspend'))) )
        {// если предмет удален или не существует - это ошибка
            $errors[] = $this->dof->get_string('error_programmitem','cstreams',$cstream->programmitemid);
        }
        if ( isset($cstream->appointmentid) AND $cstream->appointmentid != '' )
        {// если указано назначение на должность
            if ( ! $this->dof->storage('appointments')->get_records
                       (array('id'=>$cstream->appointmentid,'status'=>array('plan','active'))) )
            {// если назначение на должность удалено или не существует - это ошибка
                $errors[] = $this->dof->get_string('error_appointment','cstreams',$cstream->appointmentid);
            }
            if ( ! $this->dof->storage('teachers')->get_records(array('appointmentid'=>$cstream->appointmentid,
                      'programmitemid'=>$cstream->programmitemid,'status'=>array('plan','active'))) )
            {// если учитель не преподает данный предмет - это ошибка
                $errors[] = $this->dof->get_string('error_teacher','cstreams',$cstream);
            }
            $person = $this->dof->storage('appointments')->get_person_by_appointment($cstream->appointmentid);
            $cstream->teacherid = $person->id;
        }else
        {// преподавать пока некому
            $cstream->appointmentid = 0;
            $cstream->teacherid = 0;
        }
        if ( isset($cstream->departmentid) AND $cstream->departmentid != '' )
        {// если указано подразделение
            if ( ! $this->dof->storage('departments')->is_exists($cstream->departmentid) )
            {// если подразделения не существует - это ошибка
                $errors[] = $this->dof->get_string('error_department','cstreams',$cstream->departmentid);
            }
        }else
        {// подразделение установится автоматически
            $cstream->departmentid = 0;
        }
        // обработаем подписки на программу
        $sbcs = explode(',',$cstream->programmsbcs);
        foreach ( $sbcs as $sbcid )
        {
            $sbcid = trim($sbcid);
            if ( ! $programmsbcs = $this->dof->storage('programmsbcs')->get_records(array('id'=>$sbcid,
                                 'status'=>array('application','plan','active','suspend'))) )
            {// подписка не актуальна или не существует - ошибка
                $errors[] = $this->dof->get_string('error_programmsbc','cstreams',$sbcid);
                continue;
            }
            foreach ( $programmsbcs as $programmsbc )
            {
                $a = new stdClass();
                $a->itemid =  $cstream->programmitemid;
                $a->sbcid = $programmsbc->id;
                if ( $programmsbc->programmid != $this->dof->storage('programmitems')->
                                         get_field($cstream->programmitemid,'programmid') )
                {// програма подписки не совпадает с программой предмета
                    $errors[] = $this->dof->get_string('error_programm_sbc','cstreams',$a);
                }elseif ( $cstream->programmitemid AND $programmsbc->agenum AND
                          $this->dof->storage('programmitems')->
                          get_field($cstream->programmitemid,'agenum') != 0 AND
                          $programmsbc->agenum != $this->dof->storage('programmitems')->
                                         get_field($cstream->programmitemid,'agenum') )
                {//параллель подписки не совпадает с паралелью предмета
                    $errors[] = $this->dof->get_string('error_agenum_sbc','cstreams',$a);
                }
            }
        }
        if ( isset($cstream->eduweeks) AND ! $cstream->eduweeks )
        {//если количество недель сказано брать из периода
            $cstream->eduweeks = $this->dof->storage('ages')->get_field($this->ageid,'eduweeks');
            if ( $number = $this->dof->storage('programmitems')->get_field($cstream->programmitemid,'eduweeks') )
            {// или из предмета, если указано там
                $cstream->eduweeks = $number;
            } 
        }
        if ( isset($cstream->hours) AND ! $cstream->hours )
        {//если количество часов всего не указано - берем из предмета
            $cstream->hours = $this->dof->storage('programmitems')->get_field($cstream->programmitemid,'hours');  
        }
        if ( isset($cstream->hoursweek) AND ! $cstream->hoursweek )
        {//если количество часов в неделю не указано - берем из предмета
            $cstream->hoursweek = $this->dof->storage('programmitems')->get_field($cstream->programmitemid,'hoursweek');  
        }
        $cstream->begindate  = $this->dof->storage('ages')->get_field($this->ageid,'begindate');
        $cstream->enddate    = $this->dof->storage('ages')->get_field($this->ageid,'enddate');
        //собираем массив для возврата
        $rez = array();
        if ( ! empty($errors) )
        {// если были ошибки - запомним их
            $rez[1] = implode('<br>',$errors);
            $rez[2] = '';
        }elseif ( $write )
        {// если импорт запущен на создание - создаем потоки
            $rez[1] = 'ok';
            $rez[2] = $this->dof->storage('cstreams')->insert($cstream);
            foreach ( $sbcs as $sbcid )
            {// создадим подписки на предметы
                $sbcid = trim($sbcid);
                $this->dof->storage('cpassed')->sign_student_on_cstream($rez[2],$sbcid);
            }
        }
        // вернем результат
        return $rez;
    }
    
    
    /** Обращает строку файла в объект
     * @param array $str - строка файла
     * @return object
     */
    private function get_string_for_import($str)
    {
        //массив необходимых элементов - значения - номер элемента в исходной строке импорта
	    $fields = array();
	    $fields['programmitemid'] = 0;//id предмета
	    $fields['appointmentid'] = 1;//id назначения на должность учителя
	    $fields['departmentid'] = 2;//id подразделения
	    $fields['eduweeks'] = 3;//кол-во учебных недель
	    $fields['hours'] = 4;//часов всего на год
	    $fields['hoursweek'] = 5;//часов в неделю
	    $fields['hoursweekdistance'] = 6;//из них дистанционно
	    $fields['programmsbcs'] = 7;//id подписок на программу через запятую
	    $rez = new stdClass();//будет хранить нужные нам элементы
	    foreach ( $fields as $k => $v )
	    {//выбираем из исходной строки нужные нам элементы
	        if ( isset($str[$v]) )
	        {//нужный элемент есть - сохраняем его
	            $rez->$k = trim($str[$v]);
	        }else
	        {//нужного элемента нет - создаем его
	            $rez->$k = '';
	        }
	    }
	    return $rez;
    }
    
    
    /** Распечатывает ошибки на экран
     * @param array $object - массив с ошибками
     * @return unknown_type
     */
    public function print_error_check($object)
    {
        // устанавливаем заголовок
        print '<h4 align=\'center\' style=" color:red; ">'.
              $this->dof->get_string('error_import_file','cstreams').'</h4>';
        foreach ( $object as $error )
        {// распечатываем ошибки для каждой строчки
            print '<br>';
            print $this->dof->modlib('ig')->igs('string').' '.$error[0].':';
            print '<div style=" color:red; ">'.$error[1].'</div>';
            
        }
    }
    
    /** Посылает csv файл пользователю
     * @param array $object - массив с id созданными потоками
     */
    public function get_file_csv($object)
    {
        $result = array();
        foreach ( $object as $data )
        {// формируем строчку
            $result[] = implode(';',$data)."\n";
        }
        // посылаем заголовки
		header('Content-Type:text/csv; charset=utf8');
        header('Content-Disposition: attachment; filename="potoki.csv"');
        // посылаем файл
    	print implode('',$result);
    }
}
?>