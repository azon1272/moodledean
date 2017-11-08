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

/** Класс для работы с пользователем (Alternative Moodle Api)
 * @access public
 */
class ama_user extends ama_base
{

    /** Возвращает информацию о пользователе из БД
     * @access public
     * @param int $id - id курса
     * @return object массив типа параметр=>значение
     */
    public function get()
    {
        global $DB;
        if ( !$this->get_id() )
        {
            return false; // неизвестно какую запись извлекать
        }
        return $DB->get_record('user', array('id' => $this->get_id()));
    }

    /**
     * Существует ли пользователь с заданным $id (вернуть $id или false)
     * @param int $id - id пользователя, если не задан - берется из класса 
     */
    public function is_exists($id = null)
    {
        global $DB;
        if ( is_null($id) )
        {
            $id = $this->get_id();
        }
        if ( !$id OR ! ama_utils_is_intstring($id) )
        {
            return false;
        }
        return $DB->record_exists('user', array('id' => intval($id), 'deleted' => 0));
    }

    /**
     * Проверяет наличие роли учитель или админ у пользователя
     * @param int $userid - id проверяемого пользователя
     * @param int $courseid - id курса на котором он, возможно преподает
     * @param bool $isadmin - если true, то проверим заодно является ли он админом
     * @return bool - true если учитель (и админ - в зависимости от $isadmin)
     */
    public function is_teacher($userid = null, $courseid = null, $isadmin = true)
    {
        global $DB;
        if ( is_null($userid) )
        {//берем текушего пользователя
            $userid = $this->get_id();
        }
        if ( !$userid OR ! ama_utils_is_intstring($userid) )
        {//неправильный id пользователя
            return false;
        }

        $contextid = 0;
        if ( $courseid )
        {//проверим - является ли пользователь учителем 
            if ( class_exists('context_course') )
            {// начиная с moodle 2.6
                $context = context_course::instance($courseid);
            } else
            {// оставим совместимость с moodle 2.5 и менее
                $context = get_context_instance(CONTEXT_COURSE, $courseid);
            }
            //на переданном курсе
            if ( $context )
            {
                $contextid = $context->id;
            }
        }
        // Определим, какие роли являются учителями
        // @todo в будущем следует найти какой-то более надежный способ это определять
        $roles = array();
        $roles[] = $DB->get_record('role', array('shortname' => 'teacher'));
        $roles[] = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $roles[] = $DB->get_record('role', array('shortname' => 'coursecreator'));
        $roles[] = $DB->get_record('role', array('shortname' => 'manager'));

        // перебираем все учительские роли, и смотрим обладает ли ими пользователь
        foreach ( $roles as $role )
        {
            if ( !empty($role->id) AND user_has_role_assignment($userid, $role->id, $contextid) )
            {
                return true;
            }
        }

        return false;
    }

    /** Создает объект и возвращает его id
     * @param mixed $obj - параметры объекта или null для параметров по умолчанию 
     * @return mixed
     */
    public function create($obj = null)
    {
        global $DB;
        // Если емайл не задан - шаблон создаст его сам, но мы должны отключить отправку писем
        if ( is_object($obj) AND ! isset($obj->email) or empty($obj->email) )
        {
            // Запрещаем отправку писем
            $obj->emailstop = 1;
        }

        // Всегда создаем нового пользователя через шаблон
        // если нужно оставить поле пустым - устанавливаем его в исходном $obj
        // добавление слешей учтено (в исходном $obj слеши должны быть, шаблон обрабатыват только свои данные)
        $obj = $this->template($obj);

        // Добиваемся уникальности логина
        $obj->username = $this->username_unique($obj->username, true);

        // Перед вставкой в базу, или ее обновлением, проверим, уникальна ли запись, чтобы не было конфликтов
        if ( $this->is_unique($obj) )
        {
            // вставляем или обновляем запись в базе
            return $DB->insert_record('user', $obj);
        } else
        {
            // запись не уникальна, вставить в базу не получится
            return false;
        }
    }

