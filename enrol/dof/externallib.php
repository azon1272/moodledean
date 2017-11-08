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
 * External course participation api.
 *
 * This api is mostly read only, the actual enrol and unenrol
 * support is in each enrol plugin.
 *
 * @package    enrol
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

/**
 * DOF enrolment functions
 */
class enrol_dof_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function enrol_users_parameters() {
        return new external_function_parameters(
                array(
                    'enrolments' => new external_multiple_structure(
                            new external_single_structure(
                                    array(
                                        'roleid' => new external_value(PARAM_INT, 'Role to assign to the user'),
                                        'userid' => new external_value(PARAM_INT, 'The user that is going to be enrolled'),
                                        'courseid' => new external_value(PARAM_INT, 'The course to enrol the user role in'),
                                        'timestart' => new external_value(PARAM_INT, 'Timestamp when the enrolment start', VALUE_OPTIONAL),
                                        'timeend' => new external_value(PARAM_INT, 'Timestamp when the enrolment end', VALUE_OPTIONAL),
                                        'suspend' => new external_value(PARAM_INT, 'set to 1 to suspend the enrolment', VALUE_OPTIONAL)
                                    )
                            )
                    )
                )
        );
    }

    /**
     * Enrolment of users
     * Function throw an exception at the first error encountered.
     * @param array $enrolments  An array of user enrolment
     * @return null
     */
    public static function enrol_users($enrolments) {
        global $DB, $CFG;

        require_once($CFG->libdir . '/enrollib.php');

        $params = self::validate_parameters(self::enrol_users_parameters(),
                array('enrolments' => $enrolments));

        $transaction = $DB->start_delegated_transaction(); //rollback all enrolment if an error occurs
                                                           //(except if the DB doesn't support it)

        //retrieve the dof enrolment plugin
        $enrol = enrol_get_plugin('dof');
        if (empty($enrol)) {
            throw new moodle_exception('dofpluginnotinstalled', 'enrol_dof');
        }

        foreach ($params['enrolments'] as $enrolment) {
            // Ensure the current user is allowed to run this function in the enrolment context
            if ( class_exists('context_course') )
            {// начиная с moodle 2.6
                $context = context_course::instance($enrolment['courseid'], MUST_EXIST);
            }else
            {// оставим совместимость с moodle 2.5 и менее
                $context = get_context_instance(CONTEXT_COURSE, $enrolment['courseid'], MUST_EXIST);
            }
            self::validate_context($context);

            //check that the user has the permission to dof enrol
            require_capability('enrol/dof:enrol', $context);

            //throw an exception if user is not able to assign the role
            $roles = get_assignable_roles($context);
            if (!key_exists($enrolment['roleid'], $roles)) {
                $errorparams = new stdClass();
                $errorparams->roleid = $enrolment['roleid'];
                $errorparams->courseid = $enrolment['courseid'];
                $errorparams->userid = $enrolment['userid'];
                throw new moodle_exception('wsusercannotassign', 'enrol_dof', '', $errorparams);
            }

            //check DOF enrolment plugin instance is enabled/exist
            $enrolinstances = enrol_get_instances($enrolment['courseid'], true);
            foreach ($enrolinstances as $courseenrolinstance) {
              if ($courseenrolinstance->enrol == "dof") {
                  $instance = $courseenrolinstance;
                  break;
              }
            }
            if (empty($instance)) {
              $errorparams = new stdClass();
              $errorparams->courseid = $enrolment['courseid'];
              throw new moodle_exception('wsnoinstance', 'enrol_dof', $errorparams);
            }

            //check that the plugin accept enrolment (it should always the case, it's hard coded in the plugin)
            if (!$enrol->allow_enrol($instance)) {
                $errorparams = new stdClass();
                $errorparams->roleid = $enrolment['roleid'];
                $errorparams->courseid = $enrolment['courseid'];
                $errorparams->userid = $enrolment['userid'];
                throw new moodle_exception('wscannotenrol', 'enrol_dof', '', $errorparams);
            }

            //finally proceed the enrolment
            $enrolment['timestart'] = isset($enrolment['timestart']) ? $enrolment['timestart'] : 0;
            $enrolment['timeend']   = isset($enrolment['timeend']) ? $enrolment['timeend'] : 0;
            $enrolment['status']    = (isset($enrolment['suspend']) && !empty($enrolment['suspend'])) ?
                    ENROL_USER_SUSPENDED : ENROL_USER_ACTIVE;

            $enrol->enrol_user($instance, $enrolment['userid'], $enrolment['roleid'],
                    $enrolment['timestart'], $enrolment['timeend'], $enrolment['status']);

        }

        $transaction->allow_commit();
    }

    /**
     * Returns description of method result value
     * @return null
     */
    public static function enrol_users_returns() {
        return null;
    }

}
