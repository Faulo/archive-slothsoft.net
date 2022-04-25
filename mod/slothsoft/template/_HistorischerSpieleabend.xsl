<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl"
	xmlns:lio="http://slothsoft.net"
	xmlns:func="http://exslt.org/functions"
	extension-element-prefixes="func">
	
	<xsl:variable name="LETTERS_LOWERCASE" select="'abcdefghijklmnopqrstuvwxyz'"/>
	<xsl:variable name="LETTERS_UPPERCASE" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
	<xsl:variable name="LETTERS_REGION" select="'🇦🇧🇨🇩🇪🇫🇬🇭🇮🇯🇰🇱🇲🇳🇴🇵🇶🇷🇸🇹🇺🇻🇼🇽🇾🇿'"/>
	
	<func:function name="lio:toRegionCode">
		<xsl:param name="region"/>
		<func:result select="translate($region, $LETTERS_UPPERCASE, $LETTERS_REGION)"/>
	</func:function>
	
	<func:function name="lio:toLowerCase">
		<xsl:param name="region"/>
		<func:result select="translate($region, $LETTERS_UPPERCASE, $LETTERS_LOWERCASE)"/>
	</func:function>
	
	<xsl:variable name="action" select="'#ffa793'"/>
	<xsl:variable name="adventure" select="'#fff293'"/>
	<xsl:variable name="strategy" select="'#a5d0d4'"/>
	<xsl:variable name="transition" select="7"/>
	
	<func:function name="lio:toBackgroundColor">
		<xsl:param name="red"/>
		<xsl:param name="green"/>
		<xsl:param name="blue"/>
		<func:result select="concat(lio:toBackgorundColorCode($red), ', ', lio:toBackgorundColorCode($green), ', ', lio:toBackgorundColorCode($blue))"/>
	</func:function>
	<func:function name="lio:toBackgorundColorCode">
		<xsl:param name="likert"/>
		<func:result select="128 + $likert * 24"/>
	</func:function>
	
	<func:function name="lio:toFontColor">
		<xsl:param name="red"/>
		<xsl:param name="green"/>
		<xsl:param name="blue"/>
		<func:result select="'250, 250, 255'"/>
		<!--<func:result select="concat(lio:toFontColorCode($red), ', ', lio:toFontColorCode($green), ', ', lio:toFontColorCode($blue))"/>-->
	</func:function>
	<func:function name="lio:toFontColorCode">
		<xsl:param name="likert"/>
		<func:result select="$likert * 48"/>
	</func:function>
	
	<xsl:template match="events">
		<html>
			<head>
				<title>Historischer Spieleabend</title>
				<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes"/>
				<link rel="icon" href="/getResource.php/slothsoft/HistorischerSpieleabendIcons/logo-small.png"/>
				<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous"/>
				
				<xsl:apply-templates select="//*[@name='HistorischerSpieleabendColors']" mode="style"/>
				<xsl:apply-templates select="tracks" mode="style"/>
			</head>
			<body>
				<xsl:apply-templates select="tracks" mode="attributes"/>
				<xsl:apply-templates select="tracks" mode="form"/>
				<xsl:for-each select="present/event">
					<xsl:comment>
Liebe Computerspielwissenschaftler*innen!
:joystick:~<xsl:value-of select="position() + count(//past/event)"/>. Historische Spieleabend~:joystick:

Wann? Am <xsl:value-of select="@date"/>-24:00 (s.t.)
Wo? Im Games Innovation Lab im Zapf
Wer? @<xsl:value-of select="@moderator"/> moderiert!
Was? <xsl:value-of select="lio:format-name(@xml:id)"/>: <xsl:value-of select="@theme"/>
Wir spielen:
<xsl:for-each select="game">
	<xsl:text> - </xsl:text>
	<xsl:value-of select="@name"/>
	<xsl:text> (</xsl:text>
	<xsl:value-of select="@by"/>
	<xsl:text>, </xsl:text>
	<xsl:value-of select="@from"/>
	<xsl:text>)
</xsl:text>
</xsl:for-each>
Ihr seid alle herzlich eingeladen, aber lasst bitte ein :joystick: hier wenn ihr kommen möchtet! :reblob:

