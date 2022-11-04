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

use format_topicsactivitycards\metadata;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/topics/lib.php');

class format_topicsactivitycards extends format_topics {
    public const SECTIONLAYOUT_CARDS = 10;
    public const SECTIONLAYOUT_LIST = 20;

    public function course_format_options($foreditform = false) {
        static $courseformatoptionsforedit = false;
        static $courseformatoptions = false;

        if ($foreditform ) {
            if ($courseformatoptionsforedit === false) {
                $courseformatoptionsforedit = parent::course_format_options(true);

                $courseconfig = get_config('moodlecourse');
                $courseformatoptionsforedit['collapsible'] = array(
                    'default' => !empty($courseconfig->collapsible),
                    'type' => PARAM_BOOL,
                    'label' => new lang_string('collapsible', 'format_topicsactivitycards'),
                    'element_type' => 'advcheckbox'
                );
            }

            return $courseformatoptionsforedit;
        } else {
            if ($courseformatoptions === false) {
                $courseformatoptions = parent::course_format_options(false);

                $courseconfig = get_config('moodlecourse');
                $courseformatoptions['collapsible'] = array(
                    'default' => !empty($courseconfig->collapsible),
                    'type' => PARAM_BOOL,
                );
            }

            return $courseformatoptions;
        }
    }

