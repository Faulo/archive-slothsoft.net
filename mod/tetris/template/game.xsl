<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	
	<xsl:template match="/game">
		<div class="Game" tabindex="0">
			<dl>
				<dt>Controls:</dt>
				<dd>W A S D Space</dd>
			</dl>
			<div class="Map"
				>
				<div class="Board Blocks"/>
				<div class="Storage Blocks" style="width: 176px; height: 320px;">
					
				</div>
			</div>
			<div class="GUI">
				<input type="text" min="0" step="1" id="Control.FPS.calc" value="0" disabled="disabled"/>/<input  disabled="disabled" type="text" min="0" step="1" id="Control.FPS.aim" value="60"/> fps

			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>