    /** Обновляет информацию о пользователе в БД
     * @param object $dateobject - информация о пользователе 
     * @return id созданной записи в случае успеха, или false, если мы налажали
     * @access public
     */
    public function update($dateobject, $replace = false)
    {
        global $DB;
        $this->require_real();
        //echo 'bbb1';
        /*
         * Здесь этот фрагмент не нужен, наоборот, нужно принимать специальные меры
         * если нужно обновить полностью
          if ($replace !== true)
          {
          echo 'bbb2';
          // Merge new data with old data
          $old = $this->get();
          $dateobject = (object) array_merge((array)$old,(array)$dateobject);
          }
         */
        //echo 'bbb3';
        // addslashes_object($dateobject);
        $dateobject->id = $this->id; // добавляем в обьект для обновления id созданной в конструкторе записи
// 		Добиваемся уникальности логина
        if ( isset($dateobject->username) )
        {
            $dateobject->username = $this->username_unique($dateobject->username, true, $dateobject->id);
        }
        // Перед вставкой в базу, или ее обновлением, проверим, уникальна ли запись, чтобы не было конфликтов
        if ( !$this->is_unique($dateobject) )
        {// запись не уникальна, вставить в базу не получится
            return false;
        } else
        {
            // вставляем или обновляем запись в базе
            if ( $DB->update_record('user', $dateobject) )
            {
                return $this->id;
            } else
            {// с обновлением возникли проблемы
                return false;
            }
        }
    }

    /** Удаляет запись о пользователе из таблицы _user
     * @access public
     * @return bool true - удаление прошло успешно 
     * false в противном случае
     */
    public function delete()
    {
        if ( delete_user($this->get()) )
        {
            $this->id = 0;
            return true;
        } else
        {
            return false;
        }
    }

    /**
     * Функция проверяющая уникальность логина и email-а до записи в базу данных
     * @param stdClass object $dateobject - обьект, содержащий данные о пользователе. 
     * Имена полей обьекта совпадают с именами полей в таблице mdl_user. 
     * 
     * @access public
     * @return true or false
     */
    public function is_unique($dateobject)
    {
        global $DB;
        // Формируем SQL-запрос
        $sql = '';

        // Фильтр по логину
        if ( isset($dateobject->username) and ! empty($dateobject->username) )
        {
            $sql = $sql . ' username = \'' . $dateobject->username . '\' ';
        }
        // Фильтр по адресу электронной почты
        if ( isset($dateobject->email) and ! empty($dateobject->email) )
        {
            if ( $sql )
            {
                $sql = $sql . ' OR email = \'' . $dateobject->email . '\' ';
            } else
            {
                $sql = $sql . ' email = \'' . $dateobject->email . '\' ';
            }
        }
        // Фильтр игнорирования существующей записи по id
        // без $sql не имеет смысла
        if ( $sql AND isset($dateobject->id) AND ! empty($dateobject->id) ) // исключил: and !($this->get_id() === false) зачем это было?
        {
            // если id передан вместе с обьектом, игнорируем его
            $sql = "({$sql}) AND id<>{$dateobject->id}";
        } elseif ( $sql AND $this->get_id() )
        {
            // если id присутствует здесь, но отсутствует в переданном объекте - игнорируем местный
            $sql = "({$sql}) AND id<>{$this->get_id()}";
        }
        if ( !$sql )
        {
            // запрашивать нечего, email и логин не переданы, значит мы их не меняем, значит все ОК
            return true;
        }
        // Отсекаем удаленные
        $sql = "($sql) AND deleted='0'";
        // Считаем, если записей ноль - все хорошо
        return $DB->count_records_select('user', $sql) == 0;
    }

