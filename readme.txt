=== Insicore Chat Bubble ===
Contributors:      hadenlin
Tags:              contact button, floating button, whatsapp, live chat, click to chat
Requires at least: 5.9
Tested up to:      6.7
Requires PHP:      7.4
Stable tag:        1.0.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

A fully customizable floating contact bubble for WhatsApp, LINE, Messenger, Telegram and more — with analytics, display rules, and a built-in contact form.

== Description ==

**OmniChat Bubble** lets you add a sleek, animated floating button to your WordPress site so visitors can reach you instantly on their preferred messaging platform.

= Key Features =

* **Multi-channel support** — WhatsApp, LINE, Messenger, Facebook, Telegram, Instagram, WeChat, Viber, Zalo, TikTok, LinkedIn, Twitter/X, Skype, Snapchat, Discord, Phone, SMS, Email, and custom links.
* **Live visual builder** — real-time preview of every change as you type, desktop & mobile view.
* **Display rules** — show the bubble only on specific pages, posts, categories, tags, or custom taxonomies.
* **Behavior triggers** — reveal the bubble after a scroll depth, a time delay, or exit intent.
* **Greeting message** — a chat-style popup next to the button to invite visitors to click.
* **Notification badge** — a customizable red-dot indicator on the main button.
* **Animations** — Pulse, Bounce, Float, Shake, Zoom, Tada, Rotate, Glow, and more.
* **Theme presets** — Modern, Glass, Dark, Soft, Neon, Squared, Gradient, Flat.
* **Built-in contact form** — collect Name, Email, Phone, and Message with spam protection (honeypot).
* **Click analytics** — track opens, channel clicks, device breakdown, and top pages.
* **Round-robin agents** — rotate between multiple WhatsApp numbers or contacts automatically.
* **Pre-filled messages** — auto-populate the WhatsApp / SMS / Email message with dynamic tokens like `{page_title}`, `{page_url}`, `{site_name}`.
* **Responsive** — separate position, size, and radius settings for desktop and mobile.
* **Custom CSS** — advanced users can inject their own rules directly.

= No coding required =

Everything is configured through the visual builder in your WordPress admin. Just go to **Contact Bubble → Builder** after activation.

== Installation ==

1. Upload the `omnichat-bubble` folder to the `/wp-content/plugins/` directory, or install directly through the WordPress plugin screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Go to **Contact Bubble → Builder** to configure your channels and design.
4. Click **Save & Publish** — the bubble appears on your site immediately.

== Frequently Asked Questions ==

= Does it work with page caching plugins? =

Yes. The bubble is rendered server-side in the footer and works with all major caching plugins (WP Rocket, LiteSpeed Cache, W3 Total Cache, etc.).

= Can I show the bubble only on certain pages? =

Yes. Open the **Visibility** tab in the builder and add display rules for any combination of pages, posts, categories, tags, or custom taxonomies. Leave it empty to show on every page.

= Is WhatsApp the only channel? =

No. The plugin supports over 15 messaging platforms plus custom links. You can add multiple channels — visitors see a menu of options when they click the bubble.

= Does it slow down my site? =

No. Assets are only loaded when the bubble is active, and the frontend script is minimal (~5 KB). Analytics use `sendBeacon` so they never block navigation.

= Where are form submissions stored? =

In a custom database table (`wp_pcb_submissions`). You can view, mark as read, and delete them under **Contact Bubble → Submissions**.

= Does the built-in contact form send email notifications? =

Not in the current version. Submissions are saved to the database and viewable in the admin. Email notification support is planned for a future release.

== Screenshots ==

1. Live builder — Channels tab with quick-add presets.
2. Live builder — Design tab with real-time preview.
3. Visibility tab — display rules for pages and taxonomies.
4. Analytics page — clicks by channel, device, and top pages.
5. Submissions page — contact form entries.
6. Frontend — the floating bubble with expanded channel menu.

== Privacy ==

OmniChat Bubble collects and stores the following personal data on your WordPress site:

**Contact Form Submissions:** When a visitor submits the built-in contact form, the plugin stores the visitor's name, email address, phone number, message, the URL of the page where the form was submitted, and their IP address. This data is saved to a custom database table (`{prefix}pcb_submissions`) and is accessible only to site administrators under **Contact Bubble → Submissions**.

**Click Analytics:** When a visitor interacts with the bubble, the plugin records the event type, channel clicked, page URL, and device type (mobile or desktop) in a custom table (`{prefix}pcb_events`). IP addresses are used transiently for rate-limiting purposes via WordPress transients (maximum lifetime: 60 seconds) and are not permanently stored in the analytics table.

Site owners are responsible for disclosing this data collection to their visitors in their own Privacy Policy. OmniChat Bubble supports WordPress's built-in **Tools → Export Personal Data** and **Tools → Erase Personal Data** features for all contact form submissions.

== Changelog ==

= 1.0.0 =
* Initial public release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
