<?php
/**
 * UserDataHandler
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#UserDataHandler
 */

namespace Slothsoft\PT;

class UserDataHandler implements \w3c\dom\UserDataHandler {

/*
	const NODE_CLONED = 1;
	const NODE_IMPORTED = 2;
	const NODE_DELETED = 3;
	const NODE_RENAMED = 4;
	const NODE_ADOPTED = 5;
*/

	/**
	 * @param int $operation
	 * @param string $key
	 * @param Object $data
	 * @param Node $src
	 * @param Node $dst
	 * @return void
	 */
	public function handle($operation, $key, $data, \w3c\dom\Node $src, \w3c\dom\Node $dst) {
		
	}
}