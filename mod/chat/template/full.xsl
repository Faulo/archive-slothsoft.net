<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl">
 
	<xsl:template match="/data">
		<div class="minecraft-chat">
			<ul id="ChatLogList">
				<xsl:for-each select="chat/msg">
					<li>
						<!--<xsl:attribute name="style">color: rgb(<xsl:value-of select="php:function('vprintf', '%1$d,%2$d,%3$d', php:function('explode', '.', string(@ip)))"/>);</xsl:attribute>
						-->
						<span><xsl:value-of select="php:function('date', 'd.m.y H:i:s', number(@time))"/></span>
						<span><xsl:value-of select="@txt"/></span>
					</li>
				</xsl:for-each>
			</ul>
		</div>
	</xsl:template>
</xsl:stylesheet>