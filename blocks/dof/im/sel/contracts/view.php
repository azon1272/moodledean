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
require_once($DOF->plugin_path('im','departments','/lib.php'));
$contrid = required_param('id', PARAM_INT);
$personid = $DOF->storage('persons')->get_by_moodleid_id();
$DOF->modlib('nvg')->add_level($DOF->get_string('contractlist', 'sel'), $DOF->url_im('sel','/contracts/list.php',$addvars));
if ( $obj = $DOF->storage('contracts')->get($contrid) )
{
    
    // Проверяем права доступа
    $DOF->im('sel')->require_access('viewcontract', $obj->id);
    
    $options = array();
    $message = '';
    $change_department = new dof_im_departments_change_department($DOF,'contracts',$options);
    $errors = $change_department->execute_form();
    if ( $errors != 1 )
    {// сработал обработчик
        if ( empty($errors) )
        {// выводим сообщение, что все хорошо
            $message = '<p style=" color:green; "><b>'.$DOF->get_string('departments_change_success', 'sel').'</b></p>';
        }else
        {// все плохо...
            $message = '<p style=" color:red; "><b>'.implode('<br>',$errors).'</b></p>';
        }
    }
    
    // загружаем объект еще раз после обработчика
    
    //добавление уровня навигации
    $DOF->modlib('nvg')->add_level($obj->num, $DOF->url_im('sel',"/contracts/view.php?id={$obj->id}",$addvars));
    
}else 
{
      $DOF->modlib('nvg')->add_level($DOF->modlib('ig')->igs('error'), $DOF->url_im('sel'));
         
}
$obj = $DOF->storage('contracts')->get($contrid);
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
if ( ! $obj )
{
    print_error($DOF->get_string('notfound', 'sel', $contrid));
}

echo "<br />";
echo $message;
// Добавим ссылки
// Просмотр подписок на программу
if ( $DOF->storage('programmsbcs')->is_access('view') )
{
    echo "<a href='{$DOF->url_im('programmsbcs',"/list.php?contractid={$obj->id}",$addvars)}'>{$DOF->get_string('view_programmsbcs', 'sel')}</a><br>";
}
// создание подписки на программу
if ( $DOF->storage('programmsbcs')->is_access('create') )
{
    // лимит
    if ( $DOF->storage('config')->get_limitobject('programmsbcs',$addvars['departmentid']) )
    {
        echo "<a href='{$DOF->url_im('programmsbcs',"/edit.php?contractid={$obj->id}",$addvars)}'>
            {$DOF->get_string('create_programmsbc_for_contract', 'sel')}</a><br>";
    }else 
    {    
        echo  '<span style="color:silver;">'.$DOF->get_string('create_programmsbc_for_contract', 'sel').
            ' <br>('.$DOF->get_string('limit_message','sel').')</span><br>';        
    }
    

}
// Полсмотр сведений об учебе
if ( $DOF->im('sel')->is_access('viewpersonal') )
{
    echo "<a href='{$DOF->url_im('recordbook', '/index.php?clientid='.$obj->studentid,$addvars)}'>{$DOF->get_string('recordbook', 'sel')}</a><br>";
}
echo "<br>";
// Получаем персональную информацию
if ( ! $seller = $DOF->storage('persons')->get($obj->sellerid) )
{// если селлера нет, выведем пустые строчки
    $seller = new stdClass();
    $seller->firstname = '';
    $seller->middlename = '';
    $seller->lastname= '';
}
if ( ! $curator = $DOF->storage('persons')->get($obj->curatorid) )
{// если куратора нет, выведем пустые строчки
    $curator = new stdClass();
    $curator->firstname = '';
    $curator->middlename = '';
    $curator->lastname= '';
}
if ( ! $departname = $DOF->storage('departments')->get_field($obj->departmentid, 'name') )
{
    $departname = '&nbsp;';
}
// Рисуем таблицу
$table = new stdClass();
$table->data = array();
$table->data[] = array($DOF->get_string('num', 'sel'),$obj->num);
$table->data[] = array($DOF->get_string('date', 'sel'),date('d-m-Y',$obj->date));
$table->data[] = array($DOF->get_string('department', 'sel'),$departname);
$table->data[] = array($DOF->get_string('notes', 'sel'),nl2br(htmlspecialchars($obj->notes)));
if ( !isset($seller->middlename) )
{
    $seller->middlename = '';
}
if ( !isset($curator->middlename) )
{
    $curator->middlename = '';
}
$table->data[] = array($DOF->get_string('seller', 'sel'),"{$seller->firstname} {$seller->middlename} {$seller->lastname}");
$table->data[] = array($DOF->get_string('curator', 'sel'),"{$curator->firstname} {$curator->middlename} {$curator->lastname}");
$table->data[] = array($DOF->get_string('adddate', 'sel'),date('d-m-Y H:i:s',$obj->adddate));

