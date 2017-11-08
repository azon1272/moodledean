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

require_once($DOF->plugin_path('storage','config','/config_default.php'));

/** Класс стандартных функций хранилища
 * 
 */
class dof_storage_synclogs extends dof_storage implements dof_storage_config_interface
{
    /**
     * @var object dof_control - объект с методами ядра деканата
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************

    /** 
     * Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $DB;
        // Модификация базы данных через XMLDB
        $result = true;
        
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        if ($oldversion < 2013102900)
        {// добавим поле salfactor
            $field = new xmldb_field('textlog', XMLDB_TYPE_TEXT, null, null, null, null, null, 'error');
            $dbman->change_field_type($table, $field);
        }
        return true;// уже установлена самая свежая версия
    }
    /** 
     * Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
		return 2013102900;
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
        return 'synclogs';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
		return array();
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
        return 900;
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
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        // Используем функционал из $DOFFICE
        return $this->dof->is_access($do, NULL, $userid);
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
        mtrace("SyncLog create log file started.................");
        
        if ( $this->dof->storage('config')->
                get_config_value('save_log_file', 'storage', 'synclogs') )
        {// Запись лога разрешена - создадим файл
            $name = 0;
            for ($day = 30 ; $day <= 1 ; $day-- )
            {
                $name++;
                $this->save_log_file(time() - $day*24*3600, time() - ($day - 1)*24*3600, $name);
            }
            
        }
        mtrace("SyncLog create log file finished.................");
        
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
     * с которой работает examplest
     * @return text
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_synclogs';
    }

    // **********************************************
    //       Методы для работы с полномочиями
    // **********************************************  
    
    /** Список настроек плагина
     * @param array $options  - дополнительные параметры, указаны для совместимости
     *
     * return array
     */
    public function config_default($options=null)
    {
        // используется ли плагин
        $config = array();
        $obj = new stdClass();
        $obj->type  = 'checkbox';
        $obj->code  = 'enabled';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        
        // сохранение логов в файл
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'save_log_file';
        $obj->value = 1;
        $config[$obj->code] = $obj;
        return $config;
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** Добавляет лог для реестра синхронизации
     * @param string operation  - операция выполняемая при синхронизации
     * @param string direct     - направление синхронизации
     * @param int    syncid     - id синхронизации, 0 - если синхронизация connect или create была неуспешна
     * @param string textlog    - текст лога синхронизации 
     * @param object opt        - дополнительные параметры лога синхронизации 
     * @param string error      - есть ли ошибка в синхронизации 
     * @param string prevoperation - предыдущая операция синхронизации
     * @return int|bool - id лога или false при ошибке
     */
    public function add_log($operation,$direct,$syncid=0,$textlog='',$opt=null,$error=false,$prevoperation='')
    {
        $log = new stdClass;
        $log->syncid = $syncid;
        $log->executetime = time();
        $log->operation = $operation;
        $log->direct = $direct;
        $log->prevoperation = $prevoperation;
        $log->textlog = $textlog;
        $log->optlog = serialize($opt);
        $log->error = (int) $error;
        return $this->insert($log);
    }
    
    /** Сохранение логов в файл за указанный период
     * @access private
     * @param int $from - начало периода
     * @param int $to - конец периода
     * @return bool
     */
    private function save_log_file($from, $to=null, $fname = '1')
    {
        $select = " error = 1 AND executetime > {$from} ";
        if ( isset($to) )
        {
            $select .= " AND executetime < {$to} ";
        }
        $filename = $this->dof->plugin_path('storage', 'synclogs', '/dat/synclogs_errors'.$fname.'.log');
        $logs = $this->get_records_select($select);
        // сохраняем данные в файл
        $file = fopen($filename, 'w');
        $content = '';
        
        foreach ($logs as $log)
        {
            $content = implode('|', (array)$log);
            fwrite($file, $content."\n");
        }

        fclose($file);
        
        return true;
    }
}
?>