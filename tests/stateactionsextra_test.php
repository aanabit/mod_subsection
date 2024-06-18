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

namespace core_courseformat;

use ReflectionMethod;
use course_modinfo;

/**
 * Tests for stateactions using the subsection module.
 *
 * All this file must be moved to core as part of MDL-81765.
 *
 * @package    mod_subsection
 * @category   test
 * @copyright  2024 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class stateactionsextra_test extends \advanced_testcase {
    /**
     * Test that set_cm_indentation on activities with a delegated section.
     *
     * @covers ::set_cm_indentation
     */
    public function test_set_cm_indentation_delegated_section() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $subsection = $this->getDataGenerator()->create_module('subsection', ['course' => $course]);
        $otheractvity = $this->getDataGenerator()->create_module('forum', ['course' => $course]);
        $this->setAdminUser();

        // Initialise stateupdates.
        $courseformat = course_get_format($course->id);

        // Execute given method.
        $updates = new stateupdates($courseformat);
        $actions = new stateactions();
        $actions->cm_moveright(
            $updates,
            $course,
            [$subsection->cmid, $otheractvity->cmid],
        );

        // Format results in a way we can compare easily.
        $results = $this->summarize_updates($updates);

        // The state actions does not use create or remove actions because they are designed
        // to refresh parts of the state.
        $this->assertEquals(0, $results['create']['count']);
        $this->assertEquals(0, $results['remove']['count']);

        // Mod subsection should be ignored.
        $this->assertEquals(1, $results['put']['count']);

        // Validate course, section and cm.
        $this->assertArrayHasKey($otheractvity->cmid, $results['put']['cm']);
        $this->assertArrayNotHasKey($subsection->cmid, $results['put']['cm']);

        // Validate activity indentation.
        $mondinfo = get_fast_modinfo($course);
        $this->assertEquals(1, $mondinfo->get_cm($otheractvity->cmid)->indent);
        $this->assertEquals(1, $DB->get_field('course_modules', 'indent', ['id' => $otheractvity->cmid]));
        $this->assertEquals(0, $mondinfo->get_cm($subsection->cmid)->indent);
        $this->assertEquals(0, $DB->get_field('course_modules', 'indent', ['id' => $subsection->cmid]));

        // Now move left.
        $updates = new stateupdates($courseformat);
        $actions->cm_moveleft(
            $updates,
            $course,
            [$subsection->cmid, $otheractvity->cmid],
        );

        // Format results in a way we can compare easily.
        $results = $this->summarize_updates($updates);

        // The state actions does not use create or remove actions because they are designed
        // to refresh parts of the state.
        $this->assertEquals(0, $results['create']['count']);
        $this->assertEquals(0, $results['remove']['count']);

        // Mod subsection should be ignored.
        $this->assertEquals(1, $results['put']['count']);

        // Validate course, section and cm.
        $this->assertArrayHasKey($otheractvity->cmid, $results['put']['cm']);
        $this->assertArrayNotHasKey($subsection->cmid, $results['put']['cm']);

        // Validate activity indentation.
        $mondinfo = get_fast_modinfo($course);
        $this->assertEquals(0, $mondinfo->get_cm($otheractvity->cmid)->indent);
        $this->assertEquals(0, $DB->get_field('course_modules', 'indent', ['id' => $otheractvity->cmid]));
        $this->assertEquals(0, $mondinfo->get_cm($subsection->cmid)->indent);
        $this->assertEquals(0, $DB->get_field('course_modules', 'indent', ['id' => $subsection->cmid]));
    }

    /**
     * Generate a sorted and summarized list of an state updates message.
     *
     * It is important to note that the order in the update messages are not important in a real scenario
     * because each message affects a specific part of the course state. However, for the PHPUnit test
     * have them sorted and classified simplifies the asserts.
     *
     * @param stateupdates $updateobj the state updates object
     * @return array of all data updates.
     */
    private function summarize_updates(stateupdates $updateobj): array {
        // Check state returned after executing given action.
        $updatelist = $updateobj->jsonSerialize();

        // Initial summary structure.
        $result = [
            'create' => [
                'course' => [],
                'section' => [],
                'cm' => [],
                'count' => 0,
            ],
            'put' => [
                'course' => [],
                'section' => [],
                'cm' => [],
                'count' => 0,
            ],
            'remove' => [
                'course' => [],
                'section' => [],
                'cm' => [],
                'count' => 0,
            ],
        ];
        foreach ($updatelist as $update) {
            if (!isset($result[$update->action])) {
                $result[$update->action] = [
                    'course' => [],
                    'section' => [],
                    'cm' => [],
                    'count' => 0,
                ];
            }
            $elementid = $update->fields->id ?? 0;
            $result[$update->action][$update->name][$elementid] = $update->fields;
            $result[$update->action]['count']++;
        }
        return $result;
    }

    /**
     * Test for filter_cms_with_section_delegate protected method.
     *
     * @covers ::filter_cms_with_section_delegate
     */
    public function test_filter_cms_with_section_delegate() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $subsection = $this->getDataGenerator()->create_module('subsection', ['course' => $course]);
        $otheractvity = $this->getDataGenerator()->create_module('forum', ['course' => $course]);
        $this->setAdminUser();

        $courseformat = course_get_format($course->id);

        $modinfo = $courseformat->get_modinfo();
        $subsectioninfo = $modinfo->get_cm($subsection->cmid);
        $otheractvityinfo = $modinfo->get_cm($otheractvity->cmid);

        $actions = new stateactions();

        $method = new ReflectionMethod($actions, 'filter_cms_with_section_delegate');
        $result = $method->invoke($actions, [$subsectioninfo, $otheractvityinfo]);

        $this->assertCount(1, $result);
        $this->assertArrayHasKey($otheractvity->cmid, $result);
        $this->assertArrayNotHasKey($subsection->cmid, $result);
        $this->assertEquals($otheractvityinfo, $result[$otheractvityinfo->id]);
    }
}
