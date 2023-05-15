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
    public const SECTIONLAYOUT_COURSEDEFAULT = 0;
    public const SECTIONLAYOUT_CARDS = 10;
    public const SECTIONLAYOUT_LIST = 20;

    public const SECTIONHEADING_COURSEDEFAULT = 0;
    public const SECTIONHEADING_HEADER = 10;
    public const SECTIONHEADING_LINKEDCARD = 20;
    public const SECTIONHEADING_CARD_WITHCONTENTS = 30;

    public const PAGELAYOUT_FIXEDWIDTH = 10;
    public const PAGELAYOUT_FULLWIDTH = 20;

    public function page_set_course(moodle_page $page) {
        if ($this->get_format_options()['overridefixedwidthcoursepage'] == self::PAGELAYOUT_FULLWIDTH) {
            $page->add_body_class('overridefixedwidthcoursepage');
        }
    }

    public function course_format_options($foreditform = false) {
        static $courseformatoptionsforedit = false;
        static $courseformatoptions = false;

        if ($foreditform) {
            if ($courseformatoptionsforedit === false) {
                $courseformatoptionsforedit = parent::course_format_options(true);
                unset($courseformatoptionsforedit['coursedisplay']);

                $courseformatoptionsforedit['overridefixedwidthcoursepage'] = [
                    'label' => new lang_string('overridefixedwidthcoursepage', 'format_topicsactivitycards'),
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            self::PAGELAYOUT_FIXEDWIDTH => new lang_string('fixedwidth', 'format_topicsactivitycards'),
                            self::PAGELAYOUT_FULLWIDTH => new lang_string('fullwidth', 'format_topicsactivitycards')
                        ]
                    ]
                ];

                $courseformatoptionsforedit['sectionheading'] = [
                    'default' => self::SECTIONHEADING_HEADER,
                    'type' => PARAM_TEXT,
                    'label' => get_string('defaultsectionheading', 'format_topicsactivitycards'),
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            self::SECTIONHEADING_HEADER => get_string('sectionheading_header', 'format_topicsactivitycards'),
                            self::SECTIONHEADING_LINKEDCARD => get_string('sectionheading_linkedcard', 'format_topicsactivitycards'),
                            self::SECTIONHEADING_CARD_WITHCONTENTS => get_string('sectionheading_card_withcontents', 'format_topicsactivitycards'),
                        ]
                    ]
                ];

                $courseformatoptionsforedit['sectionlayout'] = [
                    'default' => self::SECTIONLAYOUT_CARDS,
                    'type' => PARAM_TEXT,
                    'label' => get_string('defaultsectionlayout', 'format_topicsactivitycards'),
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            self::SECTIONLAYOUT_CARDS => get_string('sectionlayout_cards', 'format_topicsactivitycards'),
                            self::SECTIONLAYOUT_LIST => get_string('sectionlayout_list', 'format_topicsactivitycards'),
                        ]
                    ]
                ];
            }

            return $courseformatoptionsforedit;
        } else {
            if ($courseformatoptions === false) {
                $courseformatoptions = parent::course_format_options(false);
                unset($courseformatoptions['coursedisplay']);

                $courseformatoptions['overridefixedwidthcoursepage'] = [
                    'default' => self::PAGELAYOUT_FULLWIDTH,
                    'type' => PARAM_INT,
                ];

                $courseformatoptions['sectionheading'] = [
                    'default' => self::SECTIONHEADING_HEADER,
                    'type' => PARAM_TEXT,
                ];

                $courseformatoptions['sectionlayout'] = [
                    'default' => self::SECTIONLAYOUT_LIST,
                    'type' => PARAM_TEXT,
                ];
            }

            return $courseformatoptions;
        }
    }

    public function section_format_options($foreditform = false): array {
        $retval = parent::section_format_options($foreditform);

        $retval['sectionheading'] = [
            'default' => self::SECTIONHEADING_COURSEDEFAULT,
            'type' => PARAM_TEXT,
            'label' => get_string('sectionheading', 'format_topicsactivitycards'),
            'element_type' => 'select',
            'element_attributes' => [
                [
                    self::SECTIONHEADING_COURSEDEFAULT => get_string('coursedefault', 'format_topicsactivitycards'),
                    self::SECTIONHEADING_HEADER => get_string('sectionheading_header', 'format_topicsactivitycards'),
                    self::SECTIONHEADING_LINKEDCARD => get_string('sectionheading_linkedcard', 'format_topicsactivitycards'),
                    self::SECTIONHEADING_CARD_WITHCONTENTS => get_string('sectionheading_card_withcontents', 'format_topicsactivitycards'),
                ]
            ]
        ];

        $retval['sectionlayout'] = [
            'default' => self::SECTIONLAYOUT_COURSEDEFAULT,
            'type' => PARAM_TEXT,
            'label' => get_string('sectionlayout', 'format_topicsactivitycards'),
            'element_type' => 'select',
            'element_attributes' => [
                [
                    self::SECTIONLAYOUT_COURSEDEFAULT => get_string('coursedefault', 'format_topicsactivitycards'),
                    self::SECTIONLAYOUT_CARDS => get_string('sectionlayout_cards', 'format_topicsactivitycards'),
                    self::SECTIONLAYOUT_LIST => get_string('sectionlayout_list', 'format_topicsactivitycards'),
                ]
            ]
        ];

        $options = range(1, 12);
        $options = array_combine($options, $options);
        $retval['renderwidth'] = [
            'default' => 4,
            'type' => PARAM_INT,
            'label' => get_string('renderwidth', 'format_topicsactivitycards'),
            'element_type' => 'select',
            'element_attributes' => [
                $options
            ]
        ];

        $retval['collapsible'] = array(
            'default' => false,
            'type' => PARAM_BOOL,
            'label' => new lang_string('collapsible', 'format_topicsactivitycards'),
            'element_type' => 'advcheckbox'
        );

        $retval['collapsedefault'] = array(
            'default' => false,
            'type' => PARAM_BOOL,
            'label' => new lang_string('collapsedefault', 'format_topicsactivitycards'),
            'element_type' => 'advcheckbox'
        );

        $retval['sectioncardbackgroundimage_filemanager'] = [
            'label' => get_string('cardimage', 'format_topicsactivitycards'),
            'element_type' => 'filemanager',
            'element_attributes' => [
                format_topicsactivitycards_cardbackgroundimage_filemanageroptions()
            ]
        ];

        $retval['additionalcssclasses'] = [
            'default' => '',
            'type' => PARAM_TEXT,
            'label' => get_string('additionalcssclasses', 'format_topicsactivitycards'),
            'element_type' => 'text'
        ];

        $retval['cleanandtruncatedescription'] = array(
            'default' => false,
            'type' => PARAM_BOOL,
            'label' => new lang_string('cleanandtruncatedescription', 'format_topicsactivitycards'),
            'element_type' => 'advcheckbox'
        );

        $retval['overridesectionsummary_editor'] = [
            'default' => '',
            'type' => PARAM_CLEANHTML,
            'label' => get_string('overridesectionsummary', 'format_topicsactivitycards'),
            'element_type' => 'editor',
            'element_attributes' => [
                '',
                $this->texteditoroptions()
            ]
        ];

        return $retval;
    }

    public function create_edit_form_elements(&$mform, $forsection = false): array {
        global $DB;

        $elements = parent::create_edit_form_elements($mform, $forsection);

        if ($forsection) {
            $coursecontext = context_course::instance($this->get_courseid());


            $sectionid = required_param('id', PARAM_INT);
            $format_options = $this->get_format_options((int)$DB->get_field('course_sections', 'section', ['id' => $sectionid]));

            $values = new stdClass();
            $values = file_prepare_standard_filemanager($values,
                'sectioncardbackgroundimage',
                format_topicsactivitycards_cardbackgroundimage_filemanageroptions(),
                $coursecontext,
                'format_topicsactivitycards',
                'sectioncardbackgroundimage',
                $sectionid);

            $values->overridesectionsummary = $format_options['overridesectionsummary_editor'] ?? '';
            $values->overridesectionsummaryformat = FORMAT_HTML;
            $values = file_prepare_standard_editor($values, 'overridesectionsummary', $this->texteditoroptions(), $coursecontext, 'format_topicsactivitycards', 'overridesectionsummary',
                $sectionid);

            $mform->setDefaults((array)$values);
        }

        return $elements;
    }

    public function update_section_format_options($data): bool {
        $context = context_course::instance($this->courseid);
        $data = (object)$data;

        if (isset($data->sectioncardbackgroundimage_filemanager)) {
            file_postupdate_standard_filemanager(
                $data,
                'sectioncardbackgroundimage',
                format_topicsactivitycards_cardbackgroundimage_filemanageroptions(),
                $context,
                'format_topicsactivitycards',
                'sectioncardbackgroundimage',
                $data->id);
            unset($data->sectioncardbackgroundimage_filemanager);
        }

        if (isset($data->overridesectionsummary_editor)) {
            $data = file_postupdate_standard_editor($data, 'overridesectionsummary', $this->texteditoroptions(), $context, 'format_topicsactivitycards',
                'overridesectionsummary', $data->id);
            $data->overridesectionsummary_editor = $data->overridesectionsummary;

            unset($data->overridesectionsummary);
            unset($data->overridesectionsummarytrust);
            unset($data->overridesectionsummaryformat);
        }

        return parent::update_section_format_options($data);
    }

    public function get_view_url($section, $options = array()) {
        if (is_object($section)) {
            $sectionnum = $section->section;
        } else {
            $sectionnum = $section;
        }

        $url = parent::get_view_url($sectionnum, $options);

        if (isset($url)) {
            $format_options = $this->get_format_options($sectionnum);

            if (
                isset($format_options['sectionheading'])
                &&
                $format_options['sectionheading'] == self::SECTIONHEADING_LINKEDCARD
            ) {
                $url->param('section', $sectionnum);
                $url->set_anchor(null);
            }
        }

        return $url;
    }

    public function uses_sections() {
        return true;
    }

    /**
     * @return array
     */
    public function texteditoroptions(): array {
        global $SITE, $CFG;

        require_once("$CFG->dirroot/lib/formslib.php");

        return ['maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $SITE->maxbytes,
            'context' => context_course::instance($this->get_courseid())];
    }

    public function uses_indentation(): bool {
        return false;
    }

    /**
     * Returns the information about the ajax support in the given source format.
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }

    private $cm_metadatas = null;

    public function get_cm_metadatas() {
        global $DB;

        if (isset($this->cm_metadatas)) {
            return $this->cm_metadatas;
        }

        $this->cm_metadatas = [];
        $cm_infos = get_fast_modinfo($this->course)->get_cms();
        if (!empty($cm_infos)) {
            list($insql, $params) = $DB->get_in_or_equal(array_keys($cm_infos), SQL_PARAMS_NAMED);
            $sql = "cmid $insql";
            foreach (metadata::get_records_select($sql, $params) as $metadata) {
                $this->cm_metadatas[$metadata->get('cmid')] = $metadata;
            }
        }

        return $this->cm_metadatas;
    }

    private $cm_cardimages = null;

    public function get_cm_cardimages() {
        if (isset($this->cm_cardimages)) {
            return $this->cm_cardimages;
        }

        $this->cm_cardimages = [];
        $contexts = context_course::instance($this->course->id)->get_child_contexts();
        $contextids = array_keys($contexts);
        $filerecords = $this->get_area_files($contextids, 'format_topicsactivitycards', 'cardbackgroundimage');

        foreach ($filerecords as $file) {
            if ($file->get_filesize() == 0) {
                continue;
            }
            $imageurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(),
                $file->get_filename());
            $imageurl = $imageurl->out();

            $this->cm_cardimages[$contexts[$file->get_contextid()]->instanceid] = $imageurl;
        }

        return $this->cm_cardimages;
    }

    private $section_cardimages = null;

    public function get_section_cardimages() {
        if (isset($this->section_cardimages)) {
            return $this->section_cardimages;
        }

        $this->section_cardimages = [];

        $fs = get_file_storage();
        $filerecords = $fs->get_area_files(context_course::instance($this->course->id)->id, 'format_topicsactivitycards', 'sectioncardbackgroundimage', false, 'itemid', false);

        foreach ($filerecords as $file) {
            if ($file->get_filesize() == 0) {
                continue;
            }
            $imageurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(),
                $file->get_filename());
            $imageurl = $imageurl->out();

            $this->section_cardimages[$file->get_itemid()] = $imageurl;
        }

        return $this->section_cardimages;
    }


    public function get_area_files($contextids, $component, $filearea) {
        global $DB;
        $result = [];

        if (empty($contextids)) {
            return $result;
        }

        $fs = get_file_storage();

        list($contextidsql, $params) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED);
        $params['filearea'] = $filearea;
        $params['component'] = $component;

        $sql = "SELECT " . self::instance_sql_fields('f', 'r') . "
                  FROM {files} f
             LEFT JOIN {files_reference} r
                       ON f.referencefileid = r.id
                 WHERE f.contextid $contextidsql
                       AND f.component = :component
                       AND f.filearea  = :filearea";

        $filerecords = $DB->get_records_sql($sql, $params);
        foreach ($filerecords as $filerecord) {
            $result[$filerecord->pathnamehash] = $fs->get_file_instance($filerecord);
        }
        return $result;
    }

    private static function instance_sql_fields($filesprefix, $filesreferenceprefix) {
        // Note, these fieldnames MUST NOT overlap between the two tables,
        // else problems like MDL-33172 occur.
        $filefields = array('contenthash', 'pathnamehash', 'contextid', 'component', 'filearea',
            'itemid', 'filepath', 'filename', 'userid', 'filesize', 'mimetype', 'status', 'source',
            'author', 'license', 'timecreated', 'timemodified', 'sortorder', 'referencefileid');

        $referencefields = array('repositoryid' => 'repositoryid',
            'reference'    => 'reference',
            'lastsync'     => 'referencelastsync');

        // id is specifically named to prevent overlaping between the two tables.
        $fields = array();
        $fields[] = $filesprefix . '.id AS id';
        foreach ($filefields as $field) {
            $fields[] = "{$filesprefix}.{$field}";
        }

        foreach ($referencefields as $field => $alias) {
            $fields[] = "{$filesreferenceprefix}.{$field} AS {$alias}";
        }

        return implode(', ', $fields);
    }

    public function normalise_render_width($renderwidth) {
        $renderwidth = $renderwidth ?? 4;
        $renderwidthsm = $renderwidth * 2 > 12 ? 12 : $renderwidth * 2;
        $renderwidthxs = $renderwidth * 4 > 12 ? 12 : $renderwidth * 4;
        return "col-$renderwidthxs col-sm-$renderwidthsm col-md-$renderwidth";
    }

    public function get_format_options($section = null) {
        $options = parent::get_format_options($section);

        if ($section !== null) {
            $courseformatoptions = $this->get_format_options(null);

            if ($options['sectionheading'] == self::SECTIONHEADING_COURSEDEFAULT) {
                $options['sectionheading'] = $courseformatoptions['sectionheading'];
            }

            if ($options['sectionlayout'] == self::SECTIONLAYOUT_COURSEDEFAULT) {
                $options['sectionlayout'] = $courseformatoptions['sectionlayout'];
            }
        }

        return $options;
    }
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place.
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return inplace_editable
 */
