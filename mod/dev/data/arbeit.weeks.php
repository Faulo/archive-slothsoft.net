<?php

$weeks = [];

$years = range(2019, 2022);

foreach ($years as $year) {
	for ($w = 1; $w < 60; $w++) {
		$start = strtotime(sprintf('%4dW%02d', $year , $w));
		if ($start === false) {
			continue;
		}
		
		
		$week = [];
		$week['year'] = (int) date('Y', $start);
		$week['month'] = (int) date('m', $start + 3 * TIME_DAY);
		$week['start'] = date('d.m.Y', $start);
		$week['end'] = date('d.m.Y', $start + 6 * TIME_DAY);
		$week['day'] = [];
		for ($i = 0; $i < 7; $i++) {
			$week['day'][] = date('d.m.Y', $start + $i * TIME_DAY);
		}
		
		if (isset($_REQUEST['month'])) {
			if ($_REQUEST['month'] != $week['month']) {
				continue;
			}
		}
		
		$weeks[$week['start']] = $week;
	}
}
/*
for ($day = 1; $day < 365; $day += 7) {
	$week = [];
	$week['start'] = date('d.m.Y', mktime(12, 0, 0, 1, $day, $year));
	$week['end'] = date('d.m.Y', mktime(12, 0, 0, 1, $day + 6, $year));
	$week['day'] = [];
	for ($i = 0; $i < 7; $i++) {
		$week['day'][] = date('Y-m-d', mktime(12, 0, 0, 1, $day + $i, $year));
	}
	$weeks[] = $week;
}
//*/

$dataFragment = $dataDoc->createDocumentFragment();
foreach ($weeks as $week) {
	$node = $dataDoc->createElement('week');
	foreach ($week as $key => $val) {
		if (is_array($val)) {
			foreach ($val as $v) {
				$child = $dataDoc->createElement($key);
				$child->textContent = $v;
				$node->appendChild($child);
			}
		} else {
			$node->setAttribute($key, $val);
		}
	}
	$dataFragment->appendChild($node);
}

foreach ($years as $year) {
	$node = $dataDoc->createElement('year');
	$node->textContent = $year;
	$dataFragment->appendChild($node);
}
foreach (range(1, 12) as $month) {
	$node = $dataDoc->createElement('month');
	$node->textContent = $month;
	$dataFragment->appendChild($node);
}

return $dataFragment;