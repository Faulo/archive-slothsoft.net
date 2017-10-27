<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/2000/svg"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="*[*[@data-cms-name='log']]">
		<xsl:apply-templates select="*[@data-cms-name='log']">
			<xsl:with-param name="status" select="*[@data-cms-name='status']"/>
		</xsl:apply-templates>
	</xsl:template>
	
	<xsl:template match="*[@data-cms-name='log']/log">
		<xsl:param name="status" select="/.."/>
		<xsl:variable name="scaleX" select="1 div 60"/>
		<xsl:variable name="scaleY" select="20"/>
		<xsl:variable name="minY" select="0"/>
		<xsl:variable name="maxY" select="10"/>
		<xsl:variable name="minX" select="0"/>
		<xsl:variable name="maxX" select="number(online[last()]/@date-stamp) - number(online[1]/@date-stamp)"/>
		<xsl:variable name="width" select="round(($maxX - $minX) * $scaleX)"/>
		<xsl:variable name="height" select="round(($maxY - $minY) * $scaleY)"/>
		
		<xsl:variable name="past" select="number(online[1]/@date-stamp)"/>
		<xsl:variable name="time" select="number(online[last()]/@date-stamp)"/>
		<svg
			contentScriptType="application/javascript"
			contentStyleType="text/css"
			color-rendering="optimizeSpeed"
			shape-rendering="optimizeSpeed"
			text-rendering="optimizeSpeed"
			image-rendering="optimizeSpeed"
			width="{$width div 2}px" height="{$height}px"
			class="log-short"
			>
			<g class="online" transform="translate({2+$width}, -2) scale(-1, 1)">
				<path>
					<xsl:attribute name="d">
						<xsl:text>M</xsl:text>
						<xsl:value-of select="$minX"/>
						<xsl:text>,</xsl:text>
						<xsl:value-of select="$height"/>
						<xsl:for-each select="online">
							<xsl:text> L</xsl:text>
							<xsl:value-of select="round((@date-stamp - $past) * $scaleX)"/>
							<xsl:text>,</xsl:text>
							<xsl:value-of select="round($height - (@count * $scaleY))"/>
							<!--<circle r="1" cx="{($time - @date-stamp) * $scaleX}" cy="{$maxY - (@count * $scaleY)}"/>-->
						</xsl:for-each>
						<xsl:text> L</xsl:text>
						<xsl:value-of select="$width+8"/>
						<xsl:text>,</xsl:text>
						<xsl:value-of select="$height"/>
						<xsl:text> Z</xsl:text>
						
					</xsl:attribute>
				</path>
			</g>
			
			<g class="axes">
				<path d="M{$minX},{$height} h{$width}"/>
				<path d="M{$minX},{$height} v{-$height}"/>
			</g>
			<g class="clock" transform="translate({2+$width}, -2) scale(-1, 1)">
				<xsl:for-each select="clock">
					<rect transform="translate({round((@date-stamp - $past) * $scaleX)}, {round($height - 8)}) scale(-1, 1)"
							width="2" height="8"/>
					<text
						transform="translate({round((@date-stamp - $past) * $scaleX)}, {round($height - 8)}) rotate(30) scale(-1, 1) ">
						<xsl:value-of select="@date-time"/>
					</text>
				</xsl:for-each>
			</g>
			<xsl:apply-templates select="$status"/>
		</svg>
	</xsl:template>
	
	<xsl:template match="*[@data-cms-name='status']/status">
		<g class="status" transform="translate(24, 24)">
			<xsl:for-each select="system">
				<g transform="translate(0, 0)">
					<text y="0em" data-dict="">Last system message:</text>
					<text y="1em" x="1em">
						<tspan class="myTime">[<xsl:value-of select="@date-datetime"/>]</tspan>
						<xsl:text> </xsl:text>
						<xsl:value-of select="@message"/>
					</text>
				</g>
			</xsl:for-each>
			<g transform="translate(384, 0)">
				<text y="0em" data-dict="">Currently online:</text>
				<xsl:choose>
					<xsl:when test="player[@online]">
						<xsl:for-each select="player[@online]">
							<text y="{position()}em" x="1em">
								<tspan class="myTime">[<xsl:value-of select="@date-datetime"/>]</tspan>
								<xsl:text> </xsl:text>
								<xsl:value-of select="@name"/>
							</text>
						</xsl:for-each>
					</xsl:when>
					<xsl:otherwise>
						<text y="1em" x="1em">-</text>
					</xsl:otherwise>
				</xsl:choose>
			</g>
			<g transform="translate(768, 0)">
				<text y="0em" data-dict="">Recently online:</text>
				<xsl:choose>
					<xsl:when test="player[not(@online)]">
						<xsl:for-each select="player[not(@online)]">
							<text y="{position()}em" x="1em">
								<tspan class="myTime">[<xsl:value-of select="@date-datetime"/>]</tspan>
								<xsl:text> </xsl:text>
								<xsl:value-of select="@name"/>
							</text>
						</xsl:for-each>
					</xsl:when>
					<xsl:otherwise>
						<text y="1em" x="1em">-</text>
					</xsl:otherwise>
				</xsl:choose>
			</g>
		</g>
	</xsl:template>
</xsl:stylesheet>