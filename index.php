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
//* 
require_once('../../config.php');
require_once($CFG->dirroot.'/local/ildcourseinfo/locallib.php');
//require_login();

$moochubid = optional_param('id', '', PARAM_RAW);

if ($moochubid == '') {
	redirect($CFG->wwwroot);
}


$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/ildcourseinfo/index.php?id='.$moochubid);
$PAGE->set_title(format_string(get_string('pluginname', 'local_ildcourseinfo')));
$PAGE->set_heading(format_string(get_string('pluginname', 'local_ildcourseinfo')));

global $DB, $USER;	

$out = '';

$moochub_course = get_moochub_course($moochubid);


// Wenn der Kurs nur in einer Sprache angeboten wird, dann wird diese Seite zwingend in der Kurssprache angezeigt
if (count($moochub_course->attributes->languages) == 1) {
	$SESSION->forcelang = $moochub_course->attributes->languages[0];
	moodle_setlocale();
	//print_object($SESSION);
}

$instructors = '';
foreach ($moochub_course->attributes->editor as $instructor) {
	if ($instructors != '') {
		$instructors .= ', ';
	}
	$instructors .= $instructor->name;
}

// TODO aus dem Sprachpaket holen deutsch: الألمانية
$lang = array('de' => get_string('german', 'local_ildcourseinfo'), 'en' => get_string('english', 'local_ildcourseinfo'), 'fr' => get_string('french', 'local_ildcourseinfo'), 'ar' => get_string('arabic', 'local_ildcourseinfo'), 'da' => get_string('danish', 'local_ildcourseinfo'));
$languages = '';
foreach ($moochub_course->attributes->languages as $language) {
	if ($languages != '') {
		$languages .= ', ';
	}
	$languages .= $lang[$language];
}

if ($course = $DB->get_record('course', array('idnumber' => $moochub_course->attributes->courseCode))) {
	$context = context_course::instance($course->id);
	if (!is_enrolled($context, null, '', true)) {
		// not enrolled
		$tocourse = get_string('enrol_now', 'local_ildcourseinfo');
	}
	else {
		// already enrolled
		$tocourse = get_string('to_course', 'local_ildcourseinfo');
	}
}
else {
	$tocourse = get_string('enrol_now', 'local_ildcourseinfo');
}

if ($moochub_course->attributes->isAccessibleForFree == 'true') {
	$cash = get_string('for_free', 'local_ildcourseinfo');
}
else {
	$cash = '';
	foreach ($moochub_course->attributes->offers as $offer) {
		if ($offer->priceCurrency == 'EUR') {
			$currency = '€';
		} else {
			$currency = $offer->priceCurrency;
		}
		$price = $offer->price;
		if(strpos($price, '.') == false) {
			$display_price = number_format ( $price , 0 , "" , "" );
		} else {
			$display_price = number_format ( $price , 2 , "," , "" );
		}
		
		$cash = $display_price . ' ' . $currency;
		break;
	}
}

$duration = '';
//if (isset($moochub_course->attributes->duration) and ($moochub_course->attributes->duration != 'P')) {
//	$interval = new DateInterval($moochub_course->attributes->duration);

$access = '';
if (isset($moochub_course->attributes->accessDuration) and ($moochub_course->attributes->accessDuration != 'P')) {
	$interval = new DateInterval($moochub_course->attributes->accessDuration);
	$access = ' '.get_string('access', 'local_ildcourseinfo');
}
elseif (isset($moochub_course->attributes->duration) and ($moochub_course->attributes->duration != 'P')) {
	$interval = new DateInterval($moochub_course->attributes->duration);
}
if (isset($interval)) {
	//$duration = $interval->format('%d'); // für Wochen habe ich nichts gefunden :( 
	//$duration = $duration / 7; 			 // also wird von Tagen in Wochen umgerechnet
	
	$intervalInSeconds = new DateTime();
	$intervalInSeconds->setTimeStamp(5097600);
	$intervalInSeconds->add($interval);
	$intervalInSeconds = $intervalInSeconds->getTimeStamp();
	$intervalInSeconds = $intervalInSeconds - 5097600;
	$duration = round($intervalInSeconds / (60 * 60 * 24 * 7));
	
	if ($duration == 1) {
		$duration = $duration.' '.get_string('week').$access;
	}
	else {
		$duration = $duration.' '.get_string('weeks').$access;
	}
}

$course_name = format_string($moochub_course->attributes->name);

if (isset($moochub_course->attributes->hashtag)) {
	$course_name .= ' (#'.$moochub_course->attributes->hashtag.')';
}

