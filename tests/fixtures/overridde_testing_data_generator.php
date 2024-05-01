<?php

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
