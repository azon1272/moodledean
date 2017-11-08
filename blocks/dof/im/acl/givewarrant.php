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
require_once('form.php');
// проверяем доступ
$DOF->im('acl')->require_access('aclwarrants:delegate');
// id доверенности
$aclwarrantid = required_param('aclwarrantid', PARAM_INT);
// id субдоверенночти
$subid = optional_param('id', 0, PARAM_INT);
// Если создаём новую субдоверенность, проверим родительскую
$check = new stdClass();
$new = false;
if ( $subid == 0 )
{
    $new = true;
} else
{// Иначе проверяем обычно
    $check->id = $subid;
}
$check->parentid = $aclwarrantid;
$check->parenttype = 'sub';
if ( !$DOF->storage('aclwarrants')->is_delegatable_sub($check, $new) )
{
    $DOF->print_error('isnotdelegatable', '', '', 'im', 'acl');
}

// добавление и сключение персон на субдоверенность
$removeselect = optional_param_array('removeselect', null, PARAM_INT);
$addselect = optional_param_array('addselect', null, PARAM_INT);

// id подразделения, в которое назначаем субдоверенность
$departmentid = $addvars['departmentid'];
// формируем массив передаваемых get-параметров
$ownerid = optional_param('ownerid', 0, PARAM_INT);
$addvars['id'] = $subid;
$addvars['aclwarrantid'] = $aclwarrantid;

$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'acl'), 
                     $DOF->url_im('acl','/index.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('give_warrant', 'acl'), 
                     $DOF->url_im('acl', '/givewarrant.php'), $addvars);
// проверим нахождение объекта в БД
if ( $subid === 0 )
{// если id = 0, формируем новую доверенность
    $aclwarrant = new stdClass();
    $aclwarrant->linkid = 0;
    $aclwarrant->linktype = 'none';
    $aclwarrant->parentid = $aclwarrantid;
    $aclwarrant->parenttype = 'sub';
    $aclwarrant->isdelegatable = 0;
    $aclwarrant->ownerid = $ownerid;
    $aclwarrant->departmentid = $departmentid;
    if ( ! $subid = $DOF->storage('aclwarrants')->insert($aclwarrant) )
    {// не удалось записать доверенность - дальше работать не можем
        $DOF->print_error('warrant_regive_failed', '', '', 'im', 'acl');
    }
    // записываем название и код по умолчанию
    $a = new stdClass();
    $a->id = $subid;
    $a->fio = $DOF->storage('persons')->get_fullname();
    $aclwarrant->code = 'sub'.$subid;
    $aclwarrant->name = $DOF->get_string('default_warrant_name', 'acl', $a);
    $DOF->storage('aclwarrants')->update($aclwarrant,$subid);
    // на всякий случай делаем редирект чтобы шаловливые ручки не наклонировали доверенностей
    $addvars['id'] = $subid;
    redirect($DOF->url_im('acl','/givewarrant.php',$addvars));
}
if ( ! $subwarrant = $DOF->storage('aclwarrants')->get($subid) )
{// если доверенность не найдена - выведем ошибку
    // @todo на место $link можно прописать ссылку, если надо будет
	$DOF->print_error('not_found_warrant', '', $subid, 'im', 'schedule');
}
// обработка создания/удаления применений
$addremoveresult = '';
if ( is_array($addselect) AND ! empty($addselect) )
{// есть персоны, которых нужно назначить на доверенность - создаем применения
    $addremoveresult = $DOF->im('acl')->process_addremove_aclwarrantagents('add', $addselect, $subid, $departmentid);
    // в зависимости от результата выводим сообщение
    $addremoveresult = $DOF->im('acl')->get_addremove_aclwarrantagents_result_message('add', $addremoveresult);
}
if ( is_array($removeselect) AND ! empty($removeselect) )
{// есть персоны, которых нужно отписать с доверенности - архивируем применения
    $addremoveresult = $DOF->im('acl')->process_addremove_aclwarrantagents('remove', $removeselect, $subid, $departmentid);
    // в зависимости от результата выводим сообщение
    $addremoveresult = $DOF->im('acl')->get_addremove_aclwarrantagents_result_message('remove', $addremoveresult);
}
// собираем данные для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->aclwarrantid = $aclwarrantid;
$customdata->departmentid = $departmentid;
$customdata->id = $subid;
// объявляем форму передоверения доверенности
$givewarrant = new dof_im_give_warrant_acl_form($DOF->url_im('acl', '/givewarrant.php', $addvars), $customdata, 'post');
$error = $givewarrant->process();
// загоняем редактируемые данные в форму
if ( $list = $DOF->storage('acl')->get_records(array(
                'aclwarrantid' => $subid), 'plugintype,plugincode,code'))
{
    foreach ($list as $rule)
    {// создаем checkbox каждому полю
        $subwarrant->acls[$rule->plugintype.'-'.$rule->plugincode.'-'.$rule->code] = 1;
    }
}
$givewarrant->set_data($subwarrant);


$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// выводим сообщения о результатах
echo $addremoveresult;
echo $error;
// отображаем форму
$givewarrant->display();
	

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>