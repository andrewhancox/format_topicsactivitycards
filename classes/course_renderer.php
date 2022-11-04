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

namespace format_topicsactivitycards;

use CFPropertyList\PListException;
use cm_info;
use completion_info;
use context_course;
use core_tag_tag;
use format_base;
use format_topicsactivitycards;
use html_writer;
use moodle_page;
use moodle_url;
use pix_icon;
use section_info;
use stdClass;

class course_renderer extends \core_course_renderer {
    /**
     * @var format_base
     */
    private $format;

    public function __construct(moodle_page $page, $target) {
        $this->format = course_get_format($page->course->id);

        parent::__construct($page, $target);
    }

    /**
     * Renders HTML to display a list of course modules in a course section
     * Also displays "move here" controls in Javascript-disabled mode
     *
     * This function calls {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course course object
     * @param int|stdClass|section_info $section relative section number or section object
     * @param int $sectionreturn section number to return to
     * @param int $displayoptions
     * @return String
     */
    public function course_section_cm_list($course, $section, $sectionreturn = null, $displayoptions = array()) {
        global $USER, $DB, $PAGE;

        $displayoptions['metadatas'] = [];
        $displayoptions['cardimages'] = [];

        $sectionlayout = $this->format->get_format_options($section)['sectionlayout'];
        if ($sectionlayout == format_topicsactivitycards::SECTIONLAYOUT_LIST) {
            return parent::course_section_cm_list($course, $section, $sectionreturn, $displayoptions);
        }

        $output = '';
        $modinfo = get_fast_modinfo($course);
        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new completion_info($course);

        // check if we are currently in the process of moving a module with JavaScript disabled
        $ismoving = $this->page->user_is_editing() && ismoving($course->id);
        if ($ismoving) {
            $movingpix = new pix_icon('movehere', get_string('movehere'), 'moodle', array('class' => 'movetarget'));
            $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }
        $cm_infos = $modinfo->get_cms();
        if (!empty($cm_infos)) {
            list($insql, $params) = $DB->get_in_or_equal(array_keys($cm_infos), SQL_PARAMS_NAMED);
            $sql = "cmid $insql";
            foreach (metadata::get_records_select($sql, $params) as $metadata) {
                $displayoptions['metadatas'][$metadata->get('cmid')] = $metadata->to_record();
            }
        }

        $contexts = context_course::instance($course->id)->get_child_contexts();
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

            $displayoptions['cardimages'][$file->get_contextid()] = $imageurl;
        }

