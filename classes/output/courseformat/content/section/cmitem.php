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

namespace format_topicsactivitycards\output\courseformat\content\section;

use core_courseformat\output\local\content\section\cmitem as cmitem_base;
use core_tag_tag;

class cmitem extends cmitem_base {
    public function export_for_template(\renderer_base $output): \stdClass {
        $model = parent::export_for_template($output);
        $format = $this->format;

        $sectionoptions = $this->format->get_format_options($this->section);

        $model->layoutlist = $sectionoptions['sectionlayout'] == \format_topicsactivitycards::SECTIONLAYOUT_LIST;

        if ($model->layoutlist) {
            return $model;
        }

        $metadatas = $format->get_cm_metadatas();

        $metadata = $metadatas[$this->mod->id] ?? null;

        $model->widthclass = $this->format->normalise_render_width(empty($metadata) ? null : $metadata->get('renderwidth'));

        if (!empty($metadata)) {
            if (empty($metadata->get('activitydescription'))) {
                // For none label activities, strip html from the description.
                if (!empty($metadata->get('cleanandtruncatedescription')) && strlen($model->cmformat->altcontent) > 1000) {//width!
                    $model->cmformat->altcontent = shorten_text(strip_tags($model->cmformat->altcontent), 1000);
                }
            } else {
                $model->cmformat->altcontent = file_rewrite_pluginfile_urls(
                    $metadata->get('activitydescription'),
                    'pluginfile.php',
                    $this->mod->context->id,
                    'format_topicsactivitycards',
                    'activitydescription',
                    0
                );

                $model->cmformat->hasname = true;
                $model->cmformat->altcontent = format_text($model->cmformat->altcontent, $metadata->get('activitydescriptionformat'));
            }

            if (!empty($metadata->get('cardfooter'))) {
                $model->cardfooter = file_rewrite_pluginfile_urls(
                    $metadata->get('cardfooter'),
                    'pluginfile.php',
                    $this->mod->context->id,
                    'format_topicsactivitycards',
                    'cardfooter',
                    0
                );

                $model->cardfooter = format_text($model->cardfooter, $metadata->get('cardfooterformat'));
            }

            $model->duration = $metadata->formattedduration();
            $model->extraclasses = $metadata->get('additionalcssclasses');
        }

        $cardimages = $format->get_cm_cardimages();
        if (isset($cardimages[$this->mod->id])) {
            $model->cardimage = $cardimages[$this->mod->id];
        }

        $model->taglist = $output->tag_list(core_tag_tag::get_item_tags('core', 'course_modules', $this->mod->id));

        return $model;
    }
}