    /**
     * Сгенерировать уникальный логин по имени кириллицей
     */
    public function username_unique($username, $translit = true, $ignoreid = null)
    {
        global $DB;
        // Добиваемся уникальности короткого имени
        $i = 1;
        if ( $translit )
        {
            // Транслитерация и приведение символов к нижнему регистру
            $username = ama_utils_translit('ru', $username, true);
        }
        $username2 = $username;
        $sql = '';
        if ( $ignoreid )
        {    // Игнорируем id
            $sql .= " AND id<>'{$ignoreid}'";
        }
        while ( $DB->record_exists_select('user', " username='{$username2}' AND deleted='0' {$sql}") )
        {
            $username2 = "{$username}-{$i}";
            ++$i;
        }

        return $username2;
    }

    /**
     * функция для поиска пользователя по имени и фамилии. Используется поиск по маске (то есть шаблон LIKE)
     * @param stdClass Object $search - обьект, содержащий поля, по которым будет производиться поиск
     * @param string $sort
     * @param int $limitfrom
     * @param int $limitnum
     * 
     * @return массив обьектов (Записи из базы. Названия полей обьектов совпадают с названиями полей в таблице mdl_user)
     * или false в случае неудачи
     * @todo Уточнить по каким параметрам производить поиск
     */
    public function search($search, $sort = 'lastname ASC', $limitfrom = 0, $limitnum = 0)
    {
        global $DB;
        if ( $this->id != false )
        {// из-за текущей архитектуры ama поиск невозможен при конкретном userid
            dof_debugging('ama_user::search() - user->id must be null to use search', DEBUG_DEVELOPER);
            return false;
        }

        if ( isset($search->firstname) AND isset($search->lastname) )
        {// если требуется произвести поиск во имени и фамилии
            $search->firstname = clean_param($search->firstname, PARAM_TEXT);
            $search->lastname = clean_param($search->lastname, PARAM_TEXT);
            $sql = "firstname LIKE '%" . $search->firstname . "%' AND lastname LIKE '%" . $search->lastname . "%' ";
        } elseif ( isset($search->firstname) )
        {// только по имени
            $search->firstname = clean_param($search->firstname, PARAM_TEXT);
            $sql = "firstname LIKE '%" . $search->firstname . "%' ";
        } elseif ( isset($search->lastname) )
        {// только по фамилии 
            $search->lastname = clean_param($search->lastname, PARAM_TEXT);
            $sql = "lastname LIKE '%" . $search->lastname . "%' ";
        } else
        {// пришел пустой поисковый запрос
            dof_debugging('ama_user::search() empty search parameters got, object expected', DEBUG_DEVELOPER);
            return false;
        }
        // не получаем удаленных пользователей
        $sql .= ' AND deleted = 0';

        return $DB->get_records_select('user', $sql, null, $sort, '*', $limitfrom, $limitnum);
    }

    /** Возвращает информацию по умолчанию о пользователе
     * Это значения полей по умолчанию для таблицы _user
     * @access public
     * @param object $data - массив значений, которые переопределяют 
     * соответствующие параметры по умолчанию 
     * @return object параметры по умолчанию для нового пользователя
     */
    public function template($data = NULL)
    {
        global $CFG;
        $user = new stdClass();
        $user->username = 'new' . substr(md5($_SERVER['REMOTE_ADDR'] . $_SERVER['REMOTE_PORT'] . microtime()), 0, 7);
        $user->password = md5($this->generate_password_moodle());
        $user->email = $this->generate_email($user->username);
        $user->timemodified = time();
        $user->department = '';
        $user->firstname = 'firstname';
        $user->lastname = 'lastname';
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->lang = 'ru_utf8';
        if ( file_exists("{$CFG->dirroot}/auth/dof/auth.php") )
        {// если есть авторицация dof - ставим ее
            $user->auth = 'dof';
        }
        // По умолчанию делаем запись не подтвержденной
        $user->confirmed = 0;
        // Запрещаем отправку писем
        $user->emailstop = 1;
        // Отключаем подписку
        $user->autosubscribe = 0;
        // Не отображаем емайл
        $user->maildisplay = 0;
        // Сливаем данные
        if ( !is_null($data) )
        {
            foreach ( $data as $key => $val )
            {
                $user->$key = $val;
            }
        }
        return $user;
    }

