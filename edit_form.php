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
 * Form for editing Openbook resource folder files block instances.
 *
 * @package   block_openbook
 * @author    University of Geneva, E-Learning Team and Bern University of Applied Sciences
 * @copyright 2025 University of Geneva {@link http://www.unige.ch} Bern University of Applied Sciences {@link http://www.bfh.ch}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_openbook_edit_form extends block_edit_form {
    #[\Override]
    protected function specific_definition($mform) {
        global $DB;

        // Fields for editing Openbook resource folder files block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('title', 'block_openbook'));
        $mform->setType('config_title', PARAM_TEXT);
        $mform->setDefault('config_title', get_string('pluginname', 'block_openbook'));

        // Select Openbook resource folders to put in dropdown box ...
        $openbooks = $DB->get_records_select_menu(
            'openbook',
            'course = ?',
            [$this->get_course_id()],
            'name',
            'id,name'
        );
        foreach ($openbooks as $key => $value) {
            $openbooks[$key] = strip_tags(format_string($value, true));
        }
        $mform->addElement('select', 'config_openbook', get_string('select_openbook', 'block_openbook'), $openbooks);
    }

    /**
     * Returns id of the course where this block is located in one of its quizzes
     *
     * @return int
     */
    protected function get_course_id(): int {
        if ($this->block->get_owning_activity()->id) {
            $cm = get_coursemodule_from_id('quiz', $this->block->get_owning_activity()->id);
            return $cm->course;
        }
        return 0;
    }

    /**
     * Display the configuration form when block is being added to the page
     *
     * @return bool
     */
    public static function display_form_when_adding(): bool {
        return true;
    }
}
