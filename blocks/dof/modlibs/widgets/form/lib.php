<?php
global $CFG;
require_once($CFG->dirroot.'/lib/formslib.php');
/*
 * Класс для работы с формами Moodle
 */
class dof_modlib_widgets_form extends moodleform
{
    /**
     * Переменная для определения того, "заморожена" ли форма для редактирования
     * 
     * @var bool 
     */
    var $isformfreezed;
    
    /**
     * @var $errors - Массив ошибок
     */
    protected $errors;
    
    function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true)
    {
        $this->errors = [];
        $this->isformfreezed = false;
        parent::moodleform($action, $customdata, $method, $target, $attributes, $editable);
    }
    
    public function set_element_attributes($element = 'hidden', $paramtype = PARAM_INT, $elementname = '', $attr = array())
    {
        $mform = $this->_form;
        if ( $mform->elementExists($elementname) )
        {
            $mform->updateElementAttr($elementname, $attr);
        } else
        {
            $mform->addElement($element, $elementname);
            $mform->setType($elementname, $paramtype);
            $mform->updateElementAttr($elementname, $attr);
        }
        
    }

    protected function definition()
    {
        
    }
    
    public function validation($data, $files) 
    {
        return array();
    }
    
    /** Код im-плагина, откуда берутся нестандартные языковые строки
     * Эта функция должна быть переопределена
     * 
     * @return string
     */
    protected function im_code()
    {
        // @todo убрать обращение к этой функции, как только мы договоримся о том как
        // будет работать добавление dof в форму
        $this->_dof_object_fix();
        $this->dof->print_error('form_err_no_im_code', '', get_class($this), 'modlib', 'ig');
    }
    /** Код storage-плагина, указывает в каком хранилище находится объект у которого меняется статус
     * 
     * @return string
     */
    protected function storage_code()
    {
        // @todo убрать обращение к этой функции, как только мы договоримся о том как
        // будет работать добавление dof в форму
        $this->_dof_object_fix();
        // каждый workflow-плагин содержит в себе код storage - его и используем
        return $this->dof->workflow($this->workflow_code())->get_storage();
    }
    
    /** Код workflow-плагина, отвечающего за смену статуса объекта
     * Эта функция должна быть переопределена
     * 
     * @return string
     */
    protected function workflow_code()
    {
        // @todo убрать обращение к этой функции, как только мы договоримся о том как
        // будет работать добавление dof в форму
        $this->_dof_object_fix();
        $this->dof->print_error('form_err_no_workflow_code', '', get_class($this), 'modlib', 'ig');
    }
    
    /** получить список значений для элемента select
     *
     * @return bool|array - массив значений для элемента select или false в случае ошибки
     * @param array $records[optional] - массив объектов-записей из любой таблицы storage, или false, если
     *                                   нужно сформировать массив только из предустановленных элементов
     * @param bool|array $firtstelm[optional] - нужен ли нулевой элемент?
     *                                 true - если нужен (по умолчанию 0 =>"--- Выбрать ---")
     *                                 false - если не нужен
     *                                 array('свой_ключ'=>'свое_значение') - если нужен собственный нулевой элемент
     *                                 Можно указать несколько первых элементов, они будут добавлены в начало
     *                                 списка в том же порядке, в котором вы их укажете
     * @param string $key[optional] - поле БД, которое будет использоваться в качестве значений элементов $select
     * @param string|array $namefield[optional] - имя поля, либо массив со значениями полей таблицы БД,
     *                                            которые нужно будет использовать в качестве отображаемого списка. 
     *                                            Все элементы после первого разделяются пробелами и помещаются 
     *                                            в квадратные скобки
     * @todo проработать вывод ошибок
     * @todo разобраться с проверкой уникальности ключа
     */
    protected function dof_get_select_values($records=false, $firstelm=true,
            $key='id', $namefields='name')
    {
        // объявляем массив для итоговых результатов
        $result = $this->_dof_get_start_array($firstelm);
        // начинаем с учтановки значений по умолчанию
        if ( ! $namefields OR empty($namefields) )
        {// если поля не указаны - берем только имя
            $namefields = array('name');
        }elseif ( is_string($namefields) )
        {// если передан один параметр - приводим его к нужному виду
            $namefields = array($namefields);
        }elseif ( ! is_array($namefields) )
        {// неправильный тип данных
            return false;
        }
        
        if ( ! $key )
        {// если по каким-то причинам ключ не указан
            $key = 'id';
        }elseif ( ! is_string($key) )
        {// неправильный формат данных
            return false;
        }
        
        // составляем запрос для базы данных
        if ( ! $records OR ! is_array($records) OR empty($records) )
        {// не получено ни одной записи из базы - вернем изначальный массив
            return $result;
        }
        // составляем массив нужный для элемента $select
        foreach ( $records as $record )
        {// для каждой записи из объекта делаем массив
            $valuestring = '';
            // составим из полей объекта строчку меню
            foreach ( $namefields as $namefield )
            {
                if ( ! isset($record->$namefield) )
                {// в переданном массиве у объектов нет нужных полей
                    continue;
                }
                if ( ! $valuestring )
                {// первый параметр напишем как есть
                    $valuestring .= $record->$namefield;
                }else
                {// все остальные параметры заключим в квадратные скобки
                    $valuestring .= ' ['.$record->$namefield.'] ';
                }
            }
            if ( ! isset($record->$key)  )
            {// в переданном массиве у объектов нет нужных полей
                continue;
            }
            if ( ! $valuestring OR is_numeric($valuestring) )
            {// @todo исправление глюка с hierselect - в сучае если мы не получили ничего в качестве
                // значения из указанных полей - то вернем добавим туда хотя бы пробел  
                $valuestring .= ' ';
            }
            // получаем готовый элемент массива для html-select
            $result[$record->$key] = $valuestring;
        }
        /*if ( count($result) != (count($records) + count($firstelm)) )
        {// для составления массива элементов выбран неуникальный ключ - это ошибка
            return false; 
        }*/
        // возвращаем итоговый результат
        return $result;
    }
    
    /** Отфильтровать список объектов, убрав те, на которые пользователь не имеет права
     * 
     * @param array $values - массив значений, ключами которого являются id записей в каком-либо хранилище (storage)
     * @param array $permissions - массив прав, которые нужно проверить у каждого элемента
     *                             Формат массива сответствует формату функции has_right() в плагине acl
     *                             Пример:
     *                             array(
     *                                 array('plugintype'=>'storage', 
     *                                       'plugincode'=>'persons', 
     *                                       'code'=>'use', 
     *                                       'departmentid' => 2,
     *                                       'userid'=> 55),
     *                                 array('plugintype'=>'workflow', 
     *                                       'plugincode'=>'persons', 
     *                                       'code'=>'changestatus'),
     *                                 ...
     *                             )
     * @param string $mode[optional] - режим проверки
     *                                AND - в итоговый массив будут включены все записи, обладающие
     *                                      ВСЕМ списком прав, указанных в массиве $permissions
     *                                OR -  в итоговый массив будут включены все записи, обладающие
     *                                      ХОТЯ БЫ ОДНИМ правом из массива permissions
     */
    protected function dof_get_acl_filtered_list($values, $permissions, $mode='AND')
    {
        // @todo убрать обращение к этой функции, как только мы договоримся о том как
        // будет работать добавление dof в форму
        $this->_dof_object_fix();
        // для получения отфильтрованного списка обращаемся к плагину acl
        return $this->dof->storage('acl')->get_acl_filtered_list($values, $permissions, $mode);
    }
    
    /** Получить стандартный первый элемент для select-поля
     * 
     * @return array
     */
    protected function _dof_get_default_first_element()
    {
        return array( 0 => '--- '.$this->dof->modlib('ig')->igs('select').' ---' );
    }
    
    /** Создать стартовый массив для select-элемента
     * 
     * @return array
     * @param object $firstelm - значение для первого элемента, переданное в функцию dof_get_select_values()
     */
    protected function _dof_get_start_array($firstelm)
    {
        if ( $firstelm === false OR is_null($firstelm) )
        {// первый элемент не нужен
            return array();
        }elseif ( $firstelm === true )
        {// нужен стандартный первый элемент
            return $this->_dof_get_default_first_element();
        }elseif ( is_array($firstelm) )
        {// указан собственный первый элемент
            return $firstelm;
        }
        // в остальных случаях добавим страндартный первый элемент
        return $this->_dof_get_default_first_element();
    }
    
    /** Функция которая добавляет поле dof ко всем формам, которые этого пока не сделали
     * @todo временная функция, на тот период пока мы не можем договориться о том, как именно добавлять 
     * в форму объект dof_control.
     */
    private function _dof_object_fix()
    {
        global $DOF;
        if ( isset($this->dof) )
        {
            if ( $this->dof instanceof dof_control )
            {// поле уже существует, и в нем находится нужный объект - ничего не делаем
                return;
            }
        }
        
        // добавляем объект dof_control в форму
        $this->dof = $DOF;
    }
    
    /**
     * Заморозить указанныые элементы формы или всю форму целиком от редактирования
     * 
     * @param array $listname - список полей формы, которые необходимо "заморозить" для редактирования
     * @return void
     */
    public function freeze_form(array $listname = null)
    {
        $this->_dof_object_fix();
        $this->dof->modlib('nvg')->add_css('modlib', 'widgets', '/css/show_hide.css');
        $mform =& $this->_form;
        if ( $this->is_form_freezed() )
        {
            return;
        }
        if ( !empty($listname) )
        {
            $mform->hardFreezeAllVisibleExcept($listname);
        } else
        {
            $hiddenfreeze = $this->get_freezeid();
            $this->add_hidden_freezeid();
            $mform->hardFreezeAllVisibleExcept(array($hiddenfreeze));
        }
        $this->isformfreezed = true;
    }
    
    public function add_hidden_freezeid()
    {
        $mform =& $this->_form;
        $hiddenfreeze = $this->get_freezeid();
        $mform->addElement('hidden', $hiddenfreeze, 1);
        $mform->setType($hiddenfreeze, PARAM_INT);
    }
    
    /**
     * Получить идентификатор для добавления в hidden-поле формы
     * Определяет, была ли форма заморожена, чтобы не выполнять validation() и process()
     * 
     * @return string
     */
    protected function get_freezeid()
    {
        return __CLASS__ . "_freeze";
    }
    
    /**
     * Определить, была ли вызвана функция freeze_form()
     * 
     * @return bool
     */
    public function is_form_freezed()
    {
        $issubmittedfreezeid = false;
        $hiddenfreeze = $this->get_freezeid();
        $mform =& $this->_form;
        
        if ( $mform->elementExists($hiddenfreeze) AND $mform->exportValue($hiddenfreeze) )
        {
            $issubmittedfreezeid = true;
        }
        return ($this->isformfreezed OR $issubmittedfreezeid);
    }
    
    /**
     * Вывести стек ошибок
     */
    protected function errors()
    {
        if ( ! empty($this->errors) )
        {
            foreach ( $this->errors as $error )
            {// Отобразить каждую ошибку
                echo $this->dof->modlib('widgets')->error_message($error);
            }
            return false;
        }
        return true;
    }
    
    /**
     * Отобразить форму
     */
    public function display()
    {
        // Вывести ошибки
        $this->errors();
        // Отобразить форму
        parent::display();
    }
}

