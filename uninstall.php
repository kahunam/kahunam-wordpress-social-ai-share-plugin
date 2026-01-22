<?php
/**
 * Uninstall handler - Removes plugin data when deleted
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('kaais_settings');
