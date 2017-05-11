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
 * Upgrade code for ildcourseinfo.
 *
 * @package    local_ildcourseinfo
 * @copyright  2017 Jan Rieger
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the local_ildcourseinfo plugins.
 *
 * @param int $oldversion The old version of the local_ildcourseinfo module
 * @return bool
 */
function xmldb_local_ildcourseinfo_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
	
	/// Add a new column newcol to the mdl_myqtype_options
    if ($oldversion < 2017020600) {

        // Define table local_ildcourseinfo to be created.
        $table = new xmldb_table('local_ildcourseinfo');

        // Adding fields to table local_ildcourseinfo.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('json', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_ildcourseinfo.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_ildcourseinfo.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Ildcourseinfo savepoint reached.
        upgrade_plugin_savepoint(true, 2017020600, 'local', 'ildcourseinfo');
    }

    return true;
}