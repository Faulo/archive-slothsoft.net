<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" indent="yes" encoding="UTF-8" media-type="application/xhtml+xml" version="1.0"/>
	<xsl:template match="/data">
		<html>
			<head>
				<script type="application/javascript">
				<![CDATA[

function mozExtensionInstall(uri) {
	InstallTrigger.install(
		[ uri ]
	);
}
				]]>
				</script>
			</head>
			<body>
				<form action="./checkout.php" method="post" enctype="multipart/form-data">
					<xsl:apply-templates select="checkout"/>
				</form>
				<xsl:apply-templates select="MozExtensionManager"/>
			</body>
		</html>
	</xsl:template>
	<xsl:template match="checkout[@step='upload']">
		<fieldset>
			<legend>Neue Version hochladen</legend>
			<label>Bitte <code>extension.xpi</code> und <code>install.rdf</code> auswählen</label>
			<input type="file" multiple="multiple" name="upload-package[]"/>
			<input type="hidden" name="upload-package"/>
			<button type="submit">Upload</button>
		</fieldset>
		<fieldset>
			<legend>Signierung hochladen</legend>
			<label>Bitte signierte <code>update.rdf</code> auswählen</label>
			<input type="file" name="upload-update[]"/>
			<input type="hidden" name="upload-update"/>
			<button type="submit">Upload</button>
		</fieldset>
	</xsl:template>
	
	<xsl:template match="MozExtensionManager">
		<ul>
			<xsl:for-each select="MozExtensionPackage">
				<li>
					<xsl:apply-templates select="."/>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>
	
	<xsl:template match="MozExtensionPackage">
		<h2><button onclick="InstallTrigger.install([ 'download.php?item_id={@id}' ]);"><xsl:value-of select="@id"/></button></h2>
		<a href="{@uri-update}">update.rdf</a>
		<br/>
		<a href="{@uri-unsigned}">unsigned.rdf</a>
	</xsl:template>
</xsl:stylesheet>