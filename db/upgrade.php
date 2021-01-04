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
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @package local
 * @subpackage customuserfields
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Steps to take during upgrades
 * @param int $oldversion (optional)
 * @return bool
 */
function xmldb_format_topicsactivitycards_upgrade($oldversion = 0) {
    global $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2020061503) {
        \format_topicsactivitycards\upgradelib::add_custom_user_fields();
        upgrade_plugin_savepoint(true, 2020061503, 'local', 'customuserfields');
    }

    return true;
}
