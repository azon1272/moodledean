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
// Copyright (C) 2008-2999  Evgeniy Gorelov (Евгений Горелов)             //
// Copyright (C) 2008-2999  Ilya Fastenko (Илья Фастенко)                 //
//                                                                        //
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
 * Класс для работы с приказами
 * Действие приказа: подписание студентов на курсы
 * @author Evgeniy Gorelov
 */
class dof_modlib_cur implements dof_plugin_modlib
{
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /** 
     * Метод, реализующий инсталяцию плагина в систему
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
    /** 
     * Метод, реализующий обновление плагина в системе
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
    /** 
     * Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2016031400;
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
        return 'neon';
    }
    
    /** 
     * Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'modlib';
    }
    /** 
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'cur';
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
        return array();
    }
    /** 
     * Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
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
    // **********************************************
    // Собственные методы
    // **********************************************
    /** 
     * Конструктор
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    
    /**
     * Получить объект класса modlib/cur/order/dof_modlib_cur_order_base
     * Если передан id, то вернуть конкретный объект.
     *
     *  @param string $code код приказа, объект которого ходим получить
     *  @param int $id[optional] id запрашиваемого приказа
     *  @param int $departmentid[optional] id подразделения приказа, 
     *      - при создании экземпляра для последующей загрузки приказа 
     *      из БД данное поле можно не указывать, т.к. оно заполнено в БД
     *      - при создании экземпляра для последующего сохранения в БД 
     *      данное поле следует обязательно указать, т.к. будут 
     *      проблемы при исполнении приказа
     *      при загрузке старого
     *  @return object|bool обект приказа dof_modlib_cur_order_base, 
     *          false - если пробуем загрузить обект другого класса,
     *          другого кода или не существующий обект
     *  @author 2011 Evgeniy Gorelov 
     */
    public function order($code, $id=null, $departmentid=null)
    {
        //проверка входных параметров
        if ( ( ! is_int_string($id) AND ! is_null($id) ) 
                OR ! is_int_string($departmentid)  AND ! is_null($departmentid) )
        {
            return false;
        }
        
        // допускается не указывать departmentid при загрузке приказа из БД
        // т.е. если передан $id приказа
        if ( ! $id AND ! $departmentid )
        {
            return false;
        }
        
        // Получим название класса приказа для переданного кода приказа
        // внутри метода подключается файл с классом
        $classname = $this->order_name($code);
        
        // Если передан id, то попробуем загрузить приказ из БД, если не совпадает 
        // тип плагина или код, то вернется false
        if ( $id )
        {
            // создадим экземпляр данного класса
            $order = new $classname($this->dof);
            
            // такое замысловатое переприсваивание из-за того, что load возвращает объект
            // типа object, а нам нужен конкретный тип, чтобы получить доступ к методам
            if ( !($order->cur_load($id)) )
            {
                return false;
            }
        }
        {
            // создадим экземпляр данного класса
            $order = new $classname($this->dof, $departmentid);
        }
        
        return $order;
    }
    
    /**
     * Получить название класса приказа данного планина по его коду
     * 
     * @param string $code код приказа
     * @return false 
	 * @author 2011 Evgeniy Gorelov
     */
    public function order_name($code)
    {
        $optype = 'modlib';
        $opcode = 'cur';
        $path = $this->dof->plugin_path($optype,$opcode,'/order/'.$code.'.php');
        if ( ! file_exists($path) )
        {//если файла нет - сообщим об этом
            $this->dof->print_error('file_not_found', '',
                    $path, $this->type(), $this->code());
        }
        //файл есть - подключаем файл
        include_once($path);
        $classname = "dof_{$optype}_{$opcode}_order_{$code}";
        if ( ! class_exists($classname) )
        {
            $this->dof->print_error('class_doesnot_exists', '',
                    $classname, $this->type(), $this->code());
        }
        
        return $classname;
    }
}
?>