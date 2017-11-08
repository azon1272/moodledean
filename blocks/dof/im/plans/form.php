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

/** Класс формы редактирования или создания контрольный точки
 * @todo запретить редактировать форму если контрольная точка удалена
 */
class dof_im_plans_edit_form extends dof_modlib_widgets_form
{
    private   $plan;
    /**
     * @var dof_control
     */
    protected $dof;
    protected $linktype;
    protected $linkid;
    
    protected function storage_code()
    {
        return 'plans';
    }
    
    protected function im_code()
    {
        return 'plans';
    }
    
    function definition()
    {// делаем глобальные переменные видимыми
        $this->plan = $this->_customdata->point;
        $this->dof  = $this->_customdata->dof;
        if ( isset($this->plan->id) AND $this->plan->id )
        {// если контрольная точка редактируется - то возьмем привязку из нее
            $this->linktype = $this->plan->linktype;
            $this->linkid   = $this->plan->linkid;
        }else
        {// если контрольная точка создается - то возьмем информацию о привязке из переданных параметров 
            $this->linktype = $this->_customdata->linktype;
            $this->linkid   = $this->_customdata->linkid;
        }
        // получаем дату начала периода или потока (если есть)
        $this->begindate = 0;
        if ( $dateobject = $this->get_current_age($this->linktype, $this->linkid) )
        {
            $this->begindate = $dateobject->begindate;
        }
        
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        // id контрольной точки
        $mform->addElement('hidden','pointid', $this->plan->id);
        $mform->setType('pointid', PARAM_INT);
        //  ключ сессии
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        // количество часов на домашнюю работу (для будущего пересчета)
        $mform->addElement('hidden','homeworkhours', 0);
        $mform->setType('homeworkhours', PARAM_INT);
        // объект привязки - 2 hidden-поля
        // тип связи 
        $mform->addElement('hidden','linktype', $this->linktype);
        $mform->setType('linktype', PARAM_ALPHANUM);
        // id связи
        $mform->addElement('hidden','linkid', $this->linkid);
        $mform->setType('linkid', PARAM_INT);
        // дата начала периода или потока (если есть)
        $mform->addElement('hidden','begindate', $this->begindate);
        $mform->setType('begindate', PARAM_INT);
        
        // создаем заголовок формы
        $mform->addElement('header','formtitle', $this->get_form_title($this->plan->id));
        
        // получаем список всех возможных тематических разделов для этой контрольной точки
        $plansections = $this->dof_get_select_values(
                                                    $this->get_plan_sections_list($this->linktype, 
                                                                                  $this->linkid),
                                                    array(0 => '--- '.$this->dof->modlib('ig')->igs('absent').' ---'));
        // тематический раздел
        $mform->addElement('select', 'plansectionsid', $this->dof->get_string('plansection',$this->im_code()).':',
                           $plansections, ' style="max-width:400px;width:100%;" ');
        $mform->setType('plansectionsid', PARAM_INT);
        
        // получаем список возможных родительских тем для тематиеского планирования
        $themes = $this->get_list_point($this->plan->id, $this->linktype, $this->linkid);
        // родительская тема 1
        $mform->addElement('select', 'parentid1', $this->dof->get_string('parenttheme',$this->im_code()).' 1:',
                           $themes, ' style="max-width:400px;width:100%;" ');
        $mform->setType('parentid1', PARAM_INT);
        // родительская тема 2
        $mform->addElement('select', 'parentid2', $this->dof->get_string('parenttheme',$this->im_code()).' 2:',
                           $themes, ' style="max-width:400px;width:100%;" ');
        $mform->setType('parentid2', PARAM_INT);
        // родительская тема 3
        $mform->addElement('select', 'parentid3', $this->dof->get_string('parenttheme',$this->im_code()).' 3:',
                           $themes, ' style="max-width:400px;width:100%;" ');
        $mform->setType('parentid3', PARAM_INT);
        
        // @todo переделать этот алгоритм тогда когда появится возможность задавать более 3-х родительских тем.
        // Сейчас он работает КРИВО и через ЗАДНИЦУ. Да простит меня Алан Тьюринг. Аминь.
        if ( $this->plan->id )
        {// устанавливаем по умолчанию значения в 3 родительские темы
            if ( $parentpoints = $this->dof->storage('planinh')->get_records(array('inhplanid'=>$this->plan->id)) )
            {
                $i = 1;
                foreach ( $parentpoints as $parentpoint )
                {
                    if ( $mform->elementExists('parentid'.$i) )
                    {
                        $mform->setDefault('parentid'.$i, $parentpoint->planid);
                    }
                    ++$i;
                }
            }
        }
        
        // название темы
        $mform->addElement('textarea', 'name', $this->dof->get_string('theme',$this->im_code()).':', array('cols'=>54, 'rows'=>6));
        /*
        $planname =& $mform->addElement('autocomplete', 'name', $this->dof->get_string('theme',$this->im_code()).':');
        // создаем переменную, которая будет храниить все возможные варианты подсказок
        $variants = (array)fullclone($themes);
        // удаляем из массива тем нулевой пункт "выбрать"
        unset($variants[0]);
        if ( ! empty($variants) )
        {// устанавливаем массив подсказок для поля "название темы"
            $planname->setOptions($variants);
        }  */
        $mform->setType('name', PARAM_TEXT);
        
        // тип темы
        $mform->addElement('select', 'type', $this->dof->get_string('typetheme',$this->im_code()).':', 
                           $this->dof->modlib('refbook')->get_lesson_types());
        
        // Номер темы в плане
        // @todo включить опцию установки номера в планировании когда это станет возможным
        //$mform->addElement('text', 'number', $this->dof->modlib('ig')->igs('number').':', 'size="2"');
        //$mform->setType('number', PARAM_INT);
        //$mform->addRule('number',$this->dof->modlib('ig')->igs('form_err_numeric'), 'numeric',null,'client');
        
        // устанавливаем переключатели, которые будут определять, какая дата будет выбрана:
        // относительная или абсолютная
        $objs = array();
        $begindatetext = '';
        if ( $this->begindate )
        {
            $begindatetext = ' <b>'.dof_userdate($this->begindate,'%Y-%m-%d').'</b>';
        }
        // радиокнопка, отвечающая за то, что будет выбрано задание относительной даты
        $objs[] = & $mform->createElement('radio', 'datetype', null, 
                    $this->dof->get_string('relative',$this->im_code(), $begindatetext), 'relative');
        if ( ! $this->is_relative_dataselector($this->linktype) )
        {// устанавливаем правило: запрещаем устанавливать абсолютную дату, если нельзя определить период
            // в котором находится контрольная точка
            // радиокнопка, отвечающая за то, что будет выбрано задание абсолютной даты
            $objs[] = & $mform->createElement('radio', 'datetype', null, $this->dof->get_string('absolute',$this->im_code()), 'absolute');
        }
        // добавляем радиокнопки в форму
        $mform->addElement('group', 'datetype_group', 
            $this->dof->get_string('date_type', $this->im_code()).':', $objs, '<br />', true);
        // устанавливаем правило: в зависимости от того, какой вариант radio-кнопки выбран - 
        // отключается либо форма относительной даты, либо форма абсолютной даты
        $mform->disabledIf('reldate_group', 'datetype_group[datetype]', 'eq', 'absolute');
        $mform->disabledIf('pinpoint_date', 'datetype_group[datetype]', 'eq', 'relative');
        
        // определяем, какой тип даты использовать по умолчанию: относительную или абсолютную
        if ( $this->is_relative_dataselector($this->linktype) )
        {// если нужна относительная дата - устанавливаем это значение по умолчанию
            $mform->setDefault('datetype_group', array('datetype' => 'relative'));
        }else
        {// в остальных случаях - устанавливаем абсолютную дату
            $mform->setDefault('datetype_group', array('datetype' => 'absolute'));
        }
        
        if ( ! $this->is_relative_dataselector($this->linktype) ) 
        {// создаем поле, отвечающее за крайний срок сдачи (абсолютный)
            // отображаем его только в том случае, если мы можем установить абсолютную дату
            $this->get_pinpoint_dateselector();
        }
        
        // создаем поле, отвечающее за крайний срок сдачи (относительный)
        $this->get_relative_dateselector();
        
        // крайний срок сдачи
        // создаем массив для будущих полей
        $objs = array();
        // Создаем элементы для ввода относительной даты обучения
        // количество недель
        $objs[] =& $mform->createElement('static', 'relddate_weeks_desc', null,
            $this->dof->get_string('weeks', $this->im_code()).':');
        $objs[] =& $mform->createElement('text', 'relddate_weeks', null, ' size="2" ');
        // дней
        $objs[] =& $mform->createElement('static', 'relddate_weeks_desc', null, 
            $this->dof->get_string('days', $this->im_code()).':');
        $objs[] =& $mform->createElement('text', 'relddate_days', null, ' size="2" ');
        // часов
        $objs[] =& $mform->createElement('static', 'relddate_weeks_desc',  null,
            $this->dof->get_string('hours', $this->im_code()).':');
        $objs[] =& $mform->createElement('text', 'relddate_hours', null, ' size="2" ');
        // добавляем элементы в форму
        $grp =& $mform->addElement('group', 'relddate_group', 
            $this->dof->get_string('reldldate',$this->im_code()).':', $objs, null, true);
       
        // устанавливаем по умолчанию дни и часы
        if ( $this->plan->id )
        {
            // для относительной даты начала
            // недели 
            $weeks = floor($this->plan->reldldate / (3600 * 24 * 7));
            // дни
            $days  = floor(($this->plan->reldldate - $weeks * 3600 * 24 * 7) / (3600 * 24));
            //часы
            $hours = floor(($this->plan->reldldate - $weeks * 3600 * 24 * 7 - $days * 3600 * 24) / (3600));
            
            $mform->setDefault('relddate_group', 
                array('relddate_weeks' => $weeks,
                      'relddate_days'  => $days,
                      'relddate_hours' => $hours));
        }
        
        $mform->setType('relddate_group[relddate_weeks]', PARAM_INT);
        $mform->setType('relddate_group[relddate_days]',  PARAM_INT);
        $mform->setType('relddate_group[relddate_hours]', PARAM_INT);
        
        // шкала 
        $mform->addElement('text', 'scale', $this->dof->get_string('scale',$this->im_code()).':', 'size="20"');
        $mform->setType('scale', PARAM_TEXT);
        
        // тип отображения
        $mform->addElement('selectyesno', 'directmap', $this->dof->get_string('directmap',$this->im_code()).':');
        $mform->setType('directmap', PARAM_INT);
        // домашнее задание
        // @todo отключить домашнее задание для итоговой аттестации
        $mform->addElement('textarea', 'homework', $this->dof->get_string('homework',$this->im_code()).' :<br>'.
                            $this->dof->get_string('homework_size',$this->im_code()), array('cols'=>68, 'rows'=>10));
        $mform->setType('homework', PARAM_TEXT);

        // часов на домашнее задание (создаем группу элементов)
        $homeworkgroup = array();
        // настройки для select-элемента "часы"
        $hoursoptions    = array();
        $hoursoptions['availableunits']   = array(3600 => $this->dof->modlib('ig')->igs('hours'));
        $homeworkgroup[] = &$mform->createElement('dof_duration', 'hours', null, $hoursoptions);
        // настройки для select-элемента "минуты"
        $minutesoptions  = array();
        $minutesoptions['availableunits'] = array(60 => $this->dof->modlib('ig')->igs('minutes'));
        $homeworkgroup[] = &$mform->createElement('dof_duration', 'minutes', null, $minutesoptions);
        // добавляем группу элементов "время на домашнее задание"
        $mform->addGroup($homeworkgroup, 'homeworkhoursgroup', $this->dof->get_string('homeworkhours', $this->im_code()), '&nbsp;');
        // синхронизация и курс в Moddle
        // @todo сделать активными, когда потребуется
        //$mform->addElement('static', 'typesync', $this->dof->get_string('typesync',$this->im_code()).':', $this->dof->get_string('in_development','programmitems'));
        //$mform->addElement('static', 'mdlinstance', $this->dof->get_string('mdlinstance',$this->im_code()).':', $this->dof->get_string('in_development','programmitems'));
        
        // поле "примечания"
        $mform->addElement('textarea', 'note',  $this->dof->get_string('note',$this->im_code()), array('cols'=>68, 'rows'=>10));
        $mform->setType('note', PARAM_TEXT);
        
        // кнопоки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->get_string('to_save',$this->im_code()));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Получить элемент dateselector для выбора даты внутри периода
     * (Для контрольных точек относящихся к cpassed и ages)
     * @return null
     */
    protected function get_pinpoint_dateselector()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( $age = $this->get_current_age($this->linktype, $this->linkid) )
        {// если мы можем точно определить период в котором работаем
            $startyear = dof_userdate($age->begindate,'%Y');
            $stopyear  = dof_userdate($age->enddate,'%Y');
        }else
        {// в остальных случаях - ставим только текущий год
            $startyear = dof_userdate(time(),'%Y');
            $stopyear  = dof_userdate(time(),'%Y');
        }
        $options = array();// объявляем массив для установки значений по умолчанию
        $options['startyear'] = $startyear; // устанавливаем год, с которого начинать вывод списка
        $options['stopyear']  = $stopyear; // устанавливаем год, которым заканчивается список
        $options['optional']  = false; // убираем галочку, делающую возможным отключение этого поля
        
        // абсолютная дата начала
        $mform->addElement('date_selector', 'pinpoint_date', 
                    $this->dof->get_string('pinpoint_date', $this->im_code()).':', $options);
        if ( isset($this->plan->id) AND $this->plan->id )
        {// если мы редактируем существующую контрольную точку - 
            $mform->setDefault('pinpoint_date', $this->plan->reldate + $this->begindate);
        }else
        {// если известен период - то установим по умолчанию дату его начала
            $mform->setDefault('pinpoint_date', time());
        }
    }
    
