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

namespace format_topicsactivitycards\output\courseformat\content\section;

use core_courseformat\output\local\content\section\cmitem as cmitem_base;

class cmitem extends cmitem_base {
    public function export_for_template(\renderer_base $output): \stdClass {
        $model = parent::export_for_template($output);
        $format = $this->format;

        $sectionoptions = $this->format->get_format_options($this->section);

        if ($format->show_editor() || $sectionoptions['sectionlayout'] != \format_topicsactivitycards::SECTIONLAYOUT_CARDS) {
            return $model;
        }


        $metadatas = $format->get_cm_metadatas();

        $metadata = $metadatas[$this->mod->id] ?? null;

        $model->widthclass = $this->format->normalise_render_width(empty($metadata) ? null : $metadata->get('renderwidth'));

        return $model;
    }
}
