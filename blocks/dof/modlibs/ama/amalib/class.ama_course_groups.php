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
//Все в этом файле написано на php5.
//Проверяем совместимость с ПО сервера
if ( 0 > version_compare(PHP_VERSION, '5') )
{
    die('This file was generated for PHP 5'); //если ниже php5, то кончаем работу
}
//Подключаем класс для работы с курсом
require_once('class.ama_course.php');

/**
 * Класс для работы с группами курса
 * @access public
 */
class ama_course_groups
{

    /**
     * @var int id группы в moodle
     */
    protected $id = null;

    /**
     * @var int id курса в Moodle
     */
    protected $courseid = null;

    /** Конструктор
     * @param int $id - id группы, с которой предстоит работать 
     * если id не передан - создание новой группы
     * @access public
     */
    public function __construct($courseid, $groupid = null)
    {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/group/lib.php');
        if ( $courseid )
        {
            $this->courseid = $courseid; //Если с id курса всё в порядке, то записываем его в обьект 
            if ( is_null($groupid) )
            {
                // Создаем новую группу
                $group = $this->template();
                $groupname = $group->name;
                $i = 1;
                //Добиваемся уникальности короткого имени
                while ( $DB->record_exists('groups', array('name' => $group->name)) )
                {
                    $group->name = $groupname . $i;
                    ++$i;
                }

                // Создаем группу в БД
                $rec = groups_create_group($group); //записываем ее в БД
                if ( $rec )
                {
                    //устанавливаем текущий id
                    $this->id = $rec;
                } else
                {
                    return false;
                }
            } elseif ( $groupid === false )
            {
                $this->id = false; // для поисковых и вспомогательных операций - не требуется создавать или брать из БД группу
            } elseif ( (int) ($groupid) )
            {
                if ( $DB->get_record('groups', array('id' => $groupid)) )
                {
                    $this->id = $groupid;
                } else
                {// группы с таким id не существует
                    dof_mtrace(3, 'ama_course_groups:Error group opened: group not exists');
                    $this->id = false;
                }
            } else
            {// переданный id группы не целочисленный
                dof_mtrace(3, "ama_course_groups:Group id must be integer!");
                $this->id = false;
            }
        } else
        {// не указан id курса
            dof_mtrace(3, 'ama_course_groups:Course id is not specified.');
        }
    }

    public function is_exists($id = null)
    {
        global $DB;
        if ( is_null($id) )
        {
            $id = $this->get_id();
        }
        if ( ama_utils_is_intstring($id) )
        {// переланный id курса является числом, все нормально
            $id = intval($id);
        } else
        {// переданный id не является числом - вернем всесто него 0
            $id = 0;
        }
        return $DB->record_exists('groups', array('id' => intval($id)));
    }

    /**
     * Возвращает параметры группы
     * @param int $id - id группы, по которой запрашивается информация
     * @return stdClass Object - обьект, содержащий данные о группе
     */
    public function get()
    {// в moodle отсутствует API функция для того чтобы получить конкретно запись из базы, 
        // поэтому делаем это напрямую
        global $DB;
        if ( !$this->get_id() )
        {
            return false; // непонятно, какую группу извлекать
        }
        return $DB->get_record('groups', array('id', $this->get_id()));
    }

    /**
     * Возвращает id группы
     * @return int id группы
     * @access public
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * Возвращает id курса, в котором находится группа 
     */
    public function get_courseid()
    {
        return $this->courseid;
    }

    /**
     * Обновить данные о группе, или создать новую группу
     * @param stdClass Object - обьект содержащий данные об обновляемой группе
     * 
     * @return id обновленной или созданной группы, или false в случае ошибки
     */
    public function update($data)
    {
        if ( $this->id === false )
        {
            return false; // запрошена другая операция
        }
        if ( $this->is_unique($data) )
        {
            $data->id = $this->get_id();
            $data->courseid = $this->courseid;
            if ( (bool) groups_update_group($data) )// Запись уникальна, обновляем базу
            {
                return $this->get_id(); // возвращаем id обновленной, или созданной записи
            } else
            {
                return false; // в процессе обновления произошла ошибка
            }
        } else
        {
            return false; // запись не уникальна
        }
    }

    /**
     * Удаляет группу в курсе
     * @param int $groupid - id группы, которую нужно удалить
     * 
     * @return true если группа успешно удалена и false в случае ошибки 
     */
    public function delete()
    {
        if ( $this->id === false )
        {
            return false; // запрошена другая операция
        }
        if ( $this->id )
        {
            return groups_delete_group($this->get_id());
        } else
        {
            return false; // id удаляемой записи не передан, значит невозможно выполнить операцию 
        }
    }

