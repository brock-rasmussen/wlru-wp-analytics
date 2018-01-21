<?php
require_once 'admin/class-script.php';
require_once 'admin/class-style.php';

/**
 * Easily add scripts to the admin.
 * @param string $handle
 * @param array $args
 * @param array $page
 * @return EZWPZ\Admin\Script
 */
function ezwpz_admin_script($handle, $args = [], $page = []) {
  return new EZWPZ\Admin\Script($handle, $args, $page);
}

/**
 * Easily add stylesheet to the admin.
 * @param string $handle
 * @param array $args
 * @param array $page
 * @return EZWPZ\Admin\Style
 */
function ezwpz_admin_style($handle, $args = [], $page = []) {
  return new EZWPZ\Admin\Style($handle, $args, $page);
}
