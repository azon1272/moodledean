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
 * Плагин аутентификации Деканата. Языковые переменные.
 *
 * @package    auth
 * @subpackage dof
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Базовые переменные
$string['pluginname'] = 'Синхронизация с Free Deans Office';
$string['auth_settings_title'] = 'Авторизация с синхронизацией Free Deans Office';
$string['auth_dofdescription'] = 'Авторизация для плагина <a href=\'http://deansoffice.ru\' target=\'_blank\'>Free Deans Office</a>';

// Настройки
$string['settings_recaptcha'] = 'Добавляет форму для подтверждения визуального/звукового элемента на страницу самостоятельной регистрации пользователей с использованием электронной почты. Это защищает ваш сайт от спамеров. Более подробную информацию смотрите на http://www.google.com/recaptcha.';
$string['settings_recaptcha_label'] = 'Включить reCAPTCHA';
$string['settings_title'] = 'Настройки';
$string['settings_dof_departmentid_label'] = 'Подразделение';
$string['settings_dof_departmentid'] = 'Подразделение Электронного Деканата, в которое следует добавлять зарегистрированных пользователей. ';
$string['settings_sendmethod_label'] = '';
$string['settings_sendmethod'] = 'Метод отправки данных о регистрации. Требуется выбрать способ отправки данных о регистрации';

$string['dof_departments_not_add'] = 'Не добавлять пользователей в Деканат';
$string['dof_departments_not_found'] = 'ПОДРАЗДЕЛЕНИЕ НЕ НАЙДЕНО';
$string['dof_departments_version_error'] = 'Требуется обновление хранилища подразделений';
$string['send_method_not_set'] = 'Регистрация не доступна';
$string['send_method_not_found'] = 'ОБРАБОТЧИК СООБЩЕНИЙ НЕ НАЙДЕН';


// Форма регистрации
$string['phone_not_valid'] = 'Некорректный номер телефона';
$string['phone_exists'] = 'Номер телефона уже указан в системе';
$string['otsms_send_success_message'] = 'На указанный Вами номер телефона было выслано SMS с данными для входа в систему.';
$string['otsms_send_error_message'] = 'Во время отправки SMS произошла ошибка. Пожалуйста, свяжитесь с администратором сайта.';
$string['otsms_send_error_title'] = 'Ошибка отправки SMS';
$string['otsms_send_success_title'] = 'Отправка SMS с данными для входа';
$string['email_send_success_message'] = 'На указанный Вами адрес электронной почты было выслано письмо с данными для входа в систему.';
$string['email_send_error_message'] = 'Во время отправки письма произошла ошибка. Пожалуйста, свяжитесь с администратором сайта.';
$string['email_send_error_title'] = 'Ошибка отправки Email';
$string['email_send_success_title'] = 'Отправка электронного письма с данными для входа';

$string['newuserfull'] = ' Здравствуйте, {$a->firstname}!        
На сайте \'{$a->sitename}\' для Вас была создана новая учетная запись. 
Вы можете зайти на сайт по следующим данным: 

Логин: {$a->username} 
Пароль: {$a->newpassword}

Чтобы начать использование сайта \'{$a->sitename}\', 
пройдите по ссылке {$a->link} 

С уважением, администратор сайта \'{$a->sitename}\', {$a->signoff}';
$string['newusershort'] = 'Логин: {$a->username}'."\n".'Пароль: {$a->newpassword}';
?>