    /** Получить относительную дату начала занятия 
     * (для контрольных точек  относящихся к programm и programmitem)
     * 
     * @return null
     */
    protected function get_relative_dateselector()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // дата проведения
        // создаем массив для будущих полей
        $objs = array();
        // Создаем элементы для ввода относительной даты обучения
        // количество недель    
        $objs[] =& $mform->createElement('static', 'reldate_weeks_desc', null,
            $this->dof->get_string('weeks', $this->im_code()).':');
        $objs[] =& $mform->createElement('text', 'reldate_weeks', null, ' size="2" ');
        
        // дней
        $objs[] =& $mform->createElement('static', 'reldate_weeks_desc', null, 
            $this->dof->get_string('days', $this->im_code()).':');
        $objs[] =& $mform->createElement('text', 'reldate_days', null, ' size="2" ');
        // часов
        $objs[] =& $mform->createElement('static', 'reldate_weeks_desc',  null,
            $this->dof->get_string('hours', $this->im_code()).':');
        $objs[] =& $mform->createElement('text', 'reldate_hours', null, ' size="2" ');
        // добавляем элементы в форму
        $grp =& $mform->addElement('group', 'reldate_group', 
            $this->dof->get_string('reldate',$this->im_code()).':', $objs, null, true);
        // устанавливаем количество недель, дней и часов
        if ( $this->plan->id )
        {
            $reldate = $this->plan->reldate;
        }else
        {
            $reldate = $this->get_default_week_num(0);
        }
        // недели 
        $weeks = floor($reldate / (3600 * 24 * 7));
        // дни
        $days  = floor(($reldate - $weeks * 3600 * 24 * 7) / (3600 * 24));
        //часы
        $hours = floor(($reldate - $weeks * 3600 * 24 * 7 - $days * 3600 * 24) / (3600));
        
