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

/**
 * Интерфейс комментариев к объектам Деканата
 * 
 * @package    im
 * @subpackage comments
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_im_comments implements dof_plugin_im
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
        // Обновим права доступа
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());  
    }
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
		return 2015052900;
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
        return 'angelfish';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'im';
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
        return 'comments';
    }
    
    /** 
     * Возвращает список плагинов, без которых этот плагин работать не может
     * 
     * @return array
     */
    public function need_plugins()
    {
        return array(
                'modlib' => array(
                        'nvg'         => 2008060300,
                        'widgets'     => 2009050800
                ),
                'storage' => array(
                        'comments'    => 2015050000,
                        'departments' => 2009040800,
                        'config'      => 2011080900,
                        'acl'         => 2011040504
                ),
                'workflow' => array(
                        'comments'    => 2009060800
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
                        'acl' => 2011040504
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
                array(
                        'plugintype' => 'im',
                        'plugincode' => 'persons',
                        'eventcode'  => 'persondata'
                )
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
         
        switch ( $do )
        {// Определяем дополнительные параметры в зависимости от запрашиваемого права
            default:
                break;
        }
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
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "comments/{$do} (block/dof/im/comments: {$do})";
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
        if ( $gentype == 'im' AND $gencode == 'persons' AND $eventcode == 'persondata' )
        {
            
        }
        return false;
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
    
    // **********************************************
    // Методы, предусмотренные интерфейсом im
    // **********************************************
    
    /** 
     * Возвращает текст для отображения в блоке на странице dof
     * 
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * 
     * @return string - html-код содержимого блока
     */
    public function get_block($name, $id = 1)
    {
        $rez = '';
        switch ($name)
        {
            default:  
                break;
        }
        return $rez;
    }
    
    /** 
     * Возвращает html-код, который отображается внутри секции
     * 
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * 
     * @return string  - html-код содержимого секции секции
     */
    public function get_section($name, $id = 1)
    {
        $rez = '';
        switch ($name)
        {

        }
        return $rez;
    }
    
    /** 
     * Возвращает текст, отображаемый в блоке на странице курса MOODLE 
     * 
     * @return string  - html-код для отображения
     */
    public function get_blocknotes($format='other')
    {
        return "<a href='{$this->dof->url_im('comments','/index.php')}'>"
                    .$this->dof->get_string('page_main_name')."</a>";
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
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        return $a;
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** 
     * Получить URL к собственным файлам плагина
     * 
     * @param string $adds[optional] - фрагмент пути внутри папки плагина
     *                                 начинается с /. Например '/index.php'
     * @param array $vars[optional] - параметры, передаваемые вместе с url
     * 
     * @return string - путь к папке с плагином 
     */
    public function url($adds='', $vars=array())
    {
        return $this->dof->url_im($this->code(), $adds, $vars);
    }
    
    /**
     * Отобразить блок комментариев для объекта деканата
     *
     * @param string $ptype - Тип плагина
     * @param string $pcode - Код плагина
     * @param int $objectid - ID объекта для которого формируется блок
     * @param string $code - Сабкод плагина
     * @param array $addvars - Массив GET-параметров
     * @param array $options - Массив дополнительных параметров отображения
     *      ['return_html' => true] - Вернуть HTML-код вместо отображения
     */
    public function commentsform($ptype, $pcode, $objectid, $addvars, $code = NULL, $options = [] )
    {
        // Подключение формы
        require_once $this->dof->plugin_path('im', 'comments', '/form.php');
        
        if ( ! isset($addvars['departmentid']) )
        {// Подразделение не указано
            $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }
        
        // Получим объект формы
        $url = $this->dof->url_im('comments','/comment.php', $addvars);
        $customdata = new stdClass();
        $customdata->dof = $this->dof;
        $customdata->plugintype = $ptype;
        $customdata->plugincode = $pcode;
        $customdata->objectid = $objectid;
        $customdata->code = $code;
        $customdata->id = optional_param('commentid', 0, PARAM_INT);
        $customdata->task = optional_param('commenttask', 'create', PARAM_TEXT);
        $customdata->returnurl = $_SERVER['REQUEST_URI'];
        $form = new dof_im_comments_form($url, $customdata);
        
        if ( isset($options['return_html']) && $options['return_html'] = true )
        {// Вместо отображения вернуть html-код списка
            $html = $form->render();
            // Отобразим блок с комментариями
            $html .= $this->show_comments_list($ptype, $pcode, $objectid, $code, $options);
            return $html;
        } else 
        {
            $form->display();
            // Отобразим блок с комментариями
            $this->show_comments_list($ptype, $pcode, $objectid, $code, $options);
        }
    }
    
    /**
     * Отобразить список комментариев
     * 
     * @param string $ptype - Тип плагина
     * @param string $pcode - Код плагина
     * @param int $objectid - ID объекта для которого формируется блок
     * @param string $code - Сабкод плагина
     * @param array $options - Массив дополнительных параметров отображения
     *      ['return_html' => true] - Отобразить html-код списка комментариев
     *      ['disable_actions' => true] - Не показывать действия над комментариями
     * 
     * @return string|void
     */
    public function show_comments_list($ptype, $pcode, $objectid, $code = NULL, $options = [])
    {
        // Подключение генератора HTML
        $this->dof->modlib('widgets')->html_writer();
        
        // Получим текущую персону
        $person = $this->dof->storage('persons')->get_bu();
        
        // Получим все комментарии по объекту
        $comments = $this->dof->storage('comments')->
            get_comments_by_object($ptype, $pcode, $objectid, $code);
        
        $commentslist = '';
        
        // Получили текущее подразделение
        $departmentid = optional_param('departmentid', 0, PARAM_INT);
        if ( ! empty($comments) )
        {
            foreach ( $comments as $comment )
            {
                // Получаем имя автора
                $username = $this->dof->storage('persons')->get_fullname($comment->personid);
                // Получим время отправки сообщения с учетом временной зоны пользователя
                $posttime = $this->dof->storage('persons')->get_userdate($comment->date, '%d.%m.%Y %H:%M', $comment->personid);
                
                // Получим кнопки редактирования
                $postedit = '';
                if ( ! isset($options['disable_actions']) || $options['disable_actions'] = false )
                {// Отобразить действия
                    if ( $this->dof->storage('comments')->is_access('edit/owner', $comment->id) )
                    {// Есть право на редактирование комментария
                        $_GET['commenttask'] = 'edit';
                        $_GET['commentid'] = $comment->id;
                        $query = http_build_query($_GET, null, '&');
                        $link = $_SERVER['PHP_SELF'].'?'.$query;
                        
                        $postedit .= dof_html_writer::link($link, get_string('edit'), array('class' => 'dof_comment_linkedit')).'  ';
                    }
                    if ( $this->dof->storage('comments')->is_access('delete/owner', $comment->id) )
                    {// Есть право на редактирование комментария
                        $_GET['commenttask'] = 'delete';
                        $_GET['commentid'] = $comment->id;
                        $query = http_build_query($_GET, null, '&');
                        $link = $_SERVER['PHP_SELF'].'?'.$query;
                        
                        $postedit .= dof_html_writer::link($link, get_string('delete'), array('class' => 'dof_comment_linkdelete'));
                    }
                }
                
                // Получим базовые блоки
                $comment_text = dof_html_writer::div($comment->text, 'dof_comment_text');
                $comment_name = dof_html_writer::div($username, 'dof_comment_name', array('style' => ''));
                $comment_time = dof_html_writer::div($posttime, 'dof_comment_time',
                        array('style' => 'float: right;font-size: 75%;padding: 5px 10px;font-style: italic;width: auto;'));
                $comment_edit = dof_html_writer::div($postedit, 'dof_comment_edit',
                        array('style' => 'float: right;font-size: 75%;padding: 5px 10px;font-style: italic;width: auto;'));
                $comment_clear = dof_html_writer::div('', 'dof_comment_clear', array('style' => 'clear: both'));
        
                // Сформируем блок
                $comment_head = dof_html_writer::div($comment_name, 'dof_comment_head',
                        array('style' => 'padding: 5px 10px; background: #EBEBEB;font-size: 80%;'));
                $comment_content = dof_html_writer::div($comment_text, 'dof_comment_content', array('style' => 'padding: 10px;border-bottom: 1px solid #ccc;'));
                $comment_footer = dof_html_writer::div($comment_time.$comment_edit.$comment_clear, 'dof_comment_footer', array('style' => ''));
        
                $commentblock = dof_html_writer::div(
                        $comment_head.$comment_content.$comment_footer,
                        'dof_commentblock',
                        array('style' => 'border: 1px solid #ccc;')
                );
                $commentslist .= $commentblock;
            }
            $commentslist = dof_html_writer::div($commentslist, 'dof_commentsblock');
        }
        
        
        if ( isset($options['return_html']) && $options['return_html'] = true )
        {// Вместо отображения вернуть html-код списка
            return $commentslist;
        }
        print($commentslist);
    }
}