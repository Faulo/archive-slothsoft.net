<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:svg="http://www.w3.org/2000/svg"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
 
	<xsl:output method="xml" indent="no" encoding="UTF-8" media-type="application/xhtml+xml" version="1.0"/>
	<xsl:template match="/data">
		<xsl:variable name="pageTitles" select="request/*"/>
		<xsl:variable name="pageHeadings" select="*[@data-cms-name='content']//html:h2[@id]"/>
		<xsl:variable name="chatNode" select="*[@data-cms-name='chat']"/>
		<xsl:variable name="requestedPage" select="request/*[1]"/>
		<html>
			<head>
				<title>
					<!--<xsl:value-of select="page/@header"/>-->
					<xsl:for-each select="$pageTitles">
						<xsl:if test="position() &gt; 1">
							<xsl:text> - </xsl:text>
						</xsl:if>
						<p data-dict="."><xsl:value-of select="@title"/></p>
					</xsl:for-each>
					<xsl:text> | Daniel Schulz</xsl:text>
				</title>
				<link rel="icon" type="image/png" href="/getResource.php/daniel/favicon"/>
				<meta name="viewport" content="width=device-width, initial-scale=1"/>
				<meta name="author" content="Daniel Schulz"/>
				<!--
				<meta name="keywords">
					<xsl:attribute name="content">
						<xsl:for-each select="$pageTitles">
							<xsl:if test="position() &gt; 1">
								<xsl:text>,</xsl:text>
							</xsl:if>
							<p data-dict="."><xsl:value-of select="@title"/></p>
						</xsl:for-each>
					</xsl:attribute>
				</meta>
				-->
			</head>
			<body>
				<div class="page">
					<h1 data-dict="" class="hidden"><xsl:value-of select="$requestedPage/@title"/></h1>
					<nav>
						<h2 class="hidden">Navigation</h2>
						<!--
						<img src="/getFragment.php/slothsoft/writing?text=Daniel+Schulz" alt="Daniel Schulz"/>
						-->
						<!--
						<svg:svg version="1.2" baseProfile="tiny">
							<svg:text x="0.5em" y="0.5em">Daniel Schulz</svg:text>
						</svg:svg>
						-->
						<xsl:copy-of select="*[@data-cms-name='navi']/node()"/>
					</nav>
					<main>
						<!--
						<header>
							<h2 data-dict="" hidden="hidden">
								<xsl:value-of select="$pageTitles[1]/@name"/>
								<xsl:text>/headline</xsl:text>
							</h2>
						</header>
						-->
						<div data-dict="">
							<xsl:value-of select="$pageTitles[1]/@name"/>
							<xsl:text>/content</xsl:text>
						</div>
						<xsl:copy-of select="*[@data-cms-name='content']/node()"/>
					</main>
				</div>
			</body>
		</html>
		
	</xsl:template>
</xsl:stylesheet>