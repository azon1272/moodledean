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
// Copyright (C) 2008-2999  Nikolay Konovalov (Николай Коновалов)         //
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
// Подключаем библиотеки верхнего уровня
require_once(dirname(realpath(__FILE__)) . '/../../lib.php');
//ini_set("soap.wsdl_cache_enabled", "0");
$action = optional_param('do', null, PARAM_TEXT);

// Проверка на то, включен плагин или нет
if ( !$DOF->sync('soap')->is_enabled() )
{// Плагин не включен
    $DOF->print_error('plugin_has_been_disabled', '', null, 'sync', 'soap');
}

// Отдаём wsdl-файл
if ( $action == 'wsdl' )
{
    // Посылаем заголовок, чтобы клиент думал что это wsdl
    header('Content-type: application/xml');
    // Подключаем файл сервиса
    echo '<?xml version="1.0" encoding="UTF-8" standalone="no"?>';
    require_once(dirname(realpath(__FILE__)).'/service.wsdl');
} else if ( $action == 'service' )
{ // Обрабатываем SOAP-запрос
    require_once(dirname(realpath(__FILE__)).'/service.php');
}
?>