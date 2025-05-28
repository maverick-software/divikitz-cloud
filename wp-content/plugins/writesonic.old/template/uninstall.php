<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
 
// drop a custom database table
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}writesonic_credit_transaction");