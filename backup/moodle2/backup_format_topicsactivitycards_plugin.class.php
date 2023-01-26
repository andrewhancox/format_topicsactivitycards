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

class backup_format_topicsactivitycards_plugin extends backup_format_plugin {

    /**
     * Returns the format information to attach to course element
     */
    protected function define_module_plugin_structure() {
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element();

        // Create one standard named plugin element (the visible container).
        // The courseid not required as populated on restore.
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());
        $metadatarecords = new backup_nested_element('metadata', ['cmid'], ['duration', 'renderwidth', 'overlaycardimage', 'cleanandtruncatedescription', 'additionalcssclasses']);

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);
        $pluginwrapper->add_child($metadatarecords);

        // Set source to populate the data.
        $metadatarecords->set_source_sql('select meta.* 
                                            from {topicsactivitycards_metadata} meta
                                             where meta.cmid = :cmid', [
                'cmid' => backup::VAR_MODID]);

        $metadatarecords->annotate_files('format_topicsactivitycards', 'cardbackgroundimage', null, $this->task->get_contextid());

        return $plugin;
    }

    protected function define_course_plugin_structure() {
        $plugin = $this->get_plugin_element();

        $pluginwrapper = new backup_nested_element($this->get_recommended_name());
        $pluginwrapper->annotate_files('format_topicsactivitycards', 'sectioncardbackgroundimage', null, $this->task->get_contextid());

        $plugin->add_child($pluginwrapper);

        return $plugin;
    }
}
