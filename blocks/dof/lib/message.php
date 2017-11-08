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
 * Поток сообщений Деканата
 *
 * @package    dof
 * @subpackage messages
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_messages
{
    /** 
     * Объект деканата
     * 
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Отображать очередь
     * 
     * @var bool
     */
    protected $display;
    
    /**
     * Массив уведомлений
     * 
     * @var array
     */
    protected $notices;
    
    /**
     * Массив сообщений
     * 
     * @var array
     */
    protected $messages;
    
    /**
     * Массив ошибок
     * 
     * @var array
     */
    protected $errors;
    
    /**
     * Конструктор класса
     * 
     * @param dof_control $dof - объект деканата 
     * @param bool $display - отображать сообщения
     */
    public function __construct ($dof, $display = false ) 
    {
        $this->dof = $dof;
        $this->display = true;
        $this->notices = [];
        $this->messages = [];
        $this->errors = [];
    }
    
    /**
     * Отобразить сообщения
     * 
     * @param array $opt - опции отображения
     *          ['returnhtml'] => true - Вернуть html код блока вместо печати  
     * @return bool
     */
    public function display($opt = array())
    {
        // Отобразить можно только один раз - защита от множественного вызова
        if ( $this->display )
        {// Отобразить блок
            $messageblock = '';
            
            if ( ! empty($this->errors) )
            {// Блок ошибок
                $messageblock .= dof_html_writer::start_div('dof_errormessages');
                foreach ( $this->errors as $error )
                {
                    $messageblock .= dof_html_writer::div($error, 'dof_errormessage alert alert-danger');
                }
                $messageblock .= dof_html_writer::end_div();
            }
            
            if ( ! empty($this->messages) )
            {// Блок сообщений
                $messageblock .= dof_html_writer::start_div('dof_messages');
                foreach ( $this->messages as $message )
                {
                    $messageblock .= dof_html_writer::div($message, 'dof_message alert alert-success');
                }
                $messageblock .= dof_html_writer::end_div();
            }
            
            if ( ! empty($this->notices) )
            {// Блок уведомлений
                $messageblock .= dof_html_writer::start_div('dof_noticemessages');
                foreach ( $this->notices as $notice )
                {
                    $messageblock .= dof_html_writer::div($notice, 'dof_noticemessage alert alert-warning');
                }
                $messageblock .= dof_html_writer::end_div();
            }
            
            if ( isset($opt['returnhtml']) && $opt['returnhtml'] === true )
            {// Возврат блока
                return dof_html_writer::div($messageblock, 'dof_messageblock');
            } else 
            {// Печать блока
                print ( dof_html_writer::div($messageblock, 'dof_messageblock') );
            }
            
            // Блок отобразили
            $this->display = false;
            return true;
        } else 
        {// Запрет отображения блока
            return false;
        }
    }
    
    /**
     * Добавить сообщение в очередь
     * 
     * @param string $text - Текст сообщения
     * @param string $type - Тип сообщения (message, notice, error)
     * 
     * @throws dof_exception_coding - при неизвестном типе сообщения
     */
    public function add($text, $type = 'notice')
    {
        switch ( $type )
        {
            case 'notice' : 
                $this->notices[] = $text;
                break;
            case 'message' :
                $this->messages[] = $text;
                break;
            case 'error' :
                $this->errors[] = $text;
                break;
            default:
                // Нет такого типа
                throw new dof_exception_coding('undefined_message_type');
        }
    }
    
    /**
     * Наличие ошибок в очереди сообщений
     * 
     * @return bool - Наличие ошибок
     */
    public function errors_exists()
    {
        return ! empty($this->errors);
    }
    
    /**
     * Наличие уведомлений в очереди сообщений
     *
     * @return bool - Наличие уведомлений
     */
    public function notices_exists()
    {
        return ! empty($this->notices);
    }
    
    /**
     * Наличие сообщений в очереди 
     *
     * @return bool - Наличие сообщений
     */
    public function messages_exists()
    {
        return ! empty($this->messages);
    }
}

?>