<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
 
	<xsl:template match="/data">
		<article class="minecraft-log paintedBox">
			<script type="application/javascript"><![CDATA[
var MinecraftLog = {
	logNode : undefined,
	logTimeout : undefined,
	init : function() {
		this.logNode = document.getElementsByClassName("minecraft-log")[0].lastChild;
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
		uri = "/getFragment.php/minecraft/log-content";
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
		var newNode = XPath.evaluate("/fragment/*", doc)[0];
		newNode = this.logNode.ownerDocument.importNode(newNode, true);
		this.logNode.parentNode.replaceChild(newNode, this.logNode);
		this.logNode = newNode;
	},
};
			
addEventListener(
	"load",
	function(eve) {
		MinecraftLog.init();
	},
	false
);
		]]></script>
			<h2>Online Players Log</h2>
			<xsl:copy-of select="*[@data-cms-name='log-content']/*"/>
		</article>
	</xsl:template>
</xsl:stylesheet>