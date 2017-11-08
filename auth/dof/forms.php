<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Плагин аутентификации Деканата. Классы форм.
 *
 * @package    auth
 * @subpackage dof
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot . '/user/editlib.php');

class auth_dof_signup_form extends moodleform 
{
    /**
     * Объявление полей формы
     */
    public function definition() 
    {
        global $USER, $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'supplyinfo', get_string('supplyinfo'),'');
        // Получение полей имени пользователя
        $namefields = useredit_get_required_name_fields();
        foreach ($namefields as $field) 
        {
            $mform->addElement('text', $field, get_string($field), 'maxlength="100" size="30"');
            $mform->setType($field, PARAM_TEXT);
            $mform->addRule($field, get_string('required'), 'required', null, 'server');
        }
        if ( ! array_search('middlename', $namefields) )
        {// Поле отчества не найдено
            $mform->addElement('text', 'middlename', get_string('middlename'), 'maxlength="100" size="30"');
            $mform->setType('middlename', PARAM_TEXT);
        }

        // Email
        $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25"');
        $mform->setType('email', PARAM_RAW_TRIMMED);
        $mform->addRule('email', get_string('missingemail'), 'required', null, 'server');
        
        // Номер телефона
        $mform->addElement('text', 'phone', get_string('phone'), 'maxlength="100" size="25" placeholder="+7 *** *******"');
        $mform->setType('phone', PARAM_RAW_TRIMMED);
        $mform->addRule('phone', get_string('required'), 'required', null, 'server');
        
        // Капча
        if ( $this->signup_captcha_enabled() ) 
        {// Капча включена
            $mform->addElement('recaptcha', 'recaptcha_element', get_string('recaptcha', 'auth'), array('https' => $CFG->loginhttps));
            $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
        }

        // Добавление в форму дополнительных полей пользователя
        profile_signup_fields($mform);

        // Политика использования
        if ( ! empty($CFG->sitepolicy) ) 
        {// Политика пользователя доступна
            $mform->addElement('header', 'policyagreement', get_string('policyagreement'), '');
            $mform->setExpanded('policyagreement');
            $mform->addElement('static', 'policylink', '', '<a href="'.$CFG->sitepolicy.'" onclick="this.target=\'_blank\'">'.get_String('policyagreementclick').'</a>');
            $mform->addElement('checkbox', 'policyagreed', get_string('policyaccept'));
            $mform->addRule('policyagreed', get_string('policyagree'), 'required', null, 'server');
        }

        // Кнопки формы
        $this->add_action_buttons(true, get_string('createaccount'));
    }

    /**
     * Валидация формы
     */
    public function validation($data, $files) 
    {
        global $CFG, $DB;
        $errors = parent::validation($data, $files);

        $authplugin = get_auth_plugin($CFG->registerauth);
        
        if ( $this->signup_captcha_enabled() ) 
        {// Валидаця капчи
            $recaptcha_element = $this->_form->getElement('recaptcha_element');
            if ( ! empty($this->_form->_submitValues['recaptcha_challenge_field']) ) 
            {
                $challenge_field = $this->_form->_submitValues['recaptcha_challenge_field'];
                $response_field = $this->_form->_submitValues['recaptcha_response_field'];
                
                if ( true !== ($result = $recaptcha_element->verify($challenge_field, $response_field)) ) 
                {
                    $errors['recaptcha'] = $result;
                }
            } else 
            {
                $errors['recaptcha'] = get_string('missingrecaptchachallengefield');
            }
        }
        
        // Валидация допполей пользователя
        $dataobject = (object)$data;
        $dataobject->id = 0;
        $errors += profile_validation($dataobject, $files);

        // Валидация email
        if ( ! validate_email($data['email']))
        {
            $errors['email'] = get_string('invalidemail');
        } else
        {
            $exist = $DB->record_exists('user', ['email' => $data['email']]);
            if ( ! empty($exist) )
            {
                $errors['email'] = get_string('emailexists');
            }
        }
        if ( ! isset($errors['email']) )
        {
            // Проверка на поддерживаемый email
            if ( $err = email_is_not_allowed($data['email']) )
            {
                $errors['email'] = $err;
            }
        }
        
        // Валидация номера телефона
        $phone = $authplugin->clean_phonenumber($data['phone']);
        if ( empty($phone) )
        {// Номер не валиден
            $errors['phone'] = get_string('phone_not_valid', 'auth_dof');
        } else
        {
            $exist = $DB->record_exists('user', ['phone2' => $phone]);
            if ( ! empty($exist) )
            {// Номер телефона уже указан в системе
                $errors['phone'] = get_string('phone_exists', 'auth_dof');
            }
            $exist = $DB->record_exists('user', ['username' => $phone]);
            if ( ! empty($exist) )
            {
                $errors['phone'] = get_string('phone_exists', 'auth_dof');
            }
        }
        
        return $errors;
    }

    /**
     * Проверка на доступность капчи
     * 
     * @return bool
     */
    protected function signup_captcha_enabled() 
    {
        global $CFG;
        return ! empty($CFG->recaptchapublickey) && ! empty($CFG->recaptchaprivatekey) && get_config('auth/dof', 'recaptcha');
    }

}
