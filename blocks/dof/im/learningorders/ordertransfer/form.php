<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
//                                                                        //
// Copyright (C) 2008-2999                                                //
// Ilia Smirnov (Илья Смирнов)                                            //
// Evgenij Tsygantsov (Евгений Цыганцов)                                  //
// Alex Djachenko (Алексей Дьяченко)  alex-pub@my-site.ru                 //
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

require_once('lib.php');
// подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/** класс формы редактирования данных по группе и ученику
 * @todo оптимизировать извлечение данных из приказа, вывев приказ в отдельное поле объекта формы
 * 
 */
class dof_im_learningorders_ordertransfer_group_and_student extends dof_modlib_widgets_form
{
    protected $dof;
    protected $groupid;
    protected $sbcid;
    /**
     * @var string - тип перевода в котором состоит группа (transfer/condtransfer/etc.)
     */
    protected $type;
    protected $agenum; 
    protected $ageid; 
    protected $array_type;
    /**
     * @var int id текущего приказа
     */
    protected $orderid;
    /**
     * @var object вся информация по текущему приказу
     */
    protected $orderdata;
    /**
     * @var dof_im_learningorders_ordertransfer объект приказа, со всеми методами
     */
    protected $order;
    /**
     * @var int - id программы 
     */
    protected $programid;

    protected function im_code()
    {
        return 'learningorders';
    }
    
    /** Получить список типов обучения
     * 
     * @return array
     */
    protected function define_learningtypes($sbcid=null)
    {
        if ( ! $sbcid )
        {// если id подписки не передано - то по попробуем найти его в _customdata
            $sbcid = $this->sbcid;
        }
        // массив типов переводов
        if ( $sbcid )
        {// будем формировать список вариантов для одного ученика
            $agenums = $this->dof->storage('programms')->get_field(
                 $this->dof->storage('programmsbcs')->get_field($sbcid,'programmid'),'agenums');
        }else
        {// Будем формировать список вариантов для группы
            $agenums = $this->dof->storage('programms')->get_field(
                 $this->dof->storage('agroups')->get_field($this->groupid,'programmid'),'agenums');
        }
        if( $this->dof->storage('programmsbcs')->get_field($sbcid,'status') == 'onleave' )
        {// из академического можно только востановить
             $this->array_type = array(
                                // 6
                                'restore'      => $this->dof->get_string('restore_form',$this->im_code()),
                                // 5
                                'exclude'      => $this->dof->get_string('exclude_form',$this->im_code())
                                );
        }elseif ( $agenums <= $this->agenum )
        {// выпускников переводить нельзя - некуда
             $this->array_type = array(
                                // 3
                                'notransfer'   => $this->dof->get_string('notransfer_form',$this->im_code()),
                                // 4
                                'academ'       => $this->dof->get_string('academ',$this->im_code()),
                                // 5
                                'exclude'      => $this->dof->get_string('exclude_form',$this->im_code())
                                );
        }elseif( $this->dof->storage('programmsbcs')->get_field($sbcid,'status') == 'suspend' )
        {// приостановленные подписки не должны попадать в приказ
             $this->array_type = array(
                                // 3
                                'notransfer'   => $this->dof->get_string('notransfer_form',$this->im_code()),
                                // 4
                                'academ'       => $this->dof->get_string('academ',$this->im_code()),
                                // 5
                                'exclude'      => $this->dof->get_string('exclude_form',$this->im_code())
                                );
        }else
        {// выводим весь список
             $this->array_type = array(
                                // 1 
                                'transfer'     => $this->dof->get_string('transfer_form',$this->im_code(), $this->agenum +1),
                                // 2 
                                'condtransfer' => $this->dof->get_string('condtransfer_form',$this->im_code()),
                                // 3                  
                                'notransfer'   => $this->dof->get_string('notransfer_form',$this->im_code()),
                                // 4
                                'academ'       => $this->dof->get_string('academ',$this->im_code()),
                                // 5
                                'exclude'      => $this->dof->get_string('exclude_form',$this->im_code())
                                );
        }
        
        return $this->array_type;
    }
    
