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

namespace mod_subsection\courseformat;

use core_courseformat\formatactions;
use core_courseformat\sectiondelegatemodule;
use core_courseformat\stateupdates;
use mod_subsection\manager;
use section_info;

/**
 * Subsection plugin section delegate class.
 *
 * @package    core_courseformat
 * @copyright  2023 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sectiondelegate extends sectiondelegatemodule {
    /**
     * Sync the section renaming with the activity name.
     * @param section_info $section
     * @param string|null $newname
     * @return string|null
     */
    public function preprocess_section_name(section_info $section, ?string $newname): ?string {
        global $DB;
        $cm = get_coursemodule_from_instance(manager::MODULE, $section->itemid);
        if (!$cm) {
            return $newname;
        }
        formatactions::cm($section->course)->rename($cm->id, $newname);
        return $newname;
    }

    /**
     * Add extra state updates when put or create a section.
     *
     * @param section_info $section the affected section.
     * @param stateupdates $updates the state updates object to notify the UI.
     */
    public function put_section_state_extra_updates(section_info $section, stateupdates $updates): void {
        $cm = get_coursemodule_from_instance(manager::MODULE, $section->itemid);
        $updates->add_cm_put($cm->id);
    }
}