Zukünftige Themen: http://slothsoft.net/HistorischerSpieleabend/?d
Zukünftigen Termine: https://calendar.google.com/calendar?cid=aGhrc3FxNDFsamlqYTJmYnZiZHVkcHEyOG9AZ3JvdXAuY2FsZW5kYXIuZ29vZ2xlLmNvbQ
</xsl:comment>
				</xsl:for-each>
				
				<xsl:for-each select="present/event">
					<div class="print poster">
						<h1>Historischer Spieleabend</h1>
						<h4><xsl:value-of select="@theme"/></h4>
						<img class="logo" src="/getResource.php/slothsoft/HistorischerSpieleabendIcons/logo-small.svg"/>
						<h2><xsl:value-of select="@date"/> im GIL</h2>
						<div class="event-left">
							<img class="icon" src="/getResource.php/slothsoft/HistorischerSpieleabendIcons/{@gfx}"/>
						</div>
						<div class="event-right">
							<ul class="ludography">
								<xsl:for-each select="game">
									<li>
										<span class="title"><xsl:value-of select="@name"/></span>
										<xsl:text> </xsl:text>
										<span class="year">
											<xsl:text>(</xsl:text>
											<!--
											<xsl:value-of select="@by"/>
											<xsl:text> </xsl:text>
											-->
											<xsl:value-of select="@from"/>
											<xsl:text>)</xsl:text>
										</span>
										<!--
										<xsl:text> </xsl:text>
										<span class="platform">[<xsl:value-of select="@on"/>]</span>
										-->
									</li>
								</xsl:for-each>
							</ul>
						</div>
						<img class="gil" src="/getResource.php/slothsoft/HistorischerSpieleabendIcons/GIL.png"/>
						<h3>
							<span class="box">
								<span>
									<large>2G</large>
									<small>(geimpft/genesen)</small>
								</span>
							</span>
						</h3>
					</div>
				</xsl:for-each>
				<div class="noprint">
					<h1 class="myBody">Historischer Spieleabend im GIL</h1>
					<hr/>
					<details class="present" open="open">
						<summary class="h2">
							Nächster Termin
						</summary>
						<div class="columns">
							<xsl:apply-templates select="present/event"/>
						</div>
					</details>
					<hr/>
					<details class="past">
						<summary class="h2">
							Vergangene Themen
						</summary>
						<div class="flex">
							<xsl:apply-templates select="past/event"/>
						</div>
					</details>
					<hr/>
					<!--
					<details class="future">
						<summary class="h2">
							Potentielle Themen (Corona-konform)
						</summary>
						<div class="flex">
							<xsl:apply-templates select="streamable/event">
								<xsl:sort select="count(@xml:id)"/>
							</xsl:apply-templates>
						</div>
					</details>
					<hr/>
					-->
					<details class="future" open="open">
						<summary class="h2">
							Potentielle Themen
						</summary>
						<div class="flex">
							<xsl:apply-templates select="future/event">
								<xsl:sort select="count(@xml:id)"/>
							</xsl:apply-templates>
						</div>
					</details>
					<hr/>
					<details class="unfinished">
						<summary class="h2">
							Unfertige Themen
						</summary>
						<div class="flex">
							<xsl:apply-templates select="unfinished/event">
								<xsl:sort select="count(@xml:id)"/>
							</xsl:apply-templates>
						</div>
					</details>
					<hr/>
					<details>
						<summary class="h2">
							Noch nicht einsortierte Spiele
						</summary>
						<div class="flex">
							<xsl:apply-templates select="unsorted/event">
								<xsl:sort select="count(@xml:id)"/>
							</xsl:apply-templates>
						</div>
					</details>
					
					
					<!--
					<h1>Historische Spieleabende in der CSWG</h1>
					<div class="container" id="top_row">
						<div class="container" id="next_topic">
							<h2>Nächster Termin:</h2>
							<xsl:apply-templates select="present/event"/>
						</div>
						<div class="container" id="past_topics">
							<h2>Vergangene Themen:</h2>
							<div class="row" id="past_topics_kacheln">
								<xsl:apply-templates select="past/event"/>
							</div>
						</div>
					</div>
					<div class="container" id="potential_topics">
						<h2>Potentielle Themen:</h2>
						<div class="container">
							<div class="row">
								<h3>Genres:</h3>
								<xsl:apply-templates select="future/event[@type='genre']"/>
							</div>
							<div class="row">
								<h3>Serien:</h3>
								<xsl:apply-templates select="future/event[@type='series']"/>
							</div>	
							<div class="row">
								<h3>Game Designer:</h3>
								<xsl:apply-templates select="future/event[@type='author']"/>
							</div>
						</div>
					</div>
					-->
				</div>
			</body>
		</html>
	</xsl:template>
	
	<xsl:template match="resource" mode="style">
		<xsl:variable name="colors" select=".//line[normalize-space(cell/@val) != '']"/>
		<style type="text/css">
			<xsl:for-each select="//track">
				<xsl:sort select="@name"/>
				<xsl:variable name="pos" select="position()"/>
				<xsl:value-of select="concat('.event.', @xml:id, ' { background-color: ', $colors[$pos]/cell/@val, '; }
