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
 * библиотека, для вызова из веб-страниц, подключает DOF.
 */ 

//загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

// Подключаем библиотеку html генератора
$DOF->modlib('widgets')->html_writer();

/**
 * Класс для формирования приказа смены статуса
 */
class dof_im_cstreams_order_status
{
	/**
     * @var dof_control
     */
    protected $dof;
    protected $gradedata;
    //protected $code;
    
    function __construct($dof, $gradedata)
    {
    	$this->dof = $dof;
        $this->gradedata = $gradedata;
        //$this->code = $code;
    }

    /** Сформировать приказ об изменении статуса потока
     * 
     * @return true or false
     */
    public function generate_order_status()
    {
        if ( ! $orderobj = $this->order_change_status() )
        {//ошибка  формировании приказа смены статуса
            return false;
        }
        if ( ! $orderid = $this->save_order_change_status($orderobj) )
        {//ошибка  при сохранении приказа смены статуса
            return false;
        }
        return $this->sign_and_execute_order($orderid);    
    }
    
    /** Формирует приказ - сменить статус
     * 
     * @return mixed object - данные приказа для сохранения
     * или bool false в случае неудачи
     */
    public function order_change_status()
    {
        //создаем объект для записи
        $orderobj = new stdClass();
        $this->dof->storage('persons')->get_bu(NULL,true);
        if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
		{// если id персоны не найден 
			return false;
		}
        //сохраняем автора приказа
        $orderobj->ownerid = $personid;
        //подразделение, к которому он относится
        if ( ! $teacher = $this->dof->storage('persons')->get($orderobj->ownerid) )
        {// пользователя, выставляющего оценку нет в базе данных
            return false;
        }
        // установим id подразделения из сведений об учителе
        $orderobj->departmentid = $teacher->departmentid;
        //дата создания приказа
        $orderobj->date = time();
        //добавляем данные, о которых приказ
        $orderobj->data = $this->get_change_status_fororder();
        return $orderobj;
    }
    
    /** Сохраняет данные приказа
     * @param object $orderobj - данные приказа для сохранения
     * @return mixed int - id приказа
     * или bool true - если приказ не создавался
     */
    public function save_order_change_status($orderobj)
    {
    	//подключаем методы работы с приказом
        $order = $this->dof->im('cstreams')->order('change_status');
        // сохраняем приказ в БД и привязываем экземпляр приказа к id
        $order->save($orderobj);
        // вернем id приказа
        return $order->get_id();
    }
    /** Подписывает и исполняет приказ
     * @param int $orderid - id приказа
     * @return bool true в случае успеха и false в случае неудачи 
     */
    public function sign_and_execute_order($orderid)
    {
    	//подключаем методы работы с приказом
    	if ( ! $order = $this->dof->im('cstreams')->order('change_status',$orderid) )
    	{// приказа нет - это ошибка
    		return false;
    	}    	
        // подписываем приказ
        if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
		{// если id персоны не найден 
			return false;
		}
        $order->sign($personid );
        //проверяем подписан ли приказ
        if ( ! $order->is_signed() )
        {//приказ не подписан
            return false;
        }
        //исполняем приказ';
        if ( ! $order->execute() )
        {//не удалось исполнить приказ
            return false;
        }
        return true;
    }
    /** Проверяет подписан ли приказ
     * @param int $orderid - id приказа
     * @return bool true если уже подписан и false если нет
     */
    public function is_signed($orderid)
    {
    	//подключаем методы работы с приказом
    	if ( ! $order = $this->dof->im('cstreams')->order('change_status',$orderid) )
    	{// приказа уже нет - будем считать что все нормально
    		return true;
    	}
        //проверяем подписан ли приказ
        if ( ! $order->is_signed() )
        {//приказ не подписан
            return false;
        }
        return true;
    }

    /** Формирует массив данных для приказа
     * @param $type  
     * @return unknown_type
     */
    private function get_change_status_fororder()
    {
    	//print_object($this->gradedata);//die;
    	//Структура приказа:
        $order = new stdClass();
		//поля сохранения смены статуса
		$order->cstreamid = $this->gradedata->id;//id периода
		$order->datechange = time();//дата смены статуса
		$order->oldstatus = $this->gradedata->oldstatus;//старый статус
		$order->newstatus = $this->gradedata->status;//новый статус

        //print_object($order);//die;
        return $order;
		
    }
    
}



/**
 * Класс генерации ведомости группы
 * для экспорта в odf
 */
class dof_im_cstreams_students_grades_odf
{
    /**
     * Хранит запись о группе
     * @var object или null
     */
    private $agroup;
    
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Хранит данные о потоке
     * из соответствующей таблицы
     * @var object или null
     */
    private $cstream;
    /**
     * инициализируем объект
     * @param dof_control $dof - методы ядра деканата
     * @param int $cstreamid - id потока, ведомость по которому надо выводить
     * @param int $agroupif - id группы, ведомость по которой надо выводить
     * @return void
     */
    public function __construct($dof, $cstreamid, $agroupid = null )
    {
        $this->dof = $dof;
        if ( ! $this->cstream = $this->dof->storage('cstreams')->get($cstreamid) )
        {//не получили поток';
            $this->cstream = null;
        }
        if ( is_null($agroupid) )
        {//группа не передана - надо вывести всех';
            $this->agroup = null;
        }else
        {//получаем объект группы';
            $this->agroup = $this->dof->storage('agroups')->get($agroupid);
        }
    }

