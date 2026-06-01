<?xml version="1.0" encoding="UTF-8"?>
<!--
  Branded XSL stylesheet for the Chatbotistic sitemap.
  Renders both the sitemap index (<sitemapindex>) and the type-specific
  sub-sitemaps (<urlset>) as styled, readable pages in the browser, while
  staying valid XML for crawlers.
-->
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:s="http://www.sitemaps.org/schemas/sitemap/0.9">
<xsl:output method="html" encoding="UTF-8" indent="yes" doctype-system="about:legacy-compat"/>

<xsl:template match="/">
<html lang="en">
<head>
	<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
	<meta name="robots" content="noindex,follow"/>
	<title>XML Sitemap — Chatbotistic</title>
	<style>
		:root { color-scheme: dark; }
		* { box-sizing: border-box; }
		body {
			margin: 0; padding: 0;
			font-family: 'Manrope', system-ui, -apple-system, Segoe UI, sans-serif;
			background:
				radial-gradient(1100px 600px at 80% -200px, rgba(160,112,255,0.20), transparent 60%),
				radial-gradient(800px 500px at -100px 160px, rgba(79,139,255,0.16), transparent 60%),
				#04060c;
			color: #e9ecf5; min-height: 100vh;
		}
		a { color: #6aa3ff; text-decoration: none; }
		a:hover { text-decoration: underline; }
		.wrap { max-width: 1080px; margin: 0 auto; padding: 56px 28px 80px; }
		.brand { display: flex; align-items: center; gap: 12px; }
		.mark {
			width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0;
			display: grid; place-items: center; color: #fff;
			background: linear-gradient(135deg,#4f8bff,#a070ff 60%,#3fdcff);
			box-shadow: 0 10px 26px -8px rgba(120,90,255,0.7);
		}
		.brand b { font-family: 'Sora', system-ui, sans-serif; font-size: 20px; font-weight: 600; }
		.eyebrow {
			display: inline-block; margin-top: 28px;
			font-family: ui-monospace, monospace; font-size: 11px;
			letter-spacing: 0.16em; text-transform: uppercase; color: #6aa3ff;
		}
		h1 { font-family: 'Sora', system-ui, sans-serif; font-size: 32px; letter-spacing: -0.02em; margin: 10px 0 6px; }
		.intro { color: #a4adc4; font-size: 15px; max-width: 640px; }
		.count {
			display: inline-block; margin-top: 18px; padding: 7px 14px; border-radius: 999px;
			background: rgba(79,139,255,0.12); border: 1px solid rgba(79,139,255,0.3);
			font-family: ui-monospace, monospace; font-size: 12px; color: #cdd6ff;
		}
		table { width: 100%; border-collapse: collapse; margin-top: 26px; }
		thead th {
			text-align: left; padding: 12px 16px;
			font-family: ui-monospace, monospace; font-size: 11px;
			letter-spacing: 0.1em; text-transform: uppercase; color: #6b7491;
			border-bottom: 1px solid rgba(255,255,255,0.12);
		}
		tbody td { padding: 13px 16px; border-bottom: 1px solid rgba(255,255,255,0.06); font-size: 14px; }
		tbody tr:hover { background: rgba(255,255,255,0.03); }
		.url { word-break: break-all; }
		.meta { color: #6b7491; font-family: ui-monospace, monospace; font-size: 12px; white-space: nowrap; }
		.num { color: #6b7491; font-family: ui-monospace, monospace; font-size: 12px; }
		footer { margin-top: 36px; color: #6b7491; font-size: 13px; }
	</style>
</head>
<body>
	<div class="wrap">
		<div class="brand">
			<span class="mark">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5C4 5 5 4 6.5 4h11C19 4 20 5 20 6.5v8c0 1.5-1 2.5-2.5 2.5H13l-4 3v-3H6.5C5 17 4 16 4 14.5z"/></svg>
			</span>
			<b>Chatbotistic</b>
		</div>

		<xsl:choose>
			<!-- Sitemap index -->
			<xsl:when test="s:sitemapindex">
				<span class="eyebrow">XML Sitemap Index</span>
				<h1>Sitemap index</h1>
				<p class="intro">This index links to the type-specific sitemaps below. Search engines and AI assistants follow it to crawl every public page, post and category efficiently.</p>
				<div class="count"><xsl:value-of select="count(s:sitemapindex/s:sitemap)"/> sitemaps</div>
				<table>
					<thead><tr><th>#</th><th>Sitemap</th><th>Last modified</th></tr></thead>
					<tbody>
						<xsl:for-each select="s:sitemapindex/s:sitemap">
							<tr>
								<td class="num"><xsl:value-of select="position()"/></td>
								<td class="url"><a href="{s:loc}"><xsl:value-of select="s:loc"/></a></td>
								<td class="meta"><xsl:value-of select="substring(s:lastmod,1,10)"/></td>
							</tr>
						</xsl:for-each>
					</tbody>
				</table>
			</xsl:when>
			<!-- URL set (sub-sitemap) -->
			<xsl:otherwise>
				<span class="eyebrow">XML Sitemap</span>
				<h1>Every URL, mapped for search</h1>
				<p class="intro">This sitemap lists public Chatbotistic URLs so search engines and AI assistants can crawl and index the site. Generated automatically by the Chatbotistic theme.</p>
				<div class="count"><xsl:value-of select="count(s:urlset/s:url)"/> URLs</div>
				<table>
					<thead><tr><th>#</th><th>URL</th><th>Last modified</th><th>Frequency</th></tr></thead>
					<tbody>
						<xsl:for-each select="s:urlset/s:url">
							<tr>
								<td class="num"><xsl:value-of select="position()"/></td>
								<td class="url"><a href="{s:loc}"><xsl:value-of select="s:loc"/></a></td>
								<td class="meta"><xsl:value-of select="substring(s:lastmod,1,10)"/></td>
								<td class="meta"><xsl:value-of select="s:changefreq"/></td>
							</tr>
						</xsl:for-each>
					</tbody>
				</table>
			</xsl:otherwise>
		</xsl:choose>

		<footer>Generated by the Chatbotistic theme · Part of the WordPressistic ecosystem</footer>
	</div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>
