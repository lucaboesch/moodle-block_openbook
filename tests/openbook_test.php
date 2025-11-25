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

namespace block_openbook;

use advanced_testcase;
use block_openbook;
use context_course;

/**
 * PHPUnit block_openbook tests
 *
 * @package    block_openbook
 * @category   test
 * @copyright  2021 Sara Arjona (sara@moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \block_openbook
 */
final class openbook_test extends advanced_testcase {
    public static function setUpBeforeClass(): void {
        require_once(__DIR__ . '/../../moodleblock.class.php');
        require_once(__DIR__ . '/../block_openbook.php');
        parent::setUpBeforeClass();
    }

    /**
     * Test the behaviour of can_block_be_added() method.
     *
     * @covers ::can_block_be_added
     */
    public function test_can_block_be_added(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a quiz and prepare the page where the block will be added.
        $course = $this->getDataGenerator()->create_course();
        /** @var mod_quiz_generator $quizgenerator */
        $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');

        // Basic quiz settings.

        $quiz = $quizgenerator->create_instance(['course' => $course->id, 'timeclose' => 1200, 'timelimit' => 600]);

        $page = new \moodle_page();
        $page->set_context(\context_module::instance($quiz->cmid));
        $page->set_course($course);
        $page->set_pagelayout('standard');
        $page->set_pagetype("mod-quiz-view");
        $page->set_url('/mod/quiz/view.php?id=' . $quiz->cmid);

        $block = new block_openbook();
        $pluginclass = \core_plugin_manager::resolve_plugininfo_class('mod');

        // If openbook module is enabled, the method should return true.
        $pluginclass::enable_plugin('openbook', 1);
        $this->assertTrue($block->can_block_be_added($page));

        // However, if the openbook module is disabled, the method should return false.
        $pluginclass::enable_plugin('openbook', 0);
        $this->assertFalse($block->can_block_be_added($page));
    }
}
