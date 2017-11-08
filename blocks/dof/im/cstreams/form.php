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

/** Редактирование одного учебного потока
 * 
 */
class dof_im_cstreams_edit_form extends dof_modlib_widgets_form
{
    private $cstream;
    //public $dof;
    
    function definition()
    {// делаем глобальные переменные видимыми

        $this->cstream = $this->_customdata->cstream;
        //print_object($this->cstream);
        $this->dof     = $this->_customdata->dof;

        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $mform->addElement('hidden','cstreamid', $this->cstream->id);
        $mform->setType('cstreamid', PARAM_INT);
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        
        // создаем заголовок формы
        $mform->addElement('header','formtitle', $this->get_form_title($this->cstream->id));
        if ( $this->cstream->id  )
        {
            $mform->addElement('static', 'name', $this->dof->get_string('name','cstreams').':', 
                                        $this->cstream->name);
        }
        // период
        $mform->addElement('select', 'ageid', $this->dof->get_string('age','cstreams').':', $this->get_list_ages());
        $mform->setType('ageid', PARAM_INT);
        $mform->addRule('ageid',$this->dof->get_string('err_required', 'cstreams'), 'required',null,'client');
        $options = '';
        if ( $this->cstream->id AND ! $this->dof->is_access('datamanage') )
        {
            $options = 'disabled';
        }
        $mform->addElement('select', 'programmid', $this->dof->get_string('programm','cstreams').':', $this->get_list_programms(),$options);
        
        $ajaxoptions = array('plugintype' => 'im','plugincode' => 'cstreams');
        $ajaxoptions['querytype'] = 'list_programmitems';
        $ajaxoptions['parentid'] = 'id_programmid';
        $ajaxoptions['customdata'] = array('cstreamid'=>$this->cstream->id);
        $mform->addElement('dof_ajaxselect', 'programmitemid', $this->dof->get_string('programmitem','cstreams').':', $options, $ajaxoptions);
        if ( $this->cstream->id )
        {
            $itemid = $this->dof->storage('cstreams')->get_field($this->cstream->id,'programmitemid');
            $ajaxoptions['customdata']['programmitemid'] = $itemid;
        }
        
        
        $ajaxoptions['querytype'] = 'list_teachers';
        $ajaxoptions['parentid'] = 'id_programmitemid';
        
        $mform->addElement('dof_ajaxselect', 'appointmentid', $this->dof->get_string('teacher','cstreams').':', null, $ajaxoptions);
        
        // получим все возможные подразделения для поля "select"
        $departments = $this->dof->storage('departments')->departments_list_subordinated(null,'0',null,true);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $departments = $this->dof_get_acl_filtered_list($departments, $permissions);
        
        // поле "подразделение"
        if ( ! $this->cstream->id )
        {// создаем поток, ставим галочку что подразделение наследуемем из предмета
            $mform->addElement('checkbox', 'depcheck', null, 
                  $this->dof->get_string('take_department_from_pitem','cstreams'));
            $mform->setType('depcheck', PARAM_BOOL);
            $mform->setDefault('depcheck', true);
        }

        $mform->addElement('select', 'depid', $this->dof->get_string('department','cstreams').':', $departments);
        $mform->setType('depid', PARAM_INT);
        if ( isset($this->cstream->departmentid) )
        {
            $mform->setDefault('depid', $this->cstream->departmentid);
        }
        if ( ! $this->cstream->id )
        {// создаем поток - делаем зависимым поле подразделения и галочку
            $mform->disabledIf('depid', 'depcheck', 'checked'); 
        }else
        {// редактируем поток - поле подразделения обязательно
            $mform->addRule('depid',$this->dof->get_string('err_required', 'cstreams'), 'required',null,'client');
        }
        // id группы в Moddle
        $mform->addElement('static', 'mdlgroup', $this->dof->get_string('mdlgroup','cstreams').':', $this->dof->get_string('in_development','cstreams'));
        
        // галочка для того чтобы указать, что количество недель наследуется из периода
        // @todo поставить галочку за текстовым элементом
        /*$mform->addElement('checkbox', 'ageeduweeks', null, 
                $this->dof->get_string('take_eduweeks_from_period', 'cstreams'));
        // делаем галочку по умолчанию поставленной
        $mform->setDefault('ageeduweeks', true);
        $mform->setType('agedates', PARAM_BOOL);
        // количество недель
        $mform->addElement('text', 'eduweeks', $this->dof->get_string('eduweeks','cstreams').':', 'size="2"');
        $mform->setType('eduweeks', PARAM_INT);
        $mform->setDefault('eduweeks', $this->get_eduweeks());
        $mform->disabledIf('eduweeks', 'ageeduweeks', 'checked');*/
        
        // количество недель
        $ageeduweeks = array();
        $ageeduweeks[] = $mform->createElement('text', 'eduweeks', null, 'size="2"');
        $ageeduweeks[] = $mform->createElement('checkbox', 'checkeduweeks', null, 
                $this->dof->get_string('take_eduweeks_from_period', 'cstreams'));
        $mform->setType('ageeduweeks[checkeduweeks]', PARAM_BOOL);
        $mform->addElement('group','ageeduweeks',$this->dof->get_string('eduweeks','cstreams'),$ageeduweeks);
        $mform->setType('ageeduweeks[eduweeks]', PARAM_INT);
        $mform->disabledIf('ageeduweeks[eduweeks]', 'ageeduweeks[checkeduweeks]', 'checked');
        
        // количество часов всего
        $pitemhours = array();
        $pitemhours[] = $mform->createElement('text', 'hours', null, 'size="2"');
        $pitemhours[] = $mform->createElement('checkbox', 'checkhours', null, 
                $this->dof->get_string('take_hours_from_pitem', 'cstreams'));
        $mform->setType('pitemhours[checkhours]', PARAM_BOOL);
        $mform->addElement('group','pitemhours',$this->dof->get_string('hours','cstreams'),$pitemhours);
        $mform->setType('pitemhours[hours]', PARAM_INT);
        $mform->disabledIf('pitemhours[hours]', 'pitemhours[checkhours]', 'checked');
        
        // количество часов в недедю
        $pitemhoursweek = array();
        $pitemhoursweek[] = $mform->createElement('text', 'hoursweek', null, 'size="2"');
        $pitemhoursweek[] = $mform->createElement('checkbox', 'checkhoursweek', null, 
                $this->dof->get_string('take_hoursweek_from_pitem', 'cstreams'));
        $mform->setType('pitemhoursweek[checkhoursweek]', PARAM_BOOL);
        $mform->addElement('group','pitemhoursweek',$this->dof->get_string('hoursweek','cstreams'),$pitemhoursweek);
        $mform->setType('pitemhoursweek[hoursweek]', PARAM_INT);
        $mform->disabledIf('pitemhoursweek[hoursweek]', 'pitemhoursweek[checkhoursweek]', 'checked');
        // часов в неделю очно
        $mform->addElement('text', 'hoursweekinternally', $this->dof->get_string('hoursweekinternally','cstreams'), 'size="3"');
        $mform->setType('hoursweekinternally', PARAM_NUMBER );
        $mform->addRule('hoursweekinternally',$this->dof->modlib('ig')->igs('form_err_numeric'), 'numeric',null,'client');
        $mform->addRule('hoursweekinternally',$this->dof->modlib('ig')->igs('form_err_numeric'), 'numeric',null,'server');
        // часов в неделю дистанционно
        $mform->addElement('text', 'hoursweekdistance', $this->dof->get_string('hoursweekdistance','cstreams'), 'size="3"');
        $mform->setType('hoursweekdistance', PARAM_NUMBER );
        $mform->addRule('hoursweekdistance',$this->dof->modlib('ig')->igs('form_err_numeric'), 'numeric',null,'client');
        $mform->addRule('hoursweekdistance',$this->dof->modlib('ig')->igs('form_err_numeric'), 'numeric',null,'server');        
        // поправочный зарплатный коэффициент
        $mform->addElement('radio', 'factor', null, $this->dof->get_string('salfactor','cstreams'),'sal');
        $mform->addElement('text', 'salfactor', '', 'size="10"');
        $mform->setType('salfactor', PARAM_TEXT);
        $mform->setDefault('salfactor', '0.00');
        // замещающий зарплатный коэффициент
        $mform->addElement('radio', 'factor', null, $this->dof->get_string('substsalfactor','cstreams'),'substsal');
        $mform->addElement('text', 'substsalfactor', '', 'size="10"');
        $mform->setType('substsalfactor', PARAM_TEXT);
        $mform->setDefault('substsalfactor', '0.00');
        // перекрываем противоположное поле
        $mform->setDefault('factor', 'sal');
        $mform->disabledIf('salfactor', 'factor', 'eq', 'substsal');
        $mform->disabledIf('substsalfactor', 'factor', 'eq', 'sal');
        // дата начала обучения
        $mform->addElement('checkbox', 'agedates', null, $this->dof->get_string('begindate_match_period','cstreams'));
        $mform->setType('agedates', PARAM_BOOL);

        $dateoptions = array();// объявляем массив для установки значений по умолчанию
        $dateoptions['startyear'] = dof_userdate(time(),'%Y')-12; // устанавливаем год, с которого начинать вывод списка
        $dateoptions['stopyear']  = dof_userdate(time(),'%Y')+12; // устанавливаем год, которым заканчивается список
        $dateoptions['optional']  = false; // убираем галочку, делающую возможным отключение этого поля
        // добавляем сам элемент
        $mform->addElement('date_selector', 'begindate', $this->dof->get_string('begindate', 'cstreams').':', $dateoptions);
        $mform->addElement('radio', 'chooseend', null, $this->dof->get_string('enddate_match_period','cstreams'),'age');
        $mform->addElement('radio', 'chooseend', null, $this->dof->get_string('enddate_match_pitem','cstreams'),'pitem');
        $mform->addElement('radio', 'chooseend', null, $this->dof->get_string('enddate_set_manually','cstreams'),'manually');
        // добавляем сам элемент
        $mform->addElement('date_selector', 'enddate', $this->dof->get_string('enddate', 'cstreams').':', $dateoptions);
        
        if ( $this->cstream->id AND $this->cstream->begindate )
        {// 
            $mform->setDefault('begindate', $this->cstream->begindate);
        }else
        {// создается новая подписка - по умолчанию установим текущее время
            $mform->setDefault('begindate', time());
        }
        if ( $this->cstream->id AND $this->cstream->enddate )
        {// 
            $mform->setDefault('enddate', $this->cstream->enddate);
        }else
        {// создается новая подписка - по умолчанию установим текущее время
            $mform->setDefault('enddate', time());
        }
        if ($this->cstream->id == 0 )
        {// если поток создается, делаем галочки поставленными
            $mform->setDefault('ageeduweeks[checkeduweeks]', true);
            $mform->setDefault('pitemhours[checkhours]', true);
            $mform->setDefault('pitemhoursweek[checkhoursweek]', true);
            $mform->setDefault('agedates', true);
            $mform->setDefault('chooseend', 'age');
            
        }else
        {//редактируется - поставим значения по умолчанию
            $mform->setDefault('ageeduweeks[eduweeks]', $this->cstream->eduweeks);
            $mform->setDefault('pitemhours[hours]', $this->cstream->hours);
            $mform->setDefault('pitemhoursweek[hoursweek]', $this->cstream->hoursweek); 
            $mform->setDefault('chooseend', 'manually');
        }
        
        
        // дата начала и дата окончания обучения не используется, если указано, что она совпадает с периодом
        $mform->disabledIf('begindate', 'agedates', 'checked'); 
        $mform->disabledIf('enddate', 'chooseend','noteq','manually');
        // кнопоки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->get_string('to_save','cstreams'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Проверка данных на стороне сервера
     * 
     * @return 
     * @param object $data [optional] - массив с данными из формы
     * @param object $files [optional] - массив отправленнных в форму файлов (если они есть)
     */
    public function validation($data,$files)
    {
        $errors = array();
        // проверим существование периода
        if ( ! isset($data['ageid']) OR ! $this->dof->storage('ages')->is_exists($data['ageid']) )
        {// учебное подразделение не существует
            $errors['ageid'] = $this->dof->get_string('err_required','cstreams');
        }
        if ( ! ($this->cstream->id AND $this->cstream->status != 'plan' AND ! $this->dof->is_access('datamanage')) )
        {
            // проверим существование предмета
            if ( isset($data['programmitemid']) AND ! $this->dof->storage('programmitems')->is_exists($data['programmitemid']) )
            {// учебное подразделение не существует
                $errors['programmitemid'] = $this->dof->get_string('err_required','cstreams');
            }
        }
        $depid = null;
        if ( ! isset($data['depcheck']) )
        {// если подразделение указано
            // проверим существование подразделения
            if ( ! isset($data['depid']) OR ! $this->dof->storage('departments')->is_exists($data['depid']) )
            {// учебное подразделение не существует
                $errors['depid'] = $this->dof->get_string('err_required','cstreams');
            }
            $depid = $data['depid'];
        } else
        {// Достанем departmentid из дисциплины
            if ( ! $depid = $this->dof->storage('programmitems')->get_field($data['programmitemid'], 'departmentid') )
            {
                $errors['depid'] = $this->dof->get_string('department_not_found', 'cstreams');
            }
        }
        // Проверим, можно ли создавать в указанном подразделении
        $cstreamid = null;
        if ( $this->cstream->id != 0)
        {
            $cstreamid = $this->cstream->id;
        }
        if ( ! $person = $this->dof->storage('persons')->get_bu() )
        {
            $errors['ageid'] = $this->dof->get_string('err_noperson', 'cstreams');
        }
        // Если нет прав редактировать процессы в указанном подразделении
        if ( ! $this->dof->storage('cstreams')->is_access('edit', null, null, $depid) )
        {
            $errors['depid'] = $this->dof->get_string('err_noaccess_depid', 'cstreams');
        }
        
        if ( ! isset($errors['ageid']) )
        {// если запись существует - то проверим, не выходит ли указанная дата начала потоков
            // за границу учебного периода
            if ( ! isset($data['agedates']) )
            {// только если не сказано взять ее из периода
                $age = $this->dof->storage('ages')->get($data['ageid']);
                if ( $data['begindate'] < $age->begindate OR $data['begindate'] > $age->enddate )
                {// дата начала обучения не совпадает с периодом - сообщим об этом, и подскажем
                    // правильные даты
                    $daterange = dof_userdate($age->begindate,'%Y-%m-%d').' - '.
                                 dof_userdate($age->enddate,'%Y-%m-%d');
                    $errors['begindate'] = $this->dof->get_string('err_begindate', 'cstreams', $daterange);
                }
            }
            if ( isset($data['chooseend']) AND $data['chooseend'] == 'manually' )
            {// только если не сказано взять ее из периода
                $age = $this->dof->storage('ages')->get($data['ageid']);
                if ( $data['enddate'] < $age->begindate OR $data['enddate'] > $age->enddate )
                {// дата начала обучения не совпадает с периодом - сообщим об этом, и подскажем
                    // правильные даты
                    $daterange = dof_userdate($age->begindate,'%Y-%m-%d').' - '.
                                 dof_userdate($age->enddate,'%Y-%m-%d');
                    $errors['enddate'] = $this->dof->get_string('err_enddate', 'cstreams', $daterange);
                }
            }
        }
        if ( isset($data['hoursweekinternally']) AND $data['hoursweekinternally'] )
        {// количество очных часов в неделю - только положительное
            $num = $data['hoursweekinternally']; 
            if ( $num < 0 )
            {
                $errors['hoursweekinternally'] = $this->dof->modlib('ig')->igs('form_err_only_positive');
            }else 
            {// остаток может быть или 0.25 или 0,50
                $ostatok = $num - floor($num);
                if ( $ostatok AND ! ($ostatok == 0.5 OR $ostatok == 0.25) )
                {
                    $errors['hoursweekinternally'] = $this->dof->get_string('hourse_part', 'cstreams');
                }
            }    
        }
        if ( isset($data['hoursweekdistance']) AND $data['hoursweekdistance'] )
        {// количество дистанционных очных часов в неделю - только положительное
            $num = $data['hoursweekdistance'];
            if ( $num < 0 )
            {
                $errors['hoursweekdistance'] = $this->dof->modlib('ig')->igs('form_err_only_positive');
            }else 
            {// остаток может быть или 0.25 или 0,50
                $ostatok = $num - floor($num);
                if ( $ostatok AND ! ($ostatok == 0.5 OR $ostatok == 0.25) )
                {
                    $errors['hoursweekdistance'] = $this->dof->get_string('hourse_part', 'cstreams');
                }
            } 
        }
        // проверка на лимит объектов
        if ( ! $this->cstream->id )
        {
            if (    isset( $data['depid'] ) AND ! $this->dof->storage('config')->get_limitobject('cstreams',$data['depid']) )
            {
                $errors['depid'] = $this->dof->get_string('limit_message','cstreams');
            }
        }else 
        {
            $depid = $this->dof->storage('cstreams')->get_field($this->cstream->id, 'departmentid');
            if ( ! $this->dof->storage('config')->get_limitobject('cstreams',$data['depid']) AND $depid != $data['depid'] )
            {
                $errors['depid'] = $this->dof->get_string('limit_message','cstreams');
            }
        }
        

        return $errors;
    }
    
    /** Возвращает массив периодов 
     * @return array список периодов, массив(id периода=>название)
     */
    private function get_list_ages()
    {
        // получаем список доступных учебных периодов
    	$rez = $this->dof->storage('ages')->get_records(array(
    	        'status'=>array('plan',
                             	'createstreams',
                             	'createsbc',
                             	'createschedule',
                            	'active')));
        // преобразуем список записей в нужный для select-элемента формат  
    	$rez = $this->dof_get_select_values($rez, true, 'id', array('name'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'ages', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        
    	if ( isset($this->cstream->ageid) AND $this->cstream->ageid )
    	{// если у нас уже указан период добавим его в список
    	    $rez[$this->cstream->ageid] = 
    	         $this->dof->storage('ages')->get_field($this->cstream->ageid,'name');
    	}
        
        return $rez;
    }
    /** Возвращает массив предметов
     * @return array список предметов, массив(id предмета=>название)
     */
    private function get_list_programmitems($programmid = null)
    {
    	$rez = array();
        // получаем список предметов, отсортированных по алфавиту
        if ( is_null($programmid) )
        {
    	    $pitems = $this->dof->storage('programmitems')->get_records
    	                    (array('status'=>array('active','suspend')));
        }else
        {
            $pitems = $this->dof->storage('programmitems')->get_records
                      (array('programmid'=>$programmid, 'status'=>array('active','suspend')));
        }
        // преобразуем список записей в нужный для select-элемента формат  
    	$rez = $this->dof_get_select_values($pitems, true, 'id', array('name', 'code'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'programmitems', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        
        if ( $this->cstream AND is_object($this->cstream) AND 
            isset($this->cstream->programmitemid) AND ! isset($rez[$this->cstream->programmitemid]) )
        {// если поток редактируется - и старого предмета нет в списке учителей
            if ( $olditem = $this->dof->storage('programmitems')->get($this->cstream->programmitemid) )
            {//  - добавим его принудительно
                $rez[$this->cstream->programmitemid] = $olditem->name.' ['.
                                 $olditem->code.']';
            }
        }
        asort($rez);
        return $rez;
    }
    /** Возвращает массив программ
     * @return array список программ, массив(id программы=>название)
     */
    private function get_list_programms()
    {
        // получаем список программ, отсортированных по алфавиту
        $programms = $this->dof->storage('programms')->get_records(array('status'=>'available'), 'name ASC');
        // преобразуем список записей в нужный для select-элемента формат  
    	$rez = $this->dof_get_select_values($programms, true, 'id', array('name', 'code'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'programms', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        
        return $rez;
    }
    /** Получает трехмерный массив списка учителей 
     * преподающих предмет для hierselectа
     * @return array
     */
    private function get_list_teachers_for_programm()
    {
        $rez = $this->dof_get_select_values();
        if ( ! $programms = $this->get_list_programms() )
        {// программ нет, вернем только нулевой элемент
            return $rez;
        }
        foreach ( $programms as $id=>$programm )
        {//для каждй программы выведем предметы с учителями
            $rez[$id] = $this->get_list_teachers_for_pitem($id);
        }
        asort($rez);
        return $rez;
    }
    /** Получает двумерный массив списка предметов 
     * для каждой программы для hierselectа
     * @return array
     */
    private function get_list_pitem_for_programm()
    {
        $rez = $this->dof_get_select_values();
        if ( ! $programms = $this->get_list_programms() )
        {// программ нет, вернем только нулевой элемент
            return $rez;
        }
        foreach ( $programms as $id=>$programm )
        {//для каждой программы выведем ее предметы
            $rez[$id] = $this->get_list_programmitems($id);
        }
        asort($rez);
        return $rez;
    }
    /** Получает двумерный массив списка учителей 
     * преподающих предмет для hierselectа
     * @return array
     */
    private function get_list_teachers_for_pitem($programmid = null)
    {
        $rez = $this->dof_get_select_values();
        if ( ! $pitems = $this->get_list_programmitems($programmid) )
        {// предметов нет, вернем только нулевой элемент
            return $rez;
        }
        foreach ( $pitems as $id=>$pitem )
        {//для каждого предмета выведем их учителей
            $rez[$id] = $this->get_list_teachers($id);
        }
        asort($rez);
        return $rez;
    }
    /** Возвращает массив персон
     * 
     * @return array список персон, массив(id предмета=>название)
     * @param object $cstream[optional] - объект из таблицы cstreams, если поток редактируется
     */
    protected function get_list_teachers($pitemid=null)
    {
        $rez = $this->dof_get_select_values();
        // получаем список всех кто может преподавать
        if ( is_int_string($pitemid) )
        {// если передан id предмета, выведем только учителей предмета
    	    $teachers = $this->dof->storage('teachers')->get_records
    	                           (array('programmitemid'=>$pitemid,'status'=>array('plan', 'active')));
        }else
        {// иначе выведем всех
            $teachers = $this->dof->storage('teachers')->get_records(array('status'=>array('plan', 'active')));
        }
        if ( $teachers AND isset($teachers) )
        {// получаем список пользователей по списку учителей
            $persons = $this->dof->storage('teachers')->get_persons_with_appid($teachers,true);
            // преобразовываем список к пригодному для элемента select виду
            $rez = $this->dof_get_select_values($persons, true, 'appointmentid', array('sortname','enumber'));
            asort($rez);
        }
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'appointments', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        
        if ( $this->cstream AND is_object($this->cstream) AND 
            isset($this->cstream->teacherid) AND ! isset($rez[$this->cstream->teacherid])
            AND $this->cstream->programmitemid == $pitemid )
        {// если поток редактируется - и старого учителя нет в списке учителей
            if ( $oldteacher = $this->dof->storage('persons')->get($this->cstream->teacherid) )
            {//  - добавим его принудительно
                $fullname = $this->dof->storage('persons')->get_fullname($oldteacher);
                $enumber = $this->dof->storage('appointments')->
                                 get_field($this->cstream->appointmentid,'enumber');
                $rez[$this->cstream->appointmentid] = "$fullname [$enumber]";
            }else
            {// напишем что учитель не найден в нашей базе
                $rez['00'] = $this->dof->get_string('no_teacher', 'cstreams');
            }
        }
        return $rez;
    }
    
    /** Возвращает количество недель по умолчанию
     * @return string
     */
    private function get_eduweeks()
    {
        $eduweeks = '';
        if ( $number = $this->dof->storage('programmitems')->get_field($this->cstream->programmitemid,'eduweeks') )
        {
            return $number;
        }
        if ( $number = $this->dof->storage('ages')->get_field($this->cstream->ageid,'eduweeks') )
        {
            return $number;
        }
        return $eduweeks;
    }
    /**
     * Возвращает строку заголовка формы
     * @param int $cstreamid
     * @return string
     */
    private function get_form_title($cstreamid)
    {
        if ( ! $cstreamid )
        {//заголовок создания формы
            return $this->dof->get_string('newcstream','cstreams');
        }else 
        {//заголовок редактирования формы
            return $this->dof->get_string('editcstream','cstreams');
        }
    }
    /**
     * Возврашает название статуса
     * @todo удалить, если не пригодится
     * @return unknown_type
     */
    private function get_string_time($date = null)
    {
        if ( is_null($date) )
        {
            return $this->dof->get_string('none','cstreams');
        }
        return dof_userdate($date,"%d-%m-%Y");
    }
    /**
     * Возвращает имя подразделения
     * @todo удалить, если не пригодится
     * @param $id
     * @return unknown_type
     */
    private function get_department_name($id)
    {
        return $this->dof->storage('departments')->get_field($id,'name');
    }
    
    /**
     * Возврашает название статуса
     * @todo удалить, если не пригодится
     * @return unknown_type
     */
    private function get_status_name($status)
    {
        return $this->dof->workflow('cstreams')->get_name($status);
    }
}

/** Класс формы для поиска предмето-класса
 * 
 */
class dof_im_cstreams_search_form extends dof_im_cstreams_edit_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    function definition()
    {
        $this->dof = $this->_customdata->dof;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('search','cstreams'));
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        //поле период
        $ages = $this->get_list_ages();
        $mform->addElement('select', 'ageid', $this->dof->get_string('age','cstreams').':', $ages);
        $mform->setType('ageid', PARAM_INT);        
        // поле "программа"
        $programms = $this->get_list_programms();
        $mform->addElement('select', 'programmid', $this->dof->get_string('programm','cstreams').':', $programms);
        $mform->setType('programmid', PARAM_INT);
        // поле "предмет"
        $pitems = $this->get_list_pitems();
        $mform->addElement('select', 'programmitemid', $this->dof->get_string('subject','cstreams').':', $pitems);
        $mform->setType('programmitemid', PARAM_INT);
        // поле "учитель"
        //$teachers = $this->get_list_teachers();
        //$mform->addElement('select', 'appointmentid', $this->dof->get_string('teacher','cstreams').':', $teachers);
        //$mform->setType('appointmentid', PARAM_INT);
        // поле "Отдел"
        /*
        $departments = $this->get_list_departments();
        $mform->addElement('select', 'departmentid', $this->dof->get_string('department','cstreams').':', $departments);
        $mform->setType('departmentid', PARAM_INT);
        */
        // поле "статус"
        $statuses = $this->get_list_statuses();
        $mform->addElement('select', 'status', $this->dof->get_string('status','cstreams').':', $statuses);
        $mform->setType('status', PARAM_TEXT);
        // поле "академическая группа"
        $agroups = $this->get_list_agroups();
        $mform->addElement('select', 'agroupid', $this->dof->get_string('agroup','cstreams').':', $agroups);
        $mform->setType('agroupid', PARAM_INT);
        // поле "замещающий коэффициент"
        $subsalfactor = $this->get_list_subsalfactor();
        $mform->addElement('select', 'subsalfactor', $this->dof->get_string('substsalfactor','cstreams').':', $subsalfactor);
        $mform->setType('substsalfactor', PARAM_TEXT);
        // поле "ученик"
        //$persons = $this->get_list_persons();
        //$mform->addElement('select', 'personid', $this->dof->get_string('student','cstreams').':', $persons);
        //$mform->setType('personid', PARAM_INT);
        // поле "начиная с"
        // $mform->addElement('dateselector', 'begindate', $this->dof->get_string('datefrom','cstreams').':');
        // кнопка "поиск"
        $this->add_action_buttons(false, $this->dof->get_string('to_find','cstreams'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Получить список всех учебных программ в нужном для формы поиска формате 
     * 
     * @return array массив для создания select-элемента
     */
    private function get_list_programms()
    {
        // извлекаем все учебные программы из базы
        $result = $this->dof->storage('programms')->
            get_records(array('status'=>array('available')),'name');
        $result = $this->dof_get_select_values($result, true, 'id', array('name', 'code'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'programms', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
    /** Получить список всех учебных программ в нужном для формы поиска формате 
     * 
     * @return array массив для создания select-элемента
     */
    private function get_list_pitems()
    {
        // извлекаем все учебные программы из базы
        $result = $this->dof->storage('programmitems')->
            get_records(array('status'=>array('active', 'suspend')),'name');
        $result = $this->dof_get_select_values($result, true, 'id', array('name', 'code'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'programmitems', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
    /** Получить список всех структурных отделов в нужном для формы поиска формате
     * 
     * @return array массив 
     */
    /*
    private function get_list_departments()
    {
        // извлекаем все отделы из базы
        $result = $this->dof->storage('departments')->departments_list_subordinated(null,'0',null,true);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
    */
    /** Список академических групп для формы поиска
     * @todo уточнить группы с какими статусами показывать
     * @return array 
     */
    private function get_list_agroups()
    {
        // извлекаем все академические группы из базы
        $result = $this->dof->storage('agroups')->get_records(array(),'name');
        $result = $this->dof_get_select_values($result, true, 'id', array('name', 'code'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'agroups', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
    /** Список учеников для формы поиска
     * 
     * @return array 
     */
    private function get_list_persons()
    {
        $result = array();
        // извлекаем все персоны
        $select = $this->dof->storage('persons')->
            get_records(array('status'=>array('normal', 'archived')),'sortname');
        if ( $select )
        {// данные удалось извлечь
            foreach ($select as $id=>$record)
            {// составляем массив вида id=>название группы
                $result[$id] = $this->dof->storage('persons')->get_fullname($id);
            }
        }
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'persons', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $this->dof_get_select_values() + $result;
    }
    /** Получить список периодов для добавления элемента select в форму
     * @return array 
     */
    private function get_list_ages()
    {
        $result = array();
        // извлекаем всех учителей из базы
        $select = $this->dof->storage('ages')->get_records(array(),'name');
        $result = $this->dof_get_select_values($select);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'ages', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
    
    /** Получить список всех доступных статусов учебного потока
     * 
     * @return array
     */
    private function get_list_statuses()
    {
        $statuses    = array();
        // добавляем значение, на случай, если по статусу искать не нужно
        $statuses[0] = '--- '.$this->dof->get_string('any_mr','cstreams').' ---';
        // получаем весь список статусов через workflow
        $statuses    = $statuses + $this->dof->workflow('cstreams')->get_list();
        // возвращаем список всех статусов
        return $statuses;
    }
    
    /** Получить список всех доступных статусов учебного потока
     * 
     * @return array
     */
    private function get_list_subsalfactor()
    {
        $list = array();
        // добавляем значение, на случай, если по статусу искать не нужно
        $list['none'] = '--- '.$this->dof->modlib('ig')->igs('any_mr').' ---';
        $list[1] = $this->dof->modlib('ig')->igs('present');
        $list[0] = $this->dof->modlib('ig')->igs('absent');
        // возвращаем список всех статусов
        return $list;
    }
}

/** Класс формы для связи групп с потоком
 * 
 */
class dof_im_cstreams_linkagroup extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    private $cstreamid;
    
    function definition()
    {
        $this->dof = $this->_customdata->dof;
        $this->cstreamid = $this->_customdata->cstreamid;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // скрытые поля
        $mform->addElement('hidden','cstreamid', $this->cstreamid);
        $mform->setType('cstreamid', PARAM_INT);
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        // информация о предмето-потоке
        $cstream = $this->dof->storage('cstreams')->get($this->cstreamid);
        $mform->addElement('header','formtitle', $this->dof->get_string('cstreaminformation','cstreams'));
        $mform->addElement('static', 'name', $this->dof->get_string('name','cstreams').':',
                           $cstream->name);
        $mform->addElement('static', 'age', $this->dof->get_string('age','cstreams').':',
                           $this->dof->storage('ages')->get_field($cstream->ageid,'name'));
        $mform->addElement('static', 'programmitem', $this->dof->get_string('programmitem','cstreams').':', 
                           $this->dof->storage('programmitems')->get_field($cstream->programmitemid,'name').' ['.
                           $this->dof->storage('programmitems')->get_field($cstream->programmitemid,'code').']');
        $mform->addElement('static', 'teacher', $this->dof->get_string('teacher','cstreams').':', 
                           $this->dof->storage('persons')->get_field($cstream->teacherid,'sortname'));
        $mform->addElement('static', 'department', $this->dof->get_string('department','cstreams').':', 
                           $this->dof->storage('departments')->get_field($cstream->departmentid,'name').' ['.
                           $this->dof->storage('departments')->get_field($cstream->departmentid,'code').']');  
        $mform->addElement('static', 'eduweeks', $this->dof->get_string('eduweeks','cstreams').':', $cstream->eduweeks);
        $mform->addElement('static', 'begindate', $this->dof->get_string('begindate','cstreams').':', 
                           dof_userdate($cstream->begindate,'%d-%m-%Y'));
        $mform->addElement('static', 'enddate', $this->dof->get_string('enddate','cstreams').':', 
                           dof_userdate($cstream->enddate,'%d-%m-%Y'));                                    
        // группы которые уже связаы с потоком
        if ( $links = $this->dof->storage('cstreamlinks')->get_cstream_cstreamlink($this->cstreamid) )
        {
           
            // заголовок
            $mform->addElement('header','formtitle', $this->dof->get_string('agroups_link','cstreams'));
            foreach ($links as $link)
            {
                // создаем массив
                $group = array();
                // Создаем элементы формы
                $group[] =& $mform->createElement('hidden','linkid', $link->id);
                
                $group[] =& $mform->createElement('select', 'agroupsync', null, 
                            $this->dof->storage('cstreamlinks')->get_list_agroupsync());
                $group[] =& $mform->createElement('checkbox','del', null, $this->dof->get_string('delete','cstreams'));
                // добавляем элементы в форму
                $grp =& $mform->addGroup($group, 'group'.$link->agroupid, 
                        '<div style="font-size: 12pt;">'.
                        $this->dof->storage('agroups')->get_field($link->agroupid,'name').
                        ' ['.$this->dof->storage('agroups')->get_field($link->agroupid,'code').']</div>');
                $mform->setType('group'.$link->agroupid.'[linkid]', PARAM_INT);
            }
            // кнопка сохранить
            $mform->addElement('submit', 'save_link', $this->dof->get_string('to_apply','cstreams'));
        }
        // группы, которые еще не связаны с потоком
        $agroups = $this->dof->storage('agroups')->get_group_nocstream($this->cstreamid);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'agroups', 'code'=>'use'));
        $agroups = $this->dof_get_acl_filtered_list($agroups, $permissions);
        
        if ( $agroups )
        {
            $cstream = $this->dof->storage('cstreams')->get($this->cstreamid);
            if ( $cstreams = $this->dof->storage('cstreams')->get_records(array('ageid'=>$cstream->ageid,
                        'programmitemid'=>$cstream->programmitemid, 'status'=>array('plan', 'active', 'suspend'))) )
            {// если уже есть такой поток
                foreach ( $agroups as $id=>$agroup )
                {
                    foreach ( $cstreams as $cstream )
                    {// и на него подписана группа
                        $params = array();
                        $params['cstreamid'] = $cstream->id;
                        $params['agroupid'] = $agroup->id;
                        if ( $this->dof->storage('cstreamlinks')->is_exists($params) ) 
                        {// поток не создаем
                            unset($agroups[$id]);
                            break;
                        }
                    }
                }
            }
        }
        if ( $agroups )
        {
            // заголовок
            $mform->addElement('header','formtitle', $this->dof->get_string('agroups_no_link','cstreams'));
            // создаем массив
            $group = array();
            // Создаем элементы формы
            $mform->addElement('select', 'groupid', $this->dof->get_string('agroup','cstreams'), $this->get_list_agroups($agroups));
            $mform->addElement('select', 'agroupsync', $this->dof->get_string('link_type','cstreams'), 
                        $this->dof->storage('cstreamlinks')->get_list_agroupsync());
            $mform->setDefault('agroupsync', 'nolink');
            // добавляем элементы в форму
            //$grp =& $mform->addGroup($group, 'group',null,'&nbsp;&nbsp;&nbsp;');
            $mform->addElement('submit', 'save_no_link', $this->dof->get_string('to_add','cstreams'));

        } 
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');   
    }
    /** Проверка данных на стороне сервера
     * 
     * @return 
     * @param object $data[optional] - массив с данными из формы
     * @param object $files[optional] - массив отправленнных в форму файлов (если они есть)
     */
    public function validation($data,$files)
    {
        $errors = array();
        $cstream = $this->dof->storage('cstreams')->get($this->cstreamid);
        if ( $cstreams = $this->dof->storage('cstreams')->get_records(array('ageid'=>$cstream->ageid,
                    'programmitemid'=>$cstream->programmitemid, 'status'=>array('plan', 'active', 'suspend'))) )
        {// если уже есть такой поток
            foreach ( $cstreams as $cstream )
            {// и на него подписана группа
                $params = array();
                $params['cstreamid'] = $cstream->id;
                $params['agroupid'] = $data['groupid'];
                if ( isset($data['groupid']) AND $this->dof->storage('cstreamlinks')->is_exists($params) ) 
                {// поток не создаем
                    $errors['groupid'] = $this->dof->get_string('error_isset_cstreamlink','cstreams');
                }
            }
        }
        if ( $csprogid = $this->dof->storage('programmitems')->get_field
                         ($cstream->programmitemid,'programmid') AND
                          isset($data['groupid']) AND $agprogid = 
                          $this->dof->storage('agroups')->get_field
                          ($data['groupid'],'programmid') )
        {// нет id программы - нет групп
            if ( $csprogid != $agprogid )
            {
                $errors['groupid'] = $this->dof->get_string('error_isset_cstreamlink','cstreams');
            }
        }
        return $errors;
    }
    
    
    
    
    /** ЭТО ЗАГОТОВКИ ДЛЯ НОВОЙ ФОРМЫ - в виде таблицы **/
    
    /**
     * возвращает html-код формы
     * @return string
     */
    public function get_form_agroups()
    {
        $rez = '';
        $rez .= '<form method="post">';
        $rez .= $this->get_agroups_table();
        $rez .= '</form>';
    }
    
    /**
     * Возвращает html-код таблицы подписанных групп
     * и групп, для которых можно создать привязку
     * @return string 
     */
    private function get_agroups_table()
    {
        //получаем группы, связанные с этим потоком
        $agroups = $this->dof->storage('agroups')->get_group_cstream($this->cstreamid);
        //получаем типы привязки каждой группы к потоку
        $agroupslinks = $this->dof->storage('cstreamlinks')->
             get_cstream_cstreamlink($this->cstreamid);
        if ( ! $agroups )
        {//нет групп - вернем пустую строку
            return '';
        }
        foreach ( $agroupslinks as $k => $v )
        {
           if (  array_key_exists($k, $agroups) )
           {
                $agroups[$k]->agroupsync = $agroupslinks[$k]->agroupsync;
           }else
           {
               $agroups[$k]->agroupsync = '';
           }
        }
        //группы есть - строим таблицу
        $table = new stdClass();
        $table->head = $this->get_head_table();
        $table->data = $this->get_data_table($agroups);
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
    
    /**
     * Возвращает заголовок таблицы групп как массив
     * @return array
     */
    private function get_head_table()
    {
        return array(
                        $this->dof->get_string('agroup', 'cstreams'),
                        $this->dof->get_string('link_type', 'cstreams'),
                        $this->dof->get_string('actions', 'cstreams'),
                    );
    }
    
    /**
     * возвращает массив строк таблицы
     * @param array  $agroups - массив записей 
     * о группах из таблицы с дополнительным 
     * полем agroupsync у каждой
     * @return array
     */
    private function get_data_table($agroups)
    {
        $array = array();
        //print_object($agroups);
        foreach( $agroups as $one )
        {
            //Сделаем имя группы ссылкой
            $path = $this->dof->url_im('agroups', '/view.php?agroupsid='.$one->id);
            $name = $one->name.'[<i>'.$one->code.'</i>]';
            $linkagroup = "<a href=\"{$path}\">{$name}</a>";
            //формируем одну строку
            $array[] = array($linkagroup, 
                             $this->get_linktype($one), 
                             $this->get_actions($one->id));
        }
        //добавим строку с кнопкой "применить"
        $apply = $this->dof->get_string('to_apply','cstreams');
        $array[] = array('&nbsp;','&nbsp;',
                 "<input type=\"submit\" name=\"change\" value=\"{$apply}\">");
        if ( $newlink = $this->get_menu_createlink() )
        {//добавим строку создания связи группы с потоком
            $array[] = $newlink;
        }
        return $array;
    }
    
    /**
     * Возвращает последний элемент строки - 
     * галочка на удаление группы
     * @param int $agroupid - id группы
     * @return string html-код строки
     */
    private function get_actions($agroupid)
    {
        return "<input type=\"checkbox\" 
            name=\"delete\" agroupid=\"{$agroupid}\">&nbsp;".
            $this->dof->get_string('delete','cstreams');
    }
    
    /**
     * Возвращает средний элемент строки таблицы - 
     * меню выбора типа привязки
     * @param object $agroup - запись из таблицы групп, 
     * с дополнительным полем agroupsync из таблицы cstreamlink
     * @return string
     */
    private function get_linktype($agroup)
    {
        //получим список всех типов привязок
        $linktypelist = $this->get_linktype_list(); 
        //формируем меню выбора привязки
        $rez = "<select name=\"{$agroup->id}\">";
        foreach ($linktypelist as $k => $v )
        {
            if ($k == $agroup->agroupsync)
            {//по умолчанию пункт меню будет выбран
                $selected = 'selected';
            }else
            {//все остальные пункты меню
                $selected = '';
            }
            $rez .= "<option {$selected} value=\"{$k}\" >{$v}</option>";
        }
        $rez .= '</select>';
        return $rez;
    }
    
    /**
     * Возвращает массив элементов последней строки - 
     * строки создания привязки группы к потоку 
     * @return array
     */
    private function get_menu_createlink()
    {
        $array = array();
        //получим группы, не привязанные к потоку
        $agroups = $this->dof->storage('agroups')->
        get_group_nocstream($this->cstreamid);
        if ( ! $agroups )
        {//все группы привязаны к потоку
            return false;
        }
        $menuagroups = '<select name="newlink>';
        foreach ( $this->get_list_agroups($agroups) as $id => $name )
        {
            $menuagroups .= "<option value=\"{$id}\">{$name}</option>";
        }
        $menuagroups .= '</select>';
        $array[] = $menuagroups;
        $array[] = $this->get_linktype_list();
        $buttonname = $this->dof->get_string('add','cstreams');
        $array[] = "<input type=\"submit\" name=\"createlink\" value=\"{$buttonname}\">";
        return $array;
    }
    
    /**
     * Возвращает массив возможных типов привязок курсов к потокам
     * @return array
     */
    private function get_linktype_list()
    {
        //получим список всех типов привязок
        $linktypelist = array();
        $linktypelist['none'] = $this->dof->get_string('choose', 'cstreams');
        $linktypelist = $linktypelist + 
        $this->dof->storage('cstreamlinks')->get_list_agroupsync();
        return $linktypelist;
    }
    
    /** Возвращает массив групп
     * @param array $agroups - список групп в виде объектов
     * @return array - unknown_type
     */
    private function get_list_agroups($agroups)
    {
        $rez = $this->dof_get_select_values();
        if ( ! is_array($agroups) )
        {// неверный формат данных
            return $rez;
        }
        $rez = $this->dof_get_select_values($agroups, true, 'id', array('name', 'code'));
        
        return $rez;
    }
}

/** Класс для формы создания учебных потоков для группы в текущем периоде
 * 
 */
class dof_im_cstreams_create_forgroup extends dof_modlib_widgets_form
{
   /**
    * @var dof_control 
    */
    protected $dof;
   /** Объявление класса формы
    */
    function definition()
    {
        $mform          = $this->_form;
        $this->dof      = $this->_customdata->dof;
        $this->agroupid = (int)$this->_customdata->agroupid;
        $this->ageid    = (int)$this->_customdata->ageid;
        // установим, были ли данные переданы извне, или установлены пользователем
        if ( $this->agroupid OR $this->ageid )
        {// данные были переданы извне
            $external = 1;
            $disabled = 'disabled';
        }else
        {// данные были заполнены пользователем
            $external = 0;
            $disabled = '';
        }
        // создаем заголовок формы
        $mform->addElement('header', 'formheader', $this->dof->get_string('create_cstreams', 'cstreams'));
        
        // поле выбора группы
        if ( $this->agroupid )
        {// если значение по умолчанию установлено - запрещаем редактировать поле
            $mform->addElement('select', 'agroupid_disabled', $this->dof->get_string('agroup','cstreams').':', 
            $this->get_list_agroups(), $disabled);
            $mform->setDefault('agroupid_disabled', $this->agroupid);
            // поскольку disabled-поля не передаются, то заведем специальное hidden-поле, 
            // которое будет выполнять эту функцию
            $mform->addElement('hidden', 'agroupid', $this->agroupid);
        }else
        {// в остальных случае выводим полный список значений
            $mform->addElement('select', 'agroupid', $this->dof->get_string('agroup','cstreams').':', 
            $this->get_list_agroups());
        }
        $mform->setType('agroupid', PARAM_INT);
        
        // период
        if ( $this->ageid )
        {// если период по умолчанию установлен - запрещаем редактировать поле выбора периода
            $mform->addElement('select', 'ageid_disabled', $this->dof->get_string('age','cstreams').':',
            $this->get_list_ages(), $disabled);
            $mform->setDefault('ageid_disabled', $this->ageid);
            // поскольку disabled-поля не передаются, то заведем специальное hidden-поле, 
            // которое будет выполнять эту функцию
            $mform->addElement('hidden', 'ageid', $this->ageid);
        }else
        {// в остальных случае выводим полный список значений
            $mform->addElement('select', 'ageid', $this->dof->get_string('age','cstreams').':',
            $this->get_list_ages());
        }
        $mform->setType('ageid', PARAM_INT);
        
        // подразделение
        $mform->addElement('checkbox', 'depcheck', null, 
                  $this->dof->get_string('take_department_from_pitem','cstreams'));
        $mform->setType('depcheck', PARAM_BOOL);
        $mform->setDefault('depcheck', false);
        
        $departments = $this->dof->storage('departments')->departments_list_subordinated(null,'0', null,true);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $departments = $this->dof_get_acl_filtered_list($departments, $permissions);
        
        $mform->addElement('select', 'departmentid', $this->dof->get_string('department','cstreams').':', $departments);
        $mform->setType('departmentid', PARAM_INT);
      
        $mform->disabledIf('departmentid', 'depcheck', 'checked'); 
        // время начала
        $mform->addElement('checkbox', 'agedates', null, $this->dof->get_string('begindate_match_period','cstreams'));
        $mform->setType('agedates', PARAM_BOOL);
        $mform->setDefault('agedates', true);
        // устанавливаем время по умолчанию
        $options = array();
        $options['startyear'] = dof_userdate(time() - 10*365 * 24 * 3600,'%Y');
        $options['stopyear']  = dof_userdate(time() + 5*365 * 24 * 3600,'%Y');
        $options['optional']  = false;
        // добавляем сам элемент
        $mform->addElement('date_selector', 'datebegin', $this->dof->get_string('begindate', 'cstreams').':', $options);
        $mform->setDefault('datebegin', time());
        $mform->disabledIf('datebegin', 'agedates', 'checked');
        $mform->addElement('radio', 'chooseend', null, $this->dof->get_string('enddate_match_period','cstreams'),'age');
        $mform->addElement('radio', 'chooseend', null, $this->dof->get_string('enddate_match_pitem','cstreams'),'pitem');
        $mform->addElement('radio', 'chooseend', null, $this->dof->get_string('enddate_set_manually','cstreams'),'manually');
        // добавляем сам элемент
        $mform->addElement('date_selector', 'dateend', $this->dof->get_string('enddate', 'cstreams').':', $options);
        $mform->setDefault('chooseend', 'age');
        $mform->setDefault('dateend', time());
        $mform->disabledIf('dateend', 'chooseend','noteq','manually');
        // кнопка "создать"
        $mform->addElement('submit', 'save', $this->dof->get_string('create_cstreams_button','cstreams'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
   /** Дополнительное определение класса.
    */
    public function definition_after_data()
    {
        // создаем ссылку на quickform
        $mform = $this->_form;
        if ( $agroup = $this->dof->storage('agroups')->get($this->agroupid) )
        {// если академическая группа указана извне, и она существует, то установим ее departmentid 
            // в качестве подразделения по умолчанию 
            if ( $agroup->departmentid )
            {// если подразделение указано корректно
                $mform->setDefault('departmentid', $agroup->departmentid);
            }
        }
    }
    
   /** Проверки данных формы
    * @todo добавить проверку даты начала
    * @todo добавить проверку существования программы и предметов в программе
    */
    public function validation($data, $files)
    {
        $errors = array();
        if ( ! isset($data['agroupid']) OR ! $data['agroupid'] )
        {// не указана академическая группа
            $errors['agroupid'] = $this->dof->get_string('agroup_not_specified', 'cstreams');
        }elseif ( ! $this->dof->storage('agroups')->is_exists($data['agroupid']) )
        {// проверим существование записи с переданным id в базе
            $errors['agroupid'] = $this->dof->get_string('agroup_not_found', 'cstreams');
        }
        if ( ! isset($data['ageid']) OR ! $data['ageid'] )
        {// не указан период
            $errors['ageid'] = $this->dof->get_string('age_not_specified', 'cstreams');
        }elseif ( ! $this->dof->storage('ages')->is_exists($data['ageid']) )
        {// проверим существование записи с переданным id в базе
            $errors['ageid'] = $this->dof->get_string('age_not_found', 'cstreams');
        }else
        {// если запись существует - то проверим, не выходит ли указанная дата начала потоков
            // за границу учебного периода
            if ( ! isset($data['agedates']) )
            {// только если не сказано взять ее из периода
                $age = $this->dof->storage('ages')->get($data['ageid']);
                if ( $data['datebegin'] < $age->begindate OR $data['datebegin'] > $age->enddate )
                {// дата начала обучения не совпадает с периодом - сообщим об этом, и подскажем
                    // правильные даты
                    $daterange = dof_userdate($age->begindate,'%Y-%m-%d').' - '.
                                 dof_userdate($age->enddate,'%Y-%m-%d');
                    $errors['datebegin'] = $this->dof->get_string('err_begindate', 'cstreams', $daterange);
                }
            }
            if ( isset($data['chooseend']) AND $data['chooseend'] == 'manually' )
            {// только если не сказано взять ее из периода
                $age = $this->dof->storage('ages')->get($data['ageid']);
                if ( $data['dateend'] < $age->begindate OR $data['dateend'] > $age->enddate )
                {// дата начала обучения не совпадает с периодом - сообщим об этом, и подскажем
                    // правильные даты
                    $daterange = dof_userdate($age->begindate,'%Y-%m-%d').' - '.
                                 dof_userdate($age->enddate,'%Y-%m-%d');
                    $errors['dateend'] = $this->dof->get_string('err_enddate', 'cstreams', $daterange);
                }
            }
        }
        if ( ! isset($data['depcheck']) )
        {// если подразделение выбрано пользователем
            // проверим существование подразделения
            if ( ! isset($data['departmentid']) OR ! $data['departmentid'] )
            {// не указано подразделение
                $errors['departmentid'] = $this->dof->get_string('department_not_specified', 'cstreams');
            }elseif ( ! $this->dof->storage('departments')->is_exists($data['departmentid']) )
            {// проверим существование записи с переданным id в базе
                $errors['departmentid'] = $this->dof->get_string('department_not_found', 'cstreams');
            }
        }
        // возвращаем ошибки, если они есть
        return $errors;
    }
    
    /** Получить список академических групп из базы данных для элемента select
     * @todo вставить проверку прав доступа
     * @return array массив в формате id_группы=>имя_группы
     */
    private function get_list_agroups()
    {
        // извлекаем все академические группы из базы
        $result = $this->dof->storage('agroups')->get_records(array(),'name');
        $result = $this->dof_get_select_values($result, true, 'id', array('name', 'code'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'agroups', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
    
    /** Получить список периодов из базы данных для элемента select
     * @todo вставить проверку прав доступа
     * @return array массив в формате id_периода=>имя_периода
     */
    private function get_list_ages()
    {
        // получаем список доступных учебных периодов
        $rez = $this->dof->storage('ages')->get_records(array
                ('status'=>array('plan',
                                'createstreams',
                                'createsbc',
                                'createschedule',
                                'active')));
        // преобразуем список записей в нужный для select-элемента формат  
        $rez = $this->dof_get_select_values($rez, true, 'id', array('name'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'ages', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        
        if ( isset($this->ageid) AND $this->ageid )
        {// если у нас уже указан период добавим его в список
            $rez[$this->ageid] = 
                 $this->dof->storage('ages')->get_field($this->ageid,'name');
        }
        
        return $rez;
    }
    
    /** Получить список всех подразделений
     * 
     * @return array массив в формате id_подразделения=>имя_подразделения
     */
    public function get_list_departments()
    {
        // извлекаем все отделы из базы
        $result = $this->dof->storage('departments')->departments_list_subordinated(null,'0',null,true);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
}

/** Класс для формы создания учебных потоков по учебной программе
 * для параллели
 */
class dof_im_cstreams_create_forprogramm extends dof_modlib_widgets_form
{
    /**
    * @var dof_control 
    */
    protected $dof;
    /** Объявление класса формы
     * @todo выровнять элемент hierselect
    */
    function definition()
    {
        // создаем ссылку на форму
        $mform            = &$this->_form;
        // забираем переданные зачения из данных 
        $this->dof        = &$this->_customdata->dof;
        $this->programmid = (int)$this->_customdata->programmid;
        $this->agenum     = (int)$this->_customdata->agenum;
        $this->ageid      = (int)$this->_customdata->ageid;
        // установим, были ли данные переданы извне, или установлены пользователем
        if ( ($this->programmid AND $this->agenum) OR $this->ageid )
        {// данные были переданы извне
            $external = 1;
            $disabled = 'disabled';
        }else
        {// данные были заполнены пользователем
            $external = 0;
            $disabled = '';
        }
        // создаем заголовок формы
        $mform->addElement('header', 'formheader', $this->dof->get_string('create_cstreams', 'cstreams'));
        // получаем значения для элемента hierselect
        $options = $this->get_hierselect_options();
        // исправление бага выравнивания hierselect
        $mform->addElement('html', '<div style=" line-height: 1.9; ">');
        // поле выбора программы 
        if ( $this->programmid )
        {// если значение по умолчанию установлено - запрещаем редактировать поле
            $hselect = &$mform->addElement('hierselect', 'prog_agenum', 
                        $this->dof->get_string('program','cstreams').':<br/>'.
                        $this->dof->get_string('parallel','cstreams').':', 
                        $disabled, '<br/>');
            // устанавливаем набор значений для hierselect
            $hselect->setOptions(array($options->programms, $options->agenums));
            // устанавливаем варианты выбора по умолчанию
            $mform->setDefault('prog_agenum', array($this->programmid, $this->agenum));
            // для того чтобы поля были залочены и после перезагрузки формы,
            // а также, чтобы не возникло проблем с проверкой
            // добавим дублирующие скрытые поля
            $mform->addElement('hidden', 'programmid', $this->programmid);
            $mform->setType('programmid', PARAM_INT);
            $mform->addElement('hidden', 'agenum', $this->agenum);
            $mform->setType('agenum', PARAM_INT);
        }else
        {// в остальных случае выводим полный список значений
            $hselect = &$mform->addElement('hierselect', 'prog_agenum', 
                        $this->dof->get_string('program','cstreams').':<br/>'.
                        $this->dof->get_string('parallel','cstreams').':', 
                        null, '<br/>');
            // устанавливаем набор значений для hierselect
            $hselect->setOptions(array($options->programms, $options->agenums));
        }
        // конец исправление бага hierselect 
        $mform->addElement('html', '</div>');
        
        
        // период
        if ( $this->ageid )
        {// если период по умолчанию установлен - запрещаем редактировать поле выбора периода
            $mform->addElement('select', 'ageid_disabled', $this->dof->get_string('age','cstreams').':',
            $this->get_list_ages(), $disabled);
            $mform->setDefault('ageid_disabled', $this->ageid);
            // для того чтобы поля были залочены и после перезагрузки формы,
            // а также, чтобы не возникло проблем с проверкой
            // добавим дублирующие скрытые поля
            $mform->addElement('hidden', 'ageid', $this->ageid);
            $mform->setType('ageid', PARAM_INT);
        }else
        {// в остальных случае выводим полный список значений
            $mform->addElement('select', 'ageid', $this->dof->get_string('age','cstreams').':',
            $this->get_list_ages());
        }
        $mform->setType('ageid', PARAM_INT);
        
        // подразделение
        $mform->addElement('checkbox', 'depcheck', null, 
                  $this->dof->get_string('take_department_from_pitem','cstreams'));
        $mform->setType('depcheck', PARAM_BOOL);
        $mform->setDefault('depcheck', false);
        
        $mform->addElement('select', 'departmentid', $this->dof->get_string('department','cstreams').':', 
        $this->get_list_departments());
        $mform->setType('departmentid', PARAM_INT);
        $mform->disabledIf('departmentid', 'depcheck', 'checked'); 
        // время начала
        $mform->addElement('checkbox', 'agedates', null, $this->dof->get_string('begindate_match_period','cstreams'));
        $mform->setType('agedates', PARAM_BOOL);
        $mform->setDefault('agedates', true);
        // устанавливаем время по умолчанию
        $options = array();
        $options['startyear'] = dof_userdate(time() - 10*365 * 24 * 3600,'%Y');
        $options['stopyear']  = dof_userdate(time() + 5*365 * 24 * 3600,'%Y');
        $options['optional']  = false;
        // добавляем сам элемент
        $mform->addElement('date_selector', 'datebegin', $this->dof->get_string('begindate', 'cstreams').':', $options);
        $mform->setDefault('datebegin', time());
        $mform->disabledIf('datebegin', 'agedates', 'checked');
        $mform->addElement('radio', 'chooseend', null, $this->dof->get_string('enddate_match_period','cstreams'),'age');
        $mform->addElement('radio', 'chooseend', null, $this->dof->get_string('enddate_match_pitem','cstreams'),'pitem');
        $mform->addElement('radio', 'chooseend', null, $this->dof->get_string('enddate_set_manually','cstreams'),'manually');
        // добавляем сам элемент
        $mform->addElement('date_selector', 'dateend', $this->dof->get_string('enddate', 'cstreams').':', $options);
        $mform->setDefault('chooseend', 'age');
        $mform->setDefault('dateend', time());
        $mform->disabledIf('dateend', 'chooseend','noteq','manually');
        // кнопка "создать"
        $mform->addElement('submit', 'save', $this->dof->get_string('create_cstreams_button','cstreams'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Получить массивы для элемента hierselect
     * 
     * @return object - объект, содержащий массивы нужной структуры для элемента hierselect
     */
    private function get_hierselect_options()
    {
        $result = new stdClass();
        // заполняем первый уровень массива - учебными программами
        $programms = $this->get_list_programms();
        foreach ( $programms as $id=>$programm )
        {// для каждой программы получаем список ее периодлов
            $agenums[$id] = $this->get_programm_agenums($id);
        }
        // создаем объект, содержащий массивы нужной структуры
        $result->programms = $programms;
        $result->agenums   = $agenums;

        return $result;
    }
    
    /** Дополнительное определение класса. Используется для
    */
    public function definition_after_data()
    {
        // создаем ссылку на quickform
        $mform = $this->_form;
        if ( $programm = $this->dof->storage('agroups')->get($this->programmid) )
        {// если академическая группа указана извне, и она существует, то установим ее departmentid 
            // в качестве подразделения по умолчанию 
            if ( $programm->departmentid )
            {// если подразделение указано корректно
                $mform->setDefault('departmentid', $programm->departmentid);
            }
        }
    }
    
    /** Проверки данных формы
    */
    public function validation($data, $files)
    {
        $errors = array();
        if ( ! isset($data['prog_agenum'][0]) OR ! $data['prog_agenum'][0] )
        {// не указана программа
            $errors['prog_agenum'] = $this->dof->get_string('programm_not_specified', 'cstreams');
        }elseif ( ! $this->dof->storage('programms')->is_exists($data['prog_agenum'][0]) )
        {// проверим существование записи с переданным id в базе
            $errors['prog_agenum'] = $this->dof->get_string('program_not_found', 'cstreams');
        }
        
        if ( ! isset($data['prog_agenum'][1]) OR ! $data['prog_agenum'][1] )
        {// не указан номер периода
            $errors['prog_agenum'] = $this->dof->get_string('agenum_not_specified', 'cstreams');
        }
        
        if ( ! isset($data['ageid']) OR ! $data['ageid'] )
        {// не указан период
            $errors['ageid'] = $this->dof->get_string('age_not_specified', 'cstreams');
        }elseif ( ! $this->dof->storage('ages')->is_exists($data['ageid']) )
        {// проверим существование записи с переданным id в базе
            $errors['ageid'] = $this->dof->get_string('age_not_found', 'cstreams');
        }else
        {// если запись существует - то проверим, не выходит ли указанная дата начала потоков
            // за границу учебного периода
            if ( ! isset($data['agedates']) )
            {// только если не сказано взять ее из периода
                $age = $this->dof->storage('ages')->get($data['ageid']);
                if ( $data['datebegin'] < $age->begindate OR $data['datebegin'] > $age->enddate )
                {// дата начала обучения не совпадает с периодом - сообщим об этом, и подскажем
                    // правильные даты
                    $daterange = dof_userdate($age->begindate,'%Y-%m-%d').' - '.
                                 dof_userdate($age->enddate,'%Y-%m-%d');
                    $errors['datebegin'] = $this->dof->get_string('err_begindate', 'cstreams', $daterange);
                }
            }
            if ( isset($data['chooseend']) AND $data['chooseend'] == 'manually' )
            {// только если не сказано взять ее из периода
                $age = $this->dof->storage('ages')->get($data['ageid']);
                if ( $data['dateend'] < $age->begindate OR $data['dateend'] > $age->enddate )
                {// дата начала обучения не совпадает с периодом - сообщим об этом, и подскажем
                    // правильные даты
                    $daterange = dof_userdate($age->begindate,'%Y-%m-%d').' - '.
                                 dof_userdate($age->enddate,'%Y-%m-%d');
                    $errors['dateend'] = $this->dof->get_string('err_enddate', 'cstreams', $daterange);
                }
            }
        }
        if ( ! isset($data['depcheck']) )
        {// если подразделение выбрано пользователем
            // проверим существование подразделения
            if ( ! isset($data['departmentid']) OR ! $data['departmentid'] )
            {// не указано подразделение
                $errors['departmentid'] = $this->dof->get_string('department_not_specified', 'cstreams');
            }elseif ( ! $this->dof->storage('departments')->is_exists($data['departmentid']) )
            {// проверим существование записи с переданным id в базе
                $errors['departmentid'] = $this->dof->get_string('department_not_found', 'cstreams');
            }
        }
        // возвращаем ошибки, если они есть
        return $errors;
    }
    
    /** Получить список всех учебных программ
     * 
     * @return array список для формирования элемента select в формате id_группы=>имя_группы
     */
    private function get_list_programms()
    {
        // извлекаем все учебные программы из базы
        $result = $this->dof->storage('programms')->
            get_records(array('status'=>array('available')),'name');
        $result = $this->dof_get_select_values($result, true, 'id', array('name', 'code'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'programms', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
    
    /** Получить список периодов группы
     * 
     * @return array
     * @param int $progid - id программы в таблице programms
     */
    private function get_programm_agenums($progid)
    {
        $result = array(0 => ' --- '.$this->dof->modlib('ig')->igs('select').' --- ');
        if ( ! $programm = $this->dof->storage('programms')->get($progid) )
        {// не получили учебную программу 
            return $result;            
        }elseif ( ! $programm->agenums )
        {// если периодов нет - так и напишем
            return $result;
        }
        
        for ( $i=1; $i<=$programm->agenums; $i++ )
        {// составляем элементы select-списка
            $result[$i] = $i.' '; // обязательно добавить пробел, чтобыв не глючил hierselect
        }
        // возвращаем резудьтат
        return $result;
    }
    
    /** Получить список периодов из базы данных для элемента select
     * @todo вставить название подразделения для каждого периода
     * @return array массив в формате id_периода=>имя_периода
     */
    private function get_list_ages()
    {
        // получаем список доступных учебных периодов
        $rez = $this->dof->storage('ages')->get_records(array(
                'status'=>array('plan',
                                'createstreams',
                                'createsbc',
                                'createschedule',
                                'active')));
        // преобразуем список записей в нужный для select-элемента формат  
        $rez = $this->dof_get_select_values($rez, true, 'id', array('name'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'ages', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        
        if ( isset($this->ageid) AND $this->ageid )
        {// если у нас уже указан период добавим его в список
            $rez[$this->ageid] = 
                 $this->dof->storage('ages')->get_field($this->ageid,'name');
        }
        
        return $rez;
    }
    
    /** Получить список всех подразделений
     * 
     * @return array массив в формате id_подразделения=>имя_подразделения
     */
    public function get_list_departments()
    {
        // извлекаем все отделы из базы
        $result = $this->dof->storage('departments')->departments_list_subordinated(null,'0',null,true);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
}

/** Класс, отвечающий за форму смену статуса учебного потока вручную
 * 
 */
class dof_im_cstreams_changestatus_form extends dof_modlib_widgets_changestatus_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    protected function im_code()
    {
        return 'cstreams';
    }
    
    protected function workflow_code()
    {
        return 'cstreams';
    }
}

/** Форма в которой можно выбрать вид отображения списка учеников для записи
 * 
 */
class dof_im_cstreams_viewmode_form extends dof_modlib_widgets_form
{
    function definition()
    {
        $mform     = $this->_form;
        $this->dof = $this->_customdata->dof;
        
        $choices = $this->get_variants();
        // добавляем элемент с выбором типа просмотра записей
        $mform->addElement('select', 'showtype', 
            $this->dof->get_string('show_persons_type', 'cstreams'), $choices);
        // по умолчанию показываем весь список пользователей, без групп
        $mform->setDefault('showtype', 'persons');
        // кнопка "выбрать"
        $mform->addElement('submit', 'select', $this->dof->modlib('ig')->igs('choose'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Список возможных вариантов просмотра списка добавляемых учеников
     * 
     * @return array
     */
    protected function get_variants()
    {
        $choices = array();
        $choices['groups']  = $this->dof->get_string('show_by_groups', 'cstreams');
        $choices['persons'] = $this->dof->get_string('show_by_persons', 'cstreams');
        
        return $choices;
    }
}

/** Класс формы поиска потоков по группам
 * 
 */
class dof_im_cstreams_search_form_by_groups extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    public function definition()
    {
        $this->dof = $this->_customdata->dof;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->modlib('ig')->igs('filter'));        
        $options = $this->get_list_options();
        // добавляем новый элемент выбора зависимых вариантов форму
        $myselect =& $mform->addElement('hierselect', 'progdata', 
                                        $this->dof->get_string('programm', 'cstreams').':<br/>'.
                                        $this->dof->get_string('agenum',  'cstreams').':<br/>',
                                        null,'<br/>');
        // устанавливаем для него варианты ответа
        // (значения по умолчанию устанавливаются в методе definition_after_data)
        $myselect->setOptions(array($options->programms, $options->agenums));
        // поле "Владелец групп"
        $ages = $this->get_list_ages();
        $mform->addElement('select', 'ageid', $this->dof->get_string('age','cstreams').':', $ages);
        $mform->setType('ageid', PARAM_INT);
        // поле "Владелец групп"
        $status_sbcs = $this->get_list_status();
        $mform->addElement('select', 'sbcstatus', $this->dof->get_string('status_sbcs','cstreams').':', $status_sbcs);
        $mform->setType('ageid', PARAM_INT);
        // кнопка "поиск"
        $this->add_action_buttons(false, $this->dof->modlib('ig')->igs('show'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    /** Возвращает массивы для hierselecta
     * @return array
     */
    protected function get_list_options()
    {
        // список программ
        $options = new stdClass;
        $options->programms = $this->get_list_programms();
        foreach ( $options->programms as $id=>$name )
        {// для каждой группы список параллелей
            $options->agenums[$id] = $this->get_list_agenums($id);
            
        }
        // вернем все
        return $options;
    }
    /** Получить список всех учебных программ в нужном для формы поиска формате 
     * 
     * @return array массив для создания select-элемента
     */
    protected function get_list_programms()
    {
        // извлекаем все учебные программы из базы
        $result = $this->dof->storage('programms')->
            get_records(array('status'=>array('available')),'name');
        $result = $this->dof_get_select_values($result, true, 'id', array('name', 'code'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'programms', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
    /** Список периодов для формы поиска
     * @param int $programmid - id программы
     * @return array 
     */
    protected function get_list_ages()
    {
        // получаем список доступных учебных периодов
        $rez = $this->dof->storage('ages')->get_records(array(
                'status'=>array('plan',
                                'createstreams',
                                'createsbc',
                                'createschedule',
                                'active')));
        // преобразуем список записей в нужный для select-элемента формат  
        $rez = $this->dof_get_select_values($rez, true, 'id', array('name'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'ages', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        
        if ( isset($this->ageid) AND $this->ageid )
        {// если у нас уже указан период добавим его в список
            $rez[$this->ageid] = 
                 $this->dof->storage('ages')->get_field($this->ageid,'name');
        }
        
        return $rez;
    }
    /** возвращает параллели программы
     * @param int $programmid - id программы
     * @return array
     */
    protected function get_list_agenums($programmid)
    {
        $result = array();
        // по умолчанию всегда 1 даже если такой параллели нет
        $result[1] = '1 ';
        $maxagenum = $this->dof->storage('programms')->get_field($programmid,'agenums');
        if ( $maxagenum )
        {// данные удалось извлечь
            for ( $i=2; $i<=$maxagenum; $i++ )
            {// составляем элементы select-списка
                $result[$i] = $i.' '; // обязательно добавить пробел, чтобы не глючил hierselect
            }
        }
        return $result;
    }
    
    /** Получить список всех структурных отделов в нужном для формы поиска формате
     * 
     * @return array массив 
     */
    protected function get_list_departments()
    {
        // извлекаем все отделы из базы
        $result = $this->dof->storage('departments')->departments_list_subordinated(null,'0',null,true);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
    
    /** Получить список всех структурных отделов в нужном для формы поиска формате
     * 
     * @return array массив 
     */
    protected function get_list_status()
    {
        // извлекаем все отделы из базы
        $result = array();
        $result['real'] = $this->dof->modlib('ig')->igs('all');
        $result['active'] = $this->dof->get_string('status_sbcs_active','cstreams');
        $result['actual'] = $this->dof->get_string('status_sbcs_actual','cstreams');
        $result['complete'] = $this->dof->get_string('status_sbcs_complete','cstreams');
        return $result;
    }
}

/** Класс формы импорта списка учебных процессов
 * 
 */
class dof_im_cstreams_import_form extends dof_modlib_widgets_form
{
    /*
     * Функция определения вида формы смены пароля
     * пароль вводится два раза. Оба поля обязательны к заполнению
     */
    protected function definition()
    {
        global $DOF;
        $this->dof = $DOF;
        $mform =& $this->_form;
        // выводим заголовок
        $mform->addElement('header', 'import', $this->dof->get_string('import_parameters','cstreams'));
        $ages = $this->get_list_ages();
        $mform->addElement('select', 'ageid', $this->dof->get_string('age','cstreams').':', $ages);
        $mform->setType('ageid', PARAM_INT);
        $mform->addRule('ageid',$this->dof->get_string('err_required', 'cstreams'), 'required',null,'client');
        // меню загрузки файла
        $mform->addElement('filepicker', 'userfile', $this->dof->get_string('import_file','cstreams').':');
        // кнопка подтверждения
        $group = array();
        $group[] = & $mform->createElement('submit', 'check', 
                     $this->dof->get_string('check_import_data','cstreams'));
        $group[] = & $mform->createElement('submit', 'begin', 
                     $this->dof->get_string('begin_import','cstreams'));
        $gr = & $mform->addElement('group','button',null,$group);
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Проверки данных формы
     * 
     */
    public function validation($data, $files)
    {
        $errors = array();
        // проверим существование периода
        if ( ! isset($data['ageid']) OR ! $this->dof->storage('ages')->is_exists($data['ageid']) )
        {// учебное подразделение не существует
            $errors['ageid'] = $this->dof->get_string('err_required','cstreams');
        }
        $ages = $this->get_list_ages();
        if ( isset($data['ageid']) AND ! array_key_exists($data['ageid'],$ages) )
        {
            $errors['ageid'] = $this->dof->get_string('error_isset_ageid','cstreams');
        }
        
        // возвращаем все возникшие ошибки, если они есть
        return $errors;
    }
    /** Список академических групп для формы поиска
     * @param int $programmid - id программы
     * @return array 
     */
    private function get_list_ages()
    {
        // получаем список доступных учебных периодов
        $result = $this->dof->storage('ages')->get_records(array(
                'status'=>array('plan',
                                'createstreams',
                                'createsbc',
                                'createschedule',
                                'active')));
        // преобразуем список записей в нужный для select-элемента формат  
        $result = $this->dof_get_select_values($result, true, 'id', array('name'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'ages', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        // найдем все актуальные потоки
        $cstreams = $this->dof->storage('cstreams')->get_records
                    (array('status'=>array('plan','active','suspend')));
        // исключим из списка периодов те, на которые есть потоки
        if ( $cstreams )
        {
            foreach ( $cstreams as $cstream )
            {
                if ( array_key_exists($cstream->ageid,$result) )
                {
                    unset($result[$cstream->ageid]);
                }
            }       
        }
        
        return $result;
    }
}  

/** Класс формы поиска потоков по назначениям на должности, договорам с сотрудниками и учебным потокам
 * 
 */
class dof_im_cstreams_search_form_by_load extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    function definition()
    {
        $this->dof = $this->_customdata->dof;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->modlib('ig')->igs('filter'));  
        // поле "Поиск по"
        $mform->addElement('select', 'search', $this->dof->modlib('ig')->igs('search').':', $this->get_search_list());
        $mform->setType('search', PARAM_INT);    
        $ajaxparams = $this->autocomplete_params();
        $mform->addElement('dof_autocomplete', 'person', $this->dof->get_string('teacher','cstreams').':', '', 
                $ajaxparams); 
         $mform->disabledIf('person', 'search', 'noeq', '0');
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        // кнопка "поиск"
        $this->add_action_buttons(false, $this->dof->modlib('ig')->igs('show'));
    }
    
    /**
     * Возвращает массив для select "Поиск по:"
     * @return array
     */
    private function get_search_list()
    {
        return array($this->dof->get_string('select_one_teacher','cstreams'),
                     $this->dof->get_string('appointments','cstreams'),
                     $this->dof->get_string('eagreements','cstreams'),
                     $this->dof->get_string('cstreams','cstreams'));
    }
    
    /** Внутренняя функция. Получить параметры для autocomplete-элемента 
     * @param string $type - тип autocomplete-элемента, для которого получается список параметров
     *                       personid - поиск по персонам
     *                       mdluser - поиск по пользователям Moodle
     * @param string $side - сторона, подписывающая договор
     *                       client - законный представитель
     *                       student - ученик
     * @param int $contractid[optional] - id договора в таблице contracts (если договор редактируется)
     * 
     * @return array
     */
    protected function autocomplete_params()
    {
        $options = array();
        $options['plugintype']   = "storage";
        $options['plugincode']   = "persons";
        $options['sesskey']      = sesskey();
        $options['type']         = 'autocomplete';
        $options['departmentid'] = $this->_customdata->departmentid;
        $options['querytype']  = "persons_list";  
        return $options;
    }
}

/** Класс формы подписки ученика на поток (в редакторе учебного плана)
 * 
 */
class dof_im_cstreams_assign_student_form extends dof_modlib_widgets_form
{
    private $cstream;
    //public $dof;
    
    function definition()
    {
        $this->cstream = $this->_customdata->cstream;
        $this->bindall = $this->_customdata->bindall;
        
        $this->dof     = $this->_customdata->dof;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $mform->addElement('hidden','bindall', $this->bindall);
        $mform->setType('bindall', PARAM_TEXT);
        $mform->addElement('hidden','pitemid', $this->cstream->pitemid);
        $mform->setType('pitemid', PARAM_INT);
        $mform->addElement('hidden','sbcid', $this->cstream->sbcid);
        $mform->setType('sbcid', PARAM_INT);
        $mform->addElement('hidden','agroupid', $this->cstream->agroupid);
        $mform->setType('agroupid', PARAM_INT);
        $mform->addElement('hidden','programmid', $this->cstream->programmid);
        $mform->setType('programmid', PARAM_INT);
        $mform->addElement('hidden','agenum', $this->cstream->agenum);
        $mform->setType('agenum', PARAM_INT);
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $opt = new stdClass();
        $opt->programmitemid = $this->cstream->pitemid;
         // создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('bind_on_cstream','cstreams'));
        $mform->addElement('static', 'ch', $this->dof->get_string('select_cstream','cstreams').':');
        if ( $this->cstream->ageid )
        {
            $opt->ageid = $this->cstream->ageid;
            $mform->setDefault('ageid', $this->cstream->ageid);
        }
        $opt->status = array('plan','active','suspend');
        if ( $this->cstream->sbcid )
        {
            $opt->nosbcid = $this->cstream->sbcid;
        }
        if ( $this->cstream->agroupid )
        {
            $opt->noagroupid = $this->cstream->agroupid;
        }
        // для создания списка обязательных предметов
        // только новые cstremas
        if ( ! $this->bindall  )
        {
            $cstreams = $this->dof->storage('cstreams')->get_listing($opt);
            // оставим в списке только те объекты, на использование которых есть право
            $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'cstreams', 'code'=>'use'));
            $cstreams = $this->dof_get_acl_filtered_list($cstreams, $permissions);
            
            if ( $cstreams )
            {
                foreach ( $cstreams as $cstream )
                {
                    if ( ! $this->cstream->ageid )
                    {
                        $cstream->name .= ' ['.$this->dof->storage('ages')->get_field($cstream->ageid,'name').']';
                    }
                    $mform->addElement('radio', 'cstreams', null, $cstream->name,$cstream->id);
                }
            }
        }    
        $mform->addElement('radio', 'cstreams',null, $this->dof->get_string('newcstream','cstreams'),'new');
        $mform->setDefault('cstreams', 'new'); 
        // период
        $mform->addElement('select', 'ageid', $this->dof->get_string('age','cstreams').':', $this->get_list_ages($this->bindall));
        $mform->setType('ageid', PARAM_INT);            

        
        $mform->addElement('select', 'appointmentid', $this->dof->get_string('teacher','cstreams').':', $this->get_list_teachers($this->cstream->pitemid));

        // получим все возможные подразделения для поля "select"
        $departments = $this->dof->storage('departments')->departments_list_subordinated(null,'0',null,true);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $departments = $this->dof_get_acl_filtered_list($departments, $permissions);
        
        // поле "подразделение"
        // ставим галочку что подразделение наследуемем из предмета
        if ( ! $this->bindall )
        {
            $mform->addElement('checkbox', 'depcheck', null, 
                  $this->dof->get_string('take_department_from_pitem','cstreams'));
            $mform->setType('depcheck', PARAM_BOOL);
            $mform->setDefault('depcheck', false);
        }    
        $mform->addElement('select', 'departmentid', $this->dof->get_string('department','cstreams').':', $departments, 'disabled');
        $mform->setType('departmentid', PARAM_INT);
        if ( ! $this->bindall  )
        {
            $mform->disabledIf('departmentid', 'depcheck', 'checked');
        }     
        // id группы в Moddle
        //$mform->addElement('static', 'mdlgroup', $this->dof->get_string('mdlgroup','cstreams').':', $this->dof->get_string('in_development','cstreams'));
        
        
        // количество недель
        $ageeduweeks = array();
        $ageeduweeks[] = $mform->createElement('text', 'eduweeks', null, 'size="2"');
        if ( ! $this->bindall  )
        {        
            $ageeduweeks[] = $mform->createElement('checkbox', 'checkeduweeks', null, 
                $this->dof->get_string('take_eduweeks_from_period', 'cstreams'));
        }        
        $mform->addElement('group','ageeduweeks',$this->dof->get_string('eduweeks','cstreams'),$ageeduweeks);
        $mform->setType('ageeduweeks[eduweeks]', PARAM_INT);
        $mform->setType('ageeduweeks[checkeduweeks]', PARAM_BOOL);
        if ( ! $this->bindall  )
        {
            $mform->disabledIf('ageeduweeks[eduweeks]', 'ageeduweeks[checkeduweeks]', 'checked');
        }    
        
        // количество часов всего
        $pitemhours = array();
        $pitemhours[] = $mform->createElement('text', 'hours', null, 'size="2"');
        if ( ! $this->bindall  )
        {
            $pitemhours[] = $mform->createElement('checkbox', 'checkhours', null, 
                $this->dof->get_string('take_hours_from_pitem', 'cstreams'));
        }        
        $mform->addElement('group','pitemhours',$this->dof->get_string('hours','cstreams'),$pitemhours);
        $mform->setType('pitemhours[hours]', PARAM_INT);
        $mform->setType('pitemhours[checkhours]', PARAM_BOOL);
        if ( ! $this->bindall  )
        {
            $mform->disabledIf('pitemhours[hours]', 'pitemhours[checkhours]', 'checked');
        }    
        
        // количество часов в недедю
        $pitemhoursweek = array();
        $pitemhoursweek[] = $mform->createElement('text', 'hoursweek', null, 'size="2"');
        if ( ! $this->bindall  )
        {
            $pitemhoursweek[] = $mform->createElement('checkbox', 'checkhoursweek', null, 
                $this->dof->get_string('take_hoursweek_from_pitem', 'cstreams'));
        }        
        $mform->addElement('group','pitemhoursweek',$this->dof->get_string('hoursweek','cstreams'),$pitemhoursweek);
        $mform->setType('pitemhoursweek[hoursweek]', PARAM_INT);
        $mform->setType('pitemhoursweek[checkhoursweek]', PARAM_BOOL);
        if ( ! $this->bindall  )
        {
            $mform->disabledIf('pitemhoursweek[hoursweek]', 'pitemhoursweek[checkhoursweek]', 'checked');
        }    
        
        // часов в неделю очно
        $mform->addElement('text', 'hoursweekinternally', $this->dof->get_string('hoursweekinternally','cstreams'), 'size="2"');
        $mform->setType('hoursweekinternally', PARAM_INT);
        $mform->addRule('hoursweekinternally',$this->dof->modlib('ig')->igs('form_err_numeric'), 'numeric',null,'client');
        $mform->addRule('hoursweekinternally',$this->dof->modlib('ig')->igs('form_err_numeric'), 'numeric',null,'server');        
        // часов в неделю дичтанционно
        $mform->addElement('text', 'hoursweekdistance', $this->dof->get_string('hoursweekdistance','cstreams'), 'size="2"');
        $mform->setType('hoursweekdistance', PARAM_INT);         
        $mform->addRule('hoursweekdistance',$this->dof->modlib('ig')->igs('form_err_numeric'), 'numeric',null,'client');
        $mform->addRule('hoursweekdistance',$this->dof->modlib('ig')->igs('form_err_numeric'), 'numeric',null,'server');         
        // поправочный зарплатный коэффициент
        $mform->addElement('radio', 'factor', null, $this->dof->get_string('salfactor','cstreams'),'sal');
        $mform->addElement('text', 'salfactor', '', 'size="10"');
        $mform->setType('salfactor', PARAM_TEXT);
        $mform->setDefault('salfactor', '0.00');
        // замещающий зарплатный коэффициент
        $mform->addElement('radio', 'factor', null, $this->dof->get_string('substsalfactor','cstreams'),'substsal');
        $mform->addElement('text', 'substsalfactor', '', 'size="10"');
        $mform->setType('substsalfactor', PARAM_TEXT);
        $mform->setDefault('substsalfactor', '0.00');
        // перекрываем противоположное поле
        $mform->setDefault('factor', 'sal');
        $mform->disabledIf('salfactor', 'factor', 'eq', 'substsal');
        $mform->disabledIf('substsalfactor', 'factor', 'eq', 'sal');
        // дата начала обучения
        $mform->addElement('checkbox', 'agedates', null, $this->dof->get_string('begindate_match_period','cstreams'));
        $mform->setType('agedates', PARAM_BOOL);

        $dateoptions = array();// объявляем массив для установки значений по умолчанию
        $dateoptions['startyear'] = dof_userdate(time(),'%Y')-12; // устанавливаем год, с которого начинать вывод списка
        $dateoptions['stopyear']  = dof_userdate(time(),'%Y')+12; // устанавливаем год, которым заканчивается список
        $dateoptions['optional']  = false; // убираем галочку, делающую возможным отключение этого поля
        // добавляем сам элемент
        $mform->addElement('date_selector', 'begindate', $this->dof->get_string('begindate', 'cstreams').':', $dateoptions);
        $mform->addElement('radio', 'chooseend', null, $this->dof->get_string('enddate_match_period','cstreams'),'age');
        if ( ! $this->bindall  )
        {
            $mform->addElement('radio', 'chooseend', null, $this->dof->get_string('enddate_match_pitem','cstreams'),'pitem');
        }    
        $mform->addElement('radio', 'chooseend', null, $this->dof->get_string('enddate_set_manually','cstreams'),'manually');
        // добавляем сам элемент
        $mform->addElement('date_selector', 'enddate', $this->dof->get_string('enddate', 'cstreams').':', $dateoptions);
        $mform->setDefault('begindate', time());
        $mform->setDefault('enddate', time());
        // если поток создается, делаем галочки поставленными
        $mform->setDefault('ageeduweeks[checkeduweeks]', true);
        $mform->setDefault('pitemhours[checkhours]', true);
        $mform->setDefault('pitemhoursweek[checkhoursweek]', true);
        $mform->setDefault('agedates', true);
        $mform->setDefault('chooseend', 'age');

        // дата начала и дата окончания обучения не используется, если указано, что она совпадает с периодом
        $mform->disabledIf('begindate', 'agedates', 'checked'); 
        $mform->disabledIf('enddate', 'chooseend','noteq','manually');
        // если не выбрано создание нового - закрываем форму создания
        // $mform->disabledIf('ageid','cstreams','noteq','new');
        $mform->disabledIf('appointmentid','cstreams','noteq','new');
        $mform->disabledIf('departmentid','cstreams','noteq','new');
        $mform->disabledIf('depcheck','cstreams','noteq','new');
        $mform->disabledIf('ageeduweeks[eduweeks]','cstreams','noteq','new');
        $mform->disabledIf('ageeduweeks[checkeduweeks]','cstreams','noteq','new');
        $mform->disabledIf('pitemhours[hours]','cstreams','noteq','new');
        $mform->disabledIf('pitemhours[checkhours]','cstreams','noteq','new');
        $mform->disabledIf('pitemhoursweek[hoursweek]','cstreams','noteq','new');
        $mform->disabledIf('pitemhoursweek[checkhoursweek]','cstreams','noteq','new');
        $mform->disabledIf('agedates','cstreams','noteq','new');
        $mform->disabledIf('begindate','cstreams','noteq','new');
        $mform->disabledIf('chooseend','cstreams','noteq','new');
        $mform->disabledIf('enddate','cstreams','noteq','new');
        $mform->disabledIf('hoursweekdistance','cstreams','noteq','new');
        $mform->disabledIf('hoursweekinternally','cstreams','noteq','new');
        // кнопоки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->get_string('to_save','cstreams'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    /** Проверка данных на стороне сервера
     * 
     * @return 
     * @param object $data[optional] - массив с данными из формы
     * @param object $files[optional] - массив отправленнных в форму файлов (если они есть)
     */
    public function validation($data,$files)
    {
        $errors = array();
        // проверим существование периода
        if ( isset($data['cstreams']) AND $data['cstreams'] === 'new' AND ! $this->dof->storage('ages')->is_exists($data['ageid']) )
        {// учебное подразделение не существует
            $errors['ageid'] = $this->dof->get_string('err_required','cstreams');
        }
        if ( isset($data['hoursweekinternally']) AND $data['hoursweekinternally'] )
        {// количество очных часов в неделю - только положительное
            if ($data['hoursweekinternally'] < 0)
            {
                $errors['hoursweekinternally'] = $this->dof->modlib('ig')->igs('form_err_only_positive');
            }
        }
        if ( isset($data['hoursweekdistance']) AND $data['hoursweekdistance'] )
        {// количество дистанционных очных часов в неделю - только положительное
            if ($data['hoursweekdistance'] < 0)
            {
                $errors['hoursweekdistance'] = $this->dof->modlib('ig')->igs('form_err_only_positive');
            }
        }
        return $errors;
    }
    /** Список академических групп для формы поиска
     * @param int $programmid - id программы
     * @return array 
     */
    private function get_list_ages($all = false)
    {
        if ( $all )
        {
            return array( $this->cstream->ageid => $this->dof->storage('ages')->get_field($this->cstream->ageid,'name') );
        }
        // получаем список доступных учебных периодов
        $result = $this->dof->storage('ages')->get_records(array(
                'status'=>array('plan',
                                'createstreams',
                                'createsbc',
                                'createschedule',
                                'active')));
        // преобразуем список записей в нужный для select-элемента формат  
        $result = $this->dof_get_select_values($result, true, 'id', array('name'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'ages', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    } 
    
    /** Возвращает массив персон
     * 
     * @return array список персон, массив(id предмета=>название)
     * @param object $cstream[optional] - объект из таблицы cstreams, если поток редактируется
     */
    protected function get_list_teachers($pitemid=null)
    {
        $rez = $this->dof_get_select_values();
        // получаем список всех кто может преподавать
        
        if ( isset($pitemid) AND $pitemid )
        {// если передан id предмета, выведем только учителей предмета
    	    $teachers = $this->dof->storage('teachers')->get_records(array
    	                           ('programmitemid'=>$pitemid,'status'=>array('plan', 'active')));
        }else
        {// иначе выведем всех
            $teachers = $this->dof->storage('teachers')->get_records(array('status'=>array('plan', 'active')));
        }
        if ( isset($teachers) AND $teachers )
        {// получаем список пользователей по списку учителей
            $persons = $this->dof->storage('teachers')->get_persons_with_appid($teachers,true);
            // преобразовываем список к пригодному для элемента select виду
            $rez = $this->dof_get_select_values($persons, true, 'appointmentid', array('sortname','enumber'));
            
            // оставим в списке только те объекты, на использование которых есть право
            $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'appointments', 'code'=>'use'));
            $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
            
            asort($rez);
        }
        if ( $this->cstream AND is_object($this->cstream) AND 
            isset($this->cstream->teacherid) AND ! isset($rez[$this->cstream->teacherid])
            AND $this->cstream->programmitemid == $pitemid )
        {// если поток редактируется - и старого учителя нет в списке учителей
            if ( $oldteacher = $this->dof->storage('persons')->get($this->cstream->teacherid) )
            {//  - добавим его принудительно
                $fullname = $this->dof->storage('persons')->get_fullname($oldteacher);
                $enumber = $this->dof->storage('appointments')->
                                 get_field($this->cstream->appointmentid,'enumber');
                $rez[$this->cstream->appointmentid] = "$fullname [$enumber]";
            }else
            {// напишем что учитель не найден в нашей базе
                $rez['00'] = $this->dof->get_string('no_teacher', 'cstreams');
            }
        }
        return $rez;
    }
    
    /** Обрабатывает данные при нажатии кнопки
     * @return mixed - bool false, если не получилось извлечь данные из таблицы,
     *                 array массив ошибок или пустой, если все записи удалось обновить. 
     */
    public function execute_form()
    {
        if ( $this->is_cancelled() )
        {//ввод данных отменен - возвращаем на страницу просмотра класса
            $addvars = array();
            $addvars['programmid'] = $this->cstream->programmid;
            $addvars['agenum'] = $this->cstream->agenum;
            $addvars['ageid'] = $this->cstream->ageid;
            $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
            redirect($this->dof->url_im('cstreams','/by_groups.php',$addvars));
        }elseif ( $this->is_submitted() AND $this->is_validated() AND $data = $this->get_data() )
        {// была нажата кнопка - получим данные
            $addvars = array();
            $addvars['programmid'] = $data->programmid;
            $addvars['agenum'] = $data->agenum;
            $addvars['ageid'] = $this->cstream->ageid;
            $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
            if ( empty($data->cstreams) )
            {
                redirect($this->dof->url_im('cstreams','/by_groups.php',$addvars));
            }
            if ( $data->cstreams === 'new' )
            {
                $cstream = new stdClass();
                if ( isset($data->ageeduweeks['checkeduweeks']) AND $data->ageeduweeks['checkeduweeks'] )
                {//если количество недель сказано брать из периода
                    $cstream->eduweeks = $this->dof->storage('ages')->get_field($data->ageid,'eduweeks');
                    if ( $number = $this->dof->storage('programmitems')->get_field($data->pitemid,'eduweeks') )
                    {// или из предмета, если указано там
                        $cstream->eduweeks = $number;
                    } 
                }else
                {//если нет - берем из формы
                    $cstream->eduweeks = intval($data->ageeduweeks['eduweeks']);
                }
                if ( isset($data->pitemhours['checkhours']) AND $data->pitemhours['checkhours'] )
                {//если количество часов всего указано - возьмем из предмета
                    $cstream->hours = $this->dof->storage('programmitems')->get_field($data->pitemid,'hours');  
                }else
                {//если нет - берем из формы
                    $cstream->hours = intval($data->pitemhours['hours']);
                }
                if ( isset($data->pitemhoursweek['checkhoursweek']) AND $data->pitemhoursweek['checkhoursweek'] )
                {//если количество часов в неделю указано - возьмем из предмета
                    $cstream->hoursweek = $this->dof->storage('programmitems')->get_field($data->pitemid,'hoursweek');  
                }else
                {//если нет - берем из формы
                    $cstream->hoursweek = intval($data->pitemhoursweek['hoursweek']);
                }
                if ( ! isset($data->departmentid) )
                {// подразделение не указано - возьмем из предмета
                    $cstream->departmentid = $this->dof->storage('programmitems')->get_field($data->pitemid, 'departmentid');
                }else
                {
                    $cstream->departmentid = $data->departmentid;
                }
                
                // часов в неделю дистанционно
                if (isset($data->hoursweekdistance) AND $data->hoursweekdistance )
                {
                    $cstream->hoursweekdistance  = $data->hoursweekdistance;    
                }
                // часов в неделю очно    
                if (isset($data->hoursweekinternally) AND $data->hoursweekinternally )
                {
                    $cstream->hoursweekinternally  = $data->hoursweekinternally;    
                }                 
                
                $cstream->ageid = $data->ageid;
                $cstream->appointmentid  = $data->appointmentid;
                $cstream->teacherid = 0;
                if ( $cstream->appointmentid )
                {// если есть назначение - найдем учителя
                    $cstream->teacherid = $this->dof->storage('appointments')->
                                           get_person_by_appointment($cstream->appointmentid)->id;
                }
                if ( isset($data->agedates) AND $data->agedates )
                {// в форме было сказано взять данные из периода
                    $data->begindate  = $this->dof->storage('ages')->get_field($data->ageid,'begindate');
                } 
                if ( isset($data->chooseend) AND $data->chooseend == 'age' )
                {// в форме было сказано взять данные из периода
                    $data->enddate  = $this->dof->storage('ages')->get_field($data->ageid,'enddate');
                } 
                if ( isset($data->chooseend) AND $data->chooseend == 'pitem' )
                {// в форме было сказано взять из предмета
                    // это сделает сам метод
                    $data->enddate  = $data->begindate + $this->dof->storage('programmitems')->
                                          get_field($data->pitemteacher[1], 'maxduration');
                } 
                // зарплатные коэффициенты     
                if ( $data->factor == 'sal' )   
                {// указан поправочный
                    $cstream->salfactor = $data->salfactor; 
                    $cstream->substsalfactor = 0; 
                }elseif ( $data->factor == 'substsal' )   
                {// указан замещающий
                    $cstream->salfactor = 0; 
                    $cstream->substsalfactor = $data->substsalfactor; 
                }
                $cstream->begindate  = $data->begindate;
                $cstream->enddate    = $data->enddate;
                if ( $this->bindall )
                {// подписка всех - массив iteьs 
                    $items = $this->dof->storage('programmitems')->get_pitems_list($this->cstream->programmid,$this->cstream->agenum);
                    $cstreamsid = array();
                    $itemsid = array();
                    foreach ( $items as $key=>$item)
                    {
                        $cstream->programmitemid = $key; 
                        if( $csid = $this->dof->storage('cstreams')->insert($cstream) )
                        {// все в порядке - сохраняем статус и возвращаем на страниу просмотра класса
                            // соберем все новые cstreams
                            $cstreamsid[] = $csid;
                            $itemsid[] = $key;
                            $this->dof->workflow('cstreams')->init($csid);
                        }else
                        {// класс выбран неверно - сообщаем об ошибке
                            return '<p style=" color:red; " align="center"><b>'.
                                   $this->dof->get_string('errorsavecstream','cstreams').'</b></p>';
                        } 
                    }  
                }else 
                {// один предмет

                    $cstream->programmitemid = $data->pitemid;
                    if( $csid = $this->dof->storage('cstreams')->insert($cstream) )
                    {// все в порядке - сохраняем статус и возвращаем на страниу просмотра класса
                        $this->dof->workflow('cstreams')->init($csid);
                    }else
                    {// класс выбран неверно - сообщаем об ошибке
                        return '<p style=" color:red; " align="center"><b>'.
                               $this->dof->get_string('errorsavecstream','cstreams').'</b></p>';
                    }
                }    
            }else
            {
                $csid = $data->cstreams;
                $data->ageid = $this->dof->storage('cstreams')->get_field($data->cstreams,'ageid');
            }
            // для одной программы
            if ( isset($data->sbcid) AND ! $this->bindall AND $sbc = $this->dof->storage('programmsbcs')->get($data->sbcid) ) 
            {
                //$addvars['departmentid'] .= '#ps'.$data->sbcid;
                $cpassed = new stdClass;
                $cpassed->programmsbcid  = $data->sbcid; 
                $cpassed->programmitemid = $data->pitemid;
                $cpassed->cstreamid      = $csid;
                $cpassed->ageid          = $data->ageid;
                // узнаем id ученика
                $cpassed->studentid      = $this->dof->storage('contracts')->get_field($sbc->contractid,'studentid');
                if( $id = $this->dof->storage('cpassed')->insert($cpassed) )
                {// все в порядке - сохраняем статус и возвращаем на страниу учебного плана
                    $this->dof->workflow('cpassed')->init($id);
                    redirect($this->dof->url_im('cstreams','/by_groups.php',$addvars).'#ps'.$data->sbcid);
                }else
                {// класс выбран неверно - сообщаем об ошибке
                    return '<p style=" color:red; " align="center"><b>'.
                           $this->dof->get_string('errorsavecstream','cstreams').'</b></p>';
                }
            }
            // для списка программ
            if ( isset($data->sbcid) AND  $this->bindall AND $sbc = $this->dof->storage('programmsbcs')->get($data->sbcid) ) 
            {
                foreach ( $cstreamsid as $key=>$id )
                {
                    $cpassed->programmitemid = $itemsid[$key];
                    $cpassed->cstreamid      = $id;           
                    $cpassed->programmsbcid  = $data->sbcid;
                    $cpassed->ageid          = $data->ageid;                    
                    // узнаем id ученика
                    $cpassed->studentid      = $this->dof->storage('contracts')->get_field($sbc->contractid,'studentid');
                    if( $id = $this->dof->storage('cpassed')->insert($cpassed) )
                    {// все в порядке - сохраняем статус и возвращаем на страниу учебного плана
                        $this->dof->workflow('cpassed')->init($id);
                    }else
                    {// класс выбран неверно - сообщаем об ошибке
                        return '<p style=" color:red; " align="center"><b>'.
                               $this->dof->get_string('errorsavecstream','cstreams').'</b></p>';
                    }
                }
                redirect($this->dof->url_im('cstreams','/by_groups.php',$addvars).'#ps'.$data->sbcid);
            }            
            
            if ( isset($data->agroupid) AND $agp = $this->dof->storage('agroups')->get($data->agroupid) AND ! $this->bindall ) 
            {
                //$addvars['departmentid'] .= '#ag'.$data->agroupid;
                $link = new stdClass();
                $link->cstreamid  = $csid;
                $link->agroupid  = $data->agroupid;
                $link->agroupsync = 'full';
                if ( $this->dof->storage('cstreamlinks')->insert($link) )
                {// успешно - сообщим об этом
                    redirect($this->dof->url_im('cstreams','/by_groups.php',$addvars).'#ag'.$data->agroupid);
                }else
                {// не успешно - тоже сообщим
                    return '<p style=" color:red; " align="center"><b>'.
                           $this->dof->get_string('nosuccessfulupdate','cstreams').'</b></p>';
                }
            }
            if ( isset($data->agroupid) AND $agp = $this->dof->storage('agroups')->get($data->agroupid) AND $this->bindall )
            {
                $link = new stdClass();
                $link->agroupid  = $data->agroupid;
                $link->agroupsync = 'full';
                foreach ( $cstreamsid as $id )
                {                
                    $link->cstreamid  = $id;
                    if ( ! $this->dof->storage('cstreamlinks')->insert($link) )
                    {// не успешно -  сообщим
                        return '<p style=" color:red; " align="center"><b>'.
                               $this->dof->get_string('nosuccessfulupdate','cstreams').'</b></p>';
                    }                
                }
                // все хорошо
                redirect($this->dof->url_im('cstreams','/by_groups.php',$addvars).'#ag'.$data->agroupid);
            }
            return ''; 
        }
    }

}
?>