<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:exsl="http://exslt.org/common"
	xmlns:func="http://exslt.org/functions"
	xmlns:str="http://exslt.org/strings"	xmlns:set="http://exslt.org/sets"
	xmlns:php="http://php.net/xsl"
	xmlns:save="http://schema.slothsoft.net/savegame/editor"
	extension-element-prefixes="exsl func str set php">
	
	<xsl:key name="dictionary-option" match="save:savegame.editor/save:dictionary/save:option" use="../@dictionary-id"/>	<xsl:key name="string-dictionary" match="save:group[@type='string-dictionary']/save:string" use="../@name"/>	
	<xsl:template match="save:savegame.editor">		<xsl:variable name="monsters" select="save:archive[@file-name='Monster_char_data.amb']/*"/>		<xsl:variable name="categories" select="key('dictionary-option', 'monster-images')"/>
		<xsl:for-each select="$categories">			<xsl:variable name="category" select="."/>			<category id="{@key}" name="{@val}">				<xsl:for-each select="$monsters">					<xsl:if test=".//*[@name = 'gfx-id']/@value = $category/@key">						<xsl:apply-templates select="." mode="extract">							<xsl:with-param name="id" select="position()"/>						</xsl:apply-templates>					</xsl:if>				</xsl:for-each>			</category>		</xsl:for-each>
	</xsl:template>
	
	<xsl:template match="*" mode="extract">		<xsl:param name="id"/>		<!--		<item id="234" name="PERLMUTT KETTE" subtype="0" subsubtype="0" image-id="1" hands="0" fingers="0" armor="0" damage="0" magic-armor="0" magic-weapon="0" lp-max="0" sp-max="0" attribute-value="5" skill-value="0" price="0" weight="200" spell-id="0" charges-default="0" max-charges-by-spell="0" max-charges-by-shop="0" price-per-charge="0" type="Amulett" slot="Amulett" ranged-type="-" ammunition-type="-" attribute-type="Schnelligkeit" skill-type="Attacke" spell-type="Heilung" is-equippable="" gender="beide"><class name="Abenteurer"/><class name="Krieger"/><class name="Paladin"/><class name="Dieb"/><class name="Ranger"/><class name="Heiler"/><class name="Alchemist"/><class name="Mystiker"/><class name="Magier"/></item>		-->		<monster id="{$id}"
			attack="{*[@name = 'attack']/@value + *[@name = 'combat-attack']/@value}"
			defense="{*[@name = 'defense']/@value + *[@name = 'combat-defense']/@value}"
			>
			<xsl:apply-templates select=".//*[@name = 'name']" mode="attr"/>
			<xsl:apply-templates select=".//*[@name = 'level']" mode="attr"/>
			<xsl:apply-templates select=".//*[@name = 'attacks-per-round']" mode="attr"/>
			<xsl:apply-templates select=".//*[@name = 'gold']" mode="attr"/>
			<xsl:apply-templates select=".//*[@name = 'food']" mode="attr"/>
			<xsl:apply-templates select=".//*[@name = 'combat-experience']" mode="attr"/>
			<xsl:apply-templates select=".//*[@name = 'magic-attack']" mode="attr"/>
			<xsl:apply-templates select=".//*[@name = 'magic-defense']" mode="attr"/>
			<xsl:for-each select=".//*[@name = 'monster-type']/*[@value]">
				<xsl:attribute name="is-{@name}"/>
			</xsl:for-each>
			<race>
				<xsl:apply-templates select=".//*[@name = 'race']" mode="attr">
					<xsl:with-param name="name" select="'name'"/>
				</xsl:apply-templates>
				<xsl:apply-templates select=".//*[@name = 'age']//*[@name = 'current']" mode="attr">
					<xsl:with-param name="name" select="'current-age'"/>
				</xsl:apply-templates>
				<xsl:apply-templates select=".//*[@name = 'age']//*[@name = 'maximum']" mode="attr">
					<xsl:with-param name="name" select="'maximum-age'"/>
				</xsl:apply-templates>
				<xsl:for-each select=".//*[@name = 'attributes']/*">
					<attribue name="{@name}"
						current="{*[@name = 'current']/@value + *[@name = 'current-mod']/@value}" maximum="{*[@name = 'current']/@value}"/>
				</xsl:for-each>
			</race>
			<class>
				<xsl:apply-templates select=".//*[@name = 'name']" mode="attr">
					<xsl:with-param name="name" select="'name'"/>
				</xsl:apply-templates>
				<xsl:apply-templates select=".//*[@name = 'school']" mode="attr"/>
				<xsl:apply-templates select=".//*[@name = 'apr-per-level']" mode="attr"/>
				<xsl:apply-templates select=".//*[@name = 'hp-per-level']" mode="attr"/>
				<xsl:apply-templates select=".//*[@name = 'sp-per-level']" mode="attr"/>
				<xsl:apply-templates select=".//*[@name = 'tp-per-level']" mode="attr"/>
				<xsl:apply-templates select=".//*[@name = 'slp-per-level']" mode="attr"/>
				<xsl:for-each select=".//*[@name = 'skills']/*">
					<skill name="{@name}"
						current="{*[@name = 'current']/@value + *[@name = 'current-mod']/@value}" maximum="{*[@name = 'current']/@value}"/>
				</xsl:for-each>
			</class>
			<xsl:apply-templates select=".//*[@name = 'gfx']" mode="extract"/>		</monster>
	</xsl:template>
	
	<xsl:template match="*[@name = 'gfx']" mode="extract">
		<xsl:variable name="width" select=".//*[@name = 'gfx-width']"/>
		<xsl:variable name="height" select=".//*[@name = 'gfx-height']"/>
		<xsl:variable name="animationCycles" select=".//*[@name = 'cycle']"/>
		<xsl:variable name="animationLengths" select=".//*[@name = 'length']"/>
		<xsl:variable name="animationMirrors" select=".//*[@name = 'mirror']/*"/>
		
		<gfx id="{.//*[@name='gfx-id']/@value}" 
			sourge-width="{$width/*[@name = 'source']/@value}" source-height="{$height/*[@name = 'source']/@value}"
			target-width="{$width/*[@name = 'target']/@value}" target-height="{$height/*[@name = 'target']/@value}">
			<xsl:for-each select="$animationCycles">
				<xsl:variable name="pos" select="position()"/>
				<xsl:variable name="length" select="$animationLengths[$pos]/@value"/>
				<animation name="{../@name}">
					<xsl:for-each select="str:tokenize(@value)[position() &lt;= $length]">
						<frame offset="{php:functionString('hexdec', .)}"/>
					</xsl:for-each>
					<xsl:if test="$animationMirrors[$pos]/@value">
						<xsl:for-each select="str:tokenize(@value)[position() &lt;= $length]">
							<xsl:sort select="position()" order="descending"/>
							<frame offset="{php:functionString('hexdec', .)}"/>
						</xsl:for-each>
					</xsl:if>
				</animation>
			</xsl:for-each>
		</gfx>
	</xsl:template>		<xsl:template match="save:integer | save:signed-integer | save:string" mode="attr">
		<xsl:param name="name" select="@name"/>		<xsl:attribute name="{$name}"><xsl:value-of select="normalize-space(@value)"/></xsl:attribute>	</xsl:template>		<xsl:template match="save:select" mode="attr">
		<xsl:param name="name" select="@name"/>		<xsl:variable name="option" select="key('dictionary-option', @dictionary-ref)[@key = current()/@value]"/>		<xsl:attribute name="{$name}"><xsl:value-of select="$option/@title | $option/@val[not($option/@title)]"/></xsl:attribute>	</xsl:template>
</xsl:stylesheet>