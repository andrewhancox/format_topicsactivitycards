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

defined('MOODLE_INTERNAL') || die();

class upgradelib {

    /**
     * Set up the custom user fields that the plugin relies on.
     *
     * @throws \coding_exception
     */
    public static function add_custom_user_fields() {
        global $DB;

        set_config('metadataenabled', true, 'metadatacontext_cohort');

        $categories = $DB->get_records('user_info_category', ['name' => get_string('pluginname', 'format_topicsactivitycards')],
                'sortorder ASC');

        // Check that we have at least one category defined.
        if (empty($categories)) {
            $defaultcategory = new \stdClass();
            $defaultcategory->name = get_string('pluginname', 'format_topicsactivitycards');
            $defaultcategory->sortorder = (1 + $DB->get_field('local_metadata_category', 'max(sortorder)', []));
            $defaultcategory->contextlevel = CONTEXT_MODULE;
            $defaultcategory->id = $DB->insert_record('local_metadata_category', $defaultcategory);
        } else {
            $defaultcategory = reset($categories);
        }

        $fields = [
                [
                        'shortname'          => 'cardimage',
                        'name'               => get_string('cardimage', 'format_topicsactivitycards'),
                        'contextlevel'       => CONTEXT_MODULE,
                        'datatype'           => 'file',
                        'categoryid'         => $defaultcategory->id,
                        'description'        => get_string('cardimagedescription', 'format_topicsactivitycards'),
                        'descriptionformate' => FORMAT_MOODLE,
                        'visible'            => 2,
                        'param1'             => 1,
                        'param2'             => 0
                ],
                [
                        'shortname'          => 'duration',
                        'name'               => get_string('duration', 'format_topicsactivitycards'),
                        'contextlevel'       => CONTEXT_MODULE,
                        'datatype'           => 'duration',
                        'categoryid'         => $defaultcategory->id,
                        'description'        => '',
                        'descriptionformate' => FORMAT_MOODLE,
                        'visible'            => 2,
                        'param1'             => ''
                ],
        ];

        foreach ($fields as $field) {
            if (!$DB->record_exists('local_metadata_field', array('shortname' => $field['shortname']))) {
                $field['sortorder'] =
                        (1 + $DB->get_field('local_metadata_field', 'max(sortorder)', ['categoryid' => $defaultcategory->id]));
                $field['id'] = $DB->insert_record('local_metadata_field', (object) $field);
            }
        }
    }
}
