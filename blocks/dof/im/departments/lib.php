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
 * Интерфейс управления подразделениями. Базовые функции плагина
 * 
 * @package    im
 * @subpackage departments
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

// Добавление общих GET параметров плагина

// Добавление уровня навигации плагина

/** 
 * Класс, формы для смены принадлежности объектов к подразделению.
 * 
 */
class dof_im_departments_change_department
{
    /**
     * @var dof_control
     */
    protected $dof;
    private $storage;
    public $options;
    
    /** Конструктор
     * @param dof_control $dof - идентификатор действия, которое должно быть совершено
     * @param string $storage - справочник, из которого подключаем html-код
     * @param array $options - список значений для кода:
     * selectdescription - надпись к select-элементу
     * selectname - имя select-элемента
     * submitname - имя кнопки
     * submittitle - надпись к кнопке
     * prefix - префикс к форме на случай если нам надо будет вывести разные html-коды
     * titlename - название заголовка html-кода
     * listname - название списка выбранных id
     * @access public
     */
    public function __construct($dof, $storage, $options = array() )
    {
        $this->dof = $dof;
        if ( ! $this->dof->plugin_exists('storage', $storage) )
        {// справочника нет в БД
            $this->dof->print_error('error_exist_storage','',$storage,'modlib','ig'); 
        }
        $this->storage = $storage;
        $this->options = $options;
        // выставим значения по умолчанию если они пустые
        if ( empty($options['selectdescription']) )
        {// надпись к select-элементу
            $this->options['selectdescription'] = $this->dof->get_string('form:selectdescription','departments').': ';
        }
        if ( empty($options['selectname']) )
        {// имя select-элемента
            $this->options['selectname'] = 'departmentid';
        }
        if ( empty($options['submitname']) )
        {// имя кнопки
            $this->options['submitname'] = 'submit';
        }
        if ( empty($options['submittitle']) )
        {// надпись к кнопке
            $this->options['submittitle'] = $this->dof->modlib('ig')->igs('carry');
        }
        if ( empty($options['prefix']) )
        {// префикс к форме на случай если нам надо будет вывести разные формы
            $this->options['prefix'] = 'depform';
        }
        if ( empty($options['titlename']) )
        {// название заголовка html-кода
            $this->options['titlename'] = $this->dof->get_string('form:titlename','departments');
        }
        if ( empty($options['listname']) )
        {// название списка выбранных id
            $this->options['listname'] = 'idlist';
        }
    }
    
    /** Возвращает html-код выбора подразделения с кнопкой
     * @param string $check - при определенном значении появляються дополнительные объекты
     * 						  в форме - чексбоксы
     * @return string - html-код выбора подразделения с кнопкой
     */
    public function get_form($check='')
    {
        $html = '<div class="mform" >';
        // заголовок формы
        $html .= '<fieldset class="clearfix" id="'.$this->options['prefix'].'_title"><legend class="ftoggler">'.$this->options['titlename'].'</legend>' ;
        // скрытое поле - справочник
        $html .= '<input type="hidden" name="'.$this->options['prefix'].'_st" value="'.$this->storage.'"/>';
        // select-выбор
        $html .= '<div class="fitemtitle"><label for="id_leaddepid">'.$this->options['selectdescription'].'</label></div>';
        $html .= '<div class="felement fselect"><select  name="'.$this->options['prefix'].'_'.$this->options['selectname'].'" >'."\n";
        // получаем все подразделения
        if ( $variants = $this->dof->storage('departments')->departments_list_subordinated(null,0,null,true) )
        {// нашли
            foreach ($variants as $id => $name)
            {// перебираем все варианты и делаем их элементами формы
                $html .= '<option value="'.$id.'" >'.$name.'</option>'."\n";
            }
        }else
        {// элементов нет
            $html .= '<option value="'.$this->options['prefix'].'_'.$this->options['selectname'].'_0" >'.$this->dof->modlib('ig')->igs('form_err_none_element').'</option>'."\n";
        }
        $html .= '</select></div>'."\n";
        // дополнительные чекс-боксы
        switch ($check)
        {
            // для контрактов
            case 'contracr_person':
                $html .= '<div class="fitemtitle"> </div>';
                $html .= '<div class="felement"><span><input type="checkbox" name="prsbc" value="1" checked>';
                $html .= $this->dof->get_string('and_prsbcs','sel').'</span></div>';
                $html .= '<br>';
                $html .= '<div class="fitemtitle"> </div>';
                $html .= '<div class="felement"><span><input type="checkbox" name="person" value="1" checked>';  
                $html .= $this->dof->get_string('and_persons','sel').'</span></div>';
                break;  
            case 'contract_employees':
                $html .= '<div class="fitemtitle"> </div>';
                $html .= '<div class="felement"><span><input type="checkbox" name="appointment" value="1" checked>';
                $html .= $this->dof->get_string('and_appointment','employees').'</span></div>';
                $html .= '<br>';
                $html .= '<div class="fitemtitle"> </div>';
                $html .= '<div class="felement"><span><input type="checkbox" name="employees" value="1" checked>';       
                $html .= $this->dof->get_string('and_employees','employees').'</span></div>';         
                break;
                    
             default: break;       
        } 
        // кнопка
        $html .= '<div class="fitemtitle"><label for="id_leaddepid"></label></div>';
        $html .= '<div class="felement fsubmit"><input type="submit" name="'.$this->options['prefix'].'_'.$this->options['submitname'].'" value="'.$this->options['submittitle'].'"/></div></fieldset></div>';
        // возвращаем добро
        return $html;
    }

