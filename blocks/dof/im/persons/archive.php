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
//id персоны
$personid = required_param('personid', PARAM_INT);
$archive = optional_param('archive', 0, PARAM_BOOL);
// проверки
// не найдена персона
if ( ! $person  = $DOF->storage('persons')->get($personid) )
{// вывод сообщения и ничего не делаем
    $errorlink = $DOF->url_im('persons','',$addvars);
    $DOF->print_error('notfound',$errorlink,$personid,'im','persons');
}
//todo нужна проверка статусов персоны
//проверка прав доступа
$DOF->storage('persons')->require_access('changestatus', $personid);

// ссылки на подтверждение и непотдверждение сохранения приказа
$linkyes = $DOF->url_im('persons', '/archive.php?personid='.$personid.'&archive=1',$addvars);
$linkno = $DOF->url_im('persons', '/list.php',$addvars);
if ( $archive )
{// если сказали удалить
    //Меняем статус персоны
    $obj = new stdClass();
    $obj->id = $person->id; 
    $obj->status = 'archived';
    $DOF->storage('persons')->update($obj);
    // рассинхронизируем персону
    $DOF->sync('personstom')->unsync($person,false);
    redirect($linkno);
}else
{
    //вывод на экран
    //печать шапки страницы
    $DOF->modlib('nvg')->add_level($DOF->get_string('listpersons', 'persons'), $DOF->url_im('persons','/list.php'),$addvars);
    $DOF->modlib('nvg')->add_level($DOF->get_string('archive_person', 'persons'),
                                   $DOF->url_im('persons','/archive.php',$addvars));
    $DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);
    // вывод названия удаляемого элемента
    echo '<div align="center" style="color:red;font-size:25px;">' . $person->sortname . '</div><br>';
    // спросим об удалении
    $DOF->modlib('widgets')->notice_yesno($DOF->get_string('confirmation_archive_person','persons'), $linkyes, $linkno);
    //печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);
}

?>