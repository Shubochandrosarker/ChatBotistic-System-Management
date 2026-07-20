<?php
/**
 * Markdown for Agents — when a request sends `Accept: text/markdown`, the
 * same URL that would normally render HTML returns a markdown version
 * instead. Browsers keep getting HTML; nothing changes for them.
 *
 * Every template shares one wrapper — header.php opens <main id="cb-main">
 * and footer.php closes it — so instead of re-deriving page content per
 * template (front-page.php alone is a hand-built 400-line landing page, not
 * post_content), the theme's own already-rendered HTML is captured with
 * output buffering and #cb-main is converted to markdown. That works
 * uniformly for the marketing landing pages, docs, pricing, and ordinary
 * post_content pages alike.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current request prefers text/markdown over text/html, per the
 * Accept header's q-values (RFC 9110 §12.5.1).
 *
 * @return bool
 */
function cb_markdown_requested() {
	$accept = isset( $_SERVER['HTTP_ACCEPT'] ) ? (string) $_SERVER['HTTP_ACCEPT'] : '';
	if ( '' === $accept || false === stripos( $accept, 'markdown' ) ) {
		return false;
	}

	$q = array(
		'text/markdown' => -1.0,
		'text/html'     => -1.0,
		'*/*'           => -1.0,
	);

	foreach ( explode( ',', $accept ) as $part ) {
		$bits = explode( ';', trim( $part ) );
		$type = strtolower( trim( (string) array_shift( $bits ) ) );
		if ( ! isset( $q[ $type ] ) ) {
			continue;
		}
		$value = 1.0;
		foreach ( $bits as $param ) {
			if ( preg_match( '/q\s*=\s*([0-9.]+)/', $param, $m ) ) {
				$value = (float) $m[1];
			}
		}
		$q[ $type ] = max( $q[ $type ], $value );
	}

	if ( $q['text/markdown'] <= 0 ) {
		return false;
	}

	return $q['text/markdown'] >= max( $q['text/html'], $q['*/*'] );
}

/**
 * Start buffering the response body so the finished HTML can be converted
 * to markdown once the theme has fully rendered the page.
 */
function cb_markdown_maybe_start_buffer() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	if ( get_query_var( 'cb_feed' ) ) {
		return; // Sitemap / llms.txt / api-catalog / openapi.json / auth.md already pick their own content type.
	}
	if ( ! cb_markdown_requested() ) {
		return;
	}
	ob_start();
	add_action( 'shutdown', 'cb_markdown_finish_buffer', 0 );
}
add_action( 'template_redirect', 'cb_markdown_maybe_start_buffer', 0 );

/**
 * Convert the buffered page HTML to markdown and print that instead. Falls
 * back to printing the original HTML untouched if anything about the page
 * doesn't match the expected shape — a markdown request should never be the
 * reason a page breaks.
 */
function cb_markdown_finish_buffer() {
	$html     = ob_get_clean();
	$markdown = ( false !== $html && '' !== trim( (string) $html ) )
		? cb_html_to_markdown_page( (string) $html )
		: null;

	if ( null === $markdown ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- passthrough of this same request's own rendered HTML.
		return;
	}

	if ( ! headers_sent() ) {
		header( 'Content-Type: text/markdown; charset=UTF-8' );
		header( 'X-Markdown-Tokens: ' . cb_markdown_token_estimate( $markdown ) );
	}
	echo $markdown; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from already-escaped page HTML text nodes.
}

/**
 * Extract #cb-main from a fully-rendered page and convert it to markdown,
 * with the page title, description and canonical URL prepended.
 *
 * @param string $html Full rendered page HTML.
 * @return string|null Markdown, or null if this doesn't look like a normal themed page.
 */
