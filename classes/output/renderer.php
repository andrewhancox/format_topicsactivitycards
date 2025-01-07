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

namespace format_topicsactivitycards\output;

use core_course_list_element;
use coursecat_helper;
use format_topics\output\renderer as section_renderer;
use format_topicsactivitycards\coursehomeheader;
use moodle_url;

class renderer extends section_renderer {

    // Override any necessary renderer method here.

    public function render_coursehomeheader(coursehomeheader $renderable) {
        $course = new core_course_list_element($renderable->course);

        $model = new \stdClass();

        foreach ($course->get_course_overviewfiles() as $file) {
            if ($file->is_valid_image()) {
                $model->courseimage = moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    null,
                    $file->get_filepath(),
                    $file->get_filename()
                )->out();
            }
        }

        $model->fullname = $course->get_formatted_fullname();

        if ($course->has_summary()) {
            $chelper = new coursecat_helper();
            $model->coursesummary = $chelper->get_course_formatted_summary($course,
                array('overflowdiv' => true, 'noclean' => true, 'para' => false));
        }

        if (
            !empty($renderable->format_options['showcompletionstate'])
            && $renderable->course->enablecompletion) {
            $model->statuspercentage = number_format(\core_completion\progress::get_course_progress_percentage($renderable->course));
        }

        return $this->render_from_template('format_topicsactivitycards/coursehomeheader', $model);
    }
}
