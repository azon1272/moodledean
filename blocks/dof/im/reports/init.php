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



/** Пример плагина интерфейса
 * 
 */
class dof_im_reports implements dof_plugin_im
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /** 
     * Метод, реализующий инсталяцию плагина в систему
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
    /** 
     * Метод, реализующий обновление плагина в системе
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
    /** 
     * Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2016011400;
    }
    /** 
     * Возвращает версии интерфейса Деканата, 
     * с которыми этот плагин может работать
     * @return string
     * @access public
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /** 
     * Возвращает версии стандарта плагина этого типа, 
     * которым этот плагин соответствует
     * @return string
     * @access public
     */
    public function compat()
    {
        return 'angelfish';
    }
    
    /** 
     * Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'im';
    }
    /** 
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'reports';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('reports'=>2012042500));
    }
    /** 
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
       return array(
                array('plugintype' => 'im',
                      'plugincode' => 'obj',
                      'eventcode'  => 'get_object_url'));  
    }
    /** 
     * Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
		// Этому плагину не нужен крон
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
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('admin') 
             OR $this->dof->is_access('manage') )
        {// манагеру можно все
            return true;
        }  
        // проверяем права, используя систему полномочий acl
        if ( $this->acl_access_check($do, $objid, $userid) )
        {// права есть - все нормально
            return true;
        }
        return false;
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
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "reports/{$do} (block/dof/im/reports: {$do})";
            if ($objid){$notice.=" id={$objid}";}
            $this->dof->print_error('nopermissions','',$notice);
        }
    }
    /** 
     * Обработать событие
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
        if ( $gentype == 'im' AND $gencode == 'obj' AND $eventcode == 'get_object_url' )
        {
            if ( $mixedvar['storage'] == 'reports' )
            {
                if ( isset($mixedvar['action']) AND $mixedvar['action'] == 'view' )
                {// Получение ссылки на просмотр объекта
                    $params = array('pitemid' => $intvar);
                    if ( isset($mixedvar['urlparams']) AND is_array($mixedvar['urlparams']) )
                    {
                        $params = array_merge($params, $mixedvar['urlparams']);
                    }
                    return $this->url('/view.php', $params);
                }
            }
        }
        return false;
    }
    /** 
     * Запустить обработку периодических процессов
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
    /** 
     * Обработать задание, отложенное ранее в связи с его длительностью
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
    /** 
     * Конструктор
     * @param dof_control $dof - объект $DOF
     * @access public
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    // **********************************************
    // Методы, предусмотренные интерфейсом im
    // **********************************************
    /** 
     * Возвращает содержимое блока
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string - html-код названия блока
     */
    function get_block($name, $id = 1)
    {
        switch ($name)
		{//выбираем нужнуое содержание по названию
		    case 'main': 
       		 	return '<a href="'.$this->dof->url_im('reports').'">'
							.$this->dof->get_string('title', 'reports').'</a>';
			break;	
		}
    }
    /** Возвращает содержимое секции
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string  - html-код названия секции
     */
    function get_section($name, $id = 1)
    {
        $rez = '';
        switch ($name)
        {

        }
        return $rez;
    }
     /** Возвращает текст для отображения в блоке dof
     * @return string  - html-код для отображения
     */
    public function get_blocknotes($format='other')
    {
		return "<a href='{$this->dof->url_im('reports','/')}'>"
                    .$this->dof->get_string('title','reports')."</a>";
    }
    
    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************   

    /** Проверить права через систему полномочий acl
     * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objectid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя в Moodle, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     */
    protected function acl_access_check($do, $objectid, $userid)
    {
        if ( ! $userid )
        {// получаем id пользователя в persons
            $userid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        }
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objectid, $userid);   
           
        switch ( $do )
        {// определяем дополнительные параметры в зависимости от запрашиваемого права
            // для некоторых прав название полномочия заменим на стандартное, для совместимости
            // запрошено неизвестное полномочие
            default: $acldata->code = $do;                                                                                   
        }
          //print_object($acldata);
        // проверка
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// право есть заканчиваем обработку
            return true;
        }        
        return false;
    }
    /** Получить список параметров для фунции has_hight()
     * @todo завести дополнительные права в плагине storage/persons и storage/contracts 
     * и при редактировании контракта или персоны обращаться к ним
     * 
     * @return object - список параметров для фунции has_hight()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $userid
     */
    protected function get_access_parametrs($action, $objectid, $userid)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->userid       = $userid;
        $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        $result->objectid     = $objectid;
        if ( ! $objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }else
        {// если указан - то установим подразделение
            $result->departmentid = $this->dof->storage('reports')->get_field($objectid, 'departmentid');
        }
        
        return $result;
    }    
    /** Проверить права через плагин acl.
     * Функция вынесена сюда, чтобы постоянно не писать длинный вызов и не перечислять все аргументы
     * 
     * @return bool
     * @param object $acldata - объект с данными для функции storage/acl->has_right() 
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->
                    has_right($acldata->plugintype, $acldata->plugincode, $acldata->code, 
                              $acldata->userid, $acldata->departmentid, $acldata->objectid);
    }         

    /** Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        return $a;
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
     * Сформировать HTML-код таблицы отчетов
     * 
     * @param string $plugintype - Тип плагина, отчеты которого будут включены в таблицу
     * @param string $plugincode - Код плагина, отчеты которого будут включены в таблицу
     * @param string $code - Код отчетов
     * @param array $options - Опции формирования
     *      [addvars] - Массив GET-параметров для ссылок
     *      [limitfrom] - Смещение отчетов
     *      [limitnum] - Число отчетов в таблице
     *      
     * @return string - HTML-код таблицы
     * 
     * @todo - Доработать проверки на наличие методов при проверке доступа
     */
    public function table_reports($plugintype = NULL, $plugincode = NULL, $code = NULL, $options = [])
    {
        // Формироване базовых опций отображения
        if ( ! isset($options['addvars']) )
        {// Формирование массива GET - параметров
            $options['addvars'] = [];
        }
        if ( ! isset($options['addvars']['departmentid']) )
        {// Добавление подразделения
            $options['addvars']['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }
        if ( ! isset($options['limitfrom']) )
        {// Нулевое смещение 
            $options['limitfrom'] = 0;
        }
        if ( ! isset($options['limitnum']) )
        {// Отобразить все элементы
            $options['limitnum'] = 0;
        }
        if ( ! isset($options['csvexport']) )
        {// Доступ к экспорту в CSV
            $options['csvexport'] = false;
        }
        
        // Условия выборки
        $conds = [];
        $conds['departmentid'] = $options['addvars']['departmentid'];
        if ( ! empty($plugintype) )
        {// Тип плагина отчета передан
            $conds['plugintype']   = $plugintype;
        }
        if ( ! empty($plugincode) )
        {// Код плагина отчета передан 
            $conds['plugincode']   = $plugincode;
        }
        if ( ! empty($code) )
        {// Код отчета передан
            $conds['code']   = $code;
        }
        // Статусы
        $realstatuses = $this->dof->workflow('reports')->get_meta_list('real');
        $conds['status']   = array_keys($realstatuses);
        // Получение массива отчетов
        $list = $this->dof->storage('reports')->get_records($conds, '', '*', $options['limitfrom'], $options['limitnum']);
        
        // Формирование таблицы
        $table = new html_table();
        $table->tablealign = 'center';
        $table->width      = '100%';
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->size = ['5%', '5%', '30%', '10%', '10%', '10%', '20%', '10%'];
        $table->wrap = [true];
        $table->align = ['center', 'center', 'left', 'left', 'left', 'left', 'left', 'left'];
        
        // Заголовок таблицы
        $table->head = [];
        // Номер строки
        $table->head[] = $this->dof->get_string('table_report_num', 'reports');
        // Действия
        $table->head[] = $this->dof->get_string('table_report_actions', 'reports');
        // Имя отчета
        $table->head[] = $this->dof->get_string('table_report_name', 'reports');
        // Дата заказа
        $table->head[] = $this->dof->get_string('table_report_requestdate', 'reports');
        // Начало сбора
        $table->head[] = $this->dof->get_string('table_report_startdate', 'reports');
        // Завершение сбора
        $table->head[] = $this->dof->get_string('table_report_enddate', 'reports');
        // Заказчик
        $table->head[] = $this->dof->get_string('table_report_person', 'reports');
        // Статус
        $table->head[] = $this->dof->get_string('table_report_status', 'reports');
        
        // Данные
        $this->data = [];
        // Счетчик
        $num = $options['limitfrom'];
        // Временная зона
        $timezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        $activestatuses = $this->dof->workflow('reports')->get_meta_list('active');
        $actionvars = $options['addvars'];
        $somevars = $actionvars;
        $somevars['plugintype'] = $plugintype;
        $somevars['plugincode'] = $plugincode;
        $somevars['code'] = $code;
        
        foreach ( $list as $report )
        {// Для каждого отчета формируем строку
            $data = [];
            
            // Номер строки
            $data[] = ++$num;
            // Действия
            $actions = '';
            
            if ( isset($activestatuses[$report->status]) AND 
                 $this->dof->$plugintype($plugincode)->is_access('view_report_'.$code, $report->id)
               )
            {// Доступ к просмотру отчета есть
                $somevars['id'] = $report->id;
                $link = $this->dof->url_im($this->code(), '/view.php', $somevars);
                $img = dof_html_writer::img(
                        $this->dof->url_im($this->code(), '/icons/view.png'), 
                        $this->dof->get_string('view_report', $this->code())
                );
                $actions .= dof_html_writer::link(
                        $link, 
                        $img, 
                        [
                            'title' => $this->dof->get_string('view_report', $this->code()),
                            'target' => '_blank'
                        ]
                );
            }
            
            if ( isset($realstatuses[$report->status]) AND
                 $this->dof->$plugintype($plugincode)->is_access('delete_report_'.$code, $report->id)
            )
            {// Доступ к удалению отчета есть
                $actionvars['id'] = $report->id;
                $link = $this->dof->url_im($this->code(), '/delete.php', $actionvars);
                $img = dof_html_writer::img(
                        $this->dof->url_im($this->code(), '/icons/delete.png'),
                        $this->dof->get_string('delete_report', $this->code())
                );
                $actions .= dof_html_writer::link(
                        $link,
                        $img,
                        [
                            'title' => $this->dof->get_string('delete_report', $this->code()), 
                            'target' => '_blank'
                        ]
                );
            }
            
            if ( isset($activestatuses[$report->status]) AND
                    $options['csvexport'] AND
                    $this->dof->$plugintype($plugincode)->is_access('export_report_'.$code, $report->id)
            )
            {// Доступ к экспорту отчета в CSV есть
                $somevars['id'] = $report->id;
                $somevars['export'] = 'csv';
                $link = $this->dof->url_im($this->code(), '/export.php', $somevars);
                $icon = $this->dof->modlib('ig')->icon('arrow_up.png');
                $img = dof_html_writer::img(
                    $this->dof->url_im($this->code(), $icon),
                    $this->dof->get_string('export_report_csv', $this->code())
                );
                
                $actions .= $this->dof->modlib('ig')->icon(
                        'arrow_up', 
                        $link, 
                        [
                                    'title' => $this->dof->get_string('export_report_csv', $this->code()),
                                    'target' => '_blank'
                        ]
                );
            }
            
            $data[] = $actions;
            // Имя отчета
            $data[] = $report->name;
            // Дата заказа
            if ( empty($report->requestdate) )
            {// Не установлена
                $data[] = $this->dof->get_string('table_report_no_requestdate', 'reports');
            } else
            {// Установлена
                $data[] = dof_userdate($report->requestdate, '%d.%m.%y %H-%M', $timezone);
            }
            // Начало сбора
            if ( empty($report->crondate) )
            {// Не установлена
                $data[] = $this->dof->get_string('table_report_no_crondate', 'reports');
            } else
            {// Установлена
                $data[] = dof_userdate($report->crondate, '%d.%m.%y %H-%M', $timezone);
            }
            // Завершение сбора
            if ( empty($report->completedate) )
            {// Не установлена
                $data[] = $this->dof->get_string('table_report_no_completedate', 'reports');
            } else
            {// Установлена
                $data[] = dof_userdate($report->completedate, '%d.%m.%y %H-%M', $timezone);
            }
            // Заказчик
            $data[] = $this->dof->storage('persons')->get_fullname($report->personid);
            // Статус
            $data[] = $this->dof->workflow('reports')->get_name($report->status);
            
            $table->data[] = $data;
        }
        
        // Возврат таблицы
        return dof_html_writer::table($table);
    }
}

