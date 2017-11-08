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
 * Справочник Задач
 * 
 */
class dof_storage_tasks extends dof_storage implements dof_storage_config_interface
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************

    public function install()
    {
        if ( ! parent::install() )
        {
            return false;
        }
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
        
    /** 
     * Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $CFG;
        $result = true;
        require_once($CFG->libdir.'/ddllib.php');//методы для установки таблиц из xml
        
        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    /** 
     * Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2014120100;
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
        return 'paradusefish';
    }
    
    /** 
     * Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'storage';
    }
    /** 
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'tasks';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('config' => 2011080900,
                                      'acl'    => 2011041800 )
        );
    }
    /** 
     * Определить, возможна ли установка плагина в текущий момент
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
    /** 
     * Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     * 
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion=0)
    {
        return array('storage'=>array('acl'     => 2011040504,
                                      'persons' => 2014040000,
                                      'config'  => 2011080900 )
        );
    }
    /** 
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        // Пока событий не обрабатываем
        return array();
    }
    /** 
     * Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
        // Просим запускать крон не чаще раза в 15 минут
        return false;
    }
    
    /** 
     * Проверяет полномочия на совершение действий
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
        // Проверяем, является ли пользователь админом
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('admin')
                OR $this->dof->is_access('manage') )
        {// администратору можно все
            return true;
        }
        
        // Выполняем действия в зависимости от задачи
        switch ($do) 
        {
            case 'view/owner' : // Просмотр своих задач
            
                if ( empty($objid) )
                {// ID объекта не передан, значит проверять нечего - вернем false
                    dof_debugging(
                        $this->dof->get_string('error_no_taskid', 'tasks', null, 'storage'), 
                        DEBUG_DEVELOPER
                    );
                    return false;
                }
                if ( empty($userid) )
                {// Персона не передана - получаем ее
                    $user = $this->dof->storage('persons')->get_bu();
                    $userid = $user->id;
                } 
                // Получаем ID задачи
                $task = $this->get($objid);
                if ( empty($task) )
                {// Такой задачи нет
                    dof_debugging(
                    $this->dof->get_string('error_no_task', 'tasks', null, 'storage'),
                    DEBUG_DEVELOPER
                    );
                    return false;
                }
                
                if ( $task->purchaserpersonid == $userid || 
                     $task->assignedpersonid == 0 || 
                     $task->assignedpersonid == $userid )
                {
                    return true;
                }
                
                // Делаем регресс к проверке общего права
                $do = 'view';
                break;
                
            case 'edit/owner' : // Редактирование своих задач

                if ( empty($objid) )
                {// ID объекта не передан, значит проверять нечего - вернем false
                    dof_debugging(
                    $this->dof->get_string('error_no_taskid', 'tasks', null, 'storage'),
                    DEBUG_DEVELOPER
                    );
                    return false;
                }
                if ( empty($userid) )
                {// Персона не передана - получаем ее
                    $user = $this->dof->storage('persons')->get_bu();
                    $userid = $user->id;
                }
                // Получаем ID задачи
                $task = $this->get($objid);
                if ( empty($task) )
                {// Такой задачи нет
                    dof_debugging(
                    $this->dof->get_string('error_no_task', 'tasks', null, 'storage'),
                    DEBUG_DEVELOPER
                    );
                    return false;
                }
                
                if ( $task->purchaserpersonid == $userid )
                {
                    return true;
                }
            
                // Делаем регресс к проверке общего права
                $do = 'edit';
                break;

            default :
                break; 
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
    
    /** 
     * Требует наличия полномочия на совершение действий
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
        if ( ! $this->is_access($do, $objid, $userid, $depid) )
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
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
        // Ничего не делаем, но отчитаемся об "успехе"
        return true;
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
     * @param dof_control $dof - объект с методами ядра деканата
     * @access public
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
        return 'block_dof_s_tasks';
    }

    // **********************************************
    //       Методы для работы с полномочиями
    // **********************************************    
    
    /** 
     * 
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
        if ( is_null($depid) )
        {// подразделение не задано - берем текущее
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        $result->objectid     = $objectid;
        if ( ! $objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }
        
        return $result;
    }    

    /** 
     * Проверить права через плагин acl.
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
      
    /** 
     * Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        
        $a['view']       = array('roles'=>array('manager'));
        $a['view/owner'] = array('roles'=>array());
        $a['edit']       = array('roles'=>array('manager'));
        $a['edit/owner'] = array('roles'=>array());
        $a['create']     = array('roles'=>array('manager','teacher','methodist'));
        $a['delete']     = array('roles'=>array('manager'));

        return $a;
    }

    /** 
     * Функция получения настроек для плагина
     *  
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
     * Добавить новую задачу в Справочник
     * 
     * @param object $insert - объект задачи
     * Поля:
     * ->parentid
     * ->assignedpersonid - кому поручена
     * ->purchaserpersonid - кем поручена задача
     * ->title - заголовок задачи
     * ->about - описание задачи
     * ->solution - решение задачи
     * ->actualdate - дата актуализации
     * ->deadlinedate - дата дедлайна
     * ->date - дата создания
     * 
     * @return bool - true, если задача успешно добавилась 
     *              - false, если произошла ошибка
     */
    public function add_task($insert)
    {
        // Проверка входных данных
        if ( ! is_object($insert) )
        { // Передан не объект
            dof_debugging($this->dof->
                get_string('error_incorrect_param_insert_not_object', 'tasks', null, 'storage'), 
                DEBUG_DEVELOPER
            );
            return false;
        }
        
        // Кому поручена задача
        if ( ! isset($insert->assignedpersonid) )
        {
            $insert->assignedpersonid = 0;
        } else 
        {
            if ( empty($insert->assignedpersonid) )
            {
                $insert->assignedpersonid = 0;
            }
        }
        
        // Кем поручена задача
        if ( ! isset($insert->purchaserpersonid) )
        { 
            $insert->purchaserpersonid = 0;
        } else
        {
            if ( empty($insert->purchaserpersonid) )
            {
                $insert->purchaserpersonid = 0;
            }
        }
        
        // Заголовок
        if ( ! isset($insert->title) )
        {
            $insert->title = $this->dof->get_string('default_task_title', 'tasks', null, 'storage');
        } else
        {
            if ( empty($insert->title) )
            {
                $insert->title = '';
            }
        }
        
        // Описание
        if ( ! isset($insert->about) )
        {
            $insert->about = $this->dof->get_string('default_task_about', 'tasks', null, 'storage');
        } else
        {
            if ( empty($insert->about) )
            {
                $insert->about = '';
            }
        }
        
        // Решение задачи
        if ( ! isset($insert->solution) )
        {
            $insert->solution = '';
        } else
        {
            if ( empty($insert->solution) )
            {
                $insert->solution = '';
            }
        }
        
        // Родительская задача
        if ( ! isset($insert->parentid) )
        {
            $insert->parentid = 0;
        } else
        {
            if ( empty($insert->parentid) )
            {
                $insert->parentid = 0;
            }
        }
        
        // Дата актуализации
        if ( ! isset($insert->actualdate) )
        {
            $insert->actualdate = null;
        } else
        {
            if ( empty($insert->actualdate) )
            {
                $insert->actualdate = null;
            }
        }
        
        // Дедлайн
        if ( ! isset($insert->deadlinedate) )
        {
            $insert->deadlinedate = null;
        } else
        {
            if ( empty($insert->deadlinedate) )
            {
                $insert->deadlinedate = null;
            }
        }
        
        // Дата создания задачи
        if ( ! isset($insert->date) )
        {
            $insert->date = intval(date('U'));
        } else
        {
            if ( empty($insert->date) )
            {
                $insert->date = intval(date('U'));
            }
        }
        
        // Пытаемся добавить задачу
        if ( $this->insert($insert) )
        {
            return true;
        } else 
        {
            dof_debugging($this->dof->
                get_string('error_insert_task', 'tasks', null, 'storage'),
                DEBUG_DEVELOPER
            );
            return false;
        }
    }
    
    /**
     * Обновить задачу
     * 
     * @param object $update
     * Поля:
     * ->title - заголовок задачи
     * ->about - описание задачи
     * ->actualdate - дата актуализации
     * ->deadlinedate - дата дедлайна
     * 
     * @return boolean
     */
    public function update_task($update)
    {
        // Проверка входных данных
        if ( ! is_object($update) )
        { // Передан не объект
            dof_debugging($this->dof->
            get_string('error_incorrect_param_insert_not_object', 'tasks', null, 'storage'),
            DEBUG_DEVELOPER
            );
            return false;
        }
        
        // Заголовок
        if ( ! isset($update->title) )
        {
            $update->title = $this->dof->get_string('default_task_title', 'tasks', null, 'storage');
        } else
        {
            if ( empty($update->title) )
            {
                $update->title = '';
            }
        }
        
        // Описание
        if ( ! isset($update->about) )
        {
            $update->about = $this->dof->get_string('default_task_about', 'tasks', null, 'storage');
        } else
        {
            if ( empty($update->about) )
            {
                $update->about = '';
            }
        }
        
        // Дата актуализации
        if ( ! isset($update->actualdate) )
        {
            $update->actualdate = null;
        } else
        {
            if ( empty($update->actualdate) )
            {
                $update->actualdate = null;
            }
        }
        
        // Дедлайн
        if ( ! isset($update->deadlinedate) )
        {
            $update->deadlinedate = null;
        } else
        {
            if ( empty($update->deadlinedate) )
            {
                $update->deadlinedate = null;
            }
        }
        
        // Пытаемся добавить задачу
        if ( $this->update($update) )
        {
            return true;
        } else
        {
            dof_debugging($this->dof->
            get_string('error_update_task', 'tasks', null, 'storage'),
            DEBUG_DEVELOPER
            );
            return false;
        }
    }
}  
?>