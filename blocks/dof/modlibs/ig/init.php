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

/** Класс для работы с идеограммами
 * 
 */
class dof_modlib_ig implements dof_plugin_modlib
{
    /**
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
        return 2016060900;
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
        return 'neon_a';
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
        return 'ig';
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
    
    /** Получить стандартную строку перевода, из списка строк, 
     * которые используются во всех плагинах
     * 
     * @return string - стандартная строка перевода
     * @param string $identifier - идентификатор строки
     * @param string $a[optional] - подставляемое значение внутрь строки.
     */
    public function igs($identifier,$a=NULL)
    {
        return $this->dof->get_string($identifier, 'ig', $a, 'modlib');
    }
    
    /** Получить html-код стандартной иконки
     * 
     * @param string $name - короткое имя иконки. Например 'view' или 'edit'
     * @param string $url[optional] - url который откроется при нажатии на ссылку
     * @param array $options - массив html-свойств img-тега
     * 
     * @return string
     */
    public function icon($name, $url=null, $options=null)
    {
        return $this->icon_plugin($name,'modlib','ig',$url,$options);
    }
    
    /** Получить стандартную языковую строку для стандартной иконки
     * 
     * @param string $name - короткое название стандартной иконки
     * 
     * @return - языковая строка, или пустая строка если перевода не нашлось
     */
    protected function standart_icon_string($name)
    {
        switch ( $name )
        {
            case 'view':   return $this->igs('view');   break;
            case 'edit':   return $this->igs('edit');   break;
            case 'add':    return $this->igs('add');    break;
            case 'delete': return $this->igs('delete'); break;
            case 'view_full': return $this->igs('view_full'); break;
            case 'edit_full': return $this->igs('edit_full'); break;
        }
        
        return '';
    }
    
    /** Получить html-код иконки из плагина
     * 
     * @param string $name - короткое имя иконки. Например 'view' или 'edit'
     * @param string $plugintype - тип плагина ('im','sync','modlib')
     * @param string $plugincode - код плагина
     * @param string $url[optional] - url который откроется при нажатии на ссылку
     * @param array $options - массив html-свойств img-тега
     * 
     * @return string
     */
    public function icon_plugin($name,$plugintype,$plugincode,$url=null,$options=null)
    {
        if ( ! empty($options) AND is_array($options) )
        {
            if ( ! isset($options['title']) )
            {
                $options['title'] = $this->standart_icon_string($name);
            }
            $options = dof_transform_tag_options($options);
        }elseif ( ! is_string($options) )
        {
            $options = ' title="'.$this->standart_icon_string($name).'"';
        }
        
        if ( !in_array($plugintype,array('im','sync','modlib')) )
        {// только для плагинов типа im, sync и modlib
            return '';
        }
        $method = 'url_'.$plugintype;
        $imgtag = '<img src="'.$this->dof->$method($plugincode, '/icons/'.$name.'.png').'" '.$options.'/>';
        if ( $url )
        {// нужно просто вывести иконку
            return '<a href="'.$url.'" >'.$imgtag.'</a>';
        }// нужно вывести иконку как ссылку
        return $imgtag;
    }
    
    /** Получить html-код иконки сортировки и направление сортировки
     * 
     * @param string $type - имя поля
     * @param string $sort - имя поля по которому производилась сортировка
     * @param string $dirlink - направление произведенной сортировки
     * 
     * @return array 
     *          string dir - направление сортировки
     *          string icon - html-код иконки
     */
    public function get_icon_sort($type,$sort,$dirlink = 'asc')
    {   
        // ставим значения по умолчанию
        $dir = 'asc';
        $icon = '';
        if ( $type == $sort )
        {// имя поля совпадает с сортировочным
            if ( $dirlink == 'desc' )
            {// сортировка была обратной - меняем стрелку на прямую сортировку
                $icon = $this->dof->modlib('ig')->icon('arrow_down');
            }
            if ( $dirlink == 'asc' )
            {// прямая сортировка - сменим на обратную
                $dir = 'desc';
                $icon = $this->dof->modlib('ig')->icon('arrow_up');
            }
        }
        // возвращаем направление сортировки и иконку
        return array($dir,$icon);
    }
}
?>