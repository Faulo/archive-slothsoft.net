<?php
use Slothsoft\Core\DOMHelper;

class Week {
	public static $weeks;
	public static $targetDuration;
	public static $months;
	public static function createWeeks(int ...$years) {
		self::$weeks = [];
		foreach ($years as $year) {
			for ($weekInYear = 1; $weekInYear < 60; $weekInYear++) {
				$start = strtotime(sprintf('%4dW%02d', $year , $weekInYear));
				if (is_int($start)) {
					self::$weeks[self::toDay($start)] = new Week($start);
				}
			}
		}
		self::$months = [];
		foreach (range(1, 12) as $i) {
			self::$months[$i] = 0;
		}
	}
	public static function findWeek(int $time, int &$offset, $duration) {
		foreach (self::$weeks as $week) {
			while ($week->contains($time + $offset)) {
				if ($week->canAppend($time + $offset, $duration)) {
					return $week;
				}
				$offset += TIME_DAY;
			}
		}
		return null;
	}
	public static function toDay(int $time) : string {
		return date('d.m.Y', $time);
	}
	public static function fillUp() {
		foreach (array_reverse(self::$weeks) as $week) {
			if ($week->lastLine) {
				$diff = self::$targetDuration - $week->getMonthDuration();
				if ($diff > 0) {
					$week->addToLastLine($diff);
				}
			}
		}
	}
	private $startDay;
	private $endDay;
	private $days;
	private $month;
	public $lastLine;
	public function __construct(int $start) {
		$this->startDay = $start;
		$this->endDay = $start + 6 * TIME_DAY;
		$this->duration = 0;
		$this->days = [];
		for ($day = $this->startDay; $day <= $this->endDay; $day += TIME_DAY) {
			$this->days[self::toDay($day)] = 0;
		}
		$this->month = (int) date('m', $start + 3 * TIME_DAY);
	}
	public function contains(int $time) {
		return isset($this->days[self::toDay($time)]);
	}
	public function canAppend(int $start, int $duration) {
		return ($this->getMonthDuration() + $duration) < self::$targetDuration and $this->days[self::toDay($start)] === 0;
	}
	public function append(stdClass $line) {
		$this->days[self::toDay($line->Start)]++;
		self::$months[$this->month] += $line->Duration;
		$this->lastLine = $line;
	}
	public function addToLastLine(int $duration) {
		self::$months[$this->month] += $duration;
		$this->lastLine->End += $duration;
		$this->lastLine->Duration += $duration;
	}
	public function getMonthDuration() {
		return self::$months[$this->month];
	}
}

$targetHours = (float) ($_REQUEST['hours'] ?? 37);
$targetDuration = (int) round($targetHours * TIME_HOUR);
$roundTimeTo = 60 * (int) ($_REQUEST['roundTo'] ?? 15);
$spreadOut = (bool) ($_REQUEST['spreadOut'] ?? false);
$fillUp = (bool) ($_REQUEST['fillUp'] ?? false);
$signature = (string) ($_REQUEST['signature'] ?? '');

Week::$targetDuration = $targetDuration;

Week::createWeeks(2019, 2020, 2021, 2022);




if (isset($_REQUEST['id'])) {
	$id = $_REQUEST['id'];
	$resDir = $this->getResourceDir('/dev/arbeit', 'xml');
	if (isset($resDir[$id])) {
		$resDoc = $resDir[$id];
		$xpath = DOMHelper::loadXPath($resDoc);
		$root = [];
		$root['targetHours'] = $targetHours;
		$root['targetDuration'] = $targetDuration;
		$root['signature'] = $signature;
		$root['date'] = date('d.m.Y', time());
		foreach ($root as $key => $val) {
			$resDoc->documentElement->setAttribute($key, $val);
		}
		$lines = [];
		foreach ($resDoc->getElementsByTagName('line') as $lineNode) {
			//<line User="Daniel Schulz" Email="faulolio@gmail.com" Client="CSW 2019 WS" Project="Unity PP" Task="" Description="Kursvorbereitung" Billable="No" Startdate="2020-01-06" Starttime="11:11:29" Enddate="2020-01-06" Endtime="12:14:18" Duration="01:02:49" Tags=""/>
			$line = new stdClass();
			$line->node = $lineNode;
			foreach ($lineNode->attributes as $attr) {
				$line->{$attr->name} = $attr->value;
			}
			
			$line->Start = strtotime("$line->Startdate $line->Starttime");
			$line->End = strtotime("$line->Enddate $line->Endtime");
			
			// post processing
			$line->Start = floor($line->Start / $roundTimeTo) * $roundTimeTo;
			$line->End = ceil($line->End / $roundTimeTo) * $roundTimeTo;
			$line->Duration = $line->End - $line->Start;
			if ($spreadOut) {				
				$offset = 0;
				if ($week = Week::findWeek($line->Start, $offset, $line->Duration)) {
					$line->Start += $offset;
					$line->End += $offset;
					$week->append($line);
				}
			}
			
			$lines[] = $line;
		}
		
		if ($spreadOut and $fillUp) {
			Week::fillUp();
		}
		
		foreach ($lines as $line) {
			$line->Startdate = date('d.m.Y', $line->Start);
			$line->Starttime = date('H:i', $line->Start);
			$line->Enddate = date('d.m.Y', $line->End);
			$line->Endtime = date('H:i', $line->End);
			$line->PrettyDuration = sprintf('%02d:%02d', floor($line->Duration / 3600), ($line->Duration / 60) % 60);
			$line->Month = (int) date('m', $line->Start);
			
			foreach ($line as $key => $val) {
				if (!is_object($val)) {
					$line->node->setAttribute($key, $val);
				}
			}
		}
		
		return $resDoc;
	}
}