/** Базовый класс всех форм смены статуса 
 * 
 */
class dof_modlib_widgets_changestatus_form extends dof_modlib_widgets_form
{    
    /** Объявление класса формы
     * 
     */
    function definition()
    {
        $mform     =& $this->_form;
        if ( isset($this->_customdata->dof) )
        {
            $this->dof = $this->_customdata->dof;
        }else
        {
            $this->_dof_object_fix();
        }
        
        // устанавливаем id объекта
        $id     = 0;
        $status = '';
        if ( isset($this->_customdata->id) AND $this->_customdata->id)
        {
            $id = $this->_customdata->id;
            // получаем старый статус
            if ( $obj = $this->dof->storage($this->storage_code())->get($id) )
            {
                $status = $obj->status;
            }
        }
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden','change', optional_param('change', 0, PARAM_INT));
        $mform->setType('change', PARAM_BOOL);
        $mform->addElement('hidden', 'oldstatus', $status);
        $mform->setType('id', PARAM_TEXT);
        $mform->setTYpe('oldstatus', PARAM_TEXT);
        //$mform->addElement('hidden', 'groupsubmit');
        //$mform->setType('groupsubmit', PARAM_RAW );
                $button = array();
        // Создаем элементы формы
        $button[] =& $mform->createElement('hidden', 'save');
        $button[] =& $mform->createElement('hidden', 'cancel');
        // добавляем элементы в форму
        $grp =& $mform->addElement('group', 'groupsubmit', null, $button);
        $mform->setType('groupsubmit[save]', PARAM_RAW );
        $mform->setType('groupsubmit[cancel]', PARAM_RAW );
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->modlib('ig')->igs('change_status'));
        //$this->add_action_buttons(true, $this->dof->modlib('ig')->igs('change'));
    }
    
    /** Объявление внешнего вида после установки данных по умолчанию  
     * 
     */
    public function definition_after_data()
    {
        $mform =& $this->_form;
        // получаем id объекта с которым работаем
        $id = $mform->getElementValue('id');
        // добавляем элементы для работы со сменой статуса
        $this->dof_set_status_selector($id);
    }
    
    /** Проверки данных формы
     * 
     */
    public function validation($data, $files)
    {
        $errors = array();
        if ( ! isset($data['id']) OR ! $data['id'] )
        {// не найдена запись - не можем изменить ее данные
            $errors['status'] = $this->dof->modlib('ig')->igs('error');
        }
        // получаем список доступных статусов
        $available = $this->dof_acl_get_usable_statuses($data['id']);
        
        if ( ! is_array($available) )
        {// даже если нам вернули false - все равно приведем переменную к пустому массиву,
            // чтобы не было oшибок
            $available = (array)$available;
        }
        
        if ( ! isset($data['status']) OR ! array_key_exists($data['status'], $available) )
        {// статус не оказался в списке допустимых - это ошибка
            $errors['status'] = $this->dof->modlib('ig')->igs('invalid_status');
        }
        // возвращаем все возникшие ошибки, если они есть
        return $errors;
    }
    
    /** Обработать пришедшие из формы данные, сменить статус,
     * создать и выполнить приказ и вывести сообщение
     * @return bool 
     */
    public function process()
    {
        global $OUTPUT;
        $mform  =& $this->_form;
        $result = true;
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {// данные отправлены в форму, и не возникло ошибок
            //print_object($formdata);
            if ( $formdata->change == 0 )
            {
                $this->dof_set_confirm_message($formdata);
                die;
            } else if ( $formdata->change == 1 )
            {
                if ( empty($formdata->groupsubmit['cancel']) )
                {
                    $oldstatus = $this->dof->storage($this->storage_code())->get_field($formdata->id, 'status');
                    // запоминаем старый статус
                    $formdata->oldstatus = $oldstatus;
                    // создаем приказ (если нужно)
                    $result = ($result AND $this->dof_generate_status_order($formdata, $result));
                    
                    $currentstatus = $this->dof->storage($this->storage_code())->get_field($formdata->id, 'status');
                    if ( $currentstatus != $formdata->status )
                    {// меняем статус (если он не изменился ранее, при исполнении приказа)
                        $result = ($result AND $this->dof->workflow($this->workflow_code())->
                                    change($formdata->id, $formdata->status));
                    }
                    
                    // производим дополнительные действия в форме при смене статуса (если нужно)
                    $result = ($result AND $this->dof_custom_changestatus_checks($formdata, $result));
                    // выводим сообщение о том что статус изменен
                    $mform->addElement('static', 'message', '', $this->dof_get_statuschange_message($result));
                }
                // обновляем список статусов в форме
                $this->dof_set_status_selector($formdata->id);
                $mform->setConstant('change',0);
            }
        }
        return $result;
    }
    
    /** Генерирует приказ о смене статуса (если нужно)
     * @param object $formdata - данные пришедние из формы
     * @param bool $result - результат прошлой операции
     * 
     * @return bool
     */
    protected function dof_generate_status_order($formdata, $result=true)
    {
        if ( ! $result )
        {// смена статуса не удалась - не генерируем приказ
            return false;
        }
        // определим название класса приказа о смене статуса
        $classname = $this->dof_default_order_classname();
        if ( class_exists($classname) )
        {// если клас приказа существует - создадим его
            $order = new $classname($this->dof, $formdata);
            return $order->generate_order_status();
        }
        // класса приказа нет - значит ничего не нужно делать
        return true;
    }
    
    /** Название стандартного класса, который генерирует приказ о смене статуса
     * Если класс приказа называется как-то по особенному - ее можно переопределить
     */
    protected function dof_default_order_classname()
    {
        return 'dof_im_'.$this->workflow_code().'_order_status';
    }
    
    /** Дополнительные проверки и действия в форме смены статуса 
     * (переопределяется в дочерних классах, если необходимо)
     * @param object $formdata - данные пришедние из формы
     * @param bool $result - результат прошлой операции
     * 
     * @return bool
     */
    protected function dof_custom_changestatus_checks($formdata, $result=true)
    {
        return true;
    }
    
    /** В зависимости от списка возможных статусов - устанавливает в select-элемент возможные варианты
     * смены статуса, или отображает собщение о том, что статус изменить нельзя
     * 
     * @param int $id - id объекта, для которого изменяется статус
     */
    protected function dof_set_status_selector($id)
    {
        $mform  =& $this->_form;
        // получаем список возможных статусов, с учетом текущего статуса и прав пользователя
        $choices = $this->dof_acl_get_usable_statuses($id);
        // получаем запись из базы по переданному id
        $obj = $this->dof->storage($this->storage_code())->get($id);
        if ( ! $choices OR empty($choices) )
        {// это конечный статус -  покажем текстовый элемент
            if ( $mform->elementExists('status') )
            {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
                $mform->removeElement('status');
            }
            if ( $mform->elementExists('status_text') )
            {// убираем старую надпись, чтобы не выводить ее 2 раза
                $mform->removeElement('status_text');
            }
            if ( $mform->elementExists('save') )
            {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
                $mform->removeElement('save');
            }
            
            if ( ! $statusname = $this->dof->workflow($this->workflow_code())->get_name($obj->status) )
            {// статуса нет - то так и напишем
                $mform->addElement('static', 'status_text', '', 
                '<b style="color:gray;">'.$this->dof->modlib('ig')->igs('no_status').'</b>');
            }else
            {//статус есть, но доступных вариантов для перехода нет - скажем об этом
                $mform->addElement('static', 'status_text', '<b style="color:gray;">'.$statusname.':</b>', 
                '<b style="color:gray;">'.$this->dof->modlib('ig')->igs('this_is_final_status').'</b>');
            }
        }else
        {// из этого статуса возможны переходы - покажем выпадающее меню
                    
            if ( $mform->elementExists('status') )
            {// элемент уже раньше был - просто обновим выпадающее меню
                $select =& $mform->getElement('status');
                $select->removeOptions();
                $select->load($choices);
            }else
            {// элемента еще не было - добавим его
                // чекбокс подтверждения смены статуса
                $mform->addElement('select', 'status', $this->dof->modlib('ig')->igs('change_to').':', $choices);
                // кнопка смены статуса
                $mform->addElement('submit', 'save', $this->dof->modlib('ig')->igs('change_status'));
            }
        }
    }
    
    /** В зависимости от списка возможных статусов - устанавливает в select-элемент возможные варианты
     * смены статуса, или отображает собщение о том, что статус изменить нельзя
     * 
     * @param int $id - id объекта, для которого изменяется статус
     */
    protected function dof_set_confirm_message($formdata)
    {
        $mform  =& $this->_form;

        if ( $mform->elementExists('status') )
        {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
            $mform->removeElement('status');
        }
        if ( $mform->elementExists('save') )
        {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
            $mform->removeElement('save');
        }
        
        if ( $mform->elementExists('groupsubmit') )
        {// удаляем галочку подтверждения
            $mform->removeElement('groupsubmit');
        }
        $mform->setConstant('change',1);
        $mform->addElement('hidden', 'status', $formdata->status);
        $mform->addElement('hidden', 'save', $formdata->save);
        //$mform->addElement('hidden', 'change', 1);
        $this->dof->modlib('nvg')->print_header(NVG_MODE_PORTAL);
        // получаем запись из базы по переданному id
        $obj = $this->dof->storage($this->storage_code())->get($formdata->id);
        $a = new stdClass;
        $a->from = $this->dof->workflow($this->workflow_code())->get_name($obj->status);
        $a->to = $this->dof->workflow($this->workflow_code())->get_name($formdata->status);
        $mform->addElement('static', 'status_text', '', 
        '<b style="color:red;">'.$this->dof->modlib('ig')->igs('status_confirm',$a).'</b>');
        $button = array();
        // Создаем элементы формы
        $mform->addElement('html', '<div style="text-align: center">');
        $button[] =& $mform->createElement('submit', 'save', $this->dof->modlib('ig')->igs('change'));
        $button[] =& $mform->createElement('submit', 'cancel', $this->dof->modlib('ig')->igs('cancel'));
        // добавляем элементы в форму
        $grp =& $mform->addElement('group', 'groupsubmit', null, $button);
        $mform->addElement('html', '</div>');
        //$this->add_action_buttons(true, $this->dof->modlib('ig')->igs('change'));
        $this->display();
        $this->dof->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    }
    
    /** Получить текстовое сообщение о результате смены статуса, чтобы потом отобразить его в форме
     * 
     * @param bool $success - результат операции
     *                        true - статус удалось изменить
     *                        false - статус не удалось изменить
     */
    protected function dof_get_statuschange_message($success)
    {
        if ( $success )
        {// сообщение о том что все хорошо
            return '<div style=" color:green; "><b>'.$this->dof->modlib('ig')->igs('status_change_success').'</b></div>';
        }
        // сообщение об ошибке
        return '<div style=" color:red; "><b>'.$this->dof->modlib('ig')->igs('status_change_failure').'</b></div>';
    }
    
    /** Получить список статусов, разрешенных workflow и acl
     * @param int $id - id объекта, для которого получается список статусов
     * 
     * @return array 
     */
    protected function dof_acl_get_usable_statuses($id)
    {
        // Получаем список возможных статусов 
        $statuses = $this->dof->workflow($this->workflow_code())->get_available($id);
        if ( ! $statuses )
        {// нет доступных статусов
            return array();
        }
        $statusnames = array_keys($statuses);
        // получаем подразделение, в котором нужно проверять смену статуса
        $departmentid = $this->dof_get_acl_departmentid($id);
        // получаем id пользователя, который производит смену статуса
        $userid       = $this->dof_get_acl_userid();
        
        // оставляем только те статусы, которые пользователь имеет право изменить
        $available = $this->dof->storage('acl')->
            get_usable_statuses('workflow', $this->workflow_code(), $statusnames, $departmentid, $userid, $id);
        
        $result = array();
        foreach ( $available as $code )
        {// перебираем все разрешенные статусы оставляем в результате только их
            $result[$code] = $statuses[$code];
        }
        
        return $result;
    }
    
    /** Получить id подразделения, в котором нужно проверять право смены статуса
     * 
     * @return int
     */
    protected function dof_get_acl_departmentid($objectid)
    {
        $mform =& $this->_form;
        if ( $object = $this->dof->storage($this->storage_code())->get($objectid) )
        {// по умолчанию всегда берем подразделение из объекта
            if ( isset($object->departmentid) AND $object->departmentid )
            {
                return $object->departmentid;
            }
        }
        if ( $id = optional_param('departmentid', 0, PARAM_INT) )
        {
            return $id;
        }
        if ( $mform->elementExists('departmentid') )
        {
            if ( $id = $mform->getElementValue('departmentid') )
            {
                return $id;
            }
        }
        if ( isset($this->_customdata->departmentid) )
        {
            return $this->_customdata->departmentid;
        }
        
        return 0;
    }
    
    /** Получить id пользователя, который меняет статус
     * @todo не использовать здесь обращение к плагину persons, и попробовать извлечь id пользователя
     * другим способом. (каким - пока не ясно)
     */
    protected function dof_get_acl_userid()
    {
        return $this->dof->storage('persons')->get_by_moodleid_id();
    }
}

 
/** Базовый класс всех форм смены статуса через todo
 * 
 */
