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
 * Define all the restore steps that will be used by the restore_block_task.
 *
 * @package   block_openbook
 * @author    University of Geneva, E-Learning Team and Bern University of Applied Sciences
 * @copyright 2025 University of Geneva {@link http://www.unige.ch} Bern University of Applied Sciences {@link http://www.bfh.ch}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Specialised restore task for the openbook block
 * (using execute_after_tasks for recoding of the referenced openbook activity).
 *
 * @package   block_openbook
 * @copyright 2025 University of Geneva {@link http://www.unige.ch} Bern University of Applied Sciences {@link http://www.bfh.ch}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_openbook_block_task extends restore_block_task {
    /**
     * Define (add) particular settings this block can have.
     */
    protected function define_my_settings() {
    }

    /**
     * Define (add) particular steps this block can have.
     */
    protected function define_my_steps() {
    }

    /**
     * Define the associated file areas.
     *
     * @return array
     */
    public function get_fileareas() {
        return []; // No associated fileareas.
    }

    /**
     * Define special handling of configdata.
     *
     * @return array
     */
    public function get_configdata_encoded_attributes() {
        return []; // No special handling of configdata.
    }

    /**
     * This function, executed after all the tasks in the plan have been executed, will
     * perform the recode of the referenced openbook activity for the block. This must be
     * done here and not in the normal execution steps because the activity can be restored
     * after the block.
     */
    public function after_restore() {
        global $DB;

        // Get the blockid.
        $blockid = $this->get_blockid();

        if ($configdata = $DB->get_field('block_instances', 'configdata', ['id' => $blockid])) {
            $config = $this->decode_configdata($configdata);
            if (!empty($config->openbook)) {
                // The config stores the openbook activity instance id. Remap it to the
                // restored instance using the mapping registered by mod_openbook's restore.
                if ($mapping = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'openbook', $config->openbook)) {
                    $config->openbook = $mapping->newitemid;

                    // Encode and save the config.
                    $configdata = base64_encode(serialize($config));
                    $DB->set_field('block_instances', 'configdata', $configdata, ['id' => $blockid]);
                }
            }
        }
    }

    /**
     * Define the contents in the block that must be processed by the link decoder.
     *
     * @return array
     */
    public static function define_decode_contents() {
        return [];
    }

    /**
     * Define the decoding rules for links belonging to the block to be executed by the link decoder.
     *
     * @return array
     */
    public static function define_decode_rules() {
        return [];
    }
}