    /** Определить тип перевода группы
     * 
     * @return string - тип перевода группы
     * @param int $group - id группы в объекте orderdata
     */
    protected function get_group_type($groupid)
    {
        if ( ! isset($this->orderdata->data->groups[$groupid]) )
        {// такой группы нет в приказе - мы не можем определить тип ее перевода
            return false;
        }
        $group = $this->orderdata->data->groups[$groupid];
        if ( $group->exclude )
        {// группа исключена из приказа
            return 'exclude';
        }
        if ( $group->oldagenum == $group->newagenum )
        {// группа оставлена на второй год
            return 'notransfer';
        }else
        {// группа переведена
            return 'transfer';
        }
    }
    
    /** Функция для обхода бага в QuickForm-элементе 'hierselect'
     * Получает номер варианта перевода ученика
     * Эта функция возвращает числа вместо нормальных ассоциативных ключей массива.
     * Это происходит из-за ошибки в элементе hierselect - в нем нельзя использовать ассоциативные 
     * массивы - javascript перестает работать.
     * 
     * @return int 
     * @param string $type - тип перевода
     */
    protected function get_number_by_transfertype($type)
    {
        switch ( $type )
        {
            case 'transfer':     return 1; break;
            case 'condtransfer': return 2; break;
            case 'notransfer':   return 3; break;
            case 'academ':       return 4; break;
            case 'exclude':      return 5; break;
            case 'restore':      return 6; break;
        }
    }
      