    /** Обрабатывает данные при нажатии кнопки
     * 
     * @todo улучшить алгоритм проверки ошибок: сначала проверять права на перенос всех объектов, 
     *       а только потом начинать их перенос 
     * 
     * @return mixed - bool false, если не получилось извлечь данные из таблицы,
     *                 array массив ошибок или пустой, если все записи удалось обновить. 
     */
    public function execute_form()
    {
        $ids = array();
        $departmentid = optional_param($this->options['prefix'].'_'.$this->options['selectname'], null, PARAM_INTEGER);
        $prsbc        = optional_param('prsbc',       0, PARAM_INTEGER);
        $person       = optional_param('person',      0, PARAM_INTEGER);
        $employees    = optional_param('employees',   0, PARAM_INTEGER); 
        $appointment  = optional_param('appointment', 0, PARAM_INTEGER);
         
        if( ! ( $ids = optional_param_array($this->options['prefix'].'_'.$this->options['listname'], null, PARAM_RAW) ) OR  ! is_array($ids) )
        {// Не удалось получить список перемещаемых записей
            return 1;
        }
        $erorrs = array();
        foreach($ids as $id)
        {
            if ( ! $this->dof->storage('departments')->is_exists($departmentid) )
            {// подразделения не существует
                $erorrs[0] =  $this->dof->get_string('notfound', 'departments', $departmentid);
            }elseif( ! $record = $this->dof->storage($this->storage)->get($id) )
            {// Не удалось найти запись
                $erorrs[$record->id] =  $this->dof->get_string('notfound_contracts', 'departments', $id);
            }elseif( ! $this->can_move_object_between_departments($id, $departmentid) )
            {//Если нет права перемещать между подразделениями
                $erorrs[$record->id] =  $this->dof->get_string('form:error:unable_to_transfer_between_departments', 
                                                'departments', array('con'=>$id,'dep'=>$departmentid));
            }else
            {
                $obj = new stdClass();
                $obj->id = $record->id;
                $obj->departmentid = $departmentid;
                if( ! $this->dof->storage($this->storage)->update($obj) )
                {//Если не удалось обновить запись
                    $erorrs[$record->id] = $this->dof->modlib('ig')->igs('record_update_failure');
                }
                // перенесем и персон ЕСЛИ стоит галочка
                if ( $person )
                {
                    // учашийся
                    if ( $personid = $this->dof->storage($this->storage)->get_field($record->id, 'studentid') )
                    {
                        $obj->id = $personid;
                        if( ! $this->dof->storage('persons')->update($obj) )
                        {//Если не удалось обновить запись персоны
                            $erorrs['per-'.$personid] = $this->dof->get_string('record_update_failure_pr','sel');
                        }
                    }
                    // законный представитель
                    if ( $clientid = $this->dof->storage($this->storage)->get_field($record->id, 'clientid') )
                    {
                        $obj->id = $clientid;
                        if( ! $this->dof->storage('persons')->update($obj) )
                        {//Если не удалось обновить запись персоны
                            $erorrs['per-'.$clientid] = $this->dof->get_string('record_update_failure_pr','sel');
                        }                        
                    }
                }
                
                if ( $prsbc )
                {// подписки на программу переносим ЕСЛИ стоит галочка
                    $errors = array_merge($erorrs, $this->move_programmsbcs($departmentid, $record->id));
                }
                
                if ( $employees )
                {// cотрудника переносим ЕСЛИ стоит галочка
                    $errors = array_merge($erorrs, $this->move_employee($departmentid, $record->id));
                }
                
                if ( $appointment )
                {// Переместить вместе с должностными пересечениями
                    $errors = array_merge($erorrs, $this->move_appointments($departmentid, $record->id));
                }                
            }
        }
        return $erorrs;
    }
    
