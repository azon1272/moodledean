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
 * Класс submit-кнопки, запрашивающей подтверждения перед отправкой.
 * Имеет зависимость от виджета modal 
 * 
 * @package    modlib
 * @subpackage widgets
 * @author     Dmitrii Shtolin <d.shtolin@gmail.com>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/form/submit.php');

class MoodleQuickForm_dof_confirm_submit extends MoodleQuickForm_submit
{
    var $_dof = '';
    var $_modalbuttonname = '';
    var $_modaltitle = '';
    var $_modalcontent = '';
    var $_submitbuttonname = '';
    var $_cancelbuttonname = '';

    /** Конструктор класса - для совместимости с будущими версиямиPHP
     *
     *
     * @param     string    Input field name attribute
     * @param     string    Input field value
     * @param     mixed     Either a typical HTML attribute string or an associative array
     * @since     1.0
     * @access    public
     * @return    void
     */
    public function __construct($elementName = null, $elementLabel = null, $options = [], $attributes = null)
    {
        $this->MoodleQuickForm_dof_confirm_submit($elementName, $elementLabel, $options, $attributes);
    }
    
   /**
    * Class constructor (for PHP 4)
    *
    * @access public
    * @param  string $elementName Element's name
    * @param  mixed  $elementLabel Label(s) for an element
    * @param  mixed  $attributes Either a typical HTML attribute string or an associative array
    */
    function MoodleQuickForm_dof_confirm_submit($elementName = null, $elementLabel = null, $options = [], $attributes = null)
    {
        global $DOF;
        parent::HTML_QuickForm_submit($elementName, $elementLabel, $attributes);
        $this->_type = 'dof_confirm_submit';
        $this->_dof = $DOF;
        $this->_modalbuttonname = $DOF->modlib('ig')->igs('continue');
        $this->_modaltitle = $DOF->modlib('ig')->igs('confirm_required');
        $this->_modalcontent = $DOF->modlib('ig')->igs('please_confirm');
        $this->_submitbuttonname = $DOF->modlib('ig')->igs('confirm');
        $this->_cancelbuttonname = $DOF->modlib('ig')->igs('cancel');

        if ( isset($options['modalbuttonname']) )
        {
            $this->_modalbuttonname = $options['modalbuttonname'];
        }
        if ( isset($options['modaltitle']) )
        {
            $this->_modaltitle = $options['modaltitle'];
        }
        if ( isset($options['modalcontent']) )
        {
            $this->_modalcontent = $options['modalcontent'];
        }
        if ( isset($options['submitbuttonname']) )
        {
            $this->_submitbuttonname = $options['submitbuttonname'];
        }
        if ( isset($options['cancelbuttonname']) )
        {
            $this->_cancelbuttonname = $options['cancelbuttonname'];
        }
        
    }
    

    function toHtml()
    {

        $this->updateAttributes([
            'style' => 'display:none'
        ]);
        
        //название кнопки, открывающей модальное окно
        $label = dof_html_writer::span($this->_modalbuttonname, 'btn btn-primary button dof_button');
        //заголовок модального окна
        $title = dof_html_writer::span($this->_modaltitle);
        //основной текст в модальном окне
        $content = dof_html_writer::div($this->_modalcontent);
        //кнопка подтверждения (нажимает скрытый submit формы)
        $contentsubmitbutton = dof_html_writer::tag('label',$this->_submitbuttonname, [
            'for' => 'id_'.$this->getName(),
            'class' => 'btn btn-primary button dof_button'
        ]);
        
        $modalid = 0;
        if ( isset($this->_dof->modlib('widgets')->modalids) )
        {
            $modalid = end($this->_dof->modlib('widgets')->modalids) + 1;
        }
        //кнопка отмены (меняет статус чекбокса, ответственного за отображение модального окна)
        $contentcancelbutton = dof_html_writer::tag('label',$this->_cancelbuttonname, [
            'for' => 'dof_modal_'.$modalid,
            'class' => 'btn btn-primary button dof_button'
        ]);
        $content .= dof_html_writer::div($contentsubmitbutton . $contentcancelbutton);
        
        //добавляем в форму модальное окно
        return $this->_dof->modlib('widgets')->modal($label, $content, $title) . parent::toHtml();
    }
}    
?>