=== TNP4B ===

- Contributors: Marco Giustini <info@marcogiustini.info>
- Tags: newsletter, buddypress, digest, email, modular, bridge, i18n
- Requires at least: 5.8
- Tested up to: 6.6
- Requires PHP: 7.4
- Stable tag: 1.1.2
- License: GPLv2 or later
- License URI: https://www.gnu.org/licenses/gpl-2.0.html
- Short description: Automatic and modular group newsletters with BuddyPress, The Newsletter Plugin (TNP) and bridges.

== Description ==

TNP4B connects BuddyPress/BuddyBoss with The Newsletter Plugin (TNP) to deliver group‑centric email digests.
It automatically creates a newsletter list for each BuddyPress group, subscribes/unsubscribes members when they join/leave, captures group content via “bridges,” and sends a daily digest through TNP.
It ships with a default /languages folder and a tnp4b.pot translation template for internationalization.

Key features:
- Automatic list creation: a TNP list per BuddyPress group
- Member management: auto‑subscribe/unsubscribe on join/leave
- Content capture: bridges append group‑specific snippets to a buffer
- Daily digests: a WP‑Cron job renders and sends the digest
- Simple HTML template compatible with TNP
- Modular architecture: bridges are auto‑loaded from a dedicated /bridges subfolder
- Internationalization by default: /languages with tnp4b.pot

Note: This plugin includes stubs for TNP API calls; wire them to your Newsletter plugin functions in production.

== Installation ==

1. Upload the `tnp4b` folder to `/wp-content/plugins/`.
2. Activate “TNP4B” from the WordPress Plugins screen.
3. Ensure BuddyPress and The Newsletter Plugin are active.
4. Install bridge installer plugins as needed; they will drop operational files into `tnp4b/bridges/`.
5. TNP4B auto‑loads any bridge file named `tnp4b-*.php` from the `bridges` subfolder.
6. Translations: the plugin ships with `tnpbp/languages/tnp4b.pot`. Use it to create `.po` and `.mo` files for your locale.

== TNP4B Bridges ==

On installation, TNP4B uses a dedicated subfolder:
`/wp-content/plugins/tnp4b/bridges/`

All bridge files (`tnp4b-[pluginname]-*.php`) are stored here and auto‑loaded at runtime.

How it works:
- A bridge installer plugin (e.g., “TNP4B Bridge – Events Manager”) is installed and activated like any WordPress plugin.
- On activation, it creates/writes a bridge file into `/bridges/` (e.g., `tnp4b-events-manager-bridge.php`).
- TNP4B automatically loads the bridge file and includes its content in the daily digest.

== Developer Notes ==

TNP4B bridges are lightweight PHP files that extend the core plugin by hooking into external plugins and appending group‑specific content to the digest buffer.

Naming conventions:
- File names must follow the pattern: `tnp4b-[pluginname]-bridge.php`.
- Place the file inside `/wp-content/plugins/tnp4b/bridges/`.
- TNP4B automatically loads all files matching this pattern.

Hooks:
- Use the target plugin’s native hooks (e.g. `em_event_save` for Events Manager, `bbp_new_topic` for bbPress).
- Inside the hook callback, call `tnp4b_append_to_buffer( $group_id, $html_snippet )` to add content to the digest.

Best practices:
- Keep bridge files minimal: only dependency checks and hook callbacks.
- Always check dependencies before running.
- Escape output with `esc_html()` and `esc_url()` to ensure security.
- Use simple HTML snippets (div, p, a) for maximum compatibility with TNP templates.
- Avoid heavy logic inside the bridge; delegate formatting to TNP4B’s message builder.
- Document the bridge clearly in comments for maintainability.

Example:
A bridge for bbPress can hook into `bbp_new_topic` and append a snippet with the topic title and link to the group digest buffer.

Summary:
- Bridges are modular, auto‑loaded, and easy to create.
- Follow naming, hook, and security best practices.
- Keep code minimal and let TNP4B handle digest formatting.

== Frequently Asked Questions ==

Do I need to manually copy bridge files?
No. Bridge installer plugins automatically write their operational bridge file into `tnp4b/bridges/`.

Can I customize the digest template?
Yes. You can override `tnp4b_render_digest_html()` in a must‑use plugin or a custom loader.

Does this send via The Newsletter Plugin?
Yes. Wire the stubs (`tnp4b_tnp_subscribe_user_to_list`, `tnp4b_tnp_unsubscribe_user_from_list`, and the digest sender call) to your TNP functions.

Is BuddyBoss supported?
BuddyBoss works when its BuddyPress compatibility functions are available.

== Screenshots ==

1. Daily digest email sample (HTML template)
2. Bridges folder structure in `/wp-content/plugins/tnp4b/bridges`
3. Example bridge (Events Manager) adding an event to the buffer
4. Languages folder `/wp-content/plugins/tnp4b/languages/` with `tnp4b.pot` for translations

== Changelog ==

= 1.1.2 =
- Added default `/languages/` folder with `tnp4b.pot` for i18n support
- Improved activation routine to seed the `.pot` template if missing

= 1.1.0 =
- Dedicated `/bridges` subfolder and autoload-only policy from bridges/

= 1.0.2 =
- Ensure bridges folder creation and autoload from core folder
- Document automatic deployment via installer bridge

= 1.0.1 =
- Initial modular loading and buffer/digest scaffolding

== Upgrade Notice ==

= 1.1.2 =
Plugin now ships with `/languages/` and `tnp4b.pot` by default for translations.

