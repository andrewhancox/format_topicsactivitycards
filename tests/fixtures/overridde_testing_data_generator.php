<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace format_topicsactivitycards\fixtures;

use testing_data_generator;

require_once("$CFG->dirroot/lib/testing/generator/data_generator.php");
class overridde_testing_data_generator extends testing_data_generator {
    public function create_course($record=null, array $options=null) {

        $record = (array)$record;

        if (isset($record['format']) && $record['format'] == 'topics') {
            $record['format'] = 'topicsactivitycards';
        }
        return parent::create_course($record, $options);
    }
}
