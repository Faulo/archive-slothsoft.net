<?php
namespace Slothsoft\Savegame\Node;


declare(ticks = 1000);

class DictionaryNode extends AbstractNode {	
	public function __construct() {
		parent::__construct();
		$this->strucData['dictionary-id'] = '';
	}
	protected function loadNode() {
	}
	protected function loadChildren() {
		$this->childNodeList = [];
	}
}