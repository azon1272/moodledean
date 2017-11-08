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

/*
 * Класс поступления нового оборудования
 */
class dof_im_inventory_order_resource_new extends dof_modlib_widgets_form
{
    private $obj;
    /**
     * @var dof_control
     */
    protected $dof;
    protected $depid;
    
    function definition()
    {// делаем глобальные переменные видимыми
        $this->dof = $this->_customdata->dof;
        $this->depid = $this->_customdata->depid;
        // категория
        $catid =  $this->_customdata->catid;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        $disabled = '';
        // список категорий
        // в методе category_list_subordinated уже проходит проверка на право use
        $category = $this->dof->storage('invcategories')->category_list_subordinated(null,'0',null,true,'',$this->depid, 'use');
        
        if ( empty($catid) )
        {// не передали категорию
            if ( ! $category )
            {// создать категорию
                $category[0] = $this->dof->get_string('no_categories','inventory');
                $disabled = 'disabled';
                $mform->setDefault('category', '1'); 
            }else
            {// выбрать их текущих, т.к. хоть 1 категория, НО существует
                $mform->setDefault('category', '2');
            }         
        }else 
        {// усьановить текущую категорию
            $mform->setDefault('category', '2');
            $mform->setDefault('cat_select', $catid);
        }
       
        
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('new_items','inventory') );
        // категория (для каждой radio-кнопки задаем уникальный id чтобы их можно было
        // отличить друг от друга в selenium-тестах)
        $mform->addElement('radio', 'category', '',$this->dof->get_string('new_cat_create','inventory'),'1',
            array('id' => 'new_cat_create'));
        
        $catopts = array('id' => 'select_cat');
        if ( $disabled )
        {// отключить выбор категории из списка
            $catopts['disabled'] = 'disabled';
        }
        $mform->addElement('radio', 'category', '',$this->dof->get_string('select_cat','inventory'),'2',
            $catopts);
        // вышестоящая категория
        $mform->addElement('select', 'cat_select', '',$category, array('style' => 'width:100%'));
        // создать категорию
        // вышестоящая категория-выбор родителя
        $rez = array('0'=>$this->dof->get_string('no_choose','inventory'));
    	if ( ! empty($category[0]) )
    	{
    	    unset($category[0]);
    	}
        $rez += $category;
    	$mform->addElement('select', 'parentid', $this->dof->get_string('parentcategory','inventory').':',$rez, 
            array('style' => 'width:100%'));
        $mform->addElement('text', 'cat_text', $this->dof->get_string('catname','inventory'), array('style' => 'width:100%'));
        $mform->setType('cat_text', PARAM_TEXT);
        // делаем дату в соотвествии с требованием ( д.месяц.г)
        $data = strtolower(date('F').'_r');
        $data = dof_userdate(time(),'%d').' '.$this->dof->modlib('ig')->igs($data).' '.dof_userdate(time(),'%Y');
        $mform->setDefault('cat_text', $data);
        // пояснение для названия
        $mform->addElement('static', 'testname', $this->dof->get_string('format_input_name_items','inventory'), 
                $this->dof->get_string('notice_name_items','inventory','<br>'));
        // название
        $mform->addElement('text', 'name', $this->dof->modlib('ig')->igs('name').':', 'size="30"');
        $mform->setType('name', PARAM_TEXT);  
        $mform->addRule('name', $this->dof->modlib('ig')->igs('form_err_required'), 'required', null, 'client'); 
        $mform->addRule('name', $this->dof->modlib('ig')->igs('form_err_required'), 'required', null);
        // количество quantity
        $mform->addElement('text', 'quantity', $this->dof->modlib('ig')->igs('quantity').':', 'size="7"');
        $mform->setType('quantity', PARAM_INTEGER);  
        $mform->addRule('quantity', $this->dof->modlib('ig')->igs('form_err_required'), 'required', null, 'client'); 
        $mform->addRule('quantity', $this->dof->modlib('ig')->igs('form_err_required'), 'required', null, 'server');
        $mform->addRule('quantity', $this->dof->get_string('only_number','inventory'), 'regex', '/^[1-9]+([0-9]+$|$)/', 'client');
        $mform->addRule('quantity', $this->dof->get_string('only_number','inventory'), 'regex', '/^[1-9]+([0-9]+$|$)/', 'server');                  
        // пояснение серии, инвентарного номера
        $mform->addElement('static', 'testname', $this->dof->get_string('format_input','inventory'), 
                $this->dof->get_string('notice_new','inventory','<br>'));
        // инвентарный номера, серии
        $mform->addElement('textarea', 'inventar', $this->dof->get_string('list_items','inventory'), array('style' => 'width:100%;max-width:500px;height:300px;'));  
        //$mform->addRule('inventar', $this->dof->modlib('ig')->igs('form_err_required'), 'required', null, 'client'); 
        //$mform->addRule('inventar', $this->dof->modlib('ig')->igs('form_err_required'), 'required', null, 'server');              
        // условия для радио
        $mform->disabledIf('cat_text','category','eq','2');
        $mform->disabledIf('parentid','category','eq','2');
        $mform->disabledIf('cat_select','category','eq','1');
        // чек бокс - указывающий на соответствие введенных и заявденных данных
        $mform->addElement('checkbox', 'box', '',$this->dof->get_string('solas','inventory'));
        $mform->setDefault('box', '0');
        // кнопки сохранить/отмена
        $this->add_action_buttons(true, $this->dof->modlib('ig')->igs('save'));

