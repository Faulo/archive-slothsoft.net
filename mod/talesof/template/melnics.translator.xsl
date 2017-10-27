<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="data[not(translator)]">
		<div class="Translator MelnicsTranslator">
			<article>
				<h2>Melnics Translator</h2>
				<label>
					<span><em>Pronunciation:</em> Input latin transcription of spoken Melnics here....</span>
					<textarea placeholder="baiba!" data-translator-type="melnics" class="input-melnics" oninput="MelnicsTranslator.typeCharacter(this)" autofocus="autofocus"/>
				</label>
				<label>
					<span><em>Meaning:</em> Input English text to be melnicsized here....</span>
					<textarea placeholder="wow!" data-translator-type="english" class="input-english" oninput="MelnicsTranslator.typeCharacter(this)"/>
				</label>
				<label>
					<span><em>Spelling:</em> Actual Melnics...</span>
					<textarea placeholder="wow!" class="output-english Melnics" readonly="readonly"/>
				</label>
			</article>
			<hr/>
			<article>
				<h2>About</h2>
				<article>
					<h3>v1.0 - 24.08.2013</h3>
					<p><a href="http://aselia.wikia.com/wiki/Melnics" rel="external">Melnics</a>, or, as some like to call it, <a href="https://lh5.googleusercontent.com/-DfxFBYMxnVc/UOE0aDX_VuI/AAAAAAAAGwM/qnnWBoxAXIU/s0/TOX_FansBible_Londau_01.jpg" rel="external">Londau</a>, is the language used by some folks in Tales of Eternia and Tales of Xillia.</p>
					<p>The "spoken Melnics" must be given as Hepburn-transcribed Japanese, with the exception that <q>ヅ</q> is written as <q>dzu</q>.</p>
					<p>Also, the Melnics sequence <q>ne</q> must be written as <q>ne</q> when they mean <q>ネ</q>, and as <q>n'e</q> when they mean <q>ンエ</q>.</p>
					<p>
						Example Input:<br/>
						<q>Ufu yaiodin din'edzuumugu tiausu, yaio aenun tiii towaa fudinn tiutoun.</q>
					</p>
				</article>
			</article>
		</div>
	</xsl:template>
	
	<xsl:template match="translator/translation" name="translator.output">
		<tbody class="output">
			<tr class="hiragana">
				<th rowspan="4">Output</th>
				<th>Kana</th>
				<xsl:for-each select="word">
					<td colspan="{@syllables}" data-parity="{(1 + position()) mod 2}">
						<ul>
							<xsl:for-each select="kana">
								<li>
									<xsl:call-template name="translator.link">
										<xsl:with-param name="kana" select="@name"/>
										<!--<xsl:with-param name="player" select="@player-uri"/>-->
									</xsl:call-template>
								</li>
							</xsl:for-each>
						</ul>
					</td>
				</xsl:for-each>
			</tr>
			<tr class="kanji">
				<th>Kanji</th>
				<xsl:for-each select="word">
					<td colspan="{@syllables}" data-parity="{(1 + position()) mod 2}">
						<ul>
							<xsl:for-each select="kana/kanji[string-length(@name) &gt; 0]">
								<li>
									<xsl:call-template name="translator.link">
										<xsl:with-param name="kana" select="@name"/>
										<!--<xsl:with-param name="player" select="@player-uri"/>-->
									</xsl:call-template>
								</li>
							</xsl:for-each>
						</ul>
					</td>
				</xsl:for-each>
			</tr>
			<tr class="english">
				<th>English</th>
				<xsl:for-each select="word">
					<td colspan="{@syllables}" data-parity="{(1 + position()) mod 2}">
						<dl>
							<xsl:for-each select="kana/kanji">
								<dt xml:lang="ja-jp">
									<xsl:variable name="player">
										<xsl:choose>
											<xsl:when test="string-length(@name) = 0">
												<xsl:value-of select="../@player-uri"/>
											</xsl:when>
											<xsl:otherwise>
												<xsl:value-of select="@player-uri"/>
											</xsl:otherwise>
										</xsl:choose>
									</xsl:variable>
									<xsl:variable name="base">
										<xsl:choose>
											<xsl:when test="string-length(@name) = 0">
												<xsl:value-of select="../@name"/>
											</xsl:when>
											<xsl:otherwise>
												<xsl:value-of select="@name"/>
											</xsl:otherwise>
										</xsl:choose>
									</xsl:variable>
									<xsl:variable name="top">
										<xsl:choose>
											<xsl:when test="string-length(@name) = 0">
											</xsl:when>
											<xsl:otherwise>
												<xsl:value-of select="../@name"/>
											</xsl:otherwise>
										</xsl:choose>
									</xsl:variable>
									<xsl:call-template name="word.complete">
										<xsl:with-param name="lang" select="'ja-jp'"/>
										<xsl:with-param name="audio" select="$player"/>
										<xsl:with-param name="base" select="$base"/>
										<xsl:with-param name="top" select="$top"/>
									</xsl:call-template>
									<!--
									<xsl:variable name="player">
										<xsl:choose>
											<xsl:when test="string-length(@name) = 0">
												<xsl:value-of select="../@player-uri"/>
											</xsl:when>
											<xsl:otherwise>
												<xsl:value-of select="@player-uri"/>
											</xsl:otherwise>
										</xsl:choose>
									</xsl:variable>
									<xsl:call-template name="word.audioPlayer">
										<xsl:with-param name="uri" select="$player"/>
									</xsl:call-template>
									<xsl:value-of select="@name"/>
									<xsl:if test="string-length(@name) = 0">
										<xsl:value-of select="../@name"/>
									</xsl:if>
									-->
								</dt>
								<dd>
									<xsl:copy-of select="english/node()"/>
									<!--
									<ul>
										<xsl:for-each select="english">
											<li><xsl:copy-of select="node()"/></li>
										</xsl:for-each>
									</ul>
									-->
								</dd>
							</xsl:for-each>
						</dl>
					</td>
				</xsl:for-each>
			</tr>
			<tr class="strokeOrder">
				<th>Stroke Order</th>
				<xsl:for-each select="word">
					<td colspan="{@syllables}" data-parity="{(1 + position()) mod 2}">
						<ul>
							<xsl:variable name="charList" select="kana/@name | kana/kanji/@name"/>
							<xsl:for-each select="$charList[string-length(.) &gt; 0]">
								<li>
									<xsl:call-template name="word.strokeOrder">
										<xsl:with-param name="text" select="."/>
									</xsl:call-template>
								</li>
							</xsl:for-each>
						</ul>
					</td>
				</xsl:for-each>
			</tr>
		</tbody>
	</xsl:template>
	
	<xsl:template name="translator.link">
		<xsl:param name="kana" select="string(.)"/>
		<xsl:param name="player"/>
		<xsl:variable name="sourceURI" select="/*/*/@sourceURI"/>
		<xsl:variable name="playerURI" select="/*/*/@playerURI"/>
		<xsl:if test="$player">
			<!--
			<a href="{$player}">►</a>
			<xsl:text> </xsl:text>
			-->
			<iframe src="{$player}" class="player"/>
		</xsl:if>
		<a href="{$sourceURI}{$kana}">
			<xsl:value-of select="$kana"/>
		</a>
	</xsl:template>
	
</xsl:stylesheet>
