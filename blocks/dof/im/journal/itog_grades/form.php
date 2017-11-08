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

// Подключаем библиотеки
require_once('lib.php');
// подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

class dof_im_journal_edit_form extends dof_modlib_widgets_form
{
    private $cstreamid;
    /**
     * @var dof_control
     */
    protected $dof;
    
    function definition()
    {// делаем глобальные переменные видимыми
        $this->cstreamid = $this->_customdata->cstreamid;
        $this->dof    = $this->_customdata->dof;
        $report = false;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $mform->addElement('hidden','id', $this->cstreamid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden','reoffset', $this->_customdata->reoffset);
        $mform->setType('reoffset', PARAM_INT);
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden','teacherid', $this->_customdata->teacherid);
        $mform->setType('teacherid', PARAM_INT);
        $mform->addElement('hidden','programmitemid', $this->_customdata->programmitemid);
        $mform->setType('programmitemid', PARAM_INT);
        $mform->addElement('hidden','ageid', $this->_customdata->ageid);
        $mform->setType('ageid', PARAM_INT);
        $mform->addElement('hidden','scale', $this->_customdata->scale);
        $mform->setType('scale', PARAM_TEXT);
        $mform->addElement('hidden','mingrade', $this->_customdata->mingrade);
        $mform->setType('mingrade', PARAM_TEXT);
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        // кнопка смены статуса - показывается только если его можно поменять
        if ( $this->_customdata->cstreamstatus == "active" AND 
             ($this->dof->storage('cpassed')->is_access('edit:grade/auto',$this->cstreamid) OR 
              $this->dof->storage('cpassed')->is_access('edit:grade',$this->cstreamid)) )
        {// автоматическое закрытие ведомости
            $mform->addElement('submit', 'auto', $this->dof->get_string('auto_itog_grades','journal'));
        }
        // создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('itog_grades','journal'));
        // выберем подписки на дисциплину текущего потока, те, которые нужны
        $cpassed = $this->dof->storage('cpassed')->get_records(array('cstreamid'=>$this->cstreamid,
                               'status'=>array_keys($this->dof->workflow('cpassed')->get_register_statuses()),
                               'repeatid'=>array(null)));
        if ( ! $cpassed )
        {// если подписки не найдены, значит их нет
            $mform->addElement('html','<p align="center">'.
                    $this->dof->get_string('not_found_cpassed_on_cstream','journal').'</p>');
        }else
        {// подписки есть
            // определим, в 1 раз или нет сдается ведомость
            $flag = false;
            if ( $this->_customdata->reoffset != true )
            {// не пересдача 
                foreach ( $cpassed as $cpass )
                {
                    // узнаем последнего наследника для оценки
                    $successorid = $this->dof->storage('cpassed')->get_last_successor($cpass->id);
                    $successor   = $this->dof->storage('cpassed')->get($successorid);
                    if ( $successor->orderid )
                    { // нашли ордер - переустанавливаем флаг и дальше нет смысла перебор делать              
                        $flag = true;
                        break;
                    }
                }
            }
            foreach ( $cpassed as $cpass )
            {// для каждой создадим поле
                $group = array();
                $disabled = '';
                if ( $cpass->status == 'suspend' OR $cpass->status == 'reoffset' )
                {// приостановленные и перезачтенные редактировать нельзя
                    $disabled = 'disabled';
                }
                if ( ! $this->dof->storage('cpassed')->is_access('edit:grade/own',$this->cstreamid) AND 
                     ! $this->dof->storage('cpassed')->is_access('edit:grade',$this->cstreamid) )
                {// ведомость заполнялась автоматически
                    $disabled = 'disabled';
                }
                $fio = '';
                if ( isset($cpass->studentid) )
                {// укажем имя студента
                    $fio = $this->dof->storage('persons')->get_fullname($cpass->studentid);
                }
                // узнаем последнего наследника для оценки
                $successorid = $this->dof->storage('cpassed')->get_last_successor($cpass->id);
                $successor   = $this->dof->storage('cpassed')->get($successorid);
                
                // создадим поле
                
                if ( ((($cpass->status != 'active') AND ($this->_customdata->reoffset != true)) 
                       OR ( ! $this->dof->storage('cpassed')->is_access('edit:grade/own',$this->cstreamid) AND
                            ! $this->dof->storage('cpassed')->is_access('edit:grade',$this->cstreamid))) OR  $flag )
                {// если оценки уже были выставлены - то покажем ведомость
                    if ( ! $successor->grade )
                    {
                        $successor->grade =  $this->dof->get_string('without_grade','journal');
                    }
                    $gradeelements[] = $mform->createElement('static', 'grade['.$successor->id.']', $fio.':', 
                             $this->dof->get_string('grade','journal').': '.$successor->grade, $disabled);
                }else
                {
                    $gradeelements[] = $mform->createElement('select', 'grade['.$successor->id.']', $fio.':', 
                                      $this->get_itog_grades_scale($successor->grade), $disabled);
                }
                if ( $successor->orderid )
                {// ссылка на скачивание ведомости в odt
                    $orderdate = dof_userdate($this->dof->storage('orders')->get_field($successor->orderid, 'exdate'),'%d.%m.%Y');
                    $downloadlink = '&nbsp;&nbsp;&nbsp;<a href="'.$this->dof->url_im('journal', 
                        '/itog_grades/export_order_itoggrades.php?id='.$successor->orderid.'&type=odf').'">'.
                        $this->dof->get_string('download_grades_in_odt', 'journal', $orderdate).'</a>';
                    $gradeelements[] = $mform->createElement('static', 'name['.$successor->id.']',  '', $downloadlink);
                    $report = true;
                }
                
                // добавляем группу с пояснением
                $grp =& $mform->addElement('group', 'group['.$successor->id.']', $fio, $gradeelements, null, false);
                // уничтожаем массив, чтобы избавиться от отработанных элементов
                unset($gradeelements);
            }
        }
        if ( ($report OR $this->_customdata->cstreamstatus != "active") AND $this->_customdata->reoffset == false 
              AND ($this->dof->storage('cpassed')->is_access('edit:grade/own',$this->cstreamid) OR 
                  $this->dof->storage('cpassed')->is_access('edit:grade',$this->cstreamid)) ) 
        {// иначе просто выводим предупреждение
            $downloadlink = '<a href="'.$this->dof->url_im('journal', 
                        '/itog_grades/edit.php?id='.$this->cstreamid.'&auto=0&reoffset=1&departmentid='.optional_param('departmentid', 0, PARAM_INT)).'">'.
                        $this->dof->get_string('itog_grades_reoffset', 'journal').'</a>';
            $mform->addElement('static', 'name', $downloadlink);
            $mform->closeHeaderBefore('name');
        }
        if ( ($this->_customdata->cstreamstatus == "active" OR $this->_customdata->reoffset == true) )
        {// если статус потока активный - показываем галочку завершения обучения
            // галочка подтверждения сохранения
            if ( ($this->dof->storage('cpassed')->is_access('edit:grade',$this->cstreamid) OR 
                  $this->dof->storage('cpassed')->is_access('edit:grade/own',$this->cstreamid)) AND ( $this->_customdata->reoffset == true OR ! $report ) )
            {// ведомость закрывается вручную - покажем подтверждение
                $mform->addElement('checkbox', 'confirm_save_grades', '','&nbsp;'.$this->dof->get_string('confirm_save_grades','journal'));
                $mform->setDefault('confirm_save_grades', false);
                $mform->setType('confirm_save_grades', PARAM_BOOL) ;
                $mform->closeHeaderBefore('confirm_save_grades');
            }
            if ( $this->_customdata->cstreamstatus == "active" )
            {// поток активный - покажем закрытие
                $mform->addElement('checkbox', 'complete_cstream', '','&nbsp;'.$this->dof->get_string('complete_cstream','journal'));
                $mform->setDefault('complete_cstream', true);
                $mform->setType('complete_cstream', PARAM_BOOL) ;
                if ( ($this->dof->storage('cpassed')->is_access('edit:grade/auto',$this->cstreamid) OR
                      $this->dof->storage('cpassed')->is_access('edit:grade',$this->cstreamid)) AND 
                      ! $this->dof->storage('cpassed')->is_access('edit:grade/own',$this->cstreamid) )
                {// закроем заголовки - если ведомость заполняется автоматически
                    $mform->closeHeaderBefore('complete_cstream');
                }
            }
            
            // кнопоки сохранить и отмена
            $this->add_action_buttons(true, $this->dof->get_string('to_save','journal'));
            
        }
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');        
        
    }
    
    /** Проверка данных на стороне сервера
     * 
     * @return 
     * @param object $data[optional] - массив с данными из формы
     * @param object $files[optional] - массив отправленнных в форму файлов (если они есть)
     * 
     * @todo добавить проверку пустых полей оценок на стороне сервера, если это потребуется
     */
    public function validation($data,$files)
    {
        $errors = array();
        // пока проверок нет
        return $errors;
    }
    /** Возвращает возможные итоговые оценки предмета
     * @param string $grade - старая оценка, если есть
     * @return array список возможных итоговых оценок
     */
    private function get_itog_grades_scale($grade = null)
    {
        $itog_grades = array();
        if ( is_null($grade) )
        {// если старой оценки нет - выведем надпись "Без оценки"
            $itog_grades[''] = $this->dof->get_string('without_grade','journal');
        }else
        {// если указана - выведем ее
             $itog_grades[''] = $this->dof->get_string('old_grade','journal',$grade);
        }
        if ( empty($this->_customdata->scale) )
        {
            return $itog_grades;
        }
        return $itog_grades + $this->dof->storage('plans')->get_grades_scale($this->_customdata->scale);
    }
}

?>