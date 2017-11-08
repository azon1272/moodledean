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
$string['pluginname'] = 'Synchronization with Free Deans Office';
$string['auth_settings_title'] = 'Authorization with Free Deans Office sync';
$string['auth_dofdescription'] = 'Authorization for <a href=\'http://deansoffice.ru\' target=\'_blank\'>Free Deans Office</a> plugin';

// Настройки
$string['settings_recaptcha'] = 'Adds a form to confirm the visual / audio element on the page self-registration of users by e-mail. This protects your site against spammers. For more information, see http://www.google.com/recaptcha. ';
$string['settings_recaptcha_label'] = 'Enable reCAPTCHA';
$string['settings_title'] = 'Settings';
$string['settings_dof_departmentid_label'] = 'Department';
$string['settings_dof_departmentid'] = 'DOF department which used for adding ner persons ';
$string['settings_sendmethod_label'] = '';
$string['settings_sendmethod'] = 'Send method for delivering a user registration data';

$string['dof_departments_not_add'] = 'Do not add users to DOF';
$string['dof_departments_not_found'] = 'DEPARTMENT NOT FOUND';
$string['dof_departments_version_error'] = 'DOF department storage update required';
$string['send_method_not_set'] = 'Send method not set';
$string['send_method_not_found'] = 'Send method processor not found';

// Форма регистрации
$string['phone_not_valid'] = 'Phone number not valid';
$string['phone_exists'] = 'Current phone number is already exists';
$string['otsms_send_success_message'] = 'A registration data has been sent on Your phone number';
$string['otsms_send_error_message'] = 'There are error occurred during sending registration data';
$string['otsms_send_error_title'] = 'SMS sending error';
$string['otsms_send_success_title'] = 'Sending SMS with registration data';
$string['email_send_success_message'] = 'A registration data has been sent on Your email';
$string['email_send_error_message'] = 'There are error occurred during sending registration data';
$string['email_send_error_title'] = 'Email sending error';
$string['email_send_success_title'] = 'Sending email with registration data';

$string['newuserfull'] = ' Hello, {$a->firstname}!        
There is a new account has been created on site \'{$a->sitename}\' . 
You can login using these data: 

Username: {$a->username} 
Password: {$a->newpassword}

To start using \'{$a->sitename}\' 
please, click to this link {$a->link} 

\'{$a->sitename}\', {$a->signoff}';
$string['newusershort'] = 'Login: {$a->username}'."\n".'Password: {$a->newpassword}';
?>