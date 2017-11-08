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
 * Точка входа для AJAX-запросов
 * 
 * @package    modlib
 * @subpackage widgets
 * @author     dido86
 * @copyright  2011
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек верхнего уровня
require_once("lib.php");

// Проверка авторизации в системе
require_login(0, true);

// Тип AJAX запроса
$type = required_param('type', PARAM_ALPHANUM);
// Тип плагина, к которому выполняется запрос
$plugincode = required_param('plugincode', PARAM_ALPHANUM);
// Код плагина, к которому выполняется запрос
$plugintype = required_param('plugintype', PARAM_ALPHANUM);
// Уникальное имя запроса в плагине
$querytype = required_param('querytype', PARAM_TEXT);
// Ключ сессии
$sesskey = required_param('sesskey', PARAM_ALPHANUM);
if ( ! confirm_sesskey($sesskey) )
{
    die('sesskey not confirmed');
}
// ID объекта, который редактируется или сохраняется
$objectid = optional_param('objectid', 0, PARAM_INT);
// ID Подразделения
$depid = optional_param('departmentid', 0, PARAM_INT);

// Данные в формате JSON. Не производим проверку здесь - она будет производится в том плагине, 
// которому адресованы данные
if ( isset($_GET['data']) )
{
    $data = $_GET['data'];
} elseif( isset($_POST['data']) )
{
    $data = $_POST['data'];
} else
{
    $data = null;
}

// Дополнительные данные
if ( isset($_GET['additional']) )
{
    $additional_data = $_GET['additional'];
} elseif( isset($_POST['additional']) )
{
    $additional_data = $_POST['additional'];
} else
{
    $additional_data = null;
}

switch ( $type )
{
    // получаем данные для автозаполнения
    case 'ajaxselect':
    case 'autocomplete': 
        $result = $DOF->modlib('widgets')->get_list_autocomplete($plugintype, $plugincode, $querytype, $depid, $data, $objectid, $additional_data);
        // кодируем ответ обратно в json и отправляем
        echo json_encode($result);
    break;
    // inline-редактирование одного поля
    case 'savefield': 
        echo $DOF->modlib('widgets')->save_ifield($plugintype, $plugincode, $querytype, $objectid, $data);
    break;
    // получение значения для inline-редактирования одного поля 
    case 'loadfield':
        echo $DOF->modlib('widgets')->load_ifield($plugintype, $plugincode, $querytype, $objectid, $data);
    break;
    case 'progressbar': 
        echo $DOF->modlib('widgets')->update_progressbar($plugintype, $plugincode, $querytype, $data);
    break;
}
?>