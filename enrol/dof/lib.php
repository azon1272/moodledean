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

require_once($CFG->dirroot.'/group/lib.php');

/**
 * DOF enrolment plugin main library file.
 *
 * @package    enrol
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class moodle_dean_plugin extends enrol_plugin {

    public function roles_protected() {
        // users may tweak the roles later
        return false;
    }

    public function allow_enrol(stdClass $instance) {
        // users with enrol cap may unenrol other users manually manually
        return true;
    }

    public function allow_unenrol(stdClass $instance) {
        // users with unenrol cap may unenrol other users manually manually
        return false;
    }

    public function allow_manage(stdClass $instance) {
        // users with manage cap may tweak period and status
        return true;
    }

    /**
     * Returns link to manual enrol UI if exists.
     * Does the access control tests automatically.
     *
     * @param object $instance
     * @return moodle_url
     */
    public function get_dof_enrol_link($instance) {
        $name = $this->get_name();
        if ($instance->enrol !== $name) {
            throw new coding_exception('invalid enrol instance!');
        }

        if (!enrol_is_enabled($name)) {
            return NULL;
        }

        if ( class_exists('context_course') )
        {// начиная с moodle 2.6
            $context = context_course::instance($instance->courseid, MUST_EXIST);
        }else
        {// оставим совместимость с moodle 2.5 и менее
            $context = get_context_instance(CONTEXT_COURSE, $instance->courseid, MUST_EXIST);
        }

        if ( ! has_capability('enrol/dof:manage', $context) or 
             ! has_capability('enrol/dof:enrol', $context) or 
             ! has_capability('enrol/dof:unenrol', $context)) {
            return NULL;
        }

        return new moodle_url('/enrol/dof/manage.php', array('enrolid'=>$instance->id, 'id'=>$instance->courseid));
    }

    /**
     * Returns enrolment instance manage link.
     *
     * By defaults looks for manage.php file and tests for manage capability.
     *
     * @param navigation_node $instancesnode
     * @param stdClass $instance
     * @return moodle_url;
     */
    public function add_course_navigation($instancesnode, stdClass $instance) {
        if ($instance->enrol !== 'dof') {
             throw new coding_exception('Invalid enrol instance type!');
        }
        if ( class_exists('context_course') )
        {// начиная с moodle 2.6
            $context = context_course::instance($instance->courseid);
        }else
        {// оставим совместимость с moodle 2.5 и менее
            $context = get_context_instance(CONTEXT_COURSE, $instance->courseid);
        }
        if (has_capability('enrol/dof:config', $context)) {
            $managelink = new moodle_url('/enrol/dof/edit.php', array('courseid'=>$instance->courseid));
            $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
        }
    }

    /**
     * Returns edit icons for the page with list of instances
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'dof') {
            throw new coding_exception('invalid enrol instance!');
        }
        if ( class_exists('context_course') )
        {// начиная с moodle 2.6
            $context = context_course::instance($instance->courseid);
        }else
        {// оставим совместимость с moodle 2.5 и менее
            $context = get_context_instance(CONTEXT_COURSE, $instance->courseid);
        }

        $icons = array();

        if (has_capability('enrol/dof:manage', $context)) {
            $managelink = new moodle_url("/enrol/dof/manage.php", array('enrolid'=>$instance->id));
            $icons[] = $OUTPUT->action_icon($managelink, new pix_icon('i/users', get_string('enrolusers', 'moodle_dean'), 'core', array('class'=>'iconsmall')));
        }
        if (has_capability('enrol/dof:config', $context)) {
            $editlink = new moodle_url("/enrol/dof/edit.php", array('courseid'=>$instance->courseid));
            $icons[] = $OUTPUT->action_icon($editlink, new pix_icon('i/edit', get_string('edit'), 'core', array('class'=>'icon')));
        }

        return $icons;
    }

    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        global $DB;
        
        if ( class_exists('context_course') )
        {// начиная с moodle 2.6
            $context = context_course::instance($courseid, MUST_EXIST);
        }else
        {// оставим совместимость с moodle 2.5 и менее
            $context = get_context_instance(CONTEXT_COURSE, $courseid, MUST_EXIST);
        }

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/dof:config', $context)) {
            return NULL;
        }

        if ($DB->record_exists('enrol', array('courseid'=>$courseid, 'enrol'=>'dof'))) {
            return NULL;
        }

        return new moodle_url('/enrol/dof/edit.php', array('courseid'=>$courseid));
    }

    /**
     * Add new instance of enrol plugin with default settings.
     * @param object $course
     * @return int id of new instance, null if can not be created
     */
    public function add_default_instance($course) {
        $fields = array('status'=>$this->get_config('status'), 'enrolperiod'=>$this->get_config('enrolperiod', 0), 'roleid'=>$this->get_config('roleid', 0));
        return $this->add_instance($course, $fields);
    }

    /**
     * Add new instance of enrol plugin.
     * @param object $course
     * @param array instance fields
     * @return int id of new instance, null if can not be created
     */
    public function add_instance($course, array $fields = NULL) {
        global $DB;

        if ($DB->record_exists('enrol', array('courseid'=>$course->id, 'enrol'=>'dof'))) {
            // only one instance allowed, sorry
            return NULL;
        }

        return parent::add_instance($course, $fields);
    }

    /**
     * Returns a button to manually enrol users through the manual enrolment plugin.
     *
     * By default the first manual enrolment plugin instance available in the course is used.
     * If no manual enrolment instances exist within the course then false is returned.
     *
     * This function also adds a quickenrolment JS ui to the page so that users can be enrolled
     * via AJAX.
     *
     * @param course_enrolment_manager $manager
     * @return enrol_user_button
     */
    public function get_dof_enrol_button(course_enrolment_manager $manager) {
        return false;
    }

    /**
     * Gets an array of the user enrolment actions
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue A user enrolment object
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol_user($instance, $ue) && has_capability("enrol/dof:unenrol", $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
        }
        if ($this->allow_manage($instance) && has_capability("enrol/dof:manage", $context)) {
            $url = new moodle_url('/enrol/dof/editenrolment.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/edit', ''), get_string('edit'), $url, array('class'=>'editenrollink', 'rel'=>$ue->id));
        }
        return $actions;
    }

    /**
     * The dof plugin has no bulk operations that can be performed
     * @param course_enrolment_manager $manager
     * @return array
     */
    public function get_bulk_operations(course_enrolment_manager $manager) {
        return array();
    }
}

/**
 * Indicates API features that the enrol plugin supports.
 *
 * @param string $feature
 * @return mixed True if yes (some features may use other values)
 */
function moodle_dean_supports($feature) {
    switch($feature) {
        case ENROL_RESTORE_TYPE: return ENROL_RESTORE_EXACT;

        default: return null;
    }
}
?>