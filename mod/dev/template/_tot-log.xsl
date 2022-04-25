<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:std="http://schema.slothsoft.net/trialoftwo/database"
	xmlns:date="http://exslt.org/dates-and-times"
    extension-element-prefixes="date">
	
	<xsl:template match="std:database">
		<html>
			<head>
				<title>Trial of Two Game Log</title>
				<style type="text/css"><![CDATA[
.time, .incidentType, .id {
	font-family: monospace;
}
.id {
	font-weight: bold;
}
.moveIncident {
	background-color: #dfd;
}
[data-incident="moveIncident"]::before {
	content: "👊";
}
.hurtIncident {
	background-color: #ffd;
}
[data-incident="hurtIncident"]::before {
	content: "🩸";
}
.spawnIncident {
	background-color: #ddf;
}
[data-incident="spawnIncident"]::before {
	content: "👶";
}
.deathIncident {
	background-color: #fdd;
}
[data-incident="deathIncident"]::before {
	content: "💀";
}
				]]></style>
			</head>
			<body>
				<h1>Trial of Two Game Log</h1>
				<ul>
					<li>Game version: <xsl:value-of select="@gameVersion"/></li>
					<li>Schema version: <xsl:value-of select="@schemaVersion"/></li>
				</ul>
				<xsl:apply-templates select="std:sessions"/>
			</body>
		</html>
	</xsl:template>
	
	<xsl:template match="std:sessions">
		<xsl:for-each select="std:session">
			<hr/>
			<details>
				<summary>Session #<xsl:value-of select="position()"/>: <xsl:value-of select="@scene"/></summary>
				<xsl:apply-templates select="."/>
			</details>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template match="std:session">
		<h3>Player Stats</h3>
		<ul>
			<li>Level: <xsl:value-of select="@scene"/></li>
			<li>Session start: <xsl:value-of select="@start"/></li>
			<li>Session end: <xsl:value-of select="@end"/></li>
			<li>Damage done: <xsl:value-of select="sum(.//std:hurtIncident[contains(std:entity/@name, 'Enemy')]/@damageAmount)"/></li>
			<li>Enemies met: <xsl:value-of select="count(.//std:spawnIncident[contains(std:entity/@name, 'Enemy')])"/></li>
			<li>Enemies vanquished: <xsl:value-of select="count(.//std:deathIncident[contains(std:entity/@name, 'Enemy')])"/></li>
			<li>Damage taken (P1): <xsl:value-of select="sum(.//std:hurtIncident[contains(std:entity/@name, 'Player_1')]/@damageAmount)"/></li>
			<li>Number of deaths (P1): <xsl:value-of select="count(.//std:deathIncident[contains(std:entity/@name, 'Player_1')])"/></li>
			<li>Damage taken (P2): <xsl:value-of select="sum(.//std:hurtIncident[contains(std:entity/@name, 'Player_2')]/@damageAmount)"/></li>
			<li>Number of deaths (P2): <xsl:value-of select="count(.//std:deathIncident[contains(std:entity/@name, 'Player_2')])"/></li>
		</ul>
		<xsl:apply-templates select="std:rooms"/>
		<xsl:apply-templates select="std:incidents"/>
	</xsl:template>
	
	<xsl:template match="std:rooms">
		<h3>Room log</h3>
		<ul>
			<xsl:for-each select="std:room[@roomEntered]">
				<xsl:sort select="@roomEntered"/>
				<xsl:sort select="@battleStarted"/>
				<xsl:sort select="@roomCleared"/>
				<li>
					<u>Room #<xsl:value-of select="position()"/>: <xsl:value-of select="@name"/></u>
					<xsl:apply-templates select="."/>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>
	
	<xsl:template match="std:room">
		<dl>
			<dt>Room entered:</dt><dd><xsl:value-of select="@roomEntered"/></dd>
			<dt>Battle started:</dt><dd><xsl:value-of select="@battleStarted"/></dd>
			<dt>Room cleared:</dt><dd><xsl:value-of select="@roomCleared"/></dd>
		</dl>
	</xsl:template>
	
	<xsl:template match="std:incidents">
		<h3>Incidents</h3>
		<table border="1">
			<thead>
				<tr>
					<th>Type</th>
					<th>Time</th>
					<th>Entity ID</th>
					<th>Entity Name</th>
					<th>Incident</th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="*">
					<xsl:sort select="@time"/>
					<tr class="{local-name()}">
						<xsl:apply-templates select="."/>
					</tr>
				</xsl:for-each>
			</tbody>
		</table>
	</xsl:template>
	
	<xsl:template match="std:moveIncident | std:hurtIncident | std:spawnIncident | std:deathIncident">
		<td title="{local-name()}"><span data-incident="{local-name()}"/></td>
		<td class="time">[<xsl:value-of select="@time"/>]</td>
		<td class="id"><xsl:value-of select="std:entity/@id"/></td>
		<td class="entityName"><xsl:value-of select="std:entity/@name"/></td>
		<td class="incidentName"><xsl:value-of select="@move"/></td>
	</xsl:template>
</xsl:stylesheet>
				