    /** Проверить право перемещения объекта из одного подразделения в другое.
     * 
     * @todo выводить ошибку о том, что именно не получилось: удалить из старого
     *       подразделения или записать в новое
     * 
     * @param int $id - id объекта для которого проверяется право переноса
     * @param int $newdepid - id подразделения (в таблице departments) в которое переносится объект
     * @param string $storage[optional] - объект какого хранилища переносится
     * @param int $olddepid - id подразделения (в таблице departments) из которого переносится объект
     *                        (передаем, чтобы сэкономить 1 обращение к базе)
     * 
     * @return null
     */
    protected function can_move_object_between_departments($id, $newdepid, $storage=null, $olddepid=null)
    {
        if ( is_null($storage) )
        {
            $storage = $this->storage;
        }
        if ( is_null($olddepid) )
        {// получаем старое подразделение - если его не передали
            $olddepid = (int)$this->dof->storage($this->storage)->get_field($id, 'departmentid');
        }
        
        if ( $this->dof->storage($this->storage)->is_access('create', NULL, NULL, $newdepid) AND
             $this->dof->storage($this->storage)->is_access('delete', $id,  NULL, $olddepid) )
        {// пользователь одновременно имеет право удалять объект 
            // из старого подразделения и добавлять его в новое
            return true;
        }
        return false;
    }
    
    /** Можно ли переместить множество дочерних объектов из одного подразделения в другое
     * Вызывается перед тем, как переместить подписки или договоры вместе с персоной
     * 
     * @todo сейчас невозможно определить какие именно подписки нельзя переместить.
     *       В будущем нужно будет добавить обработку этих ошибок
     * 
     * @param array  $objects - массив объектов, которые мы собираемся переместить
     * @param int    $newdepid - Новое подразделение, в которое перемещаются объекты
     * @param string $storage - хранилище, в котором находятся перемещаемые объекты
     * 
     * @return bool
     */
    protected function can_move_child_objects_between_departments($objects, $newdepid, $storage)
    {
        $result = true;
        foreach ( $objects as $id=>$object )
        {
            if ( ! $this->can_move_object_between_departments($id, $newdepid, $storage, $object->departmentid) )
            {
                return false;
            }
        }
    }
    
