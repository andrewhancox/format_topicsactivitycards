<?php

namespace format_topicsactivitycards;

class coursehomeheader implements \renderable {
    public $course;
    public $format_options;

    public function __construct(\stdClass $course, $format_options) {
        $this->course = $course;
        $this->format_options = $format_options;
    }
}