    function definition()
    {// перезапишем данные
        $this->dof        = $this->_customdata->dof;
        $this->sbcid      = $this->_customdata->sbcid;
        $this->orderid    = $this->_customdata->orderid;
        $this->agenum     = $this->_customdata->agenum;
        $this->groupid    = $this->_customdata->groupid;
        $this->ageid      = $this->_customdata->newageid;
        $this->type       = $this->_customdata->type;
        $this->programmid = $this->_customdata->programmid;
        $this->array_type = array();
        // получаем всю информацию по текущему приказу
        $order = new dof_im_learningorders_ordertransfer($this->dof, $this->orderid);
        $this->order     = $order;
        $this->orderdata = $order->get_order_data();
        
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $mform->addElement('hidden','sbcid', $this->_customdata->sbcid);
        $mform->setType('sbcid', PARAM_INT);
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden','agenum', $this->_customdata->agenum);
        $mform->setType('agenum', PARAM_INT);
        $mform->addElement('hidden','type', $this->_customdata->type);
        $mform->setType('type', PARAM_TEXT);
        $mform->addElement('hidden','agroupid', $this->_customdata->groupid);
        $mform->setType('agroupid', PARAM_INT);
        
        if ( $this->sbcid )
        {// отображается форма одного ученика
            $this->student_form_part($this->sbcid,$this->type);
            $this->add_action_buttons(true, $this->dof->modlib('ig')->igs('save')); 
        }else 
        {// отображается форма для группы учеников
            $this->group_form_part();
        }
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Выводит часть формы, относящуюся к группе
     * 
     * @return null
     */
    protected function group_form_part()
    {
        $mform =& $this->_form;
        // Определяем кого куда можно переводить 
        $this->define_learningtypes();
        
        if ($this->groupid)
        {// если просматривается список учеников группы
            $group = $this->dof->storage('agroups')->get($this->groupid);
            // получаем список возможных типов перевода
            $options = $this->array_type;
            
            $mform->addElement('header','name', $group->name );
            $mform->addElement('select', 'newtype', $this->dof->get_string('transfer_type', $this->im_code()), 
                             $options,' style="max-width:400px;width:100%;" ');
            // по умолчанию - переденный
            $mform->setDefault('newtype', $this->get_group_type($this->groupid));
            
            // возможных вариантов периодов
            $ages = array();
            $ageids = $this->orderdata->data->ages->where;
            foreach ( $ageids as $ageid )
            {
                $ages[$ageid] = $this->dof->storage('ages')->get_field($ageid, 'name');
            }
            $mform->addElement('select', 'newageid', $this->dof->get_string('age', $this->im_code()), 
                               $ages, ' style="max-width:400px;width:100%;" ');
            // по умолчанию - переденный
            $mform->setDefault('newageid', $this->ageid);
        }
        
        // кнопки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->modlib('ig')->igs('save'));
        
        // нет студентов в группе - не покажеи их
        if ( isset($this->orderdata->data->student->{$this->type}[$this->agenum][$this->groupid]) )
        {// список всех учеников группы
            $this->group_students_list(); 
        }
        

    }
    
    /** Выводит часть кода, отвечающую за отображение одного ученика
     * @todo добавить проверки на извлечения данных из базы
     * 
     * @param int $sbcid - id подписки на программу в таблице programmsbcs
     * @param string $transfertype - тип перевода по умолчанию
     * @param bool $checkbox - добавлять ли галочку "изменить" 
     * @return null
     */
    protected function student_form_part($sbcid, $transfertype='1', $checkbox=false, $sbcdata=null)
    {
        $mform =& $this->_form;
        
        if ( ! $contractid = $this->dof->storage('programmsbcs')->get_field($sbcid, 'contractid') )
        {// передан неправильный id контракта
            return;
        }
        if ( ! $studentid = $this->dof->storage('contracts')->get_field($contractid, 'studentid') )
        {// ученика с таким id нет в базе
            return;
        }        
        $name        = $this->dof->storage('persons')->get_fullname($studentid);
        $defaulttype = $this->get_number_by_transfertype($transfertype);
         // заголовок с ФИО ученика
        $mform->addElement('header','name', $name );
        
        if ( $checkbox )
        {
            // галочка "изменить"
            $mform->addElement('checkbox', 'change'.$sbcid, $this->dof->modlib('ig')->igs('change'));
            // форму будет нельзя изменить пока не нажата соответствующая галочка
            $mform->disabledIf('student'.$sbcid, 'change'.$sbcid, 'notchecked');
        }
        
        // Определяем кого куда можно переводить 
        $this->define_learningtypes($sbcid);
        
        $learningtypes = array();
        foreach ( $this->array_type as $typename=>$selectstring )
        {// устанавливаем числовые ключи в массиве вместо ассоциативных
            // для того чтобы решить проблему с hierselect
            $numberkey = $this->get_number_by_transfertype($typename);
            $learningtypes[$numberkey] = $selectstring;
        }
        
        foreach ( $learningtypes as $key=>$type )
        {// перебираем все типы обучения и определяем в зависимости от него группу
            $groups = array();
            // получаем тип перевода (латинскими буквами - из-за бага с hierselect)
            $learningtype = dof_im_learningorders_ordertransfer_get_transfertype($key);
            // получаем список групп для select-элемента
            $groups = $this->get_groups($sbcid, $learningtype, $transfertype);
            
            foreach ( $groups as $agroupid=>$group )
            {// перебираем все группы - и в зависимости от каждой указываем период
                $showall = false;
                if ( ! $agroupid AND ( $learningtype == 'transfer' OR 
                                       $learningtype == 'condtransfer' OR
                                       $learningtype == 'notransfer' ) )
                {// если ученик переводится, или остается на второй год без группы -  
                    // то его можно перевести в любой период
                    $showall = true;
                }
                
                $learningages[$key][$agroupid] = $this->get_age($agroupid, $this->agenum, $showall);
            }
            $learninggroups[$key] = $groups;
        }
        
       
        // добавляем новый элемент в форму
        $myselect =& $mform->addElement('hierselect', 'student'.$sbcid, 
                        $this->dof->get_string('transfer_type', $this->im_code()).'<br/>'.
                        $this->dof->get_string('group', $this->im_code()).'<br/>'.
                        $this->dof->get_string('age', $this->im_code()), null,'<br/>');
        // устанавливаем для него варианты ответа
        $myselect->setOptions(array($learningtypes, $learninggroups, $learningages));
        
        if ( isset($sbcdata->newageid) )
        {
            $ageid = $sbcdata->newageid;
        }else
        {
            $ageid = $this->ageid;
        }
        // значения по умолчанию
        $mform->setDefault('student'.$sbcid, array($defaulttype, $this->groupid, $ageid));
    }
    
    /** Функция получения групп, в которые можно записать ученика
     * 
     * @param int $sbcid - номер подписки на программу для ученика
     * @param string $learningtype - тип перевода, для которого составляется список групп
     * @param string $studenttype - тип перевода ученика
     * 
     * @return array - массив групп для select-элемента
     */    
    public function get_groups($sbcid, $learningtype, $studenttype=null)
    {
        $groups  = array('0' => $this->dof->get_string('no_group', $this->im_code()) );
        if ( ! $studenttype )
        {
            $studenttype = $this->type;
        }
        
        // узнаем программу по подписке ученика
        $programmid = $this->dof->storage('programmsbcs')->get_field($sbcid, 'programmid');
        
        
        if ( isset($this->orderdata->data->student->{$studenttype}) AND
             isset($this->orderdata->data->student->{$studenttype}[$this->agenum]) AND
             isset($this->orderdata->data->student->{$studenttype}[$this->agenum][$this->groupid]) )
        {// если нужные группы существуют - то найдем их
            // находим в приказе данные о подписке ученика
            $sbcdata = $this->orderdata->data->student->{$this->type}[$this->agenum][$this->groupid][$sbcid];
            
            foreach ( $this->orderdata->data->groups as $id=>$agroup )
            {// ищем подходящие группы для перевода
                // определяем переводится группа или остается на второй год
                if ( $agroup->oldagenum == $agroup->newagenum )
                {// параллель не меняется - группа остается на второй год
                    $grouptype = 'notransfer';
                }else
                {// группа переводится
                    $grouptype = 'transfer';
                }
                // определяем параллель, в которой должна находится группа 
                $agenum = $this->define_agenum_by_learningtype($sbcdata, $learningtype, $grouptype, $studenttype);
                if ( $agroup->oldagenum != $agenum )
                {// группа из неподходящей параллели
                    continue;
                }
                // определяем, по какой программе учится группа
                $groupobj = $this->dof->storage('agroups')->get($id);
                if ( $groupobj->programmid != $programmid )
                {// программа группы не соответствует программе ученика
                    continue;
                }
                // нужная группа найдена - добавим ее в массив
                $groups[$id] = $groupobj->name.' ['.$groupobj->code.']';
            }
        }
        
        // во всех остальных случаях есть только один вариант "без группы"
        return $groups;
    }
    
    
    /** Определить параллель в зависимости от того что происходит с учеником:
     * переводится он в следующую параллель, или остается на второй год
     * 
     * @return int - номер параллели, в которую должен перевестить ученик
     * @param object $sbcdata - данные о подписке ученика: куда и откуда его переводят и т. п.
     * @param string $oldtype - старый тип перевода ученика: переведен, перведен условно,  
     *                          оставлен на второй год или восстановлен
     *                          Это тип ученика на момент загрузки формы 
     * @param string $grouptype - переведена, переведена условно, оставлена на второй год
     * @param string $newtype - новый тип перевода ученика: этот тип бужет присвоен ученику 
     *                          после сохранения формы
     */
    protected function define_agenum_by_learningtype($sbcdata, $newtype, $grouptype, $oldtype)
    {
        switch ( $newtype )
        {// сначала определим тип ученика
            // ученик переведен или условно переведен 
            case 'transfer':  
            case 'condtransfer': 
                switch ( $grouptype )
                {// определяем тип группы
                    // переведенная или условно переведенная группа
                    case 'transfer':   return $sbcdata->oldagenum; break;
                    // группа оставленная на второй год
                    case 'notransfer': return $sbcdata->newagenum; break;
                }
            break;
            // ученик или группа остается на второй год или восстановлен  
            case 'notransfer': 
            case 'restore': 
                switch ( $grouptype )
                {// определяем тип группы
                    // переведенная или условно переведенная группа
                    case 'transfer':   return $sbcdata->oldagenum-1; break;
                    // группа оставленная на второй год
                    case 'notransfer': return $sbcdata->oldagenum;   break;
                }
            break;
        }
        
        // в остальных случаях параллель - нулевая (ученик исключен или переведен)
        return 0;
    }
    
    /** Функция получения возможных периодов для select-элемента формы
     * 
     * @param int $agroupid - id группы для которой получается период 
     * @param int $agenum - параллель в которой находится группа
     * @param bool $showall - показать все периоды (для формы группы, чтобы 
     *                        не оставался единственный вариант)
     * @return array $ages - массив периодов
     */    
    public function get_age($agroupid, $agenum, $showall=false)
    {
        $ages = array();
        
        if ( $agroupid AND ! $showall )
        {// если группа выбрана - то автоматически определим единственный период
            $ageid   = $this->order->get_ageid_by_agroupid($agroupid);
            $agename = $this->dof->storage('ages')->get_field($ageid, 'name');
            $ages[$ageid] = $this->dof->get_string('group_period', $this->im_code()).' ('.$agename.')';
        }else
        {// нужно вывести все периоды
            $ageids = $this->orderdata->data->ages->where;
            foreach ( $ageids as $ageid )
            {
                $ages[$ageid] = $this->dof->storage('ages')->get_field($ageid, 'name');
            }
        }
        
        return $ages;
    }
    
    /** Функция получения вcех студентов
     *  
     * @return null
     */ 
    public function group_students_list()
    {
        $mform =& $this->_form;
        
        // массив подписок
        $grouptudents = $this->orderdata->data->student->{$this->type}[$this->agenum][$this->groupid];
        // cортировка по lastname
        uksort($grouptudents, array('dof_im_learningorders_ordertransfer_group_and_student','sort_group_students_list'));
        // перебираем всех учеников
        foreach ($grouptudents as $sbcid=>$value)
        {// для каждого ученика создаем форму
            
            if ( $this->programmid )
            {// если просматривается список учеников без группы
                $programmid = $this->dof->storage('programmsbcs')->get_field($sbcid, 'programmid');
                
                if ( $this->programmid != $programmid )
                {// то мы не должны в одном списке выводить учеников с разными программами
                    continue;
                }
                $this->student_form_part($sbcid, $this->type, true, $value);
            }else
            {// просматривается список группы
                $this->student_form_part($sbcid, $this->type, true);
            }
            
        }
        if ( ! empty($grouptudents) )
        {// если список учеников группы не пуст - то еще раз покажем внизу кнопки сохранить и отмена
            $this->add_action_buttons(true, $this->dof->modlib('ig')->igs('save'));
        }
    }
    
    
    /** Функция сортировки студентов по sortname
	 *
     * @return array
     */
    private function sort_group_students_list($a,$b)
    {// первый элемент    
        $contractid = $this->dof->storage('programmsbcs')->get_field($a,'contractid'); 
        $studentid  = $this->dof->storage('contracts')->get_field($contractid,'studentid');
        $sortnamea  = $this->dof->storage('persons')->get_field( $studentid,'sortname');
        // второй элемент
        $contractid = $this->dof->storage('programmsbcs')->get_field($b,'contractid');
        $studentid  = $this->dof->storage('contracts')->get_field($contractid,'studentid');
        $sortnameb  = $this->dof->storage('persons')->get_field($studentid,'sortname');            

        return strnatcmp($sortnamea, $sortnameb);
    } 
    
    
    /**Проверка типа перевода и выбранного периода
     * 
     * @param object $data - массив данных
     * @param object $key1 - название поля в $data, содержащего тип перевода
     * @param object $key2 - название поля в $data, содержащего группу
     * @return bool
     */
    protected function type_check($data, $key1, $key2)
    {
        //echo $data[$key1].'<br>';
        if ( ! ($value = dof_im_learningorders_ordertransfer_get_transfertype($data[$key1])) )
        {
            $value = $data[$key1];
        }
        switch($value)
        {//Проверяем тип перевода
            case 'transfer':
            case 'condtransfer':
            case 'notransfer':
            case 'restore':
            	if( !$data[$key2] )
                {//Если не исключаем и не отправляем в академ, то период должен быть ненулевым
                    return $this->dof->get_string('age_not_chosen', 'learningorders');
                }
            case 'academ':
            case 'exclude':
            break;
            default: return $this->dof->get_string('wrong_type_error', 'learningorders');
        }
        return false;
    }

    /**Проверка данных
     * @param object $data
     * @return 
     */
    function validation($data,$files)
    {
        //print_object($data);die;
        if ( !$data['sbcid'] AND $data['agroupid'] )
        {
            if ( !isset($data['newtype']) )
            {//Не указан тип перевода
                return array('newtype' => $this->dof->get_string('no_type_error', 'learningorders'));
            }
            if ( !isset($data['newageid']) )
            {//Не указан период перевода
                return array('newageid' => $this->dof->get_string('no_age_error', 'learningorders'));
            }
        }
        $result = array();
        if ( !$data['sbcid'] AND $data['agroupid'] )
        {//проверим соответствие выбранного типа перевода и периода
            if ( $res = $this->type_check($data, 'newtype', 'newageid') )
            {
                $result = array('newageid' => $res);
            }
        }
        foreach($data as $key => $value)
        {//проверим всех студентов, чьи данные мы меняем
            // Если наткнулись на запись о студенте, то проверяем, включена ли галочка
            //или редактируем ли одного студента
            if ( is_array($value) AND ( isset($data['change'.substr($key, strlen('student'))]) OR $data['sbcid'] ) )
            {
                if ( !isset($value[0]) OR !isset($value[1]) OR !isset($value[2]) )
                {//Неверный формат переданных данных
                    return array($key => $this->dof->get_string('no_student_data', 'learningorders'));
                }
                if ( $res = $this->type_check($value, 0, 2) )
                {//если при проверке обнаружились ошибки, записываем их
                    $result = array_merge($result, array( $key => $res));
                }
            }
        }
        return $result;
    }
}

/** класс отображения select-базовый период и кнопки ДАЛЕЕ
 * 
 */
class dof_im_learningorders_ordertransfe_base extends dof_modlib_widgets_form
{
    protected $dof;
    protected $base;
    protected $age;
      