function cb_html_to_markdown_page( $html ) {
	if ( ! class_exists( 'DOMDocument' ) ) {
		return null;
	}

	$doc  = new DOMDocument();
	$prev = libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR );
	libxml_clear_errors();
	libxml_use_internal_errors( $prev );

	$xpath = new DOMXPath( $doc );
	$nodes = $xpath->query( "//*[@id='cb-main']" );
	$main  = ( $nodes && $nodes->length ) ? $nodes->item( 0 ) : null;
	if ( ! $main ) {
		return null;
	}

	$body = trim( preg_replace( "/\n{3,}/", "\n\n", cb_dom_children_to_markdown( $main ) ) );

	$title = wp_strip_all_tags( wp_get_document_title() );
	$desc  = function_exists( 'cb_meta_description' ) ? cb_meta_description() : '';
	$url   = function_exists( 'cb_current_url' ) ? cb_current_url() : home_url( '/' );

	$lines = array();
	if ( $title ) {
		$lines[] = '# ' . $title;
		$lines[] = '';
	}
	if ( $desc ) {
		$lines[] = '> ' . $desc;
		$lines[] = '';
	}
	$lines[] = 'Source: ' . $url;
	$lines[] = '';
	$lines[] = $body;

	return implode( "\n", $lines ) . "\n";
}

/**
 * Element subtrees that carry no page prose — chrome, scripts, interactive
 * controls — and should be skipped entirely rather than converted.
 *
 * @return string[]
 */
function cb_markdown_skip_tags() {
	return array( 'script', 'style', 'noscript', 'svg', 'nav', 'form', 'button', 'select', 'textarea', 'iframe', 'template' );
}

/**
 * Render every child of a DOM node to markdown and concatenate.
 *
 * @param DOMNode $node Parent node.
 * @return string
 */
function cb_dom_children_to_markdown( DOMNode $node ) {
	$out = '';
	foreach ( $node->childNodes as $child ) {
		$out .= cb_dom_node_to_markdown( $child );
	}
	return $out;
}

/**
 * Render one DOM node (and its subtree) to markdown.
 *
 * @param DOMNode $node Node.
 * @return string
 */
function cb_dom_node_to_markdown( DOMNode $node ) {
	if ( XML_TEXT_NODE === $node->nodeType ) {
		return preg_replace( '/\s+/', ' ', $node->textContent );
	}
	if ( XML_ELEMENT_NODE !== $node->nodeType || ! ( $node instanceof DOMElement ) ) {
		return '';
	}

	$tag = strtolower( $node->tagName );

	if ( in_array( $tag, cb_markdown_skip_tags(), true ) ) {
		return '';
	}
	if ( 'true' === $node->getAttribute( 'aria-hidden' ) ) {
		return '';
	}

	switch ( $tag ) {
		case 'h1':
		case 'h2':
		case 'h3':
		case 'h4':
		case 'h5':
		case 'h6':
			$text = trim( cb_dom_children_to_markdown( $node ) );
			return $text ? "\n" . str_repeat( '#', (int) substr( $tag, 1 ) ) . ' ' . $text . "\n\n" : '';

		case 'p':
		case 'summary':
			$text = trim( cb_dom_children_to_markdown( $node ) );
			return $text ? $text . "\n\n" : '';

		case 'br':
			return "\n";

		case 'hr':
			return "\n---\n\n";

		case 'strong':
		case 'b':
			$text = trim( cb_dom_children_to_markdown( $node ) );
			return $text ? '**' . $text . '**' : '';

		case 'em':
		case 'i':
			$text = trim( cb_dom_children_to_markdown( $node ) );
			return $text ? '_' . $text . '_' : '';

		case 'code':
			$text = trim( $node->textContent );
			return $text ? '`' . $text . '`' : '';

		case 'pre':
			$text = trim( $node->textContent );
			return $text ? "\n```\n" . $text . "\n```\n\n" : '';

		case 'blockquote':
			$text = trim( cb_dom_children_to_markdown( $node ) );
			if ( '' === $text ) {
				return '';
			}
			return implode( "\n", array_map( static fn( $l ) => '> ' . $l, explode( "\n", $text ) ) ) . "\n\n";

		case 'a':
			$text = trim( cb_dom_children_to_markdown( $node ) );
			$href = $node->getAttribute( 'href' );
			if ( '' === $text ) {
				return '';
			}
			if ( '' === $href || '#' === $href[0] || 0 === stripos( $href, 'javascript:' ) ) {
				return $text;
			}
			return '[' . $text . '](' . cb_markdown_absolute_url( $href ) . ')';

		case 'img':
			$alt = trim( $node->getAttribute( 'alt' ) );
			$src = $node->getAttribute( 'src' );
			return ( '' !== $alt && '' !== $src ) ? '![' . $alt . '](' . cb_markdown_absolute_url( $src ) . ')' . "\n\n" : '';

		case 'ul':
		case 'ol':
			return "\n" . cb_dom_list_to_markdown( $node, 'ol' === $tag ) . "\n";

		case 'table':
			$table = cb_dom_table_to_markdown( $node );
			return $table ? "\n" . $table . "\n" : '';

		default:
			return cb_dom_children_to_markdown( $node );
	}
}

