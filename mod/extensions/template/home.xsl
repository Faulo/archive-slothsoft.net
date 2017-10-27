<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" encoding="UTF-8" media-type="application/xhtml+xml" version="1.0"/>
	<xsl:template match="data">
		<xsl:variable name="extension" select=".//resourceDir[@data-cms-name='extension'][last()]"/>
		<xsl:variable name="signed" select=".//resourceDir[@data-cms-name='extension'][last()]"/>
		<xsl:variable name="id" select="$extension/@name"/>
		<article data-template="extensions-home">
			<h2 data-dict=""><xsl:value-of select="concat('lang/', $id, '/title')"/></h2>
			<button data-dict="" onclick="InstallTrigger.install([ '{$signed/@uri}?install' ]);"><xsl:value-of select="concat('lang/', $id, '/download')"/></button>
			<div><p>The official changelog can be found under my bsns-ish Twitter handle, <a href="https://twitter.com/Faulosoft">@Faulosoft</a></p></div>
			<div data-dict=""><xsl:value-of select="concat('lang/', $id, '/description')"/></div>
		</article>
	</xsl:template>
</xsl:stylesheet>