    // Почтовые уведомления
    /**
     * Отправит сообщение текущему пользователю
     *
     * @param int $frommuser user id from
     * @param string $subject plain text subject line of the email
     * @param string $messagetext plain text version of the message
     * @param string $messagehtml complete html version of the message (optional)
     * @param string $attachment a file on the filesystem, relative to $CFG->dataroot
     * @param string $attachname the name of the file (extension indicates MIME)
     * @param bool $usetrueaddress determines whether $from email address should
     *          be sent out. Will be overruled by user profile setting for maildisplay
     * @param int $wordwrapwidth custom word wrap width
     * @return bool|string Returns "true" if mail was sent OK, "emailstop" if email
     *          was blocked by user and "false" if there was another sort of error.
     */
    public function send_email($subject, $messagetext, $from = '', $messagehtml = '', $attachment = '', $attachname = '', $usetrueaddress = true, $replyto = '', $replytoname = '', $wordwrapwidth = 79)
    {
        $this->require_real();
        // Получаем пользователя
        if ( empty($from) )
        {
            $from = core_user::get_support_user();
        }

        return email_to_user($this->get(), $from, $subject, $messagetext, $messagehtml, $attachment, $attachname);
    }

    /**
     * Изменить пароль и отправить стандартное уведомление о регистрации с паролем
     * @param $newpassword - новый пароль, null - сгенерировать
     * @param $update - обновлять учетную запись?
     * @return boll
     */
    public function send_setnew_notice($newpassword = null, $update = false)
    {
        $this->require_real();
        // Получаем пользователя
        $user = $this->get();
        // return setnew_password_and_mail($this->get());
        global $CFG, $DB;

        $site = get_site();

        $supportuser = core_user::get_support_user();

        if ( empty($newpassword) )
        {
            // Нужно сгенерировать пароль
            $newpassword = $this->generate_password_pronounceable();
        }

        if ( $update )
        {
            // Нужно обновить пароль в БД
            if ( !$DB->set_field('user', 'password', hash_internal_user_password($newpassword), array('id' => $user->id)) )
            {
                dof_mtrace(3, 'Could not set user password!');
                return false;
            }
        }

        $a = new stdClass();
        $a->firstname = fullname($user, true);
        $a->sitename = format_string($site->fullname);
        $a->username = $user->username;
        $a->newpassword = $newpassword;
        $a->link = $CFG->wwwroot . '/login/';
        $a->signoff = generate_email_signoff();

        $message = get_string('newusernewpasswordtext', '', $a);

        $subject = format_string($site->fullname) . ': ' . get_string('newusernewpasswordsubj');

        return email_to_user($user, $supportuser, $subject, $message);
    }

