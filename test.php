<?php
$interval = 'P6M';
$timeInterval = new DateInterval ( $interval );
$intervalInSeconds = new DateTime ();
$intervalInSeconds->setTimeStamp ( 5097600 );
$intervalInSeconds->add ( $timeInterval );
$intervalInSeconds = $intervalInSeconds->getTimeStamp ();
$intervalInSeconds = $intervalInSeconds - 5097600;
$weeks = round ( (((($intervalInSeconds / 60) / 60) / 24) / 7) );

echo 'interval: ' . $interval . '<br />';
echo 'seconds: ' . $intervalInSeconds . '<br />';
echo 'minutes: ' . ($intervalInSeconds / 60) . '<br />';
echo 'hours: ' . (($intervalInSeconds / 60) / 60) . '<br />';
echo 'days: ' . ((($intervalInSeconds / 60) / 60) / 24) . '<br />';
echo 'weeks: ' . (((($intervalInSeconds / 60) / 60) / 24) / 7) . '<br />';
echo 'weeks: ' . round ( (((($intervalInSeconds / 60) / 60) / 24) / 7) ) . '<br />';

?>
<section class="absatz-info-container absatz-info1">
	<h2 style="text-align: center;">
		Was kostet der Kurs?<br>
	</h2>
	<div class="pay-green">
		<p>
			Die Gebühr für den unbetreuten Selbstlernkurs beträgt <b>50</b><b> €</b>
			(inkl. Mehrwertsteuer). Nachdem du bezahlt hast, kannst du sofort
			loslegen! Du bekommst Zugang zum Kurs und kannst für <b>sechs Monate</b>
			mit den Unterlagen arbeiten! Im Anschluss erhältst du ein
			Weiterbildungszertifikat von oncampus, wenn du die gestellten
			Aufgaben erfolgreich gelöst hast.<br>
		</p>
	</div>
	<br>
	<div class="oc-zum-course-button">
		<a href="https://mooin.oncampus.de/course/view.php?id=89">Jetzt
			einschreiben</a>
	</div>
</section>