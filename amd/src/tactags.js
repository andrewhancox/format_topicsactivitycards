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
 * @author Open Source Learning <enquiries@opensourcelearning.co.uk>
 * @link https://opensourcelearning.co.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2021, Andrew Hancox
 */

define(['jquery'], function ($) {
    return {
        init: function () {
            $('body').on('click', '.tactaglozenge', function (e) {
                e.preventDefault();

                var sender = $(e.target);
                var tagid = sender.data('tagid');

                if (sender.hasClass('badge-success')) {
                    sender.toggleClass('badge-success', false);
                    sender.toggleClass('badge-info', true);

                    if ($('.tactaglozenge.badge-success').length) { //If there is a tag still selected then hide de-selected option.
                        $('[data-tactag-' + tagid + '="1"]').toggleClass('d-none', true);
                    } else { //Otherwise show everything.
                        $('.tactaggable').toggleClass('d-none', false);
                    }
                } else {
                    if (!$('.tactaglozenge.badge-success').length) { //If there is no other tag selected then hide everything first.
                        $('.tactaggable').toggleClass('d-none', true);
                    }

                    sender.toggleClass('badge-success', true);
                    sender.toggleClass('badge-info', false);

                    $('[data-tactag-' + tagid + '="1"]').parents('.tactaggable').toggleClass('d-none', false); // If we're showing an activity then make sure the wrapping section is visible.
                    $('[data-tactag-' + tagid + '="1"]').children('.tactaggable').toggleClass('d-none', false); // If we're showing a section then show all activities.
                    $('[data-tactag-' + tagid + '="1"]').toggleClass('d-none', false);
                }
            });

            $('.tactaglozengeselected').trigger('click');
        }
    };
});
