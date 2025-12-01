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
        global $DB, $USER;
        $openbook = $DB->get_record('openbook', ['id' => $this->openbookid], '*', IGNORE_MISSING);
        $openpdffilesinpdfjs = $openbook->openpdffilesinpdfjs;
        $uselegacyviewer = $openbook->uselegacyviewer;

        $openbookurl = new \moodle_url('/mod/openbook/view.php', [
            'id' => $this->openbookcm->id,
        ]);
        $a = \html_writer::link($openbookurl, format_string($openbook->name), ['target' => '_blank']);

        $data = new stdClass();
        $data->openbooklink = get_string('accessgranted', 'block_openbook', $a);

        // Group handling: if activity is groupwise (separate groups), restrict files to user's group.
        $openbookcm = get_coursemodule_from_instance(
            'openbook',
            $this->openbookid,
            0,
            false,
            MUST_EXIST
        );
        $groupwiseactivity = groups_get_activity_group($openbookcm);
        $grouprestricted = !empty($groupwiseactivity);

        // Teacher files.

        $teacherfiles = [];

        // Determine whether current user can view all groups.
        $cmcontext = \context_module::instance($openbookcm->id);
        $canviewallgroups = has_capability('moodle/site:accessallgroups', $cmcontext);

        // Prepare possible IN-clause for members of the same group(s).
        $andgroupmembers = '';
        $groupparams = [];

        if ($grouprestricted && !$canviewallgroups) {
            $allmembers = [];
            // All other group member's user ids that are in the same group(s) as the current user in this course.
            $currentusergroups = groups_get_user_groups($openbookcm->course, $USER->id);
            foreach ($currentusergroups as $coursegroup) {
                foreach ($coursegroup as $groupid) {
                    $groupusers = groups_get_members($groupid, 'u.id');
                    if (!empty($groupusers)) {
                        $allmembers = array_merge($allmembers, $groupusers);
                    }
                }
            }

            if (!empty($allmembers)) {
                [$insql, $insqlparams] = $DB->get_in_or_equal(array_column($allmembers, 'id'), SQL_PARAMS_NAMED, 'user');
                $andgroupmembers = " AND f.itemid " . $insql;
                $groupparams = $insqlparams;
            }
        }

        $sql = "SELECT f.*
                  FROM {files} f
                  JOIN {context} c ON c.id = f.contextid AND c.contextlevel = :contextlevel
                  JOIN {course_modules} cm ON cm.id = c.instanceid
                 WHERE f.component = 'mod_openbook'
                   AND f.filearea = 'commonteacherfiles'
                   AND f.filename <> '.'
                   AND cm.instance = :openbookid
                   AND cm.module = (SELECT id FROM {modules} WHERE name = 'openbook')" .
                       $andgroupmembers . "
              ORDER BY f.filepath, f.filename";
        $params = array_merge(['openbookid' => $this->openbookid, 'contextlevel' => CONTEXT_MODULE], $groupparams);

        $records = $DB->get_records_sql($sql, $params);
        foreach ($records as $f) {
            if ($openpdffilesinpdfjs && $f->mimetype === 'application/pdf') {
                if ($uselegacyviewer) {
                    $pdfviewer = 'pdfjs-5.4.394-legacy-dist';
                } else {
                    $pdfviewer = 'pdfjs-5.4.394-dist';
                }

                $pluginfilerawurl = \moodle_url::make_pluginfile_url(
                    $f->contextid,
                    'mod_openbook',
                    'commonteacherfiles',
                    $f->itemid,
                    '/',
                    $f->filename,
                    true
                );

                $pdfjsurl = new \moodle_url('/mod/openbook/' . $pdfviewer . '/web/viewer.html', [
                    'file' => $pluginfilerawurl->out(false),
                ]);
                $teacherfiles[] = (object) [
                    'filename' => $f->filename,
                    'filepath' => $f->filepath,
                    'url' => $pdfjsurl,
                ];
            } else {
                $url =
                    \moodle_url::make_pluginfile_url(
                        $f->contextid,
                        $f->component,
                        $f->filearea,
                        $f->itemid,
                        $f->filepath,
                        $f->filename,
                        false,
                    );
                $teacherfiles[] = (object) [
                    'filename' => format_string($f->filename),
                    'filepath' => $f->filepath,
                    'url' => $url->out(false),
                ];
            }
        }
        $teacherfilelinks = [];
        foreach ($teacherfiles as $f) {
            $teacherfilelinks[] = \html_writer::link($f->url, $f->filename, ['target' => '_blank']);
        }
        if (!empty($teacherfiles)) {
            $data->teacherfiles = implode('<br/>', $teacherfilelinks);
            $data->teacherfilestitle = get_string('teacher_files', 'mod_openbook');
        }

        $obtainteacherapproval = $openbook->obtainteacherapproval;
        $andteacherapproval = "";
        $obtainstudentapproval = $openbook->obtainstudentapproval;
        $andstudentapproval = "";

        // Shared files.

        if ($openbook->filesarepersonal != 1) {
            // If files are personal, no shared files to show.

            $sharedfiles = [];

            if ($obtainteacherapproval) {
                $andteacherapproval = " AND of.teacherapproval = 1";
            }
            if ($obtainstudentapproval) {
                $andstudentapproval = " AND of.studentapproval = 1";
            }

            $sql = "SELECT f.*, of.*
              FROM {files} f
              JOIN {openbook_file} of ON of.fileid = f.id
              JOIN {context} c ON c.id = f.contextid AND c.contextlevel = :contextlevel
              JOIN {course_modules} cm ON cm.id = c.instanceid
             WHERE f.component = 'mod_openbook'
               AND f.filearea = 'attachment'
               AND f.filename <> '.'
               AND cm.instance = :openbookid" . $andteacherapproval . $andstudentapproval . "
               AND cm.module = (SELECT id FROM {modules} WHERE name = 'openbook')" .
                   $andgroupmembers . "
          ORDER BY f.filepath, f.filename";

            $params = array_merge(['openbookid' => $this->openbookid, 'contextlevel' => CONTEXT_MODULE], $groupparams);

            $records = $DB->get_records_sql($sql, $params);
            foreach ($records as $f) {
                if ($openpdffilesinpdfjs && $f->mimetype === 'application/pdf') {
                    if ($uselegacyviewer) {
                        $pdfviewer = 'pdfjs-5.4.394-legacy-dist';
                    } else {
                        $pdfviewer = 'pdfjs-5.4.394-dist';
                    }

                    $pluginfilerawurl = \moodle_url::make_pluginfile_url(
                        $f->contextid,
                        'mod_openbook',
                        'attachment',
                        $f->itemid,
                        '/',
                        $f->filename,
                        true
                    );

                    $pdfjsurl = new \moodle_url('/mod/openbook/' . $pdfviewer . '/web/viewer.html', [
                        'file' => $pluginfilerawurl->out(false),
                    ]);
                    $sharedfiles[] = (object) [
                        'filename' => $f->filename,
                        'filepath' => $f->filepath,
                        'url' => $pdfjsurl,
                    ];
                } else {
                    $url =
                        \moodle_url::make_pluginfile_url(
                            $f->contextid,
                            $f->component,
                            $f->filearea,
                            $f->itemid,
                            $f->filepath,
                            $f->filename,
                            false,
                        );
                    $sharedfiles[] = (object) [
                        'filename' => format_string($f->filename),
                        'filepath' => $f->filepath,
                        'url' => $url->out(false),
                    ];
                }
            }
            $sharedfilelinks = [];
            foreach ($sharedfiles as $f) {
                $sharedfilelinks[] = \html_writer::link($f->url, $f->filename, ['target' => '_blank']);
            }

            if (!empty($sharedfiles)) {
                $data->sharedfilestitle = get_string('publicfiles', 'mod_openbook');
                $data->sharedfiles = implode('<br/>', $sharedfilelinks);
            }
        }

        // Own files.

        $ownfiles = [];

        if ($obtainteacherapproval) {
            $andteacherapproval = " AND of.teacherapproval = 1";
        }

        $sql = "SELECT f.*, of.*
          FROM {files} f
          JOIN {openbook_file} of ON of.fileid = f.id
          JOIN {context} c ON c.id = f.contextid AND c.contextlevel = :contextlevel
          JOIN {course_modules} cm ON cm.id = c.instanceid
         WHERE f.component = 'mod_openbook'
           AND f.filearea = 'attachment'
           AND f.filename <> '.'
           AND f.itemid = :userid
           AND cm.instance = :openbookid" . $andteacherapproval . "
           AND cm.module = (SELECT id FROM {modules} WHERE name = 'openbook')" .
               $andgroupmembers . "
      ORDER BY f.filepath, f.filename";

        $params = array_merge(
            ['openbookid' => $this->openbookid, 'contextlevel' => CONTEXT_MODULE, 'userid' => $USER->id],
            $groupparams,
        );

        $records = $DB->get_records_sql($sql, $params);
        foreach ($records as $f) {
            if ($openpdffilesinpdfjs && $f->mimetype === 'application/pdf') {
                if ($uselegacyviewer) {
                    $pdfviewer = 'pdfjs-5.4.394-legacy-dist';
                } else {
                    $pdfviewer = 'pdfjs-5.4.394-dist';
                }

                $pluginfilerawurl = \moodle_url::make_pluginfile_url(
                    $f->contextid,
                    'mod_openbook',
                    'attachment',
                    $f->itemid,
                    '/',
                    $f->filename,
                    true
                );

                $pdfjsurl = new \moodle_url('/mod/openbook/' . $pdfviewer . '/web/viewer.html', [
                    'file' => $pluginfilerawurl->out(false),
                ]);
                $ownfiles[] = (object) [
                    'filename' => $f->filename,
                    'filepath' => $f->filepath,
                    'url' => $pdfjsurl,
                ];
            } else {
                $url =
                    \moodle_url::make_pluginfile_url(
                        $f->contextid,
                        $f->component,
                        $f->filearea,
                        $f->itemid,
                        $f->filepath,
                        $f->filename,
                        false,
                    );
                $ownfiles[] = (object) [
                    'filename' => format_string($f->filename),
                    'filepath' => $f->filepath,
                    'url' => $url->out(false),
                ];
            }
        }
        $ownfilelinks = [];
        foreach ($ownfiles as $f) {
            $ownfilelinks[] = \html_writer::link($f->url, $f->filename, ['target' => '_blank']);
        }

        if (!empty($ownfiles)) {
            $data->ownfilestitle = get_string('myfiles', 'mod_openbook');
            $data->ownfiles = implode('<br/>', $ownfilelinks);
        }
        return $data;
    }
}
