<?php
/**
 * TypeInfo
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#TypeInfo
 */

namespace Slothsoft\PT;

class TypeInfo implements \w3c\dom\TypeInfo {

/*
	const DERIVATION_RESTRICTION = 0x00000001;
	const DERIVATION_EXTENSION = 0x00000002;
	const DERIVATION_UNION = 0x00000004;
	const DERIVATION_LIST = 0x00000008;
*/

	/**
	 * @return string
	 */
	public function getTypeName() {
		
	}

	/**
	 * @return string
	 */
	public function getTypeNamespace() {
		
	}

	/**
	 * @param string $typeNamespaceArg
	 * @param string $typeNameArg
	 * @param int $derivationMethod
	 * @return bool
	 */
	public function isDerivedFrom($typeNamespaceArg, $typeNameArg, $derivationMethod) {
		
	}
}