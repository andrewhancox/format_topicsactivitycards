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

namespace format_topicsactivitycards\output\courseformat;

use core_courseformat\output\local\content as content_base;

class content extends content_base {

    /**
     * Returns the output class template path.
     *
     * This method redirects the default template when the course content is rendered.
     */
    public function get_template_name(\renderer_base $renderer): string {
        return 'format_topicsactivitycards/local/content';
    }

    public function export_for_template(\renderer_base $output) {
        global $PAGE;

        $data = parent::export_for_template($output);

        $formatoptions = $this->format->get_format_options();
        if (empty($formatoptions['section0_onsectionpages']) && !empty($this->format->get_section_number())) {
            unset($data->initialsection);
        }

        $lozenges = $this->format->get_tactags();

        if (empty($data->completionhelp)) {
            $data->completionhelp = '';
        }

        if (!empty($lozenges)) {
            $data->completionhelp .= $output->render_from_template('format_topicsactivitycards/lozenges', ['lozenges' => $lozenges]);
            $PAGE->requires->js_call_amd('format_topicsactivitycards/tactags', 'init');
        }

        return $data;
    }
}