// wenn kein endDate vorhanden ist dann wird mit duration gerechnet
$endDate = 0;
if (isset($moochub_course->attributes->endDate)) {
	$endDate = strtotime($moochub_course->attributes->endDate);
}
elseif ($duration != '' and isset($moochub_course->attributes->startDate)) {
	$endDate = strtotime($moochub_course->attributes->startDate) + $interval->format('%s');
}

$startDate = get_string('no_trainer', 'local_ildcourseinfo');
if ((isset($moochub_course->attributes->startDate) and $endDate > time()) 
		or 
	isset($moochub_course->attributes->startDate) and strtotime($moochub_course->attributes->startDate) < time() and $endDate == 0) {
		$startDate = date('d.m.Y', strtotime($moochub_course->attributes->startDate));
}

$out .= '<h1 class="courseinfositetitel">'.$course_name.'</h1>
<div class="oc-coursequickinfo">
  <ul class="quickinfo-inner">
    <li class="quickinf1 quickinfo">'.$startDate.'</li>
    <li class="quickinf2 quickinfo">'.$instructors.'</li> 
    <li class="quickinf3 quickinfo">'.$languages.'</li> 
    <li class="quickinf4 quickinfo">'.$duration.'</li>'.//duration_to_str($moochub_course->attributes->duration).'</li> '.
    '<li class="quickinf5 quickinfo">'.$cash.'</li> '. 
 '</ul>
</div>
<div class="clean" style="clear:both;height:0px;">
</div>
<section class="mooctrailer">';
  
if (isset($moochub_course->attributes->video)) {
  $out .= ' 
  <div id="mooc-course-video">
    <div class="video-responsive fitvid">
		<iframe src="'.$moochub_course->attributes->video.'" allowfullscreen="" width="1280" frameborder="0" height="720"></iframe>
    </div>
  </div>';
}
elseif (isset($moochub_course->attributes->videoImage)) {
	$out .= '<div class="fitvid video-responsive">
				<img src="'.$moochub_course->attributes->videoImage.'" alt="'.$course_name.'"  role="presentation" style="width:840px; vertical-align:middle; margin: 0 .5em;" class="img-responsive">
			</div>';
}
$out .= '
  <div class="clean" style="clear:both;height:0px;">
  </div>
  <!-- <div class="oc-zum-course-button">
           <a href="https://mooin.oncampus.de/course/view.php?id='.$course->id.'">'.$tocourse.'</a>
  </div> -->
</section>    
<section class="kursinfo-container">
	<section class="absatz-info-container">';
if ($moochub_course->attributes->isAccessibleForFree == 'false') {
	$a->cash = $cash;
	$a->duration = duration_to_str($moochub_course->attributes->accessDuration);
	$out.= '<h2 style="text-align: center;">
		'.get_string('cash_title', 'local_ildcourseinfo').'<br>
	</h2>
	<div class="pay-green">
		<p>'.get_string('cash_text','local_ildcourseinfo', $a).'</p>
	</div>
	<br>';	
}
// Was erwartet Dich in diesem Kurs?
$options = new stdClass();
$options->noclean = true;
$out.= '	<div class="oc-zum-course-button">
        	<a href="https://mooin.oncampus.de/course/view.php?id='.$course->id.'">'.$tocourse.'</a>
  		</div>           		
	</section>           		
	<section class="absatz-info-container">
        <h2 style="text-align:center">'.get_string('course_summary_title', 'local_ildcourseinfo').'</h2>
        '.format_text($moochub_course->attributes->abstract, FORMAT_MOODLE, $options).'
    </section>  ';
//echo $moochub_course->attributes->abstract;
// Wer führt diesen Kurs durch? ///////////////////////////////////////////////
$h = false;
foreach ($moochub_course->attributes->instructors as $trainer) {
	if ($h == false) {
		$out .= '<h2 style="text-align:center">'.get_string('trainers', 'local_ildcourseinfo').'</h2>  ';
		$h = true;
	}
	$out .=
    '<section class="absatz-info-container">
        <div class="container-fluid">
			<div class="row-fluid">
                <div class="absatz-info-text span8">
					<p><b>'.$trainer->name.'</b></p>'.
					format_text($trainer->description, FORMAT_MOODLE, $options).
				'</div>
                <div class="kursinfo-img span4"><img src="'.$trainer->image.'" alt="'.$trainer->name.'" width="270" height="272" style="" class="img-responsive img-circle" data-pin-nopin="true">
				</div>
			</div>
		</div>
	</section>';
}

// Was kannst Du in diesem Kurs lernen? ///////////////////////////////////
$text = '';
foreach ($moochub_course->attributes->learningObjectives as $lo) {
	if ($text != '') {
		$text .= '<br />';
	}
	$text .= $lo;
}
$out .= '<section class="absatz-info-container">
		<h2 style="text-align:center">'.get_string('learn', 'local_ildcourseinfo').'</h2> '. 
		'<p>'.format_text($text, FORMAT_MOODLE, $options).'</p>