    public function section_format_options($foreditform = false) {
        $retval = parent::section_format_options($foreditform);

        $retval['sectionlayout'] = [
                'default'            => 0,
                'type'               => PARAM_TEXT,
                'label'              => get_string('sectionlayout', 'format_topicsactivitycards'),
                'element_type'       => 'select',
                'element_attributes' => [
                        [
                                self::SECTIONLAYOUT_CARDS => get_string('sectionlayout_cards', 'format_topicsactivitycards'),
                                self::SECTIONLAYOUT_LIST  => get_string('sectionlayout_list', 'format_topicsactivitycards')
                        ]
                ]
        ];

        return $retval;
    }
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

function format_topicsactivitycards_cardbackgroundimage_filemanageroptions() {
    global $COURSE;
    return [
            'maxbytes'       => $COURSE->maxbytes,
            'subdirs'        => 1,
            'accepted_types' => 'image',
            'maxfiles'       => 1
    ];
}

/**
 * Inject the competencies elements into all moodle module settings forms.
 *
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $form The actual form object (required to modify the form).
 */
function format_topicsactivitycards_coursemodule_standard_elements($formwrapper, $form) {
    global $SITE;

    if ($formwrapper->get_course()->format !== 'topicsactivitycards') {
        return;
    }

    $cmid = null;
    if ($cm = $formwrapper->get_coursemodule()) {
        $cmid = $cm->id;
        $metadata = metadata::get_record(['cmid' => $cmid]);
    }

    if (empty($metadata)) {
        $metadata = new metadata();
    }

    $form->addElement('header', 'format_topicsactivitycards', get_string('pluginname', 'format_topicsactivitycards'));

    $form->addElement('duration', 'duration', get_string('duration', 'format_topicsactivitycards'));
    $form->setDefault('duration', 0);
    $form->setType('duration', PARAM_INT);

    $options = range(1, 12);
    $options = array_combine($options, $options);
    $form->addElement('select', 'renderwidth', get_string('renderwidth', 'format_topicsactivitycards'), $options);
    $form->setType('renderwidth', PARAM_INT);
    $form->setDefault('renderwidth', 4);

    $form->addElement('text', 'additionalcssclasses', get_string('additionalcssclasses', 'format_topicsactivitycards'));
    $form->setType('additionalcssclasses', PARAM_TEXT);

    $form->addElement('advcheckbox', 'overlaycardimage', '', get_string('overlaycardimage', 'format_topicsactivitycards'));

    $form->addElement('advcheckbox', 'cleanandtruncatedescription', '', get_string('cleanandtruncatedescription', 'format_topicsactivitycards'));

    $form->addElement('filemanager', 'cardbackgroundimage_filemanager', get_string('cardimage', 'format_topicsactivitycards'), '',
            format_topicsactivitycards_cardbackgroundimage_filemanageroptions());

    $values = $metadata->to_record();
    $values = file_prepare_standard_filemanager($values,
            'cardbackgroundimage',
            format_topicsactivitycards_cardbackgroundimage_filemanageroptions(),
            $formwrapper->get_context(),
            'format_topicsactivitycards',
            'cardbackgroundimage',
            0);

    $editoroptions = ['maxfiles' => EDITOR_UNLIMITED_FILES,
        'maxbytes' => $SITE->maxbytes, 'context' => $formwrapper->get_context()];
    $form->addElement('editor', 'activitydescription_editor', get_string('activitydescription', 'format_topicsactivitycards'), null, $editoroptions);
    $form->setType('activitydescription_editor', PARAM_CLEANHTML);

    $values = file_prepare_standard_editor($values, 'activitydescription', $editoroptions, $formwrapper->get_context(), 'format_topicsactivitycards', 'activitydescription',
        0);

    $form->addElement('editor', 'cardfooter_editor', get_string('cardfooter', 'format_topicsactivitycards'), null, $editoroptions);
    $form->setType('cardfooter_editor', PARAM_CLEANHTML);

    $values = file_prepare_standard_editor($values, 'cardfooter', $editoroptions, $formwrapper->get_context(), 'format_topicsactivitycards', 'cardfooter',
        0);

    $form->setDefaults((array)$values);
}

function format_topicsactivitycards_coursemodule_edit_post_actions($data, $course) {
    global $SITE;

    if ($course->format !== 'topicsactivitycards') {
        return $data;
    }

    $context = context_module::instance($data->coursemodule);

    $editoroptions = ['maxfiles' => EDITOR_UNLIMITED_FILES,
        'maxbytes' => $SITE->maxbytes, 'context' => $context];
    $data = file_postupdate_standard_editor($data, 'activitydescription', $editoroptions, $context, 'format_topicsactivitycards',
        'activitydescription', 0);
    $data = file_postupdate_standard_editor($data, 'cardfooter', $editoroptions, $context, 'format_topicsactivitycards',
        'cardfooter', 0);

    $metadata = metadata::get_record(['cmid' => $data->coursemodule]);

    if (!$metadata) {
        $metadata = new metadata();
        $metadata->set('cmid', $data->coursemodule);
    }

    $metadata->set('duration', $data->duration);
    $metadata->set('renderwidth', $data->renderwidth);
    $metadata->set('cleanandtruncatedescription', $data->cleanandtruncatedescription);
    $metadata->set('overlaycardimage', $data->overlaycardimage);
    $metadata->set('activitydescription', $data->activitydescription);
    $metadata->set('activitydescriptionformat', $data->activitydescriptionformat);
    $metadata->set('cardfooter', $data->cardfooter);
    $metadata->set('cardfooterformat', $data->cardfooterformat);
    $metadata->set('additionalcssclasses', $data->additionalcssclasses);

    if (empty($metadata->get('id'))) {
        $metadata->save();
    } else {
        $metadata->update();
    }
    file_postupdate_standard_filemanager(
            $data,
            'cardbackgroundimage',
            format_topicsactivitycards_cardbackgroundimage_filemanageroptions(),
            $context,
            'format_topicsactivitycards',
            'cardbackgroundimage',
            0);

    return $data;
}

function format_topicsactivitycards_coursemodule_validation($form, $data) {
    $errors = [];

    if (!empty($data['additionalcssclasses'])) {
        foreach (explode(' ', $data['additionalcssclasses']) as $class) {
            if (empty(preg_match('/^-?[_a-zA-Z]+[_a-zA-Z0-9-]*$/', $class))) {
                $errors['additionalcssclasses'] = get_string('invalidcss', 'format_topicsactivitycards');
            }
        }
    }

    return $errors;
}

function format_topicsactivitycards_pluginfile($course, $cm, context $context, $filearea, $args, $forcedownload) {
    $itemid = array_shift($args); // Ignore revision - designed to prevent caching problems only...

    $relativepath = implode('/', $args);
    $fullpath = "/{$context->id}/format_topicsactivitycards/$filearea/$itemid/$relativepath";
    $fs = get_file_storage();
    if (!($file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
        return false;
    }

    // Force download.
    send_stored_file($file, 0, 0, true);
}
