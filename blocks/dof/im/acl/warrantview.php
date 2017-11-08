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

$aclwarrantid = required_param('aclwarrantid', PARAM_INT);
$addvars['aclwarrantid'] = $aclwarrantid;

if ( ! $DOF->im('acl')->is_access('aclwarrants:view/owner',$aclwarrantid) )
{
    $DOF->im('acl')->require_access('aclwarrants:view',$aclwarrantid);
}

//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'acl'), 
                     $DOF->url_im('acl','/index.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('warrant_view', 'acl'), 
                     $DOF->url_im('acl','/warrantview.php'),$addvars);

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
$links = '';

$links .= "<a href='".$DOF->url_im('acl', '/warrantacl.php', array_merge($addvars,array(
        'id' => $aclwarrantid)))."'>".$DOF->get_string(
                'warrants_table_acl_list','acl')."</a>";
if ( $DOF->storage('aclwarrants')->get_field($aclwarrantid,'ownerid') == 
     $DOF->storage('persons')->get_by_moodleid_id($USER->id) )
{
    $links .= "<br/><a href='".$DOF->url_im('acl', '/index.php', array_merge($addvars,array(
        'type' => 1, 'typelist' => 1, 'aclwarrantid' => $aclwarrantid)))."'>".$DOF->get_string(
                'warrants_table_warrantagents_list','acl')."</a>";
}else
{
    $links .= "<br/><a href='".$DOF->url_im('acl', '/index.php', array_merge($addvars,array(
        'type' => 1, 'typelist' => 2, 'aclwarrantid' => $aclwarrantid)))."'>".$DOF->get_string(
                'warrants_table_warrantagents_list','acl')."</a>";
}
echo $links.'<br>';
echo $DOF->im('acl')->show_one_warrant($addvars, $aclwarrantid);

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>