        $mform->setDefault('reldate_group', 
            array('reldate_weeks' => $weeks,
                  'reldate_days'  => $days,
                  'reldate_hours' => $hours));
        
        $mform->setType('reldate_group[reldate_weeks]', PARAM_INT);
        $mform->setType('reldate_group[reldate_days]',  PARAM_INT);
        $mform->setType('reldate_group[reldate_hours]', PARAM_INT);
    }
    
    /** Определить, какой тип даты выбирать - относительная или абсолютная
     * 
     * @param string $linktype - тип связи контрольной точки с объектом
     * @return bool
     *             true - использовать относительную дату (от начала программы или предмета)
     *             false - использовать абсолютную дату (для периода или подписки на предмет)
     */
    protected function is_relative_dataselector($linktype)
    {
        switch ( $linktype )
        {
            case 'ages'          : return false;
            case 'cstreams'      : return false;
            case 'programmitems' : return true;
            case 'programms'     : return true;
            case 'plan'          : return false;
            // по умолчанию возвращаем относительную дату
            default : return true;
        }
    }
    
    /** Получить список разделов тематического планирования
     * 
     * @return array
     * @param string $linktype
     * @param int $linkid
     */
    protected function get_plan_sections_list($linktype, $linkid)
    {
        // получаем список разделов тематического планирования для выбранного 
        // предмета, программы, потока или периода
        $sections = $this->dof->storage('plansections')->get_theme_plan($linktype, $linkid, array('active'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'plansections', 'code'=>'use'));
        $sections = $this->dof_get_acl_filtered_list($sections, $permissions);
        
        return $sections;
    }
    
    /** Подстановка данных по умолчанию
     * 
     * @return null
     */
    public function definition_after_data()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // устанавливаем значение часов/минут на домашнее задание по умолчанию
        $hwhours = $mform->getElementValue('homeworkhours');
        $hours   = floor($hwhours / 3600);
        $minutes = floor(($hwhours - $hours * 3600) / 60);
        $mform->setDefault('homeworkhoursgroup', 
                            array('hours'   => $hours,
                                  'minutes' => $minutes));
        // устанавливаем по умолчанию поле directmap в нужное положение
        if ( empty($this->plan->id) )
        {// если создаеи КТ
            $linktype = $mform->getElementValue('linktype');
            if ( $linktype == 'cstreams' )
            {// темы привязанные к потоку должны по умолчанию отображаться в журнале
                $mform->setDefault('directmap', 1);
            }
        }
    }
    
    /** Проверка данных на стороне сервера
     * 
     * @return array
     * @param array $data[optional] - массив с данными из формы
     * @param array $files[optional] - массив отправленнных в форму файлов (если они есть)
     */
    public function validation($data,$files)
    {
        $errors = array();
        
        // проверка назнания темы
        if ( ! $data['parentid1'] AND ! $data['parentid2'] AND ! $data['parentid3'] )
        {// не указана ни одна из родительских тем
            if ( ! trim($data['name']) )
            {// не указано название темы, и не указана ни одна из родительских тем - это ошибка 
                $errors['name'] = $this->dof->modlib('ig')->igs('form_err_required');
            }
        }
        if ( ($data['parentid1'] == $data['parentid2'] OR $data['parentid1'] == $data['parentid3']) 
                  AND $data['parentid1'] != '0') 
        {// проверка на совпадение родительских тем 
            $errors['parentid1'] = $this->dof->get_string('field_has', $this->im_code());    
        }elseif ( ($data['parentid2'] == $data['parentid3']) AND $data['parentid2'] != '0' )
        {
            $errors['parentid2'] = $this->dof->get_string('field_has', $this->im_code());             
        }
        /*// проверка правильности даты проведения
        if ( $data['datetype_group']['datetype'] == 'absolute' )
        {// выбрана абсолютная дата проведения
            if ( $age = $this->get_current_age($data['linktype'], $data['linkid']) )
            {// мы можем точно определить период, в котором работаем
                if ( $data['pinpoint_date'] < $age->begindate )
                {// абсолютная дата начала меньше даты начала периода
                    $agebegin = date('Y-m-d', $age->begindate);
                    $errors['pinpoint_date'] = 
                        $this->dof->get_string('err_too_small_absdate', $this->im_code(), $agebegin);
                }
                if ( $data['pinpoint_date'] > $age->enddate )
                {// абсолютная дата окончания больше даты окончания периода
                    $ageend = date('Y-m-d', $age->enddate);
                    $errors['pinpoint_date'] = 
                        $this->dof->get_string('err_too_large_absdate', $this->im_code(), $ageend);
                }
            }
        }else
        {// выбрана относительная дата проведения
            $reldatetime = 
            $data['reldate_group']['reldate_weeks'] * 7 * 24 * 3600 +
            $data['reldate_group']['reldate_days']  * 24 * 3600 + 
            $data['reldate_group']['reldate_hours'] * 3600;
            if ( ! $reldatetime )
            {// не указана относительная дата начала
                $errors['reldate_group'] = $this->dof->modlib('ig')->igs('form_err_required');
            }
        }
        
        // проверка правильности крайнего срока сдачи
        // @todo проверить, что дата сдачи умещается внутри периода
        $deadline = $data['relddate_group']['relddate_weeks'] * 7 * 24 * 3600 +
        $data['relddate_group']['relddate_days'] * 24 * 3600 + 
        $data['relddate_group']['relddate_hours'] * 3600;
        if ( $deadline AND $deadline < $reldatetime )
        {// если указан крайний срок сдачи, и он раньше даты проведения урока - это ошибка
            $errors['relddate_group'] = $this->dof->get_string('err_wrong_deadline', $this->im_code());
        }*/
        
        if ( $age = $this->get_current_age($data['linktype'], $data['linkid']) )
        {// мы можем точно определить период, в котором работаем
            
            // проверка правильности даты проведения
            if ( $data['datetype_group']['datetype'] == 'absolute' )
            {// выбрана абсолютная дата проведения
                if ( $data['pinpoint_date'] < $age->begindate )
                {// абсолютная дата начала меньше даты начала периода
                    $agebegin = dof_userdate($age->begindate,'%Y-%m-%d');
                    $errors['pinpoint_date'] = 
                    $this->dof->get_string('err_too_small_absdate', $this->im_code(), $agebegin);
                }
                if ( $data['pinpoint_date'] > $age->enddate )
                {// абсолютная дата окончания больше даты окончания периода
                    $ageend = dof_userdate($age->enddate,'%Y-%m-%d');
                    $errors['pinpoint_date'] = 
                    $this->dof->get_string('err_too_large_absdate', $this->im_code(), $ageend);
                }
                $reldatetime = $data['pinpoint_date'];
            }else
            {// выбрана относительная дата проведения
                $reldatetime = 
                $data['reldate_group']['reldate_weeks'] * 7 * 24 * 3600 +
                $data['reldate_group']['reldate_days']  * 24 * 3600 + 
                $data['reldate_group']['reldate_hours'] * 3600;
                
                if ( ! $reldatetime )
                {// не указана относительная дата начала
                    $errors['reldate_group'] = $this->dof->modlib('ig')->igs('form_err_required');
                }elseif ( $age->begindate + $reldatetime > $age->enddate )
                {// относительная дата начала больше даты окончания периода
                    $errors['reldate_group'] = $this->dof->get_string('begindate_past_enddate', $this->im_code());
                }
            }
            
            // проверка правильности крайнего срока сдачи
            // @todo проверить, что дата сдачи умещается внутри периода
            $deadline = $data['relddate_group']['relddate_weeks'] * 7 * 24 * 3600 +
            $data['relddate_group']['relddate_days'] * 24 * 3600 + 
            $data['relddate_group']['relddate_hours'] * 3600;
            if ( $deadline )
            {// если указан крайний срок сдачи
                if ( $deadline < $reldatetime )
                {// если крайний срок сдачи раньше даты начала периода - это ошибка
                    $errors['relddate_group'] = $this->dof->get_string('err_wrong_deadline', $this->im_code());
                }elseif ( $age->begindate + $deadline > $age->enddate )
                {// если крайний срок сдачи позже даты окончания периода - это ошибка
                    $errors['relddate_group'] = $this->dof->get_string('enddate_before_begindate', $this->im_code());
                }
            }
        }
        
        // проверка типа темы
        if ( ! trim($data['type']) )
        {// не указан тип темы
            $errors['type'] = $this->dof->modlib('ig')->igs('form_err_required');
        }
        
        // проверка поля "домашнее задание" на длину
        if ( mb_strlen(trim($data['homework']),'utf-8') > 700 )
        {// слишком длинное домашнее задание 
            $errors['homework'] = $this->dof->get_string('err_too_long_homework','plans');
        }
        
        // @todo возможно следует разрешать назначение домашнего задания только если задано время на него 
        //if ( ! trim($data['homeworkhours']) OR ! floatval($data['homeworkhours']) )
        //{// не указаны часы на домашнее задание
        //    $errors['homeworkhours'] = $this->dof->get_string('err_no_homework_hours','plans');
        //}
        if ( ! empty($data['scale']) )
        {
            $result = $this->scale_is_valid($data['scale']);
            if ( ! empty($result) )
            {// если шкала указано неверно - то запишем возникшие ошибки в общий массив
                $errors['scale'] = $result ;
            }    
         
        }
        
        return $errors;
    }

    /** проверяет правильность задания шкалы оценок
     * 
     * @return array массив ошибок, для функции validation()
     * @param string $scale - шкала оценок
     * 
     * @todo предусмотреть случай с отрицательными числами
     * @todo добавить проверку того, действительно ли по возрастанию расположены числовые оценки
     */
    protected function scale_is_valid($scale)
    {
        if ( ! trim($scale) )
        {// шкала не задана - это ошибка
            return $this->dof->get_string('err_scale', 'plans');
        }
        // разбиваем шкалу на отдельные части
        $scale = explode(',', $scale);
        foreach ( $scale as $element )
        {// начинаем проверять переданную шкалу
            if ( ! trim($element) AND trim($element) != '0' )
            {// пустые элементы в шкале неодпустимы
                return $this->dof->get_string('err_scale_null_element', 'plans');
            }
            if ( preg_match('/-/', $element) )
            {// это диапазон
                $boundaries = explode('-', $element);
                if ( count($boundaries) != 2 )
                {// диапазон задан неправильно
                    return $this->dof->get_string('err_scale', 'plans');
                }
                // определим границы максимальных и минимальных значений
                $min = $boundaries[0];
                $max = $boundaries[1];
                if ( ($min == '' AND ! is_numeric($max)) OR (! is_numeric($min) AND $max == '') OR 
                       ($min != '' AND $max != '' AND (! is_numeric($max) OR ! is_numeric($min))) ) 
                {// диапазоны могут быть только числовыми
                    return $this->dof->get_string('err_scale_not_number_diapason', 'plans');
                }
                if ( $min == $max )
                {// максимальная оценка в диапазоне равна минимальной: 
                    // диапазон задан неверно
                    return $this->dof->get_string('err_scale_max_min_must_be_different', 'plans');
                }
            }
        }
        // если ошибки есть - то возвращаем массив, в котором указано, что именно произошло
        // если нет - то просто пустой массив
        return array();
    }
    
    /** Возвращает список точек
     * 
     * @param int $pointid - id контрольной точки которую надо
     * @param string $linktype - тип связи контрольной точки с объектом
     * @param int $linkid - id объекта с которым связана контрольная точка
     * @return array
     */
    private function get_list_point($pointid, $linktype, $linkid)
    {
        $points = array();
        $points['0'] = $this->dof->get_string('none','plans');
        // получим список всех элементов тематического планирования
        $plans = $this->dof->storage('plans')->
            get_theme_plan($linktype, $linkid, 
                        array('active', 'fixed'), true, null, true);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'plans', 'code'=>'use'));
        $plans = $this->dof_get_acl_filtered_list($plans, $permissions);
        
        if ( ! $plans )
        {// нет ни одного элемента - возвращаем массив с единственным элементом "выбрать"
           return $points; 
        }
        // для каждого плана сформируем массив id плана=>имя плана
        foreach ($plans as $plan)
        {
            if ( $plan->id <> $pointid )
            {// забиваем все, кроме той, которой не надо
                $points[$plan->id] = $plan->name;
            }
        }
        return $points;
    }
    /** Возвращает строку заголовка формы
     * 
     * @param int $id[optional] - id редактируемой в данной момент записи
     * @return string
     */
    private function get_form_title($id=null)
    {
        if ( ! $id )
        {//заголовок создания формы
            return $this->dof->get_string('newpoint','plans');
        }else 
        {//заголовок редактирования формы
            return $this->dof->get_string('editpoint','plans');
        }
    }
    
    /** Получить текущий учебный период, или false если определить период
     * не представляется возможным
     * @todo возможно нам следует формировать один общий объект, вне 
     * зависмости от того что мы получили: период или поток, либо убрать функции
     * get_linktype_age_data и get_linktype_cstream_data
     * 
     * @return object|bool
     * @param string $linktype - тип связи контрольной точки с объектом
     * @param int $linkid - id объекта с которым связана контрольная точка
     */
    protected function get_current_age($linktype, $linkid)
    {
        if ( $this->is_relative_dataselector($linktype) )
        {// это предмет либо программа - невозможно установить точную дату начала периода
            return false;
        }
        if ( $linktype == 'ages' )
        {// ищем по привязке к периоду
            return $this->get_linktype_age_data($linkid);
        }elseif( $linktype == 'cstreams' )
        {// ищем по привязке к потоку
            return $this->get_linktype_cstream_data($linkid);
        }elseif ( $linktype == 'plan' )
        {// ищем по привязке к потоку через индивидуальное планирование
            return $this->get_linktype_plan_data($linkid);
        }
    }
    
    /** Получить период к которому относятся контрольная точка
     * 
     * @return bool|object - объект из таблицы ages или false если ничего не нашлось
     * @param int $id - id периода в таблице ages
     */
    protected function get_linktype_age_data($id)
    {
        return $this->dof->storage('ages')->get($id);
    }
    
    /** Получить период к оторому относится контрольная точка внутри потока
     * 
     * @return bool|object - объект из таблицы cstreams или false если ничего не нашлось
     * @param int $id - id учебного потока в таблице cstreams
     */
    protected function get_linktype_cstream_data($id)
    {
        return $this->dof->storage('cstreams')->get($id);
    }
    
    /** Получить период к оторому относится контрольная точка внутри потока
     * при составлении учебно-тематического планирования
     * 
     * @return  bool|object - объект из таблицы cstreams или false если ничего не нашлось
     * @param int $id - id учебного потока в таблице cstreams
     */
    protected function get_linktype_plan_data($id)
    {
        return $this->dof->storage('cstreams')->get($id);
    }
    
    /** Получить номер последней недели
     * @todo сделать эту возможность настройкой
     * @param int $id - id существующей КТ (для того чтобы определить - она редактируется или создается) 
     * 
     * @return bool|int - номер недели по умолчанию, с учетом предыдущих КТ (только для создаваемых тем)
     */
    protected function get_default_week_num($id)
    {
        if ( $id )
        {// КТ редактируется - ничего не делаем
            return false;
        }
        if ( ! $this->linktype OR ! $this->linkid  )
        {// невозможно определить, к какому предмету принадлежит КТ
            return false;
        }
        if ( ! in_array($this->linktype, array('programmitems', 'cstreams', 'plan')) )
        {// автоматическое вычисление номера недели работает только для предмета, потока или 
            // индивидуального планирования
            return false;
        }
        
        if ( $this->is_relative_dataselector($this->linktype) )
        {
            // @todo заменить эту функцию на sql код, получающий запись по максимальному значению reldate
            if ( ! $plans = $this->dof->storage('plans')->get_records(array('linktype'=>$this->linktype, 
                                'linkid'=>$this->linkid), 'reldate DESC') )
            {// нет ни одной темы для этого предмета или потока - ничего не возвращаем
                return false;
            }
            
            $lastplan = current($plans);
            
            return $lastplan->reldate + 7*24*3600;
        }else
        {// @todo для абсолютной даты тоже автоматически подставлять неделю
            return false;
        }
    }
}

