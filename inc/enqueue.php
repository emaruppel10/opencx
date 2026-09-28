<?php
/**
 * Asset loading.
 *
 * In a block theme there is no classic stylesheet to enqueue: WordPress
 * generates and serves styles from theme.json automatically. This file
 * therefore only handles the small amount of JavaScript that patterns
 * cannot express, plus optional assets for custom blocks.
 *
 * @package OpenCX
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'opencx_enqueue_scripts' ) ) {
	/**
	 * Enqueues front-end scripts.
	 *
	 * @return void
	 */
	function opencx_enqueue_scripts() {
		$theme_uri = get_template_directory_uri();
		$theme_dir = get_template_directory();

		/*
		 * Web fonts.
		 *
		 * theme.json declares the family names and the design tokens, but it
		 * does not fetch the files. The families below are the ones referenced
		 * in theme.json; if they are ever self-hosted under /assets/fonts,
		 * replace this call with a "fontFace" declaration in theme.json and
		 * drop the external request entirely.
		 */
		wp_enqueue_style(
			'opencx-fonts',
			'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@300;400;500;600;700;800&family=Roboto:wght@400;500;600;700&family=Widescreen&display=swap',
			array(),
			null
		);

		/*
		 * View Transitions for the site logo, only when one is set.
		 * Core handles the markup; this just opts in.
		 */
		if ( has_custom_logo() ) {
			add_filter( 'should_load_separate_core_block_assets', '__return_true' );
		}

		/*
		 * Small progressive-enhancement scripts. Pattern markup ships fully
		 * functional in HTML, so every feature must keep working without them.
		 *
		 * Keyed by handle suffix; the version is the file mtime so edits bust
		 * the cache. A missing file is skipped rather than enqueued, which is
		 * what kept `navigation.js` a harmless stub while the nav is CSS-only.
		 */
		$scripts = array(
			'navigation'          => '/assets/js/navigation.js',
			'testimonial-carousel' => '/assets/js/testimonial-carousel.js',
			'stat-count-up'        => '/assets/js/stat-count-up.js',
			'logo-intro'           => '/assets/js/logo-intro.js',
		);

		foreach ( $scripts as $slug => $relative_path ) {
			$script_path = $theme_dir . $relative_path;

			if ( ! file_exists( $script_path ) ) {
				continue;
			}

			wp_enqueue_script(
				'opencx-' . $slug,
				$theme_uri . $relative_path,
				array(),
				(string) filemtime( $script_path ),
				array( 'strategy' => 'defer', 'in_footer' => true )
			);
		}

		/*
		 * Design token aliases. Declared before patterns.css so the real Figma
		 * token names are always defined by the time a rule references them.
		 */
		$tokens_path = $theme_dir . '/assets/css/tokens.css';

		if ( file_exists( $tokens_path ) ) {
			wp_enqueue_style(
				'opencx-tokens',
				$theme_uri . '/assets/css/tokens.css',
				array(),
				(string) filemtime( $tokens_path )
			);
		}

		/*
		 * Escape hatch for the rare pattern that CSS via theme.json cannot
		 * express. Empty by design: it must stay unused unless strictly needed.
		 */
		$styles_path = $theme_dir . '/assets/css/patterns.css';

		if ( file_exists( $styles_path ) ) {
			wp_enqueue_style(
				'opencx-patterns',
				$theme_uri . '/assets/css/patterns.css',
				array( 'opencx-tokens' ),
				(string) filemtime( $styles_path )
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'opencx_enqueue_scripts' );

if ( ! function_exists( 'opencx_enqueue_block_editor_assets' ) ) {
	/**
	 * Loads the front-end stylesheet inside the Site Editor and editor
	 * so patterns look identical while they are being composed.
	 *
	 * @return void
	 */
	function opencx_enqueue_block_editor_assets() {
		$theme_dir = get_template_directory();
		$tokens    = $theme_dir . '/assets/css/tokens.css';
		$path      = $theme_dir . '/assets/css/patterns.css';

		if ( ! file_exists( $path ) ) {
			return;
		}

		$deps = array();

		if ( file_exists( $tokens ) ) {
			wp_enqueue_style(
				'opencx-tokens-editor',
				get_template_directory_uri() . '/assets/css/tokens.css',
				array(),
				(string) filemtime( $tokens )
			);

			$deps = array( 'opencx-tokens-editor' );
		}

		wp_enqueue_style(
			'opencx-patterns-editor',
			get_template_directory_uri() . '/assets/css/patterns.css',
			$deps,
			(string) filemtime( $path )
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'opencx_enqueue_block_editor_assets' );
