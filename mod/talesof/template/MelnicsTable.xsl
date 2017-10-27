<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="*/*[@data-cms-name = 'melnics.test']">
		<html>
			<head>
				<title>The Melnics Alphabet</title>
			</head>
			<body>
				<h2 class="Melnics"><a href="/Tales/">◁</a> The Melnics Alphabet</h2>
				<!--
				<dl class="melnicsTable">
					<xsl:for-each select="character">
						<xsl:variable name="lowerChar" select="@name"/>
						<xsl:variable name="upperChar" select="translate($lowerChar,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>
						<dt class="Melnics"><xsl:value-of select="$lowerChar"/></dt>
						<dd><xsl:value-of select="$lowerChar"/></dd>
						<xsl:if test="$lowerChar != $upperChar">
							<dt class="Melnics"><xsl:value-of select="$upperChar"/></dt>
							<dd><xsl:value-of select="$upperChar"/></dd>
						</xsl:if>
					</xsl:for-each>
				</dl>
				-->
				<table class="melnicsTable" border="1">
					<tbody>
						<xsl:for-each select="character[position() &lt;= 13]">
							<xsl:variable name="lowerChar" select="@name"/>
							<xsl:variable name="upperChar" select="translate($lowerChar,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>
							<xsl:if test="$lowerChar != $upperChar">
								<tr>
									<td class="Melnics"><xsl:value-of select="$lowerChar"/></td>
									<td><xsl:value-of select="$lowerChar"/></td>
									<td><xsl:value-of select="$upperChar"/></td>
									<td class="Melnics"><xsl:value-of select="$upperChar"/></td>
								</tr>
							</xsl:if>
						</xsl:for-each>
					</tbody>
				</table>
				<table class="melnicsTable" border="1">
					<tbody>
						<xsl:for-each select="character[position() &gt; 13]">
							<xsl:variable name="lowerChar" select="@name"/>
							<xsl:variable name="upperChar" select="translate($lowerChar,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>
							<xsl:if test="$lowerChar != $upperChar">
								<tr>
									<td class="Melnics"><xsl:value-of select="$lowerChar"/></td>
									<td><xsl:value-of select="$lowerChar"/></td>
									<td><xsl:value-of select="$upperChar"/></td>
									<td class="Melnics"><xsl:value-of select="$upperChar"/></td>
								</tr>
							</xsl:if>
						</xsl:for-each>
					</tbody>
				</table>
				<table class="melnicsTable" border="1">
					<tbody>
						<xsl:for-each select="character">
							<xsl:variable name="lowerChar" select="@name"/>
							<xsl:variable name="upperChar" select="translate($lowerChar,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>
							<xsl:if test="$lowerChar = $upperChar">
								<tr>
									<td class="Melnics"><xsl:value-of select="$lowerChar"/></td>
									<td><xsl:value-of select="$lowerChar"/></td>
								</tr>
							</xsl:if>
						</xsl:for-each>
					</tbody>
				</table>
				<br/>
				<small>Credit to the font belongs to Yuki Ishimaru: <a rel="external" href="http://park10.wakwak.com/~yuki/font.html">http://park10.wakwak.com/~yuki/font.html</a></small>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