/** Класс формы для поиска класса
 * 
 */
class dof_im_plans_search_form extends dof_modlib_widgets_form
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
        $mform->addElement('header','formtitle', $this->dof->get_string('search','plans'));
        // поле "название или код"
        $mform->addElement('text', 'nameorcode', $this->dof->get_string('nameorcode','plans').':', 'size="20"');
        $mform->setType('nameorcode', PARAM_TEXT);
        // кнопка "поиск"
        $this->add_action_buttons(false, $this->dof->get_string('to_find','plans'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
}


/** Класс формы для поиска класса
 * 
 */
class dof_im_plans_edit_themeplan_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    private $section;
    /**
     * @var dof_control
     */
    protected $dof;
    
    function definition()
    {
        $this->section = $this->_customdata->section;
        $this->dof  = $this->_customdata->dof;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
                
        $mform->addElement('hidden','id', $this->section->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden','linktype', $this->section->linktype);
        $mform->setType('linktype', PARAM_ALPHA);
        $mform->addElement('hidden','linktid', $this->section->linkid);
        $mform->setType('linktid', PARAM_INT);
        //создаем заголовок формы
        if ( ! $this->section->id )
        {//заголовок создания формы
            $mform->addElement('header','formtitle', $this->dof->get_string('newthemeplan','plans'));
        }else 
        {//заголовок редактирования формы
            $mform->addElement('header','formtitle', $this->dof->get_string('editthemeplan','plans'));
        }
        // поле "имя"
        $mform->addElement('textarea', 'name', $this->dof->get_string('namethemeplan','plans').':', array('cols'=>35, 'rows'=>5));
        $mform->setType('textarea', PARAM_TEXT);
        // кнопка "сохранить"
        $this->add_action_buttons(true, $this->dof->modlib('ig')->igs('save'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
}

/** Класс формы для редактирования пояснительной записки
 * 
 */
class dof_im_plans_editplanatory_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    private $linktype;
    private $linkid;
    
    function definition()
    {
        $this->dof      = $this->_customdata->dof;
        $this->linkid   = $this->_customdata->linkid;
        $this->linktype = $this->_customdata->linktype;
        // определим значание пояcнит записки
        if ( ! $explanat = $this->dof->storage($this->linktype)->get($this->linkid)->explanatory )
        {
            $explanat = '';
        }
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('editplanatory','plans'));
        // Пояснительная записка
        $mform->addElement('htmleditor', 'textname', '', array('cols'=>50, 'rows'=>30));
        $mform->setType('textname', PARAM_CLEANHTML);
        $mform->setDefault('textname', $explanat);
        // кнопки
        $this->add_action_buttons();
    }
}

?>