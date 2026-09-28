<?php
/**
 * Theme setup: feature support, translations and editor behaviour.
 *
 * @package OpenCX
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'opencx_setup' ) ) {
	/**
	 * Registers theme supports.
	 *
	 * Visual design (colors, typography, spacing, layout) is intentionally
	 * NOT declared here: that is the single responsibility of theme.json.
	 * This function only covers capabilities that theme.json cannot express.
	 *
	 * @return void
	 */
	function opencx_setup() {
		/*
		 * Translations live in /languages so gettext stays optional
		 * until the theme is actually localized.
		 */
		load_theme_textdomain( 'opencx', get_template_directory() . '/languages' );

		// WordPress managed features.
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'responsive-embeds' );

		// Makes the block editor canvas inherit the front-end styles.
		add_theme_support( 'editor-styles' );

		// Replaces the Site Editor site title and tagline with a real logo.
		add_theme_support(
			'custom-logo',
			array(
				'height'               => 35,
				'width'                => 147,
				'flex-height'          => true,
				'flex-width'           => true,
				'header-text'          => array( 'site-title', 'site-description' ),
				'unlink-homepage-logo' => false,
			)
		);

		/*
		 * Navigation menus consumed by the core/navigation blocks inside the Header
		 * template part and the two inside the Footer template part.
		 *
		 * `primary` is the header. `footer_a` and `footer_b` are the two link
		 * lists of "Footer / 3 /" (#10244:544), which Figma draws side by side;
		 * each one is a separate menu so they can be reordered from
		 * Appearance -> Menus without touching markup.
		 */
		register_nav_menus(
			array(
				'primary'  => __( 'Primary', 'opencx' ),
				'footer_a' => __( 'Footer A', 'opencx' ),
				'footer_b' => __( 'Footer B', 'opencx' ),
			)
		);

		// Valid HTML5 markup for core output.
		add_theme_support(
			'html5',
			array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
		);

		// Open the navigation overlay right after the responsive breakpoint.
		add_action( 'after_setup_theme', 'opencx_content_width', 0 );
	}

	/**
	 * Sets the global content width used by oEmbeds and legacy images.
	 *
	 * @return void
	 */
	function opencx_content_width() {
		// Matches the "wide" layout width defined in theme.json.
		$GLOBALS['content_width'] = apply_filters( 'opencx_content_width', 1280 );
	}
}
add_action( 'after_setup_theme', 'opencx_setup' );
