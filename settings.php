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
 * Local ildcourseinfo
 *
 * @package    local
 * @subpackage local_ildcourseinfo
 * @copyright  2017 Jan Rieger
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_ildcourseinfo', get_string('pluginname', 'local_ildcourseinfo'));
    $ADMIN->add('localplugins', $settings);
	
	$name = 'local_ildcourseinfo/moochub_url';
    $title = get_string('moochub_url_title', 'local_ildcourseinfo');
    $description = get_string('moochub_url_desc', 'local_ildcourseinfo');
    $setting = new admin_setting_configtext($name, $title, $description, get_string('moochub_url_default', 'local_ildcourseinfo'));
    $settings->add($setting);
    
	$name = 'local_ildcourseinfo/additional_html';
    $title = get_string('additional_html_title', 'local_ildcourseinfo');
    $description = get_string('additional_html_desc', 'local_ildcourseinfo');
    $setting = new admin_setting_confightmleditor($name, $title, $description, get_string('additional_html_default', 'local_ildcourseinfo'));
    $settings->add($setting);
}

