<?php
/**
 * OpenCX functions and definitions.
 *
 * This file is intentionally minimal: it only loads the modular logic
 * files that live in /inc. Every feature is implemented in its own file
 * so each concern stays isolated and testable.
 *
 * @package OpenCX
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/navigation.php';
require_once get_template_directory() . '/inc/custom-post-types.php';