$metacontractnum = '';
//метаконтракты
if ( !empty($obj->metacontractid))
{
    $metacontractnum = $DOF->storage('metacontracts')->get_field($obj->metacontractid,'num');
}

$table->data[] = array($DOF->get_string('metacontract', 'sel'), $metacontractnum);


//$table->data[] = array($DOF->get_string('statusdate', 'sel'),date('d-m-Y H:i:s',$obj->statusdate));
$table->data[] = array($DOF->get_string('status', 'sel'),$DOF->workflow('contracts')->get_name($obj->status));
$table->data[] = array($DOF->get_string('print', 'sel')
            ,"<a href='".$DOF->url_im('sel',"/contracts/print.php?id={$obj->id}&type=html")."' target='_blank'>".$DOF->get_string('protokol', 'sel')." в html"."</a><br>"
//          ."<a href='".$DOF->url_im('sel',"/contracts/print.php?id={$obj->id}&type=dbg")."' target='_blank'>".$DOF->get_string('protokol', 'sel')." в odf"."</a>"
            ."<a href='".$DOF->url_im('sel',"/contracts/print.php?id={$obj->id}&type=odf")."' target='_blank'>".$DOF->get_string('protokol', 'sel')." в odf"."</a>");

// Меню статусов
//получаем статусы, в которые можно переходить из текущего
$available = array_keys($DOF->workflow('contracts')->get_available($obj->id));
$available = $DOF->storage('acl')->
    get_usable_statuses_select('workflow','contracts',$available,$addvars['departmentid'],$personid,$obj->id);


// Убираем статусы, не положенные по доступу
// @todo убрать эту старую проверку после перехода на новую систему прав
if (isset($available['wesign']) AND !$DOF->im('sel')->is_access('manageaccount'))
{
    unset($available['wesign']);
}
if (isset($available['archives']) AND !$DOF->im('sel')->is_access('manageaccount'))
{
    unset($available['archives']);
}
if (isset($available['frozen']) AND !$DOF->im('sel')->is_access('payaccount'))
{
    unset($available['frozen']);
}
if (isset($available['work']) AND !$DOF->im('sel')->is_access('payaccount'))
{
    unset($available['work']);
}

if ( is_array($available) AND ! empty($available) )
{//формируем строку для отображения меню
    $status_menu = "<form name=\"edit_obj\" method=\"post\" action=\"".$DOF->url_im('sel',"/contracts/setstatus.php",$addvars)."\">";
    $status_menu .= "\n<input type=\"hidden\" name=\"id\" value=\"{$obj->id}\" />";
    $status_menu .= "\n".'<select name="status">';
    foreach ($available as $key=>$status)
    {
        $status_menu .= "\n".'<option value="'.$key.'">'.$status.'</option>';
    }
    $status_menu .= "<input type=\"submit\" name=\"save\" value=\"{$DOF->get_string('save', 'sel')}\" />";
    
    $status_menu .= "\n".'</select>';
    if (isset($available['archives']) OR isset($available['cancel']) )
    {
        $status_menu .= "\n<br /><input name='muserkeep' value='1' type='checkbox' />{$DOF->get_string('keepuserwhenarchives', 'sel')}";
    }
    $status_menu .= "\n".'</form>';
    $table->data[] = array($DOF->get_string('setstatus', 'sel'),$status_menu);
}

$table->tablealign = "center";
$table->align = array ("left","left");
$table->wrap = array ("nowrap","");
$table->cellpadding = 5;
$table->cellspacing = 0;
$table->width = '600';
$table->size = array('200px','400px');
// $table->head = array('id', 'code');
$DOF->modlib('widgets')->print_table($table);
$DOF->modlib('widgets')->print_heading($DOF->get_string('student', 'sel'),'',3);
$DOF->im('persons')->show_person($obj->studentid,$addvars);
if ($obj->clientid <> $obj->studentid)
{
    $DOF->modlib('widgets')->print_heading($DOF->get_string('specimen', 'sel'),'',3);
    $DOF->im('persons')->show_person($obj->clientid,$addvars);
}
// Выводим ссылку только тем, кому можно редактировать контракт
if ($DOF->im('sel')->is_access('editcontract',$obj->id))
{
    echo "<br><p align=center><a href='{$DOF->url_im('sel',"/contracts/edit_first.php?contractid={$obj->id}",$addvars)}'>{$DOF->get_string('edit', 'persons')}</a></p>";
}
echo '<form action="'.$DOF->url_im('sel',"/contracts/view.php?id={$obj->id}",$addvars).'" method=POST name="change_department">';
echo '<input type="hidden" name="'.$change_department->options['prefix'].'_'.
     $change_department->options['listname'].'['.$obj->id.']" value="'.$obj->id.'"/>';
echo $change_department->get_form();
echo '</form>';

$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>