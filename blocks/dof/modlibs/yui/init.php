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
// Copyright (C) 2008-2999  Evgenij Cigancov (Евгений Цыганцов)           //
// Copyright (C) 2008-2999  Ilia Smirnov (Илья Смирнов)                   //
// Copyright (C) 2008-2999  Mariya Rojayskaya (Мария Рожайская)           //
// Copyright (C) 2008-2999  Nikolay Konovalov (Николай Коновалов)         //
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
global $DOF;
// подключаем интерфейс настроек, чтобы в плагине работали настройки
require_once($DOF->plugin_path('storage','config','/config_default.php'));
/** Класс стандартных функций интерфейса
 * 
 */
class dof_modlib_yui implements dof_plugin_modlib, dof_storage_config_interface
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /** Метод, реализующий инсталяцию плагина в систему
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
    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $oldversion - версия установленного в системе плагина
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
        return 2014110500;
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
        return 'neon';
    }
    
    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'modlib';
    }
    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'yui';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage' => array('config'  => 2011040500),
                     'modlib'  => array('widgets' => 2011052002,
                                        'nvg'     => 2011092900));
    }
    /** Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin 
     * @see dof_modlib_base_plugin::is_setup_possible()
     * 
     * @param int $oldversion [optional] - старая версия плагина в базе (если плагин обновляется)
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
     * @param int $oldversion [optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion = 0)
    {
        return array('storage'=>array('config'=> 2011080900));
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
     * если требуется - возвращает количество секунд между запусками
     * если нет - возвращает false
     * @return mixed int или bool false
     * @access public
     */
    public function is_cron()
    {
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
     * @param dof_control $dof - идентификатор действия, которое должно быть совершено
     * @access public
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    /** Запустить обработку периодических процессов
     * @param int $messages - количество отображаемых сообщений (0 - не выводить, 3 - детальная диагностика)
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin_modlib
    // **********************************************

    // **********************************************
    // Собственные методы
    // **********************************************

    /** Функция получения настроек для плагина
     *  
     */
    public function config_default($code = null)
    {
        // стандартная настройка "плагин включен"
        $config = array();
        $obj = new stdClass();
        $obj->type  = 'checkbox';
        $obj->code  = 'enabled';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        
        // Какие js-файлы по умолчанию использовать: сжатые или нет
        // @todo сделать select со списком вариантов и добавить перевод в языковой файл
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'debug_mode';
        // возможные значения: 
        // 1 (development) - полные версии js-файлов с комментариями, для разработки
        // 0 (production)  - сжатые версии js-файлов для работы 
        $obj->value = '0';
        $config[$obj->code] = $obj;
        return $config;
    }
    
    /**
     * Создаёт вызов функции JavaScript, подключая необходимые модули для загрузки.
     *
     * С помощью функции можно подключать стандартные типы модулей YUI:
     *     - YUI3    [node, event, io]
     *     - YUI2    [yui2-*]
     *     - Moodle  [moodle-*]
     *     - Gallery [gallery-*]
     *
     * @param array|string $modules - один или более модулей
     * @param string $function - название функции, которую необходимо исполнить после подключения модулей
     * @param array $arguments - аргументы для передачи в функцию
     * @param string $galleryversion - версия галереи для использования
     * @param bool $ondomready
     */
    public function yui_module($modules, $function, array $arguments = null, $galleryversion = null, $ondomready = false)
    {
        global $PAGE;
        $PAGE->requires->yui_module($modules, $function, $arguments, $galleryversion, $ondomready);
    }

}
?>