class dof_modlib_widgets_changestatus_todo_form extends dof_modlib_widgets_form
{    
    /** Объявление класса формы
     * 
     */
    function definition()
    {
        $mform     =& $this->_form;
        if ( isset($this->_customdata->dof) )
        {
            $this->dof = $this->_customdata->dof;
        }else
        {
            $this->_dof_object_fix();
        }
        
        // устанавливаем id объекта
        $id     = 0;
        $status = '';
        if ( isset($this->_customdata->id) AND $this->_customdata->id)
        {
            $id = $this->_customdata->id;
            // получаем старый статус
            if ( $obj = $this->dof->storage($this->storage_code())->get($id) )
            {
                $status = $obj->status;
            }
        }
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden','change', optional_param('change', 0, PARAM_INT));
        $mform->setType('change', PARAM_BOOL);
        $mform->addElement('hidden', 'oldstatus', $status);
        $mform->setType('id', PARAM_TEXT);
        //$mform->addElement('hidden', 'groupsubmit');
        //$mform->setType('groupsubmit', PARAM_RAW );
                $button = array();
        // Создаем элементы формы
        $button[] =& $mform->createElement('hidden', 'save');
        $button[] =& $mform->createElement('hidden', 'cancel');
        // добавляем элементы в форму
        $grp =& $mform->addElement('group', 'groupsubmit', null, $button);
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->modlib('ig')->igs('change_status'));
        //$this->add_action_buttons(true, $this->dof->modlib('ig')->igs('change'));
    }
    
    /** Объявление внешнего вида после установки данных по умолчанию  
     * 
     */
    public function definition_after_data()
    {
        $mform =& $this->_form;
        // получаем id объекта с которым работаем
        $id = $mform->getElementValue('id');
        // добавляем элементы для работы со сменой статуса
        $this->dof_set_status_selector($id);
    }
    
    /** Проверки данных формы
     * 
     */
    public function validation($data, $files)
    {
        $errors = array();
        if ( ! isset($data['id']) OR ! $data['id'] )
        {// не найдена запись - не можем изменить ее данные
            $errors['status'] = $this->dof->modlib('ig')->igs('error');
        }
        // получаем список доступных статусов
        $available = $this->dof_acl_get_usable_statuses($data['id']);
        
        if ( ! is_array($available) )
        {// даже если нам вернули false - все равно приведем переменную к пустому массиву,
            // чтобы не было oшибок
            $available = (array)$available;
        }
        
        if ( ! isset($data['status']) OR ! array_key_exists($data['status'], $available) )
        {// статус не оказался в списке допустимых - это ошибка
            $errors['status'] = $this->dof->modlib('ig')->igs('invalid_status');
        }
        // возвращаем все возникшие ошибки, если они есть
        return $errors;
    }
    
    /** Обработать пришедшие из формы данные, сменить статус,
     * создать и выполнить приказ и вывести сообщение
     * @return bool 
     */
    public function process()
    {
        global $OUTPUT;
        $mform  =& $this->_form;
        $reslut = true;
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {// данные отправлены в форму, и не возникло ошибок
            //print_object($formdata);
            if ( $formdata->change == 0 )
            {
                $this->dof_set_confirm_message($formdata);
                die;
            }elseif ( $formdata->change == 1 )
            {
                if ( empty($formdata->groupsubmit['cancel']) )
                {
                    $oldstatus = $this->dof->storage($this->storage_code())->get_field($formdata->id, 'status');
                    // запоминаем старый статус
                    $formdata->oldstatus = $oldstatus;
                    // производим дополнительные действия в форме при смене статуса (если нужно)
                    $reslut = $this->dof->add_todo('storage', $this->storage_code(), 
                              $this->storage_code().'_changestatus',
                              $formdata->id,$formdata,2,time());
                }
                // обновляем список статусов в форме
                $this->dof_set_status_selector($formdata->id);
                $mform->setConstant('change',0);
            }
        }
        return $reslut;
    }
    
    /** Генерирует приказ о смене статуса (если нужно)
     * @param object $formdata - данные пришедние из формы
     * @param bool $result - результат прошлой операции
     * 
     * @return bool
     */
    protected function dof_generate_status_order($formdata, $result=true)
    {
        if ( ! $result )
        {// смена статуса не удалась - не генерируем приказ
            return false;
        }
        // определим название класса приказа о смене статуса
        $classname = $this->dof_default_order_classname();
        if ( class_exists($classname) )
        {// если клас приказа существует - создадим его
            $order = new $classname($this->dof, $formdata);
            return $order->generate_order_status();
        }
        // класса приказа нет - значит ничего не нужно делать
        return true;
    }
    
    /** Название стандартного класса, который генерирует приказ о смене статуса
     * Если класс приказа называется как-то по особенному - ее можно переопределить
     */
    protected function dof_default_order_classname()
    {
        return 'dof_im_'.$this->workflow_code().'_order_status';
    }
    
    /** Дополнительные проверки и действия в форме смены статуса 
     * (переопределяется в дочерних классах, если необходимо)
     * @param object $formdata - данные пришедние из формы
     * @param bool $result - результат прошлой операции
     * 
     * @return bool
     */
    protected function dof_custom_changestatus_checks($formdata, $result=true)
    {
        return true;
    }
    
    /** В зависимости от списка возможных статусов - устанавливает в select-элемент возможные варианты
     * смены статуса, или отображает собщение о том, что статус изменить нельзя
     * 
     * @param int $id - id объекта, для которого изменяется статус
     */
    protected function dof_set_status_selector($id)
    {
        global $DB;
        $mform  =& $this->_form;
        // получаем список возможных статусов, с учетом текущего статуса и прав пользователя
        $choices = $this->dof_acl_get_usable_statuses($id);
        // получаем запись из базы по переданному id
        $obj = $this->dof->storage($this->storage_code())->get($id);
        if ( $DB->get_records_select('block_dof_todo'," exdate=0  AND plugintype='storage' AND 
                 plugincode='".$this->storage_code()."' AND todocode='".
                 $this->storage_code()."_changestatus' AND intvar=".$id) )
        {
            if ( $mform->elementExists('status') )
            {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
                $mform->removeElement('status');
            }
            if ( $mform->elementExists('status_text') )
            {// убираем старую надпись, чтобы не выводить ее 2 раза
                $mform->removeElement('status_text');
            }
            if ( $mform->elementExists('save') )
            {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
                $mform->removeElement('save');
            }
            $mform->addElement('static', 'status_text', '', 
                '<b style="color:green;">'.$this->dof->modlib('ig')->igs('status_change_todo').'</b>');
        }elseif ( ! $choices OR empty($choices) )
        {// это конечный статус -  покажем текстовый элемент
            if ( $mform->elementExists('status') )
            {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
                $mform->removeElement('status');
            }
            if ( $mform->elementExists('status_text') )
            {// убираем старую надпись, чтобы не выводить ее 2 раза
                $mform->removeElement('status_text');
            }
            if ( $mform->elementExists('save') )
            {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
                $mform->removeElement('save');
            }
            
            if ( ! $statusname = $this->dof->workflow($this->workflow_code())->get_name($obj->status) )
            {// статуса нет - то так и напишем
                $mform->addElement('static', 'status_text', '', 
                '<b style="color:gray;">'.$this->dof->modlib('ig')->igs('no_status').'</b>');
            }else
            {//статус есть, но доступных вариантов для перехода нет - скажем об этом
                $mform->addElement('static', 'status_text', '<b style="color:gray;">'.$statusname.':</b>', 
                '<b style="color:gray;">'.$this->dof->modlib('ig')->igs('this_is_final_status').'</b>');
            }
        }else
        {// из этого статуса возможны переходы - покажем выпадающее меню
                    
            if ( $mform->elementExists('status') )
            {// элемент уже раньше был - просто обновим выпадающее меню
                $select =& $mform->getElement('status');
                $select->removeOptions();
                $select->load($choices);
            }else
            {// элемента еще не было - добавим его
                $mform->addElement('select', 'status', $this->dof->modlib('ig')->igs('change_to').':', $choices);
                // кнопка смены статуса
                $mform->addElement('submit', 'save', $this->dof->modlib('ig')->igs('change_status'));
            }
        }
    }
    
    /** В зависимости от списка возможных статусов - устанавливает в select-элемент возможные варианты
     * смены статуса, или отображает собщение о том, что статус изменить нельзя
     * 
     * @param int $id - id объекта, для которого изменяется статус
     */
    protected function dof_set_confirm_message($formdata)
    {
        $mform  =& $this->_form;

        if ( $mform->elementExists('status') )
        {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
            $mform->removeElement('status');
        }
        if ( $mform->elementExists('save') )
        {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
            $mform->removeElement('save');
        }
        
        if ( $mform->elementExists('groupsubmit') )
        {// удаляем галочку подтверждения
            $mform->removeElement('groupsubmit');
        }
        $mform->setConstant('change',1);
        $mform->addElement('hidden', 'status', $formdata->status);
        $mform->addElement('hidden', 'save', $formdata->save);
        //$mform->addElement('hidden', 'change', 1);
        $this->dof->modlib('nvg')->print_header(NVG_MODE_PORTAL);
        // получаем запись из базы по переданному id
        $obj = $this->dof->storage($this->storage_code())->get($formdata->id);
        $a->from = $this->dof->workflow($this->workflow_code())->get_name($obj->status);
        $a->to = $this->dof->workflow($this->workflow_code())->get_name($formdata->status);
        $mform->addElement('static', 'status_text', '', 
        '<b style="color:red;">'.$this->dof->modlib('ig')->igs('status_confirm',$a).'</b>');
        $button = array();
        // Создаем элементы формы
        $button[] =& $mform->createElement('submit', 'save', $this->dof->modlib('ig')->igs('change'));
        $button[] =& $mform->createElement('submit', 'cancel', $this->dof->modlib('ig')->igs('cancel'));
        // добавляем элементы в форму
        $grp =& $mform->addElement('group', 'groupsubmit', null, $button);
        //$this->add_action_buttons(true, $this->dof->modlib('ig')->igs('change'));
        $this->display();
        echo $this->dof->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    }
    
    /** Получить текстовое сообщение о результате смены статуса, чтобы потом отобразить его в форме
     * 
     * @param bool $success - результат операции
     *                        true - статус удалось изменить
     *                        false - статус не удалось изменить
     */
    protected function dof_get_statuschange_message($success)
    {
        if ( $success )
        {// сообщение о том что все хорошо
            return '<div style=" color:green; "><b>'.$this->dof->modlib('ig')->igs('status_change_success').'</b></div>';
        }
        // сообщение об ошибке
        return '<div style=" color:red; "><b>'.$this->dof->modlib('ig')->igs('status_change_failure').'</b></div>';
    }
    
    /** Получить список статусов, разрешенных workflow и acl
     * @param int $id - id объекта, для которого получается список статусов
     * 
     * @return array 
     */
    protected function dof_acl_get_usable_statuses($id)
    {
        // Получаем список возможных статусов 
        $statuses = $this->dof->workflow($this->workflow_code())->get_available($id);
        if ( ! $statuses )
        {// нет доступных статусов
            return array();
        }
        $statusnames = array_keys($statuses);
        // получаем подразделение, в котором нужно проверять смену статуса
        $departmentid = $this->dof_get_acl_departmentid($id);
        // получаем id пользователя, который производит смену статуса
        $userid       = $this->dof_get_acl_userid();
        
        // оставляем только те статусы, которые пользователь имеет право изменить
        $available = $this->dof->storage('acl')->
            get_usable_statuses('workflow', $this->workflow_code(), $statusnames, $departmentid, $userid, $id);
        
        $result = array();
        foreach ( $available as $code )
        {// перебираем все разрешенные статусы оставляем в результате только их
            $result[$code] = $statuses[$code];
        }
        
        return $result;
    }
    
    /** Получить id подразделения, в котором нужно проверять право смены статуса
     * 
     * @return int
     */
    protected function dof_get_acl_departmentid($objectid)
    {
        $mform =& $this->_form;
        if ( $object = $this->dof->storage($this->storage_code())->get($objectid) )
        {// по умолчанию всегда берем подразделение из объекта
            if ( isset($object->departmentid) AND $object->departmentid )
            {
                return $object->departmentid;
            }
        }
        if ( $id = optional_param('departmentid', 0, PARAM_INT) )
        {
            return $id;
        }
        if ( $mform->elementExists('departmentid') )
        {
            if ( $id = $mform->getElementValue('departmentid') )
            {
                return $id;
            }
        }
        if ( isset($this->_customdata->departmentid) )
        {
            return $this->_customdata->departmentid;
        }
        
        return 0;
    }
    
    /** Получить id пользователя, который меняет статус
     * @todo не использовать здесь обращение к плагину persons, и попробовать извлечь id пользователя
     * другим способом. (каким - пока не ясно)
     */
    protected function dof_get_acl_userid()
    {
        return $this->dof->storage('persons')->get_by_moodleid_id();
    }
}

