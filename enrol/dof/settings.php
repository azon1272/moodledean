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

/**
 * DOF enrolment plugin settings and presets.
 *
 * @package    enrol
 * @subpackage dof
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// TODO Сделать тут одну единственную настройку "роль пользователя в курсе по умолчанию"
if ($ADMIN->fulltree) {

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_dof_settings', '', get_string('pluginname_desc', 'enrol_dof')));


    //--- enrol instance defaults ----------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_dof_defaults',
        get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));

    $settings->add(new admin_setting_configcheckbox('enrol_dof/defaultenrol',
        get_string('defaultenrol', 'enrol'), get_string('defaultenrol_desc', 'enrol'), 1));

    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_dof/status',
        get_string('status', 'enrol_dof'), get_string('status_desc', 'enrol_dof'), ENROL_INSTANCE_ENABLED, $options));

    $settings->add(new admin_setting_configtext('enrol_dof/enrolperiod',
        get_string('defaultperiod', 'enrol_dof'), get_string('defaultperiod_desc', 'enrol_dof'), 0, PARAM_INT));

    if (!during_initial_install()) 
    {
        if ( class_exists('context_system') )
        {// начиная с moodle 2.6
            $context = context_system::instance();
        }else
        {// оставим совместимость с moodle 2.5 и менее
            $context = get_context_instance(CONTEXT_SYSTEM);
        }
        $options = get_default_enrol_roles($context);
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_dof/roleid',
            get_string('defaultrole', 'role'), '', $student->id, $options));
        $teacher = get_archetype_roles('teacher');
        $teacher = reset($teacher);
        $settings->add(new admin_setting_configselect('enrol_dof/teacherroleid',
            get_string('defaultteacherrole', 'enrol_dof'), '', $teacher->id, $options));
        $editing = get_archetype_roles('editingteacher');
        $editing = reset($editing);
        $settings->add(new admin_setting_configselect('enrol_dof/editingroleid',
            get_string('defaulteditingrole', 'enrol_dof'), '', $editing->id, $options));
    }
}

