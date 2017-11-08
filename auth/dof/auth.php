<?php
use core\plugininfo\message;
// This file is not a part of Moodle - http://moodle.org/
// This is a none core contributed module.
//
// This is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License
// can be see at <http://www.gnu.org/licenses/>.

/**
 * Плагин аутентификации Деканата. Класс плагина.
 *
 * @package    auth
 * @subpackage dof
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');

class auth_plugin_dof extends auth_plugin_base 
{
    /**
     * Конструктор плагина
     */
    public function auth_plugin_dof() 
    {
        // Код плагина
        $this->authtype = 'dof';
        // Конфигурация плагина
        $this->config = get_config('auth/dof');
    }
    
    /**
     * Позволяет ли плагин вести регистрацию пользователей
     */
    public function can_signup() 
    {
        return true;
    }
    
    /**
     * Вернуть объект формы регистрации пользователей
     * 
     * @return auth_dof_signup_form - Объект формы регистрации
     */
    public function signup_form() 
    {
        global $CFG;
    
        // Получение обработчика сообщений
        $messageprocessor = $this->message_processor();
        if ( $messageprocessor == NULL )
        {// Обработчик сообщений не определен
            print_error('notlocalisederrormessage', 'error', '', 'Sorry, you may not use this page.');
        }
        
        require_once($CFG->dirroot.'/auth/dof/forms.php');
        return new auth_dof_signup_form(NULL, NULL, 'post', '', ['autocomplete'=>'on']);
    }
    
    /**
     * Обработчик формы ркгистрации
     *
     * @param object $user - Объект нового пользователя
     * @param boolean $notify - Отобразить уведомление о создании
     */
    public function user_signup($user, $notify = true) 
    {
        global $CFG, $PAGE, $OUTPUT;
        
        require_once($CFG->dirroot.'/user/profile/lib.php');
        require_once($CFG->dirroot.'/user/lib.php');
    
        // Установка случайного пароля пользователя
        $user->password = hash_internal_user_password('');
        // Установка типа календаря
        if ( empty($user->calendartype) ) 
        {
            $user->calendartype = $CFG->calendartype;
        }
        
        // Установка номера телефона и логина
        $phone = $this->clean_phonenumber($user->phone);
        $user->username = $phone;
        $user->phone2 = $phone;
        $user->confirmed = 1;
        $user->id = user_create_user($user, true, false);
        // Добавить пользователя в Деканат
        $this->add_user_to_dof($user);
        
        // Сохранить информацию о дополнительных полях пользователя
        profile_save_data($user);
    
        // Событие создания пользователя
        \core\event\user_created::create_from_userid($user->id)->trigger();
    
        // Отправка смс с логином и паролем
        $result = $this->send_registration_data($user);
        
        // Код обработчика сообщений
        $processorcode = $this->config->sendmethod;
        
        if ($notify) 
        {
            if ( empty($result) )
            {
                $title = get_string($processorcode.'_send_error_title', 'auth_dof');
                $message = get_string($processorcode.'_send_error_message', 'auth_dof');
                $url = "$CFG->wwwroot/index.php";
            } else 
            {
                $title = get_string($processorcode.'_send_success_title', 'auth_dof');
                $message = get_string($processorcode.'_send_success_message', 'auth_dof');
                $url = get_login_url();
                if ( ! empty($CFG->alternateloginurl) )
                {
                    $url = $CFG->alternateloginurl;
                }
            }
            $PAGE->navbar->add($title);
            $PAGE->set_title($title);
            $PAGE->set_heading($PAGE->course->fullname);
            echo $OUTPUT->header();
            notice($message, $url);
        } else 
        {
            return true;
        }
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     *
     * @return bool Authentication success or failure.
     */
    public function user_login ($username, $password) 
    {
        global $CFG, $DB;
        if ($user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id) )) {
            return validate_internal_user_password($user, $password);
        }
        return false;
    }

    /**
     * Updates the user's password.
     *
     * called when the user password is updated.
     *
     * @param  object  $user        User table object  (with system magic quotes)
     * @param  string  $newpassword Plaintext password (with system magic quotes)
     * @return boolean result
     *
     */
    public function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        return update_internal_user_password($user, $newpassword);
    }

    public function prevent_local_passwords() {
        return false;
    }

    /**
     * Явлеется ли плагин внутренним
     *
     * @return bool
     */
    public function is_internal() 
    {
        return true;
    }

    /**
     * Возможность изменения пароля
     *
     * @return bool
     */
    public function can_change_password() 
    {
        return true;
    }

    /**
     * Возвращает ссылку на восстановление пароля.
     *
     * @return string
     */
    public function change_password_url() 
    {
        // Ссылка по умолчанию
        return '';
    }

    /**
     * Возможность сброса пароля
     *
     * @return bool
     */
    public function can_reset_password() 
    {
        return true;
    }

    /**
     * Возможность ручной работы с плагином
     *
     * Например, при создании пользователей через CSV
     *
     * @return bool
     */
    public function can_be_manually_set()
    {
        return true;
    }
    
    /**
     * Отобразить форму для настройки плагина авторизации
     *
     * Функция вызывается в admin/auth.php
     *
     */
    public function config_form($config, $err, $user_fields) 
    {
        include 'config.html';
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
    public function process_config($config) 
    {
        // Установки по умолчанию
        if ( ! isset($config->recaptcha) ) 
        {// Добавление капчи
            $config->recaptcha = false;
        }
        if ( ! isset($config->dof_departmentid) ) 
        {// Подразделение Деканата, в которое добавляются пользователи
            $config->dof_departmentid = 0;
        }
        if ( ! isset($config->sendmethod) ) 
        {// Метод отправки данных для входа
            $config->sendmethod = 'disabled';
        }

        // Сохранение настроек
        set_config('recaptcha', $config->recaptcha, 'auth/dof');
        set_config('dof_departmentid', $config->dof_departmentid, 'auth/dof');
        set_config('sendmethod', $config->sendmethod, 'auth/dof');
        
        return true;
    }

    /**
     * Подтверждение зарегистрированного пользователя.
     */
    public function user_confirm($username, $confirmsecret = null) 
    {
        global $DB;        
        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->confirmed) {
                return AUTH_CONFIRM_ALREADY;
            } else { 
                if ( ! $DB->set_field("user", "confirmed", 1, array("id" => $user->id) )) {
                    return AUTH_CONFIRM_FAIL;
                }
                if ( ! $DB->set_field("user", "firstaccess", time(), array( "id" => $user->id)) ) {
                    return AUTH_CONFIRM_FAIL;
                }
                return AUTH_CONFIRM_OK;
            }
        } else  {
            return AUTH_CONFIRM_ERROR;
        }
    }
    
    /**
     * Доступность капчи
     * 
     * @return bool
     */
    public function is_captcha_enabled() {
        global $CFG;
        return isset($CFG->recaptchapublickey) && isset($CFG->recaptchaprivatekey) && get_config("auth/{$this->authtype}", 'recaptcha');
    }
    
    /**
     * Получить номер телефона без спецсимволов
     */
    public function clean_phonenumber($phone)
    {
        $phone = preg_replace("([^0-9])", "", $phone);
        if ( strlen($phone) === 11 )
        {
            return $phone;
        }
        return NULL;
    }
    
    /**
     * Обновить пароль пользователя и выслать смс с данными
     * 
     * @param stdClass $user
     */
    public function send_registration_data($user)
    {
        global $CFG;
        
        // Формирование пароля
        $newpassword = generate_password();
        update_internal_user_password($user, $newpassword, true);
        
        // ПОлучение данных для формирования сообщения
        $site = get_site();
        $supportuser = core_user::get_support_user();
        $targetuser = get_complete_user_data('id', $user->id);
        
        // Формирование сообщения
        $a = new stdClass();
        $a->firstname   = $targetuser->firstname;
        $a->lastname    = $targetuser->lastname;
        $a->sitename    = format_string($site->fullname);
        $a->username    = $targetuser->username;
        $a->newpassword = $newpassword;
        $a->link        = $CFG->httpswwwroot .'/login/index.php';
        $a->signoff     = generate_email_signoff();
        $message = new stdClass();
        $message->component         = 'auth_dof';
        $message->name              = '';
        $message->userfrom          = $supportuser;
        $message->userto            = $targetuser;
        $message->subject           = get_string('newaccount');
        $message->fullmessage       = get_string('newuserfull', 'auth_dof', $a);
        $message->fullmessageformat = FORMAT_HTML;
        $message->fullmessagehtml   = get_string('newuserfull', 'auth_dof', $a);
        $message->smallmessage      = get_string('newusershort', 'auth_dof', $a);
        $message->notification      = 1;

        // Получение обработчика сообщений
        $messageprocessor = $this->message_processor();
        if ( $this->config->sendmethod == 'otsms' )
        {// Отмена транслитерации
            $messageprocessor->translit = false;
            $messageprocessor->addsubject = false;
        }
        
        // Отправка сообщения
        return $messageprocessor->send_message($message, false);
    }
    
    /**
     * Метод получения доступных подразделений
     */
    protected function get_available_dof_departments()
    {
        global $CFG, $DB;
        
        $stringnotadd = get_string('dof_departments_not_add', 'auth_dof');
        $departmentslist = [ 0 => $stringnotadd ];
        
        // Добавление секции с информацией о пользовательском портфолио
        $dofexist = $DB->record_exists('block_instances', ['blockname' => 'dof']);
        if ( ! empty($dofexist) )
        {// Блок деканата найден в системе
            $plugin = block_instance('dof');
            if ( ! empty($plugin) )
            {// Экземпляр деканата получен
                // Подключение библиотек деканата
                require_once($CFG->dirroot .'/blocks/dof/lib.php');
                global $DOF;
                // Проверка существования API
                $exist = $DOF->plugin_exists('storage', 'departments');
                $storageversion = $DOF->storage('departments')->version();
                if ( ! empty($exist) && $storageversion > 2015120000 )
                {// API доступен
                    $options = [];
                    $exist = $DOF->plugin_exists('workflow', 'departments');
                    if ( ! empty($exist) )
                    {// Статусы подразделений доступны
                        $statuses = $DOF->workflow('departments')->get_meta_list('active');
                        $options['statuses'] = array_keys($statuses);
                    }
                    // Получение подразделений
                    $departments = $DOF->storage('departments')->get_departments(0, $options);
                    if ( ! empty($departments) )
                    {// Подразделения получены
                        foreach ( $departments as $department )
                        {
                            $departmentslist[$department->id] = $department->name;
                        }
                    }
                } else
                {
                    $stringversiondeperror = get_string('dof_departments_version_error', 'auth_dof');
                    $departmentslist = [ 0 => $stringversiondeperror ];
                }
            }
        }
        return $departmentslist;
    }
    
    /**
     * Метод получения доступных методов отправки данных о регистрации
     */
    protected function get_available_send_methods()
    {
        $stringnotset = get_string('send_method_not_set', 'auth_dof');
        $methods = [ 'disabled' => $stringnotset ];
        $processors = get_message_processors(true);
        
        if ( isset($processors['email']->enabled) && $processors['email']->enabled == 1 )
        {
            $methods['email'] = get_string('pluginname', 'message_email');
        }
        if ( isset($processors['otsms']->enabled) && $processors['otsms']->enabled == 1 )
        {
            $methods['otsms'] = get_string('pluginname', 'message_otsms');
        }
        return $methods;
    }
    
    /**
     * Получение обработчика сообщений
     * 
     * @return message_output|NULL - Объект обработчика сообщений 
     * или NULL, если обработчик не найден или не активен
     */
    protected function message_processor()
    {
        $processor = NULL;
        if ( isset($this->config->sendmethod) )
        {// Процессор выбран в настройках
            $processors = get_message_processors(true);
            if ( isset($processors[$this->config->sendmethod]->enabled) && 
                 $processors[$this->config->sendmethod]->enabled == 1 )
            {// Процессор доступен и включен
                if ( is_object($processors[$this->config->sendmethod]->object) )
                {// Объект процессора доступен
                    $processor = $processors[$this->config->sendmethod]->object;
                }
            }
        }
        return $processor;  
    }
    
    /**
     * Добавление пользователя в Деканат
     * 
     */
    protected function add_user_to_dof($user)
    {
        global $CFG, $DB;
        
        // Получение деканата
        $dofexist = $DB->record_exists('block_instances', ['blockname' => 'dof']);
        if ( ! empty($dofexist) )
        {// Блок деканата найден в системе
            $plugin = block_instance('dof');
            if ( ! empty($plugin) )
            {// Экземпляр деканата получен
                // Подключение библиотек деканата
                require_once($CFG->dirroot .'/blocks/dof/lib.php');
                global $DOF;
                // Проверка существования API
                $exist = $DOF->plugin_exists('storage', 'persons');
                if ( ! empty($exist) )
                {// API доступен
                    $departmentid = $this->config->dof_departmentid;
                    if( $departmentid > 0 )
                    {
                        // Создать пользователя
                        $id = $DOF->storage('persons')->reg_moodleuser($user);
                        if ( empty($id) )
                        {
                            return false;
                        }
                        $person = new stdClass();
                        $person->id  = $id;
                        if ( isset($user->middlename) )
                        {
                            $person->middlename  = $user->middlename;
                        }
                        $person->departmentid  = $departmentid;
                        $person->phonecell = $user->phone2;
                        $res = $DOF->storage('persons')->update($person);
                        if ( empty($res) )
                        {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }
}

