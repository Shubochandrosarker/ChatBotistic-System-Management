<?php
/**
 * V4 landing-page content — use cases (9) + product pages (5).
 *
 * Data-only. Rendering lives in template-parts/landing-v4.php and
 * template-parts/product-v4.php. Keep copy translation-ready here.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return the use-case entry for a slug, or null.
 *
 * @param string $slug
 * @return array|null
 */
function cb_v4_use_case( $slug ) {
	$all = cb_v4_use_cases();
	// Legacy slug aliases — keep old URLs working.
	$aliases = array(
		'coaches' => 'coaches-consultants',
		'clinics' => 'clinics-spas',
	);
	if ( isset( $aliases[ $slug ] ) ) {
		$slug = $aliases[ $slug ];
	}
	return $all[ $slug ] ?? null;
}

/**
 * Return all use-case entries keyed by slug.
 */
function cb_v4_use_cases() {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}
	$data = array(
		'agencies' => array(
			'icon' => 'users', 'title' => __( 'Agencies', 'chatbotistic' ), 'market' => __( 'Agencies & resellers', 'chatbotistic' ),
			'problem' => __( 'Manual reporting, slow client lead handoff, scattered tools.', 'chatbotistic' ),
			'solution' => __( 'Workspace-per-client, instant lead routing, white-label dashboards.', 'chatbotistic' ),
			'feats' => array( 'White label', 'Client workspaces', 'Multi-tenant' ),
			'hero' => __( 'Run conversational marketing for every client — under your own brand.', 'chatbotistic' ),
			'sub' => __( 'Spin up a white-label workspace per client, deploy chat + WhatsApp + booking in minutes, and bill it as recurring revenue.', 'chatbotistic' ),
			'pains' => array(
				array( __( 'Every client site is a one-off', 'chatbotistic' ), __( 'You rebuild chat, forms, and routing by hand for each project — nothing is repeatable.', 'chatbotistic' ) ),
				array( __( 'Reporting eats your margin', 'chatbotistic' ), __( 'Manual screenshots and spreadsheets to prove value, month after month.', 'chatbotistic' ) ),
				array( __( 'Leads get lost between tools', 'chatbotistic' ), __( 'Client leads scatter across inboxes, WhatsApp, and forms with no single source of truth.', 'chatbotistic' ) ),
			),
			'helps' => array(
				array( __( 'White-label everything', 'chatbotistic' ), __( 'Your logo, your domain, your client login. Chatbotistic disappears completely.', 'chatbotistic' ) ),
				array( __( 'Workspace per client', 'chatbotistic' ), __( 'Isolated inboxes, billing, roles, and analytics for each account you manage.', 'chatbotistic' ) ),
				array( __( 'Deploy in minutes', 'chatbotistic' ), __( 'Reuse widget templates across clients. Launch a full setup in under 30 minutes.', 'chatbotistic' ) ),
				array( __( 'Client-ready reports', 'chatbotistic' ), __( 'Export-ready dashboards that prove ROI without the manual busywork.', 'chatbotistic' ) ),
			),
			'flow' => array( __( 'Create a client workspace', 'chatbotistic' ), __( 'Drop in a widget template', 'chatbotistic' ), __( 'Connect their WordPress site', 'chatbotistic' ), __( 'Leads route to the right team', 'chatbotistic' ), __( 'Share the white-label dashboard', 'chatbotistic' ) ),
			'modules' => array( 'White Label Dashboard', 'Multi-Agent Inbox', 'WordPress Connector', 'Analytics', 'API & Webhooks' ),
			'metrics' => array( array( '$240', __( 'Avg. monthly revenue per client', 'chatbotistic' ) ), array( '< 30 min', __( 'Setup time per client', 'chatbotistic' ) ), array( '380+', __( 'Agency partners onboarded', 'chatbotistic' ) ) ),
			'plan' => 'Agency', 'plan_note' => __( 'Unlimited widgets, white-label, and team agents.', 'chatbotistic' ),
			'faqs' => array(
				array( __( 'Can clients log in under my brand?', 'chatbotistic' ), __( 'Yes. On Agency and Lifetime the entire dashboard, domain, and emails carry your branding — clients never see Chatbotistic.', 'chatbotistic' ) ),
				array( __( 'How is billing handled?', 'chatbotistic' ), __( 'You bill clients directly and keep the margin. Chatbotistic charges you one Agency subscription.', 'chatbotistic' ) ),
				array( __( 'Can I manage all client leads in one place?', 'chatbotistic' ), __( 'Yes — a master inbox spans every client workspace, with per-client filtering.', 'chatbotistic' ) ),
			),
		),
		'local-business' => array(
			'icon' => 'store', 'title' => __( 'Local service businesses', 'chatbotistic' ), 'market' => __( 'Local & home services', 'chatbotistic' ),
			'problem' => __( 'Phone tag, missed enquiries after hours, no follow-up system.', 'chatbotistic' ),
			'solution' => __( '24/7 AI capture, after-hours auto-reply, instant notifications.', 'chatbotistic' ),
			'feats' => array( 'WhatsApp', 'Lead capture', 'Email alerts' ),
			'hero' => __( 'Never miss another after-hours enquiry.', 'chatbotistic' ),
			'sub' => __( 'Capture every visitor and WhatsApp message around the clock, auto-reply instantly, and get pinged the moment a real lead lands.', 'chatbotistic' ),
			'pains' => array(
				array( __( 'Calls go unanswered', 'chatbotistic' ), __( 'You’re on a job, the phone rings out, and the lead calls a competitor instead.', 'chatbotistic' ) ),
				array( __( 'After-hours enquiries vanish', 'chatbotistic' ), __( 'Most enquiries arrive evenings and weekends — with no one to reply.', 'chatbotistic' ) ),
				array( __( 'No follow-up system', 'chatbotistic' ), __( 'Leads sit in a personal WhatsApp with no reminders or tracking.', 'chatbotistic' ) ),
			),
			'helps' => array(
				array( __( '24/7 WhatsApp capture', 'chatbotistic' ), __( 'Visitors start a WhatsApp chat from your site; the bot captures details instantly.', 'chatbotistic' ) ),
				array( __( 'After-hours auto-reply', 'chatbotistic' ), __( 'Different replies for open hours, after hours, and holidays — set once.', 'chatbotistic' ) ),
				array( __( 'Instant notifications', 'chatbotistic' ), __( 'You and your team get pinged the second a qualified lead comes in.', 'chatbotistic' ) ),
				array( __( 'Booking inside chat', 'chatbotistic' ), __( 'Let customers book a slot or request a callback without a phone call.', 'chatbotistic' ) ),
			),
			'flow' => array( __( 'Visitor lands after hours', 'chatbotistic' ), __( 'Bot captures name + need', 'chatbotistic' ), __( 'Auto-reply sets expectations', 'chatbotistic' ), __( 'You get an instant alert', 'chatbotistic' ), __( 'Follow up first thing', 'chatbotistic' ) ),
			'modules' => array( 'WhatsApp Widget', 'Lead Capture', 'Email Notifications', 'Booking Forms' ),
			'metrics' => array( array( '24/7', __( 'Coverage with zero staff', 'chatbotistic' ) ), array( '< 2s', __( 'First reply time', 'chatbotistic' ) ), array( '3×', __( 'More after-hours leads captured', 'chatbotistic' ) ) ),
			'plan' => 'Pro', 'plan_note' => __( 'WhatsApp agents, CRM, and the WordPress plugin.', 'chatbotistic' ),
			'faqs' => array(
				array( __( 'Do I need WhatsApp Business API?', 'chatbotistic' ), __( 'No — Chatbotistic works with a standard WhatsApp number and handles the routing for you.', 'chatbotistic' ) ),
				array( __( 'Can I set business hours?', 'chatbotistic' ), __( 'Yes. Define open hours, after-hours, and holiday replies that switch automatically.', 'chatbotistic' ) ),
				array( __( 'Will I be notified instantly?', 'chatbotistic' ), __( 'Yes — email and dashboard pings fire the moment a lead is captured.', 'chatbotistic' ) ),
			),
		),
		'clinics-spas' => array(
			'icon' => 'spa', 'title' => __( 'Clinics & Spas', 'chatbotistic' ), 'market' => __( 'Spas, salons & wellness', 'chatbotistic' ),
			'problem' => __( 'Front desk overwhelmed, no-shows, inconsistent inquiries.', 'chatbotistic' ),
			'solution' => __( 'Booking inside chat with deposit, reminder automations.', 'chatbotistic' ),
			'feats' => array( 'Booking', 'Deposits', 'Reminders' ),
			'hero' => __( 'Fill your calendar and cut no-shows — automatically.', 'chatbotistic' ),
			'sub' => __( 'Let clients book treatments inside a chat, take a deposit to lock the slot, and send reminders that keep your chairs full.', 'chatbotistic' ),
			'pains' => array(
				array( __( 'Front desk is overwhelmed', 'chatbotistic' ), __( 'Staff juggle walk-ins and phones while booking requests pile up online.', 'chatbotistic' ) ),
				array( __( 'No-shows cost real money', 'chatbotistic' ), __( 'Empty slots with no deposit mean lost revenue you can’t recover.', 'chatbotistic' ) ),
				array( __( 'Inconsistent intake', 'chatbotistic' ), __( 'Enquiries arrive half-complete, so staff chase details before confirming.', 'chatbotistic' ) ),
			),
			'helps' => array(
				array( __( 'Booking inside chat', 'chatbotistic' ), __( 'Clients pick a service, staff member, and time without leaving the conversation.', 'chatbotistic' ) ),
				array( __( 'Deposit collection', 'chatbotistic' ), __( 'Take a Stripe or PayPal deposit to confirm the slot and reduce no-shows.', 'chatbotistic' ) ),
				array( __( 'Reminder automations', 'chatbotistic' ), __( 'Confirmations and reminders go out automatically, with reschedule links.', 'chatbotistic' ) ),
				array( __( 'Structured intake', 'chatbotistic' ), __( 'Capture everything you need up front — no back-and-forth before the visit.', 'chatbotistic' ) ),
			),
			'flow' => array( __( 'Client opens the chat', 'chatbotistic' ), __( 'Picks service + time', 'chatbotistic' ), __( 'Pays a deposit', 'chatbotistic' ), __( 'Gets a confirmation', 'chatbotistic' ), __( 'Reminder before the visit', 'chatbotistic' ) ),
			'modules' => array( 'Booking Forms', 'Payment Forms', 'Email Notifications', 'AI Chatbot' ),
			'metrics' => array( array( '−60%', __( 'No-show rate', 'chatbotistic' ) ), array( '24/7', __( 'Self-serve booking', 'chatbotistic' ) ), array( '+30%', __( 'Slots filled online', 'chatbotistic' ) ) ),
			'plan' => 'Pro', 'plan_note' => __( 'Booking, deposits, and reminder automations.', 'chatbotistic' ),
			'faqs' => array(
				array( __( 'Can I take deposits?', 'chatbotistic' ), __( 'Yes — connect Stripe or PayPal and require a deposit to confirm a booking.', 'chatbotistic' ) ),
				array( __( 'Does it sync to my calendar?', 'chatbotistic' ), __( 'Bookings sync to your connected calendar and appear in the dashboard schedule.', 'chatbotistic' ) ),
				array( __( 'Can clients reschedule?', 'chatbotistic' ), __( 'Yes, reminder emails include a self-serve reschedule link.', 'chatbotistic' ) ),
			),
		),
		'ecommerce' => array(
			'icon' => 'cart', 'title' => __( 'eCommerce stores', 'chatbotistic' ), 'market' => __( 'eCommerce & retail', 'chatbotistic' ),
			'problem' => __( 'Abandoned carts, support tickets, pre-purchase questions.', 'chatbotistic' ),
			'solution' => __( 'AI store assistant, order lookup, cart recovery DMs.', 'chatbotistic' ),
			'feats' => array( 'Assistant', 'Cart recovery', 'Woo' ),
			'hero' => __( 'Answer buyers, recover carts, and grow order value.', 'chatbotistic' ),
			'sub' => __( 'An AI store assistant handles pre-purchase questions, looks up orders, and recovers abandoned carts over WhatsApp — natively on WooCommerce.', 'chatbotistic' ),
			'pains' => array(
				array( __( 'Carts get abandoned', 'chatbotistic' ), __( 'Buyers hesitate, leave, and there’s no nudge to bring them back.', 'chatbotistic' ) ),
				array( __( 'Repetitive support tickets', 'chatbotistic' ), __( '“Where’s my order?” questions flood your inbox.', 'chatbotistic' ) ),
				array( __( 'Pre-purchase questions go unanswered', 'chatbotistic' ), __( 'Hesitant buyers leave when no one answers in time.', 'chatbotistic' ) ),
			),
			'helps' => array(
				array( __( 'AI store assistant', 'chatbotistic' ), __( 'Answers product, shipping, and returns questions trained on your store.', 'chatbotistic' ) ),
				array( __( 'Order lookup', 'chatbotistic' ), __( 'Buyers self-serve order status without a support ticket.', 'chatbotistic' ) ),
				array( __( 'Cart recovery DMs', 'chatbotistic' ), __( 'Automated WhatsApp nudges bring abandoned carts back.', 'chatbotistic' ) ),
				array( __( 'WooCommerce native', 'chatbotistic' ), __( 'Product, order, and cart data sync straight from WooCommerce.', 'chatbotistic' ) ),
			),
			'flow' => array( __( 'Buyer asks a question', 'chatbotistic' ), __( 'AI answers from your catalog', 'chatbotistic' ), __( 'Cart abandoned → WhatsApp nudge', 'chatbotistic' ), __( 'Order status self-served', 'chatbotistic' ), __( 'Support load drops', 'chatbotistic' ) ),
			'modules' => array( 'AI Chatbot', 'WooCommerce Connector', 'WhatsApp Widget', 'CRM Integrations' ),
			'metrics' => array( array( '+18%', __( 'Recovered carts', 'chatbotistic' ) ), array( '−40%', __( 'Support tickets', 'chatbotistic' ) ), array( '24/7', __( 'Pre-sales answers', 'chatbotistic' ) ) ),
			'plan' => 'Pro', 'plan_note' => __( 'AI assistant, WooCommerce, and WhatsApp.', 'chatbotistic' ),
			'faqs' => array(
				array( __( 'Does it work with WooCommerce?', 'chatbotistic' ), __( 'Yes — order lookup, cart recovery, and product recommendations work out of the box.', 'chatbotistic' ) ),
				array( __( 'Can it recover abandoned carts?', 'chatbotistic' ), __( 'Yes, automated WhatsApp or email nudges fire when a cart is abandoned.', 'chatbotistic' ) ),
				array( __( 'Is the AI trained on my products?', 'chatbotistic' ), __( 'Yes, it learns from your catalog, policies, and FAQs.', 'chatbotistic' ) ),
			),
		),
		'wordpress-sites' => array(
			'icon' => 'wp', 'title' => __( 'WordPress freelancers', 'chatbotistic' ), 'market' => __( 'WordPress builders', 'chatbotistic' ),
			'problem' => __( 'Building one-off chat scripts for every client project.', 'chatbotistic' ),
			'solution' => __( 'One platform you embed in every build. Recurring revenue.', 'chatbotistic' ),
			'feats' => array( 'WP plugin', 'Shortcode', 'Reseller' ),
			'hero' => __( 'Add chat, WhatsApp, and booking to every build you ship.', 'chatbotistic' ),
			'sub' => __( 'Install one native plugin, embed via shortcode, and turn every WordPress project into recurring revenue instead of a one-off.', 'chatbotistic' ),
			'pains' => array(
				array( __( 'One-off scripts every project', 'chatbotistic' ), __( 'You re-wire chat and forms by hand for each client, with no reuse.', 'chatbotistic' ) ),
				array( __( 'No recurring revenue', 'chatbotistic' ), __( 'Builds are one-time invoices, not monthly income.', 'chatbotistic' ) ),
				array( __( 'Maintenance headaches', 'chatbotistic' ), __( 'Custom scripts break on updates and you’re the one fixing them.', 'chatbotistic' ) ),
			),
			'helps' => array(
				array( __( 'Native WordPress plugin', 'chatbotistic' ), __( 'Install, paste your key, and manage widgets from the WP admin.', 'chatbotistic' ) ),
				array( __( 'Shortcode embed', 'chatbotistic' ), __( 'Drop a widget anywhere with a shortcode or block — no theme edits.', 'chatbotistic' ) ),
				array( __( 'Reseller-ready', 'chatbotistic' ), __( 'Bundle it into retainers and resell under the Agency white-label plan.', 'chatbotistic' ) ),
				array( __( 'WooCommerce compatible', 'chatbotistic' ), __( 'Order lookup and cart recovery for client stores out of the box.', 'chatbotistic' ) ),
			),
			'flow' => array( __( 'Install the plugin', 'chatbotistic' ), __( 'Paste the account key', 'chatbotistic' ), __( 'Embed via shortcode', 'chatbotistic' ), __( 'Hand off to the client', 'chatbotistic' ), __( 'Bill a monthly retainer', 'chatbotistic' ) ),
			'modules' => array( 'WordPress Connector', 'White Label Dashboard', 'AI Chatbot', 'Booking Forms' ),
			'metrics' => array( array( '1 plugin', __( 'Across every client', 'chatbotistic' ) ), array( '5 min', __( 'To go live per site', 'chatbotistic' ) ), array( '$', __( 'New recurring revenue', 'chatbotistic' ) ) ),
			'plan' => 'Agency', 'plan_note' => __( 'White-label and unlimited client widgets.', 'chatbotistic' ),
			'faqs' => array(
				array( __( 'Is it really native to WordPress?', 'chatbotistic' ), __( 'Yes — Chatbotistic is part of the WordPressistic ecosystem with a lightweight, theme-respecting plugin.', 'chatbotistic' ) ),
				array( __( 'Can I resell to clients?', 'chatbotistic' ), __( 'Yes, the Agency plan white-labels the dashboard and widgets for reselling.', 'chatbotistic' ) ),
				array( __( 'Does it support WooCommerce?', 'chatbotistic' ), __( 'Yes, including order lookup and cart recovery.', 'chatbotistic' ) ),
			),
		),
		'saas-founders' => array(
			'icon' => 'rocket', 'title' => __( 'SaaS founders', 'chatbotistic' ), 'market' => __( 'SaaS & startups', 'chatbotistic' ),
			'problem' => __( 'Trial visitors don’t convert. Onboarding lacks a guide.', 'chatbotistic' ),
			'solution' => __( 'Onboarding bot, qualification, demo booking, free-to-paid prompts.', 'chatbotistic' ),
			'feats' => array( 'Onboarding', 'Demo', 'Trial nudge' ),
			'hero' => __( 'Convert more trials with a guide that never sleeps.', 'chatbotistic' ),
			'sub' => __( 'An onboarding bot guides new signups, qualifies enterprise leads, books demos, and nudges free users toward paid — automatically.', 'chatbotistic' ),
			'pains' => array(
				array( __( 'Trials stall at activation', 'chatbotistic' ), __( 'New users sign up, get lost, and churn before seeing value.', 'chatbotistic' ) ),
				array( __( 'Demo requests slip through', 'chatbotistic' ), __( 'Enterprise leads wait too long for a reply and go cold.', 'chatbotistic' ) ),
				array( __( 'Free users never upgrade', 'chatbotistic' ), __( 'No structured nudge moves them from free to paid.', 'chatbotistic' ) ),
			),
			'helps' => array(
				array( __( 'Onboarding bot', 'chatbotistic' ), __( 'Walks new users to their first win with contextual guidance.', 'chatbotistic' ) ),
				array( __( 'Lead qualification', 'chatbotistic' ), __( 'Routes enterprise-shaped leads to sales, self-serve to docs.', 'chatbotistic' ) ),
				array( __( 'Demo booking', 'chatbotistic' ), __( 'Qualified leads book a demo straight into your calendar.', 'chatbotistic' ) ),
				array( __( 'Free-to-paid prompts', 'chatbotistic' ), __( 'Trigger upgrade prompts based on usage and milestones.', 'chatbotistic' ) ),
			),
			'flow' => array( __( 'User signs up', 'chatbotistic' ), __( 'Bot guides activation', 'chatbotistic' ), __( 'Lead is qualified', 'chatbotistic' ), __( 'Demo booked or self-served', 'chatbotistic' ), __( 'Upgrade prompt at the right moment', 'chatbotistic' ) ),
			'modules' => array( 'AI Chatbot', 'Booking Forms', 'Lead Capture', 'Analytics' ),
			'metrics' => array( array( '+28%', __( 'Trial activation', 'chatbotistic' ) ), array( '−35%', __( 'Time to demo', 'chatbotistic' ) ), array( '+15%', __( 'Free-to-paid', 'chatbotistic' ) ) ),
			'plan' => 'Pro', 'plan_note' => __( 'Onboarding bot, qualification, and demo booking.', 'chatbotistic' ),
			'faqs' => array(
				array( __( 'Can it qualify enterprise leads?', 'chatbotistic' ), __( 'Yes — score and route high-value leads to sales while self-serve users get docs and guidance.', 'chatbotistic' ) ),
				array( __( 'Can it book demos?', 'chatbotistic' ), __( 'Yes, qualified leads book straight into a synced calendar.', 'chatbotistic' ) ),
				array( __( 'Does it integrate with my stack?', 'chatbotistic' ), __( 'Yes, via CRM integrations, API, and webhooks.', 'chatbotistic' ) ),
			),
		),
		'real-estate' => array(
			'icon' => 'home', 'title' => __( 'Real estate', 'chatbotistic' ), 'market' => __( 'Real estate & property', 'chatbotistic' ),
			'problem' => __( 'Hot property leads going cold while sitting in an inbox.', 'chatbotistic' ),
			'solution' => __( 'Instant WhatsApp handoff, property tagging, agent alerts.', 'chatbotistic' ),
			'feats' => array( 'WhatsApp', 'Tagging', 'Alerts' ),
			'hero' => __( 'Respond to property leads in seconds, not hours.', 'chatbotistic' ),
			'sub' => __( 'Hand hot leads straight to an agent on WhatsApp, tag the property they’re interested in, and alert the right person instantly.', 'chatbotistic' ),
			'pains' => array(
				array( __( 'Hot leads go cold fast', 'chatbotistic' ), __( 'Property buyers move quickly; a slow reply means a lost sale.', 'chatbotistic' ) ),
				array( __( 'No property context', 'chatbotistic' ), __( 'Leads arrive without the listing they were viewing attached.', 'chatbotistic' ) ),
				array( __( 'Agents miss the alert', 'chatbotistic' ), __( 'Enquiries sit in a shared inbox no one is watching.', 'chatbotistic' ) ),
			),
			'helps' => array(
				array( __( 'Instant WhatsApp handoff', 'chatbotistic' ), __( 'Move a hot lead into a WhatsApp chat with an agent in one tap.', 'chatbotistic' ) ),
				array( __( 'Property tagging', 'chatbotistic' ), __( 'Capture and tag the exact listing the buyer is enquiring about.', 'chatbotistic' ) ),
				array( __( 'Agent alerts', 'chatbotistic' ), __( 'The right agent is notified the instant a qualified lead comes in.', 'chatbotistic' ) ),
				array( __( 'Viewing booking', 'chatbotistic' ), __( 'Let buyers book a viewing slot inside the conversation.', 'chatbotistic' ) ),
			),
			'flow' => array( __( 'Buyer enquires on a listing', 'chatbotistic' ), __( 'Property is tagged automatically', 'chatbotistic' ), __( 'Lead handed to an agent', 'chatbotistic' ), __( 'Agent alerted instantly', 'chatbotistic' ), __( 'Viewing booked in chat', 'chatbotistic' ) ),
			'modules' => array( 'WhatsApp Widget', 'Lead Capture', 'Booking Forms', 'Email Notifications' ),
			'metrics' => array( array( '< 1 min', __( 'Lead response time', 'chatbotistic' ) ), array( '100%', __( 'Leads tagged to a listing', 'chatbotistic' ) ), array( '+35%', __( 'Viewings booked', 'chatbotistic' ) ) ),
			'plan' => 'Pro', 'plan_note' => __( 'WhatsApp handoff, tagging, and booking.', 'chatbotistic' ),
			'faqs' => array(
				array( __( 'Can leads be tagged to a property?', 'chatbotistic' ), __( 'Yes — the widget captures the listing context and tags the lead automatically.', 'chatbotistic' ) ),
				array( __( 'Can I route to specific agents?', 'chatbotistic' ), __( 'Yes, route by area, price band, or availability to the right agent.', 'chatbotistic' ) ),
				array( __( 'Can buyers book viewings?', 'chatbotistic' ), __( 'Yes, viewings can be booked directly inside the chat flow.', 'chatbotistic' ) ),
			),
		),
		'coaches-consultants' => array(
			'icon' => 'chart', 'title' => __( 'Coaches & Consultants', 'chatbotistic' ), 'market' => __( 'Coaches & consultants', 'chatbotistic' ),
			'problem' => __( 'Low-quality leads, manual discovery scheduling.', 'chatbotistic' ),
			'solution' => __( 'AI pre-qualifier, strategy call booking, payment in-flow.', 'chatbotistic' ),
			'feats' => array( 'Qualifier', 'Booking', 'Payments' ),
			'hero' => __( 'Only get on calls with people worth your time.', 'chatbotistic' ),
			'sub' => __( 'An AI pre-qualifier filters tyre-kickers, books strategy calls with the right fit, and can even collect payment before the session.', 'chatbotistic' ),
			'pains' => array(
				array( __( 'Discovery calls with bad-fit leads', 'chatbotistic' ), __( 'Hours lost on calls that were never going to convert.', 'chatbotistic' ) ),
				array( __( 'Manual scheduling back-and-forth', 'chatbotistic' ), __( 'Trading emails to find a time kills momentum.', 'chatbotistic' ) ),
				array( __( 'Unpaid no-shows', 'chatbotistic' ), __( 'Free calls get booked and ghosted with no commitment.', 'chatbotistic' ) ),
			),
			'helps' => array(
				array( __( 'AI pre-qualifier', 'chatbotistic' ), __( 'Scores budget, intent, and fit before anything lands on your calendar.', 'chatbotistic' ) ),
				array( __( 'Strategy call booking', 'chatbotistic' ), __( 'Qualified leads book straight into your synced calendar.', 'chatbotistic' ) ),
				array( __( 'Payment in-flow', 'chatbotistic' ), __( 'Charge for paid consultations inside the booking conversation.', 'chatbotistic' ) ),
				array( __( 'Landing pages with chat', 'chatbotistic' ), __( 'Spin up campaign pages with the qualifier embedded for ads.', 'chatbotistic' ) ),
			),
			'flow' => array( __( 'Lead answers qualifier', 'chatbotistic' ), __( 'Fit + budget are scored', 'chatbotistic' ), __( 'Good fits book a call', 'chatbotistic' ), __( 'Optional payment taken', 'chatbotistic' ), __( 'Call lands on your calendar', 'chatbotistic' ) ),
			'modules' => array( 'AI Chatbot', 'Booking Forms', 'Payment Forms', 'Landing Pages' ),
			'metrics' => array( array( '+70%', __( 'Call show rate', 'chatbotistic' ) ), array( '−80%', __( 'Time on bad-fit calls', 'chatbotistic' ) ), array( '2×', __( 'Qualified bookings', 'chatbotistic' ) ) ),
			'plan' => 'Pro', 'plan_note' => __( 'Qualifier, booking, and in-flow payments.', 'chatbotistic' ),
			'faqs' => array(
				array( __( 'Can I charge for calls?', 'chatbotistic' ), __( 'Yes — take a payment via Stripe or PayPal as part of the booking flow.', 'chatbotistic' ) ),
				array( __( 'How does qualification work?', 'chatbotistic' ), __( 'You define the questions and scoring; only leads above your threshold get a booking link.', 'chatbotistic' ) ),
				array( __( 'Can I use it for ads?', 'chatbotistic' ), __( 'Yes, embed the qualifier on a landing page and point campaigns straight to it.', 'chatbotistic' ) ),
			),
		),
		'travel-agencies' => array(
			'icon' => 'plane', 'title' => __( 'Travel agencies', 'chatbotistic' ), 'market' => __( 'Travel & tourism', 'chatbotistic' ),
			'problem' => __( 'Trip enquiries scattered across channels, slow quoting.', 'chatbotistic' ),
			'solution' => __( 'Multi-channel inbox, custom intake, agent assignment.', 'chatbotistic' ),
			'feats' => array( 'Multi-channel', 'Profiles', 'Routing' ),
			'hero' => __( 'Capture every trip enquiry, quote faster, close more.', 'chatbotistic' ),
			'sub' => __( 'Pull enquiries from web, WhatsApp, and forms into one inbox, capture trip details with a guided intake, and route to the right agent instantly.', 'chatbotistic' ),
			'pains' => array(
				array( __( 'Enquiries scattered everywhere', 'chatbotistic' ), __( 'Web chat, WhatsApp, email, and DMs — no single view of a traveller.', 'chatbotistic' ) ),
				array( __( 'Slow quoting loses bookings', 'chatbotistic' ), __( 'By the time you reply with a quote, they’ve booked elsewhere.', 'chatbotistic' ) ),
				array( __( 'No traveller context', 'chatbotistic' ), __( 'Agents start from scratch every time instead of seeing history.', 'chatbotistic' ) ),
			),
			'helps' => array(
				array( __( 'Multi-channel inbox', 'chatbotistic' ), __( 'Web, WhatsApp, and forms unified into one team inbox.', 'chatbotistic' ) ),
				array( __( 'Custom trip intake', 'chatbotistic' ), __( 'Capture dates, party size, destination, and budget up front.', 'chatbotistic' ) ),
				array( __( 'Agent assignment', 'chatbotistic' ), __( 'Route enquiries to the right specialist by destination or value.', 'chatbotistic' ) ),
				array( __( 'Traveller profiles', 'chatbotistic' ), __( 'Auto-built profiles with history, tags, and last touch.', 'chatbotistic' ) ),
			),
			'flow' => array( __( 'Traveller enquires anywhere', 'chatbotistic' ), __( 'Guided intake captures the trip', 'chatbotistic' ), __( 'Assigned to a specialist', 'chatbotistic' ), __( 'Agent quotes with full context', 'chatbotistic' ), __( 'Follow-up reminders fire', 'chatbotistic' ) ),
			'modules' => array( 'Multi-Agent Inbox', 'Chat Forms', 'Customer Profiles', 'WhatsApp Widget' ),
			'metrics' => array( array( '1 inbox', __( 'Across every channel', 'chatbotistic' ) ), array( '−40%', __( 'Time to first quote', 'chatbotistic' ) ), array( '+25%', __( 'Enquiry-to-booking', 'chatbotistic' ) ) ),
			'plan' => 'Agency', 'plan_note' => __( 'Team agents, multi-channel inbox, and routing.', 'chatbotistic' ),
			'faqs' => array(
				array( __( 'Can multiple agents share the inbox?', 'chatbotistic' ), __( 'Yes — add team agents with assignment, mentions, and internal notes.', 'chatbotistic' ) ),
				array( __( 'Can I capture custom trip details?', 'chatbotistic' ), __( 'Build any intake flow you like — dates, destinations, budget, party size.', 'chatbotistic' ) ),
				array( __( 'Does WhatsApp feed the same inbox?', 'chatbotistic' ), __( 'Yes, WhatsApp conversations land in the same unified inbox.', 'chatbotistic' ) ),
			),
		),
	);
	return $data;
}

