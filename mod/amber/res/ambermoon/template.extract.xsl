<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://schema.slothsoft.net/amber/amberdata"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:exsl="http://exslt.org/common"
	xmlns:func="http://exslt.org/functions"
	xmlns:str="http://exslt.org/strings"	xmlns:set="http://exslt.org/sets"
	xmlns:php="http://php.net/xsl"
	xmlns:save="http://schema.slothsoft.net/savegame/editor"
	extension-element-prefixes="exsl func str set php">
	
	<xsl:param name="lib" select="''"/>
	
	<xsl:key name="dictionary-option" match="save:savegame.editor/save:dictionary/save:option" use="../@dictionary-id"/>	<xsl:key name="string-dictionary" match="save:group[@type='string-dictionary']/save:string" use="../@name"/>	
	<xsl:template match="/">
		<amberdata>
			<xsl:for-each select=".//save:savegame.editor">
				<xsl:choose>
					<xsl:when test="$lib = 'graphics'">
						<xsl:call-template name="extract-graphics"/>
					</xsl:when>
					<xsl:when test="$lib = 'monsters'">
						<xsl:call-template name="extract-monsters"/>
					</xsl:when>
					<xsl:when test="$lib = 'classes'">
						<xsl:call-template name="extract-classes"/>
					</xsl:when>
					<xsl:when test="$lib = 'items'">
						<xsl:call-template name="extract-items"/>
					</xsl:when>
					<xsl:when test="$lib = 'maps'">
						<xsl:call-template name="extract-maps"/>
					</xsl:when>
					<xsl:when test="$lib = 'tileset.icon'">
						<xsl:call-template name="extract-tileset.icon"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text>Unknown lib: </xsl:text>
						<xsl:value-of select="$lib"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:for-each>
		</amberdata>
	</xsl:template>
	
	<xsl:template name="extract-graphics">
		<gfx-archive-list>
			<!--