        // Get the list of modules visible to user (excluding the module being moved if there is one)
        $moduleshtml = array();
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];

                if ($ismoving and $mod->id == $USER->activitycopy) {
                    // do not display moving mod
                    continue;
                }

                if ($modulehtml = $this->course_section_cm_list_item($course,
                        $completioninfo, $mod, $sectionreturn, $displayoptions)) {
                    $moduleshtml[$modnumber] = $modulehtml;
                }
            }
        }

        $classes = '';
        if (!empty($course->collapsible) && $section->section != 0) {
            $classes .= ' collapse';
            $preferencename = "format-topicsactivitycards-card-section-open_$section->id";
            user_preference_allow_ajax_update($preferencename, PARAM_BOOL);

            if (!empty(get_user_preferences($preferencename))) {
                $classes .= ' show';
            }
            $PAGE->requires->js_call_amd('format_topicsactivitycards/collapse', 'init', [
                'sectionid' => $section->section,
                'preferencename' => $preferencename
            ]);
        }

        $sectionoutput = '';
        $sectionoutput .= '<ul class="row format-topicsactivitycards-card-section img-text section ' . $classes . ' " data-draggroups="resource">';
        if (!empty($moduleshtml) || $ismoving) {
            foreach ($moduleshtml as $modnumber => $modulehtml) {
                if ($ismoving) {
                    $movingurl = new moodle_url('/course/mod.php', array('moveto' => $modnumber, 'sesskey' => sesskey()));
                    $sectionoutput .= html_writer::tag('li',
                            html_writer::link($movingurl, $this->output->render($movingpix), array('title' => $strmovefull)),
                            array('class' => 'movehere'));
                }

                $sectionoutput .= $modulehtml;
            }

            if ($ismoving) {
                $movingurl = new moodle_url('/course/mod.php', array('movetosection' => $section->id, 'sesskey' => sesskey()));
                $sectionoutput .= html_writer::tag('li',
                        html_writer::link($movingurl, $this->output->render($movingpix), array('title' => $strmovefull)),
                        array('class' => 'movehere'));
            }
            $sectionoutput .= "</ul>";
        }

        // Always output the section module list.
        $output .= $sectionoutput;

        return $output;
    }

    /**
     * Renders HTML to display one course module for display within a section.
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return String
     */
    public function course_section_cm_list_item($course, &$completioninfo, cm_info $mod, $sectionreturn,
            $displayoptions = array()) {

        $sectionlayout = $this->format->get_format_options((int) $mod->sectionnum)['sectionlayout'];
        if ($sectionlayout == \format_topicsactivitycards::SECTIONLAYOUT_LIST) {
            return parent::course_section_cm_list_item($course, $completioninfo, $mod, $sectionreturn, $displayoptions);
        }

        $output = '';
        if ($modulehtml = $this->course_section_cm($course, $completioninfo, $mod, $sectionreturn, $displayoptions)) {
            $output = $modulehtml;
        }
        return $output;
    }

    /**
     * Renders HTML to display one course module for display within a section.
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return String
     */
    public function course_section_cm($course, &$completioninfo, cm_info $mod, $sectionreturn,
            $displayoptions = array()) {
        global $PAGE;

        $sectionlayout = $this->format->get_format_options((int) $mod->sectionnum)['sectionlayout'];
        if ($sectionlayout == format_topicsactivitycards::SECTIONLAYOUT_LIST) {
            return parent::course_section_cm($course, $completioninfo, $mod, $sectionreturn, $displayoptions);
        }

        $unstyledmodules = ['label'];

        if (!$mod->is_visible_on_course_page()) {
            return '';
        }

        $template = new stdClass();
        $template->mod = $mod;
        $template->name = format_string($mod->name);

        if (in_array($mod->modname, $unstyledmodules)) {
            $template->unstyled = true;
        }

        $template->cmlink = $mod->url;
        $onclick = $mod->onclick;
        if (!empty($onclick)) {
            $template->onclick = $onclick;
        }

        if (isset($displayoptions['metadatas'][$mod->id])) {
            $moddisplayoptions = $displayoptions['metadatas'][$mod->id];
        }


        if (empty($moddisplayoptions->activitydescription)) {
            $template->text = $mod->get_formatted_content(array('overflowdiv' => false, 'noclean' => true));
            // For none label activities, strip html from the description.
            if (!empty($moddisplayoptions->cleanandtruncatedescription)) {//width!
                $template->text = shorten_text(strip_tags($template->text), 1000);
            }
        } else {

            $template->text = file_rewrite_pluginfile_urls(
                $moddisplayoptions->activitydescription,
                'pluginfile.php',
                $mod->context->id,
                'format_topicsactivitycards',
                'activitydescription',
                0
            );

            $template->text = format_text($template->text, $moddisplayoptions->activitydescriptionformat);
        }

        if (!empty($moddisplayoptions->cardfooter)) {
            $template->cardfooter = file_rewrite_pluginfile_urls(
                $moddisplayoptions->cardfooter,
                'pluginfile.php',
                $mod->context->id,
                'format_topicsactivitycards',
                'cardfooter',
                0
            );

            $template->cardfooter = format_text($template->cardfooter, $moddisplayoptions->cardfooterformat);
        }

        $template->completion = $this->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions);
        $template->cmname = $this->course_section_cm_name($mod, $displayoptions);
        $template->editing = $PAGE->user_is_editing();
        $template->availability = $this->course_section_cm_availability($mod, $displayoptions);

        if ($PAGE->user_is_editing()) {
            $editactions = course_get_cm_edit_actions($mod, $mod->indent, $sectionreturn);
            $template->editoptions = $this->course_section_cm_edit_actions($editactions, $mod, $displayoptions);
            $template->editoptions .= $mod->afterediticons;
            $template->moveicons = course_get_cm_move($mod, $sectionreturn);
        }

        if (!empty($moddisplayoptions)) {
            $template->additionalcssclasses = $moddisplayoptions->additionalcssclasses;

            $class = 'tac-time-unit small';
            $str = new stdClass();
            $str->day = html_writer::span(get_string('day'), $class);
            $str->days = html_writer::span(get_string('days'), $class);
            $str->hour = html_writer::span(get_string('hour'), $class);
            $str->hours = html_writer::span(get_string('hours'), $class);
            $str->min = html_writer::span(get_string('min'), $class);
            $str->mins = html_writer::span(get_string('mins'), $class);
            $str->sec = html_writer::span(get_string('sec'), $class);
            $str->secs = html_writer::span(get_string('secs'), $class);
            $str->year = html_writer::span(get_string('year'), $class);
            $str->years = html_writer::span(get_string('years'), $class);

            $totalsecs = $moddisplayoptions->duration;
            if (!empty($totalsecs)) {
                $template->duration = format_time($totalsecs, $str);
            }
        }
        $renderwidth = $moddisplayoptions->renderwidth ?? 4;
        $template->widthclass = "col-12 col-sm-6 col-md-$renderwidth col-xl-4";

        if (!empty($displayoptions['cardimages'][$mod->context->id])) {
            $template->cardimage = $displayoptions['cardimages'][$mod->context->id];
        }

        $template->taglist = $this->output->tag_list(core_tag_tag::get_item_tags('core', 'course_modules', $mod->id));

        // Show the header by default.
        $template->showheader = true;
        // Labels can have no header when they have no image or duration.
        if ($mod->modname === 'label') {
            $template->showheader = (!empty($template->cardimage) || !empty($template->duration));
        }

        if (empty($moddisplayoptions->overlaycardimage)) {
            $templatename = 'format_topicsactivitycards/coursemodule';
        } else {
            $templatename = 'format_topicsactivitycards/coursemoduleoverlay';
        }
        return $this->render_from_template($templatename, $template);
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
}
