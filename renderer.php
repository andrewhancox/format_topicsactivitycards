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
require_once($CFG->dirroot . '/course/format/topics/renderer.php');

class format_topicsactivitycards_renderer extends format_topics_renderer {

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        $this->courserenderer = new \format_topicsactivitycards\course_renderer($page, $target);
    }

    private $sectionlayoutinprogress = null;
    private $sectioncardsopen = false;

    protected function section_header($section, $course, $onsectionpage, $sectionreturn=null) {
        $format = course_get_format($course);
        $format_options = $format->get_format_options((int)$section->section);
        $this->sectionlayoutinprogress = $format_options['sectionlayout'];
        if ($this->page->user_is_editing() || $this->sectionlayoutinprogress != format_topicsactivitycards::SECTIONLAYOUT_LINKEDCARD || $section->section == $sectionreturn) {
            $sectionoutput = '';

            if ($this->sectioncardsopen) {
                $sectionoutput .= "</ul>";
                $this->sectioncardsopen = false;
            }

            $sectionoutput .= parent::section_header($section, $course, $onsectionpage, $sectionreturn);

            return $sectionoutput;
        }

        $sectionoutput = '';
        if (empty($this->sectioncardsopen)) {
            $sectionoutput .= '<ul class="row format-topicsactivitycards-card-sectioncards img-text section">';
            $this->sectioncardsopen = true;
        }

        $renderwidth = $moddisplayoptions->renderwidth ?? 4;

        $sectionurl = course_get_url($course);
        $sectionurl->param('section', $section->section);


        $fs = get_file_storage();

        $files = $fs->get_area_files(context_course::instance($course->id)->id, 'format_topicsactivitycards', 'sectioncardbackgroundimage', $section->id, 'itemid', false);
        if ($files) {
            $file = reset($files);
            $imageurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(),
                $file->get_filename());
            $imageurl = $imageurl->out();
        } else {
            $imageurl = '';
        }

        $model = [
            'sectionid' => $section->id,
            'widthclass' => "col-12 col-sm-6 col-md-$renderwidth",
            'uservisible' => $section->uservisible,
            'additionalcssclasses' => '',
            'name' => $this->section_title($section, $course),
            'text' => $this->format_summary_text($section),
            'sectionlink' => $section->uservisible ? $sectionurl->out(false) : '',
            'cardimage' => $imageurl,
            'availability' => $this->section_availability($section),
        ];
        $sectionoutput .= $this->render_from_template('format_topicsactivitycards/sectioncard', (object)$model);

        $section->uservisible = false;
        return $sectionoutput;
    }

    protected function section_footer() {
        if ($this->sectionlayoutinprogress != format_topicsactivitycards::SECTIONLAYOUT_LINKEDCARD) {
            $footer = parent::section_footer();
        } else {
            $footer = "";
        }
        $this->sectionlayoutinprogress = null;

        return $footer;
    }

    protected function end_section_list() {
        $sectionoutput = '';

        if ($this->sectioncardsopen) {
            $sectionoutput .= "</ul>";
            $this->sectioncardsopen = false;
        }

        $sectionoutput .= parent::end_section_list();

        return $sectionoutput;
    }
}
