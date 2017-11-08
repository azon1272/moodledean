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
$type = required_param('type',PARAM_ALPHA);
if (!$obj = $DOF->storage('contracts')->get(required_param('id', PARAM_INT)))
{
	error("Object unfinded");
}
// Проверяем права доступа
$DOF->im('sel')->require_access('viewcontract', $obj->id);

// Получаем персональную информацию
$obj->selfirstname = '';
$obj->sellastname = '';
$obj->selmiddlename = '';
if ( $seller = $DOF->storage('persons')->get($obj->sellerid) )
{// менеджер договора есть
    $obj->selfirstname = $seller->firstname;
    $obj->sellastname = $seller->lastname;
    if ( !isset($seller->middlename) )
    {
        $seller->middlename = '';
    }
    $obj->selmiddlename = $seller->middlename;
}
$student = (array) $DOF->storage('persons')->get($obj->studentid);
$student += (array) $DOF->storage('addresses')->get($student['passportaddrid']);
$student = (object) $student;
$student->name = 'student';
$obj->clientfirstname = $student->firstname;
$obj->clientlastname = $student->lastname;
if ( !isset($student->middlename) )
{
    $student->middlename = '';
}
$obj->clientmiddlename = $student->middlename;
if (($student->passtypeid == 0) or (!isset($student->passtypeid)))
{
	$student->passtypeid = $DOF->get_string('nonepasport', 'sel');
	$student->passportdate = '';
	
} else
{
	$student->passtypeid = $DOF->modlib('refbook')->pasport_type($student->passtypeid);
	$student->passportnum = $student->passportnum.' выдан(о)';
}

$client = (array) $DOF->storage('persons')->get($obj->clientid);
$client += (array) $DOF->storage('addresses')->get($client['passportaddrid']);
$client = (object) $client;
$client->name = 'specimen';
if (($client->passtypeid == 0) or (!isset($client->passtypeid)))
{
    $client->passtypeid = $DOF->get_string('nonepasport', 'sel');
    $client->passportdate = '';	
} else
{
    $client->passtypeid = $DOF->modlib('refbook')->pasport_type($client->passtypeid);
    $client->passportnum = $client->passportnum.' выдан(о)';
}

$obj->clientfirstname = $client->firstname;
$obj->clientlastname = $client->lastname;
if ( !isset($client->middlename) )
{
    $client->middlename = '';
}
$obj->clientmiddlename = $client->middlename;
// Добавляем список объектов из БД в виде массива
//$obj->table = array($student,$client);
// Добавляем список объектов из БД в виде объектов
$obj->student = $student;
$obj->client = $client;
//    print_object($obj);
// Создаем объект документа 
$templater_package = $DOF->modlib('templater')->template( 'im', 'sel',$obj, 'protokol');
// Выбираем формат экспорта
switch ($type)
{
    case 'odf' : $templater_package->send_file('odf');break;
    case 'csv' : $templater_package->send_file('csv');break;
    case 'html' : $templater_package->send_file('html');break;
    case 'dbg' :
    default : $templater_package->send_file('dbg');

}
?>