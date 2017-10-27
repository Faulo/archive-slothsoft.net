<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:em="http://www.mozilla.org/2004/em-rdf#">
	
	<!--
	<xsl:template match="/">
		<xsl:copy-of select="."/>
	</xsl:template>
	-->
	<xsl:template match="repository">
		<RDF:RDF>
			<RDF:Description about="urn:mozilla:extension:{@id}">
				<em:updates>
					<RDF:Seq>
						<xsl:apply-templates select="package"/>
					</RDF:Seq>
				</em:updates>
			</RDF:Description>
		</RDF:RDF>
	</xsl:template>
	
	
	<!-- Version -->
	<xsl:template match="package">
		<xsl:variable name="bin-uri" select="@bin-uri"/>
		<xsl:variable name="bin-hash-val" select="@bin-hash-val"/>
		<xsl:variable name="bin-hash-alg" select="@bin-hash-alg"/>
		<RDF:li>
			<xsl:for-each select="RDF:RDF/RDF:Description">
				<xsl:variable name="version" select="string(em:version)"/>
				<RDF:Description>
					<em:version><xsl:value-of select="$version"/></em:version>
					
					
					<xsl:for-each select="em:targetApplication">
						<!-- One targetApplication for each application the add-on is compatible with -->
						<em:targetApplication>
							<xsl:for-each select="RDF:Description">
								<xsl:variable name="targetId" select="string(em:id)"/>
								<xsl:variable name="minVersion" select="string(em:minVersion)"/>
								<xsl:variable name="maxVersion" select="string(em:maxVersion)"/>
								<RDF:Description>
									<em:id><xsl:value-of select="$targetId"/></em:id>
									<em:minVersion><xsl:value-of select="$minVersion"/></em:minVersion>
									<em:maxVersion><xsl:value-of select="$maxVersion"/></em:maxVersion>
									<!-- This is where this version of the add-on will be downloaded from -->
									<em:updateLink><xsl:value-of select="$bin-uri"/></em:updateLink>
									<em:updateHash>
										<xsl:value-of select="$bin-hash-alg"/>
										<xsl:text>:</xsl:text>
										<xsl:value-of select="$bin-hash-val"/>
									</em:updateHash>
									<!-- A page describing what is new in this updated version -->
									<!--<em:updateInfoURL>http://www.mysite.com/updateinfo2.2.xhtml</em:updateInfoURL>-->
								</RDF:Description>
							</xsl:for-each>
						</em:targetApplication>
					</xsl:for-each>
				</RDF:Description>
			</xsl:for-each>
		</RDF:li>
	</xsl:template>
</xsl:stylesheet>