        $mform->applyFilter('__ALL__', 'trim');
        
    }
    /**
     * Задаем проверку корректности введенных значений
     */
    function validation($data,$files)
    {
        $error = array();
        // выбрали создать свою категорию и не заполнили ЭТО поле(пустое)
		if ( $data['category'] == '1' AND empty($data['cat_text']) )
        {
            $error['cat_text'] = $this->dof->modlib('ig')->igs('form_err_required');
        }
        // проверим уникальность имени категории
        if ( $data['category'] == '1' )
        {
            if ( $this->dof->storage('invcategories')->get_records(array('status'=>'active','departmentid'=>$this->depid,'name'=>trim($data['cat_text']))) )
            {// нашли совпадение - скажем об этом
               $error['cat_text'] = $this->dof->get_string('code_not_unique','inventory'); 
            }
        }
        // количество введеных(заполненнхы) данных пользователем
        $count = $this->check_quantity($data, true);
        $text = new stdClass();
        // проверка на соотвествие заявленных данных
        if ( ! isset($data['box']) )
        {// нет галочки - и если разница - ошибка
            if ( $count != $data['quantity'] )
            {
                $text->item = $count;
                $text->askitem = $data['quantity'];
                $error['inventar'] = $this->dof->get_string('count_items','inventory',$text);
            }
        }elseif( $count > $data['quantity'] ) 
        {// голачка стоит на спринять разницу, НО
            // кол введных больше заявленных
            $text->item = $count;
            $text->askitem = $data['quantity'];
            $error['inventar'] = $this->dof->get_string('count_items','inventory',$text);
        }    
        // проверка уникальности
        $unical = $this->check_quantity($data, false, true);
        if ( $unical )
        {
            if( isset($unical['bd']) )
            {
               $error['inventar'] = $this->dof->get_string('unical_bd','inventory',$unical['bd']);  
            }else 
            {
                $error['inventar'] = $this->dof->get_string('unical_record','inventory',$unical['record']); 
            } 
        }
            
        return $error;
    }
    

    /**
     * Функци для обработки данных из формы создания/редактирования
     * 
     * @return string
     */
    public function process($addvars)
    {
        if ( $this->is_cancelled() )
		{//ввод данных отменен - возвращаем на страницу просмотра договоров
		    redirect($this->dof->url_im('inventory','/index.php',$addvars));
		}
		if ( $this->is_submitted() AND $formdata = $this->get_data() )
		{
		    // подключаем приказ
            require_once($this->dof->plugin_path('storage','invitems','/order/invitems_order.php'));
		    // все проверки прошли работам дальше
		    // выбор категории
		    if ( $formdata->category == '2' )
		    {// выбрали из текущих категори
		        $catid = $formdata->cat_select;
		    }elseif( $formdata->category == '1' ) 
		    {// создаем свою категорию 
		        $catobj = new stdClass();
		        $catobj->name = $formdata->cat_text;
		        $catobj->code = '';
		        $catobj->parentid = $formdata->parentid;
		        $catobj->status = 'active';
		        $catobj->departmentid = $addvars['departmentid'];
		        $catid = $this->dof->storage('invcategories')->insert($catobj); 
                // вставим код
		        $obj = new stdClass();
                $obj->code = 'id'.$catid;
                $this->dof->storage('invcategories')->update($obj,$catid); 
            }
            // ВСЁ хорошо, подключаем приказ
		    $order = new dof_storage_invitems_order_new_items($this->dof);
		    // ФОРМИРЕУМ ОРДЕР(приказ)
		    $mas = $this->check_quantity($formdata);
		    
		    $orderobj = new stdClass();
		    $orderobj->date = time();
		    
            //сохраняем автора приказа
            if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
            {// неудача - скажем об этом
                $error = $this->dof->get_string('no_found_person','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';
            }
            $orderobj->ownerid = $personid;
            // похразделение
		    if ( isset($addvars['departmentid']) AND $addvars['departmentid'] )
            {// установим выбранное на странице id подразделения 
                $orderobj->departmentid = $addvars['departmentid'];
            }else
            {// установим id подразделения из сведений о том кто формирует приказ
                $orderobj->departmentid = $this->dof->storage('persons')->get_field($personid,'departmentid');
            }            		    
		    $orderobj->data = new stdClass();
		    $orderobj->data->quantity = $formdata->quantity;
		    $orderobj->data->name = $formdata->name;
		    $orderobj->data->categoryid = $catid;
		    $orderobj->data->mas = $mas;

		    // сохраним приказ
		    if ( ! $order->save($orderobj) )
		    {// неудача - скажем об этом
                $error = $this->dof->get_string('no_save_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';		       
		    }
		    
		    // подписываем
		    if ( ! $order->sign($personid) ) 
		    {// неудача - скажем об этом
		        $error = $this->dof->get_string('no_sign_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';			        
		    }
		    
		    // исполняем
			if ( ! $order->execute() )
		    {// неудача - скажем об этом
		        $error = $this->dof->get_string('no_execute_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';			        
		    }	
		    // Тут все хорошо, скажем об этом
		    $returnlink = $this->dof->url_im('inventory','/invorders/view.php',
                $addvars + array('id' => $order->get_id() ));
            redirect($returnlink);
               	    
		}
        
    }   
    
    
    /**
     * Функция обработки данных из формы создания/редактирования
     * @param array $data - данные, переданные из формы
     * @param bool $flag - вывести количесто введенных элементов или нет
     * 						необходимо для проверок
     * @param bool $check - проверять на уникальность введные инвентарные номера или нет
     * bool  
     * @return string
     */
    public function check_quantity($data, $flag=false, $check = false)
    {
        // проблема в том, что через get_data приходит объект
        // а через validation($data) - массив, поэтому и проверка 
        if ( is_array($data) )
        {
            $numbers = $data['inventar'];
        }else 
        {
            $numbers = $data->inventar;
        }    
        $mas = array();
        // разбиваем на строки(количество инвент номеров)
        // строки разделены клавишей ENTER
        $mas = explode("\n",$numbers);
        // надо учесть то, что и возможно нажат ентер и не 1 раз
        // удалим эти пустышки
        
        foreach( $mas as $num=>$value )
        {
            $value = trim($value);
            if ( empty($value) )
            {
                unset($mas[$num]);
            }
        }
        // подсчитаем кол элементов
        // вывод кол строк(нового оборудования)
        if ( $flag )
        {
            return count($mas);
        }
        // преобразуем массив к массиву в массиве для вывода в ордер
        // переменная для вывода
        $out = array();
        // внутренний массив в переменной $out
        //    $out[] = array { 0 => array{ 
        //                                 0 => $outsmall[0] 
        //                                 1 => $outsmall[1]
        //                               }
        //                     1 = > array{ ...   
        //                    }  
        $outsmall = array();
        // для проверки на уникальность введенныхинвентарных номеров
        $code = array();
        foreach ( $mas as $value )
        {
            // 3 вида значений 1)inv;seria 2) ;seria 3) inv
            // обрабатываем их
            // разбиваем на инвент и серийный номера
            $outsmall = explode(";",$value);
            // чтоб не было ошибок запишем пустоту
            if ( empty($outsmall[1]) )
            {
                $outsmall[1] = '';
            }else 
            {// для проверки ввода уникальности кодового номера
                $code[] = trim($outsmall[1]);
            }
            // дадим названия полей
            $outmas = array();
            $outmas['serialnum'] = trim($outsmall[0]);
            $outmas['invnum']    = trim($outsmall[1]);
            $out[] = $outmas;
        }
        
        // проверка уникальности
        if ( $check )
        {
            foreach ( $code as $num=>$value )
            {
                // удаляем то значение, которое берем
                unset($code[$num]);
                // теперь ищем совпадение 
                if ( in_array($value, $code) )
                {// совпадение в веденных данных
                    return array('record'=>$value);
                }
                
                $status = $this->dof->workflow('invitems')->get_list_param('real');
                $record = $this->dof->storage('invitems')->get_records(array('code'=>$value,
                		'status'=>$status));
                
                if ( $record )     
                {// совпадение в бд
                    return array('bd'=>$value);
                }
            }
            // не нашли ошибок
            return false;
        }    

        // выводим результат
        return $out;
    }       
    
}


/*
 * Класс сисания оборудования 
 * работаетс приказами о списании оборудования
 */
class dof_im_inventory_order_resource_delete extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    private $obj;
    protected $dof;
    protected $depid;

    /*
     * Метод отрисовки формы на списание ообрудования
     */ 
    function definition()
    {// делаем глобальные переменные видимыми
        $this->dof = $this->_customdata->dof;
        $this->depid = $this->_customdata->depid;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // справка
        $mform->addElement('static', 'testname', $this->dof->get_string('format_input','inventory'), 
                $this->dof->get_string('notice_delete','inventory','<br>'));
        // поле ввода - техтареа        
        $mform->addElement('textarea', 'item_codes', $this->dof->get_string('list_items','inventory'), 
            array('style' => 'width:100%;max-width:400px;height:380px;'));
        
        if ( isset($this->_customdata->orderid) AND $orderid = $this->_customdata->orderid )
        {// есть ордер - установим в текстарея - его значения
            // подключаем приказ
            require_once($this->dof->plugin_path('storage','invitems','/order/invitems_order.php'));
            $order = new dof_storage_invitems_order_delete_items($this->dof);
            $order = $order->load($orderid);
            $data = $order->data;
            $text = '';
            foreach ( $data as $field=>$value )
            {
                // берем поля с данными приказа. Если это поле "invnum", 
                // значит берем его значение и меняем статус
                // очень опасная функция(прочитать мануал по ней), сторого такое равенство,
                // т.к. значение 0-означает, что запись найдена, но это и false, потому и (bool) 
                if ( strpos($field, 'invnum') !== (bool)false )
                {
                    $text .=  $value."\r\n";   
                }
            }
            // установим значения по умолчанию
            $mform->setDefault('item_codes', $text);     
        }    
        $mform->setType('item_codes', PARAM_TEXT);   
        $mform->addRule('item_codes', $this->dof->modlib('ig')->igs('form_err_required'), 'required', null, 'client'); 
        $mform->addRule('item_codes', $this->dof->modlib('ig')->igs('form_err_required'), 'required', null);
            
        // кнопки сохранить/отмена
        $this->add_action_buttons(true, $this->dof->get_string('to_save','journal'));
        $mform->applyFilter('__ALL__', 'trim');
    }  


     /**
     * Задаем проверку корректности введенных значений
     * проверям существ записей в бд, заполненость обязат полей,
     * чтобоборудование не состояло в комплекте и было активным
     * 
     * @param array $data - массив дынных, введенных в форме
     * 
     * return array $error - массив с ошибками, если таковы есть
     */
    function validation($data,$files)
    {
        $error = array();
        // получим дату
        $codes = trim($data['item_codes']);
        // проверим на пустоту
        if ( ! $codes )
        {
            $error['item_codes'] = $this->dof->modlib('ig')->igs('form_err_required');
        }
        $codes = $this->get_mas_codes($codes, true);
        if ($codes)
        {// есть ошибки в воде данных
            if( isset($codes['bd']) )
            {// нет записи в бд - нельзя списать не существующее
               $error['item_codes'] = $this->dof->get_string('unical_bd_no','inventory',$codes['bd']);  
            }
            if ( isset($codes['record']) ) 
            {// повтор записи
                if ( empty($error['item_codes']) )
                {// до этого записей об ошибках не было
                    $error['item_codes'] = $this->dof->get_string('unical_record','inventory',$codes['record']);
                }else 
                {// ещё одна ошибка - вывод с новой строки
                    $error['item_codes'] .= '<br>'.$this->dof->get_string('unical_record','inventory',$codes['record']); 
                }     
            } 
            if ( isset($codes['scrapped']) ) 
            {// оборудование уже списано
                if ( empty($error['item_codes']) )
                {// до этого записей об ошибках не было
                    $error['item_codes'] = $this->dof->get_string('item_scrapped','inventory',$codes['scrapped']);
                }else 
                {// ещё одна ошибка - вывод с новой строки
                    $error['item_codes'] .= '<br>'.$this->dof->get_string('item_scrapped','inventory',$codes['scrapped']); 
                } 
            }         
            if ( isset($codes['invset']) ) 
            {// оборудование состоит в комплекте
                if ( empty($error['item_codes']) )
                {// до этого записей об ошибках не было
                    $error['item_codes'] = $this->dof->get_string('item_in_invset','inventory',$codes['invset']);
                }else 
                {// ещё одна ошибка - вывод с новой строки
                    $error['item_codes'] .= '<br>'.$this->dof->get_string('item_in_invset','inventory',$codes['invset']); 
                } 
            }
            if ( isset($codes['right']) ) 
            {// оборудование состоит в комплекте
                if ( empty($error['item_codes']) )
                {// до этого записей об ошибках не было
                    $error['item_codes'] = $this->dof->get_string('no_right_for_delete_item','inventory',$codes['right']);
                }else 
                {// ещё одна ошибка - вывод с новой строки
                    $error['item_codes'] .= '<br>'.$this->dof->get_string('no_right_for_delete_item','inventory',$codes['right']); 
                } 
            } 
            
        }
        return $error;
        
    }

    /* Функци для обработки данных из формы списания оборудования
     * подключается приказ о списании, и формируется сам приказ  
     * 
     * @param arrya $addvars - массив доп данных для перехода по ссылкам,
     * 							чаще просто $addvars[departmentid]  
     * 
     * @return string
     */
    public function process($addvars)
    {
        if ( $this->is_cancelled() )
		{//ввод данных отменен - возвращаем на страницу просмотра договоров
		    redirect($this->dof->url_im('inventory','/index.php',$addvars));
		}
		if ( $formdata = $this->get_data() )
		{
	        // подключаем приказ
            require_once($this->dof->plugin_path('storage','invitems','/order/invitems_order.php'));
		    // все проверки прошли работам дальше
		    $order = new dof_storage_invitems_order_delete_items($this->dof);
		    // получим массив с данными
		    $masitems = $this->get_mas_codes($formdata->item_codes);
		    
		    // ФОРМИРЕУМ ОРДЕР(приказ)
		    $orderobj = new stdClass();
		    $orderobj->date = time();
		    
            //сохраняем автора приказа
            if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
            {// неудача - скажем об этом
                $error = $this->dof->get_string('no_found_person','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';
            }
            $orderobj->ownerid = $personid;
            // подразделение
		    if ( isset($addvars['departmentid']) AND $addvars['departmentid'] )
            {// установим выбранное на странице id подразделения 
                $orderobj->departmentid = $addvars['departmentid'];
            }else
            {// установим id подразделения из сведений о том кто формирует приказ
                $orderobj->departmentid = $this->dof->storage('persons')->get_field($personid,'departmentid');
            }            		    
		    $orderobj->data = new stdClass();
		    $orderobj->data->mas = $masitems;
			// сохраним приказ
		    if ( ! $order->save($orderobj) )
		    {// неудача - скажем об этом
                $error = $this->dof->get_string('no_save_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';		       
		    }
            // не делаем подпись, так как он ещё измениться
	        // на страницу просмотра того, что удаляем для подтверждения
	        $id = $order->get_id();
	        redirect($this->dof->url_im('inventory','/invorders/resource_delete_view.php?id='.$id,$addvars));	    
	    }    
	}    
        
    /* Делает из списка списанного оборудования массив
     * 
     * @param string $codes - строка с оборудованием, отделенные \n
     * @param bool $check - проверить массив(true)/ не проверять массив(false)
     * 						проверки на сущ записи в бд, повтор ввода записи, спиано ли уже это оборудование 
     * return array $masitems
     */
    private function get_mas_codes($codes, $check=false)
    {
        $masitems = array();
        // разбиваем на строки(количество инвент номеров)
        // строки разделены клавишей ENTER
        $masitems = explode("\n",$codes);
        // надо учесть то, что и возможно нажат ентер и не 1 раз
        // удалим эти пустышки и пробелы в строках
        foreach( $masitems as $num=>$value )
        {
            $value = trim($value);
            if ( empty($value) )
            {
                unset($masitems[$num]);
            }else 
            {
                $masitems[$num] = $value;
            }
        }
        // проверить массив на наличие в бд
        if ( $check )
        {
            $error = array();
            // проверки над массивом
            foreach ( $masitems as $num=>$value )
            {
                // удаляем то значение, которое берем
                // чтоб не наткгуться на самого себя и каждый раз
                // уменьшать сам массив
                unset($masitems[$num]);
                // теперь ищем совпадение 
                if ( in_array($value, $masitems) )
                {// совпадение в веденных данных, т.е. несколько одинаковых номеров за 1 раз
                    $error['record'] = $value;
                }
                // получаем реальные статусы
                $status = $this->dof->workflow('invitems')->get_list_param('real');
                // есть в бд - хорошо, нету - плохо скажем об этом
                // проверка на наличие в бд записи                    
                $record = $this->dof->storage('invitems')->get_records(array('code'=>$value,
            		'status'=>$status));
                // проверим, не списано ЛИ уже ЭТо оборудование
                $scrapped =   $this->dof->storage('invitems')->get_records(array('code'=>$value,
            		'status'=>'scrapped'));
                
                // не нашли в бд - ошибка !!!
                // т.е. оборудование с таким кодом не существует в бд, 
                // потому и нечего удалять
                if ( ! $record )     
                {
                    $error['bd'] = $value;
                }else 
                {// есть ли право его списать ?
                    if ( ! $this->dof->storage('invitems')->is_access('delete',key($record)) )
                    {
                        $error['right'] = $value;        
                    }
                }
                // оборудование уже списано - ошибка !!!
                if ( $scrapped )     
                {
                    $error['scrapped'] = $value;
                }
                // проверка, чтоб оборудование было не в комплекте !!!
                if ( $record )
                {
                    $id = key($record);
                    if ( $this->dof->storage('invitems')->get_field($id, 'invsetid') )
                    {
                        $error['invset'] = $value;
                    }
                }
            }
            // вернем ошибки либо пустой массив
            return $error; 
        }
        // вернем массив без проверок
        return $masitems;              
    }    

}   



/* Класс отрисовки оборудования для списания, 
 * с возможностью не все списать (форма подтверждения) 
*/
class dof_im_inventory_order_resource_delete_view extends dof_modlib_widgets_form
{
    
    /**
     * @var dof_control
     */
    protected $dof;
    // подразделение
    protected $depid;
    // id ордера
    protected $orderid;

    /*
     * Метод отрисовки формы подтверждения списания оборудования
     */ 
    function definition()
    {// делаем глобальные переменные видимыми
        $this->dof = $this->_customdata->dof;
        $this->depid = $this->_customdata->depid;
        $this->orderid = $this->_customdata->orderid;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // получим массив оборудования, которое хотим спиать
        $itemids = $this->get_item_ids();
        // заголовок
        $mform->addElement('header','headname', $this->dof->get_string('yes_delete_resource','inventory'));
        // html
        // отрисовываем таблицу с классами moodle
        $mform->addElement('static', 'testname', $this->dof->get_string('notice','inventory'), 
                $this->dof->get_string('notice1','inventory','<br>'));
        $mform->addElement('html', '<br><table class="generaltable boxaligncenter" cellspacing="2" cellpadding="2">');
        $mform->addElement('html', "<tr>
        	<th class='header c0' scope='col' style='vertical-align: top; text-align: center; white-space: nowrap;'> № </th>
        	<th class='header c1' scope='col' style='vertical-align: top; text-align: center; white-space: nowrap;'>"
                    .$this->dof->get_string('itemname','inventory')."</th>
        	<th class='header c2' scope='col' style='vertical-align: top; text-align: center; white-space: nowrap;'>"
                    .$this->dof->get_string('invnum','inventory','<br>')."</th>
        	<th class='header c3' scope='col' style='vertical-align: top; text-align: center; white-space: nowrap;'>"
                    .$this->dof->get_string('serialnum','inventory','<br>')."</th>
            <th class='header c3' scope='col' style='vertical-align: top; text-align: center; white-space: nowrap;'>"
                    .$this->dof->get_string('department','inventory')."</th>        
        	<th class='header c4' scope='col' style='vertical-align: top; text-align: center; white-space: nowrap;'>"
                    .$this->dof->modlib('ig')->igs('confirm')."</th>
 			</tr>");
        // переменный для првильного присвоения того или иного класса элементам формы
        $i = 1;
        $j = 0;
        // перебираем наши ids
        foreach ( $itemids as $itemid )
        {
            $item = $this->dof->storage('invitems')->get($itemid);
            $mform->addElement('html', "<tr class='r".$j." '>
            						<td class='cell' style='text-align: center;'>".$i."</td>
        							<td class='cell' style='text-align: center;'>".$item->name."</td>
        							<td class='cell' style='text-align: center;'>".$item->code."</td>
        							<td class='cell' style='text-align: center;'>".$item->serialnum."</td>
        							<td class='cell' style='text-align: center;'>".$this->dof->im('departments')->get_html_link($item->departmentid,'true')."</td>  
        							<td class='cell' style='text-align: center;'>");
            // добавим чекбокс
            $mform->addElement('checkbox', 'check_'.$itemid );
            $mform->addElement('html', "</td></tr>");
            $mform->setDefault('check_'.$itemid, 'cheked'); 
            
            // cчётчики
            $i++;
            $j++;    
            // обнулим для чередования 0 и 1
            // необходимо для корректного отображения классов
            if ( $j == 2 )
            {
                $j = '0';
            }          
        }
        // закрываем таблицу
        $mform->addElement('html', '</table>');

        // кнопки сохранить/отмена
        $this->add_action_buttons(true, $this->dof->get_string('scrapp','inventory'));
        
    }  

    /*
     * Получить список itemid из приказа
     * @param bool $code - вернуть код или id
     * 
     * return array $codes/$itemids
     */
    private function get_item_ids($code = false)
    {
        // подключаем приказ
        require_once($this->dof->plugin_path('storage','invitems','/order/invitems_order.php'));
		// все проверки прошли работам дальше
		$order = new dof_storage_invitems_order_delete_items($this->dof); 
		// данные приказа
        $data = $order->load($this->orderid);
        $data = $data->data;
        // соберем id
        $itemids = array();
        $codes = array();
        foreach ( $data as $field=>$id )
        {// есть в поле запись itemid - значит нам нужно
            // очень опасыя функция, сторого такое равенство
            if ( strpos($field, 'itemid') !== (bool)false )
            {
                $itemids[] = $id;
                $codes[$id] = $this->dof->storage('invitems')->get_field($id, 'code');
            }
        }
        // вернуть только код        
        if ( $code )
        {
            return $codes;
        }
        return $itemids;        
    }

    /*
     * Метод проверки ввода корректности данных
     *  на стороне сервера
     *  
     *  @param array $data - данные, пришедшие из формы
     *  
     *  return array - массив ошибок, если таковые есть
     */
    function validation($data,$files)
    {

        // проверим оборудование, которое будет списано
        // на корректность
		$itemids = $this->get_item_ids();
		// убрана голочка с подтверждения - значит не списываем
		// это оборудования и удалим его из общего массива
	    foreach ( $itemids as $key=>$id )
	    {
            $check = 'check_'.$id;    
	        if ( ! isset($data[$check]) )
	        {
	            unset($itemids[$key]);
	        }
	    }        

        $error = array();
        // проверки над массивом данных, которые надо СПИСАТЬ
        foreach ( $itemids as $id )
        {
            // НЕТ ОБЪЕКТА В БД !!
            if ( ! $item = $this->dof->storage('invitems')->get($id) )
            {
                $error['check_'.$id] = $this->dof->get_string('unical_bd_no','inventory', $id);
            }
            // списано уже оборудование
            if ( $item->status == 'scrapped' )     
            {
                $error['check_'.$id] = $this->dof->get_string('item_scrapped','inventory',$id); 
            }
            // проверка, чтоб оборудование было не в комплекте !!!
            if ( $item->invsetid != 0 )
            {
                $error['check_'.$id] = $this->dof->get_string('item_in_invset','inventory',$id); 
            }
        }
        // вернум ошибки
        return $error;	    
    
    }

    /*
     * Метод исполнения/ дуйствия, после нажатия на кноку списать/отмена
     * 
     * @param array $addvars - массви с доп данными для корректного перенаправления со страницы
     * 							, как правило это departmentid
     * 
     * return no
     */
    function process($addvars)
    {
        if ( $this->is_cancelled() )
		{//ввод данных отменен - возвращаем на страницу просмотра договоров
		    // приказ не исполнен, его в сторону, новая страница - новый приказ
		    redirect($this->dof->url_im('inventory','/invorders/resource_delete.php?',$addvars));
		}
		// нажали "списать"
		if ( $formdata = $this->get_data() )
		{
		    // удалим номер приказа - не нужен
		    unset($addvars['orderid']);
		    // через формдату передам только то, что не нужно списывать
		    // потому просто исключим эти id из уже существующих
		    $itemids = $this->get_item_ids();
		    $codes = $this->get_item_ids(true);
		    foreach ( $itemids as $key=>$id )
		    {
                $check = 'check_'.$id;    
		        if ( ! isset($formdata->{$check}) )
		        {// удаляем значения и у массива ключей и у массива кодов(инвент номера)
		            unset($itemids[$key]);
		            unset($codes[$id]);
		        }
		    }
            // значит удалим из ордердата всё что связано сэтим договором
            // и переоформим его
            if ( ! $this->dof->storage('orderdata')->delete_on_orderid($this->orderid) )
            {// неудача - скажем об этом
		        $error = $this->dof->get_string('no_sign_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';	
            }
            
            // подключаем приказ
            require_once($this->dof->plugin_path('storage','invitems','/order/invitems_order.php'));

		    // перезапишем наш ордер
		    // запишем новые
		    $order = new dof_storage_invitems_order_delete_items($this->dof,$this->orderid);
		    $ord = $order->load($this->orderid);
		    $ord->data = new stdClass();
		    $ord->data->mas = $codes;
		    // пересохраним ордер
		    $order->save($ord);
             
		    //сохраняем автора приказа
            if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
            {// неудача - скажем об этом
                $error = $this->dof->get_string('no_found_person','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';
            }            
		    // подписываем
		    if ( ! $order->sign($personid) ) 
		    {// неудача - скажем об этом
		        $error = $this->dof->get_string('no_sign_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';			        
		    }	
			// исполняем
			if ( ! $order->execute() )
		    {// неудача - скажем об этом
		        $error = $this->dof->get_string('no_execute_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';			        
		    }

		    // Тут все хорошо, скажем об этом
		    $returnlink = $this->dof->url_im('inventory','/invorders/view.php',
                $addvars + array('id' => $order->get_id() ));
            redirect($returnlink);		    
		}
    }    
}   


/*
 * Класс формирования нового комплекта
 */
class dof_im_inventory_order_set_invset extends dof_modlib_widgets_form
{
    private $obj;
    /**
     * @var dof_control
     */
    protected $dof;
    protected $depid;
    protected $count;
    protected $items;
    
    function definition()
    {// делаем глобальные переменные видимыми
        $this->dof = $this->_customdata->dof;
        $this->depid = $this->_customdata->depid;
        $catid = $this->_customdata->catid;
        if ( ! empty($this->_customdata->count) )
        {
            $this->count = $this->_customdata->count;
            $this->items = $this->_customdata->items;
        }    
     
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        $disabled = '';
        // список категорий
        $category = $this->dof->storage('invcategories')->category_list_subordinated(null,'0',null,true,'',$this->depid, 'use');
        
        if ( empty($catid) )
        {// не передали категорию
            if ( ! $category )
            {// создать категорию
                $category[0] = $this->dof->get_string('no_categories','inventory');
                $disabled = 'disabled';
                $mform->setDefault('category', '1'); 
            }else
            {// выбрать их текущих, т.к. хоть 1 категория, НО существует
                $mform->setDefault('category', '2');
            }         
        }else 
        {// усьановить текущую категорию
            $mform->setDefault('category', '2');
            $mform->setDefault('cat_select', $catid);
        }     
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('set_invset_doing','inventory') );
        // категория
        $mform->addElement('radio', 'category', '',$this->dof->get_string('new_cat_create','inventory'),'1');
        $mform->addElement('radio', 'category', '',$this->dof->get_string('select_cat','inventory'),'2',$disabled);

        // вышестоящая категория

        $mform->addElement('select', 'cat_select', '',$category, array('style' => 'width:100%'));
        // создать категорию
        // вышестоящая категория-выбор родителя
        $rez = array('0'=>$this->dof->get_string('no_choose','inventory'));
    	if ( ! empty($category[0]) )
    	{
    	    unset($category[0]);
    	}
        $rez += $category;
    	$mform->addElement('select', 'parentid', $this->dof->get_string('parentcategory','inventory').':',$rez, 
            array('style' => 'width:100%'));
        $mform->addElement('text', 'cat_text', $this->dof->get_string('catname','inventory'), array('style' => 'width:100%'));
        $mform->setType('cat_text', PARAM_TEXT);
        // делаем дату в соотвествии с требованием ( д.месяц.г)
        $data = strtolower(date('F').'_r');
        $data = dof_userdate(time(),'%d').' '.$this->dof->modlib('ig')->igs($data).' '.dof_userdate(time(),'%Y');
        $mform->setDefault('cat_text', $data);
        // количество quantity(сколько комплектов сформировать)
        $mform->addElement('text', 'quantity', $this->dof->get_string('count_invset','inventory').':', 'size="7"');
        $mform->setType('quantity', PARAM_INTEGER);  
        $mform->addRule('quantity', $this->dof->modlib('ig')->igs('form_err_required'), 'required', null, 'client'); 
        $mform->addRule('quantity', $this->dof->modlib('ig')->igs('form_err_required'), 'required', null, 'server');
        $mform->addRule('quantity', $this->dof->get_string('only_number','inventory'), 'regex', '/^[1-9]+([0-9]+$|$)/', 'client');
        $mform->addRule('quantity', $this->dof->get_string('only_number','inventory'), 'regex', '/^[1-9]+([0-9]+$|$)/', 'server');                  
        // количество в комплекте
        $mform->addElement('text', 'count_item_in_invset', $this->dof->get_string('count_item_in_invset','inventory').':', 'size="7"');
        $mform->setType('count_item_in_invset', PARAM_INTEGER);  
        $mform->addRule('count_item_in_invset', $this->dof->modlib('ig')->igs('form_err_required'), 'required', null, 'client'); 
        $mform->addRule('count_item_in_invset', $this->dof->modlib('ig')->igs('form_err_required'), 'required', null, 'server');
        $mform->addRule('count_item_in_invset', $this->dof->get_string('only_number','inventory'), 'regex', '/^[1-9]+([0-9]+$|$)/', 'client');
        $mform->addRule('count_item_in_invset', $this->dof->get_string('only_number','inventory'), 'regex', '/^[1-9]+([0-9]+$|$)/', 'server');  
       
        if ( ! empty($this->count) )
        {
            $mform->addElement('static', 'static', $this->dof->get_string('notice','inventory'), $this->dof->get_string('notice2','inventory'));
            if ( $this->count > 1 )
            {// Формируется несколько одинаковых комплектов
                for ( $i=1; $i<=$this->items; $i++)
                {
                    $mform->addElement('select', 'cat_'.$i, $this->dof->get_string('items','inventory',$i) , $category, array('style' => 'width:100%'));
                    $mform->setType('cat_'.$i, PARAM_INT);
                }
            }else
            {// Формируется один комплект
                for ( $i=1; $i<=$this->items; $i++)
                {
                    $mform->addElement('select', 'cat_'.$i, $this->dof->get_string('items','inventory',$i) , $category, array('style' => 'width:100%'));
                    $mform->setType('cat_'.$i, PARAM_INT);
                    // Добавляем AJAX-элемент для выбора конкретного оборудования из категории
                    $ajaxoptions = array(
                        'parentid'   => 'id_cat_'.$i,
                        'plugintype' => 'im',
                        'plugincode' => 'inventory',
                        'querytype'  => 'im_inventory_newinvset_form'
                    );
                    $mform->addElement('dof_ajaxselect', 'item_'.$i, '', null, $ajaxoptions);
                    $mform->setType('item_'.$i, PARAM_INT);
                }
            }
        }
        
        // условия для радио
        $mform->disabledIf('cat_text','category','eq','2');
        $mform->disabledIf('parentid','category','eq','2');
        $mform->disabledIf('cat_select','category','eq','1');

        // кнопки сохранить/отмена
        $this->add_action_buttons(true, $this->dof->modlib('ig')->igs('continue'));

        $mform->applyFilter('__ALL__', 'trim');
    }
    
      
    /**
     * Задаем проверку корректности введенных значений
     */
    function validation($data,$files)
    {
        
        $error = array();
        // выбрали создать свою категорию и не заполнили ЭТО поле(пустое)
		if ( $data['category'] == '1' AND empty($data['cat_text']) )
        {
            $error['cat_text'] = $this->dof->modlib('ig')->igs('form_err_required');
        }
        // проверим уникальность имени категории
        if ( $data['category'] == '1' )
        {
            if ( $this->dof->storage('invcategories')->get_records(array('status'=>'active','name'=>trim($data['cat_text']))) )
            {// нашли совпадение - скажем об этом
               $error['cat_text'] = $this->dof->get_string('code_not_unique','inventory'); 
            }
        }
        // проверка того, что хватит оборудования для формирования 
        // для указанного числа комплектов
        if ( $this->count )
        {
            $invgroup = array();
            // сделаем массив для удобства работы
            for ( $i=1; $i<=$data['count_item_in_invset']; $i++ )
            {
                $group = 'cat_'.$i;
                $invgroup[$i] = $data[$group];
            }
            // сортировка по пути
            // usort($invgroup, array('dof_im_inventory_order_set_invset', 'sortapp_by_depth')); 

            // проверим на то, когда вводим сами id и ИХ нельзя повторять
            if ( $this->count == '1' )
            {
                $items = array();
                for ( $i=1; $i<=$data['count_item_in_invset']; $i++ )
                {
                    $item = 'item_'.$i;
                    if ( ! in_array( $data[$item], $items) )
                    {// 0 не записываем, т.к. он автоматом
                        if ( ! empty($data[$item]) )
                        {
                            $items[$i] = $data[$item];
                        }    
                    }else 
                    {
                        $error[$item] = $this->dof->get_string('repeat_item','inventory',$data[$item]); 
                    }
                }
                
                // очень щекотливая проверка
                // 2 объекта в комплекте. кат1 - 1а - оборудование
                //                        кат2 - 2а - оборудование
                // кат1 родитель кат2
                // Пользователь выбирает кат1(ему доступ и дочерн ообрудование) и выбирает 2a(т.е. из дочки)
                //                       кат2(ему доступно только 2а) и ставит ЛЮБОЕ. Дилема, ведь 2а уже выбрано выше

                //  готовим данные
                // вспомогат массив
                $masequality = array();
                foreach ( $items as $i=>$itemid )
                {
                    //  кат оборудования
                    $catid = $this->dof->storage('invitems')->get_field($itemid, 'invcategoryid');
                    // взяли из др категории
                    if ( $catid != $invgroup[$i] )
                    {
                        // тут соберём все эти неравенства
                        if ( isset($masequality[$catid]) )
                        {
                            $masequality[$catid] = $masequality[$catid] + 1;
                        }else 
                        {
                            $masequality[$catid] = 1;
                        }
                    }
                } 
                
                // получили массив, в котром собраны кол оборудов из др категорий
                // будем смотреть, чтоб они не пересеклись
                for ( $i=1; $i<=$data['count_item_in_invset']; $i++ )
                {
                    $item = 'item_'.$i;
                    $group = 'cat_'.$i;
                    if ( $data[$item] == 0 )
                    {
                        // стоит выбор любой - проверяем
                        if ( isset($masequality[$data[$group]]) AND $masequality[$data[$group]] >= 
                             count($this->dof->storage('invitems')->get_category_subordinated_items($data[$group], array('status'=>'active'),$this->depid, 'use')) )
                        {
                            $error[$item] = $this->dof->get_string('repeat_item','inventory',$data[$item]); 
                        }
                    }    
                    
                }   
         
            } 
                      
            // проверка на хватку оборудования
            foreach ( $invgroup as $key=>$catid )
            {
                $cats = $this->dof->storage('invcategories')->category_list_subordinated($catid,null,null,true,'',$this->depid);
                // дозапишем отца
                $cats[$catid] = $catid;
                // выберим ключи(именно в них содержаться id категории)
                $cats = array_keys($cats); 
                // вычислим число/количество вхождение
                $n = count(array_intersect($invgroup, $cats));
                $count = count($this->dof->storage('invitems')->get_category_subordinated_items($catid, array('status'=>'active'),$this->depid, 'use'));
                // число меньше - ошибка
                if ( $count < ($n*$data['quantity']) )
                {
                    $gr = 'cat_'.$key;
                    $error[$gr] = $this->dof->get_string('limit_invitems','inventory',$count); 
                }
                
            }
                

        }          
        // вывод ошибок
        return $error;
    }
    

    /**
     * Функци для обработки данных из формы создания/редактирования
     * 
     * @return string
     */
    public function process($addvars)
    {
        if ( $this->is_cancelled() )
		{//ввод данных отменен - возвращаем на страницу просмотра договоров
		    redirect($this->dof->url_im('inventory','/index.php',$addvars));
		}
		if ( $this->is_submitted() AND $formdata = $this->get_data() )
		{
		    
		    if ( empty($this->count) )
		    {// не передали число - редирект для его ловли
	            return $formdata;//redirect($this->dof->url_im('inventory','/invorders/set_invset.php?count='.$formdata->count_item_in_invset,$addvars));
		    }else 
		    {// есть число - всё хорошо, работаем дальше
		        // проверки все прошли
		    	// подключаем приказ
		    	require_once($this->dof->plugin_path('storage','invsets','/order/invsets_order.php'));
		    	
    		    // приказ формирования комплектов
    		    $order = new dof_storage_invitems_order_set_invsets($this->dof);
                // формируем объект для ордера    		    
    		    $orderobj = new stdClass();
    		    $orderobj->date = time();
    		    
                //сохраняем автора приказа
                if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
                {// неудача - скажем об этом
                    $error = $this->dof->get_string('no_found_person','inventory');
                    echo $this->dof->modlib('widgets')->error_message($error);
                    return '';
                }
                $orderobj->ownerid = $personid;
                // похразделение
    		    if ( isset($addvars['departmentid']) AND $addvars['departmentid'] )
                {// установим выбранное на странице id подразделения 
                    $orderobj->departmentid = $addvars['departmentid'];
                }else
                {// установим id подразделения из сведений о том кто формирует приказ
                    $orderobj->departmentid = $this->dof->storage('persons')->get_field($personid,'departmentid');
                }            		    
    		    $orderobj->data = new stdClass();
    		    
		    	// выбор категории
    		    if ( $formdata->category == '2' )
    		    {// выбрали из текущих категори
    		        $orderobj->data->categoryid  = $formdata->cat_select;
    		        $catid = $formdata->cat_select;
    		    }elseif( $formdata->category == '1' ) 
    		    {// создаем свою категорию 
    		        $catobj = new stdClass();
    		        $catobj->name = $formdata->cat_text;
    		        $catobj->code = '';
    		        $catobj->parentid = $formdata->parentid;
    		        $catobj->status = 'active';
    		        $catobj->departmentid = $addvars['departmentid'];
    		        $catid = $this->dof->storage('invcategories')->insert($catobj); 
                    // вставим код
    		        $obj = new stdClass();
                    $obj->code = 'id'.$catid;
                    $this->dof->storage('invcategories')->update($obj,$catid);
                    $orderobj->data->categoryid = $catid; 
                }  
    		    
    		    // число комплектов
    		    $orderobj->data->quantity = $formdata->quantity;
    		    // кол оборудован в комплекте
    		    $orderobj->data->count = $formdata->count_item_in_invset;
    		    // категории, из которых берутся оборудование
    		    for ( $i=1; $i<=$formdata->count_item_in_invset; $i++ )
    		    {
    		        $group = 'cat_'.$i;
    		        $orderobj->data->$group = $formdata->$group;
    		    }
    		    // id оборудования, которое передали
    		    if ( $formdata->quantity =='1')
    		    {
        		    for ( $i=1; $i<=$formdata->count_item_in_invset; $i++ )
        		    {
        		        $item = 'item_'.$i;
        		        $orderobj->data->$item = $formdata->$item;
        		    }
    		    }
    		    
    		    // сохраним приказ
    		    if ( ! $order->save($orderobj) )
    		    {// неудача - скажем об этом
                    $error = $this->dof->get_string('no_save_order','inventory');
                    echo $this->dof->modlib('widgets')->error_message($error);
                    return '';		       
    		    }
    		    // подписываем
    		    if ( ! $order->sign($personid) ) 
    		    {// неудача - скажем об этом
    		        $error = $this->dof->get_string('no_sign_order','inventory');
                    echo $this->dof->modlib('widgets')->error_message($error);
                    return '';			        
    		    }	    		    
		    	// исполняем
    			if ( ! $order->execute() )
    		    {// неудача - скажем об этом
    		        $error = $this->dof->get_string('no_execute_order','inventory');
                    echo $this->dof->modlib('widgets')->error_message($error);
                    return '';			        
    		    }
    		    
    		    if ( $formdata->quantity =='1' )
    		    {// сделаем редирект на просмотр комплека
    		        $data = $order->load($order->get_id())->data;
    		        $itemid = $data->comp1_cat1;
    		        $setid = $this->dof->storage('invitems')->get_field($itemid, 'invsetid');
    		        $addvars['invcategoryid'] = $catid;
    		        $returnlink = $this->dof->url_im('inventory','/sets/view.php?id='.$setid,$addvars);
                    redirect($returnlink);		
    		    }else 
    		    {
    		        $addvars['invcategoryid'] = $catid;
         		    $returnlink = $this->dof->url_im('inventory','/sets/list.php',$addvars );
                    redirect($returnlink);	   		        
    		    }    	        
		    }
        }
    }   
    
}


/*
 * Класс расформирования комплекта
 */
class dof_im_inventory_order_set_no_invset extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    protected $depid;
    protected $id;
    
    function definition()
    {// делаем глобальные переменные видимыми
        $this->dof = $this->_customdata->dof;
        $this->depid = $this->_customdata->depid;
        $this->id = $this->_customdata->id;
     
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // заголовок
        $mform->setAdvanced('header');
        $mform->addElement('header','header', $this->dof->get_string('dissolution_set','inventory'));
        // галочка на подтверждение
        $mform->addElement('checkbox', 'checkbox', '', $this->dof->modlib('ig')->igs('confirm'));
        
        // кнопки сохранить
        $mform->addElement('submit', 'go', $this->dof->get_string('dissolution','inventory')); 
        // скрытое поле setid
        $mform->addElement('hidden','setid',$this->id);
        $mform->setType('setid',PARAM_INT);
        if ( method_exists($mform, 'addAdvancedStatusElement') )
        {// в moodle 2.5 используется addAdvancedStatusElement
            $mform->addAdvancedStatusElement('no_invset');
        }else
        {// а в 2.4 все еще setShowAdvanced
            $mform->setShowAdvanced(false);
        }
    }
    
 	/* 
 	 * Задаем проверку выбора подтверждения
     */
    function validation($data,$files)
    {
		
        $error = array();
        // не выбрали подтверждение
		if ( empty($data['checkbox']) )
		{
            $error['checkbox'] = $this->dof->modlib('ig')->igs('confirm'); 		    
		}
		return $error;
          
    }
		
    /*
     * Обработчик формы
     * 
     * @param массив $addvars = параметры для перехода по ссылкам
     */
    function process($addvars=array())
    {
        if ( $this->is_submitted() AND $formdata = $this->get_data() )
		{
		    
		    // формируем приказ на расформирование 
		    // все проверки прошли
            // подключаем приказ
            require_once($this->dof->plugin_path('storage','invsets','/order/invsets_order.php'));

            $order = new dof_storage_invitems_order_set_no_invsets($this->dof);
		    // формируем объект для ордера    		    
		    $orderobj = new stdClass();
		    $orderobj->date = time();
		    
            //сохраняем автора приказа
            if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
            {// неудача - скажем об этом
                $error = $this->dof->get_string('no_found_person','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';
            }
            $orderobj->ownerid = $personid;
            // похразделение
		    if ( isset($addvars['departmentid']) AND $addvars['departmentid'] )
            {// установим выбранное на странице id подразделения 
                $orderobj->departmentid = $addvars['departmentid'];
            }else
            {// установим id подразделения из сведений о том кто формирует приказ
                $orderobj->departmentid = $this->dof->storage('persons')->get_field($personid,'departmentid');
            }            		    
		    $orderobj->data = new stdClass();
		    // кол оборудован в комплекте
		    $count = $this->dof->storage('invitems')->count_list(array('invsetid'=>$formdata->setid));
		    $orderobj->data->count = $count;
		    // id комплекта
		    $orderobj->data->setid = $formdata->setid;
		    // сохраним приказ
		    if ( ! $order->save($orderobj) )
		    {// неудача - скажем об этом
                $error = $this->dof->get_string('no_save_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';		       
		    }
		    // подписываем
		    if ( ! $order->sign($personid) ) 
		    {// неудача - скажем об этом
		        $error = $this->dof->get_string('no_sign_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';			        
		    }			    
		    
		    // исполняем
			if ( ! $order->execute() )
		    {// неудача - скажем об этом
		        $error = $this->dof->get_string('no_execute_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';			        
		    }

		    
		    // Тут все хорошо, скажем об этом
		    $returnlink = $this->dof->url_im('inventory','/sets/view.php',
                $addvars + array('id' => $formdata->setid, 'message' => 'dissolute_set' ));
                
            redirect($returnlink);			    
		    
		}
        
    }
    
}      






/*
 * Класс выдачи комплекта
 */
class dof_im_inventory_order_set_delivery extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    protected $depid;
    protected $id;
    
    function definition()
    {// делаем глобальные переменные видимыми
        $this->dof = $this->_customdata->dof;
        $this->depid = $this->_customdata->depid;
        $this->id = $this->_customdata->id;
     
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // заголовок
        $mform->setAdvanced('header_give');
        $mform->addElement('header','header_give', $this->dof->get_string('delivery_set','inventory'));
        // галочка на подтверждение
        $mform->addElement('checkbox', 'checkbox_give', '', $this->dof->modlib('ig')->igs('confirm'));
        // скрытое поле setid
        $mform->addElement('hidden','setid_give',$this->id);
        $mform->setType('setid_give',PARAM_INT);
        // элемент автозаполнения - составим данные 
        $s = array();       
        $s['plugintype'] =   "im";
        $s['plugincode'] =   "inventory";
        $s['querytype']  =   "person_give_set";
        $s['sesskey']    =   sesskey();
        $s['type']       =   'autocomplete';
        // id объекта, которому хотим выдать персону
        $s['objectid']       =  $this->id;
        $mform->addElement('dof_autocomplete', 'search', $this->dof->get_string('choose_person','inventory'),'size=29', $s);
        $mform->setType('search', PARAM_TEXT);
        //примечение
        $mform->addElement('textarea', 'textarea', $this->dof->get_string('notice','inventory'), array('style' => 'width:100%;max-width:400px;height:150px;'));
        // кнопка выдать
        $mform->addElement('submit', 'go_give', $this->dof->get_string('give','inventory'), 'size=19px');
        // установим скрытыми поля
        if ( method_exists($mform, 'addAdvancedStatusElement') )
        {// в moodle 2.5 используется addAdvancedStatusElement
            $mform->addAdvancedStatusElement('delivery');
        }else
        {// а в 2.4 все еще setShowAdvanced
            $mform->setShowAdvanced(false);
        }
    }
    
 	 
 	 /* Задаем проверку выбора подтверждения
     */
    function validation($data,$files)
    {
        $error = array();
        // не выбрали подтверждение
		if ( empty($data['checkbox_give']) )
		{
            $error['checkbox_give'] = $this->dof->modlib('ig')->igs('confirm'); 		    
		}
        // проверка , что введена персона    
		if ( empty($data['search']['id']) )
		{
            $error['search'] = $this->dof->get_string('choose_person','inventory'); 		    
		}		
		
		return $error;
          
    }
		
    /*
     * Обработчик формы
     * 
     * @param массив $addvars = параметры для перехода по ссылкам
     */
    function process($addvars=array())
    {
        if ( $this->is_submitted() AND $formdata = $this->get_data() )
		{
		    
		    // формируем приказ на выдачу 1 комплекта
		    // все проверки прошли		    
		    $setid = $formdata->setid_give;
		    $userid = $formdata->search['id'];
		    $notes = $formdata->textarea;
		    if ( $this->dof->storage('invsets')->set_delivery($setid, $userid, $notes) )
		    {// Тут все хорошо, скажем об этом
    		    $returnlink = $this->dof->url_im('inventory','/sets/view.php',
                    $addvars + array('id' => $formdata->setid_give, 'message' => 'give_set' ));
                redirect($returnlink);	
		    }else 
		    {// неудача - скажем об этом
		        $error = $this->dof->get_string('no_execute_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';
		    }
	/*
			// TODO удалить этот код, если вс1ё в порядке работает
            // подключаем приказ
            require_once($this->dof->plugin_path('storage','invsets','/order/invsets_order.php'));

            $order = new dof_storage_invitems_order_set_delivery($this->dof);
		    // формируем объект для ордера    		    
		    $orderobj = new stdClass();
		    $orderobj->date = time();
		    
            //сохраняем автора приказа
            if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
            {// неудача - скажем об этом
                $error = $this->dof->get_string('no_found_person','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';
            }
            $orderobj->ownerid = $personid;
            // похразделение
		    if ( isset($addvars['departmentid']) AND $addvars['departmentid'] )
            {// установим выбранное на странице id подразделения 
                $orderobj->departmentid = $addvars['departmentid'];
            }else
            {// установим id подразделения из сведений о том кто формирует приказ
                $orderobj->departmentid = $this->dof->storage('persons')->get_field($personid,'departmentid');
            }            		    
            $orderobj->notes = $formdata->textarea;
		    $orderobj->data = new stdClass();
		    // кол оборудован в комплекте
		    $orderobj->data->personid = $formdata->search['id_autocomplete'];
		    $orderobj->data->setid = $formdata->setid_give;
		    // сохраним приказ
		    if ( ! $order->save($orderobj) )
		    {// неудача - скажем об этом
                $error = $this->dof->get_string('no_save_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';		       
		    }
		    // подписываем
		    if ( ! $order->sign($personid) ) 
		    {// неудача - скажем об этом
		        $error = $this->dof->get_string('no_sign_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';			        
		    }			    
		    
		    // исполняем
			if ( ! $order->execute() )
		    {// неудача - скажем об этом
		        $error = $this->dof->get_string('no_execute_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';			        
		    }
			
		    // Тут все хорошо, скажем об этом
		    $returnlink = $this->dof->url_im('inventory','/sets/view.php',
                $addvars + array('id' => $formdata->setid_give, 'message' => 'give_set' ));
                
            redirect($returnlink);		
    */	    
		    
		}
        
    }
    
}

/** Выдача любого комплекта любому ученику
 * 
 */
class dof_im_inventory_order_set_any_delivery extends  dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    /**
     * @var int id категории, которая в текущий момент выбрана на странице
     */
    protected $categoryid;
    /**
     * @var int id подразделения которое выбрано в навигации
     */
    protected $departmentid;
    
    public function definition()
    {
        $this->dof = $this->_customdata->dof;
        $this->departmentid = $this->_customdata->departmentid;
        $this->categoryid   = $this->_customdata->categoryid;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // заголовок
        $mform->addElement('header','header', $this->dof->get_string('delivery_set','inventory'));
        
        // Персона
        // Укажем данные для AJAX-запроса 
        $options = array();       
        $options['plugintype'] = "im";
        $options['plugincode'] = "inventory";
        $options['querytype']  = "person_give_set";
        $options['sesskey']    = sesskey();
        $options['type']       = 'autocomplete';
        
        $mform->addElement('dof_autocomplete', 'search', 
            $this->dof->get_string('choose_person','inventory'), array('width' => '100%'), $options);
        $mform->setType('search', PARAM_TEXT);
        
        // Категория
        // в методе category_list_subordinated уже проходит проверка на право use
        $choices = $this->categories_list();
        $mform->addElement('select', 'categoryid', $this->dof->get_string('category','inventory'), $choices);
        $mform->setType('invcategoryid', PARAM_INT);
        
        // Комплект (Список доступных для выдачи комплектов подгружается через AJAX)
        // Параметры для построения AJAX-запроса
        $ajaxoptions = 
            array(
                'plugintype' => 'im',
                'plugincode' => 'inventory',
                'querytype'  => 'im_inventory_delivery',
                'sesskey'    => sesskey(),
                'parentid'   => 'id_categoryid',
                'customdata' => array('departmentid' => $this->departmentid)
            );
        $mform->addElement('dof_ajaxselect', 'setid', $this->dof->get_string('set','inventory'), null, $ajaxoptions);
        $mform->setType('setid', PARAM_INT);
        //примечение
        $mform->addElement('textarea', 'notes', $this->dof->get_string('notice','inventory'), 
            array('style' => 'width:100%;max-width:400px;height:150px;'));
        $mform->setType('notes', PARAM_TEXT);
        // кнопка "выдать"
        $mform->addElement('submit', 'go_give', $this->dof->get_string('give','inventory'));
    }
    
    /** Проверка данных после отправки
     * @param array
     * 
     * @return array
     */
    function validation($data,$files)
    {

        $errors = array();
        // проверка , что введена персона    
        if ( empty($data['search']['id_autocomplete']) )
        {
            $errors['search'] = $this->dof->get_string('choose_person','inventory');             
        }
        
        if ( ! $data['categoryid'] )
        {// не передана категория
            $errors['categoryid'] = $this->dof->get_string('category_is_not_defined','inventory');
        }
        
        if ( $data['setid'] == '-1' )
        {// в категории нет ни одного комплекта - не можем ничего выдать
            $errors['setid'] = $this->dof->get_string('no_sets_in_category','inventory');
            return $errors;
        }
        // получаем комплект (если он указан правильно)
        if ( $data['setid'] )
        {// передали комплект
            if ( ! $set = $this->dof->storage('invsets')->get($data['setid']) )
            {
                $errors['setid'] = $this->dof->get_string('set_not_found','inventory');
            }
            
            if ( $set->status != 'active' OR ! empty($set->personid) )
            {// комплект есть - но у него неподходящий статус
                $errors['setid'] = $this->dof->get_string('wrong_set_status','inventory');
            }
        }
        // а иначе выдать любой
        
        return $errors;
    }
    
    /** Получить список категорий для выбора оборудования
     * 
     * @return array - массив категорий для select-элемента $this->categoryid
     */
    protected function categories_list()
    {
        $category = array();
        if ( ! $this->categoryid )
        {
            $this->categoryid = null;
        }else 
        {
            $catobj = $this->dof->storage('invcategories')->get($this->categoryid); 
            $category[$this->categoryid] = $catobj->name.'['.$catobj->code.']';    
            // получаем список категорий в зависимости от текущей
            $categories = $this->dof->storage('invcategories')->
                category_list_subordinated($this->categoryid,null,null,true,' ',$this->departmentid);
            // нужно добавить текущ категорию + всех дочек сдвинуть на 2 пробела
            foreach ( $categories as $id=>$catid )
            {
               $categories[$id] =  '&nbsp;&nbsp;'.$catid;
            } 
            $category += $categories;    
        }
        
        return $category;
    }
}


/*
 * Класс возврата комплекта
 */
class dof_im_inventory_order_set_return extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    protected $depid;
    protected $id;
    
    function definition()
    {// делаем глобальные переменные видимыми
        $this->dof = $this->_customdata->dof;
        $this->depid = $this->_customdata->depid;
        $this->id = $this->_customdata->id;
     
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // заголовок
        $mform->setAdvanced('header');
        $mform->addElement('header','header', $this->dof->get_string('return_set_r','inventory'));
        // галочка на подтверждение
        $mform->addElement('checkbox', 'checkbox', '', $this->dof->modlib('ig')->igs('confirm'));
        // скрытое поле setid
        $mform->addElement('hidden','setid',$this->id);
        $mform->setType('setid',PARAM_INT);
        //примечение
        $mform->addElement('textarea', 'textarea', $this->dof->get_string('notice','inventory'), array('style' => 'width:100%;max-width:400px;height:150px;'));
        // кнопка выдать
        $mform->addElement('submit', 'go', $this->dof->get_string('return_set','inventory'), 'size=19px');
        // установим скрытыми поля     
        if ( method_exists($mform, 'addAdvancedStatusElement') )
        {// в moodle 2.5 используется addAdvancedStatusElement
            $mform->addAdvancedStatusElement('return');;
        }else
        {// а в 2.4 все еще setShowAdvanced
            $mform->setShowAdvanced(false);
        }  
        
    }
    
 	 
 	 /* Задаем проверку выбора подтверждения
     */
    function validation($data,$files)
    {
        $error = array();
        // не выбрали подтверждение
		if ( empty($data['checkbox']) )
		{
            $error['checkbox'] = $this->dof->modlib('ig')->igs('confirm'); 		    
		}

		return $error;
          
    }
		
    /*
     * Обработчик формы
     * 
     * @param массив $addvars = параметры для перехода по ссылкам
     */
    function process($addvars=array())
    {
        if ( $this->is_submitted() AND $formdata = $this->get_data() )
		{
		    // формируем приказ на выдачу 1 комплекта
		    // все проверки прошли
            // подключаем приказ
            require_once($this->dof->plugin_path('storage','invsets','/order/invsets_order.php'));

            $order = new dof_storage_invitems_order_set_return($this->dof);
		    // формируем объект для ордера    		    
		    $orderobj = new stdClass();
		    $orderobj->date = time();
		    
            //сохраняем автора приказа
            if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
            {// неудача - скажем об этом
                $error = $this->dof->get_string('no_found_person','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';
            }
            $orderobj->ownerid = $personid;
            // похразделение
		    if ( isset($addvars['departmentid']) AND $addvars['departmentid'] )
            {// установим выбранное на странице id подразделения 
                $orderobj->departmentid = $addvars['departmentid'];
            }else
            {// установим id подразделения из сведений о том кто формирует приказ
                $orderobj->departmentid = $this->dof->storage('persons')->get_field($personid,'departmentid');
            }            		    
            $orderobj->notes = $formdata->textarea;
		    $orderobj->data = new stdClass();
		    // id комплекта
		    $orderobj->data->setid = $formdata->setid;
		    // сохраним приказ
		    if ( ! $order->save($orderobj) )
		    {// неудача - скажем об этом
                $error = $this->dof->get_string('no_save_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';		       
		    }
		    // подписываем
		    if ( ! $order->sign($personid) ) 
		    {// неудача - скажем об этом
		        $error = $this->dof->get_string('no_sign_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';			        
		    }			    
		    
		    // исполняем
			if ( ! $order->execute() )
		    {// неудача - скажем об этом
		        $error = $this->dof->get_string('no_execute_order','inventory');
                echo $this->dof->modlib('widgets')->error_message($error);
                return '';			        
		    }

		    
		    // Тут все хорошо, скажем об этом
		    $returnlink = $this->dof->url_im('inventory','/sets/view.php',
                $addvars + array('id' => $formdata->setid, 'message' => 'returned_set' ));
                
            redirect($returnlink);			    
		    
		}
        
    }
    
}  

?>