    // ****************************************
    //                Утилиты
    // ****************************************
    /**
     * Создать пароль (без спецсимволов): скопиоровано из moodle
     *
     * @param int $maxlen  The maximum size of the password being generated.
     * @return string
     */
    public function generate_password_moodle($maxlen = 10)
    {
        global $CFG;

        if ( empty($CFG->passwordpolicy) )
        {
            $fillers = PASSWORD_DIGITS;
            $wordlist = file($CFG->wordlist);
            $word1 = trim($wordlist[rand(0, count($wordlist) - 1)]);
            $word2 = trim($wordlist[rand(0, count($wordlist) - 1)]);
            $filler1 = $fillers[rand(0, strlen($fillers) - 1)];
            $password = $word1 . $filler1 . $word2;
        } else
        {
            // Отключаем переопределение $maxlen, иначе минимальная длинна приравнивается к максимальной
            // $maxlen = !empty($CFG->minpasswordlength) ? $CFG->minpasswordlength : 0;
            $digits = $CFG->minpassworddigits;
            $lower = $CFG->minpasswordlower;
            $upper = $CFG->minpasswordupper;
            $nonalphanum = $CFG->minpasswordnonalphanum;
            $additional = $maxlen - ($lower + $upper + $digits + $nonalphanum);

            // Make sure we have enough characters to fulfill
            // complexity requirements
            $passworddigits = PASSWORD_DIGITS;
            while ( $digits > strlen($passworddigits) )
            {
                $passworddigits .= PASSWORD_DIGITS;
            }
            $passwordlower = PASSWORD_LOWER;
            while ( $lower > strlen($passwordlower) )
            {
                $passwordlower .= PASSWORD_LOWER;
            }
            $passwordupper = PASSWORD_UPPER;
            while ( $upper > strlen($passwordupper) )
            {
                $passwordupper .= PASSWORD_UPPER;
            }
            $passwordnonalphanum = PASSWORD_NONALPHANUM;
            while ( $nonalphanum > strlen($passwordnonalphanum) )
            {
                $passwordnonalphanum .= PASSWORD_NONALPHANUM;
            }

            // Now mix and shuffle it all
            $password = str_shuffle(substr(str_shuffle($passwordlower), 0, $lower) .
                    substr(str_shuffle($passwordupper), 0, $upper) .
                    substr(str_shuffle($passworddigits), 0, $digits) .
                    substr(str_shuffle($passwordnonalphanum), 0, $nonalphanum) .
                    substr(str_shuffle($passwordlower .
                                    $passwordupper .
                                    $passworddigits .
                                    $passwordnonalphanum), 0, $additional));
        }

        return substr($password, 0, $maxlen);
    }

    /**
     * Create pronounceable password
     *
     * This method creates a string that consists of
     * vowels and consonats.
     *
     * @access private
     * @param  integer Length of the password
     * @return string  Returns the password
     */
    public function generate_password_pronounceable($maxlen = 10)
    {

        $retVal = '';

        /**
         * List of vowels and vowel sounds
         */
        $v = array('a', 'e', 'i', 'o', 'u',
            'ae', 'ou', 'io',
            'a', 'o', 'a', 'o', 'i',
            'ea', 'ou', 'ia', 'eu', 'au'
        );

        /**
         * List of consonants and consonant sounds
         */
        $c = array('b', 'd', 'g', 'h', 'k', 'l', 'm',
            'n', 'p', 'r', 's', 't', 'u', 'v', 'f',
            'tr', 'kr', 'fr', 'dr', 'vr', 'pr', 'tl',
            'gd', 'kt', 'ml', 'pt', 'hr',
            'kh', 'ph', 'st', 'sl', 'kl', 'kz', 'bz',
            'kn', 'pr', 'zk', 'zd', 'bz', 'br', 'bl',
            'dl', 'nd', 'vn', 'kv', 'gl', 'ps', 'sh'
        );

        $v_count = 12;
        $c_count = 29;

        $_Text_Password_NumberOfPossibleCharacters = $v_count + $c_count;

        for ( $i = 0; $i < $maxlen; $i++ )
        {
            $retVal .= $c[mt_rand(0, $c_count - 1)] . $v[mt_rand(0, $v_count - 1)];
        }

        return ucfirst(substr($retVal, 0, ($maxlen - 1)) . rand(1, 9));
    }

    /** Проверяет, что почтовый адрес допустим в системе. Иначе отображает ошибку
     * 
     * @param string $email
     * @return bool|string false в случае, если email допустим, иначе string с описанием ошибки
     */
    public function email_is_not_allowed($email)
    {
        return email_is_not_allowed($email);
    }

    /**
     * Генерирует email по заданному логину
     */
    protected function generate_email($login)
    {
        $suffix = 'emailsuffix.su';
        return $login . '@' . $suffix;
    }