')"/>
			</xsl:for-each>
		</style>
	</xsl:template>
	<xsl:template match="tracks" mode="style">
		<style type="text/css">
			<xsl:for-each select="track">
				<xsl:variable name="sum" select="100 div (@action + @adventure + @strategy)"/>
				<xsl:variable name="t1" select="@action * $sum"/>
				<xsl:variable name="t2" select="(@action + @adventure) * $sum"/>
				<xsl:text>body[data-</xsl:text>
				<xsl:value-of select="@xml:id"/>
				<xsl:text>="0"] article[id^="</xsl:text>
				<xsl:value-of select="@xml:id"/>
				<xsl:text>"] { display: none; } </xsl:text>
				<!--
				<xsl:text>[data-genre="</xsl:text>
				<xsl:value-of select="@xml:id"/>
				<xsl:text>"] { background-image: linear-gradient(</xsl:text>
				<xsl:value-of select="concat(
					$action, ' ', $t1 - $transition, '%, ', $t1, '%, ', $adventure, ' ', $t1 + $transition, '%, ',
					$adventure, ' ', $t2 - $transition, '%, ', $t2, '%, ', $strategy, ' ', $t2 + $transition, '%'
				)"/>
				<xsl:text>); } </xsl:text>
				<xsl:text>[data-genre="</xsl:text>
				<xsl:value-of select="@xml:id"/>
				<xsl:text>"] .theme { color: rgb(</xsl:text>
				<xsl:value-of select="lio:toFontColor(@action, @adventure, @strategy)"/>
				<xsl:text>); } </xsl:text>
				-->
			</xsl:for-each>
		</style>
	</xsl:template>
	
	<xsl:template match="tracks" mode="attributes">
		<xsl:for-each select="track">
			<xsl:attribute name="data-{@xml:id}">1</xsl:attribute>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template match="tracks" mode="form">
		<details class="tracks noprint">
			<summary>Verfügbare Kurse</summary>
			<small>(Doppelklicken um alle zu markieren)</small>
			<xsl:for-each select="track">
				<xsl:sort select="@name"/>
				<div>
					<label ondblclick="
							var c = !this.querySelector('input').checked;
							document.querySelectorAll('input').forEach(i => i.checked = c);
							document.querySelectorAll('input').forEach(i => i.onchange());
						">
						<input type="checkbox" name="{@xml:id}" checked="checked" onchange="document.body.setAttribute('data-' + this.name, this.checked ? '1' : '0')"/>
						<span class="id"><xsl:value-of select="@xml:id"/></span>
						<xsl:text> </xsl:text>
						<xsl:value-of select="@name"/>
					</label>
					<ul class="subtracks">
						<xsl:for-each select="subtrack">
							<li>
								<span class="id"><xsl:value-of select="concat(../@xml:id, position(), 'xx')"/></span>
								<xsl:text> </xsl:text>
								<xsl:value-of select="@name"/>
							</li>
						</xsl:for-each>
					</ul>
				</div>
			</xsl:for-each>
		</details>
	</xsl:template>
	
	<xsl:template match="event">
		<article class="event {substring(@xml:id, 1, 3)}" id="{@xml:id}" data-genre="{substring(@xml:id, 1, 3)}"><!--data-type="{@type}" -->
			<xsl:if test="@todo">
				<xsl:attribute name="data-todo"/>
			</xsl:if>
			<xsl:if test="@todo">
				<xsl:attribute name="data-unavailable"/>
			</xsl:if>
			<xsl:if test="@xml:id">
				<span style="float: left;">[<xsl:value-of select="@xml:id"/>]</span>
			</xsl:if>
			<xsl:if test="@date != ''">
				<p class="date"><xsl:value-of select="@date"/></p>
			</xsl:if>
			<xsl:if test="@theme">
				<h2 class="myBody header">
					<xsl:if test="@xml:id">
						<span class="course-id" data-course="{lio:format-name(@xml:id)}">
							<xsl:apply-templates select="." mode="link"/>
						</span>
					</xsl:if>
					<span class="theme">
						<xsl:value-of select="@theme"/>
					</span>
					<xsl:if test="@twitter">
						<a href="https://twitter.com/{@twitter}" target="_blank">
							🕊️
						</a>
					</xsl:if>
				</h2>
			</xsl:if>
					
			<div class="tabled-content">
				<xsl:if test="@gfx">
					<div>
						<img class="icon" src="/getResource.php/slothsoft/HistorischerSpieleabendIcons/{@gfx}"/>
					</div>
				</xsl:if>
				<div>
					<xsl:if test="@moderator">
						<p class="moderator">Moderator*in: <xsl:value-of select="@moderator"/></p>
					</xsl:if>
					<ul class="ludography">
						<xsl:for-each select="game">
							<!--<xsl:sort select="@from"/>-->
							<li>
								<xsl:if test="@wanted">
									<xsl:attribute name="data-wanted">
										<xsl:value-of select="@wanted"/>
									</xsl:attribute>
								</xsl:if>
								<xsl:apply-templates select="."/>
							</li>
						</xsl:for-each>
					</ul>
					<!--
					<xsl:if test="req">
						<p class="prereqs">Prereqs: <xsl:apply-templates select="req"/></p>
					</xsl:if>
					-->
					<xsl:if test="read">
						<p class="reading">Required reading: <xsl:apply-templates select="read"/></p>
					</xsl:if>
				</div>
			</div>
		</article>
	</xsl:template>
	
	<xsl:template match="req">
		<xsl:text> </xsl:text>
		<xsl:apply-templates select="." mode="link"/>
	</xsl:template>
	
	<xsl:template match="read">
		<xsl:text> </xsl:text>
		<xsl:apply-templates select="." mode="link"/>
	</xsl:template>
	
	<xsl:template match="event" mode="link">
		<xsl:variable name="ref" select="string(@xml:id)"/>
		<a href="#{$ref}" class="id" title="{lio:lookup-name($ref)}">
			<xsl:value-of select="lio:format-name($ref)"/>
			<xsl:text>:</xsl:text>
		</a>
	</xsl:template>
	
	<xsl:template match="req" mode="link">
		<xsl:variable name="ref" select="string(@ref)"/>
		<a href="#{$ref}" class="id" title="{lio:lookup-name($ref)}" onclick="document.querySelectorAll('h1 ~ details').forEach(d => d.open = true)">
			<xsl:attribute name="data-prereq">
				<xsl:if test="id($ref)/parent::future">missing</xsl:if>
			</xsl:attribute>
			<xsl:value-of select="lio:lookup-name($ref)"/>
		</a>
	</xsl:template>
	
	<xsl:template match="read" mode="link">
		<a href="{@href}" target="_blank" rel="external">
			<xsl:value-of select="concat(@title, ' (', @author, ', ', @year, ')')"/>
		</a>
	</xsl:template>
	
	<func:function name="lio:format-id">
		<xsl:param name="ref"/>
		<xsl:variable name="id" select="substring($ref, 1, 3)"/>
		<xsl:variable name="no" select="substring($ref, 4, 3)"/>
		<xsl:variable name="name" select="//track[@xml:id=$id]/@name"/>
		
		<func:result>
			<xsl:value-of select="$id"/>
			<xsl:text>&#160;</xsl:text>
			<xsl:value-of select="$no"/>
		</func:result>
	</func:function>
	
	<func:function name="lio:format-name">
		<xsl:param name="ref"/>
		<xsl:variable name="id" select="substring($ref, 1, 3)"/>
		<xsl:variable name="no" select="substring($ref, 4, 3)"/>
		<xsl:variable name="track" select="//track[@xml:id=$id]"/>
		<xsl:variable name="subtrack" select="$track/subtrack[position() = substring($no, 1, 1)]"/>
		<func:result>
			<xsl:value-of select="$track/@name"/>
			<xsl:text>&#160;</xsl:text>
			<xsl:value-of select="$no"/>
			<xsl:if test="$subtrack">
				<xsl:text> (</xsl:text>
				<xsl:value-of select="$subtrack/@name"/>
				<xsl:text>)</xsl:text>
			</xsl:if>
		</func:result>
	</func:function>
	
	<func:function name="lio:lookup-name">
		<xsl:param name="ref"/>
		<xsl:variable name="id" select="substring($ref, 1, 3)"/>
		<xsl:variable name="no" select="substring($ref, 4, 3)"/>
		
		<func:result>
			<xsl:value-of select="lio:format-name($ref)"/>
			<xsl:text>: </xsl:text>
			<xsl:value-of select="id($ref)/@theme"/>
		</func:result>
	</func:function>
	
	
	<xsl:template match="game">
		<span class="Z3988-TODO">
			<xsl:attribute name="title">
				<xsl:value-of select="lio:param('ctx_ver', 'Z39.88-2004')"/>
				<xsl:text>&amp;</xsl:text>
				<xsl:value-of select="lio:param('rft.title', @name)"/>
				<xsl:text>&amp;</xsl:text>
				<xsl:value-of select="lio:param('rft.author', @by)"/>
				<xsl:text>&amp;</xsl:text>
				<xsl:value-of select="lio:param('rft.pub', @by)"/>
				<xsl:text>&amp;</xsl:text>
				<xsl:value-of select="lio:param('rft.date', @from)"/>
				<xsl:text>&amp;</xsl:text>
				<xsl:value-of select="lio:param('rft.genre', 'misc')"/>
				<xsl:text>&amp;</xsl:text>
				<xsl:value-of select="lio:param('rft.howpublished', @on)"/>
			</xsl:attribute>
		</span>
		<xsl:comment>
