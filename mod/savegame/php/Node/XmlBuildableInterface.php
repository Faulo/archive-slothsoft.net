<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

interface XmlBuildableInterface
{

    public function asXML(): string;

    public function getXmlTag(): string;

    public  function getXmlAttributes(): string;
	
	public  function getXmlContent(): string;
	
	public function appendChild(XmlBuildableInterface $childNode);
	
	public function getChildNodeList();
}