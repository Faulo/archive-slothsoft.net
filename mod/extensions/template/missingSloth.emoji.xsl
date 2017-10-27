<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml">

	<xsl:template match="/data2">
		<article>
			<h1>Twitter Emoji</h1>
			<style>
._ms-emoji {
	width: 370px;
	height: 300px;
	overflow-x: hidden;
	overflow-y: auto;
	border: 1px solid silver;
}
._ms-emoji img {
    display: block;
    height: 18px;
    text-align: center;
    width: 18px;
}
			</style>
			<xsl:apply-templates select="data[row]"/>
		</article>
	</xsl:template>
	
	<xsl:template match="data[row]">
		<html>
			<head>
				<style><![CDATA[
html {
	overflow-x: hidden;
	background: navy;
}
body {
	margin: 0;
}
table {
	margin: auto;
	border-spacing: 0;
}
td {
	padding: 0.125vw;
}
img {
	display: block;
	width: 6vw;
	height: 5vw;
	margin: auto;
	text-align: auto;
	background: white;
}
				]]></style>
			</head>
			<body>
				<table>
					<xsl:for-each select="row">
						<tr>
							<xsl:for-each select="cell">
								<td>
									<xsl:if test="string-length(@name)">
										<!-- aria-label="{@name} ({@val})" draggable="false" -->
										<!--src="https://abs.twimg.com/emoji/v1/72x72/{@key}.png" -->
										<img class="twitter-emoji" 
											src="{@uri}" 
											alt="{@val}"
											title="{@name} ({@val})"
											/>
									</xsl:if>
								</td>
							</xsl:for-each>
						</tr>
					</xsl:for-each>
				</table>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>