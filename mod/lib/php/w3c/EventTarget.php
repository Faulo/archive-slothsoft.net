<?php
namespace w3c;

interface EventTarget {
	public function addEventListener($type, $listener, $capture = false);
	public function removeEventListener($type, $listener, $capture = false);
	public function dispatchEvent($event);
};
?>