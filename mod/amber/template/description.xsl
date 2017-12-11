<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="/data">
		<div data-dict="">description:<xsl:value-of select="request/page[1]/@name"/></div>
	</xsl:template>
</xsl:stylesheet>
