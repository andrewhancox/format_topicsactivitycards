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

namespace format_topicsactivitycards\output\courseformat\content;

use context_course;
use core_courseformat\output\local\content\section as section_base;
use core_media_manager;
use format_topicsactivitycards;
use renderer_base;
use stdClass;

class section extends section_base {

    public function export_for_template(renderer_base $output): stdClass {
        $model = parent::export_for_template($output);
        $format = $this->format;
        $section = $this->section;
        $coursecontext = context_course::instance($this->format->get_courseid());

        $sectionoptions = $this->format->get_format_options($this->section);

        $model->tactagids = [];
        foreach ($this->format->get_tactags() as $tag) {
            if (in_array($this->section->section, $tag->sections)) {
                $model->tactagids[] = $tag->id;
            }
        }

        $model->contentcollapsed = !empty($sectionoptions['collapsedefault']);
        $model->layoutcards = $sectionoptions['sectionlayout'] == format_topicsactivitycards::SECTIONLAYOUT_CARDS;
        $model->hidesummary = $sectionoptions['sectionheading'] != format_topicsactivitycards::SECTIONHEADING_LINKEDCARD;

        if (!empty($this->format->get_section_number())) {
            $model->returntocourselink = course_get_url($this->format->get_course()->id);
            return $model;

        }

        if ($sectionoptions['sectionheading'] == format_topicsactivitycards::SECTIONHEADING_HEADER) {
            return $model;
        }

        if (!empty($this->format->get_section_number()) && $this->format->get_section_number() == $this->section->section) {
            $sectionoptions['renderwidth'] = 12;
        }

        $model->widthclass = $this->format->normalise_render_width($sectionoptions['renderwidth'] ?? null);
        $model->extraclasses = $sectionoptions['additionalcssclasses'] ?? null;

        $cardimages = $format->get_section_cardimages();
        if (isset($cardimages[$this->section->id])) {
            $model->cardimage = $cardimages[$this->section->id];
        }

        if (!empty($sectionoptions['sectioncardbackgroundvideo'])) {
            $model->cardvideo = core_media_manager::instance()->embed_alternatives([new \moodle_url($sectionoptions['sectioncardbackgroundvideo'])]);
        }

        if ($section->uservisible) {
            $model->sectionlink = course_get_url($this->format->get_course(), $section->section);
        }

        if (!empty($sectionoptions['overridesectionsummary'])) {
            $model->summary->summarytext = file_rewrite_pluginfile_urls(
                $sectionoptions['overridesectionsummary'],
                'pluginfile.php',
                $coursecontext->id,
                'format_topicsactivitycards',
                'overridesectionsummary',
                $section->id
            );

            $model->summary->summarytext = format_text($model->summary->summarytext, $sectionoptions['overridesectionsummaryformat']);
        } else {

            if (!empty($sectionoptions['cleanandtruncatedescription']) && strlen($model->summary->summarytext) > 250) {// width!
                $model->summary->summarytext = shorten_text(strip_tags($model->summary->summarytext), 250);
            }
        }

        return $model;
    }

    protected function add_cm_data(stdClass &$data, renderer_base $output): bool {
        $sectionoptions = $this->format->get_format_options($this->section);
        $section = $this->section;
        $format = $this->format;

        if (
            $sectionoptions['sectionheading'] != format_topicsactivitycards::SECTIONHEADING_LINKEDCARD
        ) {
            $hasdata = parent::add_cm_data($data, $output);

            if ($sectionoptions['sectionheading'] == format_topicsactivitycards::SECTIONHEADING_HEADER) {
                return $hasdata;
            }
        }

        $result = false;

        $showsummary = ($section->section != 0 && $section->section != $format->get_section_number());

        $showcmlist = $section->uservisible;

        // Add activities summary if necessary.
        if ($showsummary) {
            $cmsummary = new $this->cmsummaryclass($format, $section);
            $data->cmsummary = $cmsummary->export_for_template($output);
            $data->onlysummary = true;
            $result = true;

            if (!$format->is_section_current($section)) {
                // In multipage, only the current section (and the section zero) has elements.
                $showcmlist = false;
            }
        }
        // Add the cm list.
        if ($showcmlist) {
            $cmlist = new $this->cmlistclass($format, $section);
            $data->cmlist = $cmlist->export_for_template($output);
            $result = true;
        }
        return $result;
    }

    public function get_template_name(renderer_base $renderer): string {
        return "format_topicsactivitycards/local/content/section";
    }
}
