<?php
/**
 * Reusable branded HTML email wrapper.
 *
 * Every automated Chatbotistic email (welcome, purchase receipt, contact-form
 * auto-reply, newsletter welcome, license activation, etc.) renders its body
 * through Email_Template::render() so customers always see the same
 * email-client-safe, table-based, inline-CSS layout with the WhatsApp-green
 * brand accent — regardless of which class in this plugin builds the copy.
 *
 * @package Chatbotistic\Profile
 */

namespace Chatbotistic\Profile;

defined( 'ABSPATH' ) || exit;

class Email_Template {

	/** WhatsApp green brand accent — buttons + links. */
	const BRAND_GREEN = '#25D366';

	/** Logo shown at the top of every email. */
	const LOGO_URL = 'https://www.chatbotistic.com/wp-content/uploads/2026/02/cropped.png';

	/** Support mailbox surfaced in every footer. */
	const SUPPORT_EMAIL = 'hello@chatbotistic.com';

	/**
	 * Render a complete, email-client-safe HTML document.
	 *
	 * @param array $args {
	 *     @type string $title       Heading shown at the top of the body (escaped).
	 *     @type string $preheader   Hidden preview text shown in inbox lists (escaped).
	 *     @type string $body_html   Raw HTML block (paragraphs/lists/etc) built by the caller — output as-is, not escaped.
	 *     @type string $cta_label   Optional CTA button label (escaped).
	 *     @type string $cta_url     Optional CTA button URL.
	 *     @type string $footer_note Optional extra footer line, e.g. an unsubscribe hint (allows basic HTML).
	 * }
	 * @return string Full HTML document.
	 */
	public static function render( array $args ): string {
		$title       = (string) ( $args['title'] ?? '' );
		$preheader   = (string) ( $args['preheader'] ?? '' );
		$body_html   = (string) ( $args['body_html'] ?? '' );
		$cta_label   = (string) ( $args['cta_label'] ?? '' );
		$cta_url     = (string) ( $args['cta_url'] ?? '' );
		$footer_note = (string) ( $args['footer_note'] ?? '' );

		$green = self::BRAND_GREEN;
		$year  = gmdate( 'Y' );

		$cta_html = '';
		if ( '' !== $cta_label && '' !== $cta_url ) {
			$cta_html = '
				<tr>
					<td style="padding:8px 40px 32px 40px;" align="left">
						<table role="presentation" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td style="border-radius:6px;background-color:' . $green . ';" bgcolor="' . $green . '">
									<a href="' . esc_url( $cta_url ) . '" target="_blank" rel="noopener" style="display:inline-block;padding:14px 28px;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:bold;color:#ffffff;text-decoration:none;border-radius:6px;">' . esc_html( $cta_label ) . '</a>
								</td>
							</tr>
						</table>
					</td>
				</tr>';
		}

		$footer_note_html = '';
		if ( '' !== $footer_note ) {
			$footer_note_html = '<p style="margin:0 0 12px 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:18px;color:#8a8f98;">' . wp_kses_post( $footer_note ) . '</p>';
		}

		ob_start();
		?>
<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="x-apple-disable-message-reformatting">
<title><?php echo esc_html( $title ); ?></title>
</head>
<body style="margin:0;padding:0;background-color:#f2f4f5;">
<div style="display:none;max-height:0;overflow:hidden;font-size:1px;line-height:1px;color:#f2f4f5;mso-hide:all;"><?php echo esc_html( $preheader ); ?></div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f2f4f5;">
<tr>
<td align="center" style="padding:32px 16px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:8px;overflow:hidden;">
<tr>
<td style="padding:32px 40px 16px 40px;" align="left">
<img src="<?php echo esc_url( self::LOGO_URL ); ?>" alt="Chatbotistic" style="height:36px;border:0;display:block;">
</td>
</tr>
<tr>
<td style="padding:8px 40px 0 40px;" align="left">
<h1 style="margin:0 0 16px 0;font-family:Arial,Helvetica,sans-serif;font-size:20px;line-height:28px;color:#111827;"><?php echo esc_html( $title ); ?></h1>
<div style="font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:23px;color:#374151;">
<?php echo $body_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- caller-built HTML block. ?>
</div>
</td>
</tr>
<?php echo $cta_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above from escaped parts. ?>
<tr>
<td style="padding:0 40px;">
<hr style="border:none;border-top:1px solid #e5e7eb;margin:8px 0 24px 0;">
</td>
</tr>
<tr>
<td style="padding:0 40px 32px 40px;" align="left">
<?php echo $footer_note_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above via wp_kses_post. ?>
<p style="margin:0 0 12px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:20px;color:#6b7280;">
<?php esc_html_e( 'Need a hand?', 'chatbotistic-profile' ); ?> <a href="mailto:<?php echo esc_attr( self::SUPPORT_EMAIL ); ?>" style="color:<?php echo esc_attr( $green ); ?>;text-decoration:none;"><?php echo esc_html( self::SUPPORT_EMAIL ); ?></a>
</p>
<p style="margin:0 0 16px 0;">
<a href="https://facebook.com/wordpressistic" style="color:#6b7280;text-decoration:none;font-family:Arial,Helvetica,sans-serif;font-size:12px;margin-right:12px;">Facebook</a>
<a href="https://linkedin.com/company/wordpressistic" style="color:#6b7280;text-decoration:none;font-family:Arial,Helvetica,sans-serif;font-size:12px;margin-right:12px;">LinkedIn</a>
<a href="https://twitter.com/wordpressistic" style="color:#6b7280;text-decoration:none;font-family:Arial,Helvetica,sans-serif;font-size:12px;margin-right:12px;">Twitter</a>
<a href="https://instagram.com/wordpressistic" style="color:#6b7280;text-decoration:none;font-family:Arial,Helvetica,sans-serif;font-size:12px;">Instagram</a>
</p>
<p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:16px;color:#9ca3af;">
&copy; <?php echo esc_html( $year ); ?> Chatbotistic. <?php esc_html_e( 'All rights reserved.', 'chatbotistic-profile' ); ?>
</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Standard wp_mail() headers for a branded HTML send.
	 *
	 * @return array<int,string>
	 */
	public static function mail_headers(): array {
		$from_name  = defined( 'CBP_FROM_NAME' ) ? CBP_FROM_NAME : 'Chatbotistic';
		$from_email = defined( 'CBP_FROM_EMAIL' ) ? CBP_FROM_EMAIL : 'hello@chatbotistic.com';

		/**
		 * Filter the From header used by every branded HTML email.
		 *
		 * @param string $from "Name <email@example.com>" formatted From header value.
		 */
		$from = (string) apply_filters( 'cb_email_from', sprintf( '%s <%s>', $from_name, $from_email ) );

		return array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $from,
		);
	}
}
