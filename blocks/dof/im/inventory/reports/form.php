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

class dof_im_inventory_report_sets_and_items extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /*
     * Метод отрисовкм формы
     */
    function definition()
    {    
        // делаем глобальные переменные видимыми
        $this->dof = $this->_customdata->dof;
        $depid = $this->_customdata->depid;
        if ( ! $type = $this->_customdata->type )
        {
            $type = 'persons';
        }
        if ( ! $catid = $this->_customdata->categoryid )
        {
            $catid = 0;
        }
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $mform->addElement('hidden','departmentid', $depid);
        $mform->setType('departmentid', PARAM_INT);
        // категория
        $mform->addElement('hidden','categoryid', $catid);
        $mform->setType('categoryid', PARAM_INT);
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('reports_inventory', 'inventory'));
        // выбор отчета
        $mform->addElement('radio', 'type', '', $this->dof->get_string('report_persons', 'inventory'), 'persons');
        $mform->addElement('radio', 'type', '', $this->dof->get_string('report_items', 'inventory'), 'items');
        // галочка - дочерние
        
        $mform->addElement('checkbox', 'child', '', $this->dof->get_string('including_child', 'inventory'));            
        

        // выбор времени
        $options = array();
        $options['startyear'] = dof_userdate(time()-10*365*24*3600,'%Y');
        $options['stopyear']  = dof_userdate(time()+10*365*24*3600,'%Y');
        $options['optional']  = false;
        $mform->addElement('date_time_selector', 'crondate', $this->dof->get_string('crondate','journal').':',$options);
        // настройки по умолчанию
        $mform->setDefault('type', $type);
        // дочерние покажем только для комплектов
        $mform->disabledIf('child', 'type', 'noteq','items');
            
        $mform->addElement('submit', 'buttonview', $this->dof->get_string('button_order','inventory'));

    }
    
    /* Метод обработки данных их формы
     * 
     * @param array $addvars - массив с доп данными
     * 
     * return 
     */
    public function process($addvars)
    {
        if ( $formdata = $this->get_data() )
        {

            // загружаем метод работы с отчетом
            if ( $formdata->type == 'persons' )
            {
                $report = $this->dof->im('inventory')->report('loadpersons');      
            }elseif( $formdata->type == 'items' ) 
            {
                $report = $this->dof->im('inventory')->report('loaditems');
            }
            
            // формируем данные для отчета
            $reportdata = new stdClass();
            $reportdata->crondate = $formdata->crondate;
            $reportdata->begindate = '';
            $reportdata->enddate = '';
            $reportdata->personid = $this->dof->storage('persons')->get_by_moodleid_id();
            $reportdata->departmentid = $addvars['departmentid'];
            // запишеи категорию, а то выборку никак уже не сделать
            $reportdata->objectid = $addvars['invcategoryid'];
            // запише категорию и ключая/не включая дочерние в data
            $reportdata->data = new stdClass();
            $reportdata->data->categoryid = $addvars['invcategoryid'];
            if ( isset($formdata->child) )
            {// включая дочерние
                $reportdata->data->child = 1;
            }else 
            {// не включая дочерние     
                $reportdata->data->child = 0;
            }
            // сохраняем
            $report->save($reportdata);
        }        
    }

}

?>