<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                  
// <http://deansoffice.ru/>                                               //
//                                                                        //
// Copyright (C) 2008-2999  Alex Djachenko (Алексей Дьяченко)             //
// alex-pub@my-site.ru                                                    //
// Copyright (C) 2008-2999  Evgenij Cigancov (Евгений Цыганцов)           //
// Copyright (C) 2008-2999  Ilia Smirnov (Илья Смирнов)           
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

// Подключаем библиотек
require_once('lib.php');
$type = required_param('type',PARAM_ALPHA);
$orderid=required_param('id', PARAM_INT);
$cstreamid = required_param('id', PARAM_INT);
// проверяем права доступа
if ( ! $DOF->storage('cpassed')->is_access('edit:grade/own',$cstreamid) AND 
     ! $DOF->storage('cpassed')->is_access('edit:grade/auto',$cstreamid)) 
{// нет прав на автоматическое выставление - проверим на ручное
    $DOF->storage('cpassed')->require_access('edit:grade',$cstreamid);
}

// Создаем объект ведомости итоговых оценок
$ig = new block_dof_im_journal_templater_itoggrades($DOF);
// Получаем данные ведомости
$datafortable = $ig->get_data($orderid);


// Создаем объект документа 
$templater_package = $DOF->modlib('templater')->template( 'im', 'journal',$datafortable, 'itog_grades');
// Выбираем формат экспорта
switch ( $type )
{
    case 'odf' : $templater_package->send_file('odf');break;
    case 'csv' : $templater_package->send_file('csv');break;
    case 'html': $templater_package->send_file('html');break;
    default    : $templater_package->send_file('dbg');
}
?>