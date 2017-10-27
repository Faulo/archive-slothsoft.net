<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	
	<xsl:template match="/data">
		<html>
			<head>
				<title>Persistent Tree</title>
				<style type="text/css"><![CDATA[
				]]></style>
			</head>
			<body>
				<xsl:apply-templates select="data/repository"/>
			</body>
		</html>
	</xsl:template>
	
	<xsl:template match="repository">
		
	</xsl:template>
</xsl:stylesheet>