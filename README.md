Openbook Resource Block
=====================
[![Moodle Plugin CI](https://github.com/lucaboesch/moodle-block_openbook/actions/workflows/moodle-plugin-ci.yml/badge.svg?branch=main)](https://github.com/lucaboesch/moodle-block_openbook/actions/workflows/moodle-plugin-ci.yml)
[![Latest Release](https://img.shields.io/github/v/release/lucaboesch/moodle-block_openbook?sort=semver&color=orange)](https://github.com/lucaboesch/moodle-block_openbook/releases)
[![PHP Support](https://img.shields.io/badge/php-8.1--8.4-blue)](https://github.com/lucaboesch/moodle-block_openbook/actions)
[![Moodle Support](https://img.shields.io/badge/Moodle-4.5--5.1+-orange)](https://github.com/lucaboesch/moodle-block_openbook/actions)
[![License GPL-3.0](https://img.shields.io/github/license/lucaboesch/moodle-block_openbook?color=lightgrey)](https://github.com/lucaboesch/moodle-block_openbook/blob/main/LICENSE)
[![GitHub contributors](https://img.shields.io/github/contributors/lucaboesch/moodle-block_openbook)](https://github.com/lucaboesch/moodle-block_openbook/graphs/contributors)


Requirements
------------

This plugin requires Moodle 4.5+


Motivation for this plugin
--------------------------

The block_openbook plugin provides students with quick access to files needed, and provided by
an Opensource resource folder (teacher files, own files or shared files) during a quiz
without requiring them to navigate to the Openbook resource folder beforehand. This reduces
unnecessary clicks and reduces complexity. Additionally, the block includes a direct link to
the Openbook resource folder for those who prefer accessing the full folder.

Installation
------------

Install the plugin like any other plugin to folder
/blocks/openbook

See http://docs.moodle.org/en/Installing_plugins for details on installing Moodle plugins


Usage & Settings
----------------

After installing the plugin, it can be directly used by teachers.

Capabilities
------------

This plugin also introduces these additional capabilities:

### block/openbook:addinstance

This capability controls who is allowed to add a new openbook block to a quiz activity page.


Scheduled Tasks
---------------

This plugin does not add any additional scheduled tasks.


Block placement
---------------

block_openbook can only be added to quiz activity pages. In a quiz with editing mode enabled,
teachers can add the block via 'Add a block' in the block drawer. Make sure the quiz has the
setting "Show blocks during quiz attempts" and the "Where this block appears"
setting in the block configuration is set to "Any quiz module pages" or at least
"Attempt quiz page", otherwise students won't see the block in a quiz.

Theme support
-------------

This plugin is developed and tested on Moodle Core's Boost theme.
It should also work with Boost child themes, including Moodle Core's Classic theme. However, we can't support any other theme than Boost.


Plugin repositories
-------------------

This plugin is published and regularly updated in the Moodle plugins repository:
http://moodle.org/plugins/block_openbook

The latest development version can be found on Github:
https://github.com/lucaboesch/moodle-block_openbook


Bug and problem reports / Support requests
------------------------------------------

This plugin is carefully developed and thoroughly tested, but bugs and problems can always appear.

Please report bugs and problems on Github:
https://github.com/lucaboesch/moodle-block_openbook/issues

We will do our best to solve your problems, but please note that due to limited resources we can't always provide per-case support.


Translating this plugin
-----------------------

This Moodle plugin is shipped with an english language pack only. All translations into other languages must be managed through AMOS (https://lang.moodle.org) by what they will become part of Moodle's official language pack.