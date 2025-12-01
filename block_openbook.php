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

    /**
     * Core function used to initialize the block.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_openbook');
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
            'my' => false,
        ];
    }

    #[\Override]
    public function specialization() {
        // Load userdefined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_openbook');
        } else {
            $this->title = format_string($this->config->title, true, ['context' => $this->context]);
        }

        if (empty($this->config->openbook)) {
            return false;
        }
    }

    /**
     * Replace the instance's configuration data with those currently in $this->config;
     *
     * @param bool $nolongerused
     * @return void
     */
    public function instance_config_commit($nolongerused = false) {
        // Unset config variables that are no longer used.
        unset($this->config->openbook);
        unset($this->config->courseid);
        parent::instance_config_commit($nolongerused);
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
        if (!empty($this->page->course) && !empty($this->page->course->id)) {
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

        // Find course module id for the given openbook.
        $cm = $DB->get_record_sql(
            "SELECT cm.id, cm.visible AS uservisible
              FROM {course_modules} cm
                   JOIN {modules} md ON md.id = cm.module
                   JOIN {openbook} o ON o.id = cm.instance
             WHERE o.id = :instance AND md.name = :modulename",
            ['instance' => $this->config->openbook, 'modulename' => 'openbook']
        );

        if ($cm) {
            // This is a valid openbook resource folder, create an object with properties 'id' and 'uservisible'.
            $this->openbookcm = $cm;
        } else if (empty($this->openbookcm)) {
            // Openbook does not exist. Remove it in the config so we don't repeat this check again later.
            $this->config->openbook = 0;
            $this->instance_config_commit();
        }

        if (empty($this->openbookcm)) {
            // Openbook does not exist. Remove it in the config so we don't repeat this check again later.
            $this->config->openbook = 0;
            $this->instance_config_commit();
            return null;
        }
        return $this->openbookcm;
    }

    #[\Override]
    public function instance_allow_multiple() {
        // Are you going to allow multiple instances of each block?
        // If yes, then it is assumed that the block WILL USE per-instance configuration.
        return true;
    }

    /**
     * Returns the contents.
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        global $DB;
        if (isset($this->content)) {
            return $this->content;
        }
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        if (empty($this->instance)) {
            // No configured/visible openbook — nothing to render.
            return $this->content;
        }

        if (isset($this->config->openbook)) {
            $renderable = new \block_openbook\output\main(
                $this->config->openbook,
                $this->get_openbook_cm(),
                $this->get_owning_activity(),
                $this->instance->pagetypepattern,
            );
            $renderer = $this->page->get_renderer('block_openbook');

            $this->content = new stdClass();
            $this->content->text = $renderer->render($renderable);
        }
        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Serialize and store config data
     *
     * @param stdclass $data
     * @param bool $nolongerused
     */
    public function instance_config_save($data, $nolongerused = false) {
        $config = clone($data);

        $config->openbook = $data->openbook;

        parent::instance_config_save($config, $nolongerused);
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