    /** Переместить все назначения на должность вместе с контрактом на обучение
     * (используется если выбраны дополнительные опции в форме переноса)
     * 
     * @param int $newdepid - подразделение (в таблице departments) в которое переносятся объекты
     * @param int $recordid - id главной записи, вместе с которой переносятся остальные объекты
     *                        (зависит от того какие объекты переносит форма)
     * 
     * @return array 
     */
    protected function move_appointments($newdepid, $recordid)
    {
        $errors = array();
        if ( $appointments = $this->dof->storage('appointments')->get_records(array('eagreementid' => $recordid)) )
        {
            foreach ( $appointments as $appoint  )
            {
                if ( ! $this->can_move_object_between_departments($appoint->id, $newdepid, 'appointments', $appoint->departmentid) )
                {// нет прав для перемещения подписки
                    $message = $this->dof->get_string('form:error:unable_to_transfer_between_departments', 'departments');
                    $message .= '<a href="'.$this->dof->url_im('employees', '/view_appointment.php', array('id'=>$appoint->id));
                    $message .= '">[id='.$appoint->id.']</a>';
                    $erorrs['appoint-'.$appoint->id] = $message;
                    return $errors;
                }
                
                $obj = new stdClass();
                $obj->id = $appoint->id;
                $obj->departmentid = $newdepid;
                if( ! $this->dof->storage('appointments')->update($obj) )
                {//Если не удалось обновить запись персоны
                    $erorrs['appoint-'.$appoint->id] = $this->dof->get_string('record_update_failure_appoints','employees');
                }                           
            }
        }
        
        return $errors;
    }
    
    /** Переместить сотрудника, вместе с трудовым договором
     * (используется если выбраны дополнительные опции в форме переноса)
     * 
     * @param int $newdepid - подразделение (в таблице departments) в которое переносятся объекты
     * @param int $recordid - id главной записи, вместе с которой переносятся остальные объекты
     *                        (зависит от того какие объекты переноситт форма)
     * 
     * @return array
     */
    protected function move_employee($newdepid, $recordid)
    {
        $errors = array();
        
        if ( $personid = $this->dof->storage($this->storage)->get_field($recordid, 'personid') )
        {
            if ( ! $this->can_move_object_between_departments($personid, $newdepid, 'persons') )
            {// нет прав для перемещения подписки
                $message = $this->dof->get_string('form:error:unable_to_transfer_between_departments', 'departments');
                $message .= '<a href="'.$this->dof->url_im('persons', '/view.php', array('id'=>$personid));
                $message .= '">[id='.$personid.']</a>';
                $erorrs['per-'.$personid] = $message;
                return $errors;
            }
            
            $obj = new stdClass();
            $obj->id = $personid;
            $obj->departmentid = $newdepid;
            if( ! $this->dof->storage('persons')->update($obj) )
            {//Если не удалось обновить запись персоны
                $erorrs['per-'.$personid] = $this->dof->get_string('record_update_failure_pr','employees');
            }
        }
        
        return $errors;
    }
    
    /** Переместить все подписки на программы вместе с договором на обучение
     * (используется если выбраны дополнительные опции в форме переноса)
     * 
     * @param int $newdepid - подразделение (в таблице departments) в которое переносятся объекты
     * @param int $recordid - id главной записи, вместе с которой переносятся остальные объекты
     *                        (зависит от того какие объекты переноситт форма)
     * 
     * @return array
     */
    protected function move_programmsbcs($newdepid, $recordid)
    {
        $errors = array();
        if ( $prsbcs = $this->dof->storage('programmsbcs')->get_records(array('contractid' => $recordid)) )
        {
            foreach ( $prsbcs as $sbc )
            {
                if ( ! $this->can_move_object_between_departments($sbc->id, $newdepid, 'programmsbcs', $sbc->departmentid) )
                {// нет прав для перемещения подписки
                    $message = $this->dof->get_string('form:error:unable_to_transfer_between_departments', 'departments');
                    $message .= '<a href="'.$this->dof->url_im('programmsbcs', '/view.php', array('programmsbcid'=>$sbc->id));
                    $message .= '">[id='.$sbc->id.']</a>';
                    $erorrs['prsbc-'.$sbc->id] = $message;
                    return $errors;
                }
                
                $obj = new stdClass();
                $obj->id = $sbc->id;
                $obj->departmentid = $newdepid;
                if( ! $this->dof->storage('programmsbcs')->update($obj) )
                {
                    $erorrs['prsbc-'.$sbc->id] = $this->dof->get_string('record_update_failure_prsbcs','sel');
                }                           
            }
        }
        
        return $errors;
    }
}

?>