/**
 * Return a product entry by slug, or null.
 */
function cb_v4_product( $slug ) {
	$all = cb_v4_products();
	return $all[ $slug ] ?? null;
}

/**
 * Return all product entries keyed by slug.
 */
function cb_v4_products() {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}
	$data = array(
		'whatsapp-automation' => array(
			'eyebrow' => __( 'WhatsApp automation', 'chatbotistic' ),
			'title'   => __( 'The fastest channel your buyers prefer — fully automated.', 'chatbotistic' ),
			'sub'     => __( 'Pre-chat capture, department routing, auto-replies, business hours, and CRM sync — built around the world’s most-used messenger.', 'chatbotistic' ),
			'split_title' => __( 'Capture the lead before WhatsApp even opens.', 'chatbotistic' ),
			'split_lead'  => __( 'Most click-to-chat buttons lose the lead the moment the buyer leaves your site. Chatbotistic captures name, phone, and intent first — then opens WhatsApp with full context.', 'chatbotistic' ),
			'grid_title'  => __( 'Every WhatsApp feature you need', 'chatbotistic' ),
			'cta_title'   => __( 'Turn WhatsApp into your highest-converting channel.', 'chatbotistic' ),
			'feats' => array(
				array( __( 'WhatsApp Widget', 'chatbotistic' ), __( 'Pinned to your site with a beautiful pre-chat experience.', 'chatbotistic' ) ),
				array( __( 'Click-to-chat', 'chatbotistic' ), __( 'One tap on mobile. The buyer is in WhatsApp in under a second.', 'chatbotistic' ) ),
				array( __( 'Pre-chat lead capture', 'chatbotistic' ), __( 'Get name, phone, and intent before WhatsApp opens.', 'chatbotistic' ) ),
				array( __( 'Department routing', 'chatbotistic' ), __( 'Sales, support, billing — route to the right team automatically.', 'chatbotistic' ) ),
				array( __( 'Business hours', 'chatbotistic' ), __( 'Different replies for open hours, after hours, and holidays.', 'chatbotistic' ) ),
				array( __( 'Auto-reply', 'chatbotistic' ), __( 'Instant replies with templates, variables, and follow-up rules.', 'chatbotistic' ) ),
				array( __( 'CRM capture', 'chatbotistic' ), __( 'Every WhatsApp chat lands as a structured lead with context.', 'chatbotistic' ) ),
				array( __( 'Booking through WhatsApp', 'chatbotistic' ), __( 'Send a calendar link inside the conversation flow.', 'chatbotistic' ) ),
				array( __( 'Mobile-first design', 'chatbotistic' ), __( 'Built for the 70% of buyers who never leave their phone.', 'chatbotistic' ) ),
			),
		),
		'ai-chatbot' => array(
			'eyebrow' => __( 'AI Chatbot', 'chatbotistic' ),
			'title'   => __( 'An AI assistant that knows your business. And sells like it.', 'chatbotistic' ),
			'sub'     => __( 'Trained on your site, docs, and product. Qualifies, books, and recommends — 24/7.', 'chatbotistic' ),
			'split_title' => __( 'More than answers. Outcomes.', 'chatbotistic' ),
			'split_lead'  => __( 'Plug Chatbotistic into your site and within minutes you have an AI agent that explains your services, qualifies leads, books calls, and escalates the complex stuff.', 'chatbotistic' ),
			'grid_title'  => __( 'Built for serious conversation work', 'chatbotistic' ),
			'cta_title'   => __( 'Deploy your AI assistant in under 60 seconds.', 'chatbotistic' ),
			'feats' => array(
				array( __( 'AI website assistant', 'chatbotistic' ), __( 'Trained on your site, docs, and FAQs. Answers in your tone of voice.', 'chatbotistic' ) ),
				array( __( 'FAQ automation', 'chatbotistic' ), __( 'Resolve the 80% of repeat questions before they reach a human.', 'chatbotistic' ) ),
				array( __( 'Lead qualification', 'chatbotistic' ), __( 'Branching logic that scores intent and budget before notifying you.', 'chatbotistic' ) ),
				array( __( 'Service recommendations', 'chatbotistic' ), __( 'Suggest the right package based on the buyer’s stated needs.', 'chatbotistic' ) ),
				array( __( 'Booking guidance', 'chatbotistic' ), __( 'Move qualified leads straight into a calendar booking.', 'chatbotistic' ) ),
				array( __( 'Contact collection', 'chatbotistic' ), __( 'Capture email, phone, and consent inside the conversation.', 'chatbotistic' ) ),
				array( __( 'Human handoff', 'chatbotistic' ), __( 'Seamless escalation to a live agent — full context preserved.', 'chatbotistic' ) ),
				array( __( 'Knowledge base style', 'chatbotistic' ), __( 'Long-form answers with citations to your help docs.', 'chatbotistic' ) ),
				array( __( 'Multi-language', 'chatbotistic' ), __( 'Auto-detects the buyer’s language and replies natively.', 'chatbotistic' ) ),
			),
		),
		'booking-forms' => array(
			'eyebrow' => __( 'Booking forms', 'chatbotistic' ),
			'title'   => __( 'Booking inside chat. Calendar synced. Payments collected.', 'chatbotistic' ),
			'sub'     => __( 'The fastest path from website visitor to paid appointment. Built for service businesses, clinics, and consultants.', 'chatbotistic' ),
			'split_title' => __( 'From visitor to confirmed booking in 30 seconds.', 'chatbotistic' ),
			'split_lead'  => __( 'Embed the booking flow on your site or inside a chat. The buyer picks a slot, fills intake, optionally pays a deposit, and lands as a confirmed appointment in your CRM and calendar.', 'chatbotistic' ),
			'grid_title'  => __( 'Everything booking-related, in one place', 'chatbotistic' ),
			'cta_title'   => __( 'Fill your calendar without lifting a finger.', 'chatbotistic' ),
			'feats' => array(
				array( __( 'Consultation booking', 'chatbotistic' ), __( 'Free or paid consultations with calendar sync and intake.', 'chatbotistic' ) ),
				array( __( 'Service booking', 'chatbotistic' ), __( 'Multi-service, multi-staff scheduling with capacity rules.', 'chatbotistic' ) ),
				array( __( 'Appointment booking', 'chatbotistic' ), __( 'Recurring slots, prep questions, location options.', 'chatbotistic' ) ),
				array( __( 'Strategy calls', 'chatbotistic' ), __( 'Qualifier flow before the slot picker. Only good leads get through.', 'chatbotistic' ) ),
				array( __( 'Payment collection', 'chatbotistic' ), __( 'Stripe or PayPal deposits inside the booking flow.', 'chatbotistic' ) ),
				array( __( 'Email notifications', 'chatbotistic' ), __( 'Confirmations, reminders, and reschedule links — automated.', 'chatbotistic' ) ),
				array( __( 'Lead storage', 'chatbotistic' ), __( 'Every booking becomes a contact in your CRM-style inbox.', 'chatbotistic' ) ),
				array( __( 'Admin dashboard', 'chatbotistic' ), __( 'See today’s schedule, no-shows, revenue, and team utilization.', 'chatbotistic' ) ),
			),
		),
		'agency-white-label' => array(
			'eyebrow' => __( 'Agency & White Label', 'chatbotistic' ),
			'title'   => __( 'A premium AI platform you can sell as your own.', 'chatbotistic' ),
			'sub'     => __( 'Productize chatbots, WhatsApp, and bookings. Add a recurring revenue stream — without building a SaaS from scratch.', 'chatbotistic' ),
			'split_title' => __( 'Built for agencies that want to scale', 'chatbotistic' ),
			'split_lead'  => __( 'Workspace-per-client, white-label dashboards, isolated billing, and unlimited widgets on the Agency plan. Bill clients monthly, keep the margin, and let Chatbotistic power the platform.', 'chatbotistic' ),
			'grid_title'  => __( 'Built for agencies that want to scale', 'chatbotistic' ),
			'cta_title'   => __( 'Add Chatbotistic to your agency stack — and your invoices.', 'chatbotistic' ),
			'metrics' => array(
				array( __( 'Avg. monthly revenue per client', 'chatbotistic' ), '$240' ),
				array( __( 'Setup time per client widget', 'chatbotistic' ), '< 30 min' ),
				array( __( 'Agency partners onboarded', 'chatbotistic' ), '380+' ),
			),
			'feats' => array(
				array( __( 'Sell chatbot widgets to clients', 'chatbotistic' ), __( 'Productize chatbots, WhatsApp, and booking as a monthly service.', 'chatbotistic' ) ),
				array( __( 'White label dashboard', 'chatbotistic' ), __( 'Your logo, your domain, your client login. We disappear.', 'chatbotistic' ) ),
				array( __( 'Client management', 'chatbotistic' ), __( 'Workspace-per-client with isolated inboxes, billing, and roles.', 'chatbotistic' ) ),
				array( __( 'Unlimited widgets', 'chatbotistic' ), __( 'No per-widget pricing on Agency. Build, launch, repeat.', 'chatbotistic' ) ),
				array( __( 'Custom branding', 'chatbotistic' ), __( 'Theme colors, custom CSS, email templates, and onboarding emails.', 'chatbotistic' ) ),
				array( __( 'API integrations', 'chatbotistic' ), __( 'Connect Chatbotistic into your existing agency stack.', 'chatbotistic' ) ),
				array( __( 'Agency pricing', 'chatbotistic' ), __( 'Volume tiers, lifetime deals, and partner co-marketing.', 'chatbotistic' ) ),
				array( __( 'Client lead management', 'chatbotistic' ), __( 'See every lead across every client from one master dashboard.', 'chatbotistic' ) ),
				array( __( 'Recurring revenue', 'chatbotistic' ), __( 'Bill clients monthly. Keep the margin. We power the platform.', 'chatbotistic' ) ),
			),
		),
		'wordpress-plugin' => array(
			'eyebrow' => __( 'WordPress Plugin Connector', 'chatbotistic' ),
			'title'   => __( 'Native WordPress. Zero code. Full power.', 'chatbotistic' ),
			'sub'     => __( 'Install the plugin, paste your key, and manage every Chatbotistic widget from your WP admin.', 'chatbotistic' ),
			'split_title' => __( 'A WordPress plugin built by WordPress people.', 'chatbotistic' ),
			'split_lead'  => __( 'Chatbotistic is part of the WordPressistic ecosystem — we know WordPress. The plugin is lightweight, secure, and respects your theme. Compatible with PMPro and WooCommerce out of the box.', 'chatbotistic' ),
			'grid_title'  => __( 'Every WordPress feature you need', 'chatbotistic' ),
			'cta_title'   => __( 'The WordPress chatbot plugin built for serious businesses.', 'chatbotistic' ),
			'feats' => array(
				array( __( 'Install WordPress plugin', 'chatbotistic' ), __( 'One-click install from the WP repository. Activate and you’re live.', 'chatbotistic' ) ),
				array( __( 'Connect your account', 'chatbotistic' ), __( 'Paste your API key. The plugin syncs your widgets and inbox automatically.', 'chatbotistic' ) ),
				array( __( 'Manage from WordPress', 'chatbotistic' ), __( 'Edit chatbots, forms, and booking flows from your WP admin.', 'chatbotistic' ) ),
				array( __( 'Easy embed', 'chatbotistic' ), __( 'Use a shortcode or block. No theme edits needed.', 'chatbotistic' ) ),
				array( __( 'Sync leads & bookings', 'chatbotistic' ), __( 'Every conversation lands in WP as a contact or order.', 'chatbotistic' ) ),
				array( __( 'Dashboard shortcode', 'chatbotistic' ), __( 'Embed your client’s analytics dashboard right inside their WP admin.', 'chatbotistic' ) ),
				array( __( 'PMPro compatibility', 'chatbotistic' ), __( 'Membership-aware bots that respond differently to logged-in users.', 'chatbotistic' ) ),
				array( __( 'WooCommerce ready', 'chatbotistic' ), __( 'Order lookup, cart recovery, product recommendations — out of the box.', 'chatbotistic' ) ),
			),
		),
	);
	return $data;
}