////////////////////////////////////////////////////////
// собственные элементы формы, определенные плагином  //
// FDO Здесь содержится только регистрация элементов. // 
// Один элемент - одна строка.                        //
// Название каждого нового элемента должно начинаться //
// с префикса "dof_"                                  //
////////////////////////////////////////////////////////
MoodleQuickForm::registerElementType('dof_telephone', "$CFG->dirroot/blocks/dof/modlibs/widgets/form/elements/dof_telephone/dof_telephone.php", 'MoodleQuickForm_dof_telephone');
MoodleQuickForm::registerElementType('dof_duration', "$CFG->dirroot/blocks/dof/modlibs/widgets/form/elements/dof_duration/dof_duration.php", 'MoodleQuickForm_dof_duration');
MoodleQuickForm::registerElementType('dof_single_use_submit', "$CFG->dirroot/blocks/dof/modlibs/widgets/form/elements/dof_single_use_submit/dof_single_use_submit.php", 'MoodleQuickForm_dof_single_use_submit');
MoodleQuickForm::registerElementType('dof_autocomplete', "$CFG->dirroot/blocks/dof/modlibs/widgets/form/elements/dof_autocomplete/dof_autocomplete.php", 'MoodleQuickForm_dof_autocomplete');
MoodleQuickForm::registerElementType('dof_ajaxselect', "$CFG->dirroot/blocks/dof/modlibs/widgets/form/elements/dof_ajaxselect/dof_ajaxselect.php", 'MoodleQuickForm_dof_ajaxselect');
MoodleQuickForm::registerElementType('dof_calendar', "$CFG->dirroot/blocks/dof/modlibs/widgets/form/elements/dof_calendar/dof_calendar.php", 'MoodleQuickForm_dof_calendar');
MoodleQuickForm::registerElementType('dof_date_selector', "$CFG->dirroot/blocks/dof/modlibs/widgets/form/elements/dof_date_selector/dof_date_selector.php", 'MoodleQuickForm_dof_date_selector');
MoodleQuickForm::registerElementType('dof_confirm_submit', "$CFG->dirroot/blocks/dof/modlibs/widgets/form/elements/dof_confirm_submit/dof_confirm_submit.php", 'MoodleQuickForm_dof_confirm_submit');
?>