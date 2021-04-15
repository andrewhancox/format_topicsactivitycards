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
 * @package local_commerce
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @author Open Source Learning <enquiries@opensourcelearning.co.uk>
 * @link https://opensourcelearning.co.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2021, Andrew Hancox
 */

namespace format_topicsactivitycards;

class metadata extends \core\persistent {
    const TABLE = 'topicsactivitycards_metadata';

    const RENDERWIDTH_NORMAL = 10;
    const RENDERWIDTH_DOUBLE = 20;
    const RENDERWIDTH_FULL = 30;

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return array(
                'renderwidth'       => array(
                        'type'    => PARAM_INT,
                        'default' => 0,
                ),
                'overlaycardimage'       => array(
                        'type'    => PARAM_INT,
                        'default' => 0,
                ),
                'duration'   => array(
                        'type'        => PARAM_INT,
                        'description' => 'duration',
                ),
                'cmid'   => array(
                        'type'        => PARAM_INT,
                        'description' => 'duration',
                )
        );
    }
}