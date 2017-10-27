<?xml version="1.0" encoding="UTF-8"?><xsl:stylesheet version="1.0"	xmlns="http://www.w3.org/1999/xhtml"	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">		<xsl:variable name="poll" select="//poll"/>	<xsl:variable name="user" select="//user"/>	<xsl:variable name="title" select="concat($user/@name, &quot;'s &quot;, $poll/@name, ' Poll')"/>	<xsl:variable name="rating-min" select="$poll/rating-min"/>	<xsl:variable name="rating-max" select="$poll/rating-max"/> 	<xsl:template match="/data">		<html>			<head><title><xsl:value-of select="$title"/></title>			<style type="text/css"><![CDATA[legend {	font-size: 1.4em;	font-weight: bold;}label {	display: block;}.answer::before,.answer::after,label {	font-family: monospace;	vertical-align: super;}table {	border-spacing: 1em 0.25em;}td {	padding: 0;}.question {	width: 20em;}.answer {	position: relative;	height: 2.5em;	vertical-align: top;	white-space: nowrap;}.answer::before {	content: "]]><xsl:value-of select="$rating-min/@label"/><![CDATA[";}.answer::after {	content: "]]><xsl:value-of select="$rating-max/@label"/><![CDATA[";}.answer span {	text-align: center;	position: absolute;	display: block;	text-align: center;	width: 100%;	top: 1.5em;	left: 0;	pointer-events: none;	font-family: monospace;}			]]></style>			</head>						<body>				<h1><xsl:value-of select="$title"/></h1>				<ul>					<li>Rate your preferences on a scale of <code><xsl:value-of select="$rating-min/@label"/></code> to <code><xsl:value-of select="$rating-max/@label"/></code></li>					<li>Use <code>???</code> if you dunno what something is</li>				</ul>				<form action="?poll={request/param[@name='poll']}&amp;user={request/param[@name='user']}" method="POST">					<xsl:for-each select="$poll">						<xsl:for-each select="section">							<fieldset>								<legend><xsl:value-of select="@name"/></legend>								<table>									<xsl:for-each select="question">										<xsl:variable name="answer">											<xsl:choose>												<xsl:when test="number($user/answer[@name=current()/@name])">													<xsl:value-of select="$user/answer[@name=current()/@name]"/>												</xsl:when>												<xsl:otherwise>													<xsl:value-of select="0" />												</xsl:otherwise>											</xsl:choose>										</xsl:variable>										<tr>											<td class="question">												<xsl:choose>													<xsl:when test="@href">														<a href="{@href}" target="_blank">															<xsl:value-of select="@name"/>														</a>													</xsl:when>													<xsl:otherwise>														<xsl:value-of select="@name"/>													</xsl:otherwise>												</xsl:choose>											</td>											<td class="answer">												<input name="answer[{@name}]" value="{$answer}" type="range" min="{$rating-min/@value}" max="{$rating-max/@value}" step="1" onmousemove="this.nextSibling.textContent = this.value" onchange="this.nextSibling.textContent = this.value"/>												<span><xsl:value-of select="$answer"/></span>											</td>											<td>												<label>													<input name="question[{@name}]" type="checkbox">														<xsl:if test="$user/answer[@name=current()/@name]/@question">															<xsl:attribute name="checked">checked</xsl:attribute>														</xsl:if>													</input>													???												</label>											</td>										</tr>									</xsl:for-each>								</table>							</fieldset>							<br/>						</xsl:for-each>						<button type="submit">Save!!</button>						<p>Thank you! \o/</p>					</xsl:for-each>				</form>			</body>		</html>	</xsl:template></xsl:stylesheet>