@misc{<xsl:value-of select="@by"/>.<xsl:value-of select="@from"/>,
 author = {{<xsl:value-of select="@by"/>}},
 year = {<xsl:value-of select="@from"/>},
 title = {<xsl:value-of select="@name"/>},
 organization = {<xsl:value-of select="@pub"/>},
 howpublished = {<xsl:value-of select="@on"/>}
}
		</xsl:comment>
		<span class="country">
			<xsl:choose>
				<xsl:when test="@country = 'ww'"><small>🌎</small></xsl:when>
				<xsl:when test="@country = '?'"><small>❓</small></xsl:when>
				<xsl:when test="string-length(@country)">
					<img src="https://cdn.rawgit.com/hjnilsson/country-flags/master/svg/{lio:toLowerCase(@country)}.svg" alt="{lio:toRegionCode(@country)}"/>
				</xsl:when>
				<xsl:otherwise><small>❔</small></xsl:otherwise>
			</xsl:choose>
		</span>
		<xsl:text>&#160;</xsl:text>
		<span class="dev">
			<xsl:value-of select="@by"/>
		</span>
		<xsl:text>. </xsl:text>
		<span class="year">
			<xsl:text>(</xsl:text>
			<xsl:value-of select="@from"/>
			<xsl:text>)</xsl:text>
		</span>
		<xsl:text>. </xsl:text>
		<span class="title">
			<xsl:call-template name="wiki">
				<xsl:with-param name="term" select="@name"/>
			</xsl:call-template>
		</span>
		<xsl:text>. </xsl:text>
		<xsl:if test="@version">
			<span class="version">
				<xsl:text> V. </xsl:text>
				<xsl:value-of select="@version"/>
			</span>
			<xsl:text>. </xsl:text>
		</xsl:if>
		<xsl:choose>
			<xsl:when test="@href">
				<a href="{@href}" target="_blank" rel="external">
					<span class="platform">
						<xsl:text> [</xsl:text>
						<xsl:value-of select="@on"/>
						<xsl:text>]</xsl:text>
					</span>
				</a>
			</xsl:when>
			<xsl:otherwise>
				<span class="platform">
					<xsl:text> [</xsl:text>
					<xsl:value-of select="@on"/>
					<xsl:text>]</xsl:text>
				</span>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:text>. </xsl:text>
		<xsl:if test="string-length(@manual)">
			<a class="manual" href="/getResource.php/slothsoft/HistorischerSpieleabend/{@manual}" target="_blank">📕</a>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="wiki">
		<xsl:param name="term" select="."/>
		<a href="{lio:wiki($term)}" target="_blank" rel="external">
			<xsl:value-of select="$term"/>
		</a>
	</xsl:template>
	
	<func:function name="lio:wiki">
		<xsl:param name="term" select="."/>
		<func:result>
			<xsl:text>https://en.wikipedia.org/w/index.php?title=Special:Search&amp;search=</xsl:text>
			<xsl:value-of select="php:functionString('urlencode', normalize-space($term))"/>
		</func:result>
	</func:function>
	
	<func:function name="lio:param">
		<xsl:param name="key"/>
		<xsl:param name="val"/>
		<func:result select="concat(php:functionString('urlencode', normalize-space($key)), '=', php:functionString('urlencode', normalize-space($val)))"/>
	</func:function>
</xsl:stylesheet>