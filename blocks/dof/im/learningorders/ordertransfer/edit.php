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

/**
 * Страница просмотра списка приказов.
 *
 * @package    im
 * @subpackage learningorders
 * @subpackage ordertransfer
 * @author     Dmitrii Shtolin <d.shtolin@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');
require_once('form.php');

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('nvg_edit_order', 'orders'), 
    $DOF->url_im('orders','/index.php',$addvars)
);


/* ОТОБРАЖЕНИЕ СТРАНИЦЫ */

// Проверка доступа
// $DOF->storage('departments')->require_access('view', $addvars['departmentid']);

$html = '';

//объявляем форму
$formedit = $DOF->storages('orders')->form_edit($addvars);
// Обработаем форму
$processeddata = $formedit->process();
//если обработка завершилась успешно
if($processeddata['status']=='success')
{
    //добавляем результат в html
    $html .= $processeddata['html'];
}
//В любом случае выводим все сообщения, собранные во время обработки формы
foreach($processeddata['messages'] as $message)
{
    $messages->add($message['text'],$message['type']);
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
//Вывод сообщений
$messages->display();
//Вывод формы
$formedit->display();
//Вывод контента
echo $html;
// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>