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

use html_writer;
use stdClass;

class metadata extends \core\persistent {
    const TABLE = 'topicsactivitycards_metadata';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return array(
                'renderwidth'       => array(
                        'type'    => PARAM_INT,
                        'default' => 4,
                ),
                'overlaycardimage'       => array(
                        'type'    => PARAM_INT,
                        'default' => 0,
                ),
                'cleanandtruncatedescription' => array(
                    'type' => PARAM_INT,
                    'default' => 0,
                ),
                'duration'   => array(
                        'type'        => PARAM_INT,
                        'description' => 'duration',
                ),
                'cmid'   => array(
                        'type'        => PARAM_INT,
                        'description' => 'duration',
                ),
                'activitydescription' => array(
                    'type' => PARAM_RAW,
                    'description' => 'The product description.',
                    'null' => NULL_ALLOWED,
                    'default' => '',
                ),
                'activitydescriptionformat' => array(
                    'choices' => array(FORMAT_HTML, FORMAT_MOODLE, FORMAT_PLAIN, FORMAT_MARKDOWN),
                    'type' => PARAM_INT,
                    'default' => FORMAT_HTML
                ),
                'cardfooter' => array(
                    'type' => PARAM_RAW,
                    'description' => 'The product description.',
                    'null' => NULL_ALLOWED,
                    'default' => '',
                ),
                'cardfooterformat' => array(
                    'choices' => array(FORMAT_HTML, FORMAT_MOODLE, FORMAT_PLAIN, FORMAT_MARKDOWN),
                    'type' => PARAM_INT,
                    'default' => FORMAT_HTML
                ),
                'additionalcssclasses' => array(
                    'type' => PARAM_TEXT,
                ),
        );
    }

    public function formattedduration() {
        if (empty($this->get('duration'))) {
            return '';
        }

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

        return format_time($this->get('duration'), $str);
    }
}
