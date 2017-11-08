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

require_once $DOF->plugin_path('storage', 'config', '/config_default.php');

/**
 * Справочник дерева компетенций
 * 
 * @package    storage
 * @subpackage skills
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_storage_skills extends dof_storage implements dof_storage_config_interface
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
        global $CFG;
        $result = true;
        // Методы для установки таблиц из xml
        require_once($CFG->libdir.'/ddllib.php');
        
        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
		return 2015060200;
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
        return 'skills';
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
		                'config' => 2011080900,
		                'acl'    => 2011041800
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
                   'acl'    => 2011040504,
        	       'config' => 2011080900
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
        // Пока событий не обрабатываем
        return array();
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
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        // Ничего не делаем, но отчитаемся об "успехе"
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
        return 'block_dof_s_skills';
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
     * Базовые права плагина, в интерфейсах 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        
        $a['view']   = array('roles' => array(
                'manager'
        ));
        $a['edit']   = array('roles' => array(
                'manager'
        ));
        $a['create'] = array('roles' => array(
                'manager'
        ));
        $a['delete'] = array('roles' => array(
                'manager'
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
     * Сохранить компетенцию в дереве
     * 
     * @param object $skillobj - Объект компетенции для добавления
     *                  ->id (для обновления)
     *                  ->name - Имя
     *                  ->complexity - Сложность
     *                  ->parentid - Родитель
     * @param array $options - Массив дополнительных параметров
     * 
     * @return bool|int - false в случае ошибки или ID компетенции в случае успеха
     */
    public function save( $skillobj = null, $options = array() )
    {
        // Проверка входных данных
        if ( empty($skillobj) || ! is_object($skillobj) )
        {// Проверка не пройдена
            return false;
        }
        
        // Проверка существования родителя
        $exist = $this->parent_exists($skillobj);
        if ( empty($exist) )
        {// Родитель не найден
            return false;
        }
        
        // Создаем объект для сохранения
        $saveobj = clone $skillobj;
        // Убираем автоматически генерируемые поля
        unset($saveobj->status);
        unset($saveobj->path);
        
        if ( isset($saveobj->id) && $saveobj->id > 0 )
        {// Обновляем компетенцию
            
            // Получим компетенцию в БД
            $oldskill = $this->get($saveobj->id);
            if ( empty($oldskill) )
            {// Компетенция не найдена
                return false;
            }
            
            // Обновляем компетенцию
            $res = $this->update($saveobj);
            if ( empty($res) )
            {// Обновление не удалось
                return false;
            } else
            {// Обновление удалось
                // Формируем массив опций для пересчета пути
                if ( isset($saveobj->parentid) && $saveobj->parentid != $oldskill->parentid )
                {// Изменяется родительская компетенция
                    $pathopts = array();
                } else 
                {
                    $pathopts = array('cascade' => false);
                }
                
                // Сгенерируем путь для компетенции
                if ( ! $this->update_path($saveobj->id, $pathopts) )
                {// Генерация не удалась
                    return false;
                }

                return $saveobj->id;
            }
        } else 
        {// Создаем компетенцию
            // Убираем автоматически генерируемые поля
            unset($saveobj->id);
            
            // Добавляем компетенцию
            $res = $this->insert($saveobj);
            if ( empty($res) )
            {// Добавление не удалось
                return false;
            } else
            {// Добавление удалось
                // Сгенерируем путь для компетенции
                if ( ! $this->update_path($res) )
                {// Генерация не удалась
                    return false;
                }
                return $res;
            }
        }
    }
    
    /**
     * Проверить существование родителя
     *
     * @param int|obj $skill - ID или объект компетенции
     * @param array $options - Дополнительные опции
     * @return boolean|NULL - true, если родитель существует
     *                        false, если его нет
     *                        NULL при ошибках 
     */
    public function parent_exists($skill, $options = array() )
    {
        if ( is_object($skill) )
        {// Передан объект компетенции
            if ( ! isset($skill->parentid) )
            {// В переданном объекте не указан родитель
                return NULL;
            }
        } else 
        {// Передан ID компетенции
            // Получим объект компетенции
            $skill = $this->get($skill);
            if ( empty($skill) )
            {// Не найдена
                return NULL;
            }
        }
        
        if ( $skill->parentid > 0 )
        {// Найдем компетенцию
            if ( $this->is_exists(array('id' => $skill->parentid)) )
            {// Родительская компетенция найдена
                return true;
            } else
            {// Родительская компетенция не найдена
                return false;
            }
        } else
        {// Компетенция в корне дерева
            return true;
        }
    }
    
    /**
     * Пересчитать путь у компетенции 
     *
     * @param int $skillid - ID компетенции
     * @param array $options - Дополнительные опции
     * @return string|bool - Путь, если обновление записи прошло успешно
     *                       false в случае ошибки
     */
    public function update_path($skillid, $options = array() )
    {
        // Сгенерируем незаполненные опции
        if ( ! isset($options['cascade']) )
        {// Установим каскадное обновление
            $options['cascade'] = true;
        }

        // Получим компетенцию
        $skill = $this->get($skillid);
        if ( empty($skill) )
        {// Компетенция не найдена
            return false;
        }

        // Готовим массив пути 
        $patharray = array($skill->id);
        // ID родителя
        $parentid = $skill->parentid;
        // Число циклов
        $lap = 0;
        while ( $parentid > 0 )
        {
            if ( ++$lap > 100 )
            {// Зацикливание
                return false;
            }
            $parent = $this->get($parentid);
            $parentid = $parent->parentid;
            if ( empty($parent) )
            {// Родитель не найден
                return false;
            }
            // Добавим к массиву пути
            $patharray[] = $parent->id;
        }
        // Получим путь
        $reverse = array_reverse($patharray);
        $path = implode('/', $reverse);
        // Сформируем объект для обновления
        $update = new stdClass;
        $update->path = $path;
        // Обновим
        $result = $this->update($update, $skillid);
        if ( empty($result) )
        {// Обновление не удалось
            return false;
        } else 
        {// Обновление прошло успешно
            // Проверим по опциям необходимость каскадного обновления
            if ( $options['cascade'] === true )
            {// Каскадyное обновление путей
                // Получим дочерние компетенции
                $skills = $this->get_records(array('parentid' => $skillid));
                if ( ! empty($skills) )
                {// Обновим пути у всех дочерних компетенций
                    $result = true;
                    foreach ( $skills as $skill )
                    {
                        $result = ( $result && $this->update_path($skill->id) );
                    }
                    if ( empty($result) )
                    {// Каскадное обновление завершилось с ошибкой
                        return false;
                    }
                }
            }
            
            return $path;
        }
    }
    

    /**
     * Получить всех потомков компетенции
     *
     * @param int $skillid - ID компетенции
     * @param array $options - Дополнительные опции
     * 
     * @return array - Массив потомков
     */
    public function get_children($skillid, $options = array() )
    {
        // Получим компетенцию
        $skill = $this->get($skillid);
        if ( empty($skill) )
        {// Компетенция не найдена
            return array();
        }
        
        $select = "path LIKE '$skill->path/%'";
        $skills = $this->get_records_select($select);
        
        return $skills;
    }
}   
?>