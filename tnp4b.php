<?php
/**
 * Plugin Name: TNP4B - The Newsletter Plugin for Buddypress groups
 * Plugin URI: https://example.com/tnp4b
 * Description: Group-centric newsletters for BuddyPress/BuddyBoss via The Newsletter Plugin (TNP). Auto-creates lists per group, manages subscriptions, captures content via modular bridges, and sends daily digests. Ships with /languages and tnp4b.pot for translations.
 * Version: 1.1.2
 * Author: Marco Giustini <info@marcogiustini.info>
 * License: GPLv2 or later
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Text Domain: tnp4b
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'TNP4B_VERSION', '1.1.2' );
define( 'TNP4B_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TNP4B_BRIDGES_DIR', TNP4B_PLUGIN_DIR . 'bridges' );
define( 'TNP4B_LANG_DIR', TNP4B_PLUGIN_DIR . 'languages' );

/**
 * Load translations
 */
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'tnp4b', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
});

/**
 * Activation: ensure folders and seed files
 */
register_activation_hook( __FILE__, function() {
    // Create dirs
    if ( ! file_exists( TNP4B_BRIDGES_DIR ) ) wp_mkdir_p( TNP4B_BRIDGES_DIR );
    if ( ! file_exists( TNP4B_LANG_DIR ) ) wp_mkdir_p( TNP4B_LANG_DIR );

    // Seed POT template if missing
    $pot_path = TNP4B_LANG_DIR . '/tnp4b.pot';
    if ( ! file_exists( $pot_path ) ) {
        $pot = "# TNP4B translation template\n"
             . "msgid \"\"\nmsgstr \"\"\n"
             . "\"Project-Id-Version: TNP4B " . TNP4B_VERSION . "\\n\"\n"
             . "\"MIME-Version: 1.0\\n\"\n"
             . "\"Content-Type: text/plain; charset=UTF-8\\n\"\n"
             . "\"Content-Transfer-Encoding: 8bit\\n\"\n"
             . "\"X-Generator: TNP4B\\n\"\n\n"
             . "#: tnp4b.php:1\nmsgid \"Group digest\"\nmsgstr \"\"\n"
             . "#: tnp4b.php:1\nmsgid \"Daily digest\"\nmsgstr \"\"\n";
        file_put_contents( $pot_path, $pot );
    }
});

/**
 * Safe dependency checks (BuddyPress + TNP recommended)
 */
function tnp4b_is_ready() {
    // Adjust these checks according to your environment or plugin availability.
    $bp_ready  = function_exists( 'buddypress' ) || class_exists( 'BuddyPress' );
    $tnp_ready = function_exists( 'newsletter_register_plugin' ) || class_exists( 'Newsletter' );
    return ( $bp_ready && $tnp_ready );
}

/**
 * Autoload bridges (files named tnp4b-*.php)
 */
add_action( 'plugins_loaded', function() {
    if ( is_dir( TNP4B_BRIDGES_DIR ) ) {
        foreach ( glob( TNP4B_BRIDGES_DIR . '/tnp4b-*.php' ) as $bridge_file ) {
            include_once $bridge_file;
        }
    }
});

/**
 * Buffer API: bridges append HTML snippets per group
 */
function tnp4b_append_to_buffer( $group_id, $html_snippet ) {
    $group_id = intval( $group_id );
    if ( $group_id <= 0 ) return;

    $key    = 'tnp4b_buffer_group_' . $group_id;
    $buffer = get_option( $key, array() );

    // Keep HTML safe for email contexts
    $buffer[] = wp_kses_post( $html_snippet );
    update_option( $key, $buffer, false );
}

/**
 * Render digest HTML for a given group
 */
function tnp4b_render_digest_html( $group_id ) {
    $group_id = intval( $group_id );
    $buffer   = get_option( 'tnp4b_buffer_group_' . $group_id, array() );

    ob_start();
    echo '<div style="font-family:system-ui,Segoe UI,Arial,sans-serif">';
    echo '<h2>' . esc_html__( 'Daily digest', 'tnp4b' ) . '</h2>';
    if ( empty( $buffer ) ) {
        echo '<p>' . esc_html__( 'No new items for this group.', 'tnp4b' ) . '</p>';
    } else {
        echo '<ul>';
        foreach ( $buffer as $snippet ) {
            echo '<li>' . $snippet . '</li>';
        }
        echo '</ul>';
    }
    echo '</div>';
    return ob_get_clean();
}

/**
 * Clear buffer after sending
 */
function tnp4b_clear_buffer( $group_id ) {
    delete_option( 'tnp4b_buffer_group_' . intval( $group_id ) );
}

/**
 * Stub: subscribe/unsubscribe users to TNP list mapped to group
 * Replace these with calls to The Newsletter Plugin APIs in production.
 */
function tnp4b_tnp_subscribe_user_to_list( $user_id, $list_id ) {
    // TODO: map to TNP subscription API.
}
function tnp4b_tnp_unsubscribe_user_from_list( $user_id, $list_id ) {
    // TODO: map to TNP unsubscribe API.
}

/**
 * Map BuddyPress group to TNP list ID
 * Implement a real mapping (e.g., via postmeta, options, or custom table).
 */
function tnp4b_get_group_list_id( $group_id ) {
    $list_id = get_option( 'tnp4b_list_group_' . intval( $group_id ) );
    if ( ! $list_id ) {
        // Create+store a new mapping here (stub).
        $list_id = wp_generate_uuid4();
        update_option( 'tnp4b_list_group_' . intval( $group_id ), $list_id, false );
    }
    return $list_id;
}

/**
 * BuddyPress hooks: auto-manage membership subscriptions (stubs)
 */
add_action( 'bp_groups_join_group', function( $group_id, $user_id ) {
    $list_id = tnp4b_get_group_list_id( $group_id );
    tnp4b_tnp_subscribe_user_to_list( $user_id, $list_id );
}, 10, 2 );

add_action( 'bp_groups_leave_group', function( $group_id, $user_id ) {
    $list_id = tnp4b_get_group_list_id( $group_id );
    tnp4b_tnp_unsubscribe_user_from_list( $user_id, $list_id );
}, 10, 2 );

/**
 * Daily digest via WP-Cron (demo schedule)
 * In production, align scheduling to your requirements.
 */
add_action( 'tnp4b_send_daily_digests', function() {
    if ( ! tnp4b_is_ready() ) return;

    // Fetch all groups (BuddyPress). Replace with real group query.
    $groups = apply_filters( 'tnp4b_groups_list', array() ); // external providers can supply groups

    foreach ( $groups as $group_id ) {
        $html = tnp4b_render_digest_html( $group_id );

        // TODO: send $html via TNP to the mapped list.
        // Example stub: tnp4b_tnp_send_digest( $html, tnp4b_get_group_list_id( $group_id ) );

        tnp4b_clear_buffer( $group_id );
    }
});

/**
 * Activate schedule
 */
register_activation_hook( __FILE__, function() {
    if ( ! wp_next_scheduled( 'tnp4b_send_daily_digests' ) ) {
        wp_schedule_event( time() + 3600, 'daily', 'tnp4b_send_daily_digests' );
    }
});

/**
 * Deactivate schedule
 */
register_deactivation_hook( __FILE__, function() {
    $timestamp = wp_next_scheduled( 'tnp4b_send_daily_digests' );
    if ( $timestamp ) {
        wp_unschedule_event( $timestamp, 'tnp4b_send_daily_digests' );
    }
});
