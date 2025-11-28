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
 * Class containing data for openbook block.
 *
 * @package    block_openbook
 * @copyright  2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_openbook\output;

use core_competency\url;
use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Class containing data for openbook block.
 *
 * @package   block_openbook
 * @author    University of Geneva, E-Learning Team and Bern University of Applied Sciences
 * @copyright 2025 University of Geneva {@link http://www.unige.ch} Bern University of Applied Sciences {@link http://www.bfh.ch}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {
    /**
     * main constructor.
     * Initialize the user preferences
     *
     * @param int $openbookid the openbook resource folder id.
     * @param stdClass $openbookcm the openbook course module.
     * @throws \dml_exception
     */
    public function __construct(
        /** @var int $openbookid the openbook resource folder id. */
        protected int $openbookid,
        /** @var stdClass $openbookcm the openbook course module. */
        protected stdClass $openbookcm,
    ) {
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array Context variables for the template
     * @throws \coding_exception
     *
     */
    public function export_for_template(renderer_base $output) {
        global $DB;
        $openbook = $DB->get_record('openbook', ['id' => $this->openbookid], '*', IGNORE_MISSING);

        $openbookurl = new \moodle_url('/mod/openbook/view.php', [
            'id' => $this->openbookcm->id,
        ]);
        $a = \html_writer::link($openbookurl, format_string($openbook->name), ['target' => '_blank']);

        $data = new stdClass();
        $data->openbooklink = get_string('accessgranted', 'block_openbook', $a);
        $data->ownfilestitle = get_string('myfiles', 'mod_openbook');
        $data->teacherfilestitle = get_string('teacher_files', 'mod_openbook');
        $data->sharedfilestitle = get_string('publicfiles', 'mod_openbook');
        return $data;
    }
}
