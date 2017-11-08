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
 * Интерфейс управления деревом компетенций
 * 
 * @package    im
 * @subpackage skills
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_im_skills implements dof_plugin_im
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
                'modlib' => array(
                        'nvg'         => 2008060300,
                        'widgets'     => 2009050800
                ),
                'storage' => array(
                        'skills'      => 2015060000,
                        'skilllinks'  => 2015060000,
                        'config'      => 2011080900,
                        'acl'         => 2011040504
                ),
                'workflow' => array(
                        'skills'      => 2015060000
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
                'modlib' => array(
                        'nvg'         => 2008060300,
                        'widgets'     => 2009050800
                ),
                'storage' => array(
                        'skills'      => 2015060000,
                        'skilllinks'  => 2015060000,
                        'config'      => 2011080900,
                        'acl'         => 2011040504
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
        $acldata = $this->get_access_parametrs($do, $objid, $personid);

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
    public function get_section($name, $id = 0)
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
     * Возвращает текст, отображаемый в блоке на странице курса MOODLE 
     * 
     * @return string  - html-код для отображения
     */
    public function get_blocknotes($format='other')
    {
        return "<a href='{$this->dof->url_im('skills','/index.php')}'>"
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
     * Задаем права доступа для объектов
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        $a['view'] = array(
                'roles' => array( 
                    'manager'
                )
        );
        $a['edit'] = array(
                'roles' => array(
                    'manager'
                )
        );
        $a['delete'] = array(
                'roles' => array(
                        'manager'
                )
        );
        $a['editlinks'] = array(
                'roles' => array(
                    'manager'
                )
        );
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
     * Напечатать таблицу управления компетенциями
     * 
     * @param array $addvars - массив GET параметров
     * 
     */
    public function print_tree($addvars)
    {
        // Получим ID родительского элемента
        $parentid = optional_param('parentskill', 0, PARAM_INT);
        
        // Проверка доступа
        if ( ! $this->dof->im('skills')->is_access('view', $parentid) )
        {// Доступа к просмотру компетенции и его дочерних элементов нет
            return;
        }

        // Получим родительский элемент
        $parent = $this->dof->storage('skills')->get($parentid);
        
        // Получим писок дочерних элементов
        $statuses = $this->dof->workflow('skills')->get_meta_list('real');
        $statuses = array_keys($statuses);
        $list = $this->dof->storage('skills')->get_records(array('parentid' => $parentid, 'status' => $statuses));
        
        // Добавить компетенцию
        $addlink = dof_html_writer::link(
                $this->dof->url_im('skills', '/edit.php', $addvars),
                $this->dof->get_string('table_skills_tree_add', 'skills')
        );
        echo dof_html_writer::tag('h3', $addlink);
        
        // Формируем таблицу
        $table = new stdClass;
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->align = array ("center","center","center","center","center","center");
        $table->size = array ("5%","25%","20%","10%","20%","20%");
        $table->wrap = array (true, true, true, true, true, true);
        // Шапка таблицы
        $table->head = array(
                $this->dof->get_string('table_skills_tree_counter', 'skills'),
                $this->dof->get_string('table_skills_tree_name', 'skills'),
                $this->dof->get_string('table_skills_tree_comlexity', 'skills'),
                $this->dof->get_string('table_skills_tree_self', 'skills'),
                $this->dof->get_string('table_skills_tree_path', 'skills'),
                $this->dof->get_string('table_skills_tree_status', 'skills')
        );
        
        // Заносим данные   
        $table->data[] = array();
        // Параметры для редактирования
        $somevars = $addvars;
        // Родитель
        if ( ! empty($parent) )
        {// Есть родитель
            
            // Ссылка назад
            $backlinkvars = $addvars;
            $backlinkvars['parentskill'] = $parent->parentid;
            $backlink = dof_html_writer::link(
                    $this->dof->url_im('skills', '/list.php', $backlinkvars),
                    $this->dof->get_string('table_skills_tree_back', 'skills') 
            );
            echo dof_html_writer::tag('h3', $backlink);
            
            $data = array();
            $actions = '';
            if ( $this->dof->im('skills')->is_access('edit', $parent->id) )
            {// Ссылка на редактирование компетенции
                $somevars['id'] = $addvars['parentskill'];
                $actions .= $this->dof->modlib('ig')->icon(
                        'edit', 
                        $this->dof->url_im('skills', '/edit.php', $somevars)
                );
            }
            if ( $this->dof->im('skills')->is_access('delete', $parent->id) )
            {// Ссылка на удаление компетенции
                $somevars['id'] = $addvars['parentskill'];
                $actions .= $this->dof->modlib('ig')->icon(
                    'delete',
                    $this->dof->url_im('skills', '/delete.php', $somevars)
                );
            }
            $data[] = $actions;
            $data[] = $parent->name;
            $data[] = $parent->complexity;
            if ( empty($parent->self) )
            {
                $data[] = $this->dof->get_string('table_skills_tree_self_0', 'skills');
            } else 
            {
                $data[] = $this->dof->get_string('table_skills_tree_self_1', 'skills');
            }
            $data[] = $parent->path;
            $data[] = $this->dof->workflow('skills')->get_name($parent->status);
            $table->data[] = $data;
            
            // Печать таблицы с родителем
            $this->dof->modlib('widgets')->print_table($table);
            // Заголовок дочерних компетенций
            echo dof_html_writer::tag('h3', $this->dof->get_string('table_skills_tree_childrens', 'skills'));
        }
        $table->data = array();
        foreach ( $list as $skill )
        {// Есть родитель
            if ( ! $this->dof->im('skills')->is_access('view', $skill->id) )
            {// Доступа к просмотру компетенции нет
                continue;
            }
            
            $data = array();
            $cell = '';
            if ( $this->dof->im('skills')->is_access('edit', $skill->id) )
            {// Ссылка на редактирование компетенции
                $somevars['id'] = $skill->id;
                $cell .= $this->dof->modlib('ig')->icon(
                        'edit', 
                        $this->dof->url_im('skills', '/edit.php', $somevars) 
                );
            }
            if ( $this->dof->im('skills')->is_access('delete', $skill->id) )
            {// Ссылка на удаление компетенции
                $somevars['id'] = $skill->id;
                $cell .= $this->dof->modlib('ig')->icon(
                    'delete',
                    $this->dof->url_im('skills', '/delete.php', $somevars)
                );
            }
            $data[] = $cell;
            
            $addvars['parentskill'] = $skill->id;
            $link = dof_html_writer::link($this->dof->url_im('skills', '/list.php', $addvars), $skill->name);
            $data[] = $link;
            
            $data[] = $skill->complexity;
            if ( empty($skill->self) )
            {
                $data[] = $this->dof->get_string('table_skills_tree_self_0', 'skills');
            } else
            {
                $data[] = $this->dof->get_string('table_skills_tree_self_1', 'skills');
            }
            $data[] = $skill->path;
            $data[] = $this->dof->workflow('skills')->get_name($skill->status);
            $table->data[] = $data;
        }
        $this->dof->modlib('widgets')->print_table($table);
        return;
    }
    
    /**
     * Получить список компетенций с учетом отступа
     *
     * @param $id - ID - элемента, от которого необходимо строить дерево
     * @param $level - Уровень вложенности
     *
     * @return array - Массив компетенций
     */
    public function get_skills_padding_list($id = 0, $level = 0)
    {
        $result = array();
        // Получим прямых наследников от элемента
        $statuses = $this->dof->workflow('skills')->get_meta_list('real');
        $statuses = array_keys($statuses);
        $skills = $this->dof->storage('skills')->get_records(array('parentid' => $id, 'status' => $statuses), '', 'id, name, self');
        
        if ( ! empty($skills) )
        {// Сформируем массив
            // Получим отступ
            $shift = str_pad('', $level, '-');
            foreach ( $skills as $skill )
            {
                // Сформируем элемент
                if ( $skill->self == 0 )
                {// Компетенция не самостоятельная
                    $result[$skill->id] = $shift.'['.$skill->name.']';
                } else
                {// Самостоятельная компетенция
                    $result[$skill->id] = $shift.$skill->name;
                }
                // Получим массид дочерних
                $childrens = $this->get_skills_padding_list($skill->id, $level + 1);
                // Добавим к исходному
                $result = $result + $childrens;
            }
        }
    
        return $result;
    }
    
    /**
     * Разбить компетенции для формы назначений
     * 
     * Разбивает массив компетенций на два списка - доступные компетенции и имеющиеся
     * Необходим для формирования формы назначения компетенций
     *
     * @param $list - Массив компетенций ( ID => Имя )
     * @param $ptype - Тип плагина, для объекта которого разбивается массив
     * @param $pcode - Код плагина, для объекта которого разбивается массив
     * @param $code - Сабкод плагина, определение кода объекта
     * @param $id - ID объекта для которого разбивается массив
     *
     * @return array - Массив компетенций объекта и массив доступных компетенций
     */
    public function explode_skills_list($list, $ptype, $pcode, $code, $id)
    {
        if ( ! is_array($list) )
        {// Передан не массив
            return false;
        }
        // Инициализируем массивы
        $available = $list;
        $added = array();
        
        // Получим список прилинкованных к объекту компетенций
        $conds = array();
        $conds['plugintype'] = $ptype;
        $conds['plugincode'] = $pcode;
        $conds['code'] = $code;
        $conds['objectid'] = $id;
        $skilllinks = $this->dof->storage('skilllinks')->get_records($conds, '', ' id, skillid ');
        if ( ! empty($skilllinks) )
        {// Есть прилинкованные компетенции
            // Cформируем массив компетенций
            $skillids = array();
            foreach( $skilllinks as $link )
            {
                $skillids[$link->skillid] = $link->skillid;
            }
            // Получим отсортированный список
            $skills = $this->dof->storage('skills')->get_records(array('id' => $skillids), 'path ASC', 'id');
            foreach ( $skills as $skill )
            {
                // Убираем компетенцию из доступных
                unset($available[$skill->id]);
                // Добавляем в назначенные
                $added[$skill->id] = $list[$skill->id];
            }
        }
    
        // Формируем итоговый массив
        $return = array($added, $available);
        return $return;
    }
    
    /**
     * Добавить компетенции из массива к объекту
     *
     * @param $list - Массив компетенций ( Ключ => ID компетенции )
     * @param $ptype - Тип плагина
     * @param $pcode - Код плагина
     * @param $code - Сабкод плагина
     * @param $objectid - ID объекта для которого добавляются компетенции
     *
     * @return array - Массив компетенций объекта и массив доступных компетенций
     */
    public function addselect($list, $ptype, $pcode, $code, $objectid)
    {
        if ( ! is_array($list) )
        {// Передан не массив
            return '';
        }
        
        $result = true;
        // Обновим компетенции для объекта
        foreach( $list as $id )
        {
            $result = ( $result && $this->dof->storage('skilllinks')->save($id, $ptype, $pcode, $code, $objectid));
        }
    }
    
    /**
     * Удалить компетенции из массива у объекта
     *
     * @param $list - Массив компетенций ( Ключ => ID компетенции )
     * @param $ptype - Тип плагина
     * @param $pcode - Код плагина
     * @param $code - Сабкод плагина
     * @param $objectid - ID объекта для которого удаляются компетенции
     *
     * @return array - Массив компетенций объекта и массив доступных компетенций
     */
    public function removeselect($list, $ptype, $pcode, $code, $objectid)
    {
        if ( ! is_array($list) )
        {// Передан не массив
            return '';
        }
    
        $result = true;
        // Обновим компетенции для объекта
        foreach( $list as $id )
        {
            $result = ( $result && $this->dof->storage('skilllinks')->remove($id, $ptype, $pcode, $code, $objectid));
        }
    }
    

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
        // Подключаем библиотеки
        require_once('lib.php');

        // Обработчик действий
        $message = '';
        if ( isset($_POST[$formid]) )
        {// Обработчик текущей формы
            if ( isset($_POST['addselect']) )
            {
                $addselect = $_POST['addselect'];
                $message .= $this->dof->im('skills')->addselect(
                        $addselect,
                        $ptype,
                        $pcode,
                        $code,
                        $objectid
                );
            }
            if ( isset($_POST['removeselect']) )
            {
                $removeselect = $_POST['removeselect'];
                $message .= $this->dof->im('skills')->removeselect(
                        $removeselect,
                        $ptype,
                        $pcode,
                        $code,
                        $objectid
                );
            }
        }
        
        // Формируем поле с привязкой компетенций
        $this->dof->modlib('widgets')->addremove();
        $addremove = new dof_modlib_widgets_addremove($this->dof, '', $formid);
        
        // Опции формы
        $values = new stdClass();
        $values->title       = $this->dof->get_string('form_editlinks_title', 'skills');
        $values->removelabel = $this->dof->get_string('form_editlinks_added', 'skills');
        $values->addlabel    = $this->dof->get_string('form_editlinks_available', 'skills');
        $values->addarrow    = $this->dof->get_string('form_editlinks_add', 'skills');
        $values->removearrow = $this->dof->get_string('form_editlinks_delete', 'skills');
        
        // Добавим опции
        $addremove->set_default_strings($values);
        
        // Сформируем списки
        $fulllist = $this->dof->im('skills')->get_skills_padding_list();
        list($added, $available) = $this->dof->im('skills')->explode_skills_list(
                $fulllist, 
                $ptype,
                $pcode,
                $code,
                $objectid
        );
        
        $addremove->set_add_list($available);
        $addremove->set_remove_list($added);
        
        // Вывод сообщений
        echo $message;
        // Отображение формы привязки
        $addremove->print_html();
    }
}