    /**
     * Функция возвращает объект данных 
     * для вставки в шаблон экспорта 
     * @return mixed object - объект для вставки или bool false
     */
    public function get_data()
    {
        if ( is_null($this->cstream) )
        {//поток не получен';
            return false;
        }
        $report = new stdClass();
        $report->nnum = $this->dof->modlib('ig')->igs('number');
        $report->nfio = $this->dof->modlib('ig')->igs('fio');
        $report->teacher = $this->get_teacher();
        $report->programmitem = $this->get_programmitem();
        if ($this->agroup === false )
        {//надо получить данные по группе, но ее не получили';
            $report->agroup = '';
            $report->table = [];
            return $report;
        }
        $report->agroup = $this->get_agroup_name();
        if ( ! $cpassed = $this->get_cpassed() )
        {//не получили подписок';
            $report->table = [];
            return $report;
        }
        $report->table = $this->get_table($cpassed);  
        
        return $report;
    }
    
    /**
     * получим ФИО преподавателя
     * или пустую строку
     * @return string
     */
    private function get_teacher()
    {
        if ( $teacher = $this->dof->storage('persons')->get_fullname($this->cstream->teacherid) )
        {
            return $this->dof->get_string('teacher','cstreams').': '.$teacher;
        }
        return '';
    }
    
    /**
     * Возвращает название предмета
     * @return string
     */
    private function get_programmitem()
    {
        if ( ! $programmitem = $this->dof->storage('programmitems')->
                                    get($this->cstream->programmitemid) )
        {//не получили запись из таблицы
            return '';
        }
        return $programmitem->name;
    }

    /**
     * Возвращает название группы или 
     * фразу которая должна быть вместо нее,
     * если надо вывести студентов потока.
     * @return string
     */
    private function get_agroup_name()
    {
        if ( $this->agroup )
        {
            return $this->agroup->name;
        }
        if ( is_null($this->agroup) )
        {
            return $this->dof->get_string('student_cstream','cstreams');
        }
        return 'группа не найдена'; 
    }
    
    /**
     * Возвращает массив объектов таблицы из ведомости 
     * группы или потока для для templater
     * @param array $cpassed - массив объектов таблицы cpassed
     * @return array массив объектов 
     */
    private function get_table($cpassed, $flag =  false)
    {
        $rez = array();
        $i=1;
        foreach ( $cpassed as $one )
        {
            $line = new stdClass();
            $line->num   = $i;
            $line->fio = $this->dof->storage('persons')->get_fullname($one->studentid);
            if ( $flag == 'group' )
            {
                $line->email = $this->dof->storage('persons')->get_field($one->studentid,'email');
                $line->phone = $this->dof->storage('persons')->get_field($one->studentid,'phonehome');
                $cell = $this->dof->storage('persons')->get_field($one->studentid,'phonecell');
                // нет телефона - не будем показывать скобки
                if ( $line->phone AND $cell )
                {
                    $line->phone .= '('.$cell.')';
                }elseif( ! $line->phone AND $cell )
                {
                    $line->phone .= $cell;
                }elseif( ! $line->phone AND ! $cell)
                {
                    $line->phone = $this->dof->get_string('no_data','cstreams'); 
                } 
            }                                
            
            $rez[] = $line;
            $i++;
        }
        return $rez; 
    }
    
    /**
     * Возвращает подписки студентов 
     * группы или потока на указанный поток
     * @return mixed array - массив объектов из таблицы cpassed
     * или bool false 
     */
    private function get_cpassed()
    {
        $param = new stdClass;
        $param->cstreamid = $this->cstream->id;
        $param->status = array_keys($this->dof->workflow('cpassed')->get_meta_list('actual'));
        if ( is_null($this->agroup) )
        {//надо получить все подписки потока
            return $this->dof->storage('cpassed')->get_listing($param); 
        }
        //надо получить подписки группы
        $param->agroupid = $this->agroup->id;
        return $this->dof->storage('cpassed')->get_cstream_listing($param);
    }
    
    /** Получить список учеников для группы, без указания потока
     * 
     * @return 
     */
    public function get_agroup_data()
    {
        if ($this->agroup === false )
        {//надо получить данные по группе, но ее не получили';
            return false;
        }
        if ( ! $cpassed = $this->get_agroup_cpassed() )
        {//не получили подписок';
            return false;
        }
        //заголовки для шаблона
        $report = new stdClass();
        $report->nnum = $this->dof->modlib('ig')->igs('number');
        $report->nfio = $this->dof->modlib('ig')->igs('fio');
        $report->nemail = $this->dof->modlib('ig')->igs('email');
        $report->nphone = $this->dof->get_string('nphone','cstreams');
        $report->grouplist = $this->dof->get_string('group_list', 'cstreams');
        // оставляем пустое место в шаблоне, чтобы вручную вписать программу
        $report->programmitem = '___________________________________________________';
        $report->agroup = $this->get_agroup_name();
        $report->table  = $this->get_table($cpassed,'group');
        return $report;
    }
    
    /** Получить подписки учеников группы, если неизвестен поток
     * @todo разобраться с механизмом получения списка группы. По ка что мы просто эмулируем
     * извлечение записей из таблицы cpassed
     * @return 
     */
    private function get_agroup_cpassed()
    {
        $cpassed = array();
        // поскольку ученик не может быть 2 раза записан в одну и ту же группу - 
        // то извлечем все подписки, приписанные к определенной группе
        $records = $this->dof->storage('programmsbcs')->get_records(array('agroupid'=>$this->agroup->id));
        if ( ! $records )
        {
            return $cpassed;
        }
        foreach ( $records as $record )
        {// для каждой подписки составим объект, подобный записи из cpassed
            if ( ! $contract = $this->dof->storage('contracts')->get($record->contractid) )
            {
                continue;
            }
            $elm = new stdClass();
            $elm->studentid = $contract->studentid;
            $elm->grade     = '';
            $cpassed[] = $elm;
        }
        return $cpassed;
    }
    
}



?>