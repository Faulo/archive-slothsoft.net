<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns="http://www.w3.org/1999/xhtml"
	xmlns:amber="http://schema.slothsoft.net/amber/amberdata"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:str="http://exslt.org/strings"
	extension-element-prefixes="str">
	
	<xsl:variable name="amberdata" select="/data/*[@data-cms-name='amberdata']/amber:amberdata"/>
	
	<xsl:template match="/data">
		<div class="Amber Editor">
			<script type="application/javascript"><![CDATA[
var savegameEditor;
window.addEventListener(
	"DOMContentLoaded",
	function(eve) {
		let node;
		
		node = document.querySelector(".Amber.Editor");
		if (node) {
			savegameEditor = new SavegameEditor(node);
		}
	},
	false
);
			]]></script>
			<xsl:apply-templates select="*[@data-cms-name = 'list']" mode="itemlist" />
			<div class="popup" onclick="savegameEditor.closePopup(arguments[0])" />
		</div>
	</xsl:template>
	
	<xsl:template match="amber:*">
		<xsl:apply-templates select="." mode="itemlist" />
	</xsl:template>
	
	<xsl:template match="amber:portrait-list | amber:item-list | amber:monster-list" mode="itemlist">
		<xsl:for-each select="*">
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
	</xsl:template>
	
	<xsl:template match="amber:class-list" mode="itemlist">
		<xsl:for-each select="*">
			<details data-template="flex">
				<summary>
					<h2>
						Klasse:
						<span class="green">
							<xsl:value-of select="@name" />
						</span>
					</h2>
				</summary>
				<xsl:apply-templates select="." mode="itemlist" />
			</details>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template match="amber:map-list" mode="itemlist">
		<xsl:choose>
			<xsl:when test="count(*) = 1">
				<xsl:for-each select="*">
					<button type="button" onclick="savegameEditor.viewMap(this.parentNode, '{@id}')">
						Weltkarte generieren: <span class="green"><xsl:value-of select="@name" /></span>
					</button>
					<template><xsl:copy-of select="."/></template>
				</xsl:for-each>
			</xsl:when>
			<xsl:otherwise>
				<xsl:for-each select="*">
					<details ontoggle="savegameEditor.viewMap(this, '{@id}')">
						<summary>
							<h2>
								Karte:
								<span class="green">
									<xsl:value-of select="@name" />
								</span>
							</h2>
						</summary>
						<template><xsl:copy-of select="."/></template>
					</details>
				</xsl:for-each>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="amber:class" mode="itemlist">
		<article data-class-id="{@id}" data-template="flex" class="Class">
			<xsl:value-of select="@name" />
			<xsl:apply-templates select="." mode="itemlist-inline"/>
		</article>
	</xsl:template>

	<xsl:template match="amber:class" mode="itemlist-inline">
		<table class="ClassData">
			<tbody>
				<xsl:for-each select="amber:skill">
					<tr class="right-aligned">
						<td><xsl:value-of select="@name"/>:</td>
						<xsl:if test="@current">
							<td class="number">
								<xsl:value-of select="concat(@current, '%')" />
							</td>
							<td>/</td>
						</xsl:if>
						<td class="number">
							<xsl:value-of select="concat(@maximum, '%')" />
						</td>
					</tr>
				</xsl:for-each>
			</tbody>
		</table>
	</xsl:template>

	<xsl:template match="amber:race" mode="itemlist">
		<article data-race-id="{@id}" data-template="flex" class="Race">
			<xsl:value-of select="@name" />
			<xsl:apply-templates select="." mode="itemlist-inline"/>
		</article>
	</xsl:template>

	<xsl:template match="amber:race" mode="itemlist-inline">
		<table class="RaceData">
			<tbody>
				<xsl:for-each select="amber:attribute">
					<tr class="right-aligned">
						<td><xsl:value-of select="@name"/>:</td>
						<xsl:if test="@current">
							<td class="number">
								<xsl:value-of select="concat(format-number(@current, '###'), '')" />
							</td>
							<td>/</td>
						</xsl:if>
						<td class="number">
							<xsl:value-of select="concat(@maximum, '')" />
						</td>
					</tr>
				</xsl:for-each>
			</tbody>
		</table>
	</xsl:template>

	<xsl:template match="amber:portrait" mode="itemlist">
		<article data-portrait-id="{@id}" data-template="flex" class="Portrait">
			<amber-picker type="portrait" class="portrait-picker"
									 role="button" tabindex="0">
				<amber-portrait value="{@id}" />
			</amber-picker>
			<xsl:value-of select="@name" />
		</article>
	</xsl:template>
	
	<xsl:template match="amber:monster" mode="itemlist">
		<!--<item xmlns="" id="361" image="9" name="MAGIERSTIEFEL" type="Schuhe" 
			hands="0" fingers="0" damage="0" armor="6" weight="850" gender="beide" class="Magier 
			Mystik. Alchem. Heiler"/> -->
		<article data-monster-id="{@id}" data-template="flex" class="Monster">
			<h3><xsl:value-of select="@name" /></h3>
			<ul>
				<li>
					<h3>race</h3>
					<xsl:apply-templates select="amber:race" mode="itemlist-inline"/>
				</li>
				<li>
					<h3>class</h3>
					<xsl:apply-templates select="amber:class" mode="itemlist-inline"/>
				</li>
				<li>
					<h3>equipment</h3>
					<xsl:apply-templates select="amber:equipment" mode="itemlist-inline"/>
				</li>
				<li>
					<h3>inventory</h3>
					<xsl:apply-templates select="amber:inventory" mode="itemlist-inline"/>
				</li>
				<li>
					<h3>spells</h3>
					<xsl:apply-templates select="amber:spellbook" mode="itemlist-inline"/>
				</li>
			</ul>
		</article>
	</xsl:template>
	<xsl:template match="amber:spellbook" mode="itemlist-inline">
		<div class="spells">
			<ul>
				<xsl:for-each select="amber:spell-instance">
					<li>
						<xsl:value-of select="@name"/>
					</li>
				</xsl:for-each>
			</ul>
		</div>
	</xsl:template>
	
	<xsl:template match="amber:equipment" mode="itemlist-inline">
		<div class="equipment" data-template="flex">
			<ul>
				<xsl:for-each select="amber:slot">
					<li>
						<xsl:apply-templates select="." mode="itemlist-inline"/>
					</li>
				</xsl:for-each>
			</ul>
		</div>
	</xsl:template>
	
	<xsl:template match="amber:inventory" mode="itemlist-inline">
		<div class="inventory" data-template="flex">
			<ul>
				<xsl:for-each select="amber:slot">
					<li>
						<xsl:apply-templates select="." mode="itemlist-inline"/>
					</li>
				</xsl:for-each>
			</ul>
		</div>
	</xsl:template>
	
	<xsl:template match="amber:slot" mode="itemlist-inline">
		<amber-picker type="item" class="item-picker"
									 role="button" tabindex="0"
									 onclick="savegameEditor.openPopup(arguments[0])">
			<amber-item-id value="{amber:item-instance/@item-id}"/>
			<amber-item-amount value="{amber:item-instance/@item-amount}"/>
			<amber-item-charge value="{amber:item-instance/@item-charge}"/>
		</amber-picker>
		<xsl:if test="@name">
			<span class="name"><xsl:value-of select="@name"/></span>
		</xsl:if>
	</xsl:template>

	<xsl:template match="amber:item" mode="itemlist">
		<!--<item xmlns="" id="361" image="9" name="MAGIERSTIEFEL" type="Schuhe" 
			hands="0" fingers="0" damage="0" armor="6" weight="850" gender="beide" class="Magier 
			Mystik. Alchem. Heiler"/> -->
		<article data-item-id="{@id}" data-template="flex" class="Item">
			<ul>
				<li>
					<table class="ItemName">
						<tr>
							<td>
								<amber-picker type="item" class="item-picker"
									 role="button" tabindex="0">
									<amber-item-id value="{@id}" />
								</amber-picker>
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
				<xsl:choose>
					<xsl:when test="amber:text">
						<li>
							<div class="textbox">
								<xsl:copy-of select="amber:text/node()"/>
							</div>
						</li>
					</xsl:when>
					<xsl:otherwise>
						<li>
							<table class="ItemClasses">
								<tbody>
									<tr>
										<td class="gray">------ Klassen ------</td>
									</tr>
									<tr>
										<td>
											<ul>
												<xsl:for-each select="amber:class/@name">
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
					</xsl:otherwise>
				</xsl:choose>
				<!--<xsl:copy-of select="."/> -->
			</ul>
		</article>
	</xsl:template>
</xsl:stylesheet>
