<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Free Dean's Office installation.
 *
 * @package    block
 * @subpackage dof
 * @author     Kirill Krasnoschekov, Ilya Fastenko, OpenTechnology ltd.
 * @copyright  2013
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/blocks/dof/otapilib.php');

function xmldb_block_dof_install()
{
    global $DB, $OUTPUT;
    $otapi = new block_dof_otserial();

    $result = $otapi->check_or_get_serial();
    if ($result['status'])
    {// всё прошло хорошо
        // печатаем сообщения как успешные
        foreach ($result['messages'] as $message)
        {
            echo $OUTPUT->notification($message, 'notifysuccess');
        }
        // делаем всё, что нужно делать после успешной регистрации
        // (этому плагину не нужна активация)
    }
    else
    {// что-то незаладилось
        // печатаем сообщения
        foreach ($result['messages'] as $message)
        {
            echo $OUTPUT->notification($message);
        }
    }
    return true;
}

?>