    /**
     * Осуществляет поиск указанной группы по имени и маске
     * @todo дописать функцию поиска
     */
    public function search($name, $onlyid = true)
    {
        $namelow = strtolower($name); //перевели имя внижний регистр
        $nameup = strtoupper($name); //перевели имя в верхний регистр
        if ( $this->id === false )
        {//найдем группу по имени';
            //возвращает только id группы
            if ( $rez = groups_get_group_by_name($this->courseid, $name) )
            {//полученное имя совпало с именем группы
                return $rez;
            } elseif ( $rez = groups_get_group_by_name($this->courseid, $namelow) )
            {//имя группы совпало с именем в нижнем регистре 
                return $rez;
            } elseif ( $rez = groups_get_group_by_name($this->courseid, $nameup) )
            {//имя группы совпало с именем в верхнем регистре
                return $rez;
            }//имя так и не совпало
            return false;
        } else
        {
            return false;
        }
    }

    /** Возвращает массив всех членов одной группы курса
     * @access public
     * @param 
     * @param bool $onlyids - передавать только id, или полную информацию о пользователях?
     * @return array массив id членов группы
     * 	если членов нет - возвращает пустой массив
     * @todo доделать вариант с onlyids
     */
    public function members($groupid = null, $onlyids = true)
    {
        if ( $this->get_id() )
        {
            $groupid = $this->get_id();
        } elseif ( !isset($groupid) or ! (int) $groupid )
        {
            return false;
        }

        $members = array();
        if ( !$members = groups_get_members($groupid) )// получаем всех пользователей группы
        {//нет членов группы
            $members = array();
        }
        return $members;
    }

    /**
     * Добавляет пользователя в группу
     * @param int $userid - id пользователя, которого добавляют в группу
     * 
     * @return true если пользователь добавлен успешно, либо уже находится в группе, и false в остальных случаях
     */
    public function add_member($userid)
    {
        if ( $this->id === false )
        {
            return false; // поскольку не передан id группы, мы не можем добавить туда пользователя
        }
        return groups_add_member($this->id, $userid);
    }

    /**
     * Удаляет пользователя из группы
     * @param int $userid - id пользователя, которого добавляют в группу
     * 
     * @return true если пользователь успешно удален, или false в случае неудачи 
     */
    public function remove_member($userid)
    {
        if ( $this->id === false )
        {
            return false; // поскольку не передан id группы, мы не можем удалить оттуда пользователя
        }
        return groups_remove_member($this->id, $userid);
    }
    
    /*****************************/
    // Собственные методы класса //
    /*****************************/
    /** 
     * Возвращает информацию по умолчанию о группе
     * Это значения полей по умолчанию для таблицы _groups
     * @access protected
     * @param object $data - массив значений, которые переопределяют 
     * соответствующие параметры по умолчанию 
     * @return stdClass object параметры по умолчанию для новой группы
     */
    protected function template($data = NULL)
    {
        $group = new stdClass;
        $group->courseid = $this->courseid;
        $group->name = 'Новая группа-' . date('ymdhis', time()) . substr(md5($_SERVER['REMOTE_ADDR'] . $_SERVER['REMOTE_PORT'] . microtime()), 0, 2);
        $group->description = '';
        $group->enrolmentkey = '';
        $group->picture = 0;
        $group->hidepicture = 0;
        $group->timecreated = time();
        $group->timemodified = time();
        if ( !is_null($data) )
        {
            if ( isset($data->courseid) )
            {
                unset($data->courseid);
            }
            if ( isset($data->id) )
            {
                unset($data->id);
            }
            foreach ( $data as $key => $val )
            {
                $group->$key = $val;
            }
        }
        return $group;
    }

    /**
     * Проверяет уникальность обновляемой записи
     * @param stdClass Object $data - обьект, который мы собираемся вставить в базу
     * @return true or false
     */
    protected function is_unique($data)
    {
        global $DB;
        if ( $DB->record_exists('groups', array('name' => $data->name)) )
        {
            return false;
        } else
        {
            return true;
        }
    }

    /**
     * Создает группу в курсе. За счет "особой" структуры модуля ama - эта операция пока что 
     * должна выполнятся командой update. Надеюсь, в будующей версии мы это исправим.
     * Пока не используется.
     * @param string $groupname имя создаваемой группы
     * 
     * @return int id созданной группы
     */
    /* public function create_group($groupname)
      {
      $groupname = addslashes($groupname); // обязательно экранировать все кавычки перед добавлением

      $data = new stdClass();
      $data->name = $groupname;
      return groups_create_group($data); // используем API Moodle
      } */
}
?>