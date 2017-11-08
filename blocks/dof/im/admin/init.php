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


/** Администрирование - главная панель управления
 * 
 */
class dof_im_admin implements dof_plugin_im
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
        return 2016011400;
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
        return 'admin';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib'=>array('nvg'=>2008060300),
        			 'im'=>array('standard'=>2008060300));
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
    /** Возвращает текст для отображения в блоке на странице dof
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string - html-код содержимого блока
     */
    function get_block($name, $id = 1)
    {
        // Проверяем, имеет ли право пользователь видеть админские блоки
        if (!$this->is_access('admin'))
        {
            return false;
        }
        $rez = '';
        switch ($name)
        {
            case 'menu':
                $path = $this->dof->url_im('admin','/');
                $rez .= "<a href=\"{$path}\">".$this->dof->get_string('notes', 'admin').'</a>';
                // запланированные задания
                $path = $this->dof->url_im('admin','/todo/list.php');
                $rez .= "<br /><a href=\"{$path}\">".$this->dof->get_string('todo_do', 'admin').'</a>';
                // Конфиги
                if ( $this->dof->plugin_exists('im','cfg') )
                {// плагин существует
                    $path = $this->dof->url_im('cfg','/index.php');
                    $rez .= "<br /><a href=\"{$path}\">".$this->dof->get_string('cfg', 'admin').'</a>';
                }
                // Доверенности
                if ( $this->dof->plugin_exists('im','acl') )
                {// плагин существует
                    $path = $this->dof->url_im('acl','/index.php');
                    $rez .= "<br /><a href=\"{$path}\">".$this->dof->get_string('warrants', 'admin').'</a>';
                }
                $rez .= "<br /> {$this->dof->get_string('plugins', 'admin')}:<br />";
                $path = $this->dof->url_im('admin','/plugins/index.php?type=storage');
                $rez .= "<a href=\"{$path}\">".$this->dof->get_string('storages', 'admin').'</a>';
                $path = $this->dof->url_im('admin','/plugins/index.php?type=im');
                $rez .= "<br />";
                $rez .= "<a href=\"{$path}\">".$this->dof->get_string('ims', 'admin').'</a>';
                $path = $this->dof->url_im('admin','/plugins/index.php?type=sync');
                $rez .= "<br />";
                $rez .= "<a href=\"{$path}\">".$this->dof->get_string('syncs', 'admin').'</a>';
                $path = $this->dof->url_im('admin','/plugins/index.php?type=modlib');
                $rez .= "<br />";
                $rez .= "<a href=\"{$path}\">".$this->dof->get_string('modlibs', 'admin').'</a>';
                $path = $this->dof->url_im('admin','/plugins/index.php?type=workflow');
                $rez .= "<br />";
                $rez .= "<a href=\"{$path}\">".$this->dof->get_string('workflows', 'admin').'</a>';
                $path = $this->dof->url_im('admin','/plugins/setup.php');
                $rez .= "<br />";
                $rez .= "<a href=\"{$path}\">".$this->dof->get_string('plugin_setup','admin').'</a>';
                
                break;
        }
        return $rez;
    }
    /** Возвращает html-код, который отображается внутри секции
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string  - html-код содержимого секции секции
     */
    function get_section($name, $id = 1)
    {
		$rez = '';
		switch ($name)
		{
			case 'plugins'://выводим таблицу плагинов
			{
				switch ($id)
				{
					case 1: $rez .= print_plugins('storage');break;//печатаем плагины типы storage
					case 2: $rez .= print_plugins('im');break;//печатаем плагины типа im
					case 3: $rez .= print_plugins('workflow');break;//печатаем плагины типа workflow
					case 4: $rez .= print_plugins('sync');break;//печатаем плагины типа sync
					case 5: $rez .= print_plugins('modlib');break;//печатаем плагины типа modlib
				}
			}
		}
		return $rez;
    }
     /** Возвращает текст, отображаемый в блоке на странице курса MOODLE 
      * @return string  - html-код для отображения
      */
    public function get_blocknotes($format='other')
    {
		return "<a href='{$this->dof->url_im('admin','/index.php')}'>"
                    .$this->dof->get_string('page_main_name')."</a>";
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
     * Отобразить таблицу заданий
     * @param int $list - список заданий
     * @param array $addvars - доп параметры, которые нужно вносить во все ссылки в таблице
     * @param int $load - типы загрузки системы
     * @return 
     */
    function show_list($list, $addvars, $load)
    {
        // Собираем данные
        $data = array();
        if ( ! isset($list) OR ! is_array($list) )
        {// не получили список пользователей
            print('<p align="center"><i>('.$this->dof->get_string('todo_list_is_empty', 'admin').')</i></p>');
            return "";
        }
        foreach ($list as $obj ) 
        {
            $link = '';
            if ( ! $obj->exdate )
            {
                $link = '<a href="'.$this->dof->url_im('admin','/todo/delete.php',array_merge($addvars,array('todoid'=>$obj->id))).'"><img src="'.
                $this->dof->url_im('admin', '/icons/delete.png').'" alt="'.$this->dof->modlib('ig')->igs('archive').'" title="'.
                $this->dof->modlib('ig')->igs('delete').'"></a>&nbsp;';
            }
            $loadstring = '';    
            if ( isset($load[$obj->loan]) )
            {// есть loan - выведем строкой
                $loadstring = $load[$obj->loan]; 
            }
            $data[] = array($link,
                            $obj->id,
                            $obj->plugintype,
                            $obj->plugincode,
                            $obj->todocode,
                            $obj->intvar,
                            $loadstring,
                            dof_userdate($obj->tododate,'%d.%m.%Y-%H:%M'));
		}
        // Рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        // $table->align = array ("center","center","center", "center", "center");
        // $table->wrap = array ("nowrap","","","");
        $table->cellpadding = 9;
        $table->cellspacing = 0;
        $table->width = '95%';
        $table->head = array($this->dof->get_string('actions','admin'),
                             '<a href="'.$this->dof->url_im('admin','/todo/list.php',array_merge($addvars,array('sort'=>'id'))).'">'.$this->dof->get_string('id','admin').'</a>',
                             '<a href="'.$this->dof->url_im('admin','/todo/list.php',array_merge($addvars,array('sort'=>'plugintype'))).'">'.$this->dof->get_string('plugin_type','admin').'</a>',
                             '<a href="'.$this->dof->url_im('admin','/todo/list.php',array_merge($addvars,array('sort'=>'plugincode'))).'">'.$this->dof->get_string('plugin_code','admin').'</a>',
                             '<a href="'.$this->dof->url_im('admin','/todo/list.php',array_merge($addvars,array('sort'=>'todocode'))).'">'.$this->dof->get_string('todocode','admin').'</a>',
                             $this->dof->get_string('dopparam','admin','<br>'),
                             $this->dof->get_string('loadsys','admin'),
                             '<a href="'.$this->dof->url_im('admin','/todo/list.php',array_merge($addvars,array('sort'=>'tododate'))).'">'.$this->dof->get_string('time','admin').'</a>');
        $table->data = $data;
        //передали данные в таблицу
        return $this->dof->modlib('widgets')->print_table($table,true);
	}
}