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


/** Настройки плагинов
 * 
 */
class dof_im_cfg implements dof_plugin_im
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
        return 2012052900;
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
        return 'angelfish';
    }
    
    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'im';
    }
    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'cfg';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('config'=>2011040500));
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
        return $this->dof->is_access($do);
    }
    /** Требует наличия полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        // Используем функционал из $DOFFICE
        return $this->dof->require_access($do, NULL, $userid);
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
    /** Конструктор
     * @param dof_control $dof - идентификатор действия, которое должно быть совершено
     * @access public
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    // **********************************************
    // Методы, предусмотренные интерфейсом im
    // **********************************************
    
    /**Возвращает содержимое блока, отображаемого на страницах fdo
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string - html-код текста
     */
    function get_block($name, $id = 1)
    {
       return "";
    }
    /** Возвращает содержимое секции
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string  - html-код названия секции
     */
    function get_section($name, $id = 1)
    {
    	return '';
    }
     /** Возвращает текст для отображения в блоке на страницах MOODLE 
     * @return string  - html-код для отображения
     */
    public function get_blocknotes($format='other')
    {
    	return '';
    }
    
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** Получить URL к собственным файлам плагина
     * @param string $adds[optional] - фрагмент пути внутри папки плагина
     *                                 начинается с /. Например '/index.php'
     * @param array $vars[optional] - параметры, передаваемые вместе с url
     * @return string - путь к папке с плагином 
     * @access public
     */
    public function url($adds='', $vars=array())
    {
        return $this->dof->url_im($this->code(), $adds, $vars);
    }
    /**
     * Отобразить таблицу настроек
     * @param int $list - список настроек
     * @param array $addvars - доп параметры, которые нужно вносить во все ссылки в таблице
     * @return 
     */
    function show_list($list, $addvars)
    {
        // Собираем данные
        $data = array();
        if ( ! isset($list) OR ! is_array($list) )
        {// не получили список пользователей
            print('<p align="center"><i>('.$this->dof->get_string('config_list_is_empty', 'cfg').')</i></p>');
            return "";
        }
        foreach ($list as $obj ) 
        {
            $link = '';

            // имя персоны
            $personname = "";
            if ( $obj->personid )
            {// есть персона - выведем ее ФИО
                $personname = $this->dof->storage('persons')->get_fullname($obj->personid);
            }else
            {// нету - так и напишем
                $personname = $this->dof->get_string('any_person', 'cfg');
            }
            // имя подразделения
            $departmentname = "";
            if ( $obj->departmentid )
            {// есть подразделение - выведем их имя и код
                $departmentname = $this->dof->storage('departments')->get_field($obj->departmentid,'name').' <br>['.
    	              $this->dof->storage('departments')->get_field($obj->departmentid,'code').']';
            }else
            {// нету - так и напишем
                $departmentname = $this->dof->get_string('any_department', 'cfg');
            }
            
            /*
            // наследование
            ПОКА ОТКАЗАЛИСЬ - ПОТОМУ И ЗАКОМИТИЛИ
            if ( $obj->noextend )
            {// нет
                $extend = $this->dof->modlib('ig')->igs('no');
            }else
            {// да
                $extend = $this->dof->modlib('ig')->igs('yes');
            }
            // отрисуем ссылки на действия
            // редактировать
            $link = '<a href ='.$this->dof->url_im('cfg','/edit.php?id='.$obj->id,$addvars).'>
            	<img src="'.$this->dof->url_im('cfg', '/icons/edit.png').'" 
            	 alt="'.$this->dof->get_string('edit_cfg', 'cfg').'" title="'.$this->dof->get_string('edit_cfg', 'cfg').'"></a>';
            // не глобальная настройка - добавим удаление и переопределение
            if ( $obj->departmentid )
            {
                // переопределение
                $link .= '<a href ='.$this->dof->url_im('cfg','/edit.php?id='.$obj->id,$addvars).'>
                	<img src="'.$this->dof->url_im('cfg', '/icons/edit.png').'" 
                	 alt="'.$this->dof->get_string('edit_cfg', 'cfg').'" title="'.$this->dof->get_string('edit_cfg', 'cfg').'"></a>';
                // удаление (физическое)
                $link .= '<a href ='.$this->dof->url_im('cfg','/delete.php?id='.$obj->id,$addvars).'>
                	<img src="'.$this->dof->url_im('cfg', '/icons/delete.png').'" 
                	 alt="'.$this->dof->modlib('ig')->igs('delete').'" title="'.$this->dof->modlib('ig')->igs('delete').'"></a>';                
            }*/
            // собираем данные
            $qw = "<a href = ".$this->dof->url_im('cfg','/edit.php?departmentid='.$addvars['departmentid'].'#'.$obj->id).">".$obj->id."</a>";
            $data[] = array( $qw,
                            $obj->code,
                            $obj->type,
                            str_replace('*',' * ',str_replace('+',' + ',$obj->value)),
                            $obj->plugintype,
                            $obj->plugincode,
                            $personname,
                            $departmentname );
                        //    $extend,
                        //   $link);
        }
        // Рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        // $table->align = array ("center","center","center", "center", "center");
        // $table->wrap = array ("nowrap","","","");
        $table->cellpadding = 10;
        $table->cellspacing = 0;
        $table->width = '600';
        $table->head = array('<a href="'.$this->dof->url_im('cfg','',array_merge($addvars,array('sort'=>'id'))).'">'.$this->dof->get_string('id','cfg').'</a>',
                             '<a href="'.$this->dof->url_im('cfg','',array_merge($addvars,array('sort'=>'code'))).'">'.$this->dof->get_string('code','cfg').'</a>',
                             $this->dof->get_string('type','cfg'),
                             $this->dof->get_string('value','cfg'),
                             $this->dof->get_string('plugintype','cfg'),
                             $this->dof->get_string('plugincode','cfg'),
                             $this->dof->get_string('person','cfg'),
                             $this->dof->get_string('department','cfg') );
                            // $this->dof->get_string('noextend','cfg'),
                            // $this->dof->get_string('actions','cfg') );
        $table->data = $data;
        //передали данные в таблицу
        $this->dof->modlib('widgets')->print_table($table);
    }
}