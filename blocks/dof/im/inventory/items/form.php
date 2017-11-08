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

// подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/** Класс формы поиска оборудования
 * 
 */
class block_dof_im_inventory_item_search_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    function definition()
    {
        $this->dof    = $this->_customdata->dof;
        $departmentid = $this->_customdata->departmentid;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $mform->addElement('hidden','departmentid', $departmentid);
        $mform->setType('departmentid', PARAM_INT);        
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->modlib('ig')->igs('search'));
        // поле "название или код"
        $mform->addElement('text', 'nameorcode', $this->dof->get_string('nameorcode','inventory').':', 'size="20"');
        $mform->setType('nameorcode', PARAM_TEXT);
        // кнопка "поиск"
      //  $mform->addElement('submit', 'search', $this->dof->modlib('ig')->igs('find'));
        // кнопка "сброс"
        $this->add_action_buttons('true', $this->dof->modlib('ig')->igs('find'))  ;    
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        // отключаем select-поле с категориями в случае когда оно не используется
        //$mform->disabledIf('invsetid', 'displayset', 'noteq', 'in_set');
    }
    
    /** Получить список доступных комплектов для этой категории
     * @param int $departmentid - id подразделения в таблице departments, для которого получается список комплектов
     * 
     * @return array
     */
    protected function get_invsets_list($departmentid)
    {
        $records = $this->dof->storage('invsets')->get_records(array('departmentid'=>$departmentid, 'status'=>
            array('active', 'granted', 'notavailable')));
        return $this->dof_get_select_values($records);
    }
    
    /** Получить список категорий
     * @param int $departmentid - id подразделения в таблице departments, для которого получается список категорий
     * 
     * @return array
     */
    protected function get_categories_list($departmentid)
    {
        $records = $this->dof->storage('invcategories')->get_records(array('departmentid'=>$departmentid, 'status'=> 
                array('active')));
        return $this->dof_get_select_values($records);
    }
    
    
        /**
     * Функци для обработки данных из формы создания/редактирования
     * 
     * @return string
     */
    public function process($addvars)
    {
        if ( $this->is_cancelled() )
		{
		    // отменяем фильтр, удаляем переменную
		    if ( isset($addvars['nameorcode']))
		    {
		        unset($addvars['nameorcode']);
		    }
		    redirect($this->dof->url_im('inventory','/items/list.php',$addvars));
		}
		if ( $this->is_submitted() AND $formdata = $this->get_data() )
		{
		    redirect($this->dof->url_im('inventory','/items/list.php',$addvars));
		}
    }	
}

?>