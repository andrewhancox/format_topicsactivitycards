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

function xmldb_format_topicsactivitycards_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2020061515) {
        // Define table message_popup_notifications to be created.
        $table = new xmldb_table('topicsactivitycards_metadata');

        // Adding fields to table message_popup_notifications.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('duration', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Popup savepoint reached.
        upgrade_plugin_savepoint(true, 2020061515, 'format', 'topicsactivitycards');
    }

    if ($oldversion < 2020061516) {
        $table = new xmldb_table('topicsactivitycards_metadata');
        $field = new xmldb_field('renderwidth', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, 0, 10);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2020061516, 'format', 'topicsactivitycards');
    }

    if ($oldversion < 2020061530) {
        $table = new xmldb_table('topicsactivitycards_metadata');
        $field = new xmldb_field('overlaycardimage', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, 0, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2020061530, 'format', 'topicsactivitycards');
    }

    if ($oldversion < 2020061542) {
        $fs = new file_storage();
        $filestoshiftcontext = $DB->get_records('files', ['component' => 'format_topicsactivitycards']);
        foreach ($filestoshiftcontext as $file) {
            $fileobj = $fs->get_file_by_id($file->id);
            $filerecord = new stdClass();
            $filerecord->itemid = 0;
            $fs->create_file_from_storedfile($filerecord, $fileobj);
            $fileobj->delete();
        }

        upgrade_plugin_savepoint(true, 2020061542, 'format', 'topicsactivitycards');
    }

    if ($oldversion < 2020061543) {
        $table = new xmldb_table('topicsactivitycards_metadata');
        $field = new xmldb_field('cleanandtruncatedescription', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, 0, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2020061543, 'format', 'topicsactivitycards');
    }

    if ($oldversion < 2020061544) {
        $table = new xmldb_table('topicsactivitycards_metadata');

        $fields = [
            new xmldb_field('activitydescription', XMLDB_TYPE_TEXT, null, null, null, null, null),
            new xmldb_field('activitydescriptionformat', XMLDB_TYPE_INTEGER, '10', null, null, null, 1),
        ];

        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_plugin_savepoint(true, 2020061544, 'format', 'topicsactivitycards');
    }

    if ($oldversion < 2020061545) {
        $table = new xmldb_table('topicsactivitycards_metadata');

        $field = new xmldb_field('additionalcssclasses', XMLDB_TYPE_TEXT, null, null, null, null, null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2020061545, 'format', 'topicsactivitycards');
    }

    if ($oldversion < 2020061549) {
        $table = new xmldb_table('topicsactivitycards_metadata');

        $fields = [
            new xmldb_field('cardfooter', XMLDB_TYPE_TEXT, null, null, null, null, null),
            new xmldb_field('cardfooterformat', XMLDB_TYPE_INTEGER, '10', null, null, null, 1),
        ];

        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_plugin_savepoint(true, 2020061549, 'format', 'topicsactivitycards');
    }

    if ($oldversion < 2020061550) {
        foreach ([
                     'update {topicsactivitycards_metadata} set renderwidth = 4 where renderwidth = 10',
                     'update {topicsactivitycards_metadata} set renderwidth = 6 where renderwidth = 20',
                     'update {topicsactivitycards_metadata} set renderwidth = 12  where renderwidth = 30',
                     'update {topicsactivitycards_metadata} set renderwidth = 8 where renderwidth = 40',
                     'update {topicsactivitycards_metadata} set renderwidth = 3 where renderwidth = 50',
                     'update {topicsactivitycards_metadata} set renderwidth = 9 where renderwidth = 60',
                 ] as $sql) {
            $DB->execute($sql);
        }

        upgrade_plugin_savepoint(true, 2020061550, 'format', 'topicsactivitycards');
    }

    if ($oldversion < 2023071103) {
        $table = new xmldb_table('topicsactivitycards_metadata');

        $fields = [
            new xmldb_field('tactags', XMLDB_TYPE_TEXT, null, null, null, null, null),
        ];

        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_plugin_savepoint(true, 2023071103, 'format', 'topicsactivitycards');
    }

    if ($oldversion < 2023071104) {
        $DB->execute("update {course_format_options}
                            set name = 'overridesectionsummary'
                            where name = 'overridesectionsummary_editor'");
        upgrade_plugin_savepoint(true, 2023071104, 'format', 'topicsactivitycards');
    }

    return true;
}
