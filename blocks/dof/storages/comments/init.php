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

require_once $DOF->plugin_path('storage','config','/config_default.php');

/**
 * Справочник комментариев к объектам Деканата
 * 
 * @package    storage
 * @subpackage comments
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_storage_comments extends dof_storage implements dof_storage_config_interface
{
    /**
     * Объект деканата для доступа к общим методам
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
     *
     * @return boolean
     */
    public function install()
    {
        if ( ! parent::install() )
        {
            return false;
        }
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
        
    /** 
     * Метод, реализующий обновление плагина в системе.
     * Создает или модифицирует существующие таблицы в БД
     * 
     * @param string $old_version - Версия установленного в системе плагина
     * 
     * @return boolean
     */
    public function upgrade($oldversion)
    {
        global $DB;
        $result = true;
        
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        
        if ( $oldversion < 2015060100 || ! $dbman->field_exists($table, 'code') )
        {// Добавление поля сабкода
            $field = new xmldb_field('code', XMLDB_TYPE_CHAR, '255', NULL,
                false, NULL, NULL, 'plugincode');
            if ( ! $dbman->field_exists($table, $field) )
            {// Поле не установлено
                // Добавление нового поля
                $dbman->add_field($table, $field);
            }
        
            $index = new xmldb_index('icode', XMLDB_INDEX_NOTUNIQUE,
                ['code']);
            if ( ! $dbman->index_exists($table, $index) )
            {// Индекс не установлен
                $dbman->add_index($table, $index);
            }
        }
        // Обновление полномочий, если они изменились
        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
		return 2016031700;
    }
    
    /** 
     * Возвращает версии интерфейса Деканата, с которыми этот плагин может работать
     * 
     * @return string
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /**
     * Возвращает версии стандарта плагина этого типа, которым этот плагин соответствует
     * 
     * @return string
     */
    public function compat()
    {
        return 'paradusefish';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'storage';
    }
    
    /** 
     * Возвращает короткое имя плагина
     * 
     * Оно должно быть уникально среди плагинов этого типа
     * 
     * @return string
     */
    public function code()
    {
        return 'comments';
    }
    
    /** 
     * Возвращает список плагинов, без которых этот плагин работать не может
     * 
     * @return array
     */
    public function need_plugins()
    {
		return array( 
		        'storage' => array(
		                'config'  => 2011080900,
		                'acl'     => 2011041800,
		                'persons' => 2015012000 
		        )
		);
    }
    
    /** 
     * Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin 
     * 
     * @see dof_modlib_base_plugin::is_setup_possible()
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * 
     * @return bool 
     *              true - если плагин можно устанавливать
     *              false - если плагин устанавливать нельзя
     */
    public function is_setup_possible($oldversion = 0)
    {
        return dof_is_plugin_setup_possible($this, $oldversion);
    }
    
    /** 
     * Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     * 
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     *                                    
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion = 0)
    {
        return array(
                'storage' => array(
		                'config'  => 2011080900,
		                'acl'     => 2011041800,
		                'persons' => 2015012000 
		        )
            );
    }
    
    /** 
     * Список обрабатываемых плагином событий 
     * 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     */
	public function list_catch_events()
    {
        return [
                // Удаление посещаемости из системы
                ['plugintype'=>'storage', 'plugincode'=>'schpresences', 'eventcode'=>'delete']
        ];
    }
    
    /** 
     * Требуется ли запуск cron в плагине
     * 
     * @return bool
     */
    public function is_cron()
    {
        // Запуск не требуется 
        return false;
    }
    
    /** 
     * Проверяет полномочия на совершение действий
     * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     *                     по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя Moodle, полномочия которого проверяются
     * 
     * @return bool 
     *              true - можно выполнить указанное действие по 
     *                     отношению к выбранному объекту
     *              false - доступ запрещен
     */
    public function is_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
    if ( $this->dof->is_access('datamanage') OR 
             $this->dof->is_access('admin') OR 
             $this->dof->is_access('manage') 
           )
        {// Открыть доступ для менеджеров
            return true;
        }
        
        // Получаем ID персоны, с которой связан данный пользователь 
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        
        // Дополнительные проверки прав
        switch ( $do )
        {
            case 'edit/owner' :
                // Получим комментарий
                $comment = $this->get($objid);
                if ( ! empty($comment) )
                {// Комментарий есть в системе
                    if ( $personid == $comment->personid )
                    {// Владелец
                        return true;
                    }
                }
                $do = 'edit';
                break;
            case 'delete/owner' :
        
                $comment = $this->get($objid);
                if ( ! empty($comment) )
                {
                    if ( $personid == $comment->personid )
                    {
                        return true;
                    }
                }
                $do = 'delete';
                break;
            default:
                break;
        }
        
        // Формируем параметры для проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid, $depid);
        // Производим проверку
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// Право есть
            return true;
        } 
        return false;
    }
    
	/** 
	 * Требует наличия полномочия на совершение действий
	 * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     *                     по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя Moodle, полномочия которого проверяются
     * 
     * @return bool 
     *              true - можно выполнить указанное действие по 
     *                     отношению к выбранному объекту
     *              false - доступ запрещен
     */
    public function require_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        if ( ! $this->is_access($do, $objid, $userid, $depid) )
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
            if ($objid){$notice.=" id={$objid}";}
            $this->dof->print_error('nopermissions','',$notice);
        }
    }
    
    /** 
     * Обработать событие
     * 
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * 
     * @return bool - true в случае выполнения без ошибок
     */
    public function catch_event($gentype, $gencode, $eventcode, $intvar, $mixedvar)
    {
        if ( $gentype === 'storage' AND $gencode === 'schpresences' )
        {
            switch($eventcode)
            {
                // Событие удаления посещаемости
                case 'delete' :
                	// Получение комментариев
                	$comments = $this->get_comments_by_object($gentype, $gencode, $intvar);
                	if ( empty($comments) )
                	{// Комментарии не найдены
                		break;
                	}
                	
                	// Проверка наличия в системе плагина статусов комментариев
                	$workflow_exist = $this->dof->plugin_exists('workflow', 'comments');
                	foreach ( $comments as $comment )
                	{
                		if ( $workflow_exist )
                		{// Найден плагин статусов комментариев
	                		// Смена статуса комментария
	                		$this->dof->workflow('comments')->change($comment->id, 'deleted');
                		} else
                		{// Плагин не найден
                			// Удаление комментариев
                			$this->delete($comment->id);
                		}
                	}
                	
                	break;
            }
        }
        return true;
    }
    
    /**
     * Запустить обработку периодических процессов
     * 
     * @param int $loan - нагрузка (
     *              1 - только срочные, 
     *              2 - нормальный режим, 
     *              3 - ресурсоемкие операции
     *        )
     * @param int $messages - количество отображаемых сообщений (
     *              0 - не выводить,
     *              1 - статистика,
     *              2 - индикатор, 
     *              3 - детальная диагностика
     *        )
     *        
     * @return bool - true в случае выполнения без ошибок
     */
    public function cron($loan,$messages)
    {
        return true;
    }
    
    /**
     * Обработать задание, отложенное ранее в связи с его длительностью
     * 
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * 
     * @return bool - true в случае выполнения без ошибок
     */
    public function todo($code,$intvar,$mixedvar)
    {
        return true;
    }
    
    /** 
     * Конструктор
     * 
     * @param dof_control $dof - объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }
   
    /** 
     * Возвращает название таблицы без префикса (mdl_)
     * @return text
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_comments';
    }

    // **********************************************
    //       Методы для работы с полномочиями
    // **********************************************    
    
    /** 
     * Получить список параметров для фунции has_hight()
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
        
        if ( is_null($depid) )
        {// Подразделение не задано - ищем в GET/POST
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        if ( ! $objectid )
        {// Если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }
        
        return $result;
    }    

    /** 
     * Проверить права через плагин acl.
     * 
     * Функция вынесена сюда, чтобы постоянно не писать 
     * длинный вызов и не перечислять все аргументы
     * 
     * @param object $acldata - объект с данными для функции storage/acl->has_right() 
     * 
     * @return bool
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->
                    has_right(
                            $acldata->plugintype, 
                            $acldata->plugincode, 
                            $acldata->code, 
                            $acldata->personid, 
                            $acldata->departmentid, 
                            $acldata->objectid
        );
    }    
      
    /** 
     * Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        
        $a['view']   = array('roles' => array(
                'manager',
                'teacher',
                'methodist',
                'student',
                'parent'
        ));
        $a['edit']   = array('roles' => array(
                'manager'
        ));
        $a['edit/owner'] = array('roles' => array(
                'manager',
                'teacher',
                'methodist',
                'student',
                'parent'
        ));
        $a['create'] = array('roles' => array(
                'manager',
                'teacher',
                'methodist',
                'student',
                'parent'
        ));
        $a['delete'] = array('roles' => array(
                'manager'
        ));
        $a['delete/owner'] = array('roles' => array(
                'manager',
                'teacher',
                'methodist',
                'student',
                'parent'
        ));
       
        return $a;
    }

    /** 
     * Функция получения настроек для плагина 
     */
    public function config_default($code=null)
    {
        $config = array();
        return $config;
    }       
    
    // **********************************************
    //              Собственные методы
    // ********************************************** 
    
    /**
     * Сохранить комментарий
     * 
     * @param object $commentobj - Объект комментария
     *                  Обязательные поля:
     *                  ->plugintype - Тип плагина. для которого сохраняется комментарий
     *                  ->plugincode - Код плагина. для которого сохраняется комментарий
     *                  ->objectid - ID объекта плагина.
     *                  ->text - Текст сообщения.
     *                  Необязательные поля
     *                  ->id - ID комментария для обновления
     *                  ->code - Сабкод плагина. для которого сохраняется комментарий
     *                  ->personid - ID пользователя - автора комментария
     *                  
     * @param array $options - Массив дополнительных параметров
     * 
     * @return bool|int - false в случае ошибки или ID комментария в случае успеха
     */
    public function save( $commentobj = null, $options = array() )
    {
        // Проверка входных данных
        if ( empty($commentobj) || ! is_object($commentobj) )
        {// Проверка не пройдена
            return false;
        }
        
        // Создаем объект для сохранения
        $saveobj = clone $commentobj;
        // Убираем автоматически генерируемые поля
        unset($saveobj->status);
        
        if ( isset($saveobj->id) && $saveobj->id > 0 )
        {// Обновляем комментарий
        
            // Получим комментарий в БД
            $oldobject = $this->get($saveobj->id);
            if ( empty($oldobject) )
            {// Комментарий не найдена
                return false;
            }
        
            // Обновляем компетенцию
            $res = $this->update($saveobj);
            if ( empty($res) )
            {// Обновление не удалось
                return false;
            } else
            {// Обновление удалось
                return $saveobj->id;
            }
        } else
        {// Создаем комментарий
            // Убираем автоматически генерируемые поля
            unset($saveobj->id);
            
            if ( isset($saveobj->personid) && ! empty($saveobj->personid) )
            {// Пользователь установлен
                // Проверяем на существование
                $exist = $this->dof->storage('persons')->is_exists(array('id' => $saveobj->personid));
                if ( empty($exist) )
                {// Пользователь не найден
                    return false;
                }
            } else 
            {// Пользователь не установлен
                $person = $this->dof->storage('persons')->get_bu();
                $saveobj->personid = $person->id;
            }
            $saveobj->date = time();
        
            // Добавляем комментарий
            $res = $this->insert($saveobj);
            if ( empty($res) )
            {// Добавление не удалось
                return false;
            } else
            {// Добавление удалось
                return $res;
            }
        }
    }
    
    
    /**
     * Получить комментарии по объекту
     *
     * @param $ptype - Тип плагина
     * @param $pcode - Код плагина
     * @param $code - Сабкод плагина
     * @param $objectid - ID объекта
     */
    public function get_comments_by_object($ptype, $pcode, $objectid, $code = NULL)
    {
        // Сформируем параметры получения записей
        $conds = array();
        $conds['plugintype'] = $ptype;
        $conds['plugincode'] = $pcode;
        $conds['objectid'] = $objectid;
        if ( $this->dof->plugin_exists('workflow', 'comments') )
        {// Найден плагин статусов комментариев
        	$statuses = $this->dof->workflow('comments')->get_meta_list('real');
        	$statuses = array_keys($statuses);
        	$conds['status'] = $statuses;
        }
        if ( ! is_null($code) )
        {// Установлен сабкод
            $conds['code'] = $code;
        }
        
        // Получим список комментарием к объекту
        $comments = $this->get_records($conds, 'date DESC');
        
        // Вернем комментарии
        return $comments;
    }
}   
?>