    /** Получить список записей критериям
     * 
     * @return array|bool массив записей из таблицы mdl_user или false
     * @param array $options - массив условий в формате 'название_поля' => 'значение'
     * @param string $sort [optional] - в каком направлении и по каким полям производится сортировка
     * @param string $fields [optional] - поля, которые надо возвратить
     * @param int $limitfrom [optional] - id, начиная с которого надо искать
     * @param int $limitnum [optional] - максимальное количество записей, которое надо вернуть
     */
    public function get_list($options = null, $sort = '', $fields = '*', $limitfrom = '', $limitnum = '')
    {
        global $CFG, $DB;
        $select = '';
        if ( !is_null($options) AND ! is_array($options) )
        {// передан неправильный формат данных
            return false;
        }
        if ( !empty($options) )
        {// если у нас есть условия - подставим мх в запрос
            foreach ( $options as $field => $value )
            {// перебираем все условия и в цикле составляем запрос
                if ( !$select )
                {// если это первый фрагмент запроса - то не добавляем условие AND
                    $select .= $this->query_part_select($field, $value);
                } else
                {// для второго и последующих условий - добавим
                    $select .= ' AND ' . $this->query_part_select($field, $value);
                }
            }
        }

        return $DB->get_records_select('user', $select, null, $sort, $fields, $limitfrom, $limitnum);
    }

    /**
     * Возвращает фрагмент sql-запроса после слова WHERE,
     * который определяет параметры выборки  
     * @param string $field - название поля
     * @param mixed $value - null, string или array 
     * @return mixed string - фрагмент sql-запроса
     * если $value - null, то пустая строка
     * если $value - строка, то "поле = значение"
     * если $value - массив, то "поле IN(знач1, знач2, ... значN)" 
     * если массив пуст или это не массив и не строка и не null,
     * то вернется bool false 
     * 
     * @todo это дублирование функции из storage_base. Нужно будет потом найти способ от него избавится.
     */
    public function query_part_select($field, $value = null)
    {
        if ( !is_scalar($field) OR is_bool($field) )
        {//название поля неправильного типа';
            return false;
        }
        if ( is_null($value) )
        {//значение поля не передано';
            return '';
        }
        if ( is_scalar($value) AND ! is_bool($value) )
        {//значение только одно';
            return "{$field} = '{$value}'";
        }
        if ( is_array($value) AND ! empty($value) )
        {//значений несколько';
            $isnull = '';
            foreach ( $value as $k => $v )
            {//разберемся, что передано в массиве, 
                if ( is_null($v) )
                {//передан элемент null
                    //сформируем фрагмент запроса IS NULL
                    $isnull = $field . ' IS NULL ';
                    //уберем null из массива во избежание ошибок
                    unset($value[$k]);
                } elseif ( is_scalar($v) )
                {//передано что надо - превращаем в строку
                    $value[$k] = '\'' . $v . '\'';
                } else
                {//передано то, что не надо было передавать
                    return false;
                }
            }
            if ( empty($value) )
            {//в массиве были только элементы null
                return $isnull;
            }
            //если в массиве еще что-то осталось
            $str = implode(',', $value);
            if ( $isnull )
            {// Нужно сравнивать с null-значением
                return "({$field} IN({$str}) OR {$isnull})";
            } else
            {// не нужно сравнивать с null-значением
                return "({$field} IN({$str}))";
            }
        } else
        {//не массив или пустой массив';
            return false;
        }
        //на всякий случай, если передали нечто неизвестное';
        return false;
    }

    /** Возвращает запись пользователя Moodle по его логину
     * @param string $username - логин пользователя
     * @return object - запись пользователя Moodle или false, если таковой не был найден
     */
    public function get_user_by_username($username)
    {
        global $DB;
        return $DB->get_record_select('user', " username='{$username}' AND deleted='0'");
    }

    /** Меняем метод авторизации на dof
     * @return int - id записи или bool false если что-то не так
     */
    public function replace_method_on_dof($changepassword = false)
    {
        global $CFG;
        if ( !$user = $this->get() )
        {// не нашли пользователя - ошибка
            return false;
        }
        if ( $user->auth === 'dof' )
        {
            // Уже все сменили
            return true;
        }
        if ( file_exists("{$CFG->dirroot}/auth/dof/auth.php") )
        {// если есть авторицация dof - ставим ее
            $user->auth = 'dof';
            if ( $changepassword )
            {
                // Надо сменить пароль
                $user->password = $this->generate_password();
                $this->send_setnew_notice($user->password, false);
                $user->password = hash_internal_user_password($user->password);
            }
            // обновляем запись
            return $this->update($user);
        }
        return false;
    }

