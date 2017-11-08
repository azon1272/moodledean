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
require_once('libform.php');

//получаем id подразделения
$personid = optional_param('personid',0,PARAM_INT);
$viewform = optional_param('viewform',0,PARAM_BOOL);
$date_from = optional_param('date_from',time(),PARAM_INT);
$date_to = optional_param('date_to',time(),PARAM_INT);
$display = optional_param('display','time',PARAM_TEXT);
$calendar = optional_param_array('calendar',null,PARAM_TEXT);
// если не подключан js, то поля date будут пустые
// потому делаем тут эту проверку
if ( !empty($calendar) AND is_array($calendar) AND !empty($calendar['date_from']) AND !empty($calendar['date_to']) )
{
    $date_from = $calendar['date_from'];
    $date_to   = $calendar['date_to'];
}

// нет персоны - укажем её сами                               
if ( ! $personid )
{
    $personid = $DOF->storage('persons')->get_by_moodleid_id();
}
$show_all = false;
//проверяем полномочия на просмотр информации
$DOF->im('journal')->require_access('view_schevents');

// сформируем массив из всех данных
$addnew = array('personid'=>(int) $personid,
                'display' => $display,
                'viewform'=>(int) $viewform,
                'departmentid' => (int)$addvars['departmentid']);

// право просмотра мнимых уроков
$acl_viewimplied = $DOF->storage('schevents')->is_access('view:implied');

// мнимые уроки
$show_implied = false;
if ( $acl_viewimplied )
{// отображение мнимых уроков при переходе из вкладок
    $show_implied = optional_param('show_implied', false, PARAM_BOOL);
}

// объект для формы
$default            = new stdClass();
$default->dof       = $DOF;
$default->viewform  = $viewform;
$default->depid     = $addvars['departmentid'];
$default->date_from = $date_from;
$default->date_to   = $date_to;
$default->implied   = $show_implied;

//выводим форму выбора подразделения
$depchoose = new dof_im_journal_show_events_form($DOF->url_im('journal',
                               '/show_events/show_events.php',$addnew), $default);

$depchoose->set_data(array('personid'=>(int) $personid,
                           'display' => $display,
                           'viewform'=>(int) $viewform));

