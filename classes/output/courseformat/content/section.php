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

use core_courseformat\output\local\content\section as section_base;
use format_topicsactivitycards;
use renderer_base;
use stdClass;

class section extends section_base {

    public function export_for_template(\renderer_base $output): \stdClass {
        $model = parent::export_for_template($output);
        $format = $this->format;
        $section = $this->section;

        $sectionoptions = $this->format->get_format_options($this->section);

        $model->contentcollapsed = !empty($sectionoptions['collapsedefault']);
        $model->layoutcards = $sectionoptions['sectionlayout'] == \format_topicsactivitycards::SECTIONLAYOUT_CARDS;
        $model->hidesummary = $sectionoptions['sectionheading'] != \format_topicsactivitycards::SECTIONHEADING_LINKEDCARD;

        if (!isset($sectionoptions['sectionheading']) || $sectionoptions['sectionheading'] == \format_topicsactivitycards::SECTIONHEADING_HEADER) {
            return $model;
        }

        $model->widthclass = $this->format->normalise_render_width($sectionoptions['renderwidth'] ?? null);
        $model->extraclasses = $sectionoptions['additionalcssclasses'] ?? null;

        $cardimages = $format->get_section_cardimages();
        if (isset($cardimages[$this->section->id])) {
            $model->cardimage = $cardimages[$this->section->id];
        }

        if ($section->uservisible) {
            $model->sectionlink = course_get_url($this->format->get_course(), $section->section);
        }

        if (!empty($sectionoptions['overridesectionsummary_editor']['text'])) {
            $model->summary->summarytext = file_rewrite_pluginfile_urls(
                $sectionoptions['overridesectionsummary_editor']['text'],
                'pluginfile.php',
                $this->section->id,
                'format_topicsactivitycards',
                'overridesectionsummary',
                0
            );

            $model->summary->summarytext = format_text($model->summary->summarytext, $sectionoptions['overridesectionsummary_editor']['format']);
        } else {

            if (!empty($sectionoptions['cleanandtruncatedescription']) && strlen($model->summary->summarytext) > 250) {//width!
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
            $sectionoptions['sectionheading'] != \format_topicsactivitycards::SECTIONHEADING_LINKEDCARD
        ) {
            $hasdata = parent::add_cm_data($data, $output);

            if ($sectionoptions['sectionheading'] == \format_topicsactivitycards::SECTIONHEADING_HEADER) {
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
}