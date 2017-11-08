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

// ID назначения на должность
$appointmentid = required_param('id', PARAM_INT);
// Подтверждение удаления
$delete = optional_param('delete', 0, PARAM_BOOL);

// Получим ID договора с сотрудником
$eagreementid = $DOF->storage('appointments')->get_field($appointmentid, 'eagreementid');
if ( empty($eagreementid) )
{// Не нашли договор
    $errorlink = $DOF->url_im('employees', '/list_appointeagreements', $addvars);
    $DOF->print_error('error_eagreement_not_found', $errorlink, NULL, 'im', 'employees');
}
// Получим ID персоны по договору
$personid = $DOF->storage('eagreements')->get_field($eagreementid, 'personid');
if ( empty($personid) )
{// Не нашли персону
    $errorlink = $DOF->url_im('employees', '/list_appointeagreements', $addvars);
    $DOF->print_error('error_person_not_found', $errorlink, NULL, 'im', 'employees');
}
// Проверим на существование персону, которую собираемся удалять
$person = $DOF->storage('persons')->get($personid);
if ( empty($person) )
{// Не нашли персону
    $errorlink = $DOF->url_im('employees', '/list_appointeagreements', $addvars);
    $DOF->print_error('error_person_not_found', $errorlink, NULL, 'im', 'employees');
}
// Проверка прав доступа
$DOF->im('employees')->require_access('deleteperson', $appointmentid);

// Ссылки для страницы подтверждения удаления персоны
$somevars = $addvars;
$somevars['delete'] = 1;
$somevars['id'] = $appointmentid;
$linkyes = $DOF->url_im('employees', '/delete_person', $somevars);
$linkno = $DOF->url_im('employees','/list_appointeagreements', $addvars);

if ( $delete )  
{// Если подтвердили удаление
    // Кидаем событие удаления персоны
    $result = $DOF->send_event('im', 'employees', 'delete_person', $personid);
    if ( $result )
    {
        // Параметр успешного удаления для передачи по ссылке
        $addvars['deletesucsess'] = 1;
    } else {
        // Параметр для ошибки удаления 
        $addvars['deletesucsess'] = 0;
    }
    // Редирект на страницу списка назначений
    redirect($DOF->url_im('employees','/list_appointeagreements', $addvars) );
} else
{// Отобразми запрос на удаление сотрудника
    // Хлебные крошки
    $DOF->modlib('nvg')->add_level(
            $DOF->get_string('title', 'employees'), 
            $DOF->url_im('employees','/list.php', $addvars)
    );
    $DOF->modlib('nvg')->add_level(
            $DOF->get_string('list_appointeagreement', 'employees'),
            $DOF->url_im('employees','/list_appointeagreements.php',$addvars)
            );
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    
    // Отобразим имя удаляемой персоны
    echo '<div 
            align="center" 
            style="color:red;font-size:25px;"
         >'.
            $person->sortname.
         '</div><br>';
    // Запрос на удаление
    $DOF->modlib('widgets')->notice_yesno(
            $DOF->get_string('confirmation_delete_person', 'employees'), 
            $linkyes, 
            $linkno
    );
    // Список затрагиваемых записей
    echo '<div 
            align="center" 
            style="color:red;font-size:20px;">'.
                $DOF->get_string('confirmation_delete_person_affected_records', 'employees').
         '</div><br>';
    // Запрашиваем данные с записями, которые будут затронуты в ходе удаления сотрудника
    $result = $DOF->send_event('im', 'employees', 'delete_person_info', $personid);
    if ( ! is_bool($result) )
    {// Если есть затрагиваемые записи
        foreach( $result as $data )
        {// Выводим информацию
            print $data;
        }
    } else 
    {// Записей нет - сообщим об этом
        echo $DOF->get_string('confirmation_delete_person_no_records', 'employees');
    }
    
    // Печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
}
?>