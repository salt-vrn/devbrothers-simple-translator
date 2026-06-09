<?php
/**
 * Uninstall DevBrothers Simple Translator
 *
 * @package DevBrothers_Simple_Translator
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('devbsitr_settings');

if (is_multisite()) {
    $devbsitr_sites = get_sites(['number' => 999]);

    foreach ($devbsitr_sites as $devbsitr_site) {
        switch_to_blog($devbsitr_site->blog_id);
        delete_option('devbsitr_settings');
        restore_current_blog();
    }
}
