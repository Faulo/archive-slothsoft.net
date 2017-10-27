<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	
	<xsl:template name="block-image">
		<xsl:param name="id"/>
		<!--<xsl:param name="blocks" select="/data/*[@data-cms-name='blocks']"/>-->
		<xsl:variable name="uri" select="concat('/getResource.php/minecraft/blocks/', $id)"/>
		<img src="{$uri}" alt="#{$id}" data-minecraft-block="{$id}"/>
	</xsl:template>
	<xsl:template name="image-bar">
		<xsl:param name="amount"/>
		<xsl:param name="max"/>
		<xsl:param name="images"/>
		<xsl:variable name="count" select="count($images) - 1"/>
		<!--<xsl:copy-of select="$images[1]"/>-->
		<xsl:choose>
			<xsl:when test="$amount &gt; $count">
				<img src="{$images[$count + 1]/@uri}" alt="{$count - 1}"/>
			</xsl:when>
			<xsl:when test="$amount &gt; 0">
				<img src="{$images[$amount + 1]/@uri}" alt="{$amount}"/>
			</xsl:when>
			<xsl:otherwise>
				<img src="{$images[1]/@uri}" alt="0"/>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:if test="$max &gt; $count">
			<xsl:call-template name="image-bar">
				<xsl:with-param name="amount" select="$amount - $count"/>
				<xsl:with-param name="max" select="$max - $count"/>
				<xsl:with-param name="images" select="$images"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	<!--compound-->
	<xsl:template name="nbt-charsheet">
		<xsl:param name="player"/>
		<xsl:param name="hearts"/>
		<xsl:param name="foods"/>
		<xsl:variable name="inv" select="$player/*[@key='Inventory']"/>
		<xsl:variable name="name" select="substring-before($player/@key, '.')"/>
		<xsl:variable name="lvl" select="number(concat('0', $player/*[@key='XpLevel']/@val))"/>
		<xsl:variable name="health" select="number(concat('0', $player/*[@key='Health']/@val))"/>
		<xsl:variable name="food" select="number(concat('0', $player/*[@key='foodLevel']/@val))"/>
		<xsl:variable name="posX" select="round($player/*[@key='Pos']/*[1]/@val)"/>
		<xsl:variable name="posY" select="round($player/*[@key='Pos']/*[2]/@val)"/>
		<xsl:variable name="posZ" select="round($player/*[@key='Pos']/*[3]/@val)"/>

		<div data-minecraft-charsheet="root">
			<div>
				<ul data-minecraft-charsheet="equip">
					<li>
						<xsl:variable name="equip" select="$inv/*[*/@key='Slot'][*/@val='103']"/>
						<xsl:if test="$equip">
							<xsl:call-template name="nbt-item">
								<xsl:with-param name="item" select="$equip"/>
							</xsl:call-template>
						</xsl:if>
					</li>
					<li>
						<xsl:variable name="equip" select="$inv/*[*/@key='Slot'][*/@val='102']"/>
						<xsl:if test="$equip">
							<xsl:call-template name="nbt-item">
								<xsl:with-param name="item" select="$equip"/>
							</xsl:call-template>
						</xsl:if>
					</li>
					<li>
						<xsl:variable name="equip" select="$inv/*[*/@key='Slot'][*/@val='101']"/>
						<xsl:if test="$equip">
							<xsl:call-template name="nbt-item">
								<xsl:with-param name="item" select="$equip"/>
							</xsl:call-template>
						</xsl:if>
					</li>
					<li>
						<xsl:variable name="equip" select="$inv/*[*/@key='Slot'][*/@val='100']"/>
						<xsl:if test="$equip">
							<xsl:call-template name="nbt-item">
								<xsl:with-param name="item" select="$equip"/>
							</xsl:call-template>
						</xsl:if>
					</li>
				</ul>
				<div data-minecaft-charsheet="picture">
					<img src="/Minecraft/Tectonicus/Images/{$name}.png" alt="{$name}"/>
				</div>
				<div data-minecraft-charsheet="stats">
					<h3><xsl:value-of select="$name"/></h3>
					<ul>
						<li><p data-dict=".">players/level</p><xsl:value-of select="$lvl"/></li>
						<!--
						<li>
							<xsl:text>Position: </xsl:text>
						</li>
						<li>
							<xsl:value-of select="$posX"/>
							<xsl:text>, </xsl:text>
							<xsl:value-of select="$posY"/>
							<xsl:text>, </xsl:text>
							<xsl:value-of select="$posZ"/>
						</li>
						-->
						<li data-dict="">players/laston</li>
						<li>
							<time datetime="{$player/../@change-utc}"><xsl:value-of select="$player/../@change-datetime"/></time>
						</li>
						<li>
							<xsl:call-template name="image-bar">
								<xsl:with-param name="amount" select="$food"/>
								<xsl:with-param name="max" select="20"/>
								<xsl:with-param name="images" select="$foods"/>
							</xsl:call-template>
						</li>
						<li>
							<xsl:call-template name="image-bar">
								<xsl:with-param name="amount" select="$health"/>
								<xsl:with-param name="max" select="20"/>
								<xsl:with-param name="images" select="$hearts"/>
							</xsl:call-template>
						</li>
					</ul>
				</div>
			</div>
			<xsl:call-template name="nbt-inventory-table">
				<xsl:with-param name="inv" select="$inv"/>
			</xsl:call-template>
		</div>
	</xsl:template>
	<xsl:template name="nbt-inventory-table">
		<xsl:param name="inv"/>
		<xsl:for-each select="$inv">
			<table data-minecraft-charsheet="inv">
				<tfoot>
					<tr>
						<xsl:call-template name="nbt-inventory-row">
							<xsl:with-param name="current" select="0"/>
							<xsl:with-param name="count" select="9"/>
						</xsl:call-template>
					</tr>
				</tfoot>
				<tbody>
					<tr>
						<xsl:call-template name="nbt-inventory-row">
							<xsl:with-param name="current" select="9"/>
							<xsl:with-param name="count" select="9"/>
						</xsl:call-template>
					</tr>
					<tr>
						<xsl:call-template name="nbt-inventory-row">
							<xsl:with-param name="current" select="18"/>
							<xsl:with-param name="count" select="9"/>
						</xsl:call-template>
					</tr>
					<tr>
						<xsl:call-template name="nbt-inventory-row">
							<xsl:with-param name="current" select="27"/>
							<xsl:with-param name="count" select="9"/>
						</xsl:call-template>
					</tr>
				</tbody>
			</table>
		</xsl:for-each>
	</xsl:template>
	<xsl:template name="nbt-inventory-row">
		<xsl:param name="inv" select="."/>
		<xsl:param name="current"/>
		<xsl:param name="count"/>
		<xsl:variable name="item" select="$inv/*/*[@key='Slot' and number(@val) = number($current)]/.."/>
		<td>
			<div>
			<xsl:if test="$item">
				<xsl:call-template name="nbt-item">
					<xsl:with-param name="item" select="$item"/>
				</xsl:call-template>
			</xsl:if>
			</div>
		</td>
		<xsl:if test="$count &gt; 1">
			<xsl:call-template name="nbt-inventory-row">
				<xsl:with-param name="current" select="$current + 1"/>
				<xsl:with-param name="count" select="$count - 1"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	<xsl:template name="nbt-item">
		<xsl:param name="item"/>
		<xsl:variable name="id" select="number($item/*[@key='id']/@val)"/>
		<xsl:variable name="damage" select="number($item/*[@key='Damage']/@val)"/>
		<xsl:variable name="count" select="number($item/*[@key='Count']/@val)"/>
		<xsl:variable name="slot" select="number($item/*[@key='Slot']/@val)"/>
	
		<xsl:call-template name="block-image">
			<xsl:with-param name="id" select="$id"/>
		</xsl:call-template>
		<xsl:if test="$count &gt; 1">
			<span><xsl:value-of select="$count"/></span>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
