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
 * @package format_topicsactivitycards
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @author Open Source Learning <enquiries@opensourcelearning.co.uk>
 * @link https://opensourcelearning.co.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2021, Andrew Hancox
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/topics/lib.php');

class format_topicsactivitycards extends format_topics {
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 */
function format_topicsactivitycards_inplace_editable($itemtype, $itemid, $newvalue) {

    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
                'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
                array($itemid, 'topicsactivitycards'), MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    } else {
        return null;
    }
}

// We need to override the core_course_get_module web service function so that when the activity
// is moved the correct renderer gets used to re-insert into the DOM.
function format_topicsactivitycards_override_webservice_execution($externalfunctioninfo, $params) {
    if ($externalfunctioninfo->name !== 'core_course_get_module') {
        return false;
    }

    global $PAGE, $CFG;

    require_once("$CFG->dirroot/course/externallib.php");

    // Validate and normalize parameters.
    $params = \external_api::validate_parameters(\core_course_external::get_module_parameters(),
            array('id' => $params[0], 'sectionreturn' => $params[1]));
    $id = $params['id'];
    $sectionreturn = $params['sectionreturn'];

    // Set of permissions an editing user may have.
    $contextarray = [
            'moodle/course:update',
            'moodle/course:manageactivities',
            'moodle/course:activityvisibility',
            'moodle/course:sectionvisibility',
            'moodle/course:movesections',
            'moodle/course:setcurrentsection',
    ];
    $PAGE->set_other_editing_capability($contextarray);

    // Validate access to the course (note, this is html for the course view page, we don't validate access to the module).
    list($course, $cm) = get_course_and_cm_from_cmid($id);

    if ($course->format !== 'topicsactivitycards') {
        return false;
    }

    \core_course_external::validate_context(context_course::instance($course->id));

    $courserenderer = new \format_topicsactivitycards\course_renderer($PAGE, null);
    $completioninfo = new completion_info($course);
    return $courserenderer->course_section_cm_list_item($course, $completioninfo, $cm, $sectionreturn);
}


function format_topicsactivitycards_coursemodule_standard_elements($formwrapper, $mform) {
}
