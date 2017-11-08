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


/** Класс стандартных функций интерфейса
 * 
 */
class dof_storage_statushistory extends dof_storage
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************

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
        // Версия плагина (используется при определении обновления)
		return 2016061700;
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
        return 'statushistory';
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
        // Пока событий не обрабатываем
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
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        // Используем функционал из $DOFFICE
        return $this->dof->is_access($do, NULL, $userid);
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
        // Ничего не делаем, но отчитаемся об "успехе"
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
        return 'block_dof_s_statushistory';
    }

    // **********************************************
    //              Собственные методы
    // **********************************************

    /** Записываем в БД информацию об изменении статуса плагина
     * @param mixed $plugin  имя справочника или объект плагина, которому принадлежит изменившийся объект
     * @param integer $objectid  id изменившегося объекта
     * @param string $status  Новый статус
     * @param string $prevstatus  Старое значение статуса
     * @param array $opt  дополнительные параметры при изменении статуса
     * @param string $notice  заметка об изменении статуса
     * @param integer $muserid  id пользователя moodle, от имени которого изменяется статус
     * @param bool $quiet "тихий" режим (без отправки событий)
     * @return mixed bool false если операция не удалась или id вставленной записи
     * @access public
     */
    public function change_status($plugin,$objectid, $status,$prevstatus=null,$opt=null,$notice='',$muserid=null,$quiet=false)
    {
        // Фиксируем изменение статуса объекта
        $obj = new stdClass();
        
        // Статусы
        $obj->status = $status;
        $obj->prevstatus = $prevstatus;
        
        // Имя и тип плагина
        if (is_string($plugin))
        {
            $obj->plugintype = 'storage';
            $obj->plugincode = $plugin;
        }elseif ($plugin instanceof dof_plugin)
        {
            $obj->plugintype = $plugin->type();
            $obj->plugincode = $plugin->code();
        }
        
        // id пользователя, выполнившего изменения
        if (is_null($muserid))
        {
            if ( empty($opt['personid']) )
            {
                global $USER;
                $muserid = $USER->id;
            }else
            {
                $muserid = $this->dof->storage('persons')->get_field($opt['personid'],'mdluser');
            }
        }
        
        // id пользователя в Moddle
        $obj->muserid = $muserid;
        
        // id объекта, в котором изменился статус
        $obj->objectid = $objectid;

        // время изменения статуса
        $obj->statusdate = time();
        
        // заметки
        $obj->notes = $notice;
        
        if ( is_array($opt) AND isset($opt['orderid']) )
        {// id приказа
            $obj->orderid = $opt['orderid'];
        }
        
        // Параметры объекта
        $obj->opt = serialize($opt);

        // Добавляем в БД
        return $this->insert($obj,$quiet);
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
        $conds = fullclone($inputconds);
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
    
    /**
     * Вернуть статус элемента на указанную дату
     *
     * @param string $plugintype - Тип плагина
     * @param string $plugincode - Код плагина
     * @param string $objectid - ID объекта
     * @param string $timestamp - Время, на которое необходимо получить статус
     */
    public function get_status($plugintype, $plugincode, $objectid, $timestamp = 0)
    {
        if ( empty($timestamp) )
        {
            $timestamp = 0;
        }
        // Запрос статуса
        $sql = '
                plugintype = :plugintype AND
                plugincode = :plugincode AND
                objectid = :objectid AND
                statusdate < :sdate
               ';
        // Параметры
        $sqlparams = [
            'plugintype' => $plugintype,
            'plugincode' => $plugincode,
            'objectid' => $objectid,
            'sdate' => $timestamp,
        ];
        // Получить событие смены статуса
        $status = $this->get_records_select($sql, $sqlparams, ' statusdate DESC ', '*', 0, 1);
    
        if ( ! empty($status) )
        {// Происходила смена статуса раньше указанной даты
            // В указанное время объект находился в текущем статусе
            $result = array_shift($status);
            return $result->status;
        } else
        {// Смены статуса ранее указанной даты не происходило
            $params = [
                'plugintype' => $plugintype,
                'plugincode' => $plugincode,
                'objectid' => $objectid,
            ];
            // Поучим первую смену статуса
            $status = $this->get_records($params, ' statusdate ASC ', '*', 0, 1);
    
            if ( ! empty($status) )
            {// Смена статуса объекта происходила
                // В указанное время объект находился в текущем статусе
                $result = array_shift($status);
                return $result->prevstatus;
            } else 
            {
                // Хак для подписок на предмето-классы
                if ( $plugintype == 'storage' && $plugincode == 'cpassed' )
                {
                    return $this->dof->storage('cpassed')->get_field($objectid, 'status');
                }
            }
        }
        // Статус объекта неизвестен
        return null;
    }

    /**
     * Получение даты последней смены статуса
     *
     * @param string $plugintype - Тип плагина
     * @param string $plugincode - Код плагина
     * @param int $objectid - ID объекта
     * @param string $status - статус, если ищем когда был установлен конкретный статус. Пустая строка, если не имеет значения.
     *
     * @return int|null|false - timestamp дата смены статуса
     *                          - null, если смена статуса не найдена
     *                          - false в случае ошибки
     */
    public function get_last_change_time( $plugintype, $plugincode, $objectid, $status = '' )
    {
        // Запрос времени
        $sql = [
            'plugintype = :plugintype',
            'plugincode = :plugincode',
            'objectid = :objectid'
        ];
        // Параметры
        $sqlparams = [
            'plugintype' => $plugintype,
            'plugincode' => $plugincode,
            'objectid' => $objectid
        ];
    
        if ( ! empty($status) )
        {
            $sql[] = 'status = :status';
            $sqlparams['status'] = $status;
        }
        // Поучим последнюю смену статуса
        $statushistories = $this->get_records_select(implode(" AND ", $sql), $sqlparams, ' statusdate DESC ', '*', 0, 1);
        if ( !$statushistories )
        {
            return false;
        }
        if ( ! empty($statushistories) )
        { //смена статуса объекта была
            $statushistory = array_shift($statushistories);
            //в указанное время объект находился в текущем статусе
            return $statushistory->statusdate;
        }
    
        //смена статуса не была найдена
        return null;
    }
}
?>