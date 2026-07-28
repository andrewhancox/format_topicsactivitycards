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

define(['jquery'], function($) {
    var tacfullscreen = {
        init: function() {
            const oldfn = window.document.getElementById("page-wrapper").addEventListener;

            $('.tacfullscreen').on('click', function() {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                    window.document.getElementById("page-wrapper").addEventListener = oldfn;
                    $('#page').toggleClass('mt-0', false);
                    $('nav.navbar').toggleClass('d-none', false);
                } else {
                    $('#page-wrapper').toggleClass('bg-white', true);
                    $('#page').toggleClass('mt-0', true);
                    $('nav.navbar').toggleClass('d-none', true);
                    document.getElementById("page-wrapper").requestFullscreen();
                    $('.btn.drawertoggle[data-action="closedrawer"]:not(.hidden)').trigger('click');
                    window.document.getElementById("page-wrapper").addEventListener = function() {
                    };
                }
            });
        },
    };

    return tacfullscreen;
});
