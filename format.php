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

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/completionlib.php');

// Retrieve course format option fields and add them to the $course object.
$format = course_get_format($course);
$course = $format->get_course();
$context = context_course::instance($course->id);

// Add any extra logic here.

$renderer = $format->get_renderer($PAGE);

// If we're not going to a specific section then see if we've been referred here by a CM
// If so then go to that one.
if (empty($displaysection)) {
    $referer = get_local_referer(false);

    if (!empty($referer)) {
        $refererurl = new moodle_url($referer);
        $path = $refererurl->get_path();

        if (strpos($path, '/mod/') === 0 && strpos(strrev($path), 'php.weiv/') === 0 && !empty($refererurl->get_param('id'))) {
            $cmid = $refererurl->get_param('id');
        } else if (!empty($refererurl->get_param('cmid'))) {
            $cmid = $refererurl->get_param('cmid');
        }
        $cms = get_fast_modinfo($course)->get_cms();

        if (!empty($cms[$cmid])) {
            $redirectingcm = $cms[$cmid];
            $sectionoptions = $format->get_format_options($redirectingcm->sectionnum);

            if ($sectionoptions['sectionheading'] == \format_topicsactivitycards::SECTIONHEADING_LINKEDCARD) {
                $displaysection = $redirectingcm->sectionnum;
            }
        }
    }
}

if (!empty($displaysection)) {
    $format->set_section_number($displaysection);
}

// Output course content.
$outputclass = $format->get_output_classname('content');
$widget = new $outputclass($format);
echo $renderer->render($widget);

// Include any format js module here using $PAGE->requires->js.
