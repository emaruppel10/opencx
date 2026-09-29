<?php
/**
 * Plugin Name:       OpenCX Testimonials
 * Plugin URI:        https://github.com/emaruppel10/opencx
 * Description:       Feeds the OpenCX "Testimonial / 23 /" slider from a post type, so the cards are edited in the admin instead of hardcoded in the block templates.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            OpenCX
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       opencx-testimonials
 *
 * @package OpenCX\Testimonials
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin bootstrap.
 *
 * The markup this plugin renders is the theme's: `.ocx-t23__*` classes come from the
 * OpenCX theme's `patterns.css` and the arrows, dots and keyboard support come from its
 * `testimonial-carousel.js`, which the theme already enqueues on every page. The plugin
 * therefore ships no front-end CSS or JS at all and only adds content.
 *
 * That is also why the plugin registers its own post type instead of going through the
 * theme's `opencx_post_types` filter: a plugin that registers content through a theme
 * filter stops existing the moment the theme is swapped, and the content would go with it.
 */
define( 'OPENCX_TESTIMONIALS_VERSION', '0.1.0' );
define( 'OPENCX_TESTIMONIALS_FILE', __FILE__ );
define( 'OPENCX_TESTIMONIALS_DIR', plugin_dir_path( __FILE__ ) );
define( 'OPENCX_TESTIMONIALS_URL', plugin_dir_url( __FILE__ ) );

require_once OPENCX_TESTIMONIALS_DIR . 'includes/class-post-type.php';
require_once OPENCX_TESTIMONIALS_DIR . 'includes/class-fields.php';
require_once OPENCX_TESTIMONIALS_DIR . 'includes/class-slider.php';
require_once OPENCX_TESTIMONIALS_DIR . 'includes/class-story.php';

OpenCX_Testimonials_Post_Type::init();
OpenCX_Testimonials_Fields::init();
OpenCX_Testimonials_Slider::init();
OpenCX_Testimonials_Story::init();

register_activation_hook( OPENCX_TESTIMONIALS_FILE, 'opencx_testimonials_activate' );

if ( ! function_exists( 'opencx_testimonials_activate' ) ) {
	/**
	 * Registers the post type and rebuilds the rewrite rules on activation.
	 *
	 * A custom post type is registered on `init`, but the rewrite rules are stored in the
	 * `rewrite_rules` option and were generated before this plugin existed. Without this
	 * flush, `/testimonials/<slug>/` resolves to a 404 on every site that already had rules
	 * written, while the post type still works everywhere else. That is the worst shape for
	 * a bug: the admin looks right and the links are dead.
	 *
	 * `register()` runs first because the rules are generated from the registered post type.
	 *
	 * @return void
	 */
	function opencx_testimonials_activate() {
		OpenCX_Testimonials_Post_Type::register();
		flush_rewrite_rules();
	}
}

if ( ! function_exists( 'opencx_testimonials_deactivate' ) ) {
	/**
	 * Rebuilds the rewrite rules on deactivation.
	 *
	 * The rules are left in place rather than removed: the post type is only unregistered on
	 * the next request, so expiring them here would 404 every testimonial for one request.
	 *
	 * @return void
	 */
	function opencx_testimonials_deactivate() {
		flush_rewrite_rules();
	}
}

register_deactivation_hook( OPENCX_TESTIMONIALS_FILE, 'opencx_testimonials_deactivate' );
