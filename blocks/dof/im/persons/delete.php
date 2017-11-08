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
// ID персоны
$personid = required_param('personid', PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);

// Проверки
if ( ! $person = $DOF->storage('persons')->get($personid) )
{// Не найдена персона
    $errorlink = $DOF->url_im('persons','',$addvars);
    $DOF->print_error('notfound', $errorlink, $personid, 'im', 'persons');
}

// Проверка прав доступа
$DOF->workflow('persons')->require_access('changestatus', $personid);

// Ссылки для страницы подтверждения удаления персоны
$somevars = $addvars;
$somevars['personid'] = $personid;
$somevars['delete'] = 1;
$linkyes = $DOF->url_im('persons', '/delete.php', $somevars);
$linkno = $DOF->url_im('persons', '/list.php', $addvars);
if ( $delete )  
{// Если подтвердили удаление
    // Меняем статус персоны
    if ( $DOF->workflow('persons')->change($person->id, 'deleted') )
    {// Перевод статуса не удался
        $DOF->print_error('error_status_change', $linkno, null, 'im', 'persons');   
    }
    redirect($linkno);
}else
{// Выведем запрос на подтверждение удаления
    
    // Хлебные крошки
    $DOF->modlib('nvg')->add_level($DOF->get_string('listpersons', 'persons'), 
                                   $DOF->url_im('persons','/list.php'),$addvars);
    $DOF->modlib('nvg')->add_level($DOF->get_string('delete_person', 'persons'),
                                   $DOF->url_im('persons','/delete.php',$addvars));
    // Шапка страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);
    // Имя удаляемой персоны
    echo '<div 
            align="center" 
            style="color: red; font-size: 25px;"
          >'.
            $person->sortname.
         '</div></br>';
    // Cпросим об удалении персоны
    $DOF->modlib('widgets')->notice_yesno($DOF->get_string('confirmation_delete_person','persons'), $linkyes, $linkno);
    // Подвал страницы
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);
}
?>