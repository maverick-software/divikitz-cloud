<?php
// Before removing this file, please verify the PHP ini setting `auto_prepend_file` does not point to this.

// This file was the current value of auto_prepend_file during the Wordfence WAF installation (Sat, 27 Nov 2021 06:46:35 +0000)
if (file_exists('/home/782565.cloudwaysapps.com/axyrnvjrrf/public_html/malcare-waf.php')) {
	include_once '/home/782565.cloudwaysapps.com/axyrnvjrrf/public_html/malcare-waf.php';
}
if (file_exists(__DIR__.'/wp-content/plugins/wordfence/waf/bootstrap.php')) {
	define("WFWAF_LOG_PATH", __DIR__.'/wp-content/wflogs/');
	include_once __DIR__.'/wp-content/plugins/wordfence/waf/bootstrap.php';
}