    function definition()
    {// создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // получаем данные
        $this->dof = $this->_customdata->dof;
        $this->ages = $this->_customdata->ages;
        $this->base = $this->_customdata->base;
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        // элемент select
        if( $this->ages->to )
        {
            $mform->addElement('select', 'base','', $this->ages->to, 'style="max-width:200px;width:100%" ');
            // по умолчанию - БАЗОВЫЙ
            $mform->setDefault('base', $this->base);
        }else
        {
            $mform->addElement('select', 'base','', array($this->dof->get_string('to_list_empty', 'learningorders')), 'style="max-width:200px;width:100%" ');
        }
        // кнопока ДАЛЕЕ
        $mform->addElement('submit', 'next', $this->dof->get_string('next', 'learningorders') );
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

/** Проверяет на заполненость обязательных данных
 *  КУДА и ОТКУДА
 *  а именно WHERE и FROM
 */    
    function validation($data,$files)
    {
        if( ! $this->ages->from )
        {// не заполнено поле ОТКУДА
            return array('next' => $this->dof->get_string('from_list_empty', 'learningorders'));
        }
        if( ! $this->ages->to )
        {// не заполнено поле КУДА
            return array('next' => $this->dof->get_string('to_list_empty', 'learningorders'));
        }
        return array();
    }
}





?>