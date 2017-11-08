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


/** Класс стандартных функций интерфейса
 * 
 */
class dof_modlib_templater implements dof_plugin_modlib
{
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
        return 2009031600;
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
        return 'templater';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib' => array('pear' => 2009032000));
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
     * @return bool
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
     * Возвращает обьект парсера для указанного плагина, и с указанным именем
     * @param object $plugintype - тип плагина (im, storage, и др.)
     * @param object $pluginname - имя плагина
     * @param object $odj - данные для экспорта 
     * @param object $templatename - имя шаблона форматирования (order, report и т. д.)
     * @return dof_modlib_templater_package - объект для работы с шаблоном или bool false
     */
    public function template($plugintype, $pluginname, $obj, $templatename=null)
    {
        if ( is_null($templatename) )
        {// переопределенный класс для шаблона не задан - вызываем стандартный файл modlib';
            return $this->get_standard_package($plugintype, $pluginname, $obj, $templatename);
        }else
        {// вызываем переопределенный класс из im';
            $path = $this->template_path($plugintype, $pluginname, $templatename, 'init.php', true);
            // будем искать класс с указанным названием
            $classname = 'dof_'.$plugintype.'_'.$pluginname.'_templater_'.$templatename;
        }
        if ( file_exists($path) )
        {// файл есть - подключаем';
            //подключаем файл с родительским классом
            require_once($this->dof->plugin_path($this->type(), $this->code(),'/package.php'));
            require_once($path);//подключаем файл с классом плагина
        }else
        {// файла нет - подключаем стандартный package';
            return $this->get_standard_package($plugintype, $pluginname, $obj, $templatename);        
        }
        if ( class_exists($classname) )
        {// класс с нужным названием есть'; 
            return new $classname($this->dof, $plugintype, $pluginname, $obj, $templatename);
        }else
        {// в файле нет класса с нужным названием';
            return false;
        }
    }
    /** 
     * Возвращает стандартный обработчик экспорта
     * @param object $plugintype - тип плагина (im, storage, и др.)
     * @param object $pluginname - имя плагина 
     * @param object $obj - данные для экспорта
     * @param string $templatename - имя папки документа, в которой лежит шаблон
     * @return mixed dof_modlib_templater_package - стандартный объект для работы с шаблоном
     * или bool false
     */
    private function get_standard_package($plugintype, $pluginname, $obj, $templatename=null)
    {
		$path = $this->template_path($this->type(), $this->code(), null, 'package.php');
		$classname = 'dof_modlib_templater_package';
        if ( file_exists($path) )
        {// файл есть - подключаем';
            require_once($path);
        }else
        {// файла нет - беда';
            return false;
        }
        if ( class_exists($classname) )
        {// класс с нужным названием есть в папке';
            return new $classname($this->dof, $plugintype, $pluginname, $obj, $templatename);
        }else
        {// в файле нет класса с нужным названием';
            return false;
        }		       
    }
    /** 
     * Возвращает путь к шаблону (корню или внутренней папке) 
     * @return string путь к плагину
     * @param string $plugintype - тип плагина (im, storage, и др.)
     * @param string $pluginname - имя плагина
     * @param string $templatename[optional] - имя шаблона форматирования (order, report и т. д.)
     * @param string $adds[optional] - дополнительные параметры
     * @param bool   $fromplugin - определяет, где искать подключаемые файлы. 
     *                             Если null  - то и во внешнем плагине, и в modlib.
     *                             Если true  - только во внешнем плагине
     *                             Если false - только в modlib
     *                             
     * @todo оптимизировать код, он может занимать в 2 раза меньше места
     */
    public function template_path($plugintype, $pluginname, $templatename=null, $adds=null, $fromplugin=null)
    {
        $addpath = '';
        if ( ! is_null($adds) )
        {//надо указать путь внутри шаблона
            $addpath = '/'.$adds;
        }
        // устанавиваем путь к внешнему плагину, и внутренней папке, 
        // чтобы не писать его несколько раз
        
        // путь к внешнему плагину
        $externalpath = $this->dof->plugin_path($plugintype, $pluginname,'/templater/'.$templatename.$addpath);
        // внутренний путь
        $internalpath = $this->dof->plugin_path('modlib', 'templater', $addpath);
        
        if ( is_null($templatename) )
        {// имя переопределенного плагина не указано
            if ($fromplugin === true)
            {// если имя плагина не задано, но сказано использовать внешний планин - то это ошибка 
                return false;
            }
            
            if ($this->is_file_or_dir($internalpath))
            {// в этой ветке всегда возвращаем внутренний путь
                return $internalpath;
            }else
            {//путь внутри нашего плагина не существует
                return false;
            }
        }else
        {// имя переопределенного плагина указано - берем из этой папки
            if ( $fromplugin === null )
            {//ищем и в modlib/templater и во внешнем плагине 
                if ($this->is_file_or_dir($externalpath))
                {// во внешнем плагине есть необходимый файл или папка
                    return $externalpath;
                }elseif($this->is_file_or_dir($internalpath))
                {// если во внешнем плагине нет - ищем во внутреннем
                    return $internalpath;
                }else
                {// указанный путь не существует ни во внутреннем ни во внешнем плагине
                    return false;
                }
            }elseif( $fromplugin === true )
            {//ищем только во внешнем плагине
                if ($this->is_file_or_dir($externalpath))
                {
                    return $externalpath;
                }else
                {
                    return false;
                }
            }elseif( $fromplugin === false )
            {//ищем только в modlib/templater 
                if ($this->is_file_or_dir($internalpath))
                {
                    return $internalpath;
                }else
                {
                    return false;
                }
            }
        }
    }
    
    /** 
     * Проверяет, является ли указанный путь 
     * директорией, файлом, или символической ссылкой
     * @return true или false
     * @param string $path - путь к файлу или папке
     */
    private function is_file_or_dir($path)
    {
        return is_file($path) OR is_dir($path) OR is_link($path);
    }
}
?>