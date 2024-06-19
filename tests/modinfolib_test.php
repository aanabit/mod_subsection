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

namespace tests;

use advanced_testcase;
use core_courseformat\formatactions;

/**
 * Unit tests for lib/modinfolib.php.
 *
 * @package    core
 * @category   phpunit
 * @copyright  2012 Andrew Davis
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * TODO: This test should be moved to /lib/tests/modinfolib_tests.php when integrating mod_subsection to core.
 */
class modinfolib_test extends advanced_testcase {

    /**
     * Test get_sections_delegated_by_cm method
     *
     * @covers \course_modinfo::get_sections_delegated_by_cm
     *
     * TODO: This test should be moved to /lib/tests/modinfolib_tests.php when integrating mod_subsection to core.
     */
    public function test_get_sections_delegated_by_cm(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(['numsections' => 1]);

        $modinfo = get_fast_modinfo($course);
        $delegatedsections = $modinfo->get_sections_delegated_by_cm();
        $this->assertEmpty($delegatedsections);

        // Add a section delegated by a course module.
        $subsection = $this->getDataGenerator()->create_module('subsection', ['course' => $course]);
        $modinfo = get_fast_modinfo($course);
        $delegatedsections = $modinfo->get_sections_delegated_by_cm();
        $this->assertCount(1, $delegatedsections);
        $this->assertArrayHasKey($subsection->cmid, $delegatedsections);

        // Add a section delegated by a block.
        formatactions::section($course)->create_delegated('block_site_main_menu', 1);
        $modinfo = get_fast_modinfo($course);
        $delegatedsections = $modinfo->get_sections_delegated_by_cm();
        // Sections delegated by a block shouldn't be returned.
        $this->assertCount(1, $delegatedsections);
    }
}