if ( $depchoose->is_submitted() AND $formdata = $depchoose->get_data() )
{
    //print_object($formdata);
    if ( !empty($calendar) AND is_array($calendar) AND !empty($calendar['date_from']) AND !empty($calendar['date_to']) )
    {// из календаря
        $date_from = $formdata->calendar['date_from'];
        $date_to   = $formdata->calendar['date_to'];
    }else 
    {// обычный select
        $date_from = $formdata->date_fr;
        $date_to   = $formdata->date_t;
    }    
    if ( isset($formdata->option) AND $formdata->option == 'all' )
    {// выбраны все персоны
        $personid = 0;
    }elseif( isset($formdata->option) AND $formdata->option == 'fio' )
    {// поиск персон - покажем всех из этого подразделения
        // тут начинает работу наш автокомплит
        $personid = (int)$formdata->search['id_autocomplete'];
    }
    // $personid = $formdata->option;
    if ( isset($formdata->dispaly) )
    {// когда занятия по персоне - display нет
        $display = $formdata->display;
    }
    if ( isset($formdata->buttongroup['buttonviewall']) )
    {// отображаем все поля без исключений
        $show_all = true;
    }else if ( isset($formdata->buttongroup['buttondownload']) )
    {// переходим на страницу скачивания csv файла
        $addvars['date_from']       = $date_from;
        $addvars['date_to']         = $date_to;
        $addvars['personid']        = $personid;
        redirect($DOF->url_im('journal', '/show_events/export.php', $addvars));
    }
    if ( isset($formdata->impliedview) AND $acl_viewimplied )
    {// значение отмечено и есть права - отобразим мнимые уроки
        $show_implied = true;
    }
}                 
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$DOF->modlib('widgets')->print_heading($DOF->modlib('ig')->igs('you_from_timezone', dof_usertimezone()));
// TODO в будущем вынести ЭТО в стили
// тут мы выделяем другим цветом всю строку таблицы, отвечеющей нашим условиям
echo "
  <style type='text/css'>
    #mismatch_department { color: #009900; }   
    .mismatch_department .cell { color: #009900; }  
  </style> ";
$path = $DOF->plugin_path('im','journal','/cfg/show_events.php');
$DOF->modlib('nvg')->print_sections($path);
// добавим стили для отображения завершенных потоков
echo '<style type="text/css"> #menu A { color: gray; } </style>';

$rez = '';
$list = '';

// отобразим форму
$depchoose->display();

// отобразим вкладки
if ( ! $viewform )
{
    // дозапише даты для перехода по вкладкам
    $addnew += array('date_from'=>$date_from,'date_to'=>$date_to);
    if ( $show_implied AND $acl_viewimplied ) 
    {// есть права и чекбокс отмечен - сохраним состояние чекбокса при переходе
        $addnew += array('show_implied'=>true);
    }
    echo $DOF->im('journal')->print_tab($addnew,$display);
}    

// для того, чтобы при нажатии выбора занятий по персоне
// не грузились МОИ уроки, ведь мы же ищем по персоне, не за чем грузить ещё до отправки мои уроки
$flag = true;
if ( $viewform )
{
    if ( isset($formdata) )
    {
        $flag = true;
    }else 
    {
        $flag = false;
    }    
} 

// отобразим по РЕЖИМУ
if ( $date_from < $date_to OR $date_from == $date_to )
{
    if ( $display == 'time' AND $flag )
    {// или по времени или когда есть по персоне
        if ( $personid == 0 )
        {// считаем, что персона учитель
            //подключаем методы получения списка журналов
            $d = new dof_im_journal_show_events($DOF, $addvars['departmentid']);
            //инициализируем начальную структуру
            $d->set_data(array('date_from'=>$date_from, 'date_to'=>$date_to));
            //получаем список журналов
            $rez = $d->get_table_events('time', $show_all, $show_implied);
        }
        if ( ($DOF->storage('eagreements')->is_exists(array('personid'=>$personid))) AND ($personid != 0) )
        {// считаем, что персона учитель
            //подключаем методы получения списка журналов
            $d = new dof_im_journal_show_events($DOF,$addvars['departmentid']);
            //инициализируем начальную структуру
            $d->set_data(array('date_from'=>$date_from, 'date_to'=>$date_to), $personid);
            //получаем список журналов
            $rez = $d->get_table_events('time', $show_all, $show_implied);
        }
        if ( ($DOF->storage('contracts')->is_exists(array('studentid'=>$personid))) AND ($personid != 0) )
        {// считаем, что персона студент
            //подключаем методы получения списка журналов
            $d = new dof_im_journal_show_events($DOF,$addvars['departmentid']);
            //инициализируем начальную структуру
            $d->set_data(array('date_from'=>$date_from, 'date_to'=>$date_to), null, $personid);
            //получаем список журналов
            $rez = $d->get_table_events('time', $show_all, $show_implied);
        }
    }
    // режим - по ученикам
    if( $display == 'students' )
    {
        //подключаем методы получения списка журналов
        $d = new dof_im_journal_show_events($DOF,$addvars['departmentid']);    
        //инициализируем начальную структуру
        $d->set_data(array('date_from'=>$date_from, 'date_to'=>$date_to));    
        $rez = $d->get_table_events('students', false, $show_implied);
    }
    // режим- по преподвания
    if( $display == 'teachers' )
    {
        //подключаем методы получения списка журналов
        $d = new dof_im_journal_show_events($DOF,$addvars['departmentid']);    
        //инициализируем начальную структуру
        $d->set_data(array('date_from'=>$date_from, 'date_to'=>$date_to));
        $rez = $d->get_table_events('teachers', false, $show_implied);
    }    
}

//получаем список журналов
// обработка результат + вывод сообщений, если результат пуст
if ( $personid AND $display == 'time' )
{// для персоны - покажеи имя
    $name = '<div align=center><b>'.$DOF->storage('persons')->get_field($personid, 'sortname').'</b></div><br>';
    if ( $rez )
    {// есть рузельтат - покажем чей он
        $rez = $name.$rez;
    }elseif($viewform == 0 OR isset($formdata))
    {// сообщение - нет результата
        $rez = $name.'<div align=center><i>'.$DOF->get_string('no_lesson','journal').'</i></div>';
    }
}

echo $rez;

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>