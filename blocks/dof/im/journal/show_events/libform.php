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



// Подключаем библиотеки
require_once('lib.php');

// содключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/** Класс формы отображения событий
 * 
 */
class dof_im_journal_show_events_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /*
     * Код плагина
     */
    protected function im_code()
    {
        return 'journal';
    }    
    
    function definition()
    {    
        // делаем глобальные переменные видимыми
        $this->dof = $this->_customdata->dof;
        $depid = $this->_customdata->depid;

        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('show_events', 'journal'));
        $mform->addElement('hidden', 'viewform');
        $mform->setType('viewform', PARAM_BOOL);
        $mform->addElement('hidden','departmentid', $depid);
        $mform->setType('departmentid', PARAM_INT);
		
        
        
        // добавляем для совместимость, если вдруг нет js у пользователей
        // если сработал js, то прячем эти элементы
        $options = array();
        $options['startyear'] = dof_userdate(time()-5*365*24*3600,'%Y');
        $options['stopyear']  = dof_userdate(time()+5*365*24*3600,'%Y');
        
        $options['optional']  = false;
        $mform->addElement('date_selector', 'date_fr',$this->dof->modlib('ig')->igs('from'),$options);
        $mform->setType('date', PARAM_INT);
        $mform->addElement('date_selector', 'date_t',$this->dof->modlib('ig')->igs('to'),$options);
        $mform->setType('date', PARAM_INT);
        $mform->setDefault('date_fr', $this->_customdata->date_from);
        $mform->setDefault('date_t', $this->_customdata->date_to);
        
        // добавим календарь
        $options = array();
        $options['date_from'] = $this->_customdata->date_from;
        $options['date_to'] = $this->_customdata->date_to;
        $options['calendartype'] = 'two_calendar';
        // дозапишем значения, которые надо скрывать
        $options[] = 'date_fr';
        $options[] = 'date_t';
        $mform->addElement('dof_calendar','calendar', $this->dof->get_string('time_select','journal'), $options);

        
        if ( $this->_customdata->viewform )
        {// отобразим также выпадающие списки учителей и учеников
            $mform->addElement('radio', 'option', '',$this->dof->get_string('all_persons',$this->im_code()), 'all');
            $mform->addElement('radio', 'option', '', $this->dof->get_string('search_person',$this->im_code()), 'fio');           
            $mform->setDefault('option', 'all');
            
            // элемент автозаполнения - составим данные 
            $s = array();       
            $s['plugintype'] =   "im";
            $s['plugincode'] =   "journal";
            $s['querytype']  =   "person_name";
            $s['sesskey']    =   sesskey();
            $s['type']       =   'autocomplete';
            $mform->addElement('dof_autocomplete', 'search', $this->dof->get_string('chose_person',$this->im_code()),'', $s);
            $mform->setType('search', PARAM_TEXT);
            
            $mform->DisabledIf('search','option','eq','all');
            if ( $this->dof->storage('schevents')->is_access('view:implied', null, null, $depid) )
            {// право на просмотр мнимых уроков
                $mform->addElement('checkbox', 'impliedview', '', $this->dof->get_string('implied_view','journal'));
                // тип и значение по умолчанию
                $mform->setType('impliedview', PARAM_BOOL);
                $mform->setDefault('impliedview', $this->_customdata->implied);
            }
            
        }else
        {
            $mform->addElement('hidden', 'personid');
            $mform->setType('personid', PARAM_INT);
        }
        $mform->addElement('html', '<div style="text-align: center">');
        $group=array();
        $group[] =& $mform->createElement('submit', 'buttonview',     $this->dof->get_string('button_view','journal'));
        $group[] =& $mform->createElement('submit', 'buttonviewall',  $this->dof->get_string('button_view_all','journal'));
        if ($this->_customdata->viewform == 1 AND $this->dof->im('journal')->is_access('export_events'))
        {// режим просмотра уроков по персоне - показываем кнопку "скачать"
        	$group[] =& $mform->createElement('submit', 'buttondownload', $this->dof->get_string('button_download','journal'));
        }
        $mform->addGroup($group, 'buttongroup');
        $mform->addElement('html', '</div>');
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Проверка данных на стороне сервера
     * @return array
     * @param array $data[optional] - массив с данными из формы
     */
    public function validation($data,$files)
    {// проверка ввода корректной даты
        $error = array();
        if ( isset($data['calendar']) AND $data['calendar']['date_from'] > $data['calendar']['date_to'])
        {
            $error['calendar'] = $this->dof->get_string('begindate_bigger_enddate', 'journal');
        }
        if( isset($data['date_from']) AND $data['date_from'] > $data['date_to'] ) 
        {
            $error['date_from'] = $this->dof->get_string('begindate_bigger_enddate', 'journal');
        }
        if ( isset($data['viewform']) AND isset($data['option']) AND $data['option'] == 'fio' AND ! $data['search'] )
        {
            $error['search'] = $this->dof->get_string('empty_field', 'journal');
        } 
        // проверка на то, что выбрали по персоне и ввели, которая не сущ в деканате
        if ( isset($data['option']) AND $data['option'] == 'fio' AND $data['search']['id_autocomplete'] == 0 )
        {
            $error['search'] = $this->dof->get_string('no_found_person', 'journal', $data['search']['search']);
        }
      	//print_object($this->get_data());
        return $error;
    }
    
       
}

