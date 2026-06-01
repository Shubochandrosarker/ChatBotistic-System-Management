<?php
/**
 * AI Analysis class for Insightistic Pro.
 * Supports OpenAI, Google Gemini, OpenRouter, and Anthropic Claude.
 *
 * @package Insightistic_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Insightistic_AI
 */
class Insightistic_AI {

	/**
	 * Register AJAX hook.
	 */
	public function init() {
		add_action( 'wp_ajax_insightistic_ai_analyze', array( $this, 'ajax_analyze' ) );
	}

	/**
	 * AJAX handler: run AI analysis.
	 */
	public function ajax_analyze() {
		check_ajax_referer( 'insightistic_pro_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'insightistic' ) );
		}

		if ( ! get_option( 'insightistic_pro_ai_enabled', 0 ) ) {
			wp_send_json_error( __( 'AI analysis is disabled. Enable it in Settings.', 'insightistic' ) );
		}

		$provider = get_option( 'insightistic_pro_ai_provider', 'none' );
		if ( 'none' === $provider ) {
			wp_send_json_error( __( 'No AI provider selected. Please configure one in Settings.', 'insightistic' ) );
		}

		$raw_data = wp_unslash( $_POST['data'] ?? '' );
		$data     = json_decode( $raw_data, true );
		$days     = intval( $_POST['days'] ?? 28 );

		if ( empty( $data ) ) {
			wp_send_json_error( __( 'No analytics data to analyse.', 'insightistic' ) );
		}

		$prompt = $this->build_prompt( $data, $days );

		switch ( $provider ) {
			case 'openai':
				$result = $this->call_openai( $prompt );
				break;
			case 'gemini':
				$result = $this->call_gemini( $prompt );
				break;
			case 'openrouter':
				$result = $this->call_openrouter( $prompt );
				break;
			case 'claude':
				$result = $this->call_claude( $prompt );
				break;
			default:
				wp_send_json_error( __( 'Unknown AI provider.', 'insightistic' ) );
				return;
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		$parsed = json_decode( $result, true );
		if ( ! is_array( $parsed ) ) {
			wp_send_json_error( __( 'AI returned an unreadable response. Please try again.', 'insightistic' ) );
		}

		wp_send_json_success( array( 'html' => $this->render_insights( $parsed, $provider ) ) );
	}

	/* ------------------------------------------------------------------ */
	/* Provider API Calls                                                   */
	/* ------------------------------------------------------------------ */

	/**
	 * Call OpenAI Chat Completions API.
	 *
	 * @param string $prompt User prompt.
	 * @return string|WP_Error JSON content string.
	 */
	private function call_openai( $prompt ) {
		$key = $this->get_key( 'insightistic_pro_openai_key' );
		if ( is_wp_error( $key ) ) {
			return $key;
		}

		$model = get_option( 'insightistic_pro_openai_model', 'gpt-4o-mini' );

		$response = wp_remote_post(
			'https://api.openai.com/v1/chat/completions',
			array(
				'timeout' => 45,
				'headers' => array(
					'Authorization' => 'Bearer ' . $key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( array(
					'model'           => $model,
					'messages'        => array(
						array( 'role' => 'system', 'content' => $this->system_prompt() ),
						array( 'role' => 'user',   'content' => $prompt ),
					),
					'temperature'     => 0.5,
					'max_tokens'      => 2000,
					'response_format' => array( 'type' => 'json_object' ),
				) ),
			)
		);

		return $this->extract_openai_content( $response );
	}

	/**
	 * Call Google Gemini API.
	 *
	 * @param string $prompt User prompt.
	 * @return string|WP_Error JSON content string.
	 */
	private function call_gemini( $prompt ) {
		$key = $this->get_key( 'insightistic_pro_gemini_key' );
		if ( is_wp_error( $key ) ) {
			return $key;
		}

		$model    = get_option( 'insightistic_pro_gemini_model', 'gemini-1.5-flash' );
		$endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . rawurlencode( $key );

		$full_prompt = $this->system_prompt() . "\n\n" . $prompt;

		$response = wp_remote_post(
			$endpoint,
			array(
				'timeout' => 45,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( array(
					'contents'         => array(
						array( 'parts' => array( array( 'text' => $full_prompt ) ) ),
					),
					'generationConfig' => array(
						'temperature'     => 0.5,
						'maxOutputTokens' => 2000,
						'responseMimeType' => 'application/json',
					),
				) ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			return new WP_Error( 'gemini_error', $body['error']['message'] );
		}

		$text = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
		if ( ! $text ) {
			return new WP_Error( 'gemini_empty', __( 'Gemini returned an empty response.', 'insightistic' ) );
		}

		// Strip possible markdown fences.
		$text = preg_replace( '/^```json\s*/i', '', $text );
		$text = preg_replace( '/```\s*$/', '', $text );
		return trim( $text );
	}

	/**
	 * Call OpenRouter API (OpenAI-compatible).
	 *
	 * @param string $prompt User prompt.
	 * @return string|WP_Error JSON content string.
	 */
	private function call_openrouter( $prompt ) {
		$key = $this->get_key( 'insightistic_pro_openrouter_key' );
		if ( is_wp_error( $key ) ) {
			return $key;
		}

		$model = get_option( 'insightistic_pro_openrouter_model', 'mistralai/mistral-7b-instruct:free' );

		// NOTE: response_format is intentionally omitted here.
		// Most free OpenRouter models do not support JSON mode (response_format).
		// JSON output is enforced via the system prompt and user prompt instead.
		$response = wp_remote_post(
			'https://openrouter.ai/api/v1/chat/completions',
			array(
				'timeout' => 45,
				'headers' => array(
					'Authorization' => 'Bearer ' . $key,
					'Content-Type'  => 'application/json',
					'HTTP-Referer'  => home_url(),
					'X-Title'       => 'Insightistic Analytics',
				),
				'body'    => wp_json_encode( array(
					'model'       => $model,
					'messages'    => array(
						array( 'role' => 'system', 'content' => $this->system_prompt() ),
						array( 'role' => 'user',   'content' => $prompt ),
					),
					'temperature' => 0.3,
					'max_tokens'  => 2000,
				) ),
			)
		);

		return $this->extract_openai_content( $response );
	}

	/**
	 * Call Anthropic Claude Messages API.
	 *
	 * @param string $prompt User prompt.
	 * @return string|WP_Error JSON content string.
	 */
	private function call_claude( $prompt ) {
		$key = $this->get_key( 'insightistic_pro_claude_key' );
		if ( is_wp_error( $key ) ) {
			return $key;
		}

		$model = get_option( 'insightistic_pro_claude_model', 'claude-haiku-4-5-20251001' );

		$full_prompt = $prompt . "\n\nIMPORTANT: Respond ONLY with valid JSON. Do not include any text outside the JSON object.";

		$response = wp_remote_post(
			'https://api.anthropic.com/v1/messages',
			array(
				'timeout' => 45,
				'headers' => array(
					'x-api-key'         => $key,
					'anthropic-version' => '2023-06-01',
					'Content-Type'      => 'application/json',
				),
				'body'    => wp_json_encode( array(
					'model'      => $model,
					'system'     => $this->system_prompt(),
					'messages'   => array(
						array( 'role' => 'user', 'content' => $full_prompt ),
					),
					'max_tokens' => 2000,
				) ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			return new WP_Error( 'claude_error', $body['error']['message'] );
		}

		$text = $body['content'][0]['text'] ?? '';
		if ( ! $text ) {
			return new WP_Error( 'claude_empty', __( 'Claude returned an empty response.', 'insightistic' ) );
		}

		$text = preg_replace( '/^```json\s*/i', '', $text );
		$text = preg_replace( '/```\s*$/', '', $text );
		return trim( $text );
	}

	/* ------------------------------------------------------------------ */
	/* Prompt Builders                                                      */
	/* ------------------------------------------------------------------ */

	/**
	 * System prompt sent to all AI providers.
	 */
	private function system_prompt() {
		return 'You are an expert digital marketing analyst specialising in Google Analytics, revenue attribution and conversion optimisation. '
			. 'Provide concise, data-driven, actionable insights. '
			. 'Always respond ONLY with a valid JSON object matching the requested schema. Do not include any text outside the JSON.';
	}

	/**
	 * Build the analysis prompt from structured GA4 data.
	 *
	 * @param array $data  Structured data from the GA4 class.
	 * @param int   $days  Number of days analysed.
	 * @return string
	 */
	private function build_prompt( $data, $days ) {
		$has_rev = isset( $data['totals']['revenue'] ) && $data['totals']['revenue'] > 0;

		$prompt  = "Analyse the following Google Analytics 4 data for the last {$days} days and return a JSON object.\n\n";
		$prompt .= "DATA:\n" . wp_json_encode( $data ) . "\n\n";
		$prompt .= "Return this exact JSON structure:\n";
		$prompt .= wp_json_encode( array(
			'overall_score'   => 'integer 0-100 reflecting marketing performance',
			'summary'         => '2-3 sentence executive summary',
			'key_insights'    => array(
				array(
					'title'       => 'Insight heading',
					'description' => 'Explanation',
					'impact'      => 'high|medium|low',
				),
			),
			'recommendations' => array(
				array(
					'title'           => 'Action heading',
					'description'     => 'Implementation detail',
					'priority'        => 'high|medium|low',
					'expected_impact' => 'Expected outcome',
					'effort'          => 'high|medium|low',
				),
			),
			'warnings'        => array(
				array(
					'issue'          => 'Problem',
					'severity'       => 'high|medium|low',
					'recommendation' => 'Fix',
				),
			),
		) );
		return $prompt;
	}

	/* ------------------------------------------------------------------ */
	/* Response Rendering                                                   */
	/* ------------------------------------------------------------------ */

	/**
	 * Render AI insights as HTML.
	 *
	 * @param array  $data     Parsed AI JSON response.
	 * @param string $provider Provider key.
	 * @return string HTML.
	 */
	private function render_insights( $data, $provider ) {
		$provider_labels = array(
			'openai'     => 'OpenAI',
			'gemini'     => 'Google Gemini',
			'openrouter' => 'OpenRouter',
			'claude'     => 'Anthropic Claude',
		);
		$provider_label = $provider_labels[ $provider ] ?? ucfirst( $provider );

		$score = intval( $data['overall_score'] ?? 0 );
		$score_class = $score >= 70 ? 'isp-score-high' : ( $score >= 40 ? 'isp-score-medium' : 'isp-score-low' );

		ob_start();
		?>
		<div class="isp-ai-panel">
			<div class="isp-ai-header">
				<div class="isp-ai-title">
					<span class="isp-ai-icon">✨</span>
					<?php esc_html_e( 'AI-Powered Insights', 'insightistic' ); ?>
					<span class="isp-ai-badge"><?php echo esc_html( $provider_label ); ?></span>
				</div>
				<?php if ( $score ) : ?>
				<div class="isp-score-badge <?php echo esc_attr( $score_class ); ?>">
					<span class="isp-score-number"><?php echo esc_html( $score ); ?></span>
					<span class="isp-score-label"><?php esc_html_e( '/ 100', 'insightistic' ); ?></span>
				</div>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $data['summary'] ) ) : ?>
			<div class="isp-ai-summary">
				<p><?php echo esc_html( $data['summary'] ); ?></p>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $data['key_insights'] ) ) : ?>
			<div class="isp-ai-section">
				<h3 class="isp-ai-section-title">💡 <?php esc_html_e( 'Key Insights', 'insightistic' ); ?></h3>
				<div class="isp-ai-cards">
					<?php foreach ( $data['key_insights'] as $insight ) : ?>
					<div class="isp-ai-card isp-impact-<?php echo esc_attr( $insight['impact'] ?? 'medium' ); ?>">
						<div class="isp-ai-card-impact"><?php echo esc_html( ucfirst( $insight['impact'] ?? 'medium' ) ); ?></div>
						<h4><?php echo esc_html( $insight['title'] ?? '' ); ?></h4>
						<p><?php echo esc_html( $insight['description'] ?? '' ); ?></p>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $data['recommendations'] ) ) : ?>
			<div class="isp-ai-section">
				<h3 class="isp-ai-section-title">📋 <?php esc_html_e( 'Recommendations', 'insightistic' ); ?></h3>
				<div class="isp-ai-recs">
					<?php foreach ( $data['recommendations'] as $rec ) : ?>
					<div class="isp-ai-rec isp-priority-<?php echo esc_attr( $rec['priority'] ?? 'medium' ); ?>">
						<div class="isp-rec-meta">
							<span class="isp-rec-priority"><?php echo esc_html( ucfirst( $rec['priority'] ?? 'medium' ) ); ?> <?php esc_html_e( 'Priority', 'insightistic' ); ?></span>
							<span class="isp-rec-effort"><?php esc_html_e( 'Effort:', 'insightistic' ); ?> <?php echo esc_html( ucfirst( $rec['effort'] ?? '' ) ); ?></span>
						</div>
						<h4><?php echo esc_html( $rec['title'] ?? '' ); ?></h4>
						<p><?php echo esc_html( $rec['description'] ?? '' ); ?></p>
						<?php if ( ! empty( $rec['expected_impact'] ) ) : ?>
						<p class="isp-rec-impact">📈 <?php echo esc_html( $rec['expected_impact'] ); ?></p>
						<?php endif; ?>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $data['warnings'] ) ) : ?>
			<div class="isp-ai-section">
				<h3 class="isp-ai-section-title">⚠️ <?php esc_html_e( 'Issues to Address', 'insightistic' ); ?></h3>
				<div class="isp-ai-recs">
					<?php foreach ( $data['warnings'] as $w ) : ?>
					<div class="isp-ai-rec isp-warning isp-priority-<?php echo esc_attr( $w['severity'] ?? 'medium' ); ?>">
						<div class="isp-rec-meta">
							<span class="isp-rec-priority"><?php echo esc_html( ucfirst( $w['severity'] ?? 'medium' ) ); ?> <?php esc_html_e( 'Severity', 'insightistic' ); ?></span>
						</div>
						<h4><?php echo esc_html( $w['issue'] ?? '' ); ?></h4>
						<p><?php echo esc_html( $w['recommendation'] ?? '' ); ?></p>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<div class="isp-ai-footer">
				<?php esc_html_e( 'AI analysis generated by', 'insightistic' ); ?> <?php echo esc_html( $provider_label ); ?> &bull; <a href="https://wordpressistic.com" target="_blank" rel="noopener noreferrer">Insightistic</a>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/* ------------------------------------------------------------------ */
	/* Helpers                                                              */
	/* ------------------------------------------------------------------ */

	/**
	 * Retrieve and decrypt a stored API key.
	 *
	 * @param string $option_name WP option name.
	 * @return string|WP_Error
	 */
	private function get_key( $option_name ) {
		$enc = get_option( $option_name );
		if ( ! $enc ) {
			return new WP_Error( 'no_key', __( 'API key not configured. Please add it in Settings → AI Insights.', 'insightistic' ) );
		}
		$key = Insightistic_Encryption::decrypt( $enc );
		if ( ! $key ) {
			return new WP_Error( 'bad_key', __( 'Failed to read the API key. Please re-save it in Settings.', 'insightistic' ) );
		}
		return $key;
	}

	/**
	 * Extract text content from an OpenAI-compatible API response.
	 *
	 * @param array|WP_Error $response wp_remote_post result.
	 * @return string|WP_Error
	 */
	private function extract_openai_content( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = wp_remote_retrieve_response_code( $response );
		if ( $status < 200 || $status >= 300 ) {
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			$error_msg = $body['error']['message'] ?? "API error: HTTP {$status}";
			return new WP_Error( 'api_error', $error_msg );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			return new WP_Error( 'api_error', $body['error']['message'] );
		}

		$text = $body['choices'][0]['message']['content'] ?? '';
		if ( ! $text ) {
			return new WP_Error( 'empty_response', __( 'The AI returned an empty response. Please try again.', 'insightistic' ) );
		}

		$text = preg_replace( '/^```json\s*/i', '', $text );
		$text = preg_replace( '/```\s*$/', '', $text );
		return trim( $text );
	}
}
