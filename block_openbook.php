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
 * Block Openbook resource folder files
 *
 * Documentation: {@link https://moodledev.io/docs/apis/plugintypes/blocks}
 *
 * @package   block_openbook
 * @author    University of Geneva, E-Learning Team and Bern University of Applied Sciences
 * @copyright 2025 University of Geneva {@link http://www.unige.ch} Bern University of Applied Sciences {@link http://www.bfh.ch}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_openbook extends block_base {
    /**
     * @var cm_info|stdClass has properties 'id' (course module id) and 'uservisible'
     *     (whether the openbook is visible to the current user)
     */
    protected $openbookcm = null;

    /** @var stdClass course data. */
    public $course;

    /**
     * Block initialisation
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_openbook');
    }

    function specialization() {
        global $CFG, $DB;

        // Load userdefined title and make sure it's never empty
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname','block_openbook');
        } else {
            $this->title = format_string($this->config->title, true, ['context' => $this->context]);
        }
    }

    #[\Override]
    public function applicable_formats() {
        // The Openbook resource folder files block should be only added to quizzes.
        return [
            'admin' => false,
            'site-index' => false,
            'course-view' => false,
            'mod-quiz-view' => true,
            'mod-quiz-edit' => true,
            'mod-quiz-mod' => true,
            'mod-quiz-report' => true,
            'mod-quiz-overrides' => true,
            'mod' => false,
            'my' => false
        ];
    }

    /**
     * If this block belongs to a activity context, then return that activity's id.
     * Otherwise, return 0.
     * @return stdclass the activity record.
     */
    public function get_owning_activity() {
        global $DB;

        // Set some defaults.
        $result = new stdClass();
        $result->id = 0;

        if (empty($this->instance->parentcontextid)) {
            return $result;
        }
        $parentcontext = context::instance_by_id($this->instance->parentcontextid);
        if ($parentcontext->contextlevel != CONTEXT_MODULE) {
            return $result;
        }
        $cm = get_coursemodule_from_id('quiz', $parentcontext->instanceid);
        if (!$cm) {
            return $result;
        }
        return $cm;
    }

    /**
     * Replace the instance's configuration data with those currently in $this->config;
     */
    function instance_config_commit($nolongerused = false) {
        // Unset config variables that are no longer used.
        unset($this->config->courseid);
        parent::instance_config_commit($nolongerused);
    }

    /**
     * Checks if openbook is available - it should located in the same course
     *
     * @return null|cm_info|stdClass object with properties 'id' (course module id) and 'uservisible'
     */
    protected function get_openbook_cm() {
        global $DB;
        if (empty($this->config->openbook)) {
            // No openbook is configured.
            return null;
        }

        if (!empty($this->openbookcm)) {
            return $this->openbookcm;
        }

        if (!empty($this->page->course->id)) {
            // First check if openbook belongs to the current course (we don't need to make any DB queries to find it).
            $modinfo = get_fast_modinfo($this->page->course);
            if (isset($modinfo->instances['openbook'][$this->config->openbook])) {
                $this->openbookcm = $modinfo->instances['openbook'][$this->config->openbook];
                if ($this->openbookcm->uservisible) {
                    // The openbook is in the same course and is already visible to the current user.
                    return $this->openbookcm;
                }
            }
        }

        if (empty($this->openbookcm)) {
            // Openbook does not exist. Remove it in the config so we don't repeat this check again later.
            $this->config->openbook = 0;
            $this->instance_config_commit();
            return $this->openbookcm;
        }
        return $this->openbookcm;
    }

    #[\Override]
    function instance_allow_multiple() {
        // Are you going to allow multiple instances of each block?
        // If yes, then it is assumed that the block WILL USE per-instance configuration
        return true;
    }

    /**
     * Get content
     *
     * @return stdClass
     */
    public function get_content() {
        $a = new stdClass();
        $a->folder = 'folder not linked';
        $a->files = "bar1.pdf, bar2.pdf";
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = (object) [
            'footer' => '',
            'text' => get_string('accessgranted', 'block_openbook', $a),
        ];
        return $this->content;
    }

    /**
     * This block shouldn't be added to a page if the openbook module is disabled.
     *
     * @param moodle_page $page
     * @return bool
     */
    public function can_block_be_added(moodle_page $page): bool {
        $pluginclass = \core_plugin_manager::resolve_plugininfo_class('mod');
        return $pluginclass::get_enabled_plugin('openbook');
    }
}
