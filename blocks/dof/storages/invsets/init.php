<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
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


/** Комплекты оборудования
 * 
 */
class dof_storage_invsets extends dof_storage
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
	/** Устанавливает плагин в fdo
	 * @return bool
	 */
	public function install()
	{
	    // Устанавливаем таблицы
	    if (!parent::install())
	    {
	        return false;
	    }

        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
	}
    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $CFG;
        require_once($CFG->libdir.'/ddllib.php');//методы для установки таблиц из xml
        $result = true;

        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
 
     }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
		return 2012042500;
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
        return 'paradusefish';
    }
    
    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'storage';
    }
    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'invsets';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
		return array('storage'=>array( 'acl'  => 2011040504) );
    }
    /** Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin 
     * @see dof_modlib_base_plugin::is_setup_possible()
     * 
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * 
     * @return bool 
     *              true - если плагин можно устанавливать
     *              false - если плагин устанавливать нельзя
     */
    public function is_setup_possible($oldversion=0)
    {
        return dof_is_plugin_setup_possible($this, $oldversion);
    }
    /** Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     * 
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion=0)
    {
        return array('storage'=>array('acl'=>2011040504));
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
        // Просим запускать крон не чаще раза в 15 минут
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
    public function is_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('admin') 
             OR $this->dof->is_access('manage') )
        {// манагеру можно все
            return true;
        }
        // получаем id пользователя в persons
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid, $depid);   
        // проверка
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// право есть заканчиваем обработку
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
    public function require_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        // Используем функционал из $DOFFICE
        //return $this->dof->require_access($do, NULL, $userid);
        if ( ! $this->is_access($do, $objid, $userid, $depid) )
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
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
     * @param dof_control $dof - объект с методами ядра деканата
     * @access public
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }

    /** Возвращает название таблицы без префикса (mdl_)
     * @return text
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_invsets';
    }
    

    
    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************     
    
    /** Получить список параметров для фунции has_hight()
     * 
     * @return object - список параметров для фунции has_hight()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $personid
     */
    protected function get_access_parametrs($action, $objectid, $personid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->personid     = $personid;
        $result->departmentid = $depid;
        $result->objectid     = $objectid;
        if ( $objectid )
        {// подразделение объекта
            $result->departmentid = $this->get_field($objectid, 'departmentid');
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
                              $acldata->personid, $acldata->departmentid, $acldata->objectid);
    }    
      
    /** Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        $a['view']   = array('roles'=>array('manager'));
        $a['edit']   = array('roles'=>array('manager'));
        $a['use']    = array('roles'=>array('manager'));
        $a['create'] = array('roles'=>array('manager'));
        
        return $a;
    }
    


    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** Возвращает список единиц оборудования по переданным критериям
     * 
     * @return array массив записей из базы, или false в случае ошибки
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @param object $conds[optional] - объект со списком свойств, по которым будет происходить поиск
     * @param object $countonly[optional] - только вернуть количество записей по указанным условиям
     */
    public function get_listing($conds=null, $limitfrom = null, $limitnum = null, $sort='', $fields='*', $countonly=false)
    {
        $select = 'SELECT * FROM '.$this->prefix().$this->tablename();
        if ( ! $conds )
        {// если список потоков не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        if ( ! is_null($limitnum) AND $limitnum <= 0 )
        {// количество записей на странице может быть 
            //только положительным числом
            $limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault();
        }
        if ( ! is_null($limitfrom) AND $limitfrom < 0 )
        {//отрицательные значения номера просматриваемой записи недопустимы
            $limitfrom = 0;
        }
        
        if ( $countonly )
        {// посчитаем общее количество записей, которые нужно извлечь
            return $this->count_records_select($this->get_select_listing($conds));
        }
        
        //формируем строку запроса
        if ( $query = $this->get_select_listing($conds) )
        {
            $select .= ' WHERE '.$query;
        }
        
        // Добавим сортировку
        if ( trim($sort) )
        {
            $select .= ' ORDER BY '.$sort;
        }
        
        return $this->get_records_sql($select, null, $limitfrom, $limitnum);       
    }

    /**
     * Возвращает фрагмент sql-запроса после слова WHERE
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @return string
     */
    public function get_select_listing($inputconds)
    {
        // создадим массив для фрагментов sql-запроса
        $selects = array();
        
        $conds = (array)$inputconds;
        if ( isset($conds['displaytype']) AND trim($conds['displaytype']) )
        {// для специфического запроса - специфический select
            //$selects[] = "( name LIKE '%".$conds['nameorcode']."%' OR code='".$conds['nameorcode']."')";
            switch ( $conds['displaytype'] )
            {
                case 'granted':   $selects[] = ' ( personid IS NOT NULL AND personid != 0 ) '; ; break;
                case 'available': $selects[] = ' ( personid IS NULL OR personid = 0 ) AND status = \'active\'' ; break;
                case 'all':       $selects[] = ' ( status <> \'deleted\' )';break;  
            }
            // убираем имя из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds['displaytype']);
        }else 
        {// удаленные не показываем
            $selects[] = ' status <> \'deleted\'';
        }
       
        // теперь создадим все остальные условия
        foreach ( $conds as $name=>$field )
        {
            if ( $field )
            {// если условие не пустое, то для каждого поля получим фрагмент запроса
                $selects[] = $this->query_part_select($name,$field);
            }
        } 
        //формируем запрос
        if ( empty($selects) )
        {// если условий нет - то вернем пустую строку
            return '';
        }elseif ( count($selects) == 1 )
        {// если в запросе только одно поле - вернем его
            return current($selects);
        }else
        {// у нас несколько полей - составим запрос с ними, включив их всех
            return implode($selects, ' AND ');
        }
    }
    
    /** Обработка AJAX-запросов из форм
     * @param string $querytype - тип запроса
     * @param int $objectid - id объекта с которым производятся действия
     * @param array $data - дополнительные данные пришедшие из json-запроса
     * 
     * @return array
     */
    public function widgets_field_ajax_select($querytype, $objectid, $data)
    {
        switch ( $querytype )
        {
            case 'im_inventory_delivery': return $this->widgets_newitem_form_variants($data);
            default: return array(0 => $this->dof->modlib('ig')->igs('choose'));
        }
    }
    
    /** Получить список вариантов выбора при выдаче одного комплекта
     * Выбираются комплекты определенной категории + все комплекты дочерних категорий
     * @todo оптимизировать выборку по дочерним категориям
     * @todo добавить сортировку по названию категории, к которой принадлежит комплект
     * 
     * @param object $data - данные для запроса: подразделение и родит категория
     * 
     * @return array массив для подстановки в select-список
     */
    protected function widgets_newitem_form_variants($data)
    {
        
        $categoryid = $data['parentvalue'];
        $depid = $data['departmentid'];
        if ( ! $categoryid )
        {
            return array('-1' => $this->dof->get_string('choose_category', 'invsets', null, 'storage'));
        }
        
        // Получаем список дочерних категорий
        $categories = array_keys($this->dof->storage('invcategories')->category_list_subordinated($categoryid, null,null,true,'',$depid));
        $categories[] = $categoryid;
        // пепеберем массив для запроса
        // Получаем все активные комплекты из текущей категории + изо всех дочерних и которые не задействованы
        // берем первые 20, НЕ НАДО ВСЁ грузить
        if ( ! $sets = $this->get_records(array('status'=>'active', 'invcategoryid'=>$categories),'','*','',25) )
        {// нет ни одного комплекта ни в текущей ни в дочерней категориях - вернем только нулевой элемент
            return array("-1" => $this->dof->modlib('ig')->igs('empty'));
        }
        
        $result = array(0 => $this->dof->modlib('ig')->igs('any_mr'));
        foreach ( $sets as $id=>$set )
        {// составляем название комплекта: категория + код
            $catname = $this->dof->storage('invcategories')->get_field($set->invcategoryid, 'name');
            $result[$id] = $catname.' ['.$set->code.']';
        }
        
        return $result;
    }
    
    /** Выдать комплект. Создает приказ о выдаче, подписывает и исполняет его.
     * @todo обработать вывод ошибок при помощи исключений
     * 
     * @param int $setid - id комплекта, который выдается
     * @param int $userid  - id персоны в таблице persons которой выдается комплект
     * @param string $notes[optional] - примечания, записываемые в приказ при выдаче оборудования
     * 
     * @return bool
     * 
     */
    public function set_delivery($setid, $userid, $notes='')
    {
        // формируем приказ на выдачу 1 комплекта
        // все проверки прошли
        // подключаем приказ
        require_once($this->dof->plugin_path('storage','invsets','/order/invsets_order.php'));
    
        $order = new dof_storage_invitems_order_set_delivery($this->dof);
        // формируем объект для приказа              
        $orderobj = new stdClass();
        $orderobj->date = time();
        
        //сохраняем автора приказа
        if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
        {// неудача - скажем об этом
            return false;
        }
        $orderobj->ownerid = $personid;
        
        // подразделение
        $orderobj->departmentid = $this->dof->storage('persons')->get_field($personid,'departmentid');                    
        $orderobj->notes = $notes;
        $orderobj->data = new stdClass();
        // кому и какой комплект выдан
        $orderobj->data->personid = $userid;
        $orderobj->data->setid    = $setid;
        
        // сохраним приказ
        if ( ! $order->save($orderobj) )
        {// неудача - скажем об этом
            return false;             
        }
        // подписываем
        if ( ! $order->sign($personid) ) 
        {// неудача - скажем об этом
            return false;                  
        }               
        
        // исполняем
        if ( ! $order->execute() )
        {// неудача - скажем об этом
            return false;                  
        }
        
        return true;
    }
}
?>