<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:exsl="http://exslt.org/common"
	xmlns:func="http://exslt.org/functions"
	xmlns:str="http://exslt.org/strings"
	xmlns:php="http://php.net/xsl"
	xmlns:save="http://schema.slothsoft.net/savegame"
	extension-element-prefixes="exsl func str set php">
	
	<xsl:key name="dictionary-option" match="save:savegame.editor/save:dictionary/save:option" use="../@dictionary-id"/>
	<xsl:template match="save:savegame.editor">
	</xsl:template>
	
	<xsl:template match="*" mode="extract">
	</xsl:template>
</xsl:stylesheet>