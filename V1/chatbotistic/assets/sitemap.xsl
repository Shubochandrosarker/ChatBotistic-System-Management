<?xml version="1.0" encoding="UTF-8"?>
<!--
  Branded XSL stylesheet for the Chatbotistic sitemap.
  Renders both the sitemap index (<sitemapindex>) and the type-specific
  sub-sitemaps (<urlset>) as styled, readable pages in the browser, while
  staying valid XML for crawlers.

  Layout mirrors the WordPressistic ecosystem reference: brand eyebrow,
  gradient heading with trailing period, intro copy, N sub-sitemaps
  pill, two-column table (URL · Last Modified), footer with llms.txt
  + llms-full.txt links.
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
	<title>Chatbotistic Sitemap</title>
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
		.wrap { max-width: 1100px; margin: 0 auto; padding: 64px 36px 80px; }
		.eyebrow {
			display: inline-flex; align-items: center; gap: 8px;
			padding: 7px 14px; border-radius: 999px;
			background: rgba(79,139,255,0.10);
			border: 1px solid rgba(79,139,255,0.30);
			font-family: ui-monospace, monospace; font-size: 11px;
			letter-spacing: 0.16em; text-transform: uppercase; color: #a8c1ff;
		}
		.eyebrow .dot { width: 6px; height: 6px; border-radius: 50%; background: #3fdcff; box-shadow: 0 0 10px #3fdcff; }
		h1 {
			font-family: 'Sora', system-ui, sans-serif;
			font-size: clamp(36px, 4.4vw, 54px);
			letter-spacing: -0.025em; line-height: 1.06;
			margin: 18px 0 14px; font-weight: 600;
		}
		h1 .accent {
			background: linear-gradient(120deg,#6aa3ff,#a070ff 60%,#3fdcff);
			-webkit-background-clip: text; background-clip: text; color: transparent;
		}
		.intro { color: #a4adc4; font-size: 15px; max-width: 720px; line-height: 1.6; margin: 0; }
		.count {
			font-family: ui-monospace, monospace; font-size: 12px;
			color: #6b7491; margin: 32px 0 14px; letter-spacing: 0.03em;
		}
		table {
			width: 100%; border-collapse: separate; border-spacing: 0;
			border: 1px solid rgba(255,255,255,0.10); border-radius: 16px;
			overflow: hidden; background: rgba(255,255,255,0.02);
		}
		thead th {
			text-align: left; padding: 14px 22px;
			font-family: ui-monospace, monospace; font-size: 11px;
			letter-spacing: 0.14em; text-transform: uppercase; color: #6b7491;
			background: rgba(255,255,255,0.025);
			border-bottom: 1px solid rgba(255,255,255,0.08);
		}
		thead th.last { text-align: right; }
		tbody td {
			padding: 16px 22px;
			border-bottom: 1px solid rgba(255,255,255,0.05);
			font-size: 14px;
			vertical-align: middle;
		}
		tbody tr:last-child td { border-bottom: 0; }
		tbody tr:hover { background: rgba(255,255,255,0.025); }
		.url { word-break: break-all; }
		.meta { color: #6b7491; font-family: ui-monospace, monospace; font-size: 12.5px; text-align: right; white-space: nowrap; }
		footer {
			margin-top: 32px; color: #6b7491; font-size: 13px;
			display: flex; gap: 8px; flex-wrap: wrap; align-items: center;
		}
		footer a { color: #6aa3ff; }
		footer .sep { color: #4a546b; }
	</style>
</head>
<body>
	<div class="wrap">

		<span class="eyebrow"><span class="dot"></span>XML Sitemap</span>

		<xsl:choose>
			<xsl:when test="s:sitemapindex">
				<h1>Chatbotistic <span class="accent">Sitemap.</span></h1>
				<p class="intro">This is the master sitemap index for the site. Search engines and AI assistants use it to discover every public URL. Built for humans here, machine-readable underneath.</p>
				<div class="count"><xsl:value-of select="count(s:sitemapindex/s:sitemap)"/> sub-sitemaps</div>
				<table>
					<thead><tr><th>Sitemap</th><th class="last">Last Modified</th></tr></thead>
					<tbody>
						<xsl:for-each select="s:sitemapindex/s:sitemap">
							<tr>
								<td class="url"><a href="{s:loc}"><xsl:value-of select="s:loc"/></a></td>
								<td class="meta"><xsl:value-of select="substring(s:lastmod,1,10)"/></td>
							</tr>
						</xsl:for-each>
					</tbody>
				</table>
			</xsl:when>
			<xsl:otherwise>
				<h1>Sitemap <span class="accent">URLs.</span></h1>
				<p class="intro">Public URLs in this section of the site. Search engines and AI assistants follow this list to crawl every entry.</p>
				<div class="count"><xsl:value-of select="count(s:urlset/s:url)"/> URLs</div>
				<table>
					<thead><tr><th>URL</th><th class="last">Last Modified</th></tr></thead>
					<tbody>
						<xsl:for-each select="s:urlset/s:url">
							<tr>
								<td class="url"><a href="{s:loc}"><xsl:value-of select="s:loc"/></a></td>
								<td class="meta"><xsl:value-of select="substring(s:lastmod,1,10)"/></td>
							</tr>
						</xsl:for-each>
					</tbody>
				</table>
			</xsl:otherwise>
		</xsl:choose>

		<footer>
			<span>Generated by the Chatbotistic theme</span>
			<span class="sep">·</span>
			<a href="/">chatbotistic.com</a>
			<span class="sep">·</span>
			<a href="/llms.txt">llms.txt</a>
			<span class="sep">·</span>
			<a href="/llms-full.txt">llms-full.txt</a>
		</footer>

	</div>
</body>
</html>
</xsl:template>
</xsl:stylesheet>
