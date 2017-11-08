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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/blocks/dof/otapilib.php');

/** Обновление таблиц блока dof
 *
 * @param int $oldversion
 * @todo сделать drop_enum_from_field для всех старых полей (проблема: метод drop_enum_from_field не существует в moodle 2.3+)
 */
function xmldb_block_dof_upgrade($oldversion)
{
    global $CFG, $DB;

    ////////////////////////////////////////
    // OTSerial-part
    global $OUTPUT;
    $otapi  = new block_dof_otserial(true);
    $result = $otapi->check_or_get_serial();
    if ( $result['status'] )
    {// всё прошло хорошо
        // печатаем сообщения как успешные
        foreach ( $result['messages'] as $message )
        {
            echo $OUTPUT->notification($message, 'notifysuccess');
        }
        // делаем всё, что нужно делать после успешной регистрации
        // (этому плагину не нужна активация)
    } else
    {// что-то незаладилось
        // печатаем сообщения
        foreach ( $result['messages'] as $message )
        {
            echo $OUTPUT->notification($message);
        }
    }


    ////////////////////////////////////////
    // Native DOF-part
    $dbman = $DB->get_manager();

    if ( $oldversion < 2012101000 )
    {

        // Define field personid to be added to block_dof_todo
        $table = new xmldb_table('block_dof_todo');
        $field = new xmldb_field('personid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0', 'exdate');

        // Conditionally launch add field personid
        if ( !$dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }

        $index = new xmldb_index('personid', XMLDB_INDEX_NOTUNIQUE, array('personid'));

        // Conditionally launch add index personid
        if ( !$dbman->index_exists($table, $index) )
        {
            $dbman->add_index($table, $index);
        }

        // dof savepoint reached
        upgrade_block_savepoint(true, 2012101000, 'dof');
    }

    if ( $oldversion < 2015020200 )
    {// Исправим некорректную версию плагина
        $params = array('type' => 'workflow', 'code' => 'contracts', 'version' => 20011082200);
        if ( $installedlplugin = $DB->get_record('block_dof_plugins', $params) )
        {
            $installedlplugin->version = 2015020200;
            $DB->update_record('block_dof_plugins', $installedlplugin);
        }
    }
    return true;
}

?>