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
 * Справочник связи компетенций с объектами
 * 
 * @package    storage
 * @subpackage skilllinks
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_storage_skilllinks extends dof_storage implements dof_storage_config_interface
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
        return 'skilllinks';
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
		                'acl'    => 2011041800,
		                'skills' => 2015060000
		        ),
		        'workflow' => array(
		                'skills' => 2015060000
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
        	       'config' => 2011080900,
                   'skills' => 2015060000
                ), 
                'workflow' => array(
                        'skills' => 2015060000
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
        return array(
                array('plugintype' => 'storage',  'plugincode' => 'skills', 'eventcode' => 'delete'),
                array('plugintype' => 'workflow', 'plugincode' => 'skills', 'eventcode' => 'junk')
        );
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
        // Ловим событие
        if ( $gentype === 'storage' AND $gencode === 'skills' )
        {
            switch($eventcode)
            {
                // Удалилась компетенция - удалим все ее связи
                case 'delete' :
                    // Удалим все связи
                    return $this->delete_by_skill($intvar);
                default:
                    break;
            }
        }
        if ( $gentype === 'workflow' AND $gencode === 'skills' )
        {
            switch($eventcode)
            {
                // Удалилась компетенция - удалим все ее связи
                case 'junk' :
                    // Удалим все связи
                    return $this->delete_by_skill($intvar);
                default:
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
        return 'block_dof_s_skilllinks';
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
     * Обновить связь компетенции
     *
     * @param $skillid - ID компетенции
     * @param $ptype - Тип плагина
     * @param $pcode - Код плагина
     * @param $code - Сабкод плагина
     * @param $id - ID объекта для которого обновляется связь
     *
     * @return bool
     */
    public function save($skillid, $ptype, $pcode, $code, $objectid)
    {
        // Получим компетенцию
        $skill = $this->dof->storage('skills')->get($skillid);

        if ( empty($skill) )
        {// Комппетенция не найдена
            return false;
        } 
            
        // Получим массив дочерних компетенций
        $statuses = $this->dof->workflow('skills')->get_meta_list('real');
        $statuses = array_keys($statuses);
        $children = $this->dof->storage('skills')->get_records(array('parentid' => $skillid, 'status' => $statuses));
        if ( ! empty($children) )
        {// Есть дочерние - добавим связь
            foreach ($children as $skill )
            {// Обновим компетенции
                $this->save($skill->id, $ptype, $pcode, $code, $objectid);
            }
        }
        
        // Формируем массив условий
        $cond = array();
        $cond['skillid'] = $skillid;
        $cond['plugintype'] = $ptype;
        $cond['plugincode'] = $pcode;
        $cond['code'] = $code;
        $cond['objectid'] = $objectid;
        // Проверка на существование
        $link = $this->get_record($cond);
        
        // Создаем объект для сохранения
        $saveobj = new stdClass();
        $saveobj->date = time();
        $saveobj->grade = $skill->complexity;
        if ( empty($link) )
        {// Добавляем связь
            $saveobj->skillid = $skillid;
            $saveobj->plugintype = $ptype;
            $saveobj->plugincode = $pcode;
            $saveobj->code = $code;
            $saveobj->objectid = $objectid;
            $res = $this->insert($saveobj);
        } else
        {// Обновляем связь
            $saveobj->id = $link->id;
            $res = $this->update($saveobj);
        }
        
        if ( empty($res) )
        {// Обновление не удалось
            return false;
        } else
        {// Обновление удалось
            return true;
        }
    }
    
    /**
     * Удалить связь компетенции
     *
     * @param $skillid - ID компетенции
     * @param $ptype - Тип плагина
     * @param $pcode - Код плагина
     * @param $code - Сабкод плагина
     * @param $id - ID объекта для которого удаляем связь
     *
     * @return bool
     */
    public function remove($skillid, $ptype, $pcode, $code, $objectid)
    {
        // Формируем массив условий
        $cond = array();
        $cond['skillid'] = $skillid;
        $cond['plugintype'] = $ptype;
        $cond['plugincode'] = $pcode;
        $cond['code'] = $code;
        $cond['objectid'] = $objectid;
        // Проверка на существование
        $links = $this->get_records($cond);
    
        if ( empty($links) )
        {// Связей нет
            return true;
        }
        
        $return = true;
        foreach( $links as $item )
        {// Удалим все найденные связи
            $return = ( $return && $this->delete($item->id) );
        }
        
        return $return;
    }
    
    /**
     * Удалить все связи компетенции
     * 
     * @param int $skillid - ID компетенции
     * @return bool - true
     */
    public function delete_by_skill($skillid)
    {
        // Проверим на существование компетенции
        $exist = $this->dof->storage('skills')->is_exists($skillid);
        
        if ( $exist )
        {// Компетенция существует
            // Получим связи
            $links = $this->get_records(array('skillid' => $skillid));
            if ( ! empty($links) )
            {// Ссылки есть - удалим
                foreach( $links as $link )
                {
                    $this->delete($link->id);
                }
            }
        } 
        return true;
    }
    
    /** 
     * Получить путь для получения целевых компетенций
     * 
     * @param array $savail - Массив исходных компетенций
     * @param array $sdest - Массив целевых компетенций
     * 
     */
    public function skillpath($savail,$sdest)
    {
        require_once $this->dof->plugin_path('storage', 'skilllinks', '/skillpath.php');
        
        $skillpath = new dof_storage_skilllinks_skillpath($this->dof, $savail, $sdest);
        
        $result = $skillpath->searchSPath();
        if ( $result === true )
        {
            return $skillpath->get_path();
        } else 
        {
            return false;
        }
    }
}   
?>