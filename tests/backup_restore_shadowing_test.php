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
 * @link https://opensourcelearning.co.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2026
 */

namespace format_topicsactivitycards;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

/**
 * Regression tests for the course-format backup optigroup shadowing.
 *
 * @package format_topicsactivitycards
 * @covers \backup_format_topicsactivitycards_plugin
 */
final class backup_restore_shadowing_test extends \advanced_testcase {

    /**
     * Back a course up in import mode (uncompressed directory kept on disk for inspection).
     *
     * @param \stdClass $course the course to back up
     * @return string the backup id
     */
    private function backup_course(\stdClass $course): string {
        global $USER;

        $bc = new \backup_controller(
            \backup::TYPE_1COURSE,
            $course->id,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_IMPORT,
            $USER->id
        );
        $backupid = $bc->get_backupid();
        $bc->execute_plan();
        $bc->destroy();
        return $backupid;
    }

    /**
     * Restore a backup into a brand new course.
     *
     * @param string $backupid the backup id from backup_course()
     * @param \stdClass $course the original course (name/category reused)
     * @return int the new course id
     */
    private function restore_into_new(string $backupid, \stdClass $course): int {
        global $USER;

        $newcourseid = \restore_dbops::create_new_course(
            $course->fullname . ' copy',
            $course->shortname . '_copy' . substr($backupid, 0, 4),
            $course->category
        );
        $rc = new \restore_controller(
            $backupid,
            $newcourseid,
            \backup::INTERACTIVE_NO,
            \backup::MODE_IMPORT,
            $USER->id,
            \backup::TARGET_NEW_COURSE
        );
        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();
        return $newcourseid;
    }

    /**
     * Duplicate a course through the real backup/restore pipeline.
     *
     * @param \stdClass $course the course to duplicate
     * @return int the new course id
     */
    private function backup_and_restore(\stdClass $course): int {
        return $this->restore_into_new($this->backup_course($course), $course);
    }

    /**
     * A course using a different installed format must not have its own course-format backup
     * branch shadowed by our (previously unconditioned) course-level plugin element.
     */
    public function test_course_branch_not_attached_for_other_formats(): void {
        global $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();

        $othercourse = $this->getDataGenerator()->create_course(
            ['format' => 'topics', 'numsections' => 1]
        );

        $backupid = $this->backup_course($othercourse);
        $coursexml = file_get_contents(
            $CFG->tempdir . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . $backupid
                . DIRECTORY_SEPARATOR . 'course' . DIRECTORY_SEPARATOR . 'course.xml'
        );

        $this->assertStringNotContainsString(
            'plugin_format_topicsactivitycards_course',
            $coursexml,
            'format_topicsactivitycards must not attach its course-level branch to a backup of '
                . 'a course using a different format, or it will shadow that format\'s own branch '
                . 'in the (first-match-wins) course-format optigroup.'
        );
    }

    /**
     * Regression safety net for the fix above: a topicsactivitycards course must still back up
     * and restore its own metadata and card images correctly.
     */
    public function test_own_course_roundtrip_preserves_metadata_and_images(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course(
            ['format' => 'topicsactivitycards', 'numsections' => 1]
        );
        $page = $this->getDataGenerator()->create_module('page', ['course' => $course->id, 'section' => 1]);

        $DB->insert_record('topicsactivitycards_metadata', (object) [
            'cmid' => $page->cmid,
            'duration' => 90,
            'renderwidth' => 4,
            'cleanandtruncatedescription' => 1,
            'overlaycardimage' => 0,
        ]);

        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
        );
        get_file_storage()->create_file_from_string([
            'contextid' => \context_module::instance($page->cmid)->id,
            'component' => 'format_topicsactivitycards',
            'filearea' => 'cardbackgroundimage',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'card.png',
        ], $png);

        $newcourseid = $this->backup_and_restore($course);

        $newmodinfo = get_fast_modinfo($newcourseid);
        $newpages = $newmodinfo->get_instances_of('page');
        $newpage = reset($newpages);

        $metadata = $DB->get_record('topicsactivitycards_metadata', ['cmid' => $newpage->id]);
        $this->assertNotEmpty($metadata, 'Activity card metadata must survive a backup/restore round trip.');
        $this->assertEquals(90, $metadata->duration);

        $files = get_file_storage()->get_area_files(
            \context_module::instance($newpage->id)->id,
            'format_topicsactivitycards',
            'cardbackgroundimage',
            0,
            'filename',
            false
        );
        $this->assertNotEmpty($files, 'Activity card background image must survive a backup/restore round trip.');
    }
}
