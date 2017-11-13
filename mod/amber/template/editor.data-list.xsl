<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:str="http://exslt.org/strings"
	extension-element-prefixes="str">

	<xsl:variable name="fragment"
		select="/data/*[@data-cms-name = 'list']/fragment" />

	<xsl:template match="/data">
		<div class="Amber Editor">
			<xsl:for-each select="$fragment/category">
				<details data-template="flex">
					<summary>
						<h2>
							Kategorie:
							<span class="green">
								<xsl:value-of select="@name" />
							</span>
						</h2>
					</summary>
					<ul>
						<xsl:for-each select="*">
							<li>
								<xsl:apply-templates select="." mode="itemlist" />
							</li>
						</xsl:for-each>
					</ul>
				</details>
			</xsl:for-each>
		</div>
	</xsl:template>

	<xsl:template match="portrait" mode="itemlist">
		<article data-portrait-id="{@id}" data-template="flex" class="Portrait">
			<div role="button" tabindex="0" class="picker">
				<div class="PortraitId" data-dictionary="PortraitId"
					data-picker-value="{@id}" />
			</div>
			<xsl:value-of select="@name" />

		</article>
	</xsl:template>

	<xsl:template match="item" mode="itemlist">
		<!--<item xmlns="" id="361" image="9" name="MAGIERSTIEFEL" type="Schuhe" 
			hands="0" fingers="0" damage="0" armor="6" weight="850" gender="beide" class="Magier 
			Mystik. Alchem. Heiler"/> -->
		<article data-item-id="{@id}" data-template="flex" class="Item">
			<ul>
				<li>
					<table class="ItemName">
						<tr>
							<td>
								<div role="button" tabindex="0" class="picker"
									data-hover-text="{@name}">
									<div class="ItemId" data-dictionary="ItemId"
										data-picker-value="{@id}" />
								</div>
							</td>
							<td>
								<h3>
									<xsl:value-of select="@name" />
								</h3>
								<xsl:value-of select="@type" />
							</td>
						</tr>
					</table>
					<table class="ItemData">
						<tbody>
							<tr class="right-aligned">
								<td>Gewicht:</td>
								<td class="number">
									<xsl:value-of select="concat(@weight, ' gr')" />
								</td>
							</tr>
							<tr class="right-aligned">
								<td>Wert:</td>
								<td class="number">
									<xsl:value-of select="concat(@price, ' gp')" />
								</td>
							</tr>
						</tbody>
						<tbody>
							<tr class="right-aligned">
								<td>Hände:</td>
								<td class="number">
									<xsl:value-of select="@hands" />
								</td>
							</tr>
							<tr class="right-aligned">
								<td>Finger:</td>
								<td class="number">
									<xsl:value-of select="@fingers" />
								</td>
							</tr>
							<tr class="right-aligned">
								<td>Schaden:</td>
								<td class="number">
									<xsl:if test="@damage &gt; 0">
										<xsl:text>+</xsl:text>
									</xsl:if>
									<xsl:value-of select="@damage" />
								</td>
							</tr>
							<tr class="right-aligned">
								<td>Schutz:</td>
								<td class="number">
									<xsl:if test="@armor &gt; 0">
										<xsl:text>+</xsl:text>
									</xsl:if>
									<xsl:value-of select="@armor" />
								</td>
							</tr>
						</tbody>
					</table>
				</li>
				<li>
					<table class="ItemClasses">
						<tbody>
							<tr>
								<td class="gray">------ Klassen ------</td>
							</tr>
							<tr>
								<td>
									<ul>
										<xsl:for-each select="str:tokenize(@class) | class/@name">
											<li>
												<xsl:value-of select="." />
											</li>
										</xsl:for-each>
									</ul>
								</td>
							</tr>
						</tbody>
						<tbody>
							<tr>
								<td class="gray">Geschlecht</td>
							</tr>
							<tr>
								<td>
									<xsl:value-of select="@gender" />
								</td>
							</tr>
						</tbody>
					</table>
				</li>
				<li>
					<table class="ItemMagic">
						<tbody>
							<tr>
								<td class="right-aligned" data-hover-text="Lebenspunkte Maximum">LP-Max: </td>
								<td class="number">
									<xsl:if test="@lp-max &gt; 0">
										<xsl:choose>
											<xsl:when test="@is-cursed">
												-
											</xsl:when>
											<xsl:otherwise>
												+
											</xsl:otherwise>
										</xsl:choose>
									</xsl:if>
									<xsl:value-of select="@lp-max" />
								</td>
								<td class="right-aligned" data-hover-text="Spruchpunkte Maximum">SP-Max: </td>
								<td class="number">
									<xsl:if test="@sp-max &gt; 0">
										<xsl:choose>
											<xsl:when test="@is-cursed">
												-
											</xsl:when>
											<xsl:otherwise>
												+
											</xsl:otherwise>
										</xsl:choose>
									</xsl:if>
									<xsl:value-of select="@sp-max" />
								</td>
							</tr>
							<tr>
								<td class="right-aligned" data-hover-text="Magischer Rüstschutz, Angriff">M-B-W: </td>
								<td class="number">
									<xsl:if test="@magic-weapon &gt; 0">
										<xsl:choose>
											<xsl:when test="@is-cursed">
												-
											</xsl:when>
											<xsl:otherwise>
												+
											</xsl:otherwise>
										</xsl:choose>
									</xsl:if>
									<xsl:value-of select="@magic-weapon" />
								</td>
								<td class="right-aligned" data-hover-text="Magischer Rüstschutz, Verteidigung">M-B-R: </td>
								<td class="number">
									<xsl:if test="@magic-armor &gt; 0">
										<xsl:choose>
											<xsl:when test="@is-cursed">
												-
											</xsl:when>
											<xsl:otherwise>
												+
											</xsl:otherwise>
										</xsl:choose>
									</xsl:if>
									<xsl:value-of select="@magic-armor" />
								</td>
							</tr>
						</tbody>
						<tbody>
							<tr>
								<td colspan="4" class="orange">
									<xsl:if test="@attribute-value &gt; 0">
										Attribut
									</xsl:if>
								</td>
							</tr>
							<tr>
								<td colspan="3">
									<xsl:if test="@attribute-value &gt; 0">
										<xsl:value-of select="@attribute-type" />
									</xsl:if>
								</td>
								<td class="number">
									<xsl:if test="@attribute-value &gt; 0">
										<xsl:choose>
											<xsl:when test="@is-cursed">
												-
											</xsl:when>
											<xsl:otherwise>
												+
											</xsl:otherwise>
										</xsl:choose>
										<xsl:value-of select="@attribute-value" />
									</xsl:if>
								</td>
							</tr>
						</tbody>
						<tbody>
							<tr>
								<td colspan="4" class="orange">
									<xsl:if test="@skill-value &gt; 0">
										Fähigkeit
									</xsl:if>
								</td>
							</tr>
							<tr>
								<td colspan="3">
									<xsl:if test="@skill-value &gt; 0">
										<xsl:value-of select="@skill-type" />
									</xsl:if>
								</td>
								<td class="number">
									<xsl:if test="@skill-value &gt; 0">
										<xsl:choose>
											<xsl:when test="@is-cursed">
												-
											</xsl:when>
											<xsl:otherwise>
												+
											</xsl:otherwise>
										</xsl:choose>
										<xsl:value-of select="@skill-value" />
									</xsl:if>
								</td>
							</tr>
						</tbody>
						<tbody>
							<tr>
								<td colspan="4" class="orange">
									<xsl:if test="@spell-id &gt; 0">
										<xsl:value-of select="@spell-type" />
									</xsl:if>
								</td>
							</tr>
							<tr>
								<td colspan="4">
									<xsl:if test="@spell-id &gt; 0">
										<xsl:value-of
											select="concat(@spell-name, ' (', @charges-default, ')')" />
									</xsl:if>
									<xsl:if test="@is-cursed">
										<span class="green">verflucht</span>
									</xsl:if>
								</td>
							</tr>
						</tbody>
					</table>
				</li>
				<!--<xsl:copy-of select="."/> -->
			</ul>
		</article>
	</xsl:template>
</xsl:stylesheet>
