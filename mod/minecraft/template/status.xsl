<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="*[@data-cms-name='status']/status">
		<xsl:variable name="playerList" select="player[@online]"/>
		<xsl:variable name="system" select="system"/>
		<xsl:variable name="online">
			<xsl:choose>
				<xsl:when test="$system/@online">
					<xsl:text>online</xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>offline</xsl:text>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<!--<fieldset class="status">
			<legend>Minecraft Server Status</legend>
			-->
		<aside class="status">
			<script type="application/javascript"><![CDATA[
var MinecraftStatus = {
	logNode : undefined,
	logTimeout : undefined,
	init : function() {
		this.logNode = document.getElementsByClassName("status")[0].lastChild;
		this.requestLater();
	},
	requestLater : function() {
		this.logTimeout = setTimeout(
			function(home) {
				home.requestNow();
			},
			10000,
			this
		);
	},
	requestNow : function() {
		var req, uri;
		uri = "/getFragment.php/minecraft/status";
		req = new XMLHttpRequest();
		req.open("GET", uri, true);
		req.addEventListener(
			"loadend",
			function(eve) {
				if (this.responseXML) {
					this.home.setDocument(this.responseXML);
					this.home.requestLater();
				}
			},
			false
		);
		req.home = this;
		req.send();
	},
	setDocument : function(doc) {
		var newNode = XPath.evaluate("/fragment/*/*[last()]", doc)[0];
		newNode = this.logNode.ownerDocument.importNode(newNode, true);
		this.logNode.parentNode.replaceChild(newNode, this.logNode);
		this.logNode = newNode;
	},
};
			
addEventListener(
	"load",
	function(eve) {
		MinecraftStatus.init();
	},
	false
);
			]]></script>
			<xsl:for-each select="system">
				<dl data-dict="html:dt/node()">
					<dt>Minecraft Server is:</dt>
					<dd data-minecraft-status="{$online}">
						<xsl:value-of select="$online"/>
					</dd>
					<dt>Version:</dt>
					<dd>
						<!--
						<a href="http://assets.minecraft.net/{translate(@version, '.', '_')}/minecraft.jar" title="Download from Minecraft.net">
							<xsl:value-of select="@version"/>
						</a>
						-->
						<a href="http://s3.amazonaws.com/Minecraft.Download/launcher/Minecraft.exe" title="Download from Minecraft.net" rel="external">
							<xsl:value-of select="@version"/>
						</a>
					</dd>
					<dt>Players online:</dt>
					<dd>
						<xsl:choose>
							<xsl:when test="$playerList">
								<xsl:for-each select="$playerList">
									<xsl:if test="position() &gt; 1">
										<xsl:text>, </xsl:text>
									</xsl:if>
									<span>
										<xsl:value-of select="@name"/>
									</span>
								</xsl:for-each>
							</xsl:when>
							<xsl:otherwise>
								<span>-</span>
							</xsl:otherwise>
						</xsl:choose>
					</dd>
				</dl>
			</xsl:for-each>
			<!--
			<p>
				<span y="0em" data-dict="">Currently online:</span>
				<xsl:choose>
					<xsl:when test="player">
						<xsl:for-each select="player">
							<span y="1em" x="1em">
								<tspan class="myTime">[<xsl:value-of select="@date-datetime"/>]</tspan>
								<xsl:text> </xsl:text>
								<xsl:value-of select="@name"/>
							</span>
						</xsl:for-each>
					</xsl:when>
					<xsl:otherwise>
						<span y="1em" x="1em">-</span>
					</xsl:otherwise>
				</xsl:choose>
			</p>
			-->
		</aside>
	</xsl:template>
</xsl:stylesheet>