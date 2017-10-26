<?php
/*******************************************************************************
 * \Game\Dice v1.00 20.09.2013 © Daniel Schulz
 * 
 * 	Changelog:
 * 		v1.00 20.09.2013
 * 			initial release
 ******************************************************************************/
namespace Slothsoft\Game;

class Dice {
	protected $sidesCount;
	protected $diceCount;
	public function __construct($sidesCount, $diceCount = 1) {
		$this->sidesCount = max((int) $sidesCount, 1);
		$this->diceCount = max((int) $diceCount, 1);
	}
	public function getMax() {
		return $this->diceCount * $this->sidesCount;
	}
	public function getMin() {
		return $this->diceCount;
	}
	public function getAverage() {
		$ret = $this->sidesCount;
		$ret++;
		$ret *= $this->diceCount;
		$ret /= 2;
		return $ret;
	}
	public function enclose($value) {
		return min($this->getMax(), max($this->getMin(), $value));
	}
	public function roll() {
		$ret = 0;
		for ($i = 0; $i < $this->diceCount; $i++) {
			$ret += $this->rand(1, $this->sidesCount);
		}
		return $ret;
	}
	public function rollImpossible() {
		return $this->rollLower($this->getMin());
	}
	// returns true if these dice rolled at least as high as $value
	// e.g. for ETW0, saving throws
	public function rollHigher($value) {
		return $this->roll() >= $value;
	}
	// returns true if these dice rolled at most as high as $value
	// e.g. for skill checks, attribute checks, percentages
	public function rollLower($value) {
		return $this->roll() <= $value;
	}
	public function rollEqual($value) {
		return $value === $this->roll();
	}
	protected function rand($min, $max) {
		return rand($min, $max);
	}
}