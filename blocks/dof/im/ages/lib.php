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

// Добавление таблицы стилей плагина
$DOF->modlib('nvg')->add_css('im', 'ages', '/styles.css');

// Инициализация рендера HTML
$DOF->modlib('widgets')->html_writer();

/** Класс для вывода информации о периодах
 */
class dof_im_ages_display
{
    protected $dof;
    private $data;
    private $addvars;
    private $departmentid; // подразделение
    
    function __construct($dof, $addvars)
    {
    	$this->dof = $dof;
        $this->addvars = $addvars;
        $this->departmentid = $addvars['departmentid'];
    }
    
   	/** Возвращает код im'а, в котором хранятся отслеживаемые объекты
     * @return string
     * @access private
	 */
	private function get_im()
	{
		return 'ages';
	}

    /** Распечатать вертикальную таблицу для удобного отображения информации по элементу
     * @todo не использует print_table, т.к выводит вертикальную таблицу
     * @param int $id - id периода из таблицы ages
     * @return string
     */
    public function get_table_one($id)
    {
        $table = new stdClass();
        if ( ! $age = $this->dof->storage('ages')->get($id))
        {// не нашли шаблон - плохо
            return '';
        }
        // получаем заголовки таблицы
        $descriptions = $this->get_fields_description('view');
        $data = $this->get_string_full($age);
        foreach ( $data as $elm )
        {
            $table->data[] = array('<b>'.current(each($descriptions)).'</b>', $elm);
        }
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
    
    /** Получает строку таблицы для отображения списка периодов
     * @param object $$obj - объект периода из таблицы ages
     * @return array
     */
    private function get_string_full($obj)
    {
        $add = array();
        $add['departmentid'] = $this->departmentid;
        $outadd = array();
        $outadd['departmentid'] = $this->departmentid;
        $string = array();
        $string[] = $obj->name;
   		$string[] = dof_userdate($obj->begindate,"%d-%m-%Y");
   		$string[] = dof_userdate($obj->enddate,"%d-%m-%Y");
   		$string[] = $obj->eduweeks;
        $string[] = $this->dof->storage('departments')->get_field($obj->departmentid,'name').' <br>['.
                    $this->dof->storage('departments')->get_field($obj->departmentid,'code').']';
   		if ( ! $previous = $this->dof->storage('ages')->get_field($obj->previousid,'name') )
   		{//если названия периода нет - выведем пустую строчку
   		    $previous = '';
   		}
   		$string[] = $previous;
        // добавляем ссылку
        $link = '';
        if ( $this->dof->storage('ages')->is_access('edit', $obj->id) )
        {//покажем ссылку на страницу редактирования
            $link .= $this->dof->modlib('ig')->icon('edit',
                    $this->dof->url_im($this->get_im(),'/edit.php?ageid='.$obj->id,$add));
        }
        if ( $this->dof->storage('ages')->is_access('view', $obj->id) )
        {//покажем ссылку на страницу просмотра
            $link .= $this->dof->modlib('ig')->icon('view',
                    $this->dof->url_im($this->get_im(),'/view.php?ageid='.$obj->id,$add));
        }
        if ( $this->dof->im('plans')->is_access('viewthemeplan',$obj->id,null,'ages') )
        {// если есть право на просмотр планирования
            $link .= $this->dof->modlib('ig')->icon_plugin('plan','im',$this->get_im(),
                    $this->dof->url_im('plans','/themeplan/viewthemeplan.php?linktype=ages&linkid='.$obj->id,$outadd),
                    array('title'=>$this->dof->get_string('view_plancstream', $this->get_im())));
        }
        if ( $this->dof->storage('schdays')->is_access('view') )
        {// если есть право на просмотр планирования
            $link .= $this->dof->modlib('ig')->icon_plugin('calendar','im',$this->get_im(),
                    $this->dof->url_im('schdays','/calendar.php?ageid='.$obj->id,$outadd),
                    array('title'=>$this->dof->get_string('view_calendar', $this->get_im()),
                          'width'=>'16px'));
        }
        $string[] = $obj->schdays;
        $string[] = $obj->schedudays;
        $string[] = $obj->schstartdaynum;
	    $string[] = $this->dof->workflow('ages')->get_name($obj->status);
	    array_unshift($string, $link);
        return $string;
    }
    
    /** Распечатать таблицу для отображения периодов
     * @param int $conds - параметры поиска
     * @param int $limitfrom  - $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @return string
     */
    public function get_table_all($conds,$limitfrom,$limitnum)
    {
        $sort = array();
        $sort[$this->addvars['sort']] = $this->addvars['sort'];
        $sort['dir'] = $this->addvars['dir'];
        if ( !$ages = $this->dof->storage('ages')->get_listing($conds,$limitfrom,$limitnum,$sort) )
        {// не нашли скажем об этом
            return array('<div align="center">(<i>'.$this->dof->get_string('no_ages_found', 'ages').'</i>)</div>',0);
        }
        // формируем данные
        $this->data = array();
        foreach ( $ages as $age )
        {//для каждого периода формируем строку
            $this->data[] = $this->get_string_all($age);
        }
        return array($this->print_table(),count($ages));
    }
    
    /** Получает строку таблицы для отображения списка периодов
     * @param object $$obj - объект периода из таблицы ages
     * @return array
     */
    private function get_string_all($obj)
    {
        $add = array();
        $add['departmentid'] = $this->departmentid;
        $outadd = array();
        $outadd['departmentid'] = $this->departmentid;
        $timezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        $string = array();
        $string[] = $obj->name;
   		$string[] = dof_userdate($obj->begindate,"%d-%m-%Y", $timezone);
   		$string[] = dof_userdate($obj->enddate,"%d-%m-%Y", $timezone);
   		$string[] = $obj->eduweeks;
        $string[] = $this->dof->storage('departments')->get_field($obj->departmentid,'name').' <br>['.
                    $this->dof->storage('departments')->get_field($obj->departmentid,'code').']';
   		if ( ! $previous = $this->dof->storage('ages')->get_field($obj->previousid,'name') )
   		{//если названия периода нет - выведем пустую строчку
   		    $previous = '';
   		}
   		$string[] = $previous;
        // добавляем ссылку
        $link = '';
        if ( $this->dof->storage('ages')->is_access('edit', $obj->id) )
        {//покажем ссылку на страницу редактирования
            $link .= $this->dof->modlib('ig')->icon('edit',
                    $this->dof->url_im($this->get_im(),'/edit.php?ageid='.$obj->id,$add));
        }
        if ( $this->dof->storage('ages')->is_access('view', $obj->id) )
        {//покажем ссылку на страницу просмотра
            $link .= $this->dof->modlib('ig')->icon('view',
                    $this->dof->url_im($this->get_im(),'/view.php?ageid='.$obj->id,$add));
        }
        if ( $this->dof->im('plans')->is_access('viewthemeplan',$obj->id,null,'ages') )
        {// если есть право на просмотр планирования
            $link .= $this->dof->modlib('ig')->icon_plugin('plan','im',$this->get_im(),
                    $this->dof->url_im('plans','/themeplan/viewthemeplan.php?linktype=ages&linkid='.$obj->id,$outadd),
                    array('title'=>$this->dof->get_string('view_plancstream', $this->get_im())));
        }
        if ( $this->dof->storage('schdays')->is_access('view') )
        {// если есть право на просмотр планирования
            $link .= $this->dof->modlib('ig')->icon_plugin('calendar','im',$this->get_im(),
                    $this->dof->url_im('schdays','/calendar.php?ageid='.$obj->id,$outadd),
                    array('title'=>$this->dof->get_string('view_calendar', $this->get_im()),
                          'width'=>'16px'));
        }
	    $string[] = $this->dof->workflow('ages')->get_name($obj->status);
	    array_unshift($string, $link);
        return $string;
    }
    
    /** Возвращает html-код таблицы
     * @param string $type - тип таблицы
     * @return string - html-код или пустая строка
     */
    private function print_table($type='all')
    {
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->size = array ('100px','150px','150px','200px','150px','100px');
        $table->align = array ("center","center","center","center","center","center","center","center");
        // шапка таблицы
        $table->head =  $this->get_fields_description($type);
        // заносим данные в таблицу     
        $table->data = $this->data;
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /** Получить заголовоки таблицы
     * @param string $type - тип таблицы
     * @return array
     */
    private function get_fields_description($type)
    {
        $head = array();
        switch ( $type )
		{
		    // для одного периода
		    case 'view':
		    // полная версия
			case 'full':
                $head[] = $this->dof->modlib('ig')->igs('actions');
                $head[] = $this->dof->get_string('name',           $this->get_im());
                $head[] = $this->dof->get_string('begindate',      $this->get_im());
                $head[] = $this->dof->get_string('enddate',        $this->get_im());
                $head[] = $this->dof->get_string('eduweeks',       $this->get_im());
                $head[] = $this->dof->get_string('department',     $this->get_im());
                $head[] = $this->dof->get_string('previousage',    $this->get_im());
                $head[] = $this->dof->get_string('schdays',        $this->get_im());
                $head[] = $this->dof->get_string('schedudays',     $this->get_im());
                $head[] = $this->dof->get_string('schstartdaynum', $this->get_im());
                $head[] = $this->dof->modlib('ig')->igs('status');
            break;
            // для списка периодов 
		    case 'all':
		    	$head[] = $this->dof->modlib('ig')->igs('actions');
                list($url,$icon) = $this->get_link_sort('name');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('name', $this->get_im()).'</a>'.$icon;
                list($url,$icon) = $this->get_link_sort('begindate');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('begindate', $this->get_im()).'</a>'.$icon;
                list($url,$icon) = $this->get_link_sort('enddate');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('enddate', $this->get_im()).'</a>'.$icon;
                list($url,$icon) = $this->get_link_sort('eduweeks');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('eduweeks', $this->get_im()).'</a>'.$icon;
                $head[] = $this->dof->get_string('department',  $this->get_im());
                $head[] = $this->dof->get_string('previousage', $this->get_im());
                list($url,$icon) = $this->get_link_sort('status');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->modlib('ig')->igs('status').'</a>'.$icon;
            break;
		}
		return $head;

    }
    
    /** Получить ссылку с иконкой сортировки
     * @param sting type - имя поля для которого добавляется иконка сортировки
     * @return array
     */
    private function get_link_sort($type)
    {   
        $add = $this->addvars;
        list($dir,$icon) = $this->dof->modlib('ig')->get_icon_sort($type,$add['sort'],$add['dir']);
        unset($add['sort']);
        unset($add['dir']);
        return array($this->dof->url_im('ages','/list.php?sort='.$type.'&dir='.$dir,$add),$icon);
    }
    
}

/** Класс для формирования приказа смены статуса
 */
class dof_im_ages_order_status
{
	/** @var dof_control
     */
    protected $dof;
    protected $gradedata;
    
    function __construct($dof, $gradedata)
    {
    	$this->dof = $dof;
        $this->gradedata = $gradedata;
    }

    /** Сформировать приказ об изменении статуса периода
     * @return bool
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
        $order = $this->dof->im('ages')->order('change_status');
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
    	if ( ! $order = $this->dof->im('ages')->order('change_status',$orderid) )
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
    	if ( ! $order = $this->dof->im('ages')->order('change_status',$orderid) )
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
     * @return object
     */
    private function get_change_status_fororder()
    {
    	//print_object($this->gradedata);//die;
    	//Структура приказа:
        $order = new stdClass();
		//поля сохранения смены статуса
		$order->ageid = $this->gradedata->id;//id периода
		$order->datechange = time();//дата смены статуса
		$order->oldstatus = $this->gradedata->oldstatus;//старый статус
		$order->newstatus = $this->gradedata->status;//новый статус

        //print_object($order);//die;
        return $order;
		
    }
    
}

?>