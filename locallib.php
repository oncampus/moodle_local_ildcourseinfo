<?php

function get_moochub_course($id = '') {
	global $DB;
	$allcourses = $DB->get_records_sql('SELECT idnumber, id FROM {course} WHERE idnumber = ?', array($id));
	if (isset($allcourses[$id])) {
		$courseid = $allcourses[$id]->id;
		if ($courseinfo = $DB->get_record('local_ildcourseinfo', array('course' => $courseid))) {
			return json_decode($courseinfo->json);
		}
	}
	return false;
		
	/* $allcourses = get_moochub_courses();
	foreach ($allcourses->data as $mhcourse) {
		if ($mhcourse->attributes->courseCode == $id) {
			return $mhcourse;
		}
	}
	return false; */
}

function get_moochub_courses() {
	$curl = curl(get_config('local_ildcourseinfo', 'moochub_url'));
	$curl = str_replace('<pre>', '', $curl);
	$json = str_replace('</pre>', '', $curl);
	return json_decode($json);
}

function get_moochub_courses_curl() {
	$curl = curl(get_config('local_ildcourseinfo', 'moochub_url'));
	$curl = str_replace('<pre>', '', $curl);
	$json = str_replace('</pre>', '', $curl);
	return $json;
}

function curl($url) {
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function ildcourseinfo_cron() {
	global $DB;
	$all_courses = $DB->get_records_sql('SELECT idnumber, id FROM {course}');
	$m_courses = get_moochub_courses();
	
	mtrace('Courses in json-file: '.count($m_courses->data).' ');
	if (count($m_courses->data) < 1) {
		mtrace('There is something wrong. Cancel synchronization.');
		return;
	}
	
	$inserts = 0;
	$updates = 0;
	$deletes = 0;
	$errors = 0;
	
	//mtrace('found '.count($m_courses->data).' moochub courses');
	foreach ($m_courses->data as $m_course) {
		if (isset($all_courses[$m_course->attributes->courseCode])) {
			$local_course = $all_courses[$m_course->attributes->courseCode];
			$m_json = json_encode($m_course);
			if ($local_json = $DB->get_record('local_ildcourseinfo', array('course' => $local_course->id))) {
				if ($local_json->json != $m_json) {
					$local_json->json = $m_json;
					if ($DB->update_record('local_ildcourseinfo', $local_json)) {
						mtrace('update successfull: '.$m_course->attributes->courseCode);
						$updates++;
					}
					else {
						mtrace('error while updating record: '.$m_course->attributes->courseCode);
						$errors++;
					}
				}
			}
			else {
				$new_courseinfo = new stdClass();
				$new_courseinfo->course = $local_course->id;
				$new_courseinfo->json = $m_json;
				if ($DB->insert_record('local_ildcourseinfo', $new_courseinfo, true) > 0) {
					mtrace('insert successfull: '.$m_course->attributes->courseCode);
					$inserts++;
				}
				else {
					mtrace('error while inserting record: '.$m_course->attributes->courseCode);
					$errors++;
				}
			}
		}
	}
	
	
	//mtrace('Searching courses that does not exist any longer');
	// mdl_local_ildcourseinfo durchsuchen und einträge löschen, wenn kurse davon nicht mehr im json file vorhanden sind
	$all_course_infos = $DB->get_records_sql('SELECT id, course FROM {local_ildcourseinfo}');
	foreach ($all_course_infos as $course_info) {
		// idnumber ermitteln
		$courses = $DB->get_records_sql('SELECT id, idnumber FROM {course} WHERE id = ? ', array($course_info->course));
		if (isset($courses[$course_info->course])) {
			// prüfen ob im json file ein Kurs mit der idnumber als courseCode vorliegt
			$course_exists = false;
			foreach ($m_courses->data as $m_course) {
				if ($m_course->attributes->courseCode == $courses[$course_info->course]->idnumber) {
					$course_exists = true;
					break;
				}
			}
			if (!$course_exists) {
				// Kurs existiert in moodalis nicht mehr. Eintrag in local_ildcourseinfo löschen
				if ($DB->delete_records('local_ildcourseinfo', array('id' => $course_info->id))) {
					$deletes++;
					mtrace('course does not exist in moodalis. delete successfull: '.$m_course->attributes->courseCode);
				}
				else {
					$errors++;
					mtrace('error while deleting record. course does not exist in moodalis: '.$m_course->attributes->courseCode);
				}
			}
		}
		else {
			// Kurs existiert in Moodle nicht mehr. Eintrag in local_ildcourseinfo löschen
			if ($DB->delete_records('local_ildcourseinfo', array('id' => $course_info->id))) {
				$deletes++;
				mtrace('course does not exist in moodle. delete successfull: '.$m_course->attributes->courseCode);
			}
			else {
				$errors++;
				mtrace('error while deleting record. course does not exist in moodle: '.$m_course->attributes->courseCode);
			}
		}
	}
	
	mtrace('updates: '.$updates);
	mtrace('inserts: '.$inserts);
	mtrace('deleted: '.$deletes);
	mtrace('errors: '.$errors);
}

function duration_to_str ($duration) {

	$years = 0;
	$months = 0;
	$weeks = 0;
	$days = 0;
	$hours = 0;
	$minutes = 0;

	preg_match ('/(\d)(Y)/' , $duration , $year_match);
	if (!empty($year_match)) {$years = $year_match[1];}
	preg_match ('/(P)(.*)(\d)(M)/U' , $duration , $month_match);
	if (!empty($month_match)) {$months = $month_match[3];}
	preg_match ('/(\d)(W)/' , $duration , $week_match);
	if (!empty($week_match)) {$weeks = $week_match[1];}
	preg_match ('/(\d)(D)/' , $duration , $day_match);
	if (!empty($day_match)) {$days = $day_match[1];}
	preg_match ('/(\d)(H)/' , $duration , $hour_match);
	if (!empty($hour_match)) {$hours = $hour_match[1];}
	preg_match ('/(T)(.*)(\d)(M)/' , $duration , $minute_match);
	if (!empty($minute_match)) {$minutes = $minute_match[3];}

	$duration_str = '';
	if ($years == 1) {$duration_str.= $years.' '.get_string('year','local_ildcourseinfo').' ';}
	if ($years > 1) {$duration_str.= $years.' '.get_string('years','local_ildcourseinfo').' ';}
	if ($months == 1) {$duration_str.= $months.' '.get_string('month','local_ildcourseinfo').' ';}
	if ($months > 1) {$duration_str.= $months.' '.get_string('months','local_ildcourseinfo').' ';}
	if ($weeks == 1) {$duration_str.= $weeks.' '.get_string('week','local_ildcourseinfo').' ';}
	if ($weeks > 1) {$duration_str.= $weeks.' '.get_string('weeks','local_ildcourseinfo').' ';}
	if ($days == 1) {$duration_str.= $days.' '.get_string('day','local_ildcourseinfo').' ';}
	if ($days > 1) {$duration_str.= $days.' '.get_string('days','local_ildcourseinfo').' ';}
	if ($hours == 1) {$duration_str.= $hours.' '.get_string('hour','local_ildcourseinfo').' ';}
	if ($hours > 1) {$duration_str.= $hours.' '.get_string('hours','local_ildcourseinfo').' ';}
	if ($minutes == 1) {$duration_str.= $minutes.' '.get_string('minute','local_ildcourseinfo').' ';}
	if ($minutes > 1) {$duration_str.= $minutes.' '.get_string('minutes','local_ildcourseinfo').' ';}

	return trim($duration_str);
}

?>