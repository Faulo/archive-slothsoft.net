<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="/data">
		<article>
			<h2>Introduction</h2>
			<p>Ambermoon is German RRG which was developed by Thalion Software
				and released in 1993 for the Amiga.</p>
			<p>It was one of the first video games Daniel encountered as a child
				(and the first RPG for a long time), and the fond memories he still
				holds of it is what compelled him to create the savegame editor
				you'll find here.</p>
			<p>It's not finished yet (and might never be) and not very thoroughly
				tested, so don't expect much. :'></p>
		</article>
		<xsl:copy-of select="." />
	</xsl:template>
</xsl:stylesheet>
