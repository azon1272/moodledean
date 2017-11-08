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


/** Приказы учебного заведения
 * 
 */
class dof_im_learningorders implements dof_plugin_im
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
        return 2016011400;
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
        return 'learningorders';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array();
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
    {// завуч и админ
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('manage') 
             OR $this->dof->is_access('admin') )
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
    {// Используем функционал из $DOFFICE
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "learningorders/{$do} (block/dof/im/learningorders: {$do})";
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
    /**Возвращает содержимое блока, отображаемого на страницах fdo
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string - html-код текста
     */
    function get_block($name, $id = 1)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        switch ($name)
		{//выбираем нужнуое содержание по названию
		    case 'main': 
       		 	return '<a href="'.$this->dof->url_im('learningorders','',$addvars).'">'
							.$this->dof->get_string('title', 'learningorders').'</a>';
			break;	
       		default:
       		    {//соответствия не нашлось выведем и имя и id
                    $a = new stdClass();
                    $a->name = $name;
                    $a->id = $id;
				    return $this->dof->get_string('ХХХл', 'learningorders', $a);;
       		    }
		}
    }
    /** Возвращает содержимое секции
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string  - html-код названия секции
     */
    function get_section($name, $id = 1)
    {
        return '';
    }
     /** Возвращает текст для отображения в блоке на страницах MOODLE 
     * @return string  - html-код для отображения
     */
    public function get_blocknotes($format='other')
    {
		return "<a href='{$this->dof->url_im('learningorders','/')}'>"
                    .$this->dof->get_string('title','learningorders')."</a>";
    }

    
    // **************************************************
    // Собственные методы
    // **************************************************
    
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
     * Возвращает объект приказа
     *
     * @param string $code
     * @param integer  $id
     * @return dof_storage_orders_baseorder
     */
    public function order($code, $id = NULL)
    {
        global $DOF;
        switch ($code)
        {
            case 'transfer':
                //require_once($this->dof->plugin_path('im','learningorders','/orders/transfer/init.php'));
                //$order = new dof_im_journal_order_transfer($this->dof);
                $order = $this->dof->storage('orders')->order($this->type(),$this->code(),'transfer');
                if ( ! is_null($id ))
                {// если передан id приказа - вернем его данные
                    if ( ! $order->load($id) )
                    {// не нашли
                        return false;
                    }
                }
                // Возвращаем объект приказа или его данные
                return $order;
                break;
            default:
                // Ошибка
                return false;
                break;
        }
    }

    /**
     * Возвращает id того юзера,
     * чьи полномочия проверяются
     * @param int $userid - id проверяемого юзера
     * @return int
     */
    private function store_userid($userid = null)
    {
        global $USER;
        //запоминаем проверяемого пользователя
        if ( is_null($userid) )
        {
            if ( isset($USER->id) )
            {
                $userid = $USER->id;
            }else
            {
                $userid = 0;
            }
        }
        return intval($userid);
    }


    /** Проверить права через старую систему полномочий (пока оставлено для совместимости)
     * 
     * @todo избавится от этой функции после полного перехода на новую систему полномочий
     * @deprecated Эта функция существует здесь для совместимости со старой системой прав
     * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя в Moodle, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     */    
    protected function old_access_check($do, $objid = NULL, $userid = NULL)
    {
        $userid = $this->store_userid($userid);
        switch ($do)
        {
            case 'order':
            return ( $this->dof->is_access('datamanage', null, $userid) OR
                     $this->dof->is_access('manage', null, $userid) );break;
            default: return false;
        }
    }   

    /** Проверить права через систему полномочий acl
     * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
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
            case 'order':$acldata->code = 'view';break;
            // запрошено неизвестное полномочие
            default: $acldata->code = $do;                               
        }
        // проверка
        return $this->acl_check_access_paramenrs($acldata);
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
        $result->plugintype   = 'storage';
        $result->plugincode   = 'orders';
        $result->code         = $action;
        $result->userid       = $userid;
        $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        $result->objectid     = $objectid;
        if ( ! $objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }else
        {// если указан - то установим подразделение
            $result->departmentid = $this->dof->storage('orders')->get_field($objectid, 'departmentid');
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
}