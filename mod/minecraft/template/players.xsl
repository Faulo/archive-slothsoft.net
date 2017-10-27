<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="/getTemplate.php/minecraft/functions"/>
	
	<xsl:template match="/data">
		<xsl:variable name="hearts" select="*[@data-cms-name='heart.0'] | *[@data-cms-name='heart.1'] | *[@data-cms-name='heart.2']"/>
		<xsl:variable name="foods" select="*[@data-cms-name='food.0'] | *[@data-cms-name='food.1'] | *[@data-cms-name='food.2']"/>
		<article class="minecraft-players">
			<h2 data-dict="">players/title</h2>
			<xsl:for-each select="*[@data-cms-name='players']">
				<xsl:sort select="@change-stamp" order="descending"/>
				<xsl:call-template name="nbt-charsheet">
					<xsl:with-param name="player" select="compound"/>
					<xsl:with-param name="hearts" select="$hearts"/>
					<xsl:with-param name="foods" select="$foods"/>
				</xsl:call-template>
				<!--<xsl:copy-of select="compound"/>-->
			</xsl:for-each>
		</article>
	</xsl:template>
</xsl:stylesheet>
