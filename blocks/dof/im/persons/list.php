<?PHP
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
require_once('form.php');
require_once($DOF->plugin_path('im','departments','/lib.php'));
// Защищаем списки пользователей от случайного доступа
$DOF->storage('persons')->require_access('view');
$DOF->modlib('nvg')->add_level($DOF->get_string('listpersons', 'persons'), 
      $DOF->url_im('persons','/list.php',$addvars));

// ловим номер страницы, если его передали
// какое количество строк таблицы выводить на экран
$limitnum = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum = (int)optional_param('limitnum', $limitnum, PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom    = (int)optional_param('limitfrom', '1', PARAM_INT); 

$addvars['option'] = optional_param('option', 'bylastname', PARAM_TEXT);
$addvars['children'] = optional_param('children', 0, PARAM_INT);
$conds = new stdClass();
$conds->departmentid = optional_param('departmentid', 0, PARAM_INT);
$conds->childrendepid = optional_param('childrendepid', 0, PARAM_INT);
$searchoption = optional_param('searchstring', '', PARAM_TEXT);
$searchform = new dof_im_person_search_form($DOF->url_im('persons','/list.php',array_merge((array)$conds,$addvars)));
$conds->lastname = optional_param('lastname', '', PARAM_TEXT);
$conds->fioemailmdluser = optional_param('fioemailmdluser', '', PARAM_TEXT);

if ( $formdata = $searchform->get_data() AND isset($formdata->cancel ))
{
    redirect($DOF->url_im('persons','/list.php',$addvars));
}
// Выводим шапку в режиме "портала
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL, 'left');
// СМЕНА ПОДРАЗДЕЛНИЙ
$message = '';
// объевляем класс смены подразделения
$options = array();
$change_department = new dof_im_departments_change_department($DOF,'persons',$options);

$errors = $change_department->execute_form();
if ( $errors != 1 )
{// сработал обработчик
    if ( empty($errors) )
    {// выводим сообщение, что все хорошо
        $message = '<p style=" color:green; "><b>'.$DOF->get_string('departments_change_success', 'persons').'</b></p>';
    }else
    {// все плохо...
        $message = '<p style=" color:red; "><b>'.implode('<br>',$errors).'</b></p>';
    }
}

echo $message;

echo "<ul>";
if ( $DOF->storage('persons')->is_access('create') )
{// создание персоны
    
    if ( $DOF->storage('config')->get_limitobject('persons',$conds->departmentid) )
    {
        echo "<li><a href=\"{$DOF->url_im('persons','/edit.php',$addvars)}\">
            {$DOF->get_string('createperson', 'persons')}</a></li>";
        
    }else 
    {
        $link =  '<li><span style="color:silver;">'.$DOF->get_string('createperson', 'persons').
                 ' ('.$DOF->get_string('limit_message','persons').')</span></li>';
        echo '<br>'.$link; 
    }
    if ( $DOF->is_access('datamanage') )
    {
        echo "<li><a href=\"{$DOF->url_im('persons','/util_email.php',$addvars)}\">{$DOF->get_string('createpersonemails', 'persons')}</a></li>";
    }
}
// смена временой зоны у персон
if ( $DOF->storage('persons')->is_access('edit_timezone') )
{
    
    echo "<li><a href=\"{$DOF->url_im('persons','/edit_timezone.php',array('departmentid'=>$addvars['departmentid']))}\">
        {$DOF->get_string('edit_time_zone', 'persons')}</a></li>";
}
if ( $DOF->storage('persons')->is_access('view') )
{
    $href = $DOF->url_im('persons', '/extendedsearch.php', array('departmentid' => $addvars['departmentid']));
    echo "<li><a href=\"{$href}\">{$DOF->get_string('extendedsearch', 'persons')}</a></li>";
}
echo "</ul>";
  
$searchform->set_data(array_merge($addvars,array('searchstring'=>$searchoption)));
$searchform->display();

if ( $searchform->is_submitted() AND $formdata = $searchform->get_data() AND ! isset($formdata->cancel) )
{
    $conds->lastname = '';
    $conds->fioemailmdluser = '';
    if ( isset($formdata->option) )
    {
        switch($formdata->option)
        {
            case 'bylastname':
                $conds->lastname = $formdata->searchstring;
                $searchoption = $formdata->searchstring;
            break;
            case 'byquery':
                $conds->fioemailmdluser = $formdata->searchstring;
                $searchoption = $formdata->searchstring;
            break;
            case 'byoldname':
                $conds->oldnamesearch = $formdata->searchstring;
                $searchoption = $formdata->searchstring;
            break;
        }
    }
    $addvars['children'] = 0;
    $conds->childrendepid = 0;
    if ( isset($formdata->children) AND $formdata->children )
    {// сказано искать в дочерних
        $conds->childrendepid = $conds->departmentid;
        $addvars['children'] = 1;
    }
}

// подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('persons',null,$limitnum, $limitfrom);
$list = $DOF->storage('persons')->get_listing($conds, $pages->get_current_limitfrom()-1, 
                                      $pages->get_current_limitnum(),'sortname'); 

$vars = array('limitnum'  => $pages->get_current_limitnum(),
              'limitfrom' => $pages->get_current_limitfrom(),
              'searchstring'    => $searchoption);
// добавляем все необходимые условия фильтрации
$vars = array_merge($vars, (array)$conds);

//начело формы
echo '<form action="'.$DOF->url_im('persons','/list.php', $vars).'" method=POST name="change_department">';
                                     
$DOF->im('persons')->show_list($list,$addvars,$change_department->options);

// конец формы
echo $change_department->get_form();
echo '</form>';


$pages->count = $DOF->storage('persons')->get_listing($conds, $pages->get_current_limitfrom(), 
                        $pages->get_current_limitnum(),'sortname','*',true);


// выводим строку со списком страниц
$pagesstring = $pages->get_navpages_list('/list.php', array_merge($vars,$addvars));
echo $pagesstring;


//$pathright = $DOF->plugin_path('im', 'standard').'/cfg/right.php';
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL,'right');


?>