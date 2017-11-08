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


/** Категории оборудования
 * 
 */
class dof_storage_invcategories extends dof_storage
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
        return 'invcategories';
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
        return array(array('plugintype'=>'storage', 'plugincode'=>'invcategories', 'eventcode'=>'insert'),
                     array('plugintype'=>'storage', 'plugincode'=>'invcategories', 'eventcode'=>'update'),
                     array('plugintype'=>'storage', 'plugincode'=>'invcategories', 'eventcode'=>'delete'),
                     );
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
    public function is_access($do, $objid = NULL, $userid = NULL, $depid = NULL)
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
    public function require_access($do, $objid = NULL, $userid = NULL, $depid = NULL)
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
        if ( $gentype === 'storage' AND $gencode === 'invcategories' )
        {//обрабатываем события от своего собственного справочника
            switch($eventcode)
            {
                case 'insert':
                case 'update': 
                    $obj = new stdClass();
                    $obj->path = $this->get_path_for_category($mixedvar['new']->id); 
                    $obj->depth = $this->get_depth_for_category($mixedvar['new']->id);
                    $this->update($obj, $mixedvar['new']->id, true);
                    if ( isset($mixedvar['old']->parentid) AND 
                         $mixedvar['old']->parentid != $mixedvar['new']->parentid )
                    {// подразделение сменило родителя - обновим деточек
                        $this->update_depth_path($mixedvar['old']->path);
                    }
                    if( isset($mixedvar['new']->status) AND $mixedvar['new']->status == 'deleted')
                    {// Если удаляем подразделение
                        $this->change_subcategory($mixedvar['new']->id, $mixedvar['new']->parentid);
                    }
                break;
                case 'delete':
                    
                    $this->change_subcategory($mixedvar['old']->id, $mixedvar['old']->parentidd);
                break;
            }
        }
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
        return 'block_dof_s_invcategories';
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
    protected function get_access_parametrs($action, $objectid, $personid, $depid)
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
        $a['delete'] = array('roles'=>array('manager'));
        return $a;
    }
    


    // **********************************************
    //              Собственные методы
    // **********************************************
 
    /** Возвращает путь категории оборудования
     * @param int $id - id kатегории, к которому находим путь
     * @param string $chpath - путь, который уже есть у категории
     * @return int - глубина категории
     * @access public
     */
    public function get_path_for_category($id,$chpath='')
    {
        // составим конец пути
        $chpath = $id.$chpath;
        // получим родительское подразделение
        $parentid = $this->get_field($id,'parentid');
    	if ( $parentid == 0  )
    	{// это родитель - вернем путь
    	    return $chpath;
    	}
    	return $this->get_path_for_category($parentid, '/'.$chpath);
    }
    
    /** Возвращает глубину вложенности категории
     * @param int $id - id подразделения, к которому находим глубину
     * @param int $depth - глубиеа вхождения 
     * @return int - глубина категории
     * @access public
     */
    public function get_depth_for_category($id,$depth=0)
    {
        // получим родительское подразделение
        $parentid = $this->get_field($id,'parentid');
    	if ( $parentid == 0 )
    	{// это родитель - вернем путь
    	    return $depth;
    	}
    	// продолжаем искать родителя
    	return $this->get_depth_for_category($parentid, $depth + 1);
    }    
    
    /** Обновляет путь и глубину указанной категории
     * и всех, кто ниже
     * 
     * @param int $path - путь по которому обновляем
     * @return bool - true, если всё правильно, false, если возникли ошибки
     */
    public function update_depth_path($path)
    {
        $num = 0;
        $flag = true;
        while( $list = $this->get_records_select(" path LIKE '".$path."/%'
                  AND ( status <> 'deleted' )",null, '', '*', $num, 100) )
        {// Учитывая возможности сервака, будем брать записи из справочника по частям
            foreach($list as $record)
            {// запустим обновление самих себя
                $flag = ( $flag AND $this->update($record) );
            }
            $num += 100;
        }
        return $flag;
    }    
    
    /** Меняет родителя всем категориям
     * с указанным родительской категории
     * 
     * @param int $oldid - id старой категории
     * @param int $newid - id новой категории
     * @return bool - true, если всё правильно, false, если возникли ошибки
     */
    public function change_subcategory($oldid, $newid)
    {
        if( ( ! $list = $this->get_records(array('parentid'=>$oldid))) )
        {
            return false;
        }
        $flag = true;
        foreach($list as $record)
        {
            $obj = new stdClass();
            $obj->id = $record->id;
            $obj->parentid = $newid;
            // Обновляем объект, посылая при этом событие, чтобы обновить глубину и путь
            $flag = ( $flag AND $this->update($obj) );
        }
        return $flag;
    }    
    
    
    /** Выводит список всех дочерних категорий указанной категории
     * @param int $id - id категории, к которой хотим получить список подчиненных
     * @param int $depth - глубина, для которой выводим категории и их дочек
     * @param string $path - путь
     * @param $select - вывод в виде селект поля
     * @param $space
     * @param bool $code - выводит только код(без названия-используеться для блока слева подразделении)
     * $param string $right - проверка на права категорий(чаще всего на use)
     * @return array - список дочерних подразделений
     */
    public function category_list_subordinated($id = null, $depth = '0', $path = null, $select = false, $space = '', $depid=0, $right='' )
    {
        // получим список всех дочерних подразделений
        if ( ! empty($id) )
        {// передан id - переписываем путь и глубину по нему
            $path = $this->get_field($id,'path');
            // для поиска дочерних глубину родителя увеличим на + 1
            $depth = $this->get_field($id,'depth') + 1;
        }
        // формируем sql-запрос
        $sql = " status <> 'deleted' ";
        if ( ! is_null($depth) )
        {// указана глубина - добавим ее к поиску
            $sql .= " AND depth=".$depth;
        }
        if ( ! is_null($path) )
        {// указан путь - добавим его к поиску
            if ( is_null($depth) )
            {// глубины нет - ищем жесткий путь
                $sql .= " AND path ='".$path."'";
            }else
            {// если нет - ищем родителя с дочками
                $sql .= " AND (path ='".$path."' OR path LIKE '".$path."/%')";
            } 
        }
        // категория
        if ( $depid )
        {
           $sql .= " AND departmentid=".$depid; 
        }
        $categories = array();
    	// перебираем массив и добавляем нужные нам значения    	
    	if ( $list = $this->get_records_select($sql) )
    	{// если не пуст
    	    asort($list);
            foreach ($list as $data)
            {// сформируем из них массив
                if ( $select )
                {// для select-списков - одномерный';
                    $categories[$data->id]=$space.$data->name.' ['.$data->code.']';
                    $categories += $this->category_list_subordinated(null, $depth + 1 , $data->path, $select, '&nbsp;&nbsp;'.$space);

                }else
                {// структуированный массив';
                    $data->category = $this->category_list_subordinated($data->id, null, null, $select);
                    $categories[$data->id] = $data;
    	        }
            }
        
    	}
    	if ( $right )
    	{// проверка на права
    	    foreach ( $categories as $id=>$obj )
    	    {
    	        if ( ! $this->is_access($right,$id,NULL,$depid) )
    	        {// нет права - удалим
    	            unset($categories[$id]);
    	        }
    	    }
    	}
    	return $categories;
    	
    }  

} 
?>