    /** Возвращает последний вход персоны деканата на портал
     * @param int $personid - id персоны из деканата
     * @return string - дата последнего вхда на портал
     * или bool false, если персоны на портале никогда не было
     */
    public function get_lastaccess($personid)
    {
        global $DOF;
        if ( !$userid = $DOF->storage('persons')->get_field($personid, 'mdluser') )
        {// не получили id пользователя Moodle - значит его никогда не было на портале
            return false;
        }
        $this->set_id($userid);
        if ( !$user = $this->get() )
        {// не нашли пользователя - значит его никогда не было на портале
            return false;
        }
        if ( empty($user->lastaccess) )
        {//последный вход не указан - значит его никогда не было на портале
            return false;
        }
        // вернем последний вход пользователя
        return date('d.m.Y H:i', $user->lastaccess);
    }

    /** Возвращает количество входов на портал
     * @param int $personid - id персоны
     * @return int количество заходов или bool false
     */
    public function count_login($personid, $begindate = null, $enddate = null)
    {
        global $DOF, $DB;
        if ( !$userid = $DOF->storage('persons')->get_field($personid, 'mdluser') )
        {// не получили id пользователя Moodle - значит он никогда не входил на портал
            return 0;
        }
        // укажем временной интервал
        $days = '';
        // дата начала
        if ( isset($begindate) AND $begindate )
        {// укажем с какой даты брать отчет
            $days = " AND time>{$begindate}";
        }
        // дата клнца
        if ( isset($enddate) AND $enddate )
        {// укажем с какой даты брать отчет
            $days .= " AND time<{$enddate}";
        }
        $select = "course=1 AND module='user' AND action='login' AND userid='{$userid}'  {$days}";

        return $DB->count_records_select('log', $select);
    }

    /** Определяет правильность буквенного написания email
     * @param string $email - email
     * @return bool true - если email указан верно или
     *         bool false - если найдены неккоректно введеные знаки
     */
    public function validate_email($email)
    {
        return validate_email($email);
    }

    /** Определяет правильность буквенного написания логина
     * @param string $username - логин
     * @return bool true - если логин указан верно или
     *         bool false - если найдены неккоректно введеные знаки
     */
    public function validate_username($username)
    {
        return !preg_match("/[^(-_\.[:alnum:])]/i", $username);
    }

    /** 
     * Получить список курсов, на которые подписан пользователь
     * 
     * @return array
     */
    public function get_courses($userid)
    {
        return enrol_get_users_courses($userid);
    }
    
    /**
     * Получить перечень дополнительных полей пользователя
     */
    public function get_user_custom_fields()
    {
        global $DB;
        $fields = $DB->get_records('user_info_field');
        return $fields;
    }
    
    /**
     * Получить поле дополнительное пользователя по его shortname
     */
    public function get_user_custom_field($shortname)
    {
        global $DB;
        $field = $DB->get_record('user_info_field', ['shortname' => $shortname]);
        return $field;
    }
    
    /**
     * Получить перечень всех имеющихся значений поля
     * 
     * @param string $name - Имя поля
     * 
     * @return array - Массив значений
     */
    public function get_user_custom_field_options($name)
    {
        global $DB;
        
        $options = [];
        
        $field = $DB->get_record('user_info_field', ['shortname' => $name]);
        if ( ! empty($field) )
        {// Поле найдено
            $sql = " SELECT MIN(id), data  
                    FROM {user_info_data}
                    WHERE fieldid = :fieldid
                    GROUP BY data ";
            $koptions = $DB->get_records_sql($sql, ['fieldid' => $field->id]);
            if ( ! empty($koptions) )
            {
                foreach ( $koptions as $id => $instance )
                {
                    $options[$id] = $instance->data;
                }
            }
        }
        
        return $options;
    }
       