function format_topicsactivitycards_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
            [$itemid, 'topicsactivitycards'], MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}

function format_topicsactivitycards_cardbackgroundimage_filemanageroptions() {
    global $COURSE;
    return [
        'maxbytes' => $COURSE->maxbytes,
        'subdirs' => 1,
        'accepted_types' => 'image',
        'maxfiles' => 1
    ];
}

function format_topicsactivitycards_showcoursemoduleelements($course, $section) {
    if ($course->format !== 'topicsactivitycards') {
        return false;
    }

    $courseformat = course_get_format($course);
    $sectionoptions = $courseformat->get_format_options((int)$section);
    if ($sectionoptions['sectionlayout'] != \format_topicsactivitycards::SECTIONLAYOUT_CARDS) {
        return false;
    }

    return true;
}

/**
 * Inject the competencies elements into all moodle module settings forms.
 *
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $form The actual form object (required to modify the form).
 */
function format_topicsactivitycards_coursemodule_standard_elements($formwrapper, $form) {
    global $SITE;

    if (!format_topicsactivitycards_showcoursemoduleelements($formwrapper->get_course(), $formwrapper->get_section())) {
        return;
    }

    if ($cm = $formwrapper->get_coursemodule()) {
        $cmid = $cm->id;
        $metadata = metadata::get_record(['cmid' => $cmid]);
    }

    if (empty($metadata)) {
        $metadata = new metadata();
    }

    $form->addElement('header', 'format_topicsactivitycards', get_string('pluginname', 'format_topicsactivitycards'));

    $form->addElement('duration', 'tacduration', get_string('duration', 'format_topicsactivitycards'));
    $form->setDefault('tacduration', 0);
    $form->setType('tacduration', PARAM_INT);

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

    $values->tacduration = $values->duration;
    unset($values->duration);

    $form->setDefaults((array)$values);
}

function format_topicsactivitycards_coursemodule_edit_post_actions($data, $course) {
    global $SITE;

    if (!format_topicsactivitycards_showcoursemoduleelements($course, $data->section)) {
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

    $metadata->set('duration', $data->tacduration);
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

function format_topicsactivitycards_coursemodule_validation($formwrapper, $data) {
    if (!format_topicsactivitycards_showcoursemoduleelements($formwrapper->get_course(), $formwrapper->get_section())) {
        return [];
    }

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
