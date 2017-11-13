<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns="http://www.w3.org/2000/svg"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:variable name="itemMap"
		select="/data/resource[@data-cms-name = 'item-map']" />

	<xsl:variable name="itemList"
		select="/data/data[@data-cms-name = 'items']/item" />
	<xsl:variable name="typeList"
		select="/data/data[@data-cms-name = 'items']/type" />

	<xsl:variable name="ITEM_SIZE" select="$itemMap/@width" />

	<xsl:variable name="WIDTH_COUNT" select="count($typeList)" />
	<xsl:variable name="HEIGHT_COUNT" select="120" />

	<xsl:template match="/data">
		<svg version="2.0" contentScriptType="application/javascript"
			contentStyleType="text/css" color-rendering="optimizeSpeed"
			shape-rendering="crispEdges" text-rendering="optimizeSpeed"
			image-rendering="pixelated"
			viewBox="{$ITEM_SIZE} {$ITEM_SIZE} {$WIDTH_COUNT * $ITEM_SIZE} {$HEIGHT_COUNT * $ITEM_SIZE}"
			width="{$WIDTH_COUNT * $ITEM_SIZE}" height="{$HEIGHT_COUNT * $ITEM_SIZE}">
			<defs>
				<image id="sprite-map" href="data:{$itemMap/@type};base64,{$itemMap/@base64}"
					width="{$itemMap/@width}" height="{$itemMap/@height}" />
				<clipPath id="sprite-clipping">
					<rect width="{$ITEM_SIZE}" height="{$ITEM_SIZE}" />
				</clipPath>
				<xsl:for-each select="//item">
					<use id="item-{@id}-image" href="#sprite-map"
						transform="translate(0, {(@image - 1) * - $ITEM_SIZE})" />
				</xsl:for-each>
			</defs>
			<xsl:for-each select="$typeList">
				<xsl:variable name="typeOffset" select="position() * $ITEM_SIZE" />
				<g class="{@name}" transform="translate({$typeOffset}, 0)">
					<xsl:for-each select="$itemList[@type = current()/@name]">
						<xsl:variable name="itemOffset" select="position() * $ITEM_SIZE" />
						<use href="#item-{@id}-image" transform="translate(0, {$itemOffset})"
							clip-path="url(#sprite-clipping)" />

						<view id="item-{@id}"
							viewBox="{$typeOffset} {$itemOffset} {$ITEM_SIZE} {$ITEM_SIZE}">
							<xsl:copy-of select="." />
							<!--
								<xsl:for-each select="@*">
								<xsl:attribute name="data-{name()}"><xsl:value-of select="."/></xsl:attribute>
								</xsl:for-each>
							-->
						</view>
					</xsl:for-each>
				</g>
			</xsl:for-each>
			<view id="item-0" viewBox="{-$ITEM_SIZE} {-$ITEM_SIZE} {$ITEM_SIZE} {$ITEM_SIZE}" />
		</svg>
	</xsl:template>
</xsl:stylesheet>
