<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
 
	<xsl:template match="*[@data-cms-name='archive']">
		<div class="minecraft-chat">
			<ul class="messageLog">
				<xsl:apply-templates select="range"/>
			</ul>
		</div>
	</xsl:template>
	
	<xsl:template match="range">
		<xsl:apply-templates select="p"/>
	</xsl:template>
	
	<xsl:template match="p">
		<li style="color:rgb({@color});">
			<time datetime="{@time-utc}"><xsl:value-of select="@time-string"/></time>
			<strong><xsl:copy-of select="node()"/></strong>
		</li>
	</xsl:template>
</xsl:stylesheet>