</section>';
	
	
// Wie ist der Kurs aufgebaut? / Inhaltliche Gliederung /////////////////////////////////////////////////
if (isset($moochub_course->attributes->description)) {
	$out .=
		'<section class="absatz-info-container">
			<h2 style="text-align:center">'.get_string('structure', 'local_ildcourseinfo').'</h2>    
			<p>'.format_text($moochub_course->attributes->description, FORMAT_MOODLE, $options).' 
		</section>';
}

// 3 Spalten: Vorrausetzungen, Arbeitsaufwand, Leistungsnachweise ///////////////////////////////////////
$out .= '
    <section class="absatz-info-container">
		<div class="container-fluid">
			<div class="row-fluid">';

// 1 Vorrausetzungen/Vorkenntnisse
if (isset($moochub_course->attributes->coursePrerequisites)) {
	$text = '';
	foreach ($moochub_course->attributes->coursePrerequisites as $cp) {
		$text .= '<p>'.$cp.'</p>';
	}
	$out .=	
				'<div class="absatz-info-text span4">
					<h2 style="text-align:center">'.get_string('prerequisites', 'local_ildcourseinfo').'</h2>'.
					format_text($text, FORMAT_MOODLE, $options).'
				</div>';
}
elseif (isset($moochub_course->attributes->courseRecommendations)) {
	$text = '';
	foreach ($moochub_course->attributes->courseRecommendations as $cr) {
		$text .= '<p>'.$cr.'</p>';
	}
	$out .=	
				'<div class="absatz-info-text span4">
					<h2 style="text-align:center">'.get_string('recommendations', 'local_ildcourseinfo').'</h2>'.
					format_text($text, FORMAT_MOODLE, $options).'
				</div>';
}
else {
	$out .=	
				'<div class="absatz-info-text span4">
					<h2 style="text-align:center">'.get_string('recommendations', 'local_ildcourseinfo').'</h2>    
					<p>'.get_string('no_recommendations', 'local_ildcourseinfo').'</p>
				</div>';
}
// 2 Workload
if (isset($moochub_course->attributes->workloadDescription)) {
	$out .=
				'<div class="absatz-info-text span4">
					<h2 style="text-align:center">'.get_string('workload', 'local_ildcourseinfo').'</h2>    
					<p>'.format_text($moochub_course->attributes->workloadDescription, FORMAT_MOODLE, $options).'</p>
				</div>';
}
// 3 Leistungsnachweise
$badges = (isset($moochub_course->attributes->badges) and $moochub_course->attributes->badges);
if (isset($moochub_course->attributes->certificates) or $badges) {
	
	$certificates = get_string('cert_start', 'local_ildcourseinfo').' ';
	/* if (isset($moochub_course->attributes->certificate)) {
		$certificates .= get_string($moochub_course->attributes->certificate->type, 'local_ildcourseinfo');
	}
	if ($badges) {
		$certificates .= ' '.get_string('cert_and', 'local_ildcourseinfo').' ';
		$certificates .= get_string('certificate_badges', 'local_ildcourseinfo');
	} */
	//*
	
	if ($badges) {
		$badge = new stdClass();
		$badge->type = 'certificate_badges';
		$moochub_course->attributes->certificates[] = $badge;
	}
	//print_object($moochub_course->attributes->certificates);
	$cert_count = 0;
	$certs = count($moochub_course->attributes->certificates);
	foreach ($moochub_course->attributes->certificates as $certificate) {
		if ($cert_count > 0 and $cert_count < $certs - 1) {
			$certificates .= ', ';
		}
		if ($cert_count > 0 and $cert_count == $certs - 1) {
			$certificates .= ' '.get_string('cert_and', 'local_ildcourseinfo').' ';
		}
		$certificates .= get_string($certificate->type, 'local_ildcourseinfo');
		$cert_count++;
	}
	//*/
	$certificates .= get_string('cert_end', 'local_ildcourseinfo');
	
	$out .=
					'<div class="absatz-info-text span4">
						<h2 style="text-align:center">'.get_string('certificates', 'local_ildcourseinfo').'</h2>'.
						$certificates.
					'</div>';
}
else {
	$out .=
					'<div class="absatz-info-text span4">
						<h2 style="text-align:center">'.get_string('certificates', 'local_ildcourseinfo').'</h2>'.
						get_string('no_certificates', 'local_ildcourseinfo').
					'</div>';
}
$out .=				
			'</div>
		</div>
    </section>';
    