    /**
     * Получить данные пользователя
     * 
     * Возвращает данные пользователя вместе с дополнительными полями
     */
    public function get_user_profile() 
    {
        global $CFG;
        require_once($CFG->dirroot.'/user/profile/lib.php');
        
        if ( ! empty($this->id) )
        {
            $user = $this->get();
            profile_load_data($user);
            profile_load_custom_fields($user);
            return $user;
        } else 
        {
            return NULL;
        }
        
    }
    
    /**
     * Получить значение поля пользователя
     * 
     * @param string $custonfieldvalueid - ID поля пользоательского значения
     *
     * @return object|NULL - Пользовательский ответ или NULL
     */
    public function get_user_customfield_value($customfieldvalueid)
    {
        global $DB;
        
        // Получение данных
        $data = $DB->get_record('user_info_data', ['id' => $customfieldvalueid]);
        
        if ( ! empty($data) )
        {
            return $data;
        }
        return NULL;
    }
    
    /**
     * Получить ID пользователей по значению поля
     *
     * Из за нестандартизированной работы различных полей пользователя
     * требуется различная обработка у типов полей
     * 
     * @param mixed $shortname - Название дополнительного поля пользователя поля пользователя
     * @param mixed $value - Значение дополнительного поля пользователя
     *
     * @return array|NULL - Массив ID пользоаелей или NULL
     */
    public function get_userids_by_customfield_value($shortname, $value)
    {
        global $DB;
    
        // Поле не найдено
        $field = $this->get_user_custom_field($shortname);
        if ( empty($field) )
        {
            return NULL;
        }
        // Поиск пользователей в зависимости от типа поля
        switch ( $field->datatype )
        {
            case 'menu' :
                if ( isset($field->param1) ) 
                {
                    $options = explode("\n", $field->param1);
                } else {
                    $options = [];
                }
                if ( isset($options[(int)$value]) )
                {// Значение найдено
                    $value = $options[(int)$value];
                }
                break;
                
        }
        // Получение данных
        $select = ' fieldid = :fieldid AND data = :data ';
        $options = ['fieldid' => $field->id, 'data' => $value];
        $data = $DB->get_records_select('user_info_data', $select, $options);
        
        if ( ! empty($data) )
        {
            // Формирование массива пользователей
            $users = [];
            foreach ( $data as $field )
            {
                $users[$field->userid] = $field->userid;
            }
            $users = array_keys($users);
            return $users;
        }
        return NULL;
    }
    
    /**
     * Получить аватар пользователя
     * 
     * @param array $options - Дополнительные параметры отображения
     *             int  'size' - Размер изображения в пикселях(200 по-умолчанию)
     *             array 'attributes' - Атрибуты тега изображения
     * 
     * @return string|NULL - HTML-код изображения пользователя или NULL
     */
    public function get_user_picture_html($options = [])
    {
        global $PAGE;
    
        // Получить идентификатор пользователя
        $id = $this->get_id();
        if ( ! empty($id) )
        {// Указан ID пользователя Moodle
            $mdluser = $this->get();
            $pic = new user_picture($mdluser);
            $pic->size = 200;
            if ( isset($options['size']) )
            {// Размер указан
                $pic->size = (int)$options['size'];
            }
            
            $url = $pic->get_url($PAGE);
            $attributes = ['width' => (string)$pic->size, 'height' => 'auto'];
            // Переопределение атрибутов из опций обработки
            if ( isset($options['attributes']) && ! empty($options['attributes']) )
            {
                $options['attributes'] = (array)$options['attributes'];
                foreach ( $options['attributes'] as $name => $value )
                {
                    $attributes[$name] = $value;
                }
            }
            return dof_html_writer::img($url, '', $attributes);
        } 
        return null;
    }

}
