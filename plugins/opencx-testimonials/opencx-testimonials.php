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

OpenCX_Testimonials_Post_Type::init();
OpenCX_Testimonials_Fields::init();
OpenCX_Testimonials_Slider::init();
