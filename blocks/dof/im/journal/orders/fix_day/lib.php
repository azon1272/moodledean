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

//загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

class dof_im_orders_fix_day_display
{
    /**
     * @var dof_control
     */
    protected $dof;
    private $data; // данные для построения таблицы отчета
    private $departmentid; // подразделение
    private $addvars; // набор параметров, которые мы приплюсовываем к сылкам
    
    /** Конструктор
     * @param dof_control $dof - объект с методами ядра деканата
     * @param int $departmentid - id подразделения в таблице departments
     * @param array $addvars - массив get-параметров для ссылки
     * @access public
     */
    public function __construct($dof,$departmentid,$addvars)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
        $this->departmentid = $departmentid;
        $this->addvars      = $addvars;
    }
    
   	/** Возвращает код im'а, в котором хранятся отслеживаемые объекты
     * @return string
     * @access private
	 */
	private function get_im()
	{
		return 'journal';
	}
	
    /**
     * Возвращает объект отчета
     *
     * @param string $code
     * @param integer  $id
     * @return dof_storage_orders_baseorder
     */
    public function order($id = NULL)
    {
        return $this->dof->storage('reports')->order('im', 'journal', 'fix_day', $id);
    }
    
    /** Распечатать таблицу для отображения списка отчетов
     * @param string $list - список отчетов из таблицы reports
     * @return string
     */
    public function get_table_list($list)
    {
        if ( ! $list )
        {// не нашли шаблон - плохо
            return '';
        }
        // формируем данные
        $this->data = array();
        foreach ( $list as $order )
        {//для каждого шаблона формируем строку
            $this->data[] = $this->get_string_list($order);         
        }
        return $this->print_table('list');
    }
    
    /** Получает строку для отображения отчета
     * @param int $obj - объект шаблона из таблицы reports
     * @return array
     */
    private function get_string_list($obj)
    {
        $add = $this->addvars;
        // убираем сортировку
        unset($add['sort']);
        unset($add['dir']);
        $add['departmentid'] = $this->departmentid;  
        $string   = array();
        // дата регистрации отчета
        if ( empty($obj->date) )
        {
            $string[] = $this->dof->modlib('ig')->igs('no_specify_jr');;
        }else
        {
            $string[] = dof_userdate($obj->date,'%d.%m.%y %H-%M');
        }
        $string[] = $this->dof->storage('persons')->get_fullname($obj->ownerid);
        if ( empty($obj->signdate) )
        {
            $string[] = $this->dof->modlib('ig')->igs('no_specify_jr');;
        }else
        {
            $string[] = dof_userdate($obj->signdate,'%d.%m.%y %H-%M');
        }
        $string[] = $this->dof->storage('persons')->get_fullname($obj->signerid);
        if ( empty($obj->exdate) )
        {
            $string[] = $this->dof->modlib('ig')->igs('no_specify_jr');;
        }else
        {
            $string[] = dof_userdate($obj->exdate,'%d.%m.%y %H-%M');
        }
        // дата не ранее которой должен собраться отчет
        if ( empty($obj->crondate) )
        {
            $string[] = $this->dof->modlib('ig')->igs('no_specify_jr');;
        }else
        {
            $string[] = dof_userdate($obj->crondate,'%d.%m.%y %H-%M');
        }
        $link = ''; 
        if ( $this->dof->workflow('orders')->is_access('changestatus',$obj->id) AND is_null($obj->exdate)
             AND $obj->ownerid == $this->dof->storage('persons')->get_by_moodleid_id() )
        {//покажем ссылку на страницу просмотра
            $link .= $this->dof->modlib('ig')->icon('delete',
                    $this->dof->url_im($this->get_im(),'/orders/fix_day/delete.php?id='.$obj->id,$add));
        }
        array_unshift($string, $link);
        return $string;
    }
    
    /** Возвращает html-код таблицы
     * @param string $type - тип отображения данных
     *                           list - список отчетов
     *                           
     * @return string - html-код или пустая строка
     */
    protected function print_table($type)
    {
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->width = '100%';
        switch ( $type )
		{
            case 'list': // список
                //$table->size = array ('50px','150px','150px','200px','150px','100px');
                $table->wrap = array (true);
                $table->align = array("center","center","center","center","center",
                                      "center","center");
            break; 
		}
        
        // шапка таблицы
        $table->head = $this->get_header($type);
        // заносим данные в таблицу     
        $table->data = $this->data;
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /** Получить заголовок для списка таблицы, или список полей
     * для списка отображения одного объекта 
     * @param string $type - тип отображения данных
     *                           list - список отчетов
     * @return array
     */
    private function get_header($type)
    {
        $head = array();
        switch ( $type )
		{
		    // просмотр списка
            case 'list':
                $head[] = $this->dof->modlib('ig')->igs('actions');
                list($url,$icon) = $this->get_link_sort('date');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('order_date', $this->get_im()).'</a>'.$icon;
                list($url,$icon) = $this->get_link_sort('ownersortname');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('order_owner', $this->get_im()).'</a>'.$icon;
                list($url,$icon) = $this->get_link_sort('signdate');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('order_signdate', $this->get_im()).'</a>'.$icon;
                list($url,$icon) = $this->get_link_sort('signsortname');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('order_signer', $this->get_im()).'</a>'.$icon;
                list($url,$icon) = $this->get_link_sort('exdate');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('order_exdate', $this->get_im()).'</a>'.$icon;
                list($url,$icon) = $this->get_link_sort('crondate');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('order_crondate', $this->get_im()).'</a>'.$icon;
            break; 
            
		}
		return $head;
    }
    
    private function get_link_sort($type)
    {   
        $add = $this->addvars;
        list($dir,$icon) = $this->dof->modlib('ig')->get_icon_sort($type,$add['sort'],$add['dir']);
        unset($add['sort']);
        unset($add['dir']);
        return array($this->dof->url_im('journal','/orders/fix_day/list.php?sort='.$type.'&dir='.$dir,$add),$icon);
    }
    
}

?>