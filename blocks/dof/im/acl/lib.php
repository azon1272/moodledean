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

// Требуется базовый доступ
$DOF->require_access('view');

/** 
 * Класс отображения вкладок для доверенностей и доверенных лиц
 * 
 */
class dof_im_aclwarrants_display
{
    /**
     * @var dof_control
     */
    protected $dof;
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
        $this->dof          = $dof;
        $this->departmentid = $departmentid;
        $this->addvars      = $addvars;
    }
    
    /** Возвращает код im'а, в котором хранятся отслеживаемые объекты
     * @return string
     * @access private
     */
    private function get_im()
    {
        return 'acl';
    }
    
    /***************************************/
    /**** Методы отображения информации ****/
    /****      на главной странице      ****/
    /***************************************/
    
    public function get_nvg($type='', $typelist='')
    {
        $addvars = $this->addvars;
        $addvars['type'] = $type;
        $addvars['typelist'] = $typelist;
        switch ($type)
        {
            case 0:
                $string = $this->dof->get_string('warrants', 'acl');
                $this->dof->modlib('nvg')->add_level($string, $this->dof->url_im('acl','/index.php'), $addvars);
            break;
            case 1:
                $string = $this->dof->get_string('warrantagents', 'acl');
                $this->dof->modlib('nvg')->add_level($string, $this->dof->url_im('acl','/index.php'), $addvars);
            break;
        }
        // добавляем уровень навигации для вкладок 2-ого уровня
        switch ($type.'-'.$typelist)
        {
            case '0-0':
                $string = $this->dof->get_string('warrants_table_given_by_core', 'acl');
                $this->dof->modlib('nvg')->add_level($string, $this->dof->url_im('acl','/index.php'),$addvars);
                break;
            case '0-1':
                $string = $this->dof->get_string('warrants_table_given_by_system', 'acl');
                $this->dof->modlib('nvg')->add_level($string, $this->dof->url_im('acl','/index.php'),$addvars);
                break;
                
            case '0-2':
                $string = $this->dof->get_string('warrants_table_given_by_users', 'acl');
                $this->dof->modlib('nvg')->add_level($string, $this->dof->url_im('acl','/index.php'),$addvars);
                break;
                
            case '0-3':
                $string = $this->dof->get_string('warrants_table_given_to_me', 'acl');
                $this->dof->modlib('nvg')->add_level($string, $this->dof->url_im('acl','/index.php'),$addvars);
                break;
                
            case '0-4':
                $string = $this->dof->get_string('warrants_table_given_by_me', 'acl');
                $this->dof->modlib('nvg')->add_level($string, $this->dof->url_im('acl','/index.php'),$addvars);
                break;
                
            case '1-0':
                $string = $this->dof->get_string('warrants_table_trust_me', 'acl');
                $this->dof->modlib('nvg')->add_level($string, $this->dof->url_im('acl','/index.php'),$addvars);
                break;
                
            case '1-1':
                $string = $this->dof->get_string('warrants_table_trust_my', 'acl');
                $this->dof->modlib('nvg')->add_level($string, $this->dof->url_im('acl','/index.php'),$addvars);
                break;
            case '1-2':
                $string = $this->dof->get_string('warrants_table_trust_all', 'acl');
                $this->dof->modlib('nvg')->add_level($string, $this->dof->url_im('acl','/index.php'),$addvars);
                break;
        }
    }
    
   /** Получение таблицы с вкладками для отображения доверенностей
    * 
    * @param string $type - тип вкладки для второго уровня
    * @param string $typelist - тип отображения для вкладки 2-ого уровня
    * @return string - html-код заголовка с вкладками
    */
    public function get_main_page_tabs($type='', $typelist='')
    {   
        $result = '';
        // создаем массив, содержащий уровни вкладок
        $tablevels = array();
        // в любом случае получаем данные вкладки верхнего уровня
        $tablevels = $this->get_aclwarrants_tabs();
        $result .= '<div>'.
                   $this->dof->modlib('widgets')->print_tabs($tablevels, $type, NULL, NULL, true).'</div>';
    
        if ($type === 0)
        {// выводим вкладку 2-ого уровня для доверенностей
            $tablevels = $this->get_aclwarrants_tabs($type);
            $result .= '<div>'.
                       $this->dof->modlib('widgets')->print_tabs($tablevels, $typelist, NULL, NULL, true).'</div>';
            
        }else if ($type === 1)
        {// выводим вкладку 2-ого уровня для поверенных
            $tablevels = $this->get_aclwarrants_tabs($type);
            $result .= '<div>'.
                       $this->dof->modlib('widgets')->print_tabs($tablevels, $typelist, NULL, NULL, true).'</div>';
        }
        return $result;
    }
    
   /** Получить список закладок для доверенностей
    * @param string $type - тип вкладки: warrant, trust
    *
    * @return array - массив вкладок, объектов dof_modlib_widgets_tabobject
    */
    protected function get_aclwarrants_tabs($type='')
    {
        $tabs = array();
        // получаем список для вкладки соответствующего типа
        $data = $this->get_warrant_table_param($type);
        
        foreach ( $data as $line )
        {// создаем саму вкладку
            $tabs[] = $this->dof->modlib('widgets')->create_tab($line['id'], $line['link'], $line['text']);
        }
        return $tabs;
    }
    
    /** Получить данные для вкладок с доверенностями
     * @param string $type - тип вкладки: warrant, trust
     *
     * @return array - массив вкладок
     */
    protected function get_warrant_table_param($type='')
    {
        $data = array();
    
        if ( $type === 0)
        {// собираем данные для вкладки "Доверенности"
            $wararr = array();
            if ( $this->dof->is_access('admin') )
            {// только для админа
                $wararr[] = array('id' => 0, 'text' => $this->dof->get_string('warrants_table_given_by_core', 'acl'));
                $wararr[] = array('id' => 1, 'text' => $this->dof->get_string('warrants_table_given_by_system', 'acl'));
                $wararr[] = array('id' => 2, 'text' => $this->dof->get_string('warrants_table_given_by_users', 'acl'));
            }
            $wararr[] = array('id' => 3, 'text' => $this->dof->get_string('warrants_table_given_to_me', 'acl'));
            $wararr[] = array('id' => 4, 'text' => $this->dof->get_string('warrants_table_given_by_me', 'acl'));
            
            // массив с данными для отображения
            foreach ($wararr as $value)
            {
                $link = $this->dof->url_im('acl', '/index.php', array('type' => 0, 
                        'typelist' => $value['id'], 'departmentid' => $this->departmentid));
                
                $data[] = array('id' => $value['id'], 'link' => $link, 'text' => $value['text']);
            }
            
        }else if ( $type === 1)
        {// собираем данные для вкладки "Поверенные"
            $wararr = array();
            $wararr[] = array('id' => 0, 'text' => $this->dof->get_string('warrants_table_trust_me', 'acl'));
            $wararr[] = array('id' => 1, 'text' => $this->dof->get_string('warrants_table_trust_my', 'acl'));
            if ( $this->dof->is_access('admin') )
            {// только для админа
                $wararr[] = array('id' => 2, 'text' => $this->dof->get_string('warrants_table_trust_all', 'acl'));
            }
            foreach ($wararr as $value)
            {
                $link = $this->dof->url_im('acl', '/index.php', array('type' => 1,
                        'typelist' => $value['id'], 'departmentid' => $this->departmentid) );
                
                $data[] = array('id' => $value['id'],  'link' => $link, 'text' => $value['text']);
            }
             
        }else
        {
            $wlink = $this->dof->url_im('acl', '/index.php', array('type' => 0, 'departmentid' => $this->departmentid) );
            $tlink = $this->dof->url_im('acl', '/index.php', array('type' => 1, 'departmentid' => $this->departmentid) );
            
            $data[] = array('id' => 0,    'link' => $wlink,    'text' => 
                    $this->dof->get_string('warrants_table_warrants', 'acl'));
            
            $data[] = array('id' => 1, 'link' => $tlink,    'text' => 
                    $this->dof->get_string('warrants_table_trusts', 'acl'));
        }
        return $data;
    }
    
   /**
    * В зависимости от типа получаем список доверенностей и 
    * приводим их в удобный для отображения вид
    * 
    * @return string - html-код списка доверенностей
    */
    public function get_warrants_by_type($type, $limitnum, $limitfrom, $count=false)
    {// в зависимости от типа получаем определенный список
        $conds = array();
        switch ($type)
        {
            case 0:
                $conds['parenttype'] = 'core';
            break;
                
            case 1:
                $conds['parenttype'] = 'ext';
                $conds['departmentid'] = $this->addvars['departmentid'];
            break;
                
            case 2:
                $conds['parenttype'] = 'sub';
                $conds['departmentid'] = $this->addvars['departmentid'];
            break;
                
            case 3:
                $conds['personid'] = $this->dof->storage('persons')->get_by_moodleid_id();
            break;
                
            case 4:
                $conds['ownerid'] = $this->dof->storage('persons')->get_by_moodleid_id();
            break;
                
            default: return false;
        }
        if ( $count )
        {
            return $this->dof->storage('aclwarrants')->get_listing($conds,$limitfrom,$limitnum,'','*',true);
        }
        $sort = '';
        if ($this->check_sortable_params('warrant', $this->addvars['ordercol']))
        {// парметры верны - добавляем в запрос сортировку
            $sort = $this->addvars['ordercol'];
        }
        $warrantlist = $this->dof->storage('aclwarrants')->get_listing($conds,$limitfrom,$limitnum,$sort);
        // готовим таблицу для вывода
        $table = new stdClass();
        $table->tablealign = 'center';
        $table->cellpadding = '8';
        $table->cellspacing = '4';
        $table->size = array('20%', '10%', '20%', '5%', '10%', '15%', '15%');
        $table->width = '70%';
        $table->align = $table->align = array('center', 'center', 'center', 'center', 'center', 'center', 'center');
        
        $table->head = $this->get_header('sortwarrant');
        if (!empty($warrantlist))
        {// маcсив не пустой - заносим данные в таблицу
            foreach ($warrantlist as $line)
            {// выбираем только те данные, которые необходимо отображать
                $obj = new stdClass();
                
                $actions = '';
                $actions .= "<a href='".$this->dof->url_im('acl', '/warrantacl.php', array(
                        'id' => $line->id,'departmentid'=>$this->departmentid))."'><img src='".$this->dof->url_im('acl', '/icons/list_acl.png')
                        ."' title='".$this->dof->get_string('warrants_table_actions_acl_list', 'acl')
                        ."' /></a>";
                if ($line->parenttype == 'sub')
                {// для субдоверенностей добавляем кнопку редактирования
                    $actions .= $this->dof->modlib('ig')->icon('edit',$this->dof->url_im('acl','/givewarrant.php?id='.$line->id.
                            '&aclwarrantid='.$line->parentid.'&departmentid='.$this->departmentid));
                }else if ($line->parenttype == 'ext')
                {// для доверенностей, выданных системой включаем кнопку передоверения
                    if ( $this->dof->storage('aclwarrants')->is_delegatable($line) )
                    {// Если родительская доверенность позволяет это сделать
                        $actions .= "<a href='".$this->dof->url_im('acl', '/givewarrant.php?aclwarrantid='.$line->id.'&departmentid='.$this->departmentid)
                        ."'><img src='".$this->dof->url_im('acl', '/icons/sub_warrant.png')
                        ."' title='".$this->dof->get_string('warrants_table_actions_warrant_give', 'acl')
                        ."' /></a>";
                    }
                }
                $obj->actions    = $actions;
                $obj->name       = "<a href='".$this->dof->url_im('acl', '/warrantview.php', array(
                        'aclwarrantid' => $line->id))."'>".$line->name."</a>";
                $obj->code          = $line->code;
                $obj->description   = $line->description;
                $obj->status        = $this->dof->workflow('aclwarrants')->get_name($line->status);         
                $parentlink = '';
                if (intval($line->parentid) > 0)
                {// parentid > 0 - получаем имя родительской доверенности
                    if ( $parent = $this->dof->storage('aclwarrants')->get_record(array(
                            'id' => $line->parentid)) )
                    {// есть запись - формируем ссылку на доверенность 
                        $link = $this->dof->url_im('acl', '/warrantview.php', array(
                                'aclwarrantid' => $line->parentid));
                        $string = $parent->name."[".$parent->code."]";
                        
                        $parentlink = "<a href='".$link."'>".$string."</a>";
                    }    
                }
                $obj->parent        = $parentlink;
                $personfio = '';
                if ( intval($line->ownerid) > 0)
                {// ownerid больше нуля - получаем полное имя владельца
                    $personfio = $this->dof->storage('persons')->get_fullname($line->ownerid);
                }
                $obj->ownerid        = $personfio;
                $string = '';
                if (intval($line->parentid) > 0)
                {// parentid > 0 - получаем имя родительской доверенности
                    if ( $parent = $this->dof->storage('aclwarrants')->get_record(array(
                            'id' => $line->parentid)) )
                    {// есть запись - формируем ссылку на доверенность
                    $link = $this->dof->url_im('acl', '/warrantacl.php', array(
                            'id' => $line->parentid));
                    $string = $parent->name."[".$parent->code."]";
                    $parentlink = "<a href='".$link."'>".$string."</a>";
                    }
                }
                $string = '';
                $department = new stdClass();
                if ( intval($line->departmentid) > 0)
                {// departmentid больше нуля - получаем подразделение
                    if ( $department = $this->dof->storage('departments')->get_record(array(
                            'id' => $line->departmentid)))
                    {
                        $string = $department->name."[".$department->code."]";
                    }
                }
                $obj->departmentid    = $string;
                $table->data[] = $obj;
            }    
        }
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /**
     * В зависимости от типа получаем список поверенных и
     * приводим их в удобный для отображения вид
     *
     * @return string - html-код списка доверенностей
     */
    public function get_warrantagents_by_type($type, $limitnum, $limitfrom, $count=false, $aclwarrantid=0)
    {// в зависимости от типа получаем определенный список
        $warrantagentslist = array();
        $conds = array();
        switch ($type)
        {
            case 0:
                $conds['personid'] = $this->dof->storage('persons')->get_by_moodleid_id();
                break;
                 
            case 1:
                $conds['ownerid'] = $this->dof->storage('persons')->get_by_moodleid_id();
                if ( $aclwarrantid )
                {
                    $conds['aclwarrantid'] = $aclwarrantid;
                }
            break;
            case 2:
                $conds['departmentid'] = $this->departmentid;
                if ( $aclwarrantid )
                {
                    $conds['aclwarrantid'] = $aclwarrantid;
                }
            break;     
            default: return false;
        }
        if ( $count )
        {
            return $this->dof->storage('aclwarrantagents')->get_listing($conds,null,null,'','*',true);
        }
        $sort = '';
        if ($this->check_sortable_params('trust', $this->addvars['ordercol']))
        {// парметры верны - добавляем в запрос сортировку
            $sort = $this->addvars['ordercol'];    
        }
        $warrantagentslist = $this->dof->storage('aclwarrantagents')->get_listing(
                $conds,$limitfrom,$limitnum,$sort);
        // готовим таблицу для вывода
        $table = new stdClass();
        $table->tablealign = 'center';
        $table->cellpadding = 8;
        $table->cellspacing = 4;
        $table->width = '70%';
        $table->align = $table->align = array('center', 'center', 'center', 'center', 'center');
        
        $table->head = $this->get_header('sorttrust');
        if ( !empty($warrantagentslist) )
        {// маcсив не пустой - заносим данные в таблицу
            foreach ($warrantagentslist as $line)
            {// выбираем только те данные, которые необходимо отображать
                $obj = new stdClass();
                $personfio = '';
                if ( intval($line->personid) > 0)
                {// personid больше нуля - получаем полное имя владельца
                    $personfio = $this->dof->storage('persons')->get_fullname($line->personid);
                }
                $obj->personid        = $personfio;
                $string = '';
                $department = new stdClass();
                if ( intval($line->departmentid) > 0)
                {// departmentid больше нуля - получаем подразделение
                    if ( $department = $this->dof->storage('departments')->get_record(array(
                            'id' => $line->departmentid)))
                    {
                        $string = $department->name."[".$department->code."]";
                    } 
                }
                $obj->departmentid  = $string;
                $obj->begindate     = strftime('%d/%m/%Y', $line->begindate);
                $obj->duration         = strftime('%d/%m/%Y', $line->begindate + $line->duration);
                $obj->status        = $this->dof->workflow('aclwarrantagents')->get_name($line->status);
                $table->data[] = $obj;
            }
        }
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /** В зависимости от типа выводим либо доверенности, 
     *  либо поверенных 
     * @param string $type - тип данных на выводе
     * @param int $depid - id одразделения
     * @param int $aclwarrant - id доверенности
     * @return string - html-код созданных эелементов
     */
    public function get_tablelist_data($type, $typelist, $limitnum, $limitfrom, $aclwarrantid, $count=false) 
    {   
        switch ($type)
        {
            case 0:
                return $this->get_warrants_by_type($typelist, $limitnum, $limitfrom, $count);
                break;
                 
            case 1:
                return $this->get_warrantagents_by_type($typelist, $limitnum, $limitfrom, $count, $aclwarrantid);
                break;
                 
            default: return false;
        }
        
        return false;
    }
    
    /** Получить заголовок для списка таблицы, или список полей
     * для списка отображения одного объекта
     * @param string $type - тип отображения данных:
     *                        underload - недогруженные
     * @param bool $sortable - подключение сортировки
     * @return array
     */
    protected function get_header($type)
    {
        $params = array();
        $params = $this->addvars;
        //$params['orderby'] = 'ASC';
        unset($params['ordercol']);
        
        //if (isset($this->addvars['orderby']) AND $this->addvars['orderby'] == 'ASC')
        //{// текущий порядок отображения по возрастанию - укажем обратный
        //    $params['orderby'] = 'DESC';
        //}
        $link = $this->dof->url_im('acl', '/index.php', $params);
        
        switch ( $type )
        {// доверенности
            case 'warrant':        
                return array(
                    $this->dof->modlib('ig')->igs('actions'),
                    $this->dof->get_string('warrants_table_code', $this->get_im()),
                    $this->dof->get_string('warrants_table_description', $this->get_im()),
                    $this->dof->get_string('warrants_table_status', $this->get_im()),
                    $this->dof->get_string('warrants_table_parent', $this->get_im()),
                    $this->dof->get_string('warrants_table_ownerid', $this->get_im()),
                    $this->dof->get_string('warrants_table_departmentid', $this->get_im())
                );
            break;
            case 'sortwarrant':     
                return array(
                    $this->dof->modlib('ig')->igs('actions'),
                    "<a href='".$link."&ordercol=name"."'>".
                        $this->dof->get_string('warrants_table_name', $this->get_im())."</a>",
                    "<a href='".$link."&ordercol=code"."'>".
                        $this->dof->get_string('warrants_table_code', $this->get_im())."</a>",
                    $this->dof->get_string('warrants_table_description', $this->get_im()),
                    "<a href='".$link."&ordercol=status"."'>".
                        $this->dof->get_string('warrants_table_status', $this->get_im())."</a>",
                    $this->dof->get_string('warrants_table_parent', $this->get_im()),
                    $this->dof->get_string('warrants_table_ownerid', $this->get_im()),
                    $this->dof->get_string('warrants_table_departmentid', $this->get_im())
                );
            break;
            // поверенные
            case 'trust':
                return array(
                    $this->dof->get_string('warrants_table_person', $this->get_im()),
                    $this->dof->get_string('warrants_table_departmentid', $this->get_im()),
                    $this->dof->get_string('warrants_table_begindate', $this->get_im()),
                    $this->dof->get_string('warrants_table_duration', $this->get_im()),
                    $this->dof->get_string('warrants_table_status', $this->get_im()) );
            break;
            case 'sorttrust':
                return array(
                    $this->dof->get_string('warrants_table_person', $this->get_im()),
                    $this->dof->get_string('warrants_table_departmentid', $this->get_im()),
                    "<a href='".$link."&ordercol=begindate"."'>".
                        $this->dof->get_string('warrants_table_begindate', $this->get_im())."</a>",
                    $this->dof->get_string('warrants_table_duration', $this->get_im()),
                    "<a href='".$link."&ordercol=status"."'>".
                        $this->dof->get_string('warrants_table_status', $this->get_im())."</a>" );
            break;
        }
    }
    
    public function check_sortable_params($tabtype, $ordercol, $orderby = '')
    {// проверяем колонки
        if ($tabtype == 'warrant')
        {
            if ($ordercol != 'name' AND $ordercol != 'status' AND $ordercol != 'code')
            {
                return false;
            }
        } else if ($tabtype == 'trust')
        {
            if ($ordercol != 'departmentid' AND $ordercol != 'status' AND $ordercol != 'begindate'
                   AND $ordercol != 'duration')
            {
                return false;
            }
        } else 
        {
            return false;
        }
        // метод сортировки
        //if (strtolower($orderby) != 'asc' AND strtolower($orderby) != 'desc')
        //{
        //    return false;
        //}
        return true;
    }
}


?>