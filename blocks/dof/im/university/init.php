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

/** Класс стандартных функций интерфейса
 * 
 */
class dof_im_university implements dof_plugin_im
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /** Метод, реализующий инсталяцию плагина в систему
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function install()
    {
        return true;
    }
    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания/изменения?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function upgrade($oldversion)
    {
        return true;
    }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2016060900;
    }
    /** Возвращает версии интерфейса Деканата, 
     * с которыми этот плагин может работать
     * @return string
     * @access public
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /** Возвращает версии стандарта плагина этого типа, 
     * которым этот плагин соответствует
     * @return string
     * @access public
     */
    public function compat()
    {
        return 'angelfish';
    }
    
    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'im';
    }
    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'university';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib'=>array('nvg'=>2008060300,
                                     'widgets'=>2009050800));
    }
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
       return array();
    }
    /** Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
       return false;
    }
    
    /** Проверяет полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        if ( $this->dof->is_access('manage') OR $this->dof->is_access('datamanage') 
             OR $this->dof->is_access('admin') )
        {//это админ - ему все можно';
            return true;
        }
        switch ($do)
        {
            case 'student': return $this->dof->storage('contracts')->is_student();break;
            case 'teacher': return $this->is_teacher();break;
            case 'client' : return $this->dof->storage('contracts')->is_client();break;
            case 'seller' : return $this->dof->storage('contracts')->is_seller();break;
            case 'manager': return $this->dof->is_access('manage');break;
            default       : return false;
        }
        
        // Используем функционал из $DOFFICE
        return $this->dof->is_access($do, NULL, $userid);
    }
    /** Требует наличия полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        // Используем функционал из $DOFFICE
        //return $this->dof->require_access($do, NULL, $userid);
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "university/{$do} (block/dof/im/university: {$do})";
            if ($objid){$notice.=" id={$objid}";}
            $this->dof->print_error('nopermissions','',$notice);
        }
    }
    /** Обработать событие
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        return true;
    }
    /** Запустить обработку периодических процессов
     * @param int $loan - нагрузка (1 - только срочные, 2 - нормальный режим, 3 - ресурсоемкие операции)
     * @param int $messages - количество отображаемых сообщений (0 - не выводить,1 - статистика,
     *  2 - индикатор, 3 - детальная диагностика)
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function cron($loan,$messages)
    {
        return true;
    }
    /** Обработать задание, отложенное ранее в связи с его длительностью
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function todo($code,$intvar,$mixedvar)
    {
        return true;
    }

    /** Конструктор
     * @param dof_control $dof - идентификатор действия, которое должно быть совершено
     * @access public
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    // **********************************************
    // Методы, предусмотренные интерфейсом im
    // **********************************************
    /** Возвращает текст для отображения в блоке на странице dof
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string - html-код содержимого блока
     */
    function get_block($name, $id = 1)
    {
        $rez = '';
        switch ($name)
        {
            case 'main':
            break;
        }
        return $rez;
    }
    /** Возвращает html-код, который отображается внутри секции
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string  - html-код содержимого секции секции
     */
    function get_section($name, $id = 1)
    {
        $rez = '';
        switch ($name)
        {
            case 'student': $rez .= $this->show_for_student();break;
            case 'teacher': $rez .= $this->show_for_teacher();break;
            case 'manager': $rez .= $this->show_for_manager();break;
            case 'my':      $rez .= $this->show_for_my();break;
        }
        return $rez;
    }
     /** Возвращает текст, отображаемый в блоке на странице курса MOODLE 
      * @return string  - html-код для отображения
      */
    public function get_blocknotes($format='other')
    {
        return "<a href='{$this->dof->url_im('university','/index.php')}'>"
                    .$this->dof->get_string('page_main_name')."</a>";
    }

    // **********************************************
    //              Собственные методы
    // **********************************************
    /** Получить URL к собственным файлам плагина
     * @param string $adds[optional] - фрагмент пути внутри папки плагина
     *                                 начинается с /. Например '/index.php'
     * @param array $vars[optional] - параметры, передаваемые вместе с url
     * @return string - путь к папке с плагином 
     * @access public
     */
    public function url($adds='', $vars=array())
    {
        return $this->dof->url_im($this->code(), $adds, $vars);
    }
    /**
     * Возвращает html-код ссылок доступных ученику 
     * для просмотра в Электронном деканате
     * @return string
     */
    public function show_for_student()
    {
        $rez = '';
        $rez .= '<ul>';
        $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im('university', '/icons/recordbook.png')."'); \">".
        $this->get_one_link('recordbook').'</li>';
        $rez .= '</ul>';
        return $rez;
    }
    
    /**
     * Возвращает html-код ссылок доступных клиенту 
     * для просмотра в Электронном деканате
     * @return string
     */
    public function show_for_client()
    {
        print '<h1>show_for_client</h1>';
    }

    /**
     * Возвращает html-код ссылок доступных преподавателю 
     * для просмотра в Электронном деканате
     * @return string
     */
    public function show_for_teacher()
    {
        $rez = '';
        $rez .= '<ul>';
        $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im('university', '/icons/journal.png')."'); \">".
        $this->get_one_link('journal').'</li>';
//        $rez .= '<li>'.$this->get_one_link('agroups').'</li>';
//        $rez .= '<li>'.$this->get_one_link('cstreams').'</li>';
//        $rez .= '<li>'.$this->get_one_link('cpassed').'</li>';
//        $rez .= '<li>'.$this->get_one_link('plans').'</li>';
        $rez .= '</ul>';
        return $rez;
    }
    
    /**
     * Возвращает html-код ссылок доступных администрации 
     * для просмотра в Электронном деканате
     * @return string
     */
    public function show_for_manager()
    {
        // Подразделение, выбранное на странице
        // @todo заменить вызов optional_param на передачу параметра в функцию
        $depid = optional_param('departmentid', 0, PARAM_INT);
        // пользователь, зашедший на страницу
        $personid = $this->dof->storage('persons')->get_by_moodleid_id();

        $num = $this->get_nums_elements($depid);
        $rez  = '';

        $rez .= '<table width="100%"><tr valign="top"><td>';
        $rez .= '<ul>';
        // заголовок "основная информация"
        $rez .= '<li><b>'.$this->dof->get_string('general_information', $this->code()).'</b></li>';
        

        // подразделения
        $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/departments.png')."'); \">".
        $this->get_one_link('departments').$num['departments'].'</li>';

        // программы
        $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/programms.png')."'); \">".
        $this->get_one_link('programms').$num['programms'].'</li>';
        // дисциплины

        // показываем только админу и датаманагеру
        if ( $this->dof->is_access('admin') OR $this->dof->is_access('datamanage') )
        {
            $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/programmitems.png')."'); \">".
            $this->get_one_link('programmitems').$num['programmitems'].'</li>';
        }

        //метадисциплины
        if ( $this->dof->storage('programmitems')->is_access('view/meta') )
        {
            $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/programmitems.png')."'); \">".
            $this->get_one_link('programmitems','/list_agenum.php?departmentid='.$depid.'&meta=1','link_name_metaprogrammitems').$num['metaprogrammitems'].'</li>';
        }

        // сотрудники
        $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/employees.png')."'); \">".
        $this->get_one_link('employees').$num['eagreements'].'</li>'; 
        
        // ресурсы
        if ( $this->dof->im('inventory')->is_enabled($depid, $personid) )
        {// показываем ссылку на ресурсы если плагин включен
            $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/inventory.png')."'); \">".
            $this->get_one_link('inventory').$num['invitems'].'</li>';
        }else
        {// показываем сообщение о том что плагин отключен
            $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/inventory.png')."'); \">".
            $this->dof->get_string('link_name_inventory',$this->code()).
            ' ['.$this->dof->modlib('ig')->igs('disabled').']'.'</li>';
        }
        
        
        
        $rez .= '</ul>';
        $rez .= '</td><td>';
        $rez .= '<ul>';
         
        // заголовок "работа с контингентом"
        $rez .= '<li><b>'.$this->dof->get_string('manage_staff', $this->code()).'</b></li>';
        
        // Участники
        $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/employees.png')."'); \">".
        $this->get_one_link('participants').'</li>';
        // классы
        $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/agroups.png')."'); \">".
        $this->get_one_link('agroups').$num['agroups'].'</li>';
        // Договоры
        $rez .= "<li style=\"list-style-image: url('".$this->dof->url_im($this->code(),  '/icons/contracts.png')."');\">".
        $this->get_one_link('sel').$num['contracts'].'</li>';
        // <a href="'.$this->dof->url_im('sel', '/contracts/list.php',$addvars).'">'.
        // $this->dof->get_string('contracts',$this->code()).'</a></li>';
        // Новый договор
        if ( $this->dof->storage('config')->get_limitobject('contracts',$depid) )
        {// достигнут лимит - нельзя создавать новый контракт
            $rez .= " <li style=\"margin-left:20px; list-style-image: url('".$this->dof->url_im($this->code(), '/icons/add.png')."');\">".
            $this->get_one_link('sel','/contracts/edit_first.php','new_student').'</li> ';
        }       


        //  <a href="'.$this->dof->url_im('sel', '/contracts/edit_first.php').'">'.
        //   $this->dof->get_string('new_student',$this->code()).'</li> </ul>';
        // люди
        $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/persons.png')."'); \">".
        $this->get_one_link('persons').$num['persons'].'</li>';
        // подписки на программы
        $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/programmsbcs.png')."'); \">".
        $this->get_one_link('programmsbcs').$num['programmsbcs'].'</li>';
        // приказы контингента
        $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/learningorders.png')."'); \">".
        $this->get_one_link('learningorders').'</li>';
        
        $rez .= '</ul>';
        $rez .= '</td><td>';        
        $rez .= '<ul>';
        
        // заголовок "Организация учебного процесса"
        $rez .= '<li><b>'.$this->dof->get_string('process_organizing', $this->code()).'</b></li>';
        // учебные года
        $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/ages.png')."'); \">".
        $this->get_one_link('ages').$num['ages'].'</li>';
        // журнал
        $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/journal.png')."'); \">".
        $this->get_one_link('journal').'</li>';
        // предмето-классы
        $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/cstreams.png')."'); \">".
        $this->get_one_link('cstreams').$num['cstreams'].'</li>';
        // Импорт предмето классов
        $rez .= "<li style=\"margin-left:20px;list-style-image: url('".$this->dof->url_im($this->code(), '/icons/import_cstreams.png')."');\">".
        $this->get_one_link('cstreams','/import_cstreams.php','import_cstreams').'</li>';        
        //$rez .= '<ul style="margin-left:-20px;"><li style="list-style-image: url(\''.$this->dof->url_im($this->code(), "/icons/import_cstreams.png").'\');">
        //<a href="'.$this->dof->url_im('cstreams', '/import_cstreams.php').'">'.
        //$this->dof->get_string('import_cstreams',$this->code()).'</li></ul>';
        // учебный план учеников
        $rez .= "<li style=\"margin-left:20px;list-style-image: url('".$this->dof->url_im($this->code(), '/icons/view_edu_process.png')."');\">".
        $this->get_one_link('cstreams','/by_groups.php','participants_cstreams').'</li>';         
        //$rez .= '<ul style="margin-left:-20px;"><li style="list-style-image: url(\''.$this->dof->url_im($this->code(), '/icons/view_edu_process.png')."'); \">".
        //'<a href="'.$this->dof->url_im('cstreams','/by_groups.php').'">'.
        //$this->dof->get_string('participants_cstreams',$this->code()).'</li></ul>';
        // нагрузка учителя
        $rez .= "<li style=\"margin-left:20px;list-style-image: url('".$this->dof->url_im($this->code(), '/icons/teacher_load.png')."');\">".
        $this->get_one_link('cstreams','/by_load.php','teacher_load').'</li>';         
        // Приказ на передачу нагрузки преподавателя
        $rez .= "<li style=\"margin-left:20px;list-style-image: url('".$this->dof->url_im($this->code(), '/icons/learningorders.png')."'); \">".
        $this->get_one_link('cstreams','/orderctload/index.php','order_change_teacher').'</li>';
        //$rez .= '<ul style="margin-left:-20px;"><li style="list-style-image: url(\''.$this->dof->url_im($this->code(), '/icons/teacher_load.png')."'); \">".
        //'<a href="'.$this->dof->url_im('cstreams','/by_load.php').'">'.
        //$this->dof->get_string('teacher_load',$this->code()).'</li></ul>';
        // шаблоны учебной недели
        $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/schedule.png')."'); \">".
        $this->get_one_link('schedule').'</li>';
        // изучаемые и пройденные курсы
        $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im($this->code(), '/icons/cpassed.png')."'); \">".
        $this->get_one_link('cpassed').$num['cpassed'].'</li>';
        $rez .= '</ul>';
        $rez .= '</td></tr></table>';
        return $rez;
    }

    /**
     * Возвращает html-код ссылок моих данных
     * для просмотра в Электронном деканате
     * @return string
     */
    public  function show_for_my()
    {
        $rez = '';
        $rez .= '<ul>';
        $rez .= "<li style=\" list-style-image: url('".$this->dof->url_im('university', '/icons/profile.png')."'); \">".
        $this->get_one_link('my').'</li>';
        $rez .= '</ul>';
        return $rez;
    }
    
    /**
     * Создает ссылку на главную страницу какого-либо плагина
     * @param string $plugin - код плагина интерфейса
     * @param string $dop - дополнительный путь внутри плагина
     * @param string $str - строка перевода - дополнительно
     * @return string - html-код ссылки
     */
    private function get_one_link($plugin, $dop='', $str='')
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        if ( ! $dop )
        {// не передали доп параметр - значит index
            $dop = '/index.php';
        }
        $path = $this->dof->url_im($plugin,$dop,$addvars);
        $type = 'storage';
        $modul = $plugin;
        $pravo = 'view';
        // у каждого плагина свои права
        switch ($plugin)
        {
            case 'participants'  : $type = 'im'; $pravo = 'interface_base'; break;
            case 'employees'     : $modul = 'eagreements'; break;
            case 'journal'       : $type = 'im';   
                                   $pravo = 'view_schevents'; break;
            case 'sel'           : $modul = 'contracts'; 
                if ( $str == 'new_student')
                {// новый договор
                    $pravo = 'create';
                }
                break;
            case 'learningorders' :  $pravo = 'order'; 
                                     $type = 'im';    break;             
            //case 'persons'        :  $pravo = 'view'; break;
            //case 'programmsbcs'   :  $pravo = 'view'; break; 
            case 'inventory'      :  $type = 'im';$pravo = 'view'; break;
            //case 'ages'  	        :  $pravo = 'view'; break;  
            //case 'agroups'        :  $pravo = 'view'; break;            
            //case 'cpassed'        :  $pravo = 'view'; break;  
            case 'cstreams'       : 
                if ( $str ==  'import_cstreams' )
                {// импорт
                    $pravo = 'import';
                    $type = 'im';
                }elseif( $str == 'participants_cstreams' )
                {// учебный план учашихся
                    $pravo = 'viewcurriculum';
                    $type = 'im';
                }elseif( $str == 'order_change_teacher' )
                {// учебный план учашихся
                    $pravo = 'order';
                    $type = 'im';
                }
                break;
            case 'schedule'        :  $modul = 'schtemplates'; break;  
        }
        // переопределим переменную plugin
        if ( $str )
        {// перевод не основного плагина
            $plugin = $str;
        }else 
        {
            $plugin = 'link_name_'.$plugin;
        }
        
        if ( $modul == 'recordbook' OR $modul == 'my' )
        {
            return '<a href="'.$path.'">'.$this->dof->get_string($plugin,$this->code()).'</a>';
        }
 
        if ( $this->dof->$type($modul)->is_access($pravo) )
        {
            return '<a href="'.$path.'">'.$this->dof->get_string($plugin,$this->code()).'</a>';
        }


        return $this->dof->get_string($plugin,$this->code());
    }

    
    
    /**
     * Возвращает лимит объектов, которые могут быть созданы в этом плагине
     * @param string $plugincode - код плагина (ages,agroups...)
     * @param string $dep - id подразделения, по умолчанию 0
     * @return string $num - кол элементов в иде строки 
     */
    private function get_objectlimit($plugincode)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        // берем значение настройки
        $num = $this->dof->storage('config')->get_config_value('objectlimit', 'storage', $plugincode, $depid);
        if ( $num == '-1' )
        {// бесконечно много
            return '&#8734;';
        }
        if ( $num )
        {// определенное кол-во
            return $num;
        }else
        {
            return '';
        }
    }


    /**
     * Возвращает массив чисел активных элементов
     * Добавлен элемент "метадисциплина"
     * @param integer $depid - id текущего подразделения(по умолчанию 0)
     * @return array - 
     */
    private function get_nums_elements($depid=0)
    {
        // массив отображаемых элементов на главной странице деканата
        $elements = array('departments','programms','programmitems','metaprogrammitems','eagreements','agroups','contracts',
        				  'persons','programmsbcs','ages','cstreams','cpassed','schtemplates','invitems');
        $count = array();

        foreach ( $elements as $element)
        {// определим кол 
            $dep = 'departmentid';
            switch ( $element )
            {// определяем статус

                case 'departments'   : 
                    // определим количество элементов
                    if ( $depid != 0 )
                    {// текущее подразделение            
                        $conds = new stdClass();
                        $conds->leaddepid = $depid;
                        $leaddep = array_keys($this->dof->storage('departments')->departments_list_subordinated($conds->leaddepid));
                        // добавим сам подчененные массив в массив
                        $leaddep[] = $conds->leaddepid;
                        $conds->leaddepid = $leaddep;                    
                        $count[$element] = $this->dof->storage($element)->count_records_select($this->dof->im('departments')->get_select_listing($conds));
                    }else 
                    {// все подразделения
                        $count[$element] = $this->dof->storage($element)->count_records_select('status is null OR status=\'active\' ');
                    }                                      
                    break;    
                case 'cpassed'       : 
                    $status = 'active';
                    if ( $depid != 0 )
                    {// текущее подразделение 
                        $params = array(); 
                        $params['departmentid'] = $depid;           
                        $cstreams = $this->dof->storage('cstreams')->get_records($params, '', 'id');
                        if ( $cstreams )
                        {// есть записи принадлежащие такому подразделению
                            $cstreamids = array();
                            foreach ( $cstreams as $cstream )
                            {// собираем все cstreamids
                                $cstreamids[] = $cstream->id;
                            }
                            // склеиваем их в строку
                            $cstreamidsstring = implode(', ', $cstreamids);
                            // составляем условие
                            $selects = ' cstreamid IN ('.$cstreamidsstring.')';
                            $count[$element] = $this->dof->storage($element)->count_records_select('status=\'active\' AND '.$selects);
                        }else 
                        {
                            $count[$element] = '';
                        }    
                    }else 
                    {// все подразделения
                        $params = array();
                        $params['status'] = $status;
                        $count[$element] = $this->dof->storage($element)->count_list($params);
                    }        
                    break;  
                case 'programmitems':
                    if ( $depid != 0 )
                    {// текущее подразделение
                        $params = array();
                        $params['departmentid'] = $depid; 
                        //считаем общее число дисциплин(включая метадисциплины)
                        $countall[$element] = $this->dof->storage($element)->count_list($params);
                        
                        //programmid = 0 - признак метадисциплины
                        $params['programmid'] = 0;
                        //считаем число обычных дисциплин
                        $count[$element] = $countall[$element] - $this->dof->storage($element)->count_list($params);
                    }
                    else
                    {// все подразделения
                        $params = array();
                        //считаем общее число дисциплин(включая метадисциплины)
                        $countall[$element] = $this->dof->storage($element)->count_list($params);
                        
                        //programmid = 0 - признак метадисциплины
                        $params['programmid'] = 0;
                        //считаем число обычных дисциплин
                        $count[$element] = $countall[$element] - $this->dof->storage($element)->count_list($params);
                    }
                    break;
                case 'metaprogrammitems':
                    //считаем кол-во метадисциплин
	                $count[$element] = $this->dof->storage('programmitems')->get_metapitems_count($depid);
                    break;
                    
                case 'invitems'      :   $status = 'active';
                    if ( ! $depid )
                    {
                        $count[$element] = '-';
                    }  
                    break;
                case 'programms'     : $status = 'available'; break;
                case 'persons'       : $status = 'normal';    break;
                case 'contracts'     : $status = 'work';      break;
                case 'programmitems' :
                case 'eagreements'   :
                case 'agroups'       : 
                case 'programmsbcs'  : 
                case 'ages'   		 : 
                case 'cstreams'      : 
                case 'schtemplates'      : $status = 'active'; break; 
                 

                default: $status = 'active';                              
            }

           
            // определим количество элементов(для сpassed и depart  шни уже определены)
            $params = array();
            if ( $depid != 0 AND ! in_array($element, array('departments','cpassed','programmitems','metaprogrammitems')) )
            {// текущее подразделение    
                $params['status'] = $status;        
                $params['departmentid'] = $depid;                           
                $count[$element] = $this->dof->storage($element)->count_list($params);
            }elseif( ! in_array($element, array('departments','cpassed','programmitems','metaprogrammitems','invitems')) ) 
            {    // все подразделения
                $params['status'] = $status;
                $count[$element] = $this->dof->storage($element)->count_list($params);
            }
            
        }

        // добавим лимит
        foreach ( $count as $element=>$num )
        {
            if ($element == 'metaprogrammitems')
            {
                $a = $this->dof->storage('programmitems')->get_limit_metapitems();
            }
            else
            {
                $a =  $this->get_objectlimit($element);
            }
            
            if ( $a )
            {
                $count[$element] =' ('.$num.'/'.$a.')';
            }else
            {
                $count[$element] =' ('.$num.')';
            }
        }
	    
        return $count;
    }
    
    /**
     * Является ли пользователь учителем
     * @param $personid - id пользователя в таблице persons
     * @return bool
     */
    public function is_teacher($personid = null)
    {
        $userid = $this->store_userid();
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        return $this->dof->storage('cstreams')->is_teacher($personid);
    }
}
?>
