<?php
/**
 * Landing-page content — product modules and industry use cases.
 *
 * Each entry feeds template-parts/landing.php. Keep copy here so templates
 * stay logic-only and the content is translation-ready in one place.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return a single landing entry by slug, or null.
 *
 * @param string $slug Page slug.
 * @return array|null
 */
function cb_landing_get( $slug ) {
	$all = cb_landing_data();
	return $all[ $slug ] ?? null;
}

/**
 * Full landing dataset, keyed by page slug.
 *
 * @return array<string,array>
 */
function cb_landing_data() {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}

	$t = 'chatbotistic';

	$data = array(

		/* ===================== PRODUCTS ===================== */

		'whatsapp-automation' => array(
			'kind'    => 'product',
			'eyebrow' => __( 'WhatsApp Automation', $t ),
			'title'   => __( 'Automate WhatsApp — the channel your buyers actually reply on', $t ),
			'lead'    => __( 'WhatsApp messages get opened within minutes, not days. Chatbotistic puts an AI agent on your number that captures leads, answers instantly, routes to the right person, and never sleeps.', $t ),
			'stats'   => array(
				array( '98%', __( 'of WhatsApp messages are opened', $t ) ),
				array( '< 1 min', __( 'average first reply, fully automated', $t ) ),
				array( '24/7', __( 'coverage with no extra headcount', $t ) ),
			),
			'problems_title' => __( 'Why click-to-chat alone loses you money', $t ),
			'problems' => array(
				array( __( 'The lead leaves with no trace', $t ), __( 'A plain click-to-chat button opens WhatsApp and forgets the visitor. No name, no source, no record — if they never message, you never knew they were there.', $t ) ),
				array( __( 'Replies come too late', $t ), __( 'By the time someone on your team sees the message, the buyer has already messaged two competitors. Speed is the whole game.', $t ) ),
				array( __( 'Everything is manual', $t ), __( 'Routing, qualifying, follow-ups, business hours — all handled by a human copy-pasting between chats and a spreadsheet.', $t ) ),
			),
			'solution' => array(
				'heading' => __( 'Capture the lead before WhatsApp even opens', $t ),
				'lead'    => __( 'Chatbotistic collects name, number and intent in a pre-chat step, then hands a fully-qualified conversation to your AI agent or your team.', $t ),
				'items'   => array(
					array( 'form', __( 'Pre-chat lead capture', $t ), __( 'Grab name, phone and the reason for contact before the chat opens — every visitor becomes a tracked lead.', $t ) ),
					array( 'ai', __( 'Instant AI replies', $t ), __( 'Your agent answers FAQs, shares pricing, and qualifies budget in seconds, in your tone of voice.', $t ) ),
					array( 'users', __( 'Department routing', $t ), __( 'Sales, support and billing each get the right conversations automatically — no manual triage.', $t ) ),
					array( 'cal', __( 'Business hours logic', $t ), __( 'Different flows for open hours, after hours and holidays so buyers always get a useful reply.', $t ) ),
				),
			),
			'features' => array(
				'heading' => __( 'Everything in the WhatsApp module', $t ),
				'items'   => array(
					array( 'wa', __( 'WhatsApp widget', $t ), __( 'A polished click-to-chat button with a branded pre-chat experience on every page.', $t ) ),
					array( 'bolt', __( 'Auto-reply templates', $t ), __( 'Reusable replies with variables, delays and follow-up rules.', $t ) ),
					array( 'inbox', __( 'CRM capture', $t ), __( 'Every chat lands as a structured lead with full context and history.', $t ) ),
					array( 'chat', __( 'Broadcasts', $t ), __( 'Send approved template messages to opted-in contacts at scale.', $t ) ),
					array( 'cal', __( 'Booking in chat', $t ), __( 'Drop a calendar link straight into the conversation flow.', $t ) ),
					array( 'chart', __( 'Conversation analytics', $t ), __( 'Track volume, response time and conversion by agent and channel.', $t ) ),
				),
			),
			'why' => array(
				array( 'spark', __( 'Live in minutes', $t ), __( 'Connect a number, pick a template, embed the widget — no developer, no long onboarding.', $t ) ),
				array( 'shield', __( 'Compliant by design', $t ), __( 'Opt-in capture and consent handling built around WhatsApp Business policy.', $t ) ),
				array( 'tag', __( 'Honest pricing', $t ), __( 'A fraction of enterprise messaging suites, with no per-seat tax on your team.', $t ) ),
			),
			'faqs' => array(
				array( 'q' => __( 'Do I need the WhatsApp Business API?', $t ), 'a' => __( 'For click-to-chat widgets, no. For the AI agent that sends and receives messages automatically, you connect a WhatsApp Business API number — we guide you through it, or provision one for you on higher plans.', $t ) ),
				array( 'q' => __( 'Will the AI sound like a robot?', $t ), 'a' => __( 'No. You set the tone, name and personality, and train the agent on your own content so replies sound like your brand.', $t ) ),
				array( 'q' => __( 'Can a human take over a chat?', $t ), 'a' => __( 'Yes. Any conversation can be escalated to your team in the shared inbox with full history preserved.', $t ) ),
				array( 'q' => __( 'Does it work on mobile?', $t ), 'a' => __( 'Yes — the widget and pre-chat flow are mobile-first, which matters since most WhatsApp buyers never leave their phone.', $t ) ),
			),
			'related' => array(
				array( __( 'AI Chatbot', $t ), '', 'ai', __( 'On-site AI agent', $t ) ),
				array( __( 'Booking Forms', $t ), '', 'cal', __( 'Book inside chat', $t ) ),
				array( __( 'Local businesses', $t ), '', 'store', __( 'Use case', $t ) ),
				array( __( 'Pricing', $t ), '', 'tag', __( 'See plans', $t ) ),
			),
			'cta' => array(
				'title' => __( 'Turn WhatsApp into your highest-converting channel', $t ),
				'sub'   => __( 'Connect your number and go live today. No credit card required to start.', $t ),
			),
		),

		'ai-chatbot' => array(
			'kind'    => 'product',
			'eyebrow' => __( 'AI Chatbot', $t ),
			'title'   => __( 'An AI agent that knows your business — and sells like it', $t ),
			'lead'    => __( 'Train Chatbotistic on your site, docs and services. It answers buyer questions accurately, qualifies intent, recommends the right offer and books the call — 24 hours a day.', $t ),
			'stats'   => array(
				array( '80%', __( 'of repeat questions resolved without a human', $t ) ),
				array( '3×', __( 'more leads captured vs. a contact form', $t ) ),
				array( '5 min', __( 'to train and launch your first agent', $t ) ),
			),
			'problems_title' => __( 'Why most website visitors leave silently', $t ),
			'problems' => array(
				array( __( 'Nobody fills long forms', $t ), __( 'A static contact form asks for everything up front and gives nothing back. Most visitors simply close the tab.', $t ) ),
				array( __( 'Answers arrive hours later', $t ), __( 'Buyers want to know pricing, scope and availability now — not after an email round-trip tomorrow.', $t ) ),
				array( __( 'Your best content is buried', $t ), __( 'The answers exist in your docs and pages, but visitors will not dig for them. They need it handed over in conversation.', $t ) ),
			),
			'solution' => array(
				'heading' => __( 'More than answers — outcomes', $t ),
				'lead'    => __( 'Point the agent at your content and within minutes it explains your services, qualifies leads, books calls and escalates the complex cases to a human.', $t ),
				'items'   => array(
					array( 'ai', __( 'Trained on your content', $t ), __( 'Feed it your site, FAQs and documents — it learns your offer and answers in your voice.', $t ) ),
					array( 'form', __( 'Lead qualification', $t ), __( 'Branching logic that scores intent and budget before notifying your team.', $t ) ),
					array( 'cal', __( 'Booking guidance', $t ), __( 'Move qualified buyers straight into a calendar slot inside the conversation.', $t ) ),
					array( 'inbox', __( 'Human handoff', $t ), __( 'Seamless escalation to a live agent with the full conversation context attached.', $t ) ),
				),
			),
			'features' => array(
				'heading' => __( 'Built for serious conversation work', $t ),
				'items'   => array(
					array( 'ai', __( 'AI website assistant', $t ), __( 'Answers in your tone of voice, grounded in your real content.', $t ) ),
					array( 'chat', __( 'FAQ automation', $t ), __( 'Resolves the repetitive 80% before it ever reaches a person.', $t ) ),
					array( 'tag', __( 'Service recommendations', $t ), __( 'Suggests the right package based on the buyer’s stated needs.', $t ) ),
					array( 'mail', __( 'Contact collection', $t ), __( 'Captures email, phone and consent inside the chat.', $t ) ),
					array( 'globe', __( 'Multi-language', $t ), __( 'Detects the buyer’s language and replies natively.', $t ) ),
					array( 'page', __( 'Knowledge-base answers', $t ), __( 'Long-form responses with citations back to your help docs.', $t ) ),
				),
			),
			'why' => array(
				array( 'spark', __( 'Accurate, not generic', $t ), __( 'Grounded in your content, so it never invents pricing or promises you can’t keep.', $t ) ),
				array( 'gear', __( 'No-code builder', $t ), __( 'A visual flow editor with AI fallback — anyone on your team can maintain it.', $t ) ),
				array( 'shield', __( 'Your data stays yours', $t ), __( 'Conversations are encrypted, never sold, and exportable or deletable any time.', $t ) ),
			),
			'faqs' => array(
				array( 'q' => __( 'How does the agent learn my business?', $t ), 'a' => __( 'You point it at your website URL, upload documents, or paste FAQs. It indexes that content and answers strictly from it.', $t ) ),
				array( 'q' => __( 'Can it book appointments?', $t ), 'a' => __( 'Yes — the AI chatbot works with the Booking module to offer calendar slots and confirm appointments inside the chat.', $t ) ),
				array( 'q' => __( 'What happens when it does not know an answer?', $t ), 'a' => __( 'It captures the question and contact details, then escalates to your team instead of guessing.', $t ) ),
				array( 'q' => __( 'Can I use it on a non-WordPress site?', $t ), 'a' => __( 'Yes. Embed it with a one-line snippet on any platform, or use the native WordPress plugin.', $t ) ),
			),
			'related' => array(
				array( __( 'WhatsApp Automation', $t ), '', 'wa', __( 'Automate WhatsApp', $t ) ),
				array( __( 'WordPress Plugin', $t ), '', 'wp', __( 'Native install', $t ) ),
				array( __( 'SaaS founders', $t ), '', 'rocket', __( 'Use case', $t ) ),
				array( __( 'Features', $t ), '', 'spark', __( 'All modules', $t ) ),
			),
			'cta' => array(
				'title' => __( 'Deploy your AI assistant in under five minutes', $t ),
				'sub'   => __( 'Train it on your content and watch it qualify leads while you sleep.', $t ),
			),
		),

		'booking-forms' => array(
			'kind'    => 'product',
			'eyebrow' => __( 'Booking Forms', $t ),
			'title'   => __( 'Turn conversations into booked, paid appointments', $t ),
			'lead'    => __( 'The fastest path from website visitor to confirmed appointment. Calendar-synced booking with intake questions and deposit collection — built for service businesses, clinics and consultants.', $t ),
			'stats'   => array(
				array( '30 sec', __( 'from visitor to confirmed booking', $t ) ),
				array( '−40%', __( 'no-shows with automatic reminders', $t ) ),
				array( '2×', __( 'booking rate vs. a calendar link alone', $t ) ),
			),
			'problems_title' => __( 'Why a plain calendar link is not enough', $t ),
			'problems' => array(
				array( __( 'Friction kills the booking', $t ), __( 'Sending people off to a separate scheduling tool loses the ones who are not already sold.', $t ) ),
				array( __( 'No-shows eat your day', $t ), __( 'Without reminders and a small commitment, a chunk of every calendar simply does not turn up.', $t ) ),
				array( __( 'Intake happens twice', $t ), __( 'You book the slot, then chase the customer separately for the details you needed all along.', $t ) ),
			),
			'solution' => array(
				'heading' => __( 'Book, qualify and collect — in one flow', $t ),
				'lead'    => __( 'The visitor picks a slot, answers your intake questions, optionally pays a deposit, and lands as a confirmed appointment in your calendar and CRM.', $t ),
				'items'   => array(
					array( 'cal', __( 'In-chat scheduling', $t ), __( 'Pick a time without ever leaving the conversation or the page.', $t ) ),
					array( 'form', __( 'Smart intake', $t ), __( 'Collect exactly the details you need before the appointment, with conditional questions.', $t ) ),
					array( 'card', __( 'Deposit collection', $t ), __( 'Take a Stripe or PayPal deposit to lock in commitment and cut no-shows.', $t ) ),
					array( 'mail', __( 'Automated reminders', $t ), __( 'Confirmations, reminders and reschedule links sent without you lifting a finger.', $t ) ),
				),
			),
			'features' => array(
				'heading' => __( 'Everything booking-related, in one place', $t ),
				'items'   => array(
					array( 'cal', __( 'Consultation booking', $t ), __( 'Free or paid consultations with calendar sync.', $t ) ),
					array( 'users', __( 'Multi-staff scheduling', $t ), __( 'Route bookings across team members with capacity rules.', $t ) ),
					array( 'rocket', __( 'Strategy-call qualifier', $t ), __( 'A qualifying flow before the slot picker — only good leads get through.', $t ) ),
					array( 'card', __( 'Payments in-flow', $t ), __( 'Deposits, full payment or recurring, collected during booking.', $t ) ),
					array( 'inbox', __( 'Lead storage', $t ), __( 'Every booking becomes a contact in your CRM-style inbox.', $t ) ),
					array( 'chart', __( 'Booking dashboard', $t ), __( 'Today’s schedule, no-shows, revenue and team utilisation at a glance.', $t ) ),
				),
			),
			'why' => array(
				array( 'bolt', __( 'Zero-friction flow', $t ), __( 'Booking happens in chat, so you keep the momentum you worked to build.', $t ) ),
				array( 'card', __( 'Built-in payments', $t ), __( 'Stripe and PayPal native — no extra plugin, no extra fee from us.', $t ) ),
				array( 'plug', __( 'Syncs with your stack', $t ), __( 'Calendars, CRMs and Google Sheets connect out of the box.', $t ) ),
			),
			'faqs' => array(
				array( 'q' => __( 'Which calendars does it sync with?', $t ), 'a' => __( 'Google Calendar and other major calendars sync two-way so availability stays accurate everywhere.', $t ) ),
				array( 'q' => __( 'Can I take payment at booking?', $t ), 'a' => __( 'Yes. Collect a deposit or full payment with Stripe or PayPal inside the booking flow.', $t ) ),
				array( 'q' => __( 'Can different services have different intake questions?', $t ), 'a' => __( 'Yes — each service can have its own questions, duration, staff and pricing.', $t ) ),
				array( 'q' => __( 'Does it reduce no-shows?', $t ), 'a' => __( 'Automatic reminders plus an optional deposit typically cut no-shows sharply compared with a bare calendar link.', $t ) ),
			),
			'related' => array(
				array( __( 'AI Chatbot', $t ), '', 'ai', __( 'Guide buyers to book', $t ) ),
				array( __( 'Clinics & spas', $t ), '', 'cal', __( 'Use case', $t ) ),
				array( __( 'Coaches', $t ), '', 'rocket', __( 'Use case', $t ) ),
				array( __( 'Pricing', $t ), '', 'tag', __( 'See plans', $t ) ),
			),
			'cta' => array(
				'title' => __( 'Fill your calendar without lifting a finger', $t ),
				'sub'   => __( 'Launch a booking flow today and let qualified appointments roll in.', $t ),
			),
		),

		'wordpress-plugin' => array(
			'kind'    => 'product',
			'eyebrow' => __( 'WordPress Plugin', $t ),
			'title'   => __( 'Native WordPress. Zero code. Full power.', $t ),
			'lead'    => __( 'Install the Chatbotistic plugin, paste your license key, and manage every chatbot, WhatsApp widget and booking flow straight from your WordPress admin.', $t ),
			'stats'   => array(
				array( '1-click', __( 'install from your WordPress dashboard', $t ) ),
				array( 'Shortcode', __( 'or block — no theme edits needed', $t ) ),
				array( 'PHP 8+', __( 'lightweight, secure and theme-friendly', $t ) ),
			),
			'problems_title' => __( 'Why bolted-on chat scripts cause pain', $t ),
			'problems' => array(
				array( __( 'Snippets get lost', $t ), __( 'Pasting raw scripts into theme files breaks on every theme update and is impossible to audit.', $t ) ),
				array( __( 'Two dashboards, no sync', $t ), __( 'Managing chat in one tool and your site in another means leads and content drift apart.', $t ) ),
				array( __( 'Performance hit', $t ), __( 'Heavy third-party widgets drag down the Core Web Vitals you worked hard to earn.', $t ) ),
			),
			'solution' => array(
				'heading' => __( 'A WordPress plugin built by WordPress people', $t ),
				'lead'    => __( 'Chatbotistic is part of the WordPressistic ecosystem — the plugin is lightweight, secure and respects your theme.', $t ),
				'items'   => array(
					array( 'plug', __( 'One-click install', $t ), __( 'Add it from your dashboard like any plugin, activate, and you are live.', $t ) ),
					array( 'key', __( 'License activation', $t ), __( 'Paste your key to sync widgets, inbox and analytics automatically.', $t ) ),
					array( 'code', __( 'Shortcode & block embed', $t ), __( 'Drop a widget anywhere with a shortcode or Gutenberg block — no theme edits.', $t ) ),
					array( 'inbox', __( 'Leads sync into WordPress', $t ), __( 'Every conversation lands in WP as a contact or order you can act on.', $t ) ),
				),
			),
			'features' => array(
				'heading' => __( 'Every WordPress feature you need', $t ),
				'items'   => array(
					array( 'gear', __( 'Manage from WP admin', $t ), __( 'Edit chatbots, forms and booking flows without leaving WordPress.', $t ) ),
					array( 'page', __( 'Dashboard shortcode', $t ), __( 'Embed the analytics dashboard right inside the admin.', $t ) ),
					array( 'shield', __( 'Membership-aware', $t ), __( 'Bots can respond differently to logged-in members and visitors.', $t ) ),
					array( 'cart', __( 'WooCommerce ready', $t ), __( 'Order lookup, cart recovery and product recommendations out of the box.', $t ) ),
					array( 'chart', __( 'Native analytics', $t ), __( 'See conversations and leads without opening another tab.', $t ) ),
					array( 'bolt', __( 'Built for speed', $t ), __( 'Assets load deferred and lean so your Core Web Vitals stay green.', $t ) ),
				),
			),
			'why' => array(
				array( 'wp', __( 'WordPress-first', $t ), __( 'Designed for the platform, not ported to it as an afterthought.', $t ) ),
				array( 'shield', __( 'Secure & audited', $t ), __( 'Nonce-checked, capability-gated, and free of script-injection hacks.', $t ) ),
				array( 'spark', __( 'Always current', $t ), __( 'Updates ship through the normal plugin flow with per-file cache busting.', $t ) ),
			),
			'faqs' => array(
				array( 'q' => __( 'Does it slow my site down?', $t ), 'a' => __( 'No. The plugin loads its assets deferred and lean, and only on the pages where a widget is used.', $t ) ),
				array( 'q' => __( 'Will it survive theme updates?', $t ), 'a' => __( 'Yes — widgets are added via shortcode or block, never by editing theme files, so updates are safe.', $t ) ),
				array( 'q' => __( 'Is it WooCommerce compatible?', $t ), 'a' => __( 'Yes. The plugin supports order lookup, cart recovery and product recommendations for WooCommerce stores.', $t ) ),
				array( 'q' => __( 'How do I activate it?', $t ), 'a' => __( 'Install the free plugin, then paste the license key from your Chatbotistic account to unlock your widgets.', $t ) ),
			),
			'related' => array(
				array( __( 'WordPress sites', $t ), '', 'wp', __( 'Use case', $t ) ),
				array( __( 'AI Chatbot', $t ), '', 'ai', __( 'On-site agent', $t ) ),
				array( __( 'Agency & White Label', $t ), '', 'tag', __( 'Resell it', $t ) ),
				array( __( 'Documentation', $t ), '', 'page', __( 'Setup guide', $t ) ),
			),
			'cta' => array(
				'title' => __( 'The WordPress chatbot plugin built for serious businesses', $t ),
				'sub'   => __( 'Free to install. Activate with any Chatbotistic plan.', $t ),
			),
		),

		'agency-white-label' => array(
			'kind'    => 'product',
			'eyebrow' => __( 'Agency & White Label', $t ),
			'title'   => __( 'A premium AI platform you can sell as your own', $t ),
			'lead'    => __( 'Productize chatbots, WhatsApp and booking as a monthly service. Add recurring revenue under your own brand — without building a SaaS from scratch.', $t ),
			'stats'   => array(
				array( '$240+', __( 'typical monthly revenue per client', $t ) ),
				array( '< 30 min', __( 'to set up a new client widget', $t ) ),
				array( '100%', __( 'your brand — we stay invisible', $t ) ),
			),
			'problems_title' => __( 'Why building your own tool rarely pays off', $t ),
			'problems' => array(
				array( __( 'One-off scripts do not scale', $t ), __( 'Hand-building a chat script per client means no recurring revenue and endless maintenance.', $t ) ),
				array( __( 'SaaS is expensive to build', $t ), __( 'Engineering a real multi-tenant platform costs more time and money than most agencies can spare.', $t ) ),
				array( __( 'Reselling another brand is weak', $t ), __( 'Sending clients to a tool with someone else’s logo undercuts your positioning and your margin.', $t ) ),
			),
			'solution' => array(
				'heading' => __( 'Your brand, your clients, our engine', $t ),
				'lead'    => __( 'Run a profitable recurring service on top of Chatbotistic with full white label and isolated client workspaces.', $t ),
				'items'   => array(
					array( 'tag', __( 'Full white label', $t ), __( 'Your logo, your domain, your client login experience — Chatbotistic disappears.', $t ) ),
					array( 'users', __( 'Client workspaces', $t ), __( 'A separate workspace per client with isolated inbox, billing and roles.', $t ) ),
					array( 'card', __( 'Client billing', $t ), __( 'Set your own prices and bill clients directly — keep the margin.', $t ) ),
					array( 'inbox', __( 'Master dashboard', $t ), __( 'See every lead across every client from one control panel.', $t ) ),
				),
			),
			'features' => array(
				'heading' => __( 'Built for agencies that want to scale', $t ),
				'items'   => array(
					array( 'spark', __( 'Unlimited widgets', $t ), __( 'No per-widget pricing — build, launch and repeat.', $t ) ),
					array( 'gear', __( 'Custom branding', $t ), __( 'Theme colors, custom CSS and branded onboarding emails.', $t ) ),
					array( 'code', __( 'API integrations', $t ), __( 'Connect Chatbotistic into your existing agency stack.', $t ) ),
					array( 'wa', __( 'Dedicated numbers', $t ), __( 'Provision a WhatsApp Business number per client.', $t ) ),
					array( 'chart', __( 'Recurring revenue', $t ), __( 'Bill monthly, keep the margin, we power the platform.', $t ) ),
					array( 'shield', __( 'Partner support', $t ), __( 'Priority support and co-marketing for agency partners.', $t ) ),
				),
			),
			'why' => array(
				array( 'tag', __( 'Lower cost, higher margin', $t ), __( 'A fraction of enterprise platform pricing, so your markup is real profit.', $t ) ),
				array( 'spark', __( 'Launch this week', $t ), __( 'No build phase — brand it, onboard a client and start billing.', $t ) ),
				array( 'users', __( 'True multi-tenant', $t ), __( 'Each client is fully isolated, so data and billing never cross over.', $t ) ),
			),
			'faqs' => array(
				array( 'q' => __( 'How white-label is it really?', $t ), 'a' => __( 'On the Agency plan you get your logo and theme; the White Label plan adds your own domain and full reseller control of the client login.', $t ) ),
				array( 'q' => __( 'Can I set my own client pricing?', $t ), 'a' => __( 'Yes. You bill clients at whatever price you choose and keep the difference.', $t ) ),
				array( 'q' => __( 'Do clients see Chatbotistic anywhere?', $t ), 'a' => __( 'With full white label, no — the dashboard, widgets and emails all carry your brand.', $t ) ),
				array( 'q' => __( 'Is there a partner program?', $t ), 'a' => __( 'Yes — agency partners get volume pricing, priority support and co-marketing. Contact sales to discuss terms.', $t ) ),
			),
			'related' => array(
				array( __( 'Agencies', $t ), '', 'users', __( 'Use case', $t ) ),
				array( __( 'WordPress sites', $t ), '', 'wp', __( 'Use case', $t ) ),
				array( __( 'Affiliate Program', $t ), '', 'spark', __( 'Earn commission', $t ) ),
				array( __( 'Pricing', $t ), '', 'tag', __( 'Agency plans', $t ) ),
			),
			'cta' => array(
				'title' => __( 'Add Chatbotistic to your agency stack — and your invoices', $t ),
				'sub'   => __( 'Talk to us about partner terms and white-label onboarding.', $t ),
			),
		),

		/* ===================== USE CASES ===================== */

		'agencies' => array(
			'kind'    => 'usecase',
			'eyebrow' => __( 'For agencies', $t ),
			'title'   => __( 'Add a recurring revenue line your clients will thank you for', $t ),
			'lead'    => __( 'Marketing, web and WhatsApp agencies use Chatbotistic to productize AI chat, lead capture and booking — delivered under their own brand, billed monthly, managed from one dashboard.', $t ),
			'stats'   => array(
				array( '$240+', __( 'monthly revenue per client widget', $t ) ),
				array( '< 30 min', __( 'to onboard a new client', $t ) ),
				array( '1', __( 'dashboard for every client you manage', $t ) ),
			),
			'problems_title' => __( 'What slows agencies down today', $t ),
			'problems' => array(
				array( __( 'Project revenue is one-and-done', $t ), __( 'You deliver a site, invoice once, and the client relationship goes quiet until the next project.', $t ) ),
				array( __( 'Client leads are scattered', $t ), __( 'Every client uses a different inbox, form and spreadsheet, so reporting is a manual scramble.', $t ) ),
				array( __( 'Tooling carries someone else’s logo', $t ), __( 'Reselling a third-party chat tool weakens your brand and hands the relationship to a vendor.', $t ) ),
			),
			'solution' => array(
				'heading' => __( 'A productized service you fully own', $t ),
				'lead'    => __( 'Chatbotistic gives you a white-label platform with a workspace per client, so you can launch, manage and bill an AI chat service at scale.', $t ),
				'items'   => array(
					array( 'tag', __( 'White-label everything', $t ), __( 'Your logo, your domain, your client login — the platform is yours.', $t ) ),
					array( 'users', __( 'Workspace per client', $t ), __( 'Isolated inbox, billing and team roles for every account you run.', $t ) ),
					array( 'inbox', __( 'One master dashboard', $t ), __( 'See leads and performance across every client from a single screen.', $t ) ),
					array( 'card', __( 'Bill clients your way', $t ), __( 'Set your own prices and keep the recurring margin every month.', $t ) ),
				),
			),
			'features' => array(
				'heading' => __( 'What we provide your agency', $t ),
				'items'   => array(
					array( 'spark', __( 'Unlimited widgets', $t ), __( 'No per-widget fees — deploy across every client you take on.', $t ) ),
					array( 'wa', __( 'WhatsApp per client', $t ), __( 'A dedicated business number and flow for each account.', $t ) ),
					array( 'chart', __( 'Client-ready reports', $t ), __( 'Branded performance reports you can send without editing.', $t ) ),
					array( 'gear', __( 'Custom branding', $t ), __( 'Theme colors, CSS and onboarding emails in your identity.', $t ) ),
					array( 'code', __( 'API & webhooks', $t ), __( 'Wire Chatbotistic into the rest of your agency stack.', $t ) ),
					array( 'shield', __( 'Priority partner support', $t ), __( 'Faster help and co-marketing for agency partners.', $t ) ),
				),
			),
			'why' => array(
				array( 'spark', __( 'Recurring revenue, fast', $t ), __( 'No SaaS to build — brand it, onboard clients and start billing this week.', $t ) ),
				array( 'tag', __( 'Healthy margin', $t ), __( 'Honest platform pricing means your client markup is real profit.', $t ) ),
				array( 'users', __( 'Truly multi-tenant', $t ), __( 'Each client is isolated, so data and billing never cross.', $t ) ),
			),
			'faqs' => array(
				array( 'q' => __( 'Can I resell under my own brand?', $t ), 'a' => __( 'Yes. The Agency and White Label plans let you present the entire platform — dashboard, widgets and emails — as your own.', $t ) ),
				array( 'q' => __( 'How do I manage many clients?', $t ), 'a' => __( 'Each client gets an isolated workspace, and you oversee them all from one master dashboard.', $t ) ),
				array( 'q' => __( 'What do I charge clients?', $t ), 'a' => __( 'Whatever you like — you set client pricing and keep the margin over your plan cost.', $t ) ),
				array( 'q' => __( 'Is there onboarding help?', $t ), 'a' => __( 'Yes — agency partners get priority support and guided white-label setup.', $t ) ),
			),
			'related' => array(
				array( __( 'Agency & White Label', $t ), '', 'tag', __( 'Product', $t ) ),
				array( __( 'WordPress sites', $t ), '', 'wp', __( 'Use case', $t ) ),
				array( __( 'Affiliate Program', $t ), '', 'spark', __( 'Earn more', $t ) ),
				array( __( 'Pricing', $t ), '', 'card', __( 'Agency plans', $t ) ),
			),
			'cta' => array(
				'title' => __( 'Launch your white-label AI chat service', $t ),
				'sub'   => __( 'Talk to us about agency and partner terms.', $t ),
			),
		),

		'local-business' => array(
			'kind'    => 'usecase',
			'eyebrow' => __( 'For local businesses', $t ),
			'title'   => __( 'Never miss another enquiry — even after closing time', $t ),
			'lead'    => __( 'Restaurants, trades, studios and local services use Chatbotistic to answer questions, capture leads and book jobs 24/7, so a missed call is no longer a lost customer.', $t ),
			'stats'   => array(
				array( '24/7', __( 'enquiry capture, even after hours', $t ) ),
				array( '< 1 min', __( 'reply time on WhatsApp', $t ) ),
				array( '0', __( 'leads lost to a missed phone call', $t ) ),
			),
			'problems_title' => __( 'Why local businesses leak customers', $t ),
			'problems' => array(
				array( __( 'Phone tag loses the job', $t ), __( 'You are on a job, the phone rings out, and the caller simply rings the next business on the list.', $t ) ),
				array( __( 'After-hours enquiries vanish', $t ), __( 'Most people search in the evening — when nobody is there to answer.', $t ) ),
				array( __( 'No system for follow-up', $t ), __( 'Enquiries live in a mix of texts, voicemails and notes, so good leads go cold.', $t ) ),
			),
			'solution' => array(
				'heading' => __( 'An always-on front desk for your business', $t ),
				'lead'    => __( 'Chatbotistic answers common questions, captures every enquiry and books work — on your website and on WhatsApp — around the clock.', $t ),
				'items'   => array(
					array( 'chat', __( 'Answer instantly', $t ), __( 'Hours, pricing, location and availability answered the moment a visitor asks.', $t ) ),
					array( 'wa', __( 'WhatsApp capture', $t ), __( 'Let customers reach you on the app they already use, with auto-replies after hours.', $t ) ),
					array( 'cal', __( 'Book the job', $t ), __( 'Take appointments and quote requests straight from the conversation.', $t ) ),
					array( 'mail', __( 'Instant alerts', $t ), __( 'Get pinged the second a new enquiry lands so you can follow up fast.', $t ) ),
				),
			),
			'features' => array(
				'heading' => __( 'What we provide local businesses', $t ),
				'items'   => array(
					array( 'ai', __( 'Trained on your business', $t ), __( 'Answers about services, pricing and hours in your own words.', $t ) ),
					array( 'form', __( 'Quote request capture', $t ), __( 'Collect job details and contact info in a simple chat flow.', $t ) ),
					array( 'cal', __( 'After-hours booking', $t ), __( 'Customers self-book or request callbacks while you sleep.', $t ) ),
					array( 'inbox', __( 'One simple inbox', $t ), __( 'Every enquiry from every channel in one place.', $t ) ),
					array( 'phone', __( 'Click-to-call & chat', $t ), __( 'Make it effortless for mobile visitors to reach you.', $t ) ),
					array( 'chart', __( 'See what works', $t ), __( 'Track enquiries, sources and conversion without spreadsheets.', $t ) ),
				),
			),
			'why' => array(
				array( 'spark', __( 'Set up in an afternoon', $t ), __( 'No technical skills — answer a few questions and you are live.', $t ) ),
				array( 'tag', __( 'Priced for small business', $t ), __( 'Plans that pay for themselves with a single extra job a month.', $t ) ),
				array( 'phone', __( 'Meets buyers on mobile', $t ), __( 'Built for the phone-first way local customers actually search.', $t ) ),
			),
			'faqs' => array(
				array( 'q' => __( 'I am not technical — can I still use it?', $t ), 'a' => __( 'Yes. Setup is a guided form — describe your business and the agent is ready. No code, no developer.', $t ) ),
				array( 'q' => __( 'Can it handle bookings for my services?', $t ), 'a' => __( 'Yes. The booking module takes appointments and quote requests with the details you need up front.', $t ) ),
				array( 'q' => __( 'Does it work after hours?', $t ), 'a' => __( 'That is the point — it captures and answers enquiries 24/7 and alerts you so you can follow up.', $t ) ),
				array( 'q' => __( 'Can customers reach me on WhatsApp?', $t ), 'a' => __( 'Yes, with pre-chat capture and after-hours auto-replies so no message is ever missed.', $t ) ),
			),
			'related' => array(
				array( __( 'WhatsApp Automation', $t ), '', 'wa', __( 'Product', $t ) ),
				array( __( 'Booking Forms', $t ), '', 'cal', __( 'Product', $t ) ),
				array( __( 'Clinics & spas', $t ), '', 'cal', __( 'Use case', $t ) ),
				array( __( 'Pricing', $t ), '', 'tag', __( 'See plans', $t ) ),
			),
			'cta' => array(
				'title' => __( 'Turn every missed call into a booked customer', $t ),
				'sub'   => __( 'Set up your always-on front desk in under an hour.', $t ),
			),
		),

		'clinics-spas' => array(
			'kind'    => 'usecase',
			'eyebrow' => __( 'For clinics & spas', $t ),
			'title'   => __( 'Fill your appointment book while your front desk focuses on care', $t ),
			'lead'    => __( 'Dental practices, medspas, salons and wellness clinics use Chatbotistic to answer treatment questions, run intake and book appointments — with deposits and reminders that cut no-shows.', $t ),
			'stats'   => array(
				array( '−40%', __( 'no-shows with deposits and reminders', $t ) ),
				array( '24/7', __( 'self-service appointment booking', $t ) ),
				array( '0', __( 'enquiries lost while staff are with clients', $t ) ),
			),
			'problems_title' => __( 'What overloads clinic front desks', $t ),
			'problems' => array(
				array( __( 'The desk cannot do two things at once', $t ), __( 'While staff care for a client in the room, the phone and the website enquiries go unanswered.', $t ) ),
				array( __( 'No-shows cost real money', $t ), __( 'Empty chairs from forgotten appointments are revenue you can never recover.', $t ) ),
				array( __( 'Intake is repetitive', $t ), __( 'Collecting the same history and prep details by phone eats hours every week.', $t ) ),
			),
			'solution' => array(
				'heading' => __( 'Conversational intake and booking that runs itself', $t ),
				'lead'    => __( 'Chatbotistic answers treatment questions, walks clients through intake and books appointments — taking a deposit so the slot is committed.', $t ),
				'items'   => array(
					array( 'chat', __( 'Treatment Q&A', $t ), __( 'Answer questions on services, pricing and aftercare in your clinic’s voice.', $t ) ),
					array( 'form', __( 'Conversational intake', $t ), __( 'Collect history and prep answers in a friendly step-by-step flow.', $t ) ),
					array( 'cal', __( 'Self-service booking', $t ), __( 'Clients pick a slot with the right practitioner and duration.', $t ) ),
					array( 'card', __( 'Deposit to confirm', $t ), __( 'A small deposit secures the appointment and keeps the chair full.', $t ) ),
				),
			),
			'features' => array(
				'heading' => __( 'What we provide clinics & spas', $t ),
				'items'   => array(
					array( 'cal', __( 'Multi-practitioner scheduling', $t ), __( 'Route bookings by service, room and staff availability.', $t ) ),
					array( 'mail', __( 'Reminders & reschedules', $t ), __( 'Automatic confirmations, reminders and easy reschedule links.', $t ) ),
					array( 'users', __( 'Returning-client recognition', $t ), __( 'Profiles with visit history for a personal experience.', $t ) ),
					array( 'wa', __( 'WhatsApp booking', $t ), __( 'Let clients book and confirm on the channel they prefer.', $t ) ),
					array( 'shield', __( 'Careful data handling', $t ), __( 'Encrypted, consent-aware intake flows you control.', $t ) ),
					array( 'chart', __( 'Schedule dashboard', $t ), __( 'Today’s bookings, no-shows and utilisation at a glance.', $t ) ),
				),
			),
			'why' => array(
				array( 'card', __( 'Deposits cut no-shows', $t ), __( 'A small commitment dramatically improves attendance.', $t ) ),
				array( 'spark', __( 'Frees your front desk', $t ), __( 'Routine questions and booking are handled, so staff focus on clients.', $t ) ),
				array( 'shield', __( 'Privacy-respecting', $t ), __( 'Intake data is encrypted and never sold — you stay in control.', $t ) ),
			),
			'faqs' => array(
				array( 'q' => __( 'Can clients book with a specific practitioner?', $t ), 'a' => __( 'Yes. Booking can be filtered by service, practitioner, room and duration.', $t ) ),
				array( 'q' => __( 'How does it reduce no-shows?', $t ), 'a' => __( 'It can require a deposit at booking and sends automatic reminders with reschedule links.', $t ) ),
				array( 'q' => __( 'Is intake data handled safely?', $t ), 'a' => __( 'Intake flows are encrypted in transit, consent-aware, and the data is never sold. You can export or delete it any time.', $t ) ),
				array( 'q' => __( 'Can it answer treatment questions?', $t ), 'a' => __( 'Yes — the AI agent is trained on your services so it answers accurately in your clinic’s tone.', $t ) ),
			),
			'related' => array(
				array( __( 'Booking Forms', $t ), '', 'cal', __( 'Product', $t ) ),
				array( __( 'AI Chatbot', $t ), '', 'ai', __( 'Product', $t ) ),
				array( __( 'Local businesses', $t ), '', 'store', __( 'Use case', $t ) ),
				array( __( 'Security', $t ), '', 'shield', __( 'Data handling', $t ) ),
			),
			'cta' => array(
				'title' => __( 'Keep every chair full — automatically', $t ),
				'sub'   => __( 'Launch conversational booking for your clinic today.', $t ),
			),
		),

		'real-estate' => array(
			'kind'    => 'usecase',
			'eyebrow' => __( 'For real estate', $t ),
			'title'   => __( 'Respond to property leads in seconds, not hours', $t ),
			'lead'    => __( 'Agents, brokerages and property developers use Chatbotistic to qualify enquiries, capture buyer requirements and route hot leads to the right agent instantly over WhatsApp.', $t ),
			'stats'   => array(
				array( '< 1 min', __( 'to first response on a new lead', $t ) ),
				array( '24/7', __( 'enquiry handling across listings', $t ) ),
				array( '100%', __( 'of leads captured with full context', $t ) ),
			),
			'problems_title' => __( 'Why property leads go cold', $t ),
			'problems' => array(
				array( __( 'Speed decides the deal', $t ), __( 'Buyers contact several listings at once — the first agent to reply usually wins the viewing.', $t ) ),
				array( __( 'Leads sit in an inbox', $t ), __( 'A hot enquiry waiting hours for a reply is a viewing booked with someone else.', $t ) ),
				array( __( 'No qualification up front', $t ), __( 'Agents waste time on enquiries with the wrong budget, timeline or location.', $t ) ),
			),
			'solution' => array(
				'heading' => __( 'Instant qualification and agent routing', $t ),
				'lead'    => __( 'Chatbotistic greets every enquiry, captures budget, location and timeline, and hands a qualified lead to the right agent on WhatsApp immediately.', $t ),
				'items'   => array(
					array( 'chat', __( 'Instant engagement', $t ), __( 'Every listing enquiry gets an immediate, useful reply — day or night.', $t ) ),
					array( 'form', __( 'Buyer qualification', $t ), __( 'Capture budget, area, property type and timeline before an agent steps in.', $t ) ),
					array( 'users', __( 'Agent routing', $t ), __( 'Route by location, language or specialism to the best-matched agent.', $t ) ),
					array( 'wa', __( 'WhatsApp handoff', $t ), __( 'Move the conversation to WhatsApp with full context for a fast close.', $t ) ),
				),
			),
			'features' => array(
				'heading' => __( 'What we provide real estate teams', $t ),
				'items'   => array(
					array( 'tag', __( 'Property tagging', $t ), __( 'Every lead is tagged to the listing and enquiry source.', $t ) ),
					array( 'cal', __( 'Viewing booking', $t ), __( 'Let buyers book viewings directly into an agent’s calendar.', $t ) ),
					array( 'mail', __( 'Hot-lead alerts', $t ), __( 'Agents are notified the instant a qualified lead lands.', $t ) ),
					array( 'inbox', __( 'Shared lead inbox', $t ), __( 'Every enquiry, profile and history in one place for the team.', $t ) ),
					array( 'globe', __( 'Multi-language', $t ), __( 'Engage international buyers in their own language.', $t ) ),
					array( 'chart', __( 'Source reporting', $t ), __( 'See which portals and campaigns produce real viewings.', $t ) ),
				),
			),
			'why' => array(
				array( 'bolt', __( 'First to reply, first to win', $t ), __( 'Sub-minute responses put your agents ahead of every competing listing.', $t ) ),
				array( 'form', __( 'Only qualified leads', $t ), __( 'Agents spend time on buyers who match the property and the budget.', $t ) ),
				array( 'globe', __( 'Built for global buyers', $t ), __( 'Multi-language engagement for international property markets.', $t ) ),
			),
			'faqs' => array(
				array( 'q' => __( 'Can it route leads to specific agents?', $t ), 'a' => __( 'Yes — route by location, language, property type or agent specialism automatically.', $t ) ),
				array( 'q' => __( 'Does it qualify buyers?', $t ), 'a' => __( 'Yes. It captures budget, area and timeline so agents only follow up on matched enquiries.', $t ) ),
				array( 'q' => __( 'Can buyers book viewings themselves?', $t ), 'a' => __( 'Yes, the booking module lets buyers schedule viewings into an agent’s calendar.', $t ) ),
				array( 'q' => __( 'Does it handle international enquiries?', $t ), 'a' => __( 'Yes — it detects and replies in the buyer’s language, which is ideal for cross-border markets.', $t ) ),
			),
			'related' => array(
				array( __( 'WhatsApp Automation', $t ), '', 'wa', __( 'Product', $t ) ),
				array( __( 'AI Chatbot', $t ), '', 'ai', __( 'Product', $t ) ),
				array( __( 'Booking Forms', $t ), '', 'cal', __( 'Book viewings', $t ) ),
				array( __( 'Pricing', $t ), '', 'tag', __( 'See plans', $t ) ),
			),
			'cta' => array(
				'title' => __( 'Be the first agent every buyer hears back from', $t ),
				'sub'   => __( 'Put instant lead qualification on every listing today.', $t ),
			),
		),

		'ecommerce' => array(
			'kind'    => 'usecase',
			'eyebrow' => __( 'For eCommerce', $t ),
			'title'   => __( 'Recover carts and answer pre-sales questions before they bounce', $t ),
			'lead'    => __( 'Online stores use Chatbotistic as an AI store assistant — answering product questions, looking up orders and recovering abandoned carts on WhatsApp where messages actually get read.', $t ),
			'stats'   => array(
				array( '70%', __( 'of carts are abandoned on average', $t ) ),
				array( '98%', __( 'WhatsApp open rate for recovery messages', $t ) ),
				array( '24/7', __( 'pre-sales and order support', $t ) ),
			),
			'problems_title' => __( 'Where eCommerce revenue leaks', $t ),
			'problems' => array(
				array( __( 'Pre-purchase doubt kills the sale', $t ), __( 'Shipping, sizing and returns questions go unanswered and the shopper quietly leaves.', $t ) ),
				array( __( 'Abandoned carts stay abandoned', $t ), __( 'Recovery emails land in promotions tabs and rarely get opened.', $t ) ),
				array( __( '“Where is my order?” floods support', $t ), __( 'Repetitive order-status tickets eat the time your team needs for real issues.', $t ) ),
			),
			'solution' => array(
				'heading' => __( 'An AI store assistant that lifts conversion', $t ),
				'lead'    => __( 'Chatbotistic answers product questions, looks up orders and re-engages abandoned carts on the channels shoppers actually check.', $t ),
				'items'   => array(
					array( 'chat', __( 'Pre-sales answers', $t ), __( 'Shipping, sizing, stock and returns answered instantly, in chat.', $t ) ),
					array( 'cart', __( 'Cart recovery', $t ), __( 'Re-engage abandoned carts with a WhatsApp message that gets opened.', $t ) ),
					array( 'inbox', __( 'Order lookup', $t ), __( 'Shoppers self-serve order status without opening a ticket.', $t ) ),
					array( 'tag', __( 'Product recommendations', $t ), __( 'Guide shoppers to the right item based on what they ask.', $t ) ),
				),
			),
			'features' => array(
				'heading' => __( 'What we provide online stores', $t ),
				'items'   => array(
					array( 'cart', __( 'WooCommerce ready', $t ), __( 'Native order lookup, cart recovery and recommendations.', $t ) ),
					array( 'wa', __( 'WhatsApp messaging', $t ), __( 'Order updates and recovery on a channel with a 98% open rate.', $t ) ),
					array( 'ai', __( 'Trained on your catalog', $t ), __( 'Accurate answers about your real products and policies.', $t ) ),
					array( 'mail', __( 'Re-engagement flows', $t ), __( 'Automated nudges for browse and cart abandonment.', $t ) ),
					array( 'globe', __( 'Multi-language', $t ), __( 'Sell to international shoppers in their own language.', $t ) ),
					array( 'chart', __( 'Conversion analytics', $t ), __( 'See how chat assistance lifts revenue per visitor.', $t ) ),
				),
			),
			'why' => array(
				array( 'cart', __( 'Built for WooCommerce', $t ), __( 'Deep store integration, not a generic chat bolt-on.', $t ) ),
				array( 'wa', __( 'Messages that get read', $t ), __( 'WhatsApp recovery beats email open rates many times over.', $t ) ),
				array( 'spark', __( 'Lifts revenue per visitor', $t ), __( 'Answer doubt at the moment of purchase and conversion climbs.', $t ) ),
			),
			'faqs' => array(
				array( 'q' => __( 'Does it integrate with WooCommerce?', $t ), 'a' => __( 'Yes — order lookup, cart recovery and product recommendations work out of the box with WooCommerce.', $t ) ),
				array( 'q' => __( 'How does cart recovery work?', $t ), 'a' => __( 'When a shopper abandons a cart, Chatbotistic can re-engage them with an automated WhatsApp message that links straight back to checkout.', $t ) ),
				array( 'q' => __( 'Can it answer product questions accurately?', $t ), 'a' => __( 'Yes. The agent is trained on your catalog and policies so answers reflect your real products.', $t ) ),
				array( 'q' => __( 'Can shoppers check order status themselves?', $t ), 'a' => __( 'Yes, they can look up order status in chat without creating a support ticket.', $t ) ),
			),
			'related' => array(
				array( __( 'WhatsApp Automation', $t ), '', 'wa', __( 'Product', $t ) ),
				array( __( 'WordPress Plugin', $t ), '', 'wp', __( 'WooCommerce setup', $t ) ),
				array( __( 'AI Chatbot', $t ), '', 'ai', __( 'Product', $t ) ),
				array( __( 'Pricing', $t ), '', 'tag', __( 'See plans', $t ) ),
			),
			'cta' => array(
				'title' => __( 'Stop losing sales to unanswered questions', $t ),
				'sub'   => __( 'Add an AI store assistant to your shop today.', $t ),
			),
		),

		'coaches' => array(
			'kind'    => 'usecase',
			'eyebrow' => __( 'For coaches & consultants', $t ),
			'title'   => __( 'Book only the discovery calls that are worth your time', $t ),
			'lead'    => __( 'Coaches, consultants and freelancers use Chatbotistic to pre-qualify prospects, run discovery flows and book paid strategy calls — so the calendar fills with the right people.', $t ),
			'stats'   => array(
				array( '2×', __( 'show-up rate with deposits on calls', $t ) ),
				array( '24/7', __( 'discovery and booking on autopilot', $t ) ),
				array( '0', __( 'unqualified calls clogging your week', $t ) ),
			),
			'problems_title' => __( 'What drains a coach’s calendar', $t ),
			'problems' => array(
				array( __( 'Free calls with the wrong fit', $t ), __( 'Hours disappear into discovery calls with prospects who were never going to buy.', $t ) ),
				array( __( 'Scheduling ping-pong', $t ), __( 'Going back and forth to find a time loses momentum and loses prospects.', $t ) ),
				array( __( 'No-shows on free slots', $t ), __( 'When a call costs nothing to book, it costs nothing to skip.', $t ) ),
			),
			'solution' => array(
				'heading' => __( 'A qualifier that protects your time', $t ),
				'lead'    => __( 'Chatbotistic runs a discovery conversation, scores fit and budget, then books — and optionally charges for — the call.', $t ),
				'items'   => array(
					array( 'form', __( 'Pre-call qualifier', $t ), __( 'A conversational flow that scores fit, goals and budget before booking.', $t ) ),
					array( 'cal', __( 'Direct calendar booking', $t ), __( 'Qualified prospects pick a slot instantly — no email tag.', $t ) ),
					array( 'card', __( 'Paid strategy calls', $t ), __( 'Charge for the call or take a deposit to guarantee they show.', $t ) ),
					array( 'ai', __( 'Always-on intake', $t ), __( 'Prospects start the conversation any time, in their own words.', $t ) ),
				),
			),
			'features' => array(
				'heading' => __( 'What we provide coaches & consultants', $t ),
				'items'   => array(
					array( 'rocket', __( 'Discovery flows', $t ), __( 'Branching questions that surface goals and readiness to buy.', $t ) ),
					array( 'card', __( 'Payment in-flow', $t ), __( 'Stripe or PayPal for paid calls and deposits.', $t ) ),
					array( 'mail', __( 'Reminders & follow-ups', $t ), __( 'Automated nudges that keep booked calls on the calendar.', $t ) ),
					array( 'page', __( 'Lead magnet capture', $t ), __( 'Trade a resource for contact details inside the chat.', $t ) ),
					array( 'inbox', __( 'Prospect profiles', $t ), __( 'Every conversation saved with context for the call.', $t ) ),
					array( 'chart', __( 'Funnel analytics', $t ), __( 'See where prospects drop and what converts.', $t ) ),
				),
			),
			'why' => array(
				array( 'form', __( 'Protects your calendar', $t ), __( 'Only qualified, ready-to-buy prospects reach a booked slot.', $t ) ),
				array( 'card', __( 'Fewer no-shows', $t ), __( 'A deposit or paid call sharply lifts attendance.', $t ) ),
				array( 'spark', __( 'Runs while you work', $t ), __( 'Discovery and booking happen 24/7 without your involvement.', $t ) ),
			),
			'faqs' => array(
				array( 'q' => __( 'Can I charge for discovery calls?', $t ), 'a' => __( 'Yes. Take full payment or a deposit with Stripe or PayPal inside the booking flow.', $t ) ),
				array( 'q' => __( 'How does qualification work?', $t ), 'a' => __( 'A branching conversation captures goals, timeline and budget, and only matched prospects reach the slot picker.', $t ) ),
				array( 'q' => __( 'Will it reduce no-shows?', $t ), 'a' => __( 'Yes — a paid or deposit-backed call plus automated reminders typically doubles show-up rates.', $t ) ),
				array( 'q' => __( 'Can I use it without a website?', $t ), 'a' => __( 'Yes — share a hosted link, or embed the flow on any site or landing page.', $t ) ),
			),
			'related' => array(
				array( __( 'Booking Forms', $t ), '', 'cal', __( 'Product', $t ) ),
				array( __( 'AI Chatbot', $t ), '', 'ai', __( 'Product', $t ) ),
				array( __( 'SaaS founders', $t ), '', 'rocket', __( 'Use case', $t ) ),
				array( __( 'Pricing', $t ), '', 'tag', __( 'See plans', $t ) ),
			),
			'cta' => array(
				'title' => __( 'Fill your calendar with qualified calls only', $t ),
				'sub'   => __( 'Set up your discovery and booking flow today.', $t ),
			),
		),

		'wordpress-sites' => array(
			'kind'    => 'usecase',
			'eyebrow' => __( 'For WordPress sites', $t ),
			'title'   => __( 'Add AI chat to every WordPress build — and bill for it', $t ),
			'lead'    => __( 'WordPress freelancers and studios use Chatbotistic as the conversation layer they embed in every client site — one platform, a native plugin, and a recurring revenue stream.', $t ),
			'stats'   => array(
				array( '1-click', __( 'plugin install on any WP site', $t ) ),
				array( '50K+', __( 'WordPress sites in the ecosystem', $t ) ),
				array( '0', __( 'lines of custom chat code to maintain', $t ) ),
			),
			'problems_title' => __( 'Why custom chat on WordPress is a trap', $t ),
			'problems' => array(
				array( __( 'One-off scripts per project', $t ), __( 'Building a bespoke chat or form for each client means code you maintain forever, for free.', $t ) ),
				array( __( 'Plugins bloat the site', $t ), __( 'Stacking heavy third-party widgets wrecks the performance you promised the client.', $t ) ),
				array( __( 'No recurring income', $t ), __( 'You build it once, hand it over, and the revenue stops.', $t ) ),
			),
			'solution' => array(
				'heading' => __( 'One conversation layer for every build', $t ),
				'lead'    => __( 'Chatbotistic ships a lightweight WordPress plugin you embed in every project and manage from one account.', $t ),
				'items'   => array(
					array( 'plug', __( 'Native plugin', $t ), __( 'Install, activate with a key, and widgets appear — no theme edits.', $t ) ),
					array( 'code', __( 'Shortcode & block', $t ), __( 'Place chat, booking or forms anywhere with a block or shortcode.', $t ) ),
					array( 'bolt', __( 'Performance-safe', $t ), __( 'Deferred, lean assets keep Core Web Vitals green.', $t ) ),
					array( 'tag', __( 'Recurring revenue', $t ), __( 'Bill clients monthly for managed chat — keep the margin.', $t ) ),
				),
			),
			'features' => array(
				'heading' => __( 'What we provide WordPress professionals', $t ),
				'items'   => array(
					array( 'wp', __( 'WordPressistic ecosystem', $t ), __( 'Part of a tool family built specifically for WordPress.', $t ) ),
					array( 'gear', __( 'Manage in WP admin', $t ), __( 'Edit bots, forms and booking flows without leaving WordPress.', $t ) ),
					array( 'shield', __( 'Membership-aware', $t ), __( 'Respond differently to logged-in members and visitors.', $t ) ),
					array( 'cart', __( 'WooCommerce support', $t ), __( 'Order lookup and cart recovery for store clients.', $t ) ),
					array( 'key', __( 'License per site', $t ), __( 'Clean activation and license management for every project.', $t ) ),
					array( 'chart', __( 'Embedded dashboard', $t ), __( 'Show clients their results inside their own WP admin.', $t ) ),
				),
			),
			'why' => array(
				array( 'wp', __( 'WordPress-native', $t ), __( 'Built for the platform — secure, lightweight and theme-friendly.', $t ) ),
				array( 'tag', __( 'A new revenue line', $t ), __( 'Turn a one-off build into a managed monthly service.', $t ) ),
				array( 'spark', __( 'Reusable everywhere', $t ), __( 'One platform you embed in every site you ship.', $t ) ),
			),
			'faqs' => array(
				array( 'q' => __( 'How is the plugin installed?', $t ), 'a' => __( 'Install it from the dashboard like any plugin, activate, and paste your license key to sync widgets.', $t ) ),
				array( 'q' => __( 'Will it slow client sites down?', $t ), 'a' => __( 'No — assets are deferred and lean, and load only where a widget is used.', $t ) ),
				array( 'q' => __( 'Can I resell it to clients?', $t ), 'a' => __( 'Yes. With Agency and White Label plans you manage client sites and bill them under your own brand.', $t ) ),
				array( 'q' => __( 'Does it work with WooCommerce and membership plugins?', $t ), 'a' => __( 'Yes — it supports WooCommerce order flows and membership-aware responses.', $t ) ),
			),
			'related' => array(
				array( __( 'WordPress Plugin', $t ), '', 'wp', __( 'Product', $t ) ),
				array( __( 'Agency & White Label', $t ), '', 'tag', __( 'Resell it', $t ) ),
				array( __( 'Agencies', $t ), '', 'users', __( 'Use case', $t ) ),
				array( __( 'Documentation', $t ), '', 'page', __( 'Setup guide', $t ) ),
			),
			'cta' => array(
				'title' => __( 'Make AI chat a standard part of every build', $t ),
				'sub'   => __( 'Install the plugin and start billing for managed chat.', $t ),
			),
		),

		'saas-founders' => array(
			'kind'    => 'usecase',
			'eyebrow' => __( 'For SaaS founders', $t ),
			'title'   => __( 'Turn trial visitors into activated, paying users', $t ),
			'lead'    => __( 'SaaS founders use Chatbotistic to qualify trial signups, guide onboarding, book demos and nudge free users toward paid — without hiring an onboarding team.', $t ),
			'stats'   => array(
				array( '24/7', __( 'onboarding guidance for new users', $t ) ),
				array( '3×', __( 'demo bookings vs. a static CTA', $t ) ),
				array( '0', __( 'extra headcount to run it', $t ) ),
			),
			'problems_title' => __( 'Where SaaS funnels lose users', $t ),
			'problems' => array(
				array( __( 'Visitors do not convert to trial', $t ), __( 'A pricing page with no guide leaves undecided visitors to leave undecided.', $t ) ),
				array( __( 'Onboarding has no co-pilot', $t ), __( 'New users hit a wall, get no nudge, and churn before the aha moment.', $t ) ),
				array( __( 'Free users never get asked', $t ), __( 'Without a prompt at the right moment, free users simply stay free.', $t ) ),
			),
			'solution' => array(
				'heading' => __( 'A growth co-pilot across the funnel', $t ),
				'lead'    => __( 'Chatbotistic engages visitors, qualifies trials, guides activation and prompts the upgrade at the right moment.', $t ),
				'items'   => array(
					array( 'ai', __( 'Pre-sales answers', $t ), __( 'Answer product, pricing and integration questions on the spot.', $t ) ),
					array( 'rocket', __( 'Onboarding guide', $t ), __( 'Walk new users to their first win with contextual prompts.', $t ) ),
					array( 'cal', __( 'Demo booking', $t ), __( 'Qualified prospects book a demo straight from the conversation.', $t ) ),
					array( 'spark', __( 'Upgrade nudges', $t ), __( 'Prompt free-to-paid at the moment a user hits real value.', $t ) ),
				),
			),
			'features' => array(
				'heading' => __( 'What we provide SaaS teams', $t ),
				'items'   => array(
					array( 'form', __( 'Trial qualification', $t ), __( 'Score signups so sales focuses on the accounts that matter.', $t ) ),
					array( 'code', __( 'API & webhooks', $t ), __( 'Pipe events and leads into your product and data stack.', $t ) ),
					array( 'globe', __( 'Multi-language', $t ), __( 'Engage a global user base in their own language.', $t ) ),
					array( 'inbox', __( 'Unified inbox', $t ), __( 'Sales and support share one view of every conversation.', $t ) ),
					array( 'plug', __( 'CRM sync', $t ), __( 'Two-way sync with HubSpot, Pipedrive and more.', $t ) ),
					array( 'chart', __( 'Funnel analytics', $t ), __( 'See where activation stalls and what moves the needle.', $t ) ),
				),
			),
			'why' => array(
				array( 'rocket', __( 'Covers the whole funnel', $t ), __( 'One agent works from first visit to paid upgrade.', $t ) ),
				array( 'code', __( 'Developer-friendly', $t ), __( 'A clean REST API and webhooks for every event.', $t ) ),
				array( 'tag', __( 'Scales without headcount', $t ), __( 'Onboarding and qualification run automatically as you grow.', $t ) ),
			),
			'faqs' => array(
				array( 'q' => __( 'Can it connect to my product?', $t ), 'a' => __( 'Yes — use the REST API and webhooks to send events both ways between Chatbotistic and your app.', $t ) ),
				array( 'q' => __( 'Can it book product demos?', $t ), 'a' => __( 'Yes. Qualified prospects can book a demo into your calendar directly from the chat.', $t ) ),
				array( 'q' => __( 'Does it help with activation?', $t ), 'a' => __( 'Yes — it guides new users with contextual prompts toward their first meaningful win.', $t ) ),
				array( 'q' => __( 'Can it run in multiple languages?', $t ), 'a' => __( 'Yes, it detects and replies in the user’s language for a global audience.', $t ) ),
			),
			'related' => array(
				array( __( 'AI Chatbot', $t ), '', 'ai', __( 'Product', $t ) ),
				array( __( 'Booking Forms', $t ), '', 'cal', __( 'Demo booking', $t ) ),
				array( __( 'Coaches', $t ), '', 'rocket', __( 'Use case', $t ) ),
				array( __( 'Features', $t ), '', 'spark', __( 'All modules', $t ) ),
			),
			'cta' => array(
				'title' => __( 'Turn signups into activated, paying users', $t ),
				'sub'   => __( 'Add a growth co-pilot to your funnel today.', $t ),
			),
		),
	);

	/* Resolve internal links by slug now that the map is known. */
	$urls = array(
		__( 'AI Chatbot', $t )            => '/ai-chatbot/',
		__( 'WhatsApp Automation', $t )   => '/whatsapp-automation/',
		__( 'Booking Forms', $t )         => '/booking-forms/',
		__( 'WordPress Plugin', $t )      => '/wordpress-plugin/',
		__( 'Agency & White Label', $t )  => '/agency-white-label/',
		__( 'Agencies', $t )              => '/agencies/',
		__( 'Local businesses', $t )      => '/local-business/',
		__( 'Clinics & spas', $t )        => '/clinics-spas/',
		__( 'Real estate', $t )           => '/real-estate/',
		__( 'eCommerce', $t )             => '/ecommerce/',
		__( 'Coaches', $t )               => '/coaches/',
		__( 'WordPress sites', $t )       => '/wordpress-sites/',
		__( 'SaaS founders', $t )         => '/saas-founders/',
		__( 'Pricing', $t )               => '/pricing/',
		__( 'Features', $t )              => '/features/',
		__( 'Documentation', $t )         => '/docs/',
		__( 'Affiliate Program', $t )     => '/affiliate/',
		__( 'Security', $t )              => '/security/',
	);
	foreach ( $data as &$entry ) {
		if ( empty( $entry['related'] ) ) {
			continue;
		}
		foreach ( $entry['related'] as &$rel ) {
			if ( empty( $rel[1] ) && isset( $urls[ $rel[0] ] ) ) {
				$rel[1] = home_url( $urls[ $rel[0] ] );
			}
		}
		unset( $rel );
	}
	unset( $entry );

	return $data;
}
