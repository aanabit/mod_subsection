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

use core_courseformat\output\local\content\section\controlmenu;
use core_courseformat\sectiondelegatemodule;
use core_courseformat\base as course_format;
use action_menu;
use mod_subsection\manager;
use renderer_base;

/**
 * Subsection plugin section delegate class.
 *
 * @package    mod_subsection
 * @copyright  2023 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sectiondelegate extends sectiondelegatemodule {

    /**
     * Allow delegate plugin to modify the available section menu.
     *
     * @param course_format $format The course format instance.
     * @param controlmenu $controlmenu The control menu instance.
     * @param renderer_base $output The renderer instance.
     * @return action_menu|null The new action menu with the list of edit control items or null if no action menu is available.
     */
    public function get_section_action_menu(
        course_format $format,
        controlmenu $controlmenu,
        renderer_base $output,
    ): ?action_menu {

        $instance = manager::create_from_id($format->get_courseid(), $this->sectioninfo->itemid);
        $cminfo = $instance->get_coursemodule();
        $controlmenuclass = $format->get_output_classname('content\\cm\\controlmenu');
        $controlmenu = new $controlmenuclass(
            $format,
            $this->sectioninfo,
            $cminfo,
        );

        return $controlmenu->get_action_menu($instance->get_renderer());
    }
}
