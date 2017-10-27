<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="/getTemplate.php/minecraft/functions"/>
	
	<xsl:template match="/data">
		<article class="minecraft-mall">
			<h2 data-dict="">mall/head</h2>
			<p data-dict="">mall/title</p>
			<xsl:apply-templates select="*[@data-cms-name='trade']/object" mode="mall"/>
		</article>
	</xsl:template>
	
	<xsl:template match="object" mode="mall">
		<table>
			<thead>
				<tr data-dict="*/text()">
					<th colspan="3">mall/buy</th>
					<th/>
					<th colspan="3">mall/sell</th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="*">
					<xsl:sort select="@val" data-type="number"/>
					<xsl:apply-templates select="." mode="mall"/>
				</xsl:for-each>
			</tbody>
		</table>
	</xsl:template>
	<xsl:template match="*" mode="mall">
		<tr class="number">
			<td>
				<xsl:text>1</xsl:text> 
				<xsl:call-template name="block-image">
					<xsl:with-param name="id" select="266"/>
				</xsl:call-template>
			</td>
			<td><xsl:text>=</xsl:text></td>
			<td>
				<xsl:value-of select="@val"/>
				<xsl:call-template name="block-image">
					<xsl:with-param name="id" select="@key"/>
				</xsl:call-template>
			</td>
			<th><xsl:text>&lt;---&gt;</xsl:text></th>
			<td>
				<xsl:value-of select="1.5 * number(@val)"/>
				<xsl:call-template name="block-image">
					<xsl:with-param name="id" select="@key"/>
				</xsl:call-template>
			</td>
			<td><xsl:text>=</xsl:text></td>
			<td>
				<xsl:text>1</xsl:text> 
				<xsl:call-template name="block-image">
					<xsl:with-param name="id" select="266"/>
				</xsl:call-template>
			</td>
			
			
		</tr>
	</xsl:template>
</xsl:stylesheet>
