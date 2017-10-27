<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="/data">
		<div class="minecraft-law" data-dict="*/*/text()">
			<section>
				<h2>law/forbidden/title</h2>
				<div style="font-weight: bolder; color: darkred;">law/forbidden</div>
				<p>law/forbidden/note</p>
			</section>
			<hr/>
			<section>
				<h2>law/allowed/title</h2>
				<div style="font-weight: bolder; color: darkgreen;">law/allowed</div>
				<p>law/allowed/note</p>
			</section>
			<hr/>
			<section>
				<h2>law/restricted/title</h2>
				<div style="font-weight: bolder; color: navy;">law/restricted</div>
				<p>law/restricted/note</p>
			</section>
		</div>
	</xsl:template>
</xsl:stylesheet>
