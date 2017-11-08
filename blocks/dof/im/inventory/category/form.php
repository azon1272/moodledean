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
 * Здесь происходит объявление класса формы, 
 * на основе класса формы из плагина modlib/widgets. 
 * Подключается из init.php. 
 */

// Подключаем библиотеки
require_once('lib.php');
// подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

class dof_im_inventory_category_edit extends dof_modlib_widgets_form
{
    private $obj;
    private $depid;
    /**
     * @var dof_control
     */
    protected $dof;
    
    function definition()
    {// делаем глобальные переменные видимыми
        $this->dof = $this->_customdata->dof;
        $this->id = $this->_customdata->id;
        $this->depid = $this->_customdata->depid;
        if ( $this->id )
        {
            $this->obj = $this->dof->storage('invcategories')->get($this->id);
        }       
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // id
        $mform->addElement('hidden','id', $this->id);
        $mform->setType('id', PARAM_INT);
        //создаем заголовок формы
        $mform->addElement('header','formtitle',  $this->get_form_title($this->id));
        // название категории
        $mform->addElement('text', 'name', $this->dof->get_string('catname','inventory').':', 'size="30"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', $this->dof->modlib('ig')->igs('form_err_required'), 'required',null,'client');
        $mform->addRule('name', $this->dof->modlib('ig')->igs('form_err_required'), 'required',null,'server');
        // кодовое название
        $mform->addElement('text', 'code', $this->dof->get_string('code','inventory').':', 'size="30"');
        $mform->setType('code', PARAM_TEXT);
        // вышестоящая категория
        // в методе category_list_subordinated уже проходит проверка на право use
        $category = $this->get_list_parent($this->id);
        $mform->addElement('select', 'parentid', $this->dof->get_string('parentcategory','inventory').':',$category, 
            array('style' => 'width:100%'));
        // структурное подразделение
        $departments = $this->dof->storage('departments')->departments_list_subordinated(null,'0', null,true);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $departments = $this->dof_get_acl_filtered_list($departments, $permissions);
        $mform->addElement('select', 'departmentid', 
                            $this->dof->get_string('department','inventory').':', $departments, 
                            array('style' => 'width:100%'));
        $mform->setType('departmentid', PARAM_INT);
        $mform->setDefault('departmentid', $this->depid);
        //кнопоки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->get_string('to_save','journal'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        
    }
    /**
     * Задаем проверку корректности введенных значений
     */
    function validation($data,$files)
    {
		$error = array();
		//print_object($data);die;
        if ( ! trim($data['name']) )
        {// если не указано название
            $error['name'] = $this->dof->get_string('err_required','inventory');
        }
        if ( trim($data['code']) AND $obj = $this->dof->storage('invcategories')->
             get_records(array('code'=>strtolower($data['code']))) AND $data['id']<>key($obj)  )
        {
            $error['code'] = $this->dof->get_string('code_not_unique','inventory');          
        }

		return $error;
    }
    
    /**
     * Возвращает строку заголовка формы
     * @param int $ageid
     * @return string
     */
    private function get_form_title($id)
    {
        if ( ! $id )
        {//заголовок создания формы
            return $this->dof->modlib('ig')->igs('create');
        }else 
        {//заголовок редактирования формы
            return $this->dof->modlib('ig')->igs('edit');
        }
        
    }

    /** Возвращает список категорий
     * @param int $id - id категории, которое надо исключить
     * @return array
     */
    protected function get_list_parent($id)
    {
    	$rez = array('0'=>$this->dof->get_string('no_choose','inventory'));
    	$category = $this->dof->storage('invcategories')->category_list_subordinated(null,'0',null,true,'',$this->depid);
    	if ( ! empty($this->obj->parentid) AND ! (array_key_exists($this->obj->parentid, $category)) )
    	{
    	    $rez[$this->obj->parentid] = $this->dof->storage('invcategories')->get($this->obj->parentid)->name;  
    	}
    	if ( ! is_array($category) )
        {//получили не массив - это ошибка';
            return $rez;
        }
        //$path = $this->dof->storage('departments')->get_field($departmentid,'path');
        if ( $id <> 0 )
        {//родителя на дочек вешать нельзя
            // найдем дочек
            $daughterdep = $this->dof->storage('invcategories')->
                           category_list_subordinated($id,'0',null,true);
            // и исключим их
            $category = array_diff_key($category,$daughterdep);
        }
        $rez += $category;
        if ( array_key_exists($id, $rez) AND $id <> 0 )
        {// исключим из массива текущее подразделение, если оно есть
            unset($rez[$id]);
        }
        return $rez;
    }    
    
    
    /**
     * Функци яобработки данных из формы создания/редактирования
     * @return string
     */
    public function process($addvars)
    {
        if ( $this->is_cancelled() )
		{//ввод данных отменен - возвращаем на страницу просмотра договоров
		    redirect($this->dof->url_im('inventory','/category/list.php',$addvars));
		}
		if ( $this->is_submitted() AND $formdata = $this->get_data() )
		{
		 // print_object($formdata);die;
		    $category = new stdClass();
		    $category->name = $formdata->name;
		    $category->code = $formdata->code;
		    $category->parentid = $formdata->parentid;
		    $category->departmentid = $formdata->departmentid;
		    if ( $formdata->id AND ! $formdata->code )
            {// если запись редактируется и код не указан - то заменим код на id
                $category->code = 'id'.$formdata->id;
            }
            if ( ! $formdata->id ) 
            {//создание
                $category->status = 'active';
                if ( $id = $this->dof->storage('invcategories')->insert($category) )
                {// пустой код - сами зададим его
                    if ( empty($category->code) )
                    {
                        $obj = new stdClass();
                        $obj->code = 'id'.$id;
                        $this->dof->storage('invcategories')->update($obj,$id); 
                    }
                }else 
                {// не сохранились данный
                    redirect($this->dof->url_im('inventory','/category/edit.php?error=1',$addvars));
                }
                // сохранились
                redirect($this->dof->url_im('inventory','/category/list.php',$addvars));
            }else 
            {// редактирование
                if ( $this->dof->storage('invcategories')->update($category,$formdata->id) )
                {// сохранились
                    redirect($this->dof->url_im('inventory','/category/list.php',$addvars));
                }else 
                {// не сохранились
                    redirect($this->dof->url_im('inventory','/category/edit.php?error=1&id='.$formdata->id,$addvars));
                } 
            }
                

		}
        
    }   
   
}

    
?>