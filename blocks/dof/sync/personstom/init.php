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


/** Класс стандартных функций интерфейса
 * 
 */
class dof_sync_personstom implements dof_sync
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
        return 2015012100;
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
        return 'ancistrus';
    }
    
    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'sync';
    }
    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'personstom';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib'=>array('ama'=>2008100200),
                     'storage'=>array('persons'=>2008101600));

    }
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array(array('plugintype' => 'storage',  'plugincode' => 'persons', 'eventcode' => 'insert'),
                     array('plugintype' => 'storage',  'plugincode' => 'persons', 'eventcode' => 'update'),
                     array('plugintype' => 'workflow', 'plugincode' => 'persons', 'eventcode' => 'person_active'),
                     array('plugintype' => 'workflow', 'plugincode' => 'persons', 'eventcode' => 'person_not_active')
        );
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
     * @param int $id - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function catch_event($gentype,$gencode,$eventcode,$id,$mixedvar)
    {
        // Ловим событие, если пользователя синхронизируем и запись изменилась
        if ( $gentype==='storage' 
                AND $gencode === 'persons'
                AND $eventcode === 'insert'
                AND isset($mixedvar['new']->sync2moodle)
                AND $mixedvar['new']->sync2moodle )
        {
            if ( isset($mixedvar['old']) AND $mixedvar['new'] == $mixedvar['old'] )
            { // Объекты одинаковые - синхронизация не требуется
                return true;
            }
            // Синхронизация для пользователя не требуется
            $changelogin = false;
            // Нужно изменить логин если есть фамилия,
            if ( isset($mixedvar['new']->lastname) AND // 
                // нет старых данных или фамилии не совпадают
                (!isset($mixedvar['old']) OR $mixedvar['old']->lastname!==$mixedvar['new']->lastname) AND
                // это новый пользователь или в конфиге стоит автосмена логина
                $this->get_cfg('autochangelogin') )
            {
                // Создаем нового пользователя
                // или у старого сменилась фамилия и при этом разрешено автоматическое обновление логинов
                $changelogin = true;
            }
            // Синхронизируем персону
            $this->sync($mixedvar['new'],$changelogin);
        } else if ( $gentype==='storage' 
                AND $gencode === 'persons'
                AND $eventcode === 'update'
                AND isset($mixedvar['new']->sync2moodle)
                AND $mixedvar['new']->sync2moodle )
        {
            if ( isset($mixedvar['old']) AND $mixedvar['new'] == $mixedvar['old'] )
            { // Объекты одинаковые - синхронизация не требуется
                return true;
            }
            // Синхронизация для пользователя не требуется
            $changelogin = false;
            // Нужно изменить логин если есть фамилия,
            if ( isset($mixedvar['new']->lastname) AND // 
                // нет старых данных или фамилии не совпадают
                (!isset($mixedvar['old']) OR $mixedvar['old']->lastname!==$mixedvar['new']->lastname) AND
                // это новый пользователь или в конфиге стоит автосмена логина
                $this->get_cfg('autochangelogin') )
            {
                // Создаем нового пользователя
                // или у старого сменилась фамилия и при этом разрешено автоматическое обновление логинов
                $changelogin = true;
            }
            // Синхронизируем персону
            $this->sync($mixedvar['new'], $changelogin);
        }
        // Событие смены статуса у персоны на активный
        if ( $gentype==='workflow' AND
             $gencode === 'persons' AND
             $eventcode === 'person_active'
        )
        {
            // Необходимо описать действия по восстановлению удаленной из moodle персоны
        }
        
        // Событие смены статуса у персоны на неактивный
        if ( $gentype==='workflow' AND 
             $gencode === 'persons' AND 
             $eventcode === 'person_not_active' 
           )
        {
            // Проверка существования персоны
            if ( ! $person = $this->dof->storage('persons')->get($id) )
            {// Не найдена персона
                return false;
            }
            // Рассинхронизируем персону
            return $this->unsync($person, false);
        }
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
        if ($code === 'syncall')
        {
            // Нас попросили провести "очистку"
            return $this->sync_all();
        }
        return true;
    }
    // **********************************************
    // Собственные методы
    // **********************************************
    /** 
     * Конструктор
     * 
     * @param dof_control $dof - объект ядра деканата
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    
    /** 
     * Синхронизируем всех пользователей
     */
    public function sync_all()
    {
        // Получаем список персон
        dof_mtrace(3,"Start sync");
        $list = $this->dof->storage('persons')->get_list_synced();
        dof_mtrace(3,"Geted list ".count($list));
        foreach ($list as $person)
        {
            // Синхронизируем очередного пользователя
            $result = $this->sync($person);
            dof_mtrace(3," * Person {$person->id} result {$result}");
        }
        return true;
    }
    
    /** 
     * Синхронизация персоны если это разрешено в ее настройках
     * 
     * @param object $person - объект персоны деканата
     * @param bool $changelogin - замена логина на сгенерированный системой
     * @param bool $syncon - принудительная синхронизация
     * 
     * @return boolean|int - ID пользователя в moodle
     * @access public
     */
    public function sync($person,$changelogin=false,$syncon=false)
    {    
        // Синхронизируем пользователя
        $person2 = new stdClass();
        // Требуется ли принудительно включить синхронизацию?
        if ($syncon)
        {
            $person->sync2moodle=1;
            $person2->sync2moodle=1;
        }
        // Синхронизируем пользователя
        if ( ! $person2->mdluser = $this->sync_person_data($person,$changelogin) )
        {
            // Синхронизация завершилась неудачей
            return false;
        }
        // Сохраняем идентификатор
        if (isset($person->mdluser) and $person->mdluser)
        {// Идентификатор уже был установлен - оставляем 
            // Вернем ID пользователя moodle, на которого ссылается персона
            return $person->mdluser;
        }else
        {// Сохраняем созданный идентификатор mdluser в person
            if ( ! $this->dof->storage('persons')->update($person2, $person->id, true) )
            {// Ошибка синхронизации
                return false;
            }
            // Вернем ID пользователя moodle, на которого ссылается персона
            return $person2->mdluser;
        }
    }
    
    /** 
     * Провести рассинхронизацию пользователя с  Moodle
     * 
     * @param object $person - объект персоны деканата
     * @param bool $muserkeep - требуется ли удалить пользователя из moodle
     * 
     * @return bool - true в случае успеха и false в случае неудачной рассинхронизации
     */
    public function unsync($person, $muserkeep = false)
    {
        // Была ли персона синхронизирован ранее?
        if ( !isset($person->id) OR !$person->id
                OR !isset($person->sync2moodle) OR !$person->sync2moodle
                OR !isset($person->mdluser) OR !$person->mdluser )
        {
            return false;
        }
        // Требуется ли удалить пользователя?
        if ( ! $muserkeep AND ! $this->delete_user($person) )
        {
            return false;
        }
        // Отключаем синхронизацию
        $person2 = new stdClass();
        $person2->mdluser = null;
        $person2->sync2moodle = 0;

        return $this->dof->storage('persons')->update($person2,$person->id);
    }
    /** Удаляет пользователя из Moodle
     * @param object $person
     * 
     */
    protected function delete_user($person)
    {
        if ( !isset($person->mdluser )
            OR !$person->mdluser OR!$this->dof->modlib('ama')->user(false)->is_exists($person->mdluser))
        { // пользователя уже нет
            return true;
        }else
        { // удаляем
            $this->dof->send_event($this->type(),$this->code(),'deleteuser',$person->id,array('person'=>$person));
            return $this->dof->modlib('ama')->user($person->mdluser)->delete();
        }
    }
    
    /** Синхронизировать персону с пользователем Moodle
     * 
     * @param object $person - Объект персоны
     * @param bool $changelogin - Сгенерировать логин
     * 
     */
    protected function sync_person_data($person, $changelogin = false)
    {
        // Проверяем входные данные
        if ( ! is_object($person) OR ! isset($person->id) OR empty($person->id)  
                OR !isset($person->sync2moodle) OR empty($person->sync2moodle) )
        {   // Не передали данные или запрещена синхронизация
            // echo "non sync";
            return false;
        }
        
        // Начинаем формировать данные пользователя
        $user = new stdClass();
        $firstname = $person->firstname;
        if ( !empty($person->middlename) )
        {
            $firstname .= " {$person->middlename}";
        }
        $user->firstname = $firstname;
        $user->lastname = $person->lastname;
        $user->department = 'person';
        $user->idnumber = $person->id;
        if ( ! empty($person->email) )
        {    // указан email
            $user->email = $person->email;
        }
        // Получаем адресс пользователя из справочника адресов
        if (isset($person->passportaddrid))
        {
            $addressid = $person->passportaddrid;
        } else
        {
            $addressid = $person->addressid;
        }
        if (isset($addressid) AND $addressid)
        {
            $addres = $this->dof->storage('addresses')->get($addressid);
            $user->country = $addres->country;
            if ($addres->city)
            {
                // Указан город
                $user->city = $addres->city;
            }elseif ($addres->region)
            {
                // Город не указан - берем регион
                $user->city = $this->dof->modlib('refbook')->region($addres->country,$addres->region);
            }
        }
        
        // Номера телефонов
        if ( isset($person->phonehome) && ! empty($person->phonehome) )
        {// Установлен домашний телефон
            $user->phone1 = $person->phonehome;
        } else 
        {// Домашний телефон не указан, проверка на наличие рабочего
            if ( isset($person->phonework) && ! empty($person->phonework) )
            {// Установлен рабочий телефон
                $user->phone1 = $person->phonework;
            }
        }
        if ( isset($person->phonecell) && ! empty($person->phonecell) )
        {// Мобильный телефон указан
            $user->phone2 = $person->phonecell;
        }
        
        if ($person->mdluser)
        {    // Зарегистрированный пользователь
            // echo 'aaa2';
            if ($this->dof->modlib('ama')->user(false)->is_exists($person->mdluser))
            {    // Обновляем существующего пользователя

                if ($changelogin)
                {    // Нужно сменить логин
                    // Выбераем поле для логина
                    // Формируем уникальный логин из левой части емайла или транслитерацией
                    if (isset($person->email) AND !empty($person->email))
                    {
                        // Делаем логином первую часть емайла
                        $username = substr($person->email,0, strpos($person->email, '@'));
                        // $username = strstr($person->email, '@', true);
                    }elseif (empty($username) AND isset($person->lastname) AND !empty($person->lastname))
                    {
                        $username = $person->lastname;
                    }elseif (empty($username) AND isset($person->firstname) AND !empty($person->firstname))
                    {
                        $username = $person->firstname;
                    } else
                    {
                        $username = "p".$person->id;
                    }
                    // Делаем логин уникальным (больше не требуется, так как это происходит в ama_user)
                    // $user->username = $this->dof->modlib('ama')->user(false)->username_unique($username,true,$person->mdluser);
                    // Указываем желаемый логин - транслитерацию и уникальность добавит класс ama
                    // а префикс добавит обработчик событий
                    $user->username = $username;

                }
                // Подключаем общий обработчик, если он существует
                if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/userprocess.php')))
                {
                    // Подключаем файл с дополнительным разработчиком, который может использовать переменные
                    // $perosn и изменять $user
                    include $processfile;
                }
                // Подключаем обработчик добавления, если он существует
                if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/updateuserprocess.php')))
                {
                    // Подключаем файл с дополнительным разработчиком, который может использовать переменные
                    // $perosn и изменять $user
                    include $processfile;
                }
                
                // Отправляем событие регистрации нового пользователя Moodle
                $this->dof->send_event($this->type(),$this->code(),'updateuser',$person->id,array('person'=>$person,'user'=>$user));
                // Обновляем пользователя
                if ($mdluser = $this->dof->modlib('ama')->user($person->mdluser)->update($user))
                {
                   // Подключаем общий обработчик, если он существует
                    if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/userafter.php')))
                    {
                        // Подключаем файл с дополнительным обработчиком, который может использовать переменные
                        // $perosn и $user, чтобы выполнить действия после создания или обновления
                        include $processfile;
                    }
                    // Подключаем постобработчик обновления, если он существует
                    if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/updateuserafter.php')))
                    {
                        // Подключаем файл с дополнительным обработчиком, который может использовать переменные
                        // $perosn и $user, чтобы выполнить действия после создания или обновления
                        include $processfile;
                    } 
                }
                return $mdluser;
                
            }else
            {
                // echo 'aaa5';
                // Такого пользователя не существует
                return false;
            }
        }elseif (isset($person->email) AND !empty($person->email))
        {   // Новый пользователь
            // Добавляем нового пользователя

            // Создаем шаблон
            $user = $this->dof->modlib('ama')->user(false)->template($user);
            // Пользователь подтвержден
            $user->confirmed = 1;
            $user->emailstop = 0;
            $user->autosubscribe = 1;
            $user->maildisplay = 2;
            // Формируем уникальный логин из левой части емайла или транслитерацией
            if (isset($person->email) AND !empty($person->email))
            {
                // Делаем логином первую часть емайла
                $username = substr($person->email,0, strpos($person->email, '@'));
                // $username = strstr($person->email, '@', true);
            }elseif (empty($username) AND isset($person->lastname) AND !empty($person->lastname))
            {
                $username = $person->lastname;
            }elseif (empty($username) AND isset($person->firstname) AND !empty($person->firstname))
            {
                $username = $person->firstname;
            } else
            {
                $username = "p".$person->id;
            }
            // $user->username = $this->dof->modlib('ama')->user(false)->username_unique("a-".$username,true);
            // Указываем желаемый логин - транслитерацию и уникальность добавит класс ama
            // а префикс добавит обработчик событий
            $user->username = $username;
            
            // Подключаем общий обработчик, если он существует
            if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/userprocess.php')))
            {
                // Подключаем файл с дополнительным обработчиком, который может использовать переменные
                // $perosn и изменять $user
                include $processfile;
            }
            // Подключаем обработчик добавления, если он существует
            if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/adduserprocess.php')))
            {
                // Подключаем файл с дополнительным обработчиком, который может использовать переменные
                // $perosn и изменять $user
                include $processfile;
            } 
            
            // Отправляем событие регистрации нового пользователя Moodle
            // К событию прикрепляем ссылку на объекты $user и $person,
            // обработчики событий могут менять $user
            $this->dof->send_event($this->type(),$this->code(),'adduser',$person->id,array('person'=>$person,'user'=>$user));
            
            //echo 'aaa7'.$user->username;
            // return $this->dof->modlib('ama')->user()->update($user);
            if ($mdluser = $this->dof->modlib('ama')->user(false)->create($user))
            {
               // Подключаем общий обработчик, если он существует
                if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/userafter.php')))
                {
                    // Подключаем файл с дополнительным обработчиком, который может использовать переменные
                    // $perosn и $user, чтобы выполнить действия после создания или обновления
                    include $processfile;
                }
                // Подключаем постобработчик добавления, если он существует
                if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/adduserafter.php')))
                {
                    // Подключаем файл с дополнительным обработчиком, который может использовать переменные
                    // $perosn и $user, чтобы выполнить действия после создания или обновления
                    // По-умолчанию этот файл отсылает уведомление о пароле
                    include $processfile;
                } 
            }
            return $mdluser;
            
        }else
        {
            // echo 'aaa8';
            return false;
        }
    }
    /** Возвращает запись пользователя Moodle по его логину
     * @param string $username - логин пользователя
     * @return object - запись пользователя Moodle или false, если таковой не был найден
     */
    public function get_mdluser_byusername($username)
    {
        if ( ! is_string($username) )
        {// неправильный формат данных
            return false;
        }
        return $this->dof->modlib('ama')->user(false)->get_user_by_username($username);

    }
        
    /** Получить пользователя moodle по его id
     * 
     * @return object|boolean - объект из таблицы mdl_user или false
     * @param int $mdluserid - id пользователя в moolde
     */
    public function get_mdluser($mdluserid)
    {
        if ( ! is_numeric($mdluserid) )
        {// неправильный формат данных
            return false;
        }
        if ( ! $this->dof->modlib('ama')->user(false)->is_exists($mdluserid) )
        {// если пользователя не существует - то мы не сможем его вернуть
            return false;
        }
        return $this->dof->modlib('ama')->user($mdluserid)->get();
    }
    
    /** Получить персону деканата по id пользователя в moodle
     * 
     * @return 
     * @param int $mdluserid - id пользователя в moodle
     * @param bool $create[optional] - создавать персону ли персону?
     *                 - true -  создать персону, если пользователь moodle существует, а такой персоны нет
     *                 - false - не создавать персону
     */
    public function get_person($mdluserid)
    {
        if ( ! is_numeric($mdluserid) )
        {// неправильный формат входных данных
            return false;
        }
        if ( ! $user = $this->get_mdluser($mdluserid) )
        {// пользователь moodle не существует
            return false;
        }
        if ( ! $person = $this->dof->storage('persons')->get_by_moodleid($mdluserid) )
        {// в базе нет персоны с таким mdluserid            
        // не нашли, попытаемся создать
            // перепишем пользователя Moodle
            $obj = new stdClass();
            $obj->mdluser = $user->id; 
            $obj->sync2moodle= 1;
            $obj->email = $user->email;
            $obj->firstname = $user->firstname;
            $obj->lastname = $user->lastname;
            $obj->addressid = null;
            if ( ! $personid = $this->dof->storage('persons')->insert($obj) )
            {// не получилось даже создать
                return false;
            }
            // создали - заберем ее из БД
            $person = $this->dof->storage('persons')->get($personid);
        }
        // сравним email
        if ( $person->email == $user->email )
        {// совпали - вернем персону
            return $person;
        }
        // что-то неправильно
        return false;
    }
    
    /** Возвращает запись персоны из деканата по его логину из Moodle
     * @param string $username - логин пользователя
     * @param bool $create - создать персону в деканате, если такова не нашлась
     * @return object - запись персоны из деканата или false, если таковой не был найден
     */
    public function get_person_byusername($username)
    {
        if ( ! $user = $this->get_mdluser_byusername($username) )
        {// не нашли 
            return false;
        }
        // нaйдем персону из деканата или создадим ее
        return $this->get_person($user->id);

    }
    
    // Служебные методы 
    //
    /**
     * Вернуть массив с настройками или одну переменную
     * @param $key - переменная
     * @return mixed
     */
    protected function get_cfg($key=null)
    {
        // Возвращает параметры конфигурации
        include ($this->dof->plugin_path($this->type(),$this->code(),'/cfg/cfg.php'));
        if (empty($key))
        {
            return $sync_personstom;
        }else
        {
            return @$sync_personstom[$key];
        }
    } 
    
    /** Получить часовой пояс пользователя moodle по его id
     * 
     * @return string - часовой пояс в UTC или пустая строка
     * @param int $mdluserid - id пользователя в moolde
     */
    public function get_usertimezone($mdluserid = null)
    {
        global $USER;
        if ( is_null($mdluserid) )
        {   // Берем id текущего пользователя
            $mdluserid = $USER->id;
        }
        if ( ! $user = $this->get_mdluser($mdluserid) )
        {// неправильный формат данных
            return '';
        }
        return dof_usertimezone($user->timezone);
    }
    
    /** Получить дату и время с учетом часового пояса
     * 
     * @return string - время с учетом часового пояса
     * @param int $date - время в unixtime
     * @param string $format - формат даты с учетом символов используемых в strftime
     * @param int $mdluserid - id пользователя в moolde
     * @param boolean $fixday - true стирает нуль перед %d
     *                          false - не стирает
     */
    public function get_userdate($date, $format = '', $mdluserid = null, $fixday = false)
    {
        global $USER;
        if ( is_null($mdluserid) )
        {   // Берем id текущего пользователя
            $mdluserid = $USER->id;
        }
        if ( ! $user = $this->get_mdluser($mdluserid) )
        {// неправильный формат данных
            return strftime($format,$date);
        }
        return dof_userdate($date,$format,$user->timezone,$fixday);
    }
    
    /** Получить дату и время с учетом часового пояса
     * 
     * @return array - время с учетом часового пояса
     * @param int $date - время в unixtime
     * @param int $mdluserid - id пользователя в moolde
     */
    public function get_usergetdate($date, $mdluserid = null)
    {
        global $USER;
        if ( is_null($mdluserid) )
        {   // Берем id текущего пользователя
            $mdluserid = $USER->id;
        }
        if ( ! $user = $this->get_mdluser($mdluserid) )
        {// неправильный формат данных
            return getdate($date);
        }
        return dof_usergetdate($date,$user->timezone);
    }
    
    /** Получить дату и время с учетом часового пояса
     * 
     * @return int - время с учетом часового пояса в Unixtime
     * @param int $date - время в unixtime
     * @param int $mdluserid - id пользователя в moolde
     */
    public function get_make_timestamp($hour=0, $minute=0, $second=0, $month=1, $day=1, $year=0, $mdluserid = null, $applydst=true)
    {
        global $USER;
        if ( is_null($mdluserid) )
        {   // Берем id текущего пользователя
            $mdluserid = $USER->id;
        }
        if ( ! $user = $this->get_mdluser($mdluserid) )
        {// неправильный формат данных
            return $date;
        }
        return dof_make_timestamp($year, $month, $day, $hour, $minute, $second, $user->timezone, $applydst);
    }
}
?>