// Veranstalter ///////////////////////////////////////////////////
$h = false;
foreach ($moochub_course->attributes->partnerInstitute as $so) {
	if ($h == false) {
		$out .= '<section class="absatz-info-container">
					<h2 style="text-align:center;">'.get_string('source_organization', 'local_ildcourseinfo').'</h2>  
					<p style="text-align:center;">';
		$h = true;
	}
	$out .=
		'	
			<a href="'.$so->url.'" target="_blank">
				<img src="'.$so->logo.'" alt="'.format_string($so->name).'"  role="presentation" style="max-width: 270px; max-height:150px; vertical-align:middle; margin: 0 .5em; margin-top: 10px;" class="img-responsive">
			</a>
			&nbsp;
		';
	//width="375" height="134"
}
if ($h == true) {
	$out .=
	'	</p>
	</section>';
}

// Kooperationspartner (contributor) //////////////////////////
$h = false;
foreach ($moochub_course->attributes->contributor as $so) {
	if ($h == false) {
		$out .= '<section class="absatz-info-container">
					<h2 style="text-align:center;">'.get_string('contributor', 'local_ildcourseinfo').'</h2>  
					<p style="text-align:center;">';
		$h = true;
	}

	/* if (isset($so->url)) {
		$out .= '<a href="'.$so->url.'" target="_blank">';
	} */
	$out .=
		'	
			<a href="'.$so->url.'" target="_blank">
				<img src="'.$so->logo.'" alt="'.format_string($so->name).'"  role="presentation" style="max-width: 270px; max-height:150px; vertical-align:middle; margin: 0 .5em; margin-top: 10px;" class="img-responsive">
			</a>
			&nbsp;
		';
	/* if (isset($so->url)) {
		'</a>
			&nbsp;
		';
	} */
	//width="375" height="134"
}
if ($h == true) {
	$out .=
	'	</p>
	</section>';
}


// Förderer ///////////////////////////////////////////////////
$h = false;
foreach ($moochub_course->attributes->sponsor as $sponsor) {
	if ($h == false) {
		$out .= '<section class="absatz-info-container">
					<h2 style="text-align:center;">'.get_string('sponsors', 'local_ildcourseinfo').'</h2>';
		$h = true;
	}
	if (isset($moochub_course->attributes->sponsorDescription)) {
		$out .= '<p>'.format_text($moochub_course->attributes->sponsorDescription, FORMAT_MOODLE, $options).'</p>';
	}
	$out .= '<p style="text-align:center;">';
	$out .=
		'	<a href="'.$sponsor->url.'" target="_blank">
				<img src="'.$sponsor->logo.'" alt="'.format_string($sponsor->name).'"  role="presentation" style="max-width: 270px; max-height:150px; vertical-align:middle; margin: 0 .5em; margin-top: 10px;" class="img-responsive">
			</a>
			&nbsp;
		</p>';
}
if ($h == true) {
	$out .=
	'</section>';
}
// TODO Multilang
// Lizenz ///////////////////////////////////////////////
if (isset($moochub_course->attributes->licence)) {
	$licenceDescription = get_string('licenceDescription', 'local_ildcourseinfo');
	if (isset($moochub_course->attributes->licenceDescription)) {
		$licenceDescription = $moochub_course->attributes->licenceDescription;
	}
	$licence_fullname = $moochub_course->attributes->licence;
	$licence_logo = '';
	require_once('licences.php');
	if (isset($licences[$moochub_course->attributes->licence])) {
		$licence_fullname = $licences[$moochub_course->attributes->licence]->fullname;
		$licence_logo = $licences[$moochub_course->attributes->licence]->logo;
	}
	$out .=
    '
	<section class="absatz-info-container">
        <h2 style="text-align:center">'.get_string('licence', 'local_ildcourseinfo').'</h2>
		<p style="text-align: center;">'.$licenceDescription.'</p>
		<p style="text-align: center;">
			<a href="'.$moochub_course->attributes->licence.'" target="_blank">'.
				$licence_fullname.
				'<br /><br /><img src="'.$licence_logo.'" alt="'.$licence_fullname.'">'.
			'</a>
		</p>
    </section>
	<a id="scrollup" href="#" style="display: inline;"></a>
</section>';
}

if (count($moochub_course->attributes->languages) == 1) {
	unset($SESSION->forcelang);
	moodle_setlocale();
	//print_object($SESSION);
}
echo $OUTPUT->header();

//print_object($course);
//print_object($moochub_course->attributes);
//echo get_moochub_courses_curl();
if ($moochub_course) {
	echo $out;
}
else {
	echo '<h1 class="courseinfositetitel">Wrong id!</h1>';
}

// TODO output additional HTML
echo $OUTPUT->footer();

//*/

?>
