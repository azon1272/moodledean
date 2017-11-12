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

/** Класс для работы с ролями курса
 * @todo предусмотреть возможность подписки на курс используя не только плагин moodle_dean
 */
class ama_course_roles
{

    //id курса, в котором должна быть определена роль
    protected $courseid = 0;
    protected $roleid = 0;

    /** Плагин подписки по умолчанию
     * @var enrol_plugin|moodle_dean_plugin
     */
    protected $enrol;

    /** конструктор класса - создает объект от текущего класса
     * @access public
     * @param int $courseid - id курса, с которым собираются работать
     * @param int $roleid[optional] - id роли (в таблице mdl_role) которая будет назначена пользователю 
     *                      при записи на курс. 
     *                      Если роль не указана - то она берется из настроек плагина moodle_dean
     * @return null
     */
    public function __construct($courseid, $roleid = false, $type = 'student')
    {
        $this->courseid = intval($courseid);

        $enrol = enrol_get_plugin('dof');
        //print_object($enrol);die;
        if ( empty($enrol) )
        {// плагин enrol/dof не установлен - а он обязательно нужен, потому что вся подписка происходит
            // через него
            throw new dof_exception('dofpluginnotinstalled', 'moodle_dean');
        }

        $this->enrol = $enrol;
        // Выбираем роль
        if ( $roleid === false )
        {// Берем из настроек плагина подписки роль по умолчанию в зависимости от типа
            switch ( $type )
            {
                case 'student':
                    $this->roleid = $enrol->get_config('roleid');
                    break;
                case 'teacher':
                    $this->roleid = $enrol->get_config('teacherroleid');
                    break;
                case 'redactor':
                    $this->roleid = $enrol->get_config('editingroleid');
                    break;
            }
        } else
        {// Нам уже передали роль
            $this->roleid = intval($roleid);
        }
    }

    /** Возвращает список ролей, определенных в текущем  контексте 
     * @access public
     * @return array массив ролей
     */
    public function roles()
    {
        
    }

    /** Возвращает список пользователей, которые 
     * имеют указанную роль в текущем контексте
     * @access public
     * @param int $roleid - id роли
     * @return array - массив id пользователей
     */
    public function assigned($roleid)
    {
        $returnvalue = array();
        return (array) $returnvalue;
    }

    /** Подписываем пользователя на курс с ролью по-умолчанию
     * @access public
     * @param int $userid - id пользователя
     * @param int $timeend - метка времени окончания пребывания указанного пользователя 
     * @param bool $deprecated - скрыть (true) пользователя под этой ролью или нет (false)
     *             параметр не используется. Удалить его при рефакторинге
     * @return bool true - назначение прошло успешно
     * false в иных случаях
     * 
     * @todo удалить лишние параметры при рефакторинге
     * @todo добавить параметр $timestart, чтобы можно было создать подписку которая начнет действовать
     *       только через некоротое время
     */
    public function enrol($userid, $timeend = 0, $deprecated = null)
    {
        global $DB, $DOF;

        if ( !$instance = $this->get_course_enrol_instance() )
        {// если используемый плагин подписки недоступен в переданном курсе - то сначала включим его
            $course = $DB->get_record('course', array('id' => $this->courseid));
            $instanceid = $this->enrol->add_instance($course);
            $instance = $DB->get_record('enrol', array('id' => $instanceid));
        }
        // Выполняем подписку, используя плагин moodle_dean
        $this->enrol->enrol_user($instance, $userid, $this->roleid, $timestart = 0, $timeend);
        // Функция подписки пользователя не возвращает значений,
        // уведомление об отписке происходит через события Moodle.
        // Если мы хотим это отслеживать - то следует позаботится об этом, прописав отслеживание событий в block_dof
        // Записываем в логи
        $DOF->add_to_log('modlib', 'ama', 'enrol', 'view.php?id=' . $this->courseid, '');
        
        return true;
    }

    /** Определяет, включен ли используемый плагин подписки в переданном курсе
     * @todo предусмотреть вариант с доступным, но не включенным плагином подписки
     * 
     * @return bool|object - объект, который хранит данные о плагине подписки в курсе
     *                       или false если плагин в курсе не включен
     */
    protected function get_course_enrol_instance()
    {
        // получаем все плагины подписки, доступные в этом курсе
        $instances = enrol_get_instances($this->courseid, false);
        // получаем название используемого в текущий момент плагина подписки
        $myinstancename = $this->enrol->get_name();
        foreach ( $instances as $instance )
        {// проверяем, есть ли плагин moodle_dean в списке разрешенных к использованию в курсе
            if ( $instance->enrol == $myinstancename )
            {
                return $instance;
            }
        }
        // просмотрели все плагины, но не нашли нашего - значит он не доступен в курсе
        return false;
    }

    /** Отписываем пользователя с курса
     * @access public
     * @param  int $userid - id пользователя
     * @return bool true - пользователь успешно отчислен false в иных случаях 
     * 
     * @todo пока непонятно что делать с параметром $anyenrol. В Moodle 2.x нет возможности отписать
     *       пользователя, убрав все типы подписок на курс. Выяснить такой способ при рефакторинге
     * @todo предусмотреть вариант с доступным, но отключенным плагином подписки
     */
    public function unenrol($userid)
    {
        // отписываем пользователя
        $context = context_course::instance($this->courseid);
        role_unassign($this->roleid, $userid, $context->id, '', NULL);
        // найдем еще роли пользователя
        $roles = get_user_roles($context, $userid);
        if ( empty($roles) )
        {// ролей больше нет - убираем подписку пользователю
            if ( !$instance = $this->get_course_enrol_instance() )
            {
                return true;
            }

            $this->enrol->unenrol_user($instance, $userid);
        }
        // Функция отписки пользователя не возвращает значений,
        // уведомление об отписке происходит через события Moodle,
        // поэтому всегда считаем что операция прошла успешно
        return true;
    }

}