/** Форма выбора режима отображения расписания
 * 
 */
class dof_im_journal_display_mode_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    /**
     * @var int - id подразделения в таблице departments, для которого будет просматриваться расписание
     */
    protected $departmentid;
    
    protected function im_code()
    {
        return 'journal';
    }
    
    public function definition()
    {
        $mform     = $this->_form;
        $this->dof = $this->_customdata->dof;
        // заголовок
        $mform->addElement('header', 'header', $this->dof->get_string('display_mode', $this->im_code()));
        // учебный период
        $mform->addElement('select', 'ageid', $this->dof->get_string('age', $this->im_code()), $this->get_ages());
        $mform->setType('ageid', PARAM_INT);
        // тип отображения
        $mform->addElement('select', 'display', $this->dof->get_string('display_mode', $this->im_code()), $this->get_display_modes());
        $mform->setType('display', PARAM_ALPHANUM);
        // кнопка "показать"
        $mform->addElement('submit', 'go', $this->dof->modlib('ig')->igs('show'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Получить список доступных учебных периодов
     * @todo добавить проверку права использовать период, когда будет преведена в порядок
     *       функция is_access() в плагине ages. Пока что просто выводятся все доступные периоды
     *       от plan до active
     * @todo Учитывать переданное подразделение
     * 
     * @return array массив учебных периодов для select-элемента
     */
    protected function get_ages()
    {
        $ages = $this->dof->storage('ages')->
                    get_records(array('status'=>array('plan', 'createstreams', 'createsbc', 'createschedule', 'active')));
        
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'ages', 'code'=>'view'));
        $ages        = $this->dof_get_acl_filtered_list($ages, $permissions);
        
        return $this->dof_get_select_values($ages);
    }
    
    /** Получить возможные режимы отображения расписания (по времени/по ученикам/по учителям)
     * 
     * @return array
     */
    protected function get_display_modes()
    {
        return array(
                'students' => $this->dof->get_string('display_mode:students', $this->im_code()),
                'teachers' => $this->dof->get_string('display_mode:teachers', $this->im_code()));
    }
    
    /** Получить id пользователя в таблице persons, который сейчас редактирует форму
     * 
     * @return int
     */
    protected function get_userid()
    {
        $person = $this->dof->storage('persons')->get_bu();
        if ( $person )
        {
            return $person->id;
        } 
        
        return false;
    }
    
    /** Обработчик формы
     * 
     * @param array $urloptions - массив дополнительных параметров для ссылки при редиректе
     */
    public function process($urloptions)
    {
        if ( $formdata = $this->get_data() AND confirm_sesskey() )
        {
            // добавляем к странице новые параметры
            $urloptions['ageid']   = $formdata->ageid;
            $urloptions['display'] = $formdata->display;
            // создаем ссылку
            $url = $this->dof->url_im('journal', '/show_events/schedule.php', $urloptions);
            // перезагружаем страницу
            redirect($url, '', 0);
        }
    }
}


?>