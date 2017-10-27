<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="/getTemplate.php/minecraft/functions"/>
	
	<xsl:template match="/data">
		<article class="minecraft-workshop">
			<h2 data-dict="">workshop/head</h2>
			<p data-dict="">workshop/title</p>
			<xsl:apply-templates select="*[@data-cms-name='craft']/object/*" mode="workshop"/>
		</article>
	</xsl:template>
	<xsl:template match="array" mode="workshop">
		
		<div class="Craft number">
			<div class="Workbench">
				<xsl:for-each select="array">
					<div>
						<xsl:for-each select="integer">
							<xsl:call-template name="block-image">
								<xsl:with-param name="id" select="@val"/>
							</xsl:call-template>
						</xsl:for-each>
					</div>
				</xsl:for-each>
			</div>
			<div>→</div>
			<div>
				<xsl:value-of select="integer/@val"/>
				<xsl:call-template name="block-image">
					<xsl:with-param name="id" select="@key"/>
				</xsl:call-template>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>
