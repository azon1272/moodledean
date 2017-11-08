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

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

// Добавление общих GET параметров плагина
$addvars['plugintype'] = optional_param('plugintype', '', PARAM_TEXT);
$addvars['plugincode'] = optional_param('plugincode', '', PARAM_TEXT);
$addvars['code']       = optional_param('code', '', PARAM_TEXT);

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('title', 'reports'), 
    $DOF->url_im('reports')
);

if ($addvars['plugintype'] != '' AND $addvars['plugincode'] != '' AND $addvars['plugincode'] != '')
{
    $title = $DOF->get_string($addvars['plugintype']."_".$addvars['plugincode']."_".$addvars['code'], 'reports');
    $url = $DOF->url_im('reports', '/list.php', array('plugintype' => $addvars['plugintype'],
            'plugincode' => $addvars['plugincode'],
            'code' => $addvars['code']));
    
    $DOF->modlib('nvg')->add_level($title, $url);
}

class dof_im_reports_display
{
    /**
     * @var dof_control
     */
    protected $dof;
    private $data; // данные для построения таблицы отчета
    private $departmentid; // подразделение
    private $addvars; // набор параметров, которые мы приплюсовываем к сылкам
    
    /** 
     * Конструктор
     * 
     * @param dof_control $dof - объект с методами ядра деканата
     * @param int $departmentid - id подразделения в таблице departments
     * @param array $addvars - массив get-параметров для ссылки
     * @access public
     */
    public function __construct($dof,$departmentid,$addvars)
    {
        // Сохраняем ссылку на DOF, чтобы вызывать его через $this->dof
        $this->dof          = $dof;
        $this->departmentid = $departmentid;
        $this->addvars      = $addvars;
    }
    
    /** 
     * Возвращает код im'а, в котором хранятся отслеживаемые объекты
     * 
     * @return string
     * @access private
     */
    private function get_im()
    {
        return 'reports';
    }
	
    /**
     * Возвращает объект отчета
     *
     * @param string $code
     * @param integer  $id
     * @return dof_storage_orders_baseorder
     */
    public function report($plugintype,$plugincode,$code,$id = NULL)
    {
        return $this->dof->storage('reports')->report($this->addvars['plugintype'], 
                                    $this->addvars['plugincode'], $this->addvars['code'], $id);
    }
    
    /** 
     * Распечатать таблицу для отображения списка отчетов
     * 
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
        foreach ( $list as $report )
        {//для каждого шаблона формируем строку
            $this->data[] = $this->get_string_list($report);         
        }
        return $this->print_table('list');
    }
    
    /** 
     * Получает строку для отображения отчета
     * 
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
        $string[] = $obj->name;
        // дата регистрации отчета
        if ( empty($obj->requestdate) )
        {
            $string[] = $this->dof->get_string('no_request_date', $this->get_im());
        }else
        {
            $string[] = dof_userdate($obj->requestdate,'%d.%m.%y %H-%M');
        }
        // дата не ранее которой должен собраться отчет
        if ( empty($obj->crondate) )
        {
            $string[] = $this->dof->get_string('no_cron_date', $this->get_im());
        }else
        {
            $string[] = dof_userdate($obj->crondate,'%d.%m.%y %H-%M');
        }
        // дата завершения сбора отчета
        if ( empty($obj->completedate) )
        {
            $string[] = $this->dof->get_string('no_complete_date', $this->get_im());
        }else
        {
            $string[] = dof_userdate($obj->completedate,'%d.%m.%y %H-%M');
        }
        $string[] = $this->dof->storage('persons')->get_fullname($obj->personid);
        $string[] = $obj->status;//$this->dof->workflow('reports')->get_name($obj->status); // статус 
        $link = ''; 
        if ( $this->dof->storage('reports')->is_access('view_report_'.$obj->plugintype.'_'.$obj->plugincode.'_'.$obj->code,$obj->id) AND 
             $obj->status == 'completed' )
        {// пользователь может просматривать шаблон
            $link .= ' <a href='.$this->dof->url_im($this->get_im(),'/view.php?id='.$obj->id,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view.png').
                    '"alt="'.$this->dof->get_string('view_report', $this->get_im()).
                    '" title="'.$this->dof->get_string('view_report', $this->get_im()).'">'.'</a>';
        }
        // если пользователь имеет право удалить отчет
        if ( $this->dof->storage('reports')->is_access('delete',$obj->id) OR
             $obj->personid == $this->dof->storage('persons')->get_by_moodleid_id() )
        {// добаляем соответствующую иконку в столбец действий
            $link .= ' <a href='.$this->dof->url_im($this->get_im(),'/delete.php?id='.$obj->id,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/delete.png').
                    '"alt="'.$this->dof->get_string('delete_report', $this->get_im()).
                    '" title="'.$this->dof->get_string('delete_report', $this->get_im()).'">'.'</a>';
        }
        array_unshift($string, $link);
        return $string;
    }
    
    /** 
     * Возвращает html-код таблицы
     * 
     * @param string $type - тип отображения данных
     *                           list - список отчетов
     *                           
     * @return string - html-код или пустая строка
     */
    protected function print_table($type)
    {
        // рисуем таблицу
        $table              = new stdClass();
        $table->tablealign  = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->width       = '100%';
        switch ($type) 
        {
            case 'list': // список
//                $table->size = array ('50px','150px','150px','200px','150px','100px'); 
                $table->wrap = array(true);
                $table->align = array("center", "center", "center", "center", "center",
                    "center", "center");
            break;
        }

        // шапка таблицы
        $table->head = $this->get_header($type);
        // заносим данные в таблицу     
        $table->data = $this->data;
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /** 
     * Получить заголовок для списка таблицы, или список полей
     * для списка отображения одного объекта 
     * 
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
                list($url,$icon) = $this->get_link_sort('name');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('name', $this->get_im()).'</a>'.$icon;
                list($url,$icon) = $this->get_link_sort('requestdate');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('request_date', $this->get_im()).'</a>'.$icon;
                list($url,$icon) = $this->get_link_sort('crondate');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('cron_date', $this->get_im()).'</a>'.$icon;
                list($url,$icon) = $this->get_link_sort('completedate');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('complete_date', $this->get_im()).'</a>'.$icon;
                list($url,$icon) = $this->get_link_sort('sortname');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->get_string('person', $this->get_im()).'</a>'.$icon;
                list($url,$icon) = $this->get_link_sort('status');
                $head[] = '<a href=\''.$url.'\'>'.
                    $this->dof->modlib('ig')->igs('status').'</a>'.$icon;
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
        return array($this->dof->url_im('reports','/list.php?sort='.$type.'&dir='.$dir,$add),$icon);
    }
}

?>