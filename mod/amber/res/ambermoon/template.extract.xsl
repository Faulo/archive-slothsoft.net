<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://schema.slothsoft.net/amber/amberdata" xmlns:amber="http://schema.slothsoft.net/amber/amberdata"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:exsl="http://exslt.org/common"
	xmlns:func="http://exslt.org/functions" xmlns:str="http://exslt.org/strings"
	xmlns:set="http://exslt.org/sets" xmlns:math="http://exslt.org/math"
	xmlns:php="http://php.net/xsl" xmlns:save="http://schema.slothsoft.net/savegame/editor"
	xmlns:html="http://www.w3.org/1999/xhtml" extension-element-prefixes="exsl func str set math php">

	<xsl:param name="lib" select="''" />
	<xsl:param name="dictionaryURL" select="''" />

	<xsl:variable name="dictionaryDocument" select="document($dictionaryURL)" />

	<xsl:key name="dictionary-option"
		match="/amber:amberdata/amber:dictionary-list/amber:dictionary/amber:option"
		use="../@dictionary-id" />	<xsl:key name="string-dictionary"		match="save:instruction[@type='string-dictionary']/save:string" use="../@name" />

	<func:function name="amber:getName">
		<xsl:param name="context" select="." />

		<xsl:choose>
			<xsl:when test="@name">
				<func:result select="string(@name)" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:if test="../@dictionary-ref">
					<xsl:variable name="option"
						select="amber:getDictionaryOption(../@dictionary-ref, count(preceding-sibling::*))" />
					<func:result select="string($option/@val)" />
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
	</func:function>

	<func:function name="amber:getDictionaryOption">
		<xsl:param name="id" />
		<xsl:param name="key" />

		<func:result select="amber:getDictionary($id)[@key = $key]" />
	</func:function>

	<func:function name="amber:getDictionary">
		<xsl:param name="id" />

		<xsl:choose>
			<xsl:when test="$dictionaryDocument">
				<xsl:for-each select="$dictionaryDocument">
					<func:result select="key('dictionary-option', $id)" />
				</xsl:for-each>
			</xsl:when>
			<xsl:otherwise>
				<func:result select="/.." />
			</xsl:otherwise>
		</xsl:choose>
	</func:function>

	<xsl:template match="/">
		<xsl:element name="amberdata"
			namespace="http://schema.slothsoft.net/amber/amberdata">
			<xsl:for-each select=".//save:savegame.editor">
				<xsl:choose>
					<xsl:when test="$lib = 'graphics'">
						<xsl:call-template name="extract-graphics" />
					</xsl:when>
					<xsl:when test="$lib = 'portraits'">
						<xsl:call-template name="extract-portraits" />
					</xsl:when>
					<xsl:when test="$lib = 'dictionaries'">
						<xsl:call-template name="extract-dictionaries" />
					</xsl:when>
					<xsl:when test="$lib = 'pcs'">
						<xsl:call-template name="extract-pcs" />
					</xsl:when>
					<xsl:when test="$lib = 'npcs'">
						<xsl:call-template name="extract-npcs" />
					</xsl:when>
					<xsl:when test="$lib = 'monsters'">
						<xsl:call-template name="extract-monsters" />
					</xsl:when>
					<xsl:when test="$lib = 'classes'">
						<xsl:call-template name="extract-classes" />
					</xsl:when>
					<xsl:when test="$lib = 'items'">
						<xsl:call-template name="extract-items" />
					</xsl:when>
					<xsl:when test="$lib = 'tileset.icons'">
						<xsl:call-template name="extract-tileset.icons" />
					</xsl:when>
					<xsl:when test="$lib = 'tileset.labs'">
						<xsl:call-template name="extract-tileset.labs" />
					</xsl:when>
					<xsl:when test="$lib = 'maps.3d'">
						<xsl:call-template name="extract-maps">
							<xsl:with-param name="maps"
								select="save:archive[contains(@name, 'Map_data.amb')]/*
							[.//*[@name='unknown']/*[1]/@value != 13]
							[.//*[@name='map-type']/@value = 1]" />
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="$lib = 'maps.2d'">
						<xsl:call-template name="extract-maps">
							<xsl:with-param name="maps"
								select="save:archive[contains(@name, 'Map_data.amb')]/*
							[.//*[@name='unknown']/*[1]/@value != 13]
							[.//*[@name='map-type']/@value = 2]" />
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="$lib = 'worldmap.lyramion'">
						<xsl:call-template name="extract-worldmap">
							<xsl:with-param name="maps"
								select="save:archive[contains(@name, 'Map_data.amb')]/*[position() &lt;= 16]" />
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="$lib = 'worldmap.morag' or $lib = 'worldmap.kire'">
						<xsl:call-template name="extract-worldmap">
							<xsl:with-param name="maps"
								select="save:archive[contains(@name, 'Map_data.amb')]/*
							[.//*[@name='unknown']/*[1]/@value = 13]" />
						</xsl:call-template>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text>Unknown lib: </xsl:text>
						<xsl:value-of select="$lib" />
					</xsl:otherwise>
				</xsl:choose>
			</xsl:for-each>
		</xsl:element>
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
			<gfx-archive file-name="items" file-path="Amberfiles/Object_icons">
				<for-each-file width="16" bitplanes="5" palette="49"
					transparent="1" />
			</gfx-archive>

			<gfx-archive file-name="portraits" file-path="Amberfiles/Portraits.amb">
				<for-each-file width="32" bitplanes="5" palette="49"
					transparent="0" />
			</gfx-archive>

			<gfx-archive file-name="events" file-path="Amberfiles/Event_pix.amb">
				<for-each-file width="320" bitplanes="5" palette="31"
					transparent="0" />
			</gfx-archive>

			<gfx-archive file-name="combat-backgrounds" file-path="Amberfiles/Combat_background.amb">
				<for-each-file width="320" bitplanes="5" palette="5"
					transparent="0" />
			</gfx-archive>

			<gfx-archive file-name="places" file-path="Amberfiles/Pics_80x80.amb">
				<for-each-file width="80" bitplanes="5" palette="49"
					transparent="0" />
			</gfx-archive>

			<gfx-archive file-name="palettes" file-path="Amberfiles/Palettes.amb">
				<for-each-file width="256" bitplanes="2" palette="0"
					transparent="0" />
			</gfx-archive>

			<gfx-archive file-name="tileset.icons" file-path="Amberfiles/1Icon_gfx.amb">
				<xsl:for-each select="str:split(str:padding(50, '-'), '')">

					<for-each-file width="16" bitplanes="5"
						palette="{position() - 1}" transparent="1" />
				</xsl:for-each>
			</gfx-archive>
			<gfx-archive file-name="tileset.icons" file-path="Amberfiles/2Icon_gfx.amb">
				<xsl:for-each select="str:split(str:padding(50, '-'), '')">

					<for-each-file width="16" bitplanes="5"
						palette="{position() - 1}" transparent="1" />
				</xsl:for-each>
			</gfx-archive>
			<gfx-archive file-name="tileset.icons" file-path="Amberfiles/3Icon_gfx.amb">
				<xsl:for-each select="str:split(str:padding(50, '-'), '')">

					<for-each-file width="16" bitplanes="5"
						palette="{position() - 1}" transparent="1" />
				</xsl:for-each>
			</gfx-archive>

			<!--
				<gfx-archive file-name="transports" file-path="Amberfiles/Travel_gfx.amb">
				<for-each-file width="16" bitplanes="4" palette="0" transparent="0"/>
				</gfx-archive>
				<gfx-archive file-name="tilesets.floor" file-path="Amberfiles/Floors.amb">
				<for-each-file width="64" bitplanes="4" palette="0" transparent="0"/>
				</gfx-archive>
			-->

			<xsl:variable name="monsters"
				select="save:archive[@name='Monster_char_data.amb']/*" />
			<xsl:if test="count($monsters)">
				<gfx-archive file-name="monsters" file-path="Amberfiles/Monster_gfx.amb">
					<xsl:for-each select="$monsters">
						<file id="{.//*[@name='gfx-id']/@value}" width="{.//*[@name='width']/*[@name='source']/@value}"
							height="{.//*[@name='height']/*[@name='source']/@value}"
							bitplanes="5" palette="15" transparent="1" />
					</xsl:for-each>
				</gfx-archive>
			</xsl:if>
		</gfx-archive-list>
	</xsl:template>

	<xsl:template name="extract-portraits">
		<portrait-list>
			<portrait-category name="Menschen und Halb-Elfen ♂">
				<portrait id="1" name="A" />
				<portrait id="2" name="B" />
				<portrait id="3" name="C" />
				<portrait id="4" name="D" />
				<portrait id="5" name="E" />
				<portrait id="6" name="F" />
				<portrait id="7" name="G" />
				<portrait id="8" name="H" />
				<portrait id="9" name="I" />
				<portrait id="10" name="J" />
				<portrait id="11" name="K" />
				<portrait id="12" name="L" />
				<portrait id="13" name="M" />
				<portrait id="14" name="N" />
				<portrait id="15" name="O" />
				<portrait id="16" name="P" />
				<portrait id="17" name="Q" />
				<portrait id="18" name="R" />
				<portrait id="19" name="S" />
				<portrait id="20" name="T" />
				<portrait id="21" name="U" />
				<portrait id="22" name="V" />
				<portrait id="23" name="W" />
				<portrait id="24" name="X" />
				<portrait id="25" name="Y" />
				<portrait id="26" name="Z" />
				<portrait id="27" name="$" />
				<portrait id="102" name="€" />
			</portrait-category>
			<portrait-category name="Menschen und Halb-Elfen ♀">
				<portrait id="28" name="A" />
				<portrait id="29" name="B" />
				<portrait id="30" name="C" />
				<portrait id="31" name="D" />
				<portrait id="32" name="E" />
				<portrait id="33" name="F" />
				<portrait id="34" name="G" />
				<portrait id="35" name="H" />
				<portrait id="36" name="I" />
				<portrait id="37" name="J" />
				<portrait id="38" name="K" />
				<portrait id="39" name="L" />
				<portrait id="40" name="M" />
				<portrait id="41" name="N" />
				<portrait id="42" name="O" />
				<portrait id="43" name="P" />
				<portrait id="44" name="Q" />
				<portrait id="45" name="R" />
				<portrait id="46" name="S" />
				<portrait id="47" name="T" />
				<portrait id="48" name="U" />
				<portrait id="49" name="V" />
				<portrait id="50" name="W" />
				<portrait id="51" name="X" />
				<portrait id="52" name="Y" />
				<portrait id="53" name="Z" />
			</portrait-category>
			<portrait-category name="Elfen ♂">
				<portrait id="61" name="A" />
				<portrait id="62" name="B" />
				<portrait id="63" name="C" />
				<portrait id="64" name="D" />
				<portrait id="65" name="E" />
				<portrait id="66" name="F" />
				<portrait id="67" name="G" />
				<portrait id="68" name="H" />
			</portrait-category>
			<portrait-category name="Elfen ♀">
				<portrait id="69" name="A" />
				<portrait id="70" name="B" />
				<portrait id="71" name="C" />
				<portrait id="72" name="D" />
				<portrait id="73" name="E" />
				<portrait id="74" name="F" />
				<portrait id="75" name="G" />
				<portrait id="76" name="H" />
				<portrait id="77" name="I" />
				<portrait id="78" name="J" />
			</portrait-category>
			<portrait-category name="Zwerge und Gnome ♂">
				<portrait id="54" name="A" />
				<portrait id="55" name="B" />
				<portrait id="56" name="C" />
				<portrait id="57" name="D" />
				<portrait id="58" name="E" />
				<portrait id="59" name="F" />
				<portrait id="60" name="G" />
				<portrait id="90" name="H" />
				<portrait id="91" name="I" />
				<portrait id="92" name="J" />
				<portrait id="93" name="K" />
				<portrait id="94" name="L" />
				<portrait id="95" name="M" />
				<portrait id="96" name="N" />
				<portrait id="97" name="O" />
				<portrait id="98" name="P" />
			</portrait-category>
			<portrait-category name="Zwerge und Gnome ♀">
				<portrait id="99" name="A" />
				<portrait id="100" name="B" />
				<portrait id="101" name="C" />
			</portrait-category>
			<portrait-category name="Sylphen">
				<portrait id="79" name="A" />
				<portrait id="80" name="B" />
			</portrait-category>
			<portrait-category name="Moraner">
				<portrait id="85" name="A" />
				<portrait id="86" name="B" />
				<portrait id="87" name="C" />
				<portrait id="88" name="D" />
				<portrait id="89" name="E" />
			</portrait-category>
			<portrait-category name="Tiere">
				<portrait id="81" name="A" />
				<portrait id="82" name="B" />
				<portrait id="83" name="C" />
				<portrait id="84" name="D" />
			</portrait-category>
		</portrait-list>
	</xsl:template>

	<xsl:template name="extract-pcs">
	</xsl:template>

	<xsl:template name="extract-npcs">
	</xsl:template>

	<xsl:template name="extract-monsters">
		<xsl:variable name="monsters"
			select="save:archive[@name='Monster_char_data.amb']/*" />
		<xsl:if test="count($monsters)">
			<xsl:variable name="categories"
				select="amber:getDictionary('monster-images')" />
			<monster-list>
				<xsl:for-each select="$categories">
					<xsl:variable name="category" select="." />
					<monster-category name="{@val}">
						<xsl:for-each select="$monsters">
							<xsl:if test=".//*[@name = 'gfx-id']/@value = $category/@key">
								<xsl:call-template name="extract-monster">
									<xsl:with-param name="id" select="position()" />
								</xsl:call-template>
							</xsl:if>
						</xsl:for-each>
					</monster-category>
				</xsl:for-each>
			</monster-list>
		</xsl:if>
	</xsl:template>

	<xsl:template name="extract-classes">
		<xsl:variable name="classes" select="save:archive//*[@name='classes']/*/*" />
		<xsl:if test="count($classes)">
			<xsl:variable name="expList"
				select="save:archive//*[@name='class-experience']/*" />
			<class-list>
				<xsl:for-each select="$classes">
					<xsl:variable name="id" select="position()" />
					<xsl:call-template name="extract-class">
						<xsl:with-param name="root" select=". | $expList[$id]" />
						<xsl:with-param name="id" select="position()" />
					</xsl:call-template>
				</xsl:for-each>
			</class-list>
		</xsl:if>
	</xsl:template>

	<xsl:template name="extract-items">
		<xsl:variable name="items"
			select="(save:archive[@name='AM2_CPU'] | save:archive[@name='AM2_BLIT'])//*[@name = 'items']/*/*" />
		<xsl:variable name="texts"
			select="save:archive[@name='Object_texts.amb']" />

		<xsl:if test="count($items)">
			<xsl:variable name="categories"
				select="set:distinct($items//*[@name = 'type']/@value)" />
			<item-list>
				<xsl:for-each select="$categories">
					<xsl:variable name="category" select="." />
					<item-category
						name="{amber:getDictionaryOption('item-types', $category)/@val}">
						<xsl:for-each select="$items">
							<xsl:if test=".//*[@name = 'type']/@value = $category">
								<xsl:call-template name="extract-item">
									<xsl:with-param name="id" select="position()" />
									<xsl:with-param name="texts" select="$texts" />
								</xsl:call-template>
							</xsl:if>
						</xsl:for-each>
					</item-category>
				</xsl:for-each>
			</item-list>
		</xsl:if>
	</xsl:template>

	<xsl:template name="extract-maps">
		<xsl:param name="maps" select="/.." />
		<xsl:if test="count($maps)">
			<xsl:variable name="texts"
				select="save:archive[contains(@name, 'Map_texts.amb')]/*" />
			<map-list>
				<xsl:for-each select="$maps">
					<xsl:sort select="@file-name" />
					<xsl:variable name="id" select="@file-name" />
					<xsl:call-template name="extract-map">
						<xsl:with-param name="root"
							select=". | $texts[@file-name = current()/@file-name]" />
						<xsl:with-param name="id" select="$id" />
					</xsl:call-template>
				</xsl:for-each>
			</map-list>
		</xsl:if>
	</xsl:template>

	<xsl:template name="extract-worldmap">
		<xsl:param name="maps" select="/.." />
		<xsl:if test="count($maps)">
			<xsl:variable name="root" select="$maps[1]" />
			<xsl:variable name="texts"
				select="save:archive[contains(@name, 'Map_texts.amb')]/*" />
			<map-list>
				<xsl:variable name="size" select="math:sqrt(count($maps))" />
				<xsl:variable name="field-maps" select="$maps//*[@name='fields']" />

				<xsl:variable name="width" select="$root//*[@name='width']/@value" />
				<xsl:variable name="height" select="$root//*[@name='height']/@value" />
				<xsl:variable name="tileset"
					select="$root//*[@name='tileset-id']/@value" />
				<xsl:variable name="palette"
					select="$root//*[@name='palette-id']/@value" />
				<xsl:variable name="map-type" select="$root//*[@name='map-type']/@value" />
				<xsl:variable name="world" select="$root//*[@name='world']/@value" />

				<map id="{$lib}" width="{$size * $width}" height="{$size * $height}"
					tileset-id="{$tileset}" palette-id="{$palette}" map-type="{$map-type}"
					world="{$world}">
					<xsl:choose>
						<xsl:when test="$world = 0">
							<xsl:attribute name="name"><xsl:value-of
								select="'LYRAMIONISCHE INSELN'" /></xsl:attribute>
						</xsl:when>
						<xsl:when test="$world = 1">
							<xsl:attribute name="name"><xsl:value-of
								select="'WALDMOND'" /></xsl:attribute>
						</xsl:when>
						<xsl:when test="$world = 2">
							<xsl:attribute name="name"><xsl:value-of
								select="'WÜSTENMOND'" /></xsl:attribute>
						</xsl:when>
					</xsl:choose>
					<field-map>
						<xsl:variable name="mapYList"
							select="str:split(str:padding($size, '-'), '')" />
						<xsl:variable name="mapXList"
							select="str:split(str:padding($size, '-'), '')" />
						<xsl:variable name="fieldYList"
							select="str:split(str:padding($height, '-'), '')" />
						<xsl:variable name="fieldXList"
							select="str:split(str:padding($width, '-'), '')" />

						<xsl:for-each select="$mapYList">
							<xsl:variable name="mapY" select="position()" />
							<xsl:for-each select="$fieldYList">
								<xsl:variable name="fieldY" select="position()" />
								<field-row>
									<xsl:for-each select="$mapXList">
										<xsl:variable name="mapX" select="position()" />

										<xsl:for-each select="$fieldXList">
											<xsl:variable name="fieldX" select="position()" />

											<xsl:variable name="field"
												select="$field-maps[($mapY - 1) * $size + $mapX]/*[($fieldY - 1) * $width + $fieldX]" />
											<field>
												<xsl:apply-templates select="$field/*[1][@value &gt; 0]"
													mode="attr">
													<xsl:with-param name="name" select="'low'" />
												</xsl:apply-templates>
												<xsl:apply-templates select="$field/*[2][@value &gt; 0]"
													mode="attr">
													<xsl:with-param name="name" select="'high'" />
												</xsl:apply-templates>
												<xsl:apply-templates select="$field/*[3][@value &gt; 0]"
													mode="attr">
													<xsl:with-param name="name" select="'event'" />
												</xsl:apply-templates>
											</field>
										</xsl:for-each>
									</xsl:for-each>
								</field-row>
							</xsl:for-each>
						</xsl:for-each>
					</field-map>
				</map>
			</map-list>
		</xsl:if>
	</xsl:template>

	<xsl:template name="extract-map">
		<xsl:param name="root" select="." />
		<xsl:param name="id" />

		<xsl:variable name="width" select="$root//*[@name='width']/@value" />
		<xsl:variable name="height" select="$root//*[@name='height']/@value" />
		<xsl:variable name="tileset" select="$root//*[@name='tileset-id']/@value" />
		<xsl:variable name="palette" select="$root//*[@name='palette-id']/@value" />
		<xsl:variable name="map-type" select="$root//*[@name='map-type']/@value" />
		<map id="{$id}">
			<xsl:choose>
				<xsl:when test="$id &gt; 512">
					<xsl:attribute name="name"><xsl:value-of
						select="'MORAG'" /></xsl:attribute>
				</xsl:when>
				<xsl:when test="$id &gt; 256">
					<xsl:apply-templates select="($root//save:string)[1]"
						mode="attr">
						<xsl:with-param name="name" select="'name'" />
					</xsl:apply-templates>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="name"><xsl:value-of
						select="'LYRAMIONISCHE INSELN'" /></xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>

			<xsl:apply-templates select="$root//*[@name='data']/*"
				mode="attr" />

			<xsl:apply-templates select="$root//*[@name='unknown']"
				mode="unknown" />


			<xsl:for-each select="$root//*[@name = 'label']/*">
				<label>
					<xsl:apply-templates select="." mode="attr">
						<xsl:with-param name="name" select="'name'" />
					</xsl:apply-templates>
				</label>
			</xsl:for-each>


			<xsl:for-each select="$root//*[@name='fields']">
				<xsl:variable name="fieldYList"
					select="str:split(str:padding($height, '-'), '')" />
				<xsl:variable name="fieldXList"
					select="str:split(str:padding($width, '-'), '')" />
				<xsl:variable name="fields" select="*" />
				<field-map>
					<xsl:for-each select="$fieldYList">
						<xsl:variable name="fieldY" select="position()" />
						<field-row>
							<xsl:for-each select="$fieldXList">
								<xsl:variable name="fieldX" select="position()" />
								<xsl:variable name="field"
									select="$fields[($fieldY - 1) * $width + $fieldX]" />
								<field>
									<xsl:apply-templates select="$field/*[1][@value &gt; 0]"
										mode="attr">
										<xsl:with-param name="name" select="'low'" />
									</xsl:apply-templates>
									<xsl:apply-templates select="$field/*[2][@value &gt; 0]"
										mode="attr">
										<xsl:with-param name="name" select="'high'" />
									</xsl:apply-templates>
									<xsl:apply-templates select="$field/*[3][@value &gt; 0]"
										mode="attr">
										<xsl:with-param name="name" select="'event'" />
									</xsl:apply-templates>
								</field>
							</xsl:for-each>
						</field-row>
					</xsl:for-each>
				</field-map>
			</xsl:for-each>
		</map>
	</xsl:template>

	<xsl:template name="extract-tileset.icons">
		<xsl:variable name="tilesets"
			select="save:archive[contains(@name, 'Icon_data.amb')]/*" />
		<xsl:if test="count($tilesets)">
			<tileset-icon-list>
				<xsl:for-each select="$tilesets">
					<xsl:sort select="@file-name" />
					<xsl:variable name="id" select="number(@file-name)" />
					<xsl:call-template name="extract-tileset.icon">
						<xsl:with-param name="id" select="$id" />
					</xsl:call-template>
				</xsl:for-each>
			</tileset-icon-list>
		</xsl:if>
	</xsl:template>


	<xsl:template name="extract-tileset.icon">
		<xsl:param name="root" select="." />
		<xsl:param name="id" />
		<tileset-icon id="{$id}">
			<xsl:apply-templates select="$root//*[@name='data']//*[@value]"
				mode="attr" />
			<xsl:for-each select=".//*[@name = 'tiles']/*/*">
				<xsl:if test="*[@name='image-count']/@value &gt; 0">
					<tile id="{position()}">
						<xsl:apply-templates select="*[@value]" mode="attr" />
					</tile>
				</xsl:if>
			</xsl:for-each>
		</tileset-icon>
	</xsl:template>

	<xsl:template name="extract-tileset.labs">
		<xsl:variable name="tilesets"
			select="save:archive[contains(@name, 'Lab_data.amb')]/*" />
		<xsl:if test="count($tilesets)">
			<tileset-lab-list>
				<xsl:for-each select="$tilesets">
					<xsl:sort select="@file-name" />
					<xsl:variable name="id" select="number(@file-name)" />
					<xsl:call-template name="extract-tileset.lab">
						<xsl:with-param name="id" select="$id" />
					</xsl:call-template>
				</xsl:for-each>
			</tileset-lab-list>
		</xsl:if>
	</xsl:template>


	<xsl:template name="extract-tileset.lab">
		<xsl:param name="root" select="." />
		<xsl:param name="id" />
		<tileset-lab id="{$id}">
			<xsl:apply-templates select="$root//*[@name='data']//*[@value]"
				mode="attr" />
			<xsl:for-each select=".//*[@name = 'tiles']/*/*">
				<xsl:if test="*[@name='image-count']/@value &gt; 0">
					<tile id="{position()}">
						<xsl:apply-templates select="*[@value]" mode="attr" />
					</tile>
				</xsl:if>
			</xsl:for-each>
		</tileset-lab>
	</xsl:template>



	<xsl:template name="extract-monster">
		<xsl:param name="root" select="." />
		<xsl:param name="id" />
		<monster id="{$id}"
			attack="{*[@name = 'attack']/@value + *[@name = 'combat-attack']/@value}"
			defense="{*[@name = 'defense']/@value + *[@name = 'combat-defense']/@value}">
			<xsl:apply-templates select=".//*[@name = 'name']"
				mode="attr" />
			<xsl:apply-templates select=".//*[@name = 'level']"
				mode="attr" />
			<xsl:apply-templates select=".//*[@name = 'attacks-per-round']"
				mode="attr" />
			<xsl:apply-templates select=".//*[@name = 'gold']"
				mode="attr" />
			<xsl:apply-templates select=".//*[@name = 'food']"
				mode="attr" />
			<xsl:apply-templates select=".//*[@name = 'combat-experience']"
				mode="attr" />
			<xsl:apply-templates select=".//*[@name = 'magic-attack']"
				mode="attr" />
			<xsl:apply-templates select=".//*[@name = 'magic-defense']"
				mode="attr" />
			<xsl:for-each select=".//*[@name = 'monster-type']/*[@value]">
				<xsl:attribute name="is-{amber:getName()}" />
			</xsl:for-each>
			<race>
				<xsl:apply-templates select=".//*[@name = 'race']"
					mode="attr">
					<xsl:with-param name="name" select="'name'" />
				</xsl:apply-templates>
				<xsl:apply-templates select=".//*[@name = 'age']//*[@name = 'current']"
					mode="attr">
					<xsl:with-param name="name" select="'current-age'" />
				</xsl:apply-templates>
				<xsl:apply-templates select=".//*[@name = 'age']//*[@name = 'maximum']"
					mode="attr">
					<xsl:with-param name="name" select="'maximum-age'" />
				</xsl:apply-templates>
				<xsl:for-each select=".//*[@name = 'attributes']/*">
					<attribute name="{amber:getName()}"
						current="{*[@name = 'current']/@value + *[@name = 'current-mod']/@value}"
						maximum="{*[@name = 'current']/@value}" />
				</xsl:for-each>
			</race>
			<class>
				<xsl:apply-templates select=".//*[@name = 'name']"
					mode="attr">
					<xsl:with-param name="name" select="'name'" />
				</xsl:apply-templates>
				<xsl:apply-templates select=".//*[@name = 'school']"
					mode="attr" />
				<xsl:apply-templates select=".//*[@name = 'apr-per-level']"
					mode="attr" />
				<xsl:apply-templates select=".//*[@name = 'hp-per-level']"
					mode="attr" />
				<xsl:apply-templates select=".//*[@name = 'sp-per-level']"
					mode="attr" />
				<xsl:apply-templates select=".//*[@name = 'tp-per-level']"
					mode="attr" />
				<xsl:apply-templates select=".//*[@name = 'slp-per-level']"
					mode="attr" />
				<xsl:for-each select=".//*[@name = 'skills']/*">
					<skill name="{amber:getName()}"
						current="{*[@name = 'current']/@value + *[@name = 'current-mod']/@value}"
						maximum="{*[@name = 'current']/@value}" />
				</xsl:for-each>
			</class>
			<xsl:call-template name="extract-equipment" />
			<xsl:call-template name="extract-inventory" />
			<xsl:call-template name="extract-spellbook" />
			<xsl:for-each select=".//*[@name = 'gfx']">
				<xsl:call-template name="extract-gfx" />
			</xsl:for-each>
		</monster>
	</xsl:template>

	<xsl:template name="extract-spellbook">
		<xsl:param name="root" select="." />
		<xsl:for-each select=".//*[@name = 'spells']">
			<spellbook>
				<xsl:for-each select=".//*[string-length(@value) &gt; 0]">
					<spell-instance name="{amber:getName()}" />
				</xsl:for-each>
			</spellbook>
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="extract-equipment">
		<xsl:param name="root" select="." />
		<xsl:for-each select="$root//*[@name = 'equipment']">
			<equipment>
				<xsl:for-each select="*">
					<xsl:call-template name="extract-slot" />
				</xsl:for-each>
			</equipment>
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="extract-inventory">
		<xsl:param name="root" select="." />
		<xsl:for-each select="$root//*[@name = 'inventory']">
			<inventory>
				<xsl:for-each select="*">
					<xsl:call-template name="extract-slot" />
				</xsl:for-each>
			</inventory>
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="extract-slot">
		<xsl:param name="root" select="." />
		<slot>
			<xsl:if test="$root/@name">
				<xsl:attribute name="name"><xsl:value-of select="@name" /></xsl:attribute>
			</xsl:if>
			<xsl:for-each select="$root//*[@name = 'item-id'][@value &gt; 0]/..">
				<xsl:call-template name="extract-item-instance" />
			</xsl:for-each>
		</slot>
	</xsl:template>

	<xsl:template name="extract-item-instance">
		<xsl:param name="root" select="." />
		<item-instance>
			<xsl:apply-templates select="$root//*[@name = 'item-id']"
				mode="attr" />
			<xsl:apply-templates select="$root//*[@name = 'item-amount']"
				mode="attr" />
			<xsl:apply-templates select="$root//*[@name = 'item-charge']"
				mode="attr" />

			<xsl:for-each select=".//*[@name = 'item-status']/*[@value != '']">
				<xsl:attribute name="is-{amber:getName()}" />
			</xsl:for-each>
		</item-instance>
	</xsl:template>

	<xsl:template name="extract-gfx">
		<xsl:param name="root" select="." />

		<xsl:variable name="width" select=".//*[@name = 'width']" />
		<xsl:variable name="height" select=".//*[@name = 'height']" />
		<xsl:variable name="animationCycles" select=".//*[@name = 'cycle']" />
		<xsl:variable name="animationLengths" select=".//*[@name = 'length']" />
		<xsl:variable name="animationMirrors" select=".//*[@name = 'mirror']/*" />

		<gfx id="{.//*[@name='gfx-id']/@value}" sourge-width="{$width/*[@name = 'source']/@value}"
			source-height="{$height/*[@name = 'source']/@value}" target-width="{$width/*[@name = 'target']/@value}"
			target-height="{$height/*[@name = 'target']/@value}">
			<xsl:for-each select="$animationCycles">
				<xsl:variable name="pos" select="position()" />
				<xsl:variable name="length" select="$animationLengths[$pos]/@value" />
				<animation name="{../@name}">
					<xsl:for-each select="str:tokenize(@value)[position() &lt;= $length]">
						<frame offset="{php:functionString('hexdec', .)}" />
					</xsl:for-each>
					<xsl:if test="$animationMirrors[$pos]/@value">
						<xsl:for-each select="str:tokenize(@value)[position() &lt;= $length]">
							<xsl:sort select="position()" order="descending" />
							<frame offset="{php:functionString('hexdec', .)}" />
						</xsl:for-each>
					</xsl:if>
				</animation>
			</xsl:for-each>
		</gfx>
	</xsl:template>

	<xsl:template name="extract-class">
		<xsl:param name="root" />		<xsl:param name="id" />		<class id="{$id}">
			<xsl:apply-templates select="$root//*[@name = 'name']"
				mode="attr" />
			<xsl:apply-templates select="$root//*[@name = 'school']"
				mode="attr" />
			<xsl:apply-templates select="$root//*[@name = 'apr-per-level']"
				mode="attr" />
			<xsl:apply-templates select="$root//*[@name = 'hp-per-level']"
				mode="attr" />
			<xsl:apply-templates select="$root//*[@name = 'sp-per-level']"
				mode="attr" />
			<xsl:apply-templates select="$root//*[@name = 'tp-per-level']"
				mode="attr" />
			<xsl:apply-templates select="$root//*[@name = 'slp-per-level']"
				mode="attr" />

			<xsl:apply-templates
				select="$root[../@name = 'class-experience']//save:integer" mode="attr">
				<xsl:with-param name="name" select="'base-experience'" />
			</xsl:apply-templates>

			<xsl:for-each select="$root//*[@name = 'skills']/*">
				<skill name="{amber:getName()}" maximum="{*/@value}" />
			</xsl:for-each>		</class>
	</xsl:template>

	<xsl:template name="extract-item">
		<xsl:param name="root" select="." />
		<xsl:param name="texts" select="/.." />
		<xsl:param name="id" />

		<item id="{$id}">
			<xsl:variable name="type" select=".//*[@name = 'type']/@value" />
			<xsl:variable name="subtype" select=".//*[@name = 'subtype']/@value" />
			<xsl:variable name="subsubtype" select=".//*[@name = 'subsubtype']/@value" />
			<xsl:variable name="spell-id" select=".//*[@name = 'spell-id']/@value" />
			<xsl:variable name="spell-type" select=".//*[@name = 'spell-type']/@value" />
			<xsl:variable name="spell-dictionary">
				<xsl:choose>
					<xsl:when test="$spell-type = 0">
						<xsl:value-of select="'spells-white'" />
					</xsl:when>
					<xsl:when test="$spell-type = 1">
						<xsl:value-of select="'spells-blue'" />
					</xsl:when>
					<xsl:when test="$spell-type = 2">
						<xsl:value-of select="'spells-green'" />
					</xsl:when>
					<xsl:when test="$spell-type = 3">
						<xsl:value-of select="'spells-black'" />
					</xsl:when>
					<xsl:when test="$spell-type = 6">
						<xsl:value-of select="'spells-misc'" />
					</xsl:when>
				</xsl:choose>
			</xsl:variable>

			<xsl:apply-templates select=".//save:integer[@name != ''] | .//save:string"
				mode="attr" />

			<xsl:apply-templates select=".//*[@name = 'type']"
				mode="attr" />
			<xsl:apply-templates select=".//*[@name = 'hands']"
				mode="attr" />
			<xsl:apply-templates select=".//*[@name = 'fingers']"
				mode="attr" />
			<xsl:apply-templates select=".//*[@name = 'slot']"
				mode="attr" />
			<xsl:apply-templates select=".//*[@name = 'ammunition-type']"
				mode="attr" />
			<xsl:apply-templates select=".//*[@name = 'ranged-type']"
				mode="attr" />

			<xsl:if test=".//*[@name = 'attribute-value']/@value &gt; 0">
				<xsl:apply-templates select=".//*[@name = 'attribute-type']"
					mode="attr" />
			</xsl:if>
			<xsl:if test=".//*[@name = 'skill-value']/@value &gt; 0">
				<xsl:apply-templates select=".//*[@name = 'skill-type']"
					mode="attr" />
			</xsl:if>

			<xsl:if test="$spell-id &gt; 0">
				<xsl:attribute name="spell-type">
					<xsl:value-of
					select="key('string-dictionary', 'spell-types')[position() = $spell-type + 1]/@value" />
				</xsl:attribute>
				<xsl:attribute name="spell-name">
					<xsl:value-of
					select="key('string-dictionary', $spell-dictionary)[position() = $spell-id]/@value" />
				</xsl:attribute>
			</xsl:if>
			<xsl:attribute name="gender">
				<xsl:choose>
					<xsl:when
				test=".//*[@name = 'male']/@value &gt; .//*[@name = 'female']/@value">männlich</xsl:when>
					<xsl:when
				test=".//*[@name = 'male']/@value &lt; .//*[@name = 'female']/@value">weiblich</xsl:when>
					<xsl:otherwise>beide</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:for-each select=".//*[@name = 'properties']/*[@value != '']">
				<xsl:attribute name="is-{amber:getName()}" />
			</xsl:for-each>
			<xsl:for-each select=".//*[@name = 'classes']/*[@value != '']">
				<class name="{amber:getName()}" />
			</xsl:for-each>
			<xsl:if test="$type = 8">
				<xsl:call-template name="extract-text">
					<xsl:with-param name="root"
						select="$texts/*[position() = $subtype]/*/*[position() = ($subsubtype + 1)]//@value" />
					<xsl:with-param name="id" select="0" />
				</xsl:call-template>
			</xsl:if>
		</item>
	</xsl:template>

	<xsl:template name="extract-text">
		<xsl:param name="root" select="." />
		<xsl:param name="id" />
		<text id="{$id}">
			<xsl:for-each select="str:split($root, '^')">
				<xsl:value-of select="translate(., '$', '&#160;')" />
				<br xmlns="http://www.w3.org/1999/xhtml" />
			</xsl:for-each>
		</text>
	</xsl:template>	<xsl:template match="save:integer | save:signed-integer | save:string"		mode="attr">
		<xsl:param name="name" select="@name" />
		<xsl:param name="value" select="@value" />		<xsl:attribute name="{$name}"><xsl:value-of			select="normalize-space($value)" /></xsl:attribute>	</xsl:template>	<xsl:template match="save:select" mode="attr">
		<xsl:param name="name" select="@name" />		<xsl:variable name="option"			select="amber:getDictionaryOption(@dictionary-ref, @value)" />		<xsl:attribute name="{$name}">
			<xsl:value-of select="$option/@title | $option/@val[not($option/@title)]" />
		</xsl:attribute>	</xsl:template>

	<xsl:template match="save:group" mode="unknown">
		<unknown>
			<xsl:for-each select="*">
				<xsl:if test="position() &gt; 1">
					<xsl:text> </xsl:text>
				</xsl:if>
				<xsl:value-of select="@value" />
				<!-- <xsl:value-of select="str:align(@value, '000', 'right')"/> -->
			</xsl:for-each>
		</unknown>
	</xsl:template>
























	<xsl:template name="extract-dictionaries">
		<xsl:variable name="AM2"
			select="save:archive[@name='AM2_BLIT' or @name='AM2_CPU']" />
		<dictionary-list>
			<dictionary dictionary-id="events">
				<option key="0" val="valdyn" />
				<option key="1" val="grandfather" />
				<option key="2" val="flight:blue2green" />
				<option key="3" val="flight:blue2orange" />
				<option key="4" val="flight:green2blue" />
				<option key="5" val="flight:green2orange" />
				<option key="6" val="flight:orange2blue" />
				<option key="7" val="flight:orange2green" />
				<option key="8" val="game over" />
				<option key="255" val="-" />
			</dictionary>

			<dictionary dictionary-id="popup-bitmask">
				<option key="1" val="1" />
				<option key="2" val="2" />
				<option key="3" val="3" />
			</dictionary>

			<dictionary dictionary-id="sprite-cycles">
				<option key="0" val="idling" />
				<option key="1" val="melee attack" />
				<option key="2" val="ranged attack" />
				<option key="3" val="casting spell" />
				<option key="4" val="taking damage" />
				<option key="5" val="dying" />
				<option key="6" val="battle start" />
				<option key="7" val="?" />
			</dictionary>

			<dictionary dictionary-id="map-types">
				<option key="1" val="3D" />
				<option key="2" val="2D" />
			</dictionary>

			<dictionary dictionary-id="map-worlds">
				<option key="0" val="Lyramion" />
				<option key="1" val="Waldmond" />
				<option key="2" val="Wüstenmond" />
			</dictionary>

			<dictionary dictionary-id="character-types">
				<option key="0" val="Partymitglied" />
				<option key="1" val="NPC" />
				<option key="2" val="Monster" />
			</dictionary>

			<dictionary dictionary-id="monster-types">
				<option key="0" val="undead" />
				<option key="1" val="demon" />
				<option key="2" val="boss" />
				<option key="3" val="animal" />
			</dictionary>

			<dictionary dictionary-id="monster-images">
				<option key="1" val="Gargoyle" />
				<option key="2" val="Untoter" />
				<option key="3" val="Dämon" />
				<option key="4" val="Ork" />
				<option key="5" val="Echse" />
				<option key="6" val="Riese" />
				<option key="7" val="Krieger" />
				<option key="8" val="Morag Drache" />
				<option key="9" val="Golem" />
				<option key="10" val="Sansrie" />
				<option key="11" val="Schwarzmagier" />
				<option key="12" val="Minotaur" />
				<option key="13" val="Nera" />
				<option key="14" val="Magische Wache" />
				<option key="15" val="Feuerdrache" />
				<option key="16" val="Spinne" />
				<option key="17" val="Dieb" />
				<option key="18" val="Biest" />
				<option key="19" val="Energiekugel" />
				<option key="20" val="Morag Magier" />
				<option key="21" val="Antiker Wächter" />
				<option key="22" val="Fluchwespe" />
				<option key="23" val="Tornak" />
				<option key="24" val="Gizzek" />
				<option key="25" val="Morag Maschine" />
			</dictionary>

			<dictionary dictionary-id="item-status">
				<option key="0" val="identified" />
				<option key="1" val="broken" />
			</dictionary>

			<dictionary dictionary-id="item-properties">
				<option key="0" val="cursed" />
				<option key="1" val="purchaseable" />
				<option key="2" val="stackable" />
				<option key="3" val="combat-equippable" />
				<option key="4" val="useable" />
				<option key="5" val="readable" />
				<option key="6" val="cloneable" />
				<option key="7" val="?" />
			</dictionary>

			<dictionary dictionary-id="item-spell-types">
				<option key="0" val="Heilung" />
				<option key="1" val="Alchemie" />
				<option key="2" val="Mystik" />
				<option key="3" val="Destruktion" />
				<option key="4" />
				<option key="5" />
				<option key="6" val="Funktion" />
			</dictionary>
			
			<xsl:for-each select="$AM2//save:instruction[@name = 'spell-types']">
				<dictionary dictionary-id="spell-types">
					<xsl:for-each select="*">
						<option key="{position() - 1}" val="{@value}" />
					</xsl:for-each>
				</dictionary>
			</xsl:for-each>

			<dictionary dictionary-id="item-types">
				<option key="21" val="Zustandsveränderung" />
				<option key="1" val="Rüstung" />
				<option key="2" val="Helm" />
				<option key="3" val="Schuhe" />
				<option key="4" val="Schild" />
				<option key="5" val="Nahkampfwaffe" />
				<option key="6" val="Fernkampfwaffe" />
				<option key="7" val="Munition" />
				<option key="8" val="Textrolle" />
				<option key="9" val="Spruchrolle" />
				<option key="10" val="Trank" />
				<option key="11" val="Amulett" />
				<option key="12" val="Brosche" />
				<option key="13" val="Ring" />
				<option key="14" val="Gemme" />
				<option key="15" val="Werkzeug" />
				<option key="16" val="Schlüssel" />
				<option key="17" val="Gegenstand" />
				<option key="18" val="Mag. Gegenstand" />
				<option key="19" val="Spezialgegenstand" />
				<option key="20" val="Transportmittel" />
			</dictionary>

			<dictionary dictionary-id="item-slots">
				<option key="0" val="-" />
				<option key="1" val="Amulett" />
				<option key="2" val="Helm" />
				<option key="3" val="Brosche" />
				<option key="4" val="Waffe" />
				<option key="5" val="Rüstung" />
				<option key="6" val="Schild" />
				<option key="7" val="Ring" />
				<option key="8" val="Schuhe" />
			</dictionary>

			<dictionary dictionary-id="ailments">
				<option key="0" val="Irritation" />
				<option key="1" val="Verrücktheit" />
				<option key="2" val="Schlaf" />
				<option key="3" val="Panik" />
				<option key="4" val="Erblindet" />
				<option key="5" val="Stoned" />
				<option key="6" val="Erschöpfung" />
				<option key="7" val="-" />
				<option key="8" val="Gelähmt" />
				<option key="9" val="Vergiftet" />
				<option key="10" val="Versteinert" />
				<option key="11" val="Krank" />
				<option key="12" val="Alterung" />
				<option key="13" val="Tot (Leiche)" />
				<option key="14" val="Tot (Asche)" />
				<option key="15" val="Tot (Staub)" />
			</dictionary>

			<dictionary dictionary-id="ailments-healer">
				<option key="0" val="Gelähmt" />
				<option key="1" val="Vergiftet" />
				<option key="2" val="Versteinert" />
				<option key="3" val="Krank" />
				<option key="4" val="Alterung" />
				<option key="5" val="Tot (Leiche)" />
				<option key="6" val="Tot (Asche)" />
				<option key="7" val="Tot (Staub)" />
				<option key="8" val="Verrücktheit" />
				<option key="9" val="Erblindet" />
				<option key="10" val="Stoned" />
				<option key="11" val="HP" />
				<option key="12" val="Fluch" />
			</dictionary>

			<dictionary dictionary-id="award-operations">
				<option key="0" val="+=" />
				<option key="6" val="|=" />
			</dictionary>

			<dictionary dictionary-id="languages">
				<option key="0" val="Mensch" />
				<option key="1" val="Elfisch" />
				<option key="2" val="Zwergisch" />
				<option key="3" val="Gnomisch" />
				<option key="4" val="Sylphisch" />
				<option key="5" val="Felinisch" />
				<option key="6" val="Morag" />
				<option key="7" val="Tiersprache" />
			</dictionary>

			<dictionary dictionary-id="attributes">
				<option key="0" val="STÄ" description="Stärke" />
				<option key="1" val="INT" description="Intelligenz" />
				<option key="2" val="GES" description="Geschicklichkeit" />
				<option key="3" val="SCH" description="Schnelligkeit" />
				<option key="4" val="KON" description="Konstitution" />
				<option key="5" val="KAR" description="Karisma" />
				<option key="6" val="GLÜ" description="Glück" />
				<option key="7" val="A-M" description="Anti-Magie" />
			</dictionary>

			<dictionary dictionary-id="skills">
				<option key="0" val="ATT" description="Attacke" />
				<option key="1" val="PAR" description="Parade" />
				<option key="2" val="SCH" description="Schwimmen" />
				<option key="3" val="KRI" description="Kritische Treffer" />
				<option key="4" val="F-F" description="Fallen Finden" />
				<option key="5" val="F-E" description="Fallen Entschärfen" />
				<option key="6" val="S-Ö" description="Schlösser Knacken" />
				<option key="7" val="SUC" description="Suchen" />
				<option key="8" val="SRL" description="Spruchrollen Lesen" />
				<option key="9" val="M-B" description="Magie Benutzen" />
			</dictionary>

			<dictionary dictionary-id="equipment-slots">
				<option key="0" val="Amulett" />
				<option key="1" val="Helm" />
				<option key="2" val="Brosche" />
				<option key="3" val="Waffe" />
				<option key="4" val="Rüstung" />
				<option key="5" val="Schild" />
				<option key="6" val="Ring" />
				<option key="7" val="Schuhe" />
				<option key="8" val="Ring" />
			</dictionary>

			<dictionary dictionary-id="genders">
				<option key="0" val="männlich" />
				<option key="1" val="weiblich" />
			</dictionary>

			<dictionary dictionary-id="races">
				<option key="0" val="Mensch" />
				<option key="1" val="Elf" />
				<option key="2" val="Zwerg" />
				<option key="3" val="Gnom" />
				<option key="4" val="Halb-Elf" />
				<option key="5" val="Sylphe" />
				<option key="6" val="Feline" />
				<option key="7" val="Moraner" />
				<option key="8" val="Thalioner" />
				<option key="13" val="(leer)" />
				<option key="14" val="Tier" />
			</dictionary>

			<dictionary dictionary-id="classes">
				<option key="0" val="Abenteurer" />
				<option key="1" val="Krieger" />
				<option key="2" val="Paladin" />
				<option key="3" val="Dieb" />
				<option key="4" val="Ranger" />
				<option key="5" val="Heiler" />
				<option key="6" val="Alchemist" />
				<option key="7" val="Mystiker" />
				<option key="8" val="Magier" />
				<option key="9" val="Katze" />
				<option key="10" val="(leer)" />
			</dictionary>

			<dictionary dictionary-id="schools">
				<option key="0" val="Keine" />
				<option key="1" val="Weiß (Paladin)" />
				<option key="129" val="Weiß (Heiler)" />
				<option key="2" val="Blau (Abenteurer)" />
				<option key="130" val="Blau (Alchemist)" />
				<option key="4" val="Grün (Ranger)" />
				<option key="132" val="Grün (Mystiker)" />
				<option key="8" val="Schwarz (Magier)" />
			</dictionary>

			<dictionary dictionary-id="hands">
				<option key="0" val="Beide Hände frei" />
				<option key="1" val="Eine Hand frei" />
				<option key="2" val="Keine Hand frei" />
			</dictionary>

			<dictionary dictionary-id="fingers">
				<option key="0" val="Beide Finger frei" />
				<option key="1" val="Ein Finger frei" />
				<option key="2" val="Kein Finger frei" />
			</dictionary>

			<dictionary dictionary-id="ammunitions">
				<option key="0" val="-" />
				<option key="1" val="Schleuderstein" />
				<option key="2" val="Pfeil" />
				<option key="3" val="Bolzen" />
				<option key="4" val="Schleuderdolch" />
			</dictionary>

			<dictionary dictionary-id="event-types">
				<option key="0" val="debug" />
				<option key="1" val="teleport" />
				<option key="2" val="door" />
				<option key="3" val="chest" />
				<option key="4" val="popup text" />
				<option key="5" val="spinner" />
				<option key="6" val="take damage" />
				<option key="8" val="riddlemouth" />
				<option key="9" val="award" />
				<option key="10" val="set tile" />
				<option key="11" val="battle" />
				<option key="12" val="place" />
				<option key="13" val="if" />
				<option key="14" val="set" />
				<option key="15" val="if-1d100" />
				<option key="16" val="start" />
				<option key="18" val="create" />
				<option key="17" val="print text" />
				<option key="19" val="prompt" />
				<option key="20" val="play music" />
				<option key="21" val="exit" />
				<option key="22" val="spawn" />
				<option key="23" val="success" />
			</dictionary>

			<dictionary dictionary-id="event-trigger-types">
				<option key="0" val="mention keyword" />
				<option key="1" val="show item" />
				<option key="2" val="give item" />
				<option key="3" val="give gold" />
				<option key="4" val="give food" />
				<option key="5" val="ask to join group" />
				<option key="6" val="ask to leave group" />
				<option key="7" val="start conversation" />
				<option key="8" val="end conversation" />
			</dictionary>

			<dictionary dictionary-id="event-award-types">
				<option key="0" val="attribute" />
				<option key="7" val="language" />
				<option key="8" val="experience" />
				<option key="9" val="alkemstat" />
			</dictionary>

			<dictionary dictionary-id="event-set-types">
				<option key="0" val="global variable" />
				<option key="1" val="map event?" />
				<option key="4" val="map variable" />
				<option key="6" val="take item" />
				<option key="8" val="keyword" />
			</dictionary>

			<dictionary dictionary-id="event-create-types">
				<option key="0" val="item" />
				<option key="1" val="gold" />
				<option key="2" val="food" />
			</dictionary>

			<dictionary dictionary-id="event-if-types">
				<option key="0" val="global variable" />
				<option key="4" val="map variable" />
				<option key="5" val="partymember" />
				<option key="6" val="item owned" />
				<option key="7" val="item used" />
				<option key="9" val="exit" />
				<option key="14" val="touch-action" />
				<option key="20" val="inspect-action" />
			</dictionary>

			<dictionary dictionary-id="event-spawn-types">
				<option key="1" val="horse" />
				<option key="2" val="raft" />
				<option key="3" val="ship" />
				<option key="4" val="hoverdisc" />
				<option key="5" val="giant eagle" />
				<option key="6" val="cape" />
				<option key="7" val="magic broom" />
				<option key="8" val="swimming" />
				<option key="9" val="lizard" />
				<option key="10" val="sandship" />
			</dictionary>

			<dictionary dictionary-id="event-teleport-types">
				<option key="0" val="default" />
				<option key="1" val="teleporter" />
				<option key="2" val="windtor" />
				<option key="3" val="upwards" />
				<option key="5" val="downwards" />
			</dictionary>

			<dictionary dictionary-id="boolean">
				<option key="0" val="false" />
				<option key="1" val="true" />
				<option key="2" val="toggle" />
			</dictionary>

			<dictionary dictionary-id="boolean-reverse">
				<option key="0" val="true" />
				<option key="256" val="false" />
			</dictionary>

			<dictionary dictionary-id="proportionality">
				<option key="0" val="=" />
				<option key="1" val="&lt;" />
			</dictionary>

			<dictionary dictionary-id="target">
				<option key="0" val="leader" />
				<option key="1" val="everyone" />
			</dictionary>

			<dictionary dictionary-id="keywords">
				<option key="0" val="HALLO" />
				<option key="1" val="WEIN" />
				<option key="2" val="EINSTURZ" />
				<option key="3" val="WERKZEUG" />
				<option key="4" val="DIEBESGILDE" />
				<option key="5" val="BANDITEN" />
				<option key="6" val="GEGENSTAND" />
				<option key="7" val="SILK" />
				<option key="8" val="STADTGARTEN" />
				<option key="9" val="REISE" />
				<option key="10" val="NEWLAKE" />
				<option key="11" val="BRUDERSCHAFT" />
				<option key="12" val="GEGENMITTEL" />
				<option key="13" val="KATER" />
				<option key="14" val="TOCHTER" />
				<option key="15" val="SABINE" />
				<option key="16" val="BURNVILLE" />
				<option key="17" val="KATZE" />
				<option key="18" val="FELIX" />
				<option key="19" val="SANDRA" />
				<option key="20" val="KELLER" />
				<option key="21" val="VERRÜCKTER" />
				<option key="22" val="ORKS" />
				<option key="23" val="PROBLEME" />
				<option key="24" val="FEEN" />
				<option key="25" val="FEE" />
				<option key="26" val="PFERDE" />
				<option key="27" val="SILBERHAND" />
				<option key="28" val="WEINPOKALE" />
				<option key="29" val="HEIMAT" />
				<option key="30" val="SCHICKSAL" />
				<option key="31" val="GASTHOF" />
				<option key="32" val="DORINA" />
				<option key="33" val="BIER" />
				<option key="34" val="ZIMMER" />
				<option key="35" val="DOR GRESTIN" />
				<option key="36" val="MAGIE" />
				<option key="37" val="DOR KIREDON" />
				<option key="38" val="TORNAK" />
				<option key="39" val="TORNAK-EI" />
				<option key="40" val="EI" />
				<option key="41" val="SCHLÜSSEL" />
				<option key="42" val="MINE" />
				<option key="43" val="BERNSTEIN" />
				<option key="44" val="KIRE" />
				<option key="45" val="ÖFFNUNGSWORT" />
				<option key="46" val="PASTETEN" />
				<option key="47" val="BAUM" />
				<option key="48" val="SYLPHE" />
				<option key="49" val="SCHWESTER" />
				<option key="50" val="OKNARD" />
				<option key="51" val="SARIEL" />
				<option key="52" val="MAGIER" />
				<option key="53" val="SPHÄRE DER ÖFFNUNG" />
				<option key="54" val="RING" />
				<option key="55" val="BIBLIOTHEK" />
				<option key="56" val="LADEN" />
				<option key="57" val="KETNAR" />
				<option key="58" val="REISEN" />
				<option key="59" val="TRAUER" />
				<option key="60" val="LAGERKELLER" />
				<option key="61" val="TOR" />
				<option key="62" val="KARTOGRAPHIE" />
				<option key="63" val="KUNSTMALER" />
				<option key="64" val="MAGISCHE WÖRTER" />
				<option key="65" val="SCHNISM" />
				<option key="66" val="TEMPUS FUGIT" />
				<option key="67" val="RINGE" />
				<option key="68" val="REPERATUR" />
				<option key="69" val="FLUCH" />
				<option key="70" val="AMULETT" />
				<option key="71" val="PATIENT" />
				<option key="72" val="BRUDER" />
				<option key="73" val="BROG" />
				<option key="74" val="TURM" />
				<option key="75" val="DREK" />
				<option key="76" val="KRÄUTER" />
				<option key="77" val="FLIEGEN" />
				<option key="78" val="DÖNNER" />
				<option key="79" val="GRÜNER JUWEL" />
				<option key="80" val="PROBLEM" />
				<option key="81" val="NERA" />
				<option key="82" val="KAY" />
				<option key="83" val="BIEST" />
				<option key="84" val="XENOBIL" />
				<option key="85" val="SCHULE" />
				<option key="86" val="GADLON" />
				<option key="87" val="ÜBUNGSHALLEN" />
				<option key="88" val="RINAKLES" />
				<option key="89" val="DRACHEN" />
				<option key="90" val="BLADUS" />
				<option key="91" val="ANDARIEL" />
				<option key="92" val="DOBRANUR" />
				<option key="93" val="DAMBRANO" />
				<option key="94" val="HALFTON" />
				<option key="95" val="NETALDUR" />
				<option key="96" val="NASE" />
				<option key="97" val="NÄHNADEL" />
				<option key="98" val="SPIEGEL" />
				<option key="99" val="REIF" />
				<option key="100" val="NADEL" />
				<option key="101" val="TEST" />
				<option key="102" val="SORGEN" />
				<option key="103" val="HARFE" />
				<option key="104" val="BROM" />
				<option key="105" val="REITECHSE" />
				<option key="106" val="242" />
				<option key="107" val="301" />
				<option key="108" val="MORAG" />
				<option key="109" val="ANTONIUS" />
				<option key="110" val="VATER ANTONIUS" />
				<option key="111" val="GRYBAN" />
				<option key="112" val="ÄRGER" />
				<option key="113" val="GRUFTSCHLÜSSEL" />
				<option key="114" val="RUINEN" />
			</dictionary>

			<dictionary dictionary-id="spells-white">
				<option key="0" val="HAND AUFLEGEN" />
				<option key="1" val="FURCHT LÖSEN" />
				<option key="2" val="PANIK BESEITIGEN" />
				<option key="3" val="SCHATTEN LÖSEN" />
				<option key="4" val="BLINDHEIT HEILEN" />
				<option key="5" val="SCHMERZEN LINDERN" />
				<option key="6" val="KRANKHEIT HEILEN" />
				<option key="7" val="LEICHTE HEILUNG" />
				<option key="8" val="GIFT LÖSEN" />
				<option key="9" val="GIFT NEUTRALISIEREN" />
				<option key="10" val="MITTLERE HEILUNG" />
				<option key="11" val="VERTREIBE UNTOTE" />
				<option key="12" val="ZERSTÖRE UNTOTE" />
				<option key="13" val="HEILIGES WORT" />
				<option key="14" val="TOTE ERWECKEN" />
				<option key="15" val="ASCHE WANDELN" />
				<option key="16" val="STAUB WANDELN" />
				<option key="17" val="GROSSE HEILUNG" />
				<option key="18" val="MASSENHEILUNG" />
				<option key="19" val="WIEDERBELEBUNG" />
				<option key="20" val="STARRE LÖSEN" />
				<option key="21" val="LÄHMUNG HEILEN" />
				<option key="22" val="ALTERUNG HEILEN" />
				<option key="23" val="ALTERUNG STOPPEN" />
				<option key="24" val="STEIN ZU FLEISCH" />
				<option key="25" val="AUFWECKEN" />
				<option key="26" val="IRRITATION HEILEN" />
				<option key="27" val="DROGEN AUFLÖSEN" />
				<option key="28" val="VERRÜCKTHEIT HEILEN" />
				<option key="29" val="AUSDAUER KRÄFTIGEN" />
			</dictionary>

			<dictionary dictionary-id="spells-blue">
				<option key="0" val="GEGENSTAND LADEN" />
				<option key="1" val="LICHT" />
				<option key="2" val="MAGISCHE FACKEL" />
				<option key="3" val="MAGISCHE LATERNE" />
				<option key="4" val="IMITIERTE SONNE" />
				<option key="5" val="GEISTERWAFFE" />
				<option key="6" val="ESSEN ERSCHAFFEN" />
				<option key="7" val="FLUCH BESEITIGEN" />
				<option key="8" val="BLINK" />
				<option key="9" val="SPRUNG" />
				<option key="10" val="FLUCHT" />
				<option key="11" val="WORT DES MARKIERENS" />
				<option key="12" val="WORT DER RÜCKKEHR" />
				<option key="13" val="MAGISCHER SHIELD" />
				<option key="14" val="MAGISCHE WAND" />
				<option key="15" val="MAGISCHE BARRIERE" />
				<option key="16" val="MAGISCHE WAFFE" />
				<option key="17" val="MAGISCHER ANGRIFF" />
				<option key="18" val="MAGISCHE ATTACKE" />
				<option key="19" val="LEVITATION" />
				<option key="20" val="ANTI-MAGIE WAND" />
				<option key="21" val="ANTI-MAGIE SPHÄRE" />
				<option key="22" val="ALCHEMISTISCHER GLOBUS" />
				<option key="23" val="HAST" />
				<option key="24" val="MASSENHAST" />
				<option key="25" val="REPARIERE GEGENSTAND" />
				<option key="26" val="VERDOPPELE GEGENSTAND" />
				<option key="27" val="LP-STEHLER" />
				<option key="28" val="SP-SAUGER" />
			</dictionary>

			<dictionary dictionary-id="spells-green">
				<option key="0" val="MONSTERWISSEN" />
				<option key="1" val="IDENTIFIKATION" />
				<option key="2" val="WISSEN" />
				<option key="3" val="HELLSICHT" />
				<option key="4" val="WAHRHEIT SEHEN" />
				<option key="5" val="KARTENSCHAU" />
				<option key="6" val="MAGISCHER KOMPASS" />
				<option key="7" val="FALLEN FINDEN" />
				<option key="8" val="MONSTER FINDEN" />
				<option key="9" val="PERSONEN FINDEN" />
				<option key="10" val="GEHEIMTÜREN FINDEN" />
				<option key="11" val="MYSTISCHE KARTENZEICHNUNG" />
				<option key="12" val="MYSTISCHE KARTE I" />
				<option key="13" val="MYSTISCHE KARTE II" />
				<option key="14" val="MYSTISCHE KARTE III" />
				<option key="15" val="MYSTISCHE GLOBUS" />
				<option key="16" val="ZEIGE MONSTER-LP" />
			</dictionary>

			<dictionary dictionary-id="spells-black">
				<option key="0" val="MAGISCHES GESCHOSS" />
				<option key="1" val="MAGISCHER PFEIL" />
				<option key="2" val="LÄHMEN" />
				<option key="3" val="VERGIFTEN" />
				<option key="4" val="VERSTEINERN" />
				<option key="5" val="KRANKHEIT" />
				<option key="6" val="ALTERN" />
				<option key="7" val="IRRITATION" />
				<option key="8" val="VERRÜCKTHEIT" />
				<option key="9" val="SCHLAF" />
				<option key="10" val="FURCHT" />
				<option key="11" val="BLENDEN" />
				<option key="12" val="DROGEN" />
				<option key="13" val="OPFER AUFLÖSEN" />
				<option key="14" val="DRECKSCHLEUDER" />
				<option key="15" val="STEINSCHLAG" />
				<option key="16" val="ERDRUTSCH" />
				<option key="17" val="ERDBEBEN" />
				<option key="18" val="WINDTEUFEL" />
				<option key="19" val="WINDHEULER" />
				<option key="20" val="DONNERSCHLAG" />
				<option key="21" val="WIRBELSTURM" />
				<option key="22" val="FEUERSTRAHL" />
				<option key="23" val="FEUERBALL" />
				<option key="24" val="FEUERSTURM" />
				<option key="25" val="FEUERSÄULE" />
				<option key="26" val="WASSERFALL" />
				<option key="27" val="EISBALL" />
				<option key="28" val="EISSTURM" />
				<option key="29" val="EISSCHAUER" />
				<option key="30" val="EISSCHAUER" />
			</dictionary>

			<dictionary dictionary-id="items">
				<option key="0" val="-" />
				<option key="1" val="GELÄHMT" />
				<option key="2" val="VERGIFTET" />
				<option key="3" val="VERSTEINERT" />
				<option key="4" val="KRANK" />
				<option key="5" val="ALTERUNG" />
				<option key="6" val="TOT" />
				<option key="7" val="VERRÜCKT" />
				<option key="8" val="ERBLINDET" />
				<option key="9" val="STONED" />
				<option key="10" val="HAND AUFLEGEN" />
				<option key="11" val="FURCHT LÖSEN" />
				<option key="12" val="PANIK BESEITIGEN" />
				<option key="13" val="SCHATTEN LÖSEN" />
				<option key="14" val="BLINDHEIT HEILEN" />
				<option key="15" val="SCHMERZEN LINDERN" />
				<option key="16" val="KRANKHEIT HEILEN" />
				<option key="17" val="LEICHTE HEILUNG" />
				<option key="18" val="GIFT LÖSEN" />
				<option key="19" val="GIFT NEUTRAL." />
				<option key="20" val="MITTLERE HEILUNG" />
				<option key="21" val="VERTREIBE UNTOTEN" />
				<option key="22" val="ZERSTÖRE UNTOTE" />
				<option key="23" val="HEILIGES WORT" />
				<option key="24" val="TOTE ERWECKEN" />
				<option key="25" val="ASCHE WANDELN" />
				<option key="26" val="STAUB WANDELN" />
				<option key="27" val="GROSSE HEILUNG" />
				<option key="28" val="MASSENHEILUNG" />
				<option key="29" val="WIEDERBELEBUNG" />
				<option key="30" val="STARRE AUFLÖSEN" />
				<option key="31" val="LÄHMUNG HEILEN" />
				<option key="32" val="ALTERUNG HEILEN" />
				<option key="33" val="ALTERUNG STOPPEN" />
				<option key="34" val="STEIN ZU FLEISCH" />
				<option key="35" val="AUFWECKEN" />
				<option key="36" val="IRRITATION HEILEN" />
				<option key="37" val="DROGEN AUFLÖSEN" />
				<option key="38" val="VERRÜCKTHEIT HEILEN" />
				<option key="39" val="AUSDAUER KRÄFTIGEN" />
				<option key="40" val="GEGENSTAND LADEN" />
				<option key="41" val="LICHT" />
				<option key="42" val="MAGISCHE FACKEL" />
				<option key="43" val="MAGISCHE LATERNE" />
				<option key="44" val="IMITIERTE SONNE" />
				<option key="45" val="GEISTERWAFFE" />
				<option key="46" val="ESSEN ERSCHAFFEN" />
				<option key="47" val="FLUCH BESEITIGEN" />
				<option key="48" val="BLINK" />
				<option key="49" val="SPRUNG" />
				<option key="50" val="FLUCHT" />
				<option key="51" val="WORT DES MARKIERENS" />
				<option key="52" val="WORT DER RÜCKKEHR" />
				<option key="53" val="MAGISCHES SCHILD" />
				<option key="54" val="MAGISCHE WAND" />
				<option key="55" val="MAGISCHE BARRIERE" />
				<option key="56" val="MAGISCHE WAFFE" />
				<option key="57" val="MAGISCHER ANGRIFF" />
				<option key="58" val="MAGISCHE ATTACKE" />
				<option key="59" val="LEVITATION" />
				<option key="60" val="ANTI MAGIE WAND" />
				<option key="61" val="ANTI MAGIE SPHERE" />
				<option key="62" val="ALCHEMISTIS. GLOBE" />
				<option key="63" val="HAST" />
				<option key="64" val="MASSENHAST" />
				<option key="65" val="REPARIERE GEGENST." />
				<option key="66" val="VERDOPPLE GEGENST." />
				<option key="67" val="LP-STEHLER" />
				<option key="68" val="SP-SAUGER" />
				<option key="69" val="ALCHEMIE SPRUCH -30" />
				<option key="70" val="MONSTER WISSEN" />
				<option key="71" val="IDENTIFIKATION" />
				<option key="72" val="WISSEN" />
				<option key="73" val="HELLSICHT" />
				<option key="74" val="WAHRHEIT SEHEN" />
				<option key="75" val="KARTENSCHAU" />
				<option key="76" val="MAGISCHER KOMPASS" />
				<option key="77" val="FALLEN FINDEN" />
				<option key="78" val="MONSTER FINDEN" />
				<option key="79" val="PERSONEN FINDEN" />
				<option key="80" val="GEHEIMTÜREN FINDEN" />
				<option key="81" val="MYSTISCHE KARTENZE." />
				<option key="82" val="MYSTISCHE KARTE I" />
				<option key="83" val="MYSTISCHE KARTE II" />
				<option key="84" val="MYSTISCHE KARTE III" />
				<option key="85" val="MYSTISCHER GLOBE" />
				<option key="86" val="ZEIGE MONSTER LP" />
				<option key="87" val="MYSTIK SPRUCH -18-" />
				<option key="88" val="MYSTIK SPRUCH -19-" />
				<option key="89" val="MYSTIK SPRUCH -20-" />
				<option key="90" val="MYSTIK SPRUCH -21-" />
				<option key="91" val="MYSTIK SPRUCH -22-" />
				<option key="92" val="MYSTIK SPRUCH -23-" />
				<option key="93" val="MYSTIK SPRUCH -24-" />
				<option key="94" val="MYSTIK SPRUCH -25-" />
				<option key="95" val="MYSTIK SPRUCH -26-" />
				<option key="96" val="MYSTIK SPRUCH -27-" />
				<option key="97" val="MYSTIK SPRUCH -28-" />
				<option key="98" val="MYSTIK SPRUCH -29-" />
				<option key="99" val="MYSTIK SPRUCH -30-" />
				<option key="100" val="MAGISCHES GESCHOSS" />
				<option key="101" val="MAGISCHE PFEILE" />
				<option key="102" val="LÄHMEN" />
				<option key="103" val="VERGIFTEN" />
				<option key="104" val="VERSTEINERN" />
				<option key="105" val="KRANKHEIT" />
				<option key="106" val="ALTERN" />
				<option key="107" val="IRRITATION" />
				<option key="108" val="VERRÜCKTHEIT" />
				<option key="109" val="SCHLAF" />
				<option key="110" val="FURCHT" />
				<option key="111" val="BLENDEN" />
				<option key="112" val="DROGEN" />
				<option key="113" val="OPFER AUFLÖSEN" />
				<option key="114" val="DRECKSCHLEUDER" />
				<option key="115" val="STEINSCHLAG" />
				<option key="116" val="ERDRUTSCH" />
				<option key="117" val="ERDBEBEN" />
				<option key="118" val="WINDTEUFEL" />
				<option key="119" val="WINDHEULER" />
				<option key="120" val="DONNERSCHLAG" />
				<option key="121" val="WIRBELSTURM" />
				<option key="122" val="FEUERSTRAHL" />
				<option key="123" val="FEUERBALL" />
				<option key="124" val="FEUERSTURM" />
				<option key="125" val="FEUERSÄULE" />
				<option key="126" val="WASSERFALL" />
				<option key="127" val="EISBALL" />
				<option key="128" val="EISSTURM" />
				<option key="129" val="EISSCHAUER" />
				<option key="130" val="FACKEL" />
				<option key="131" val="KRISTALLKUGEL" />
				<option key="132" val="KOMPASS" />
				<option key="133" val="MAGISCHES BILD" />
				<option key="134" val="WINDKETTE" />
				<option key="135" val="WINDPERLE" />
				<option key="136" val="KARTENLOKATOR" />
				<option key="137" val="UHR" />
				<option key="138" val="DIETRICH" />
				<option key="139" val="MAGISCHE FLUGSCHEIB" />
				<option key="140" val="HEXENBESEN" />
				<option key="141" val="BRECHEISEN" />
				<option key="142" val="SPITZHACKE" />
				<option key="143" val="SCHAUFEL" />
				<option key="144" val="SEIL" />
				<option key="145" val="RUNENALPHABET" />
				<option key="146" val="GEMME" />
				<option key="147" val="DIAMANT" />
				<option key="148" val="SMARAGD" />
				<option key="149" val="RUBIN" />
				<option key="150" val="ERDSTEIN" />
				<option key="151" val="BERNSTEIN" />
				<option key="152" val="REGENBOGENSTEIN" />
				<option key="153" val="BERGKRISTALL" />
				<option key="154" val="TOPAZ" />
				<option key="155" val="FLÖTE" />
				<option key="156" val="PILZ" />
				<option key="157" val="PILZ" />
				<option key="158" val="ELFENHARFE" />
				<option key="159" val="KRISTALLHARFE" />
				<option key="160" val="KARTE VON LYRAMION" />
				<option key="161" val="MAGISCHE LANDKARTE" />
				<option key="162" val="HUT" />
				<option key="163" val="HORNHELM" />
				<option key="164" val="STAHLHELM" />
				<option key="165" val="KLEIDUNG" />
				<option key="166" val="ROBE" />
				<option key="167" val="LEDER" />
				<option key="168" val="WATTIERTES LEDER" />
				<option key="169" val="BESCHLAGENES LEDER" />
				<option key="170" val="KETTENHEMD" />
				<option key="171" val="BÄNDERPANZER" />
				<option key="172" val="PLATTENPANZER" />
				<option key="173" val="RITTERRÜSTUNG" />
				<option key="174" val="SANDALEN" />
				<option key="175" val="LEDERSCHUHE" />
				<option key="176" val="LEDERSTIEFEL" />
				<option key="177" val="BUCKLER" />
				<option key="178" val="RUNDSCHILD" />
				<option key="179" val="KLEINES TURMSCHILD" />
				<option key="180" val="GROSSES TURMSCHILD" />
				<option key="181" val="MESSER" />
				<option key="182" val="DOLCH" />
				<option key="183" val="KURZSCHWERT" />
				<option key="184" val="LANGSCHWERT" />
				<option key="185" val="SCIMITAR" />
				<option key="186" val="ZWEIHÄNDER" />
				<option key="187" val="HEILIGES SCHWERT" />
				<option key="188" val="KAMPFSTAB" />
				<option key="189" val="KEULE" />
				<option key="190" val="DRESCHFLEGEL" />
				<option key="191" val="STREITKOLBEN" />
				<option key="192" val="MORGENSTERN" />
				<option key="193" val="KAMPFHAMMER" />
				<option key="194" val="AXT" />
				<option key="195" val="KAMPFAXT" />
				<option key="196" val="TRIDENT" />
				<option key="197" val="PEITSCHE" />
				<option key="198" val="SCHLEUDER" />
				<option key="199" val="KURZBOGEN" />
				<option key="200" val="LANGBOGEN" />
				<option key="201" val="ARMBRUST" />
				<option key="202" val="SCHLEUDERSTEINE" />
				<option key="203" val="PFEIL" />
				<option key="204" val="BOLZEN" />
				<option key="205" val="SCHRANKSCHLÜSSEL" />
				<option key="206" val="TESTAMENT" />
				<option key="207" val="EISENRING" />
				<option key="208" val="SILBERBESTECK" />
				<option key="209" val="SHANDRAS BERNSTEIN" />
				<option key="210" val="BUCH DER ARACHNIDEN" />
				<option key="211" val="HEILTRANK I" />
				<option key="212" val="HEILTRANK II" />
				<option key="213" val="HEILTRANK III" />
				<option key="214" val="HEILTRANK IV" />
				<option key="215" val="ALLHEILUNGS PHIOLE" />
				<option key="216" val="STÄRKUNGS PHIOLE" />
				<option key="217" val="ENTGIFTUNGS PHIOLE" />
				<option key="218" val="LEERE PHIOLE" />
				<option key="219" val="JUNGBRUNNEN PHIOLE" />
				<option key="220" val="SPRUCHPUNKTE I" />
				<option key="221" val="SPRUCHPUNKTE II" />
				<option key="222" val="SPRUCHPUNKTE III" />
				<option key="223" val="SPRUCHPUNKTE IV" />
				<option key="224" val="SPRUCHPUNKTE V" />
				<option key="225" val="STÄRKE TRUNK" />
				<option key="226" val="INTELLIGENZ TRUNK" />
				<option key="227" val="GESCHICKLICH. TRUNK" />
				<option key="228" val="SCHNELLICHKE. TRUNK" />
				<option key="229" val="KONSTITUTION TRUNK" />
				<option key="230" val="KARISMA TRUNK" />
				<option key="231" val="STEINSCHLEUDER" />
				<option key="232" val="MANDO'S SCHWERT" />
				<option key="233" val="MAGISCHE PFEILE" />
				<option key="234" val="PERLMUTT KETTE" />
				<option key="235" val="NETSRAKS ZAUBERSTAB" />
				<option key="236" val="WUNSCHMÜNZEN" />
				<option key="237" val="NECROMANTEN DOLCH" />
				<option key="238" val="NECROMANTEN BROSCHE" />
				<option key="239" val="EI" />
				<option key="240" val="BERNSTEINBROCKEN" />
				<option key="241" val="DREBLIN'S NOTIZEN" />
				<option key="242" val="BLASENFRUCHT" />
				<option key="243" val="LATERNE" />
				<option key="244" val="TEXTROLLE" />
				<option key="245" val="MAGENBITTER" />
				<option key="246" val="TÜRSCHLÜSSEL (I)" />
				<option key="247" val="TÜRSCHLÜSSEL (II)" />
				<option key="248" val="TARNKAPPE" />
				<option key="249" val="SCHATTENGÜRTEL" />
				<option key="250" val="FLINKSCHUHE" />
				<option key="251" val="MÖRDERKLINGE" />
				<option key="252" val="ANTI-MAGIE TRUNK" />
				<option key="253" val="GEGENMITTEL" />
				<option key="254" val="WASSER DES LEBENS" />
				<option key="255" val="SUMPFLILIE" />
				<option key="256" val="ANTONIUS SCHLÜSSEL" />
				<option key="257" val="SANDRA'S SCHLÜSSEL" />
				<option key="258" val="BURNVILLE SCHLÜSSEL" />
				<option key="259" val="HEILIGES HORN" />
				<option key="260" val="GALA'S STAB" />
				<option key="261" val="ZELLENSCHLÜSSEL" />
				<option key="262" val="ALTE ROBE" />
				<option key="263" val="AMULETT B O S" />
				<option key="264" val="AMULETT T A R" />
				<option key="265" val="AMULETT T A R B O S" />
				<option key="266" val="SILBERHAND" />
				<option key="267" val="NAGIERS BRIEF" />
				<option key="268" val="KOPF HÜGELRIESEN" />
				<option key="269" val="AMTSKETTE" />
				<option key="270" val="GOLDENE HUFEISEN" />
				<option key="271" val="RING DES SOBEK" />
				<option key="272" val="GOLDENER WEINPOKAL" />
				<option key="273" val="GOLDBROSCHE" />
				<option key="274" val="ZETTEL DES BANDITEN" />
				<option key="275" val="SCHATTENLEDER" />
				<option key="276" val="FEUERBRAND" />
				<option key="277" val="ZIELBOGEN" />
				<option key="278" val="SONNENHELM" />
				<option key="279" val="NAGIERS SCHLÜSSEL" />
				<option key="280" val="KIRE'S BRIEF" />
				<option key="281" val="FEUERSTEIN &amp; EISEN" />
				<option key="282" val="GALASTEIN" />
				<option key="283" val="SELTSAMER STEIN" />
				<option key="284" val="GEFÄNGNISSSCHLÜSSEL" />
				<option key="285" val="WURFSICHEL" />
				<option key="286" val="ROBE DES MAGUS" />
				<option key="287" val="SPHÄRE DER ÖFFNUNG" />
				<option key="288" val="ALKEM'S RING" />
				<option key="289" val="KRYPTA SCHLÜSSEL" />
				<option key="290" val="LICH KRONE" />
				<option key="291" val="KIRE'S SCHLÜSSEL" />
				<option key="292" val="MINENSCHLÜSSEL" />
				<option key="293" val="DORINA'S BROSCHE" />
				<option key="294" val="FERRIN'S SCHLÜSSEL" />
				<option key="295" val="FERRIN'S LIKÖR" />
				<option key="296" val="FERRIN'S ARMBRUST" />
				<option key="297" val="BOLLGAR SCHLÜSSEL" />
				<option key="298" val="KEULE DER GALA" />
				<option key="299" val="ROBE DER GALA" />
				<option key="300" val="SABINE'S TAGEBUCH" />
				<option key="301" val="AMBERMOON BILD" />
				<option key="302" val="RING DER STÄRKE" />
				<option key="303" val="RING DER INTELLI." />
				<option key="304" val="RING DER GESCHICK." />
				<option key="305" val="RING DER SCHNELL." />
				<option key="306" val="RING DER KONSTIT." />
				<option key="307" val="KARISMA RING" />
				<option key="308" val="GLÜCKS RING" />
				<option key="309" val="ANTIMAGIE RING" />
				<option key="310" val="MOND AMULETT" />
				<option key="311" val="VALDYN'S LEDERCAPE" />
				<option key="312" val="VALDYNS LEDERSCHUHE" />
				<option key="313" val="VALDYN'S SCHWERT" />
				<option key="314" val="VALDYN'S AMULETT" />
				<option key="315" val="LUMINOR'S SCHLÜSSEL" />
				<option key="316" val="EISWASSER" />
				<option key="317" val="KALMIR KRAUT" />
				<option key="318" val="DREK'S SCHLÜSSEL" />
				<option key="319" val="DÄMONENSPIESS" />
				<option key="320" val="XENOBIL HOLZ" />
				<option key="321" val="STAB DES AUFBAUS" />
				<option key="322" val="MITRHIL HEMD" />
				<option key="323" val="MAGISCHE BOLZEN" />
				<option key="324" val="AROMATISCHES KRAUT" />
				<option key="325" val="LEUCHTENDE BLÜTEN" />
				<option key="326" val="LÄNGLICHE BLÄTTER" />
				<option key="327" val="BLAUE HALME" />
				<option key="328" val="FUNKELNDE PILZE" />
				<option key="329" val="KIRE'S BUCH" />
				<option key="330" val="ZWERGENAXT" />
				<option key="331" val="ZWERGENSCHLEUDER" />
				<option key="332" val="ZIELBROSCHE" />
				<option key="333" val="PARADERING" />
				<option key="334" val="ÖFFNUNGSSTAB" />
				<option key="335" val="SCHLEUDERMESSER" />
				<option key="336" val="SCHLEUDERDOLCH" />
				<option key="337" val="SCHNELLSTICH" />
				<option key="338" val="DOLCHSCHLEUDER" />
				<option key="339" val="WINDTOR BUCH" />
				<option key="340" val="SCHREIN SCHLÜSSEL" />
				<option key="341" val="BRALUM'S SCHLÜSSEL" />
				<option key="342" val="NERA'S RING" />
				<option key="343" val="DÄMONENSCHLAF" />
				<option key="344" val="FEUERDISTEL" />
				<option key="345" val="REZEPT" />
				<option key="346" val="DÖNNER'S SCHLÜSSEL" />
				<option key="347" val="BLITZSTIEFEL" />
				<option key="348" val="KAY'S AMULETT" />
				<option key="349" val="NOREL'S SCHLÜSSEL" />
				<option key="350" val="ELFENBOGEN" />
				<option key="351" val="MAGISCHE WURFAXT" />
				<option key="352" val="SANSRIE'S BLUT" />
				<option key="353" val="PELANI'S SCHLÜSSEL" />
				<option key="354" val="KRISTALLSAITEN" />
				<option key="355" val="SCHLANGENSTEIN" />
				<option key="356" val="ZEITSTOPPER" />
				<option key="357" val="SANSRIE'S SCHLÜSSEL" />
				<option key="358" val="SANSRIES HALSBAND" />
				<option key="359" val="SANSRIE'S RING" />
				<option key="360" val="SCHLANGENHELM" />
				<option key="361" val="MAGIERSTIEFEL" />
				<option key="362" val="ANTIKES BUCH" />
				<option key="363" val="ANTIKER GEGENSTAND" />
				<option key="364" val="GRAV. SCHEIBE GELB" />
				<option key="365" val="GRAVIERTE KUGEL ROT" />
				<option key="366" val="BUCH SCHLÜSSEL" />
				<option key="367" val="GRAVIER. KUGEL GELB" />
				<option key="368" val="GRAV. SCHEIBE GRÜN" />
				<option key="369" val="GRAV. SCHEIBE BLAU" />
				<option key="370" val="HANGARSCHLÜSSEL" />
				<option key="371" val="S'OREL SCHLÜSSEL" />
				<option key="372" val="S'OREL NACHRICHTEN" />
				<option key="373" val="PALADIN BUCH" />
				<option key="374" val="KRÄUTER BUCH" />
				<option key="375" val="AMBERSTAR" />
				<option key="376" val="AMBERMOON NOVELLE" />
				<option key="377" val="BLAUER NAVSTEIN" />
				<option key="378" val="GRÜNER NAVSTEIN" />
				<option key="379" val="GELBER NAVSTEIN" />
				<option key="380" val="STINKENDER PILZ" />
				<option key="381" val="KIRE'S NACHRICHT" />
				<option key="382" val="MORAG ROBE" />
				<option key="383" val="MORAG DART" />
				<option key="384" val="TRUHENSCHLÜSSEL" />
				<option key="385" val="ZELLENSCHLÜSSEL" />
				<option key="386" val="MONSTERAUGE" />
				<option key="387" val="MORAG KARTE" />
				<option key="388" val="PALAST S'OREL" />
				<option key="389" val="PALAST S'ARIN" />
				<option key="390" val="PALAST S'ENDAR" />
				<option key="391" val="PALAST S'TROG" />
				<option key="392" val="PALAST S'KAT" />
				<option key="393" val="PALAST S'LORWIN" />
				<option key="394" val="MORAG SCHWERT" />
				<option key="395" val="MORAG SCHILD" />
				<option key="396" val="MORAG RÜSTUNG" />
				<option key="397" val="BESTELLUNG SANDBOOT" />
				<option key="398" val="MORAG WASSERFLASCHE" />
				<option key="399" val="LÄHMUNG HEILEN" />
				<option key="400" val="GRUFTSCHLÜSSEL" />
				<option key="401" val="SCHATZSCHLÜSSEL 1" />
				<option key="402" val="SCHATZSCHLÜSSEL 2" />
			</dictionary>

			<dictionary dictionary-id="partymembers">
				<option key="1" val="THALION" />
				<option key="2" val="NETSRAK" />
				<option key="3" val="MANDO" />
				<option key="4" val="ERIK" />
				<option key="5" val="CHRIS" />
				<option key="6" val="MONIKA" />
				<option key="7" val="TAR DER DUNKLE" />
				<option key="8" val="EGIL" />
				<option key="9" val="SELENA" />
				<option key="10" val="NELVIN" />
				<option key="11" val="SABINE" />
				<option key="12" val="VALDYN" />
				<option key="13" val="TARGOR" />
				<option key="14" val="LEONARIA" />
				<option key="15" val="GRYBAN" />
			</dictionary>

			<dictionary dictionary-id="map-directions">
				<option key="0" val="north" />
				<option key="1" val="east" />
				<option key="2" val="south" />
				<option key="3" val="west" />
				<option key="4" val="no change" />
			</dictionary>

			<dictionary dictionary-id="map-ids">
				<option key="0" val="-" />
				<option key="1" val="Lyramion (000|000)" />
				<option key="2" val="Lyramion (050|000)" />
				<option key="3" val="Lyramion (100|000)" />
				<option key="4" val="Lyramion (150|000)" />
				<option key="5" val="Lyramion (200|000)" />
				<option key="6" val="Lyramion (250|000)" />
				<option key="7" val="Lyramion (300|000)" />
				<option key="8" val="Lyramion (350|000)" />
				<option key="9" val="Lyramion (400|000)" />
				<option key="10" val="Lyramion (450|000)" />
				<option key="11" val="Lyramion (500|000)" />
				<option key="12" val="Lyramion (550|000)" />
				<option key="13" val="Lyramion (600|000)" />
				<option key="14" val="Lyramion (650|000)" />
				<option key="15" val="Lyramion (700|000)" />
				<option key="16" val="Lyramion (750|000)" />
				<option key="17" val="Lyramion (000|050)" />
				<option key="18" val="Lyramion (050|050)" />
				<option key="19" val="Lyramion (100|050)" />
				<option key="20" val="Lyramion (150|050)" />
				<option key="21" val="Lyramion (200|050)" />
				<option key="22" val="Lyramion (250|050)" />
				<option key="23" val="Lyramion (300|050)" />
				<option key="24" val="Lyramion (350|050)" />
				<option key="25" val="Lyramion (400|050)" />
				<option key="26" val="Lyramion (450|050)" />
				<option key="27" val="Lyramion (500|050)" />
				<option key="28" val="Lyramion (550|050)" />
				<option key="29" val="Lyramion (600|050)" />
				<option key="30" val="Lyramion (650|050)" />
				<option key="31" val="Lyramion (700|050)" />
				<option key="32" val="Lyramion (750|050)" />
				<option key="33" val="Lyramion (000|100)" />
				<option key="34" val="Lyramion (050|100)" />
				<option key="35" val="Lyramion (100|100)" />
				<option key="36" val="Lyramion (150|100)" />
				<option key="37" val="Lyramion (200|100)" />
				<option key="38" val="Lyramion (250|100)" />
				<option key="39" val="Lyramion (300|100)" />
				<option key="40" val="Lyramion (350|100)" />
				<option key="41" val="Lyramion (400|100)" />
				<option key="42" val="Lyramion (450|100)" />
				<option key="43" val="Lyramion (500|100)" />
				<option key="44" val="Lyramion (550|100)" />
				<option key="45" val="Lyramion (600|100)" />
				<option key="46" val="Lyramion (650|100)" />
				<option key="47" val="Lyramion (700|100)" />
				<option key="48" val="Lyramion (750|100)" />
				<option key="49" val="Lyramion (000|150)" />
				<option key="50" val="Lyramion (050|150)" />
				<option key="51" val="Lyramion (100|150)" />
				<option key="52" val="Lyramion (150|150)" />
				<option key="53" val="Lyramion (200|150)" />
				<option key="54" val="Lyramion (250|150)" />
				<option key="55" val="Lyramion (300|150)" />
				<option key="56" val="Lyramion (350|150)" />
				<option key="57" val="Lyramion (400|150)" />
				<option key="58" val="Lyramion (450|150)" />
				<option key="59" val="Lyramion (500|150)" />
				<option key="60" val="Lyramion (550|150)" />
				<option key="61" val="Lyramion (600|150)" />
				<option key="62" val="Lyramion (650|150)" />
				<option key="63" val="Lyramion (700|150)" />
				<option key="64" val="Lyramion (750|150)" />
				<option key="65" val="Lyramion (000|200)" />
				<option key="66" val="Lyramion (050|200)" />
				<option key="67" val="Lyramion (100|200)" />
				<option key="68" val="Lyramion (150|200)" />
				<option key="69" val="Lyramion (200|200)" />
				<option key="70" val="Lyramion (250|200)" />
				<option key="71" val="Lyramion (300|200)" />
				<option key="72" val="Lyramion (350|200)" />
				<option key="73" val="Lyramion (400|200)" />
				<option key="74" val="Lyramion (450|200)" />
				<option key="75" val="Lyramion (500|200)" />
				<option key="76" val="Lyramion (550|200)" />
				<option key="77" val="Lyramion (600|200)" />
				<option key="78" val="Lyramion (650|200)" />
				<option key="79" val="Lyramion (700|200)" />
				<option key="80" val="Lyramion (750|200)" />
				<option key="81" val="Lyramion (000|250)" />
				<option key="82" val="Lyramion (050|250)" />
				<option key="83" val="Lyramion (100|250)" />
				<option key="84" val="Lyramion (150|250)" />
				<option key="85" val="Lyramion (200|250)" />
				<option key="86" val="Lyramion (250|250)" />
				<option key="87" val="Lyramion (300|250)" />
				<option key="88" val="Lyramion (350|250)" />
				<option key="89" val="Lyramion (400|250)" />
				<option key="90" val="Lyramion (450|250)" />
				<option key="91" val="Lyramion (500|250)" />
				<option key="92" val="Lyramion (550|250)" />
				<option key="93" val="Lyramion (600|250)" />
				<option key="94" val="Lyramion (650|250)" />
				<option key="95" val="Lyramion (700|250)" />
				<option key="96" val="Lyramion (750|250)" />
				<option key="97" val="Lyramion (000|300)" />
				<option key="98" val="Lyramion (050|300)" />
				<option key="99" val="Lyramion (100|300)" />
				<option key="100" val="Lyramion (150|300)" />
				<option key="101" val="Lyramion (200|300)" />
				<option key="102" val="Lyramion (250|300)" />
				<option key="103" val="Lyramion (300|300)" />
				<option key="104" val="Lyramion (350|300)" />
				<option key="105" val="Lyramion (400|300)" />
				<option key="106" val="Lyramion (450|300)" />
				<option key="107" val="Lyramion (500|300)" />
				<option key="108" val="Lyramion (550|300)" />
				<option key="109" val="Lyramion (600|300)" />
				<option key="110" val="Lyramion (650|300)" />
				<option key="111" val="Lyramion (700|300)" />
				<option key="112" val="Lyramion (750|300)" />
				<option key="113" val="Lyramion (000|350)" />
				<option key="114" val="Lyramion (050|350)" />
				<option key="115" val="Lyramion (100|350)" />
				<option key="116" val="Lyramion (150|350)" />
				<option key="117" val="Lyramion (200|350)" />
				<option key="118" val="Lyramion (250|350)" />
				<option key="119" val="Lyramion (300|350)" />
				<option key="120" val="Lyramion (350|350)" />
				<option key="121" val="Lyramion (400|350)" />
				<option key="122" val="Lyramion (450|350)" />
				<option key="123" val="Lyramion (500|350)" />
				<option key="124" val="Lyramion (550|350)" />
				<option key="125" val="Lyramion (600|350)" />
				<option key="126" val="Lyramion (650|350)" />
				<option key="127" val="Lyramion (700|350)" />
				<option key="128" val="Lyramion (750|350)" />
				<option key="129" val="Lyramion (000|400)" />
				<option key="130" val="Lyramion (050|400)" />
				<option key="131" val="Lyramion (100|400)" />
				<option key="132" val="Lyramion (150|400)" />
				<option key="133" val="Lyramion (200|400)" />
				<option key="134" val="Lyramion (250|400)" />
				<option key="135" val="Lyramion (300|400)" />
				<option key="136" val="Lyramion (350|400)" />
				<option key="137" val="Lyramion (400|400)" />
				<option key="138" val="Lyramion (450|400)" />
				<option key="139" val="Lyramion (500|400)" />
				<option key="140" val="Lyramion (550|400)" />
				<option key="141" val="Lyramion (600|400)" />
				<option key="142" val="Lyramion (650|400)" />
				<option key="143" val="Lyramion (700|400)" />
				<option key="144" val="Lyramion (750|400)" />
				<option key="145" val="Lyramion (000|450)" />
				<option key="146" val="Lyramion (050|450)" />
				<option key="147" val="Lyramion (100|450)" />
				<option key="148" val="Lyramion (150|450)" />
				<option key="149" val="Lyramion (200|450)" />
				<option key="150" val="Lyramion (250|450)" />
				<option key="151" val="Lyramion (300|450)" />
				<option key="152" val="Lyramion (350|450)" />
				<option key="153" val="Lyramion (400|450)" />
				<option key="154" val="Lyramion (450|450)" />
				<option key="155" val="Lyramion (500|450)" />
				<option key="156" val="Lyramion (550|450)" />
				<option key="157" val="Lyramion (600|450)" />
				<option key="158" val="Lyramion (650|450)" />
				<option key="159" val="Lyramion (700|450)" />
				<option key="160" val="Lyramion (750|450)" />
				<option key="161" val="Lyramion (000|500)" />
				<option key="162" val="Lyramion (050|500)" />
				<option key="163" val="Lyramion (100|500)" />
				<option key="164" val="Lyramion (150|500)" />
				<option key="165" val="Lyramion (200|500)" />
				<option key="166" val="Lyramion (250|500)" />
				<option key="167" val="Lyramion (300|500)" />
				<option key="168" val="Lyramion (350|500)" />
				<option key="169" val="Lyramion (400|500)" />
				<option key="170" val="Lyramion (450|500)" />
				<option key="171" val="Lyramion (500|500)" />
				<option key="172" val="Lyramion (550|500)" />
				<option key="173" val="Lyramion (600|500)" />
				<option key="174" val="Lyramion (650|500)" />
				<option key="175" val="Lyramion (700|500)" />
				<option key="176" val="Lyramion (750|500)" />
				<option key="177" val="Lyramion (000|550)" />
				<option key="178" val="Lyramion (050|550)" />
				<option key="179" val="Lyramion (100|550)" />
				<option key="180" val="Lyramion (150|550)" />
				<option key="181" val="Lyramion (200|550)" />
				<option key="182" val="Lyramion (250|550)" />
				<option key="183" val="Lyramion (300|550)" />
				<option key="184" val="Lyramion (350|550)" />
				<option key="185" val="Lyramion (400|550)" />
				<option key="186" val="Lyramion (450|550)" />
				<option key="187" val="Lyramion (500|550)" />
				<option key="188" val="Lyramion (550|550)" />
				<option key="189" val="Lyramion (600|550)" />
				<option key="190" val="Lyramion (650|550)" />
				<option key="191" val="Lyramion (700|550)" />
				<option key="192" val="Lyramion (750|550)" />
				<option key="193" val="Lyramion (000|600)" />
				<option key="194" val="Lyramion (050|600)" />
				<option key="195" val="Lyramion (100|600)" />
				<option key="196" val="Lyramion (150|600)" />
				<option key="197" val="Lyramion (200|600)" />
				<option key="198" val="Lyramion (250|600)" />
				<option key="199" val="Lyramion (300|600)" />
				<option key="200" val="Lyramion (350|600)" />
				<option key="201" val="Lyramion (400|600)" />
				<option key="202" val="Lyramion (450|600)" />
				<option key="203" val="Lyramion (500|600)" />
				<option key="204" val="Lyramion (550|600)" />
				<option key="205" val="Lyramion (600|600)" />
				<option key="206" val="Lyramion (650|600)" />
				<option key="207" val="Lyramion (700|600)" />
				<option key="208" val="Lyramion (750|600)" />
				<option key="209" val="Lyramion (000|650)" />
				<option key="210" val="Lyramion (050|650)" />
				<option key="211" val="Lyramion (100|650)" />
				<option key="212" val="Lyramion (150|650)" />
				<option key="213" val="Lyramion (200|650)" />
				<option key="214" val="Lyramion (250|650)" />
				<option key="215" val="Lyramion (300|650)" />
				<option key="216" val="Lyramion (350|650)" />
				<option key="217" val="Lyramion (400|650)" />
				<option key="218" val="Lyramion (450|650)" />
				<option key="219" val="Lyramion (500|650)" />
				<option key="220" val="Lyramion (550|650)" />
				<option key="221" val="Lyramion (600|650)" />
				<option key="222" val="Lyramion (650|650)" />
				<option key="223" val="Lyramion (700|650)" />
				<option key="224" val="Lyramion (750|650)" />
				<option key="225" val="Lyramion (000|700)" />
				<option key="226" val="Lyramion (050|700)" />
				<option key="227" val="Lyramion (100|700)" />
				<option key="228" val="Lyramion (150|700)" />
				<option key="229" val="Lyramion (200|700)" />
				<option key="230" val="Lyramion (250|700)" />
				<option key="231" val="Lyramion (300|700)" />
				<option key="232" val="Lyramion (350|700)" />
				<option key="233" val="Lyramion (400|700)" />
				<option key="234" val="Lyramion (450|700)" />
				<option key="235" val="Lyramion (500|700)" />
				<option key="236" val="Lyramion (550|700)" />
				<option key="237" val="Lyramion (600|700)" />
				<option key="238" val="Lyramion (650|700)" />
				<option key="239" val="Lyramion (700|700)" />
				<option key="240" val="Lyramion (750|700)" />
				<option key="241" val="Lyramion (000|750)" />
				<option key="242" val="Lyramion (050|750)" />
				<option key="243" val="Lyramion (100|750)" />
				<option key="244" val="Lyramion (150|750)" />
				<option key="245" val="Lyramion (200|750)" />
				<option key="246" val="Lyramion (250|750)" />
				<option key="247" val="Lyramion (300|750)" />
				<option key="248" val="Lyramion (350|750)" />
				<option key="249" val="Lyramion (400|750)" />
				<option key="250" val="Lyramion (450|750)" />
				<option key="251" val="Lyramion (500|750)" />
				<option key="252" val="Lyramion (550|750)" />
				<option key="253" val="Lyramion (600|750)" />
				<option key="254" val="Lyramion (650|750)" />
				<option key="255" val="Lyramion (700|750)" />
				<option key="256" val="Lyramion (750|750)" />
				<option key="257" val=" THALION BÜRO  " />
				<option key="258" val=" GROSSVATERS HAUS  " />
				<option key="259" val=" GROSSVATERS KELLER  " />
				<option key="260" val=" ALTE HÖHLE - 1.EBENE  " />
				<option key="261" val=" ALTE HÖHLE - 2.EBENE  " />
				<option key="262" val=" GEHEIMZIMMER  " />
				<option key="263" val=" DIE STADT SPANNENBERG  " />
				<option key="264" val=" ZUM HINKENDEN GAUNER  " />
				<option key="265" val=" GAUNERS WEINKELLER  " />
				<option key="266" val=" HAUS DER HEILER  " />
				<option key="267" val=" ZELLENTRAKT  " />
				<option key="268" val=" HAUS DER TRAINER  " />
				<option key="269" val=" HAUS DES FREIHERRN  " />
				<option key="270" val=" TOLIMARS PFERDESTALL  " />
				<option key="271" val=" KAPITÄN TORLES WERFT  " />
				<option key="272" val=" HAUS DES FISCHERS  " />
				<option key="273" val=" STADTHAUS  " />
				<option key="274" val=" HAUS DER BANDITEN  " />
				<option key="275" val=" HAUS - OBERGESCHOSS  " />
				<option key="276" val=" HAUS - KELLER  " />
				<option key="277" val=" TEMPEL DER GALA  " />
				<option key="278" val=" HAUS DES WEISEN  " />
				<option key="279" val=" SYLPHEN-HÖHLE  " />
				<option key="280" val=" ORKHÖHLE  " />
				<option key="281" val=" ALCHEMISTENTURM - 1  " />
				<option key="282" val=" ALCHEMISTENTURM - 2  " />
				<option key="283" val=" ALTE KRYPTA  " />
				<option key="284" val=" BURNVILLE-TUNNEL  " />
				<option key="285" val=" STADT BURNVILLE  " />
				<option key="286" val=" HEILERHAUS BURNVILLE  " />
				<option key="287" val=" HAUS DER KÜNSTE  " />
				<option key="288" val=" TAVERNE ZUM KAKTUS  " />
				<option key="289" val=" GÄSTEZIMMER  " />
				<option key="290" val=" NALVENS MAGIERSCHULE  " />
				<option key="291" val=" BOLLGAR-TUNNEL  " />
				<option key="292" val=" TORHAUS  " />
				<option key="293" val=" LUMINORS FOLTERKAMMER  " />
				<option key="294" val=" LUMINORS TURM - 1  " />
				<option key="295" val=" LUMINORS TURM - 2  " />
				<option key="296" val=" LUMINORS TURM - 3  " />
				<option key="297" val=" LUMINORS TURM - 4  " />
				<option key="298" val=" LUMINORS TURM - 5  " />
				<option key="299" val=" HÜGELHÖHLEN  " />
				<option key="400" val=" LEBABS TURM - 1  " />
				<option key="401" val=" LEBABS TURM - 2  " />
				<option key="402" val=" LEBABS TURM - 3  " />
				<option key="403" val=" LEBABS TURM - 4  " />
				<option key="404" val=" LEBABS TURM - 5  " />
				<option key="405" val=" TURMSPITZE  " />
				<option key="406" val=" GEMSTONE RUINEN  " />
				<option key="407" val=" WAFFENKAMMER  " />
				<option key="408" val=" WINDSCHREIN  " />
				<option key="409" val=" GNOMMINE  " />
				<option key="410" val=" TAVERNE ZUM SCHACHT  " />
				<option key="411" val=" HEXENHAUS  " />
				<option key="412" val=" HEXENHAUS KELLER  " />
				<option key="413" val=" DÖNNERS ALTE MINE  " />
				<option key="414" val=" HÜTTE DES WALDHÜTERS  " />
				<option key="415" val=" HÖHLE DER BESTIE  " />
				<option key="416" val=" ILLIEN - ELFENSTADT  " />
				<option key="417" val=" PELANIS PALAST  " />
				<option key="418" val=" HAUS DES HARFNERS  " />
				<option key="419" val=" ZUM ADLERHORST  " />
				<option key="420" val=" DAS DORF SNAKESIGN  " />
				<option key="421" val=" TAVERNE ZUR GÖTTIN  " />
				<option key="422" val=" SANSRIETEMPEL - 1  " />
				<option key="423" val=" SANSRIETEMPEL - 2  " />
				<option key="424" val=" SANSRIES TUNNEL  " />
				<option key="425" val=" DIE STADT NEWLAKE  " />
				<option key="426" val=" TAVERNE ZUM KRATER  " />
				<option key="427" val=" PALAST DES BARONS  " />
				<option key="428" val=" GRUFT VON NEWLAKE  " />
				<option key="429" val=" BIBLIOTHEK - NEWLAKE  " />
				<option key="430" val=" DIE FESTE GODSBANE  " />
				<option key="431" val=" TEMPEL DER BRUDERSCHAFT  " />
				<option key="432" val=" OBERPRIESTER GEMÄCHER  " />
				<option key="433" val=" TARBOS SARGHALLE  " />
				<option key="434" val=" HANGAR AUF LYRAMION  " />
				<option key="435" val=" F-SCHIFF: LYRAMION  " />
				<option key="436" val=" F-SCHIFF: WALDMOND  " />
				<option key="437" val=" F-SCHIFF: MORAG  " />
				<option key="438" val=" ALTE ZWERGENMINE  " />
				<option key="439" val=" HANGAR AUF MORAG  " />
				<option key="440" val=" GEFÄNGNIS - S'ANGRILA  " />
				<option key="441" val=" DIE STADT S'ANGRILA  " />
				<option key="442" val=" ZUM FLIEGENDEN MORANER  " />
				<option key="443" val=" REITECHSENHÄNDLERIN  " />
				<option key="444" val=" PALAST DES S'RIEL  " />
				<option key="445" val=" PALAST DES S'OREL  " />
				<option key="446" val=" PALAST DES S'ARIN  " />
				<option key="447" val=" PALAST DES S'ENDAR  " />
				<option key="448" val=" PALAST DES S'TROG  " />
				<option key="449" val=" PALAST DES S'KAT  " />
				<option key="450" val=" PALAST DES S'LORWIN  " />
				<option key="451" val=" SANDBOOTBAUER  " />
				<option key="452" val=" MORAG-TURM  " />
				<option key="453" val=" MASCHINENRAUM  " />
				<option key="454" val=" HANGAR AUF MORAG  " />
				<option key="455" val=" GEFÄNGNIS - S'ANGRILA  " />
				<option key="300" val=" KIRES MOND  " />
				<option key="301" val=" KIRES MOND  " />
				<option key="302" val=" KIRES MOND  " />
				<option key="303" val=" KIRES MOND  " />
				<option key="304" val=" KIRES MOND  " />
				<option key="305" val=" KIRES MOND  " />
				<option key="306" val=" KIRES MOND  " />
				<option key="307" val=" KIRES MOND  " />
				<option key="308" val=" KIRES MOND  " />
				<option key="309" val=" KIRES MOND  " />
				<option key="310" val=" KIRES MOND  " />
				<option key="311" val=" KIRES MOND  " />
				<option key="312" val=" KIRES MOND  " />
				<option key="313" val=" KIRES MOND  " />
				<option key="314" val=" KIRES MOND  " />
				<option key="315" val=" KIRES MOND  " />
				<option key="316" val=" KIRES MOND  " />
				<option key="317" val=" KIRES MOND  " />
				<option key="318" val=" KIRES MOND  " />
				<option key="319" val=" KIRES MOND  " />
				<option key="320" val=" KIRES MOND  " />
				<option key="321" val=" KIRES MOND  " />
				<option key="322" val=" KIRES MOND  " />
				<option key="323" val=" KIRES MOND  " />
				<option key="324" val=" KIRES MOND  " />
				<option key="325" val=" KIRES MOND  " />
				<option key="326" val=" KIRES MOND  " />
				<option key="327" val=" KIRES MOND  " />
				<option key="328" val=" KIRES MOND  " />
				<option key="329" val=" KIRES MOND  " />
				<option key="330" val=" KIRES MOND  " />
				<option key="331" val=" KIRES MOND  " />
				<option key="332" val=" KIRES MOND  " />
				<option key="333" val=" KIRES MOND  " />
				<option key="334" val=" KIRES MOND  " />
				<option key="335" val=" KIRES MOND  " />
				<option key="336" val=" GASTHAUS GEMSTONE  " />
				<option key="337" val=" KIRES RESIDENZ  " />
				<option key="338" val=" EINGANGSBEREICH RUINEN  " />
				<option key="339" val=" DOR GRESTIN  " />
				<option key="340" val=" FERRINS KELLER  " />
				<option key="341" val=" RATSHALLE  " />
				<option key="342" val=" RUINENUNTERGRUND  " />
				<option key="343" val=" DOR KIREDON  " />
				<option key="344" val=" FERRINS SCHMIEDE  " />
				<option key="345" val=" MINE  " />
				<option key="346" val=" DORINAS HÖHLE  " />
				<option key="347" val=" DORINAS HÖHLE UNTEN  " />
				<option key="348" val=" MINE - 2. EBENE  " />
				<option key="349" val=" RUINENTURM  " />
				<option key="350" val=" EINGANG DER ALTEN RUINEN  " />
				<option key="351" val=" KELLERGEWÖLBE  " />
				<option key="352" val=" MINE - 2. EBENE  " />
				<option key="353" val=" KIRES RESIDENZ  " />
				<option key="354" val=" MINE - 3. EBENE  " />
				<option key="355" val=" FERRINS LAGERKELLER  " />
				<option key="356" val=" TORNAK-HÖHLE  " />
				<option key="357" val=" TORNAK-HÖHLE  " />
				<option key="358" val=" KLEINE HÖHLE  " />
				<option key="359" val=" FERRINS SCHMIEDE  " />
				<option key="360" val=" HAUS DES HEILERS ASRUB  " />
				<option key="361" val=" GADLON: KELLER 2  " />
				<option key="362" val=" GADLON: KELLER 1  " />
				<option key="363" val=" GADLON: ERDGESCHOSS  " />
				<option key="364" val=" GADLON: 1. STOCK  " />
				<option key="365" val=" GADLON: 2. STOCK  " />
				<option key="366" val=" ANTIKE ANLAGE 1  " />
				<option key="367" val=" ANTIKE ANLAGE 2  " />
				<option key="368" val=" ANTIKE ANLAGE 3  " />
				<option key="369" val=" ANTIKE ANLAGE 4  " />
				<option key="513" val=" Morag" />
			</dictionary>

			<dictionary dictionary-id="shops">
				<option key="3" val="Spannenberg, Warenhändler von Spannenberg" />
				<option key="2" val="Spannenberg, Bibliothek der Heiler" />
				<option key="4" val="Spannenberg, Bibliothek der Mystik" />
				<option key="1" val="Spannenberg, Laden der Diebesgilde" />
				<option key="5" val="Alchemistenturm, Bibliothek der Alchemie" />
				<option key="6" val="Alchemistenturm, Laden der Alchemie" />
				<option key="10" val="Burnville, Warenhändler von Burnville" />
				<option key="11" val="Burnville, Nalven's Bibliothek" />
				<option key="7" val="Burnville, Bibliothek der Heiler" />
				<option key="8" val="Burnville, Bibliothek des Milzor" />
				<option key="9" val="Burnville, Theresa's Schmuckladen" />
				<option key="12" val="Newlake, Warenhandel von Newlake" />
				<option key="13" val="Illien, Lendrai's Magische Waffen" />
				<option key="15" val="Illien, Aiolu's Tränke Aller Art" />
				<option key="14" val="Illien, Spruchrollen Bibliothek" />
				<option key="17" val="Gnommine, Brom's Warenhaus" />
				<option key="16" val="Snakesign, Warenhändler von Snakesign" />
				<option key="31" val="Dor Kiredon, Ferrin's Schmiede" />
				<option key="32" val="Dor Grestin, Randor's Lädchen" />
				<option key="18" val="S'Angrila, Warenhändler von S'Angrila" />
				<option key="33" val="-" />
			</dictionary>

			<dictionary dictionary-id="songs">
				<option key="0" val="-" />
				<option key="1" val="Who said &quot;Hi Ho&quot; ?" />
				<option key="2" val="Mellow camel funk" />
				<option key="3" val="Close to the hedge" />
				<option key="4" val="Voice of the bagpipe" />
				<option key="5" val="Downtown" />
				<option key="6" val="SHIP" />
				<option key="7" val="Whole lotta dove" />
				<option key="8" val="Horse is no disgrace" />
				<option key="9" val="Don't look Bach" />
				<option key="10" val="Rough waterfront tavern" />
				<option key="11" val="Sapphire fireballs of pure love" />
				<option key="12" val="The Aum remains the same" />
				<option key="13" val="CAPITAL" />
				<option key="14" val="Plodding along" />
				<option key="15" val="Compact disc" />
				<option key="16" val="Riverside travelling blues" />
				<option key="17" val="Nobody's vault but mine" />
				<option key="18" val="La Crypta Strangiato" />
				<option key="19" val="Misty dungeon hop" />
				<option key="20" val="Burn baby burn!" />
				<option key="21" val="Bar brawlin'" />
				<option key="22" val="Psychedelic dune groove" />
				<option key="23" val="Stairway to level 50" />
				<option key="24" val="That hunch is back" />
				<option key="25" val="Chicken soup" />
				<option key="26" val="Dragon chase in creepy dungeon" />
				<option key="27" val="His master's voice" />
				<option key="28" val="To the Unknown" />
				<option key="29" val="Oh no not another magical event" />
				<option key="30" val="The Uh-oh song" />
				<option key="31" val="Owner of a lonely sword" />
				<option key="32" val="GAME OVER" />
				<option key="255" val="[stop]" />
			</dictionary>

			<dictionary dictionary-id="places">
				<option key="0" val="-" />
				<option key="1" val="GÄSTEZIMMER VON SPANNENBERG" />
				<option key="2" val="SPANNENBERG DIEBES GILDE" />
				<option key="3" val="SPANNENBERG DIEBES GILDE" />
				<option key="4" val="SPANNENBERG DIEBES GILDE" />
				<option key="5" val="HEILER VON SPANNENBERG" />
				<option key="6" val="HEILER VON SPANNENBERG" />
				<option key="7" val="BIBLIOTHEK DER HEILER" />
				<option key="8" val="SCHLAFSAAL DER HEILER" />
				<option key="9" val="MEISTER KARL'S ATTACKE TRAIN." />
				<option key="10" val="MEISTER ROLF'S PARADE TRAIN." />
				<option key="11" val="TOLIMARS PFERDE" />
				<option key="12" val="WARENHÄNDLER VON SPANNENBERG" />
				<option key="13" val="YNNEP LEBENSMITTEL" />
				<option key="14" val="SAGE VON SPANNENBERG" />
				<option key="15" val="TORLES WERFT" />
				<option key="16" val="LADEN DER DIEBESGILDE" />
				<option key="17" val="NAGIER'S K-TREFFER TRAINING" />
				<option key="18" val="BIBLIOTHEK DER MYSTIK" />
				<option key="19" val="BIBLIOTHEK DER ALCHEMIE" />
				<option key="20" val="LADEN DER ALCHEMIE" />
				<option key="21" val="BIBLIOTHEK DER HEILER" />
				<option key="22" val="HEILER VON BURNVILLE" />
				<option key="23" val="BIBLIOTHEK DES MILZOR" />
				<option key="24" val="THERESA'S SCHMUCKLADEN" />
				<option key="25" val="REZEPTION DES KAKTUS" />
				<option key="26" val="SULP LEBENSMITTEL" />
				<option key="27" val="WARENHÄNDLER VON BURNVILLE" />
				<option key="28" val="NALVEN'S SPRUCH TRAINING" />
				<option key="29" val="NALVEN'S LESE TRAINING" />
				<option key="30" val="NALVEN'S BIBLIOTHEK" />
				<option key="31" val="SCHWIMMSCHULE VON BURNVILLE" />
				<option key="32" val="SCHMIEDE VON BURNVILLE" />
				<option key="33" val="LEBAB'S VERZAUBERUNGEN" />
				<option key="34" val="GASTHAUS 'GEMSTONE'" />
				<option key="35" val="FERRIN'S SCHMIEDE" />
				<option key="36" val="DER HEILER ASRUB" />
				<option key="37" val="DIE SCHÄTZE DES WALDES" />
				<option key="38" val="DAS NEST" />
				<option key="39" val="DRELB'S SUCH TRAINING" />
				<option key="40" val="BROM'S WARENHAUS" />
				<option key="41" val="REZEPTION - ZUM SCHACHT" />
				<option key="42" val="LENDRAIS MAGISCHE WAFFEN" />
				<option key="43" val="MANA - LEBENSMITTEL" />
				<option key="44" val="SPRUCHROLLEN BIBLIOTHEK" />
				<option key="45" val="AIOLU'S TRÄNKE ALLER ART" />
				<option key="46" val="HEILER VON ILLIEN" />
				<option key="47" val="REZEPTION DES ADLER'S" />
				<option key="48" val="SAGE VON SNAKESIGN" />
				<option key="49" val="SCHMIEDE VON SNAKESIGN" />
				<option key="50" val="RATIONEN BEI SCHLANGENFRASS" />
				<option key="51" val="WARENHÄNDLER VON SNAKESIGN" />
				<option key="52" val="HEILER DER SANSRIE" />
				<option key="53" val="REZEPTION DER GÖTTIN" />
				<option key="54" val="SCHMIEDE VON NEWLAKE" />
				<option key="55" val="HAUS DER HEILER" />
				<option key="56" val="SIERPINIM - LEBENSMITTEL" />
				<option key="57" val="WARENHANDEL VON NEWLAKE" />
				<option key="58" val="REZEPTION DES KRATER'S" />
				<option key="59" val="FERRIN'S SCHMIEDE:REPARATUREN" />
				<option key="60" val="FERRIN'S SCHMIEDE:REPARATUREN" />
				<option key="61" val="RANDOR'S LÄDCHEN" />
				<option key="62" val="LEBENSMITTEL IN S'ANGRILA" />
				<option key="63" val="WARENHÄNDLER VON S'ANGRILA" />
				<option key="64" val="SCHMIEDE VON S'ANGRILA" />
			</dictionary>

			<dictionary dictionary-id="gotos">
				<option key="1" val="GOTO 1" />
				<option key="2" val="GOTO 2" />
				<option key="3" val="GOTO 3" />
				<option key="4" val="GOTO 4" />
				<option key="5" val="GOTO 5" />
				<option key="6" val="GOTO 6" />
				<option key="7" val="GOTO 7" />
				<option key="8" val="GOTO 8" />
				<option key="9" val="GOTO 9" />
				<option key="10" val="GOTO 10" />
				<option key="11" val="GOTO 11" />
				<option key="12" val="GOTO 12" />
				<option key="13" val="GOTO 13" />
				<option key="14" val="GOTO 14" />
				<option key="15" val="GOTO 15" />
				<option key="16" val="GOTO 16" />
				<option key="17" val="GOTO 17" />
				<option key="18" val="GOTO 18" />
				<option key="19" val="GOTO 19" />
				<option key="20" val="GOTO 20" />
				<option key="21" val="GOTO 21" />
				<option key="22" val="GOTO 22" />
				<option key="23" val="GOTO 23" />
				<option key="24" val="GOTO 24" />
				<option key="25" val="GOTO 25" />
				<option key="26" val="GOTO 26" />
				<option key="27" val="GOTO 27" />
				<option key="28" val="GOTO 28" />
				<option key="29" val="GOTO 29" />
				<option key="30" val="GOTO 30" />
				<option key="31" val="GOTO 31" />
				<option key="32" val="GOTO 32" />
				<option key="65535" val="RETURN" />
			</dictionary>

			<dictionary dictionary-id="colors">
				<option key="0" val="00 transparent" />
				<option key="1" val="01 white" />
				<option key="2" val="02 light gray" />
				<option key="3" val="03 medium gray" />
				<option key="4" val="04 dark gray" />
				<option key="5" val="05 really dark gray" />
				<option key="6" val="06 dark brown" />
				<option key="7" val="07 dark yellow" />
				<option key="8" val="08 medium brown" />
				<option key="9" val="09 light brown" />
				<option key="10" val="10" />
				<option key="11" val="11" />
				<option key="12" val="12 blue" />
				<option key="13" val="13 light blue" />
				<option key="14" val="14 yellow" />
				<option key="15" val="15 orange" />
			</dictionary>

			<dictionary dictionary-id="tile-icon-data-0">
				<option key="0" />
				<option key="1" />
				<option key="2" />
				<option key="3" />
				<option key="4" />
				<option key="5" />
				<option key="6" />
				<option key="7" />
			</dictionary>

			<dictionary dictionary-id="tile-icon-data-1">
				<option key="0" />
				<option key="1" />
				<option key="2" />
				<option key="3" />
				<option key="4" />
				<option key="5" />
				<option key="6" />
				<option key="7" />
			</dictionary>

			<dictionary dictionary-id="tile-icon-mobility">
				<option key="0" val="foot" />
				<option key="1" val="horse" />
				<option key="2" val="raft" />
				<option key="3" val="ship" />
				<option key="4" val="broom" />
				<option key="5" val="eagle" />
				<option key="6" val="cape" />
				<option key="7" val="is-water" />
			</dictionary>

			<dictionary dictionary-id="tile-icon-data-3">
				<option key="0" />
				<option key="1" />
				<option key="2" />
				<option key="3" />
				<option key="4" />
				<option key="5" />
				<option key="6" />
				<option key="7" />
			</dictionary>

		</dictionary-list>
	</xsl:template>


</xsl:stylesheet>