'Static_data.amb' => [
	'width' => [16, 32],
	'bitplanes' => [3, 5],
	'palette' => 0,
	'color' => 24,
],
'Palettes.amb' => [
	'width' => 256,
	'bitplanes' => 2,
	'palette' => 0,
],
'Lab_background.amb' => [
	'width' => 144,
	'bitplanes' => 4,
	'palette' => 0,
],
'Floors.amb' => [
	'width' => 64,
	'bitplanes' => 4,
	'palette' => 0,
],
'1Icon_gfx.amb' => [
	'width' => 16,
	'bitplanes' => 5,
	'palette' => null,
],
'2Icon_gfx.amb' => [
	'width' => 16,
	'bitplanes' => 5,
	'palette' => null,
],
'3Icon_gfx.amb' => [
	'width' => 16,
	'bitplanes' => 5,
	'palette' => null,
],
'Automap_graphics' => [
	'width' => null,
	'bitplanes' => null,
	'palette' => 0,
],
'Combat_background.amb' => [
	'width' => 320,
	'bitplanes' => 5,
	'palette' => 5,
],
'Combat_graphics' => [
	'width' => null,
	'bitplanes' => null,
	'palette' => 0,
],
'Event_pix.amb' => [
	'width' => 320,
	'bitplanes' => 5,
	'palette' => 31,
],
'Layouts.amb' => [
	'width' => 320,
	'bitplanes' => 3,
	'palette' => 10,
],
'Monster_gfx.amb' => [
	'width' => 80, //[64, 48, 80, 96], //[48, 64, 96, 128],
	'bitplanes' => 5,
	'palette' => null,
],
'NPC_gfx.amb' => [
	'width' => 16,
	'bitplanes' => 5,
	'palette' => null,
],
'Object_icons' => [
	'width' => 16,
	'bitplanes' => 5,
	'palette' => 49,
],
'Party_gfx.amb' => [
	'width' => 16,
	'bitplanes' => 5,
	'palette' => 0,
],
'Pics_80x80.amb' => [
	'width' => 80,
	'bitplanes' => 5,
	'palette' => 49,
],
'PICS80.AMB' => [
	'width' => 80,
	'bitplanes' => null,
	'palette' => 49,
],
'Portraits.amb' => [
	'width' => 32,
	'bitplanes' => 5,
	'palette' => 49,
	//'createSprite' => true,
],
'Stationary' => [
	'width' => [32, 48],
	'bitplanes' => 5,
	'palette' => 0,
],
'Travel_gfx.amb' => [
	'width' => 16,
	'bitplanes' => 4,
	'palette' => null,
],
-->
			<gfx-archive file-name="items" file-path="./Object_icons">
				<for-each-file width="16" bitplanes="5" palette="49" transparent="1"/>
			</gfx-archive>
			
			<gfx-archive file-name="portraits" file-path="./Portraits.amb">
				<for-each-file width="32" bitplanes="5" palette="49" transparent="0"/>
			</gfx-archive>
			
			<gfx-archive file-name="events" file-path="./Event_pix.amb">
				<for-each-file width="320" bitplanes="5" palette="31" transparent="0"/>
			</gfx-archive>
			
			<gfx-archive file-name="combat-backgrounds" file-path="./Combat_background.amb">
				<for-each-file width="320" bitplanes="5" palette="5" transparent="0"/>
			</gfx-archive>
			
			<gfx-archive file-name="places" file-path="./Pics_80x80.amb">
				<for-each-file width="80" bitplanes="5" palette="49" transparent="0"/>
			</gfx-archive>
			
			<gfx-archive file-name="palettes" file-path="Palettes.amb">
				<for-each-file width="256" bitplanes="2" palette="0" transparent="0"/>
			</gfx-archive>
			
			<gfx-archive file-name="tilesets.icon" file-path="1Icon_gfx.amb">
				<for-each-file width="16" bitplanes="5" palette="0" transparent="1"/>
			</gfx-archive>
			<gfx-archive file-name="tilesets.icon" file-path="2Icon_gfx.amb">
				<for-each-file width="16" bitplanes="5" palette="0" transparent="1"/>
			</gfx-archive>
			<gfx-archive file-name="tilesets.icon" file-path="3Icon_gfx.amb">
				<for-each-file width="16" bitplanes="5" palette="0" transparent="1"/>
			</gfx-archive>
			
			<!--
			<gfx-archive file-name="transports" file-path="Travel_gfx.amb">
				<for-each-file width="16" bitplanes="4" palette="0" transparent="0"/>
			</gfx-archive>
			<gfx-archive file-name="tilesets.floor" file-path="Floors.amb">
				<for-each-file width="64" bitplanes="4" palette="0" transparent="0"/>
			</gfx-archive>
			-->
			
			<xsl:variable name="monsters" select="save:archive[@file-name='Monster_char_data.amb']/*"/>
			<xsl:if test="count($monsters)">
				<gfx-archive file-name="monsters" file-path="Monster_gfx.amb">
					<xsl:for-each select="$monsters">
						<file id="{.//*[@name='gfx-id']/@value}" width="{.//*[@name='width']/*[@name='source']/@value}" height="{.//*[@name='height']/*[@name='source']/@value}" bitplanes="5" palette="15" transparent="1"/>
					</xsl:for-each>
				</gfx-archive>
			</xsl:if>
		</gfx-archive-list>
	</xsl:template>
	
	<xsl:template name="extract-monsters">
		<xsl:variable name="monsters" select="save:archive[@file-name='Monster_char_data.amb']/*"/>
		<xsl:if test="count($monsters)">
			<xsl:variable name="categories" select="key('dictionary-option', 'monster-images')"/>
			<monster-list>
				<xsl:for-each select="$categories">
					<xsl:variable name="category" select="."/>
					<monster-category id="{@key}" name="{@val}">
						<xsl:for-each select="$monsters">
							<xsl:if test=".//*[@name = 'gfx-id']/@value = $category/@key">
								<xsl:call-template name="extract-monster">
									<xsl:with-param name="id" select="position()"/>
								</xsl:call-template>
							</xsl:if>
						</xsl:for-each>
					</monster-category>
				</xsl:for-each>
			</monster-list>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="extract-classes">
		<xsl:variable name="classes" select="save:archive//*[@name='classes']/*/*"/>
		<xsl:if test="count($classes)">
			<xsl:variable name="expList" select="save:archive//*[@name='class-experience']/*"/>
			<class-list>
				<xsl:for-each select="$classes">
					<xsl:variable name="id" select="position()"/>
					<xsl:call-template name="extract-class">
						<xsl:with-param name="root" select=". | $expList[$id]"/>
						<xsl:with-param name="id" select="position()"/>
					</xsl:call-template>
				</xsl:for-each>
			</class-list>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="extract-items">
		<xsl:variable name="items" select="(save:archive[@file-name='AM2_CPU'] | save:archive[@file-name='AM2_BLIT'])//*[@name = 'items']/*/*"/>
		
		<xsl:if test="count($items)">
			<xsl:variable name="categories" select="set:distinct($items//*[@name = 'type']/@value)"/>
			<item-list>
				<xsl:for-each select="$categories">
					<xsl:variable name="category" select="."/>
					<item-category id="{.}" name="{key('dictionary-option', 'item-types')[@key = $category]/@val}">
						<xsl:for-each select="$items">
							<xsl:if test=".//*[@name = 'type']/@value = $category">
								<xsl:call-template name="extract-item">
									<xsl:with-param name="id" select="position()"/>
								</xsl:call-template>
							</xsl:if>
						</xsl:for-each>
					</item-category>
				</xsl:for-each>
			</item-list>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="extract-maps">
		<xsl:variable name="maps" select="save:archive[@file-name[. = '1Map_data.amb' or . = '2Map_data.amb' or . = '3Map_data.amb']]/*"/>
		<xsl:if test="count($maps)">
			<xsl:variable name="texts" select="save:archive[@file-name[. = '1Map_texts.amb' or . = '2Map_texts.amb' or . = '3Map_texts.amb']]/*"/>
			<map-list>
				<xsl:for-each select="$maps">
					<xsl:sort select="@file-name"/>
					<xsl:variable name="id" select="@file-name"/>
					<xsl:call-template name="extract-map">
						<xsl:with-param name="root" select=". | $texts[@file-name = current()/@file-name]"/>
						<xsl:with-param name="id" select="$id"/>
					</xsl:call-template>
				</xsl:for-each>
			</map-list>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="extract-tileset.icon">
	</xsl:template>
	
	
	
	<xsl:template name="extract-monster">
		<xsl:param name="root" select="."/>
		<xsl:param name="id"/>
		<monster id="{$id}"
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
			<xsl:for-each select=".//*[@name = 'gfx']">
				<xsl:call-template name="extract-gfx"/>
			</xsl:for-each>
		</monster>
	</xsl:template>
	
	<xsl:template name="extract-gfx">
		<xsl:param name="root" select="."/>
	
		<xsl:variable name="width" select=".//*[@name = 'width']"/>
		<xsl:variable name="height" select=".//*[@name = 'height']"/>
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
	</xsl:template>
	
	<xsl:template name="extract-class">
		<xsl:param name="root"/>		<xsl:param name="id"/>		<class id="{$id}">
			<xsl:apply-templates select="$root//*[@name = 'name']" mode="attr"/>
			<xsl:apply-templates select="$root//*[@name = 'school']" mode="attr"/>
			<xsl:apply-templates select="$root//*[@name = 'apr-per-level']" mode="attr"/>
			<xsl:apply-templates select="$root//*[@name = 'hp-per-level']" mode="attr"/>
			<xsl:apply-templates select="$root//*[@name = 'sp-per-level']" mode="attr"/>
			<xsl:apply-templates select="$root//*[@name = 'tp-per-level']" mode="attr"/>
			<xsl:apply-templates select="$root//*[@name = 'slp-per-level']" mode="attr"/>
			
			<xsl:apply-templates select="$root[../@name = 'class-experience']//save:integer" mode="attr">
				<xsl:with-param name="name" select="'base-experience'"/>
			</xsl:apply-templates>
			
			<xsl:for-each select="$root//*[@name = 'skills']/*">
				<skill name="{@name}" maximum="{*/@value}"/>
			</xsl:for-each>		</class>
	</xsl:template>
	
	<xsl:template name="extract-item">
		<xsl:param name="root" select="."/>
		<xsl:param name="id"/>
		
		<item id="{$id}">
			<xsl:variable name="spell-id" select=".//*[@name = 'spell-id']/@value"/>
			<xsl:variable name="spell-type" select=".//*[@name = 'spell-type']/@value"/>
			<xsl:variable name="spell-dictionary">
				<xsl:choose>
					<xsl:when test="$spell-type = 0">
						<xsl:value-of select="'spells-white'"/>
					</xsl:when>
					<xsl:when test="$spell-type = 1">
						<xsl:value-of select="'spells-blue'"/>
					</xsl:when>
					<xsl:when test="$spell-type = 2">
						<xsl:value-of select="'spells-green'"/>
					</xsl:when>
					<xsl:when test="$spell-type = 3">
						<xsl:value-of select="'spells-black'"/>
					</xsl:when>
					<xsl:when test="$spell-type = 6">
						<xsl:value-of select="'spells-misc'"/>
					</xsl:when>
				</xsl:choose>
			</xsl:variable>
			
			<xsl:apply-templates select=".//save:integer[@name != ''] | .//save:string" mode="attr"/>
			
			<xsl:apply-templates select=".//*[@name = 'type']" mode="attr"/>
			<xsl:apply-templates select=".//*[@name = 'hands']" mode="attr"/>
			<xsl:apply-templates select=".//*[@name = 'fingers']" mode="attr"/>
			<xsl:apply-templates select=".//*[@name = 'slot']" mode="attr"/>
			<xsl:apply-templates select=".//*[@name = 'ammunition-type']" mode="attr"/>
			<xsl:apply-templates select=".//*[@name = 'ranged-type']" mode="attr"/>
			
			<xsl:if test=".//*[@name = 'attribute-value']/@value &gt; 0">
				<xsl:apply-templates select=".//*[@name = 'attribute-type']" mode="attr"/>
			</xsl:if>
			<xsl:if test=".//*[@name = 'skill-value']/@value &gt; 0">
				<xsl:apply-templates select=".//*[@name = 'skill-type']" mode="attr"/>
			</xsl:if>
			
			<xsl:if test="$spell-id &gt; 0">
				<xsl:attribute name="spell-type">
					<xsl:value-of select="key('string-dictionary', 'spell-types')[position() = $spell-type + 1]/@value"/>
				</xsl:attribute>
				<xsl:attribute name="spell-name">
					<xsl:value-of select="key('string-dictionary', $spell-dictionary)[position() = $spell-id]/@value"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:attribute name="gender">
				<xsl:choose>
					<xsl:when test=".//*[@name = 'male']/@value &gt; .//*[@name = 'female']/@value">männlich</xsl:when>
					<xsl:when test=".//*[@name = 'male']/@value &lt; .//*[@name = 'female']/@value">weiblich</xsl:when>
					<xsl:otherwise>beide</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:for-each select=".//*[@name = 'properties']/*[@value != '']">
				<xsl:attribute name="is-{@name}"/>
			</xsl:for-each>
			<xsl:for-each select=".//*[@name = 'classes']/*[@value != '']">
				<class name="{@name}"/>
			</xsl:for-each>
		</item>
	</xsl:template>
	
	<xsl:template name="extract-map">
		<xsl:param name="root" select="."/>
		<xsl:param name="id"/>
		
		<map id="{$id}">
			<xsl:choose>
				<xsl:when test="$id &gt; 512">
					<xsl:attribute name="name"><xsl:value-of select="'MORAG'"/></xsl:attribute>
				</xsl:when>
				<xsl:when test="$id &gt; 256">
					<xsl:apply-templates select="($root//save:string)[1]" mode="attr">
						<xsl:with-param name="name" select="'name'"/>
					</xsl:apply-templates>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="name"><xsl:value-of select="'LYRAMIONISCHE INSELN'"/></xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			
			<xsl:apply-templates select="$root//*[@name='data']/*" mode="attr"/>
			
			<xsl:apply-templates select="$root//*[@name='unknown']" mode="unknown"/>
			
			<!--
			<xsl:for-each select="$root//*[@name = 'label']/*">
				<label>
					<xsl:apply-templates select="." mode="attr">
						<xsl:with-param name="name" select="'name'"/>
					</xsl:apply-templates>
				</label>
			</xsl:for-each>
			-->
		</map>
	</xsl:template>	
	
	
	
		<xsl:template match="save:integer | save:signed-integer | save:string" mode="attr">
		<xsl:param name="name" select="@name"/>
		<xsl:param name="value" select="@value"/>		<xsl:attribute name="{$name}"><xsl:value-of select="normalize-space($value)"/></xsl:attribute>	</xsl:template>		<xsl:template match="save:select" mode="attr">
		<xsl:param name="name" select="@name"/>		<xsl:variable name="option" select="key('dictionary-option', @dictionary-ref)[@key = current()/@value]"/>		<xsl:attribute name="{$name}"><xsl:value-of select="$option/@title | $option/@val[not($option/@title)]"/></xsl:attribute>	</xsl:template>
	
	<xsl:template match="save:group" mode="unknown">
		<unknown>
			<xsl:for-each select="*">
				<xsl:if test="position() &gt; 1">
					<xsl:text> </xsl:text>
				</xsl:if>
				<xsl:value-of select="@value"/>
				<!-- <xsl:value-of select="str:align(@value, '000', 'right')"/> -->
			</xsl:for-each>
		</unknown>
	</xsl:template>
</xsl:stylesheet>