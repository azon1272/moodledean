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


/**
 * Синхронизация компетенций. 
 *
 * @package    sunk
 * @subpackage skills
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_sync_skills implements dof_sync
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
        return true;
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
        return true;
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
        return 'ancistrus';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'sync';
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
                'im' => array(
                        'skills' => 2015060000
                ),
                'sync' => array(
                        'personstom' => 2015012100
                ),
                'storage' => array(
                        'skills'      => 2015060000,
                        'skilllinks'  => 2015060000,
                ),
                'workflow' => array(
                        'skills'      => 2015060000
                )
        );
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
                'im' => array(
                        'skills' => 2015060000
                ),
                'sync' => array(
                        'personstom' => 2015012100
                ),
                'storage' => array(
                        'skills'      => 2015060000,
                        'skilllinks'  => 2015060000,
                ),
                'workflow' => array(
                        'skills'      => 2015060000
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
        return array(
                array('plugintype' => 'storage',  'plugincode' => 'persons', 'eventcode' => 'insert')
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
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        // Используем функционал из $DOFFICE
        return $this->dof->is_access($do, NULL, $userid);
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
    public function catch_event($gentype,$gencode,$eventcode,$id,$mixedvar)
    {
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
     * @param dof_control $dof - объект ядра деканата
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    
    // **********************************************
    // Собственные методы
    // **********************************************
    
    /**
     * Отобразить блок назначения компетенций
     *
     * @param $ptype - Тип плагина
     * @param $pcode - Код плагина
     * @param $code - Сабкод плагина
     * @param $objectid - ID объекта для которого удаляются компетенции
     * @param $formid - Уникальное имя формы
     */
    public function linkform($ptype, $pcode, $code, $objectid, $formid = 'addremoveform')
    {
        $this->dof->im('skills')->linkform(
            $ptype, 
            $pcode, 
            $code, 
            $objectid,
            $formid
        );
    }
    
    /**
     * Отобразить блок назначения требуемых компетенций курса
     *
     * @param $courseid - ID курса
     */
    public function course_depend_skills_form($courseid)
    {
        $this->dof->im('skills')->linkform(
                'sync',
                'mdlskills',
                'depend',
                $courseid,
                'coursesettings_skills_depend'
        );
    }
    
    /**
     * Отобразить блок назначения предоставляемых компетенций курса
     *
     * @param $courseid - ID курса
     */
    public function course_provide_skills_form($courseid)
    {
        $this->dof->im('skills')->linkform(
                'sync',
                'mdlskills',
                'provide',
                $courseid,
                'coursesettings_skills_provide'
        );
    }
    
    /**
     * Назначить компетенции курса пользователю
     *
     * @param $courseid - ID курса
     * @param $userid - ID пользователя 
     * 
     * @return bool - Результат назначения компетенций
     */
    public function course_set_user_skills($courseid, $userid)
    {
        // Получим персону
        $person = $this->dof->sync('personstom')->get_person($userid);

        if ( empty($person) )
        {
            return false;
        }
        
        // Получим предоставляемые компетенции
        $cond['plugintype'] = 'sync';
        $cond['plugincode'] = 'mdlskills';
        $cond['code'] = 'provide';
        $cond['objectid'] = $courseid;
        $skills = $this->dof->storage('skilllinks')->get_records($cond);
        
        if ( empty($skills) )
        {// Комперенции для назначения не найдены
            return true;
        }
        
        $return = true;
        foreach ( $skills as $skill)
        {
            $return = ( $return && $this->dof->storage('skilllinks')->save(
                    $skill->skillid, 
                    'storages', 
                    'persons', 
                    'own', 
                    $person->id
                    ) 
            );
        }
    }
    
    /**
     * Отобразить блок назначения целевых компетенций пользователя
     *
     * @param $userid - ID пользователя
     */
    public function user_target_skills_form($userid)
    {
        // Получим персону
        $person = $this->dof->sync('personstom')->get_person($userid);
        if ( empty($person) )
        {
            return;
        }
        $this->dof->im('skills')->linkform(
                'storages',
                'persons',
                'target',
                $person->id,
                'target_skills'
        );
    }
    
    /**
     * Получить список имеющихся у пользователя компетенций
     *
     * @param $userid - ID пользователя 
     * @param $parentid - ID родительской компетенции
     * 
     * @return array|bool - Список компетенций пользователя или false в случае ошибки
     */
    public function user_get_own_skills($userid, $parentid = 0)
    {
        // Получим персону
        $person = $this->dof->sync('personstom')->get_person($userid);
        if ( empty($person) )
        {
            return false;
        }
               
        // Получим имеющиеся компетенции
        $cond = array();
        $cond['plugintype'] = 'storages';
        $cond['plugincode'] = 'persons';
        $cond['code'] = 'own';
        $cond['objectid'] = $person->id;
        $ownskills = $this->dof->storage('skilllinks')->get_records($cond);
        if ( empty($ownskills) )
        {// Компетенций нет
            return array();
        }
        
        // Получим форматированный список компетенций
        $list = $this->dof->im('skills')->get_skills_padding_list($parentid);
        if ( empty($list) )
        {// Нет списка компетенций, но есть назначенные
            return false;
        }
        $result = array();
        foreach ( $ownskills as $skilllink )
        {
            if ( isset($list[$skilllink->skillid]) )
            {// Добавим компетенцию
                $result[$skilllink->skillid] = $list[$skilllink->skillid];
            }
        }
        
        return $result;
    }
    
    /**
     * Получить список целевых компетенций пользователя
     *
     * @param $userid - ID пользователя
     * @param $parentid - ID родительской компетенции
     *
     * @return array|bool - Список компетенций пользователя или false в случае ошибки
     */
    public function user_get_target_skills($userid, $parentid = 0)
    {
        // Получим персону
        $person = $this->dof->sync('personstom')->get_person($userid);
        if ( empty($person) )
        {
            return false;
        }
        // Получим имеющиеся компетенции
        $cond = array();
        $cond['plugintype'] = 'storages';
        $cond['plugincode'] = 'persons';
        $cond['code'] = 'target';
        $cond['objectid'] = $person->id;
        $targetskills = $this->dof->storage('skilllinks')->get_records($cond);
        if ( empty($targetskills) )
        {// Компетенций нет
            return array();
        }
    
        // Получим форматированный список компетенций
        $list = $this->dof->im('skills')->get_skills_padding_list($parentid);
        if ( empty($list) )
        {// Нет списка компетенций, но есть назначенные
            return false;
        }
        $result = array();
        foreach ( $targetskills as $skilllink )
        {
            if ( isset($list[$skilllink->skillid]) )
            {// Добавим компетенцию
                $result[$skilllink->skillid] = $list[$skilllink->skillid];
            }
        }
        return $result;
    }
    
    /**
     * Получить ID целевых курсов пользователя
     *
     * @param $userid - ID пользователя
     *
     * @return array|bool - Список идентификаторов
     */
    public function user_get_target_courses_id($userid)
    {
        // Получим целевые компетенции пользователя
        $userskills = $this->user_get_target_skills($userid);
        
        if ( empty($userskills) )
        {// Целевых компетенций пользователя нет
            return array();
        }
        
        // Получим связи курсов с теми же компетенциями
        $cond = array();
        $cond['plugintype'] = 'sync';
        $cond['plugincode'] = 'mdlskills';
        $cond['code'] = 'provide';
        $cond['skillid'] = array_keys($userskills);
        $links = $this->dof->storage('skilllinks')->get_records($cond);
        if ( empty($links) )
        {// Связей нет
            return array();
        }
        // Подготовим результат
        $result = array();
        foreach ( $links as $link )
        {// Сформируем массив курсов
            $result[] = $link->objectid;     
        }
        return $result;
    }
    
    /**
     * Получить ID доступных курсов пользователя
     *
     * @param $userid - ID пользователя
     *
     * @return array|bool - Список идентификаторов
     */
    public function user_get_own_courses_id($userid)
    {
        // Получим целевые компетенции пользователя
        $userskills = $this->user_get_own_skills($userid);
    
        if ( empty($userskills) )
        {// Целевых компетенций пользователя нет, сформируем список курсов без компетенций
            // Получим связи курсов с теми же компетенциями
            $cond = array();
            $cond['plugintype'] = 'sync';
            $cond['plugincode'] = 'mdlskills';
            // Получим все курсы
            $linksallcourses = $this->dof->storage('skilllinks')->get_records($cond);
            $allcourses = array();
            foreach ( $linksallcourses as $link )
            {
                $allcourses[$link->objectid] = $link->objectid;
            }
            $cond['code'] = 'depend';
            // Получим курсы с входными компетенциями
            $linksdepcourses = $this->dof->storage('skilllinks')->get_records($cond);
            $depcourses = array();
            foreach ( $linksdepcourses as $link )
            {
                $depcourses[$link->objectid] = $link->objectid;
            }
            
            $result = array_diff($allcourses, $depcourses);
            return $result;
        }
    
        // Получим связи курсов с теми же компетенциями
        $cond = array();
        $cond['plugintype'] = 'sync';
        $cond['plugincode'] = 'mdlskills';
        $cond['code'] = 'depend';
        $cond['skillid'] = array_keys($userskills);
        $links = $this->dof->storage('skilllinks')->get_records($cond);
        if ( empty($links) )
        {// Связей нет
            return array();
        }
        // Подготовим результат
        $result = array();
        foreach ( $links as $link )
        {// Сформируем массив курсов
            $result[] = $link->objectid;
        }
    
        return $result;
    }
    
    public function get_path($savail = array(), $sdest = array())
    {
        return $this->dof->storage('skilllinks')->skillpath($savail, $sdest);
    }
}
?>