/**
 * Render a <ul>/<ol> to markdown list lines, recursing into nested lists.
 *
 * @param DOMElement $list    The list element.
 * @param bool       $ordered Whether to number items.
 * @param int        $depth   Nesting depth, for indentation.
 * @return string
 */
function cb_dom_list_to_markdown( DOMElement $list, $ordered, $depth = 0 ) {
	$out = '';
	$i   = 1;
	foreach ( $list->childNodes as $item ) {
		if ( ! ( $item instanceof DOMElement ) || 'li' !== strtolower( $item->tagName ) ) {
			continue;
		}
		$nested = '';
		$text   = '';
		foreach ( $item->childNodes as $child ) {
			if ( $child instanceof DOMElement && in_array( strtolower( $child->tagName ), array( 'ul', 'ol' ), true ) ) {
				$nested .= "\n" . cb_dom_list_to_markdown( $child, 'ol' === strtolower( $child->tagName ), $depth + 1 );
			} else {
				$text .= cb_dom_node_to_markdown( $child );
			}
		}
		$prefix = str_repeat( '  ', $depth ) . ( $ordered ? ( $i++ . '. ' ) : '- ' );
		$out   .= $prefix . trim( preg_replace( '/\s+/', ' ', $text ) ) . "\n" . $nested;
	}
	return $out;
}

/**
 * Render a <table> to a GitHub-flavoured markdown table.
 *
 * @param DOMElement $table Table element.
 * @return string
 */
function cb_dom_table_to_markdown( DOMElement $table ) {
	$xpath = new DOMXPath( $table->ownerDocument );
	$rows  = $xpath->query( './/tr', $table );
	if ( ! $rows || ! $rows->length ) {
		return '';
	}

	$grid = array();
	foreach ( $rows as $row ) {
		$cells = array();
		foreach ( $row->childNodes as $cell ) {
			if ( $cell instanceof DOMElement && in_array( strtolower( $cell->tagName ), array( 'td', 'th' ), true ) ) {
				$cells[] = trim( preg_replace( '/\s+/', ' ', cb_dom_children_to_markdown( $cell ) ) );
			}
		}
		if ( $cells ) {
			$grid[] = $cells;
		}
	}
	if ( ! $grid ) {
		return '';
	}

	$cols  = max( array_map( 'count', $grid ) );
	$lines = array();
	foreach ( $grid as $i => $cells ) {
		$cells   = array_pad( $cells, $cols, '' );
		$lines[] = '| ' . implode( ' | ', array_map( static fn( $c ) => str_replace( '|', '\\|', $c ), $cells ) ) . ' |';
		if ( 0 === $i ) {
			$lines[] = '| ' . implode( ' | ', array_fill( 0, $cols, '---' ) ) . ' |';
		}
	}
	return implode( "\n", $lines ) . "\n";
}

/**
 * Resolve a possibly-relative href/src against the site URL.
 *
 * @param string $url URL from the markup.
 * @return string
 */
function cb_markdown_absolute_url( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url || preg_match( '#^([a-z][a-z0-9+.\-]*:|//)#i', $url ) ) {
		return $url;
	}
	return home_url( '/' . ltrim( $url, '/' ) );
}

/**
 * Rough token-count estimate (~4 characters/token) for the X-Markdown-Tokens
 * header — good enough for an agent to budget context, not a real tokenizer.
 *
 * @param string $text Markdown text.
 * @return int
 */
function cb_markdown_token_estimate( $text ) {
	return (int) max( 1, ceil( strlen( (string) $text ) / 4 ) );
}
