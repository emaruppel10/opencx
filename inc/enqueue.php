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
		 * Widescreen / Widescreen Ex (Adobe Fonts kit yas5ftt).
		 *
		 * Widescreen is not on Google Fonts: a `family=Widescreen` param there
		 * is ignored silently (the stylesheet still returns 200, with only
		 * Inter/Manrope/Roboto in it), so every rule using it fell through to
		 * the system fallback. It is a licensed Adobe kit, so it has to be
		 * served from use.typekit.net.
		 *
		 * The kit exposes both families in 300/400/600:
		 *   widescreen      -> Widescreen      (Regular / Light / SemiBold)
		 *   widescreen-ex   -> Widescreen Ex   (Regular / Light / SemiBold)
		 * which is exactly the set the Figma text styles use.
		 *
		 * Enqueued as a stylesheet rather than a bare <link> so it stays
		 * cache-busted and ordered before the pattern CSS. The kit is
		 * domain-locked on the Adobe side; add every domain that serves this
		 * site (including localhost) in the Adobe Fonts kit settings.
		 */
		wp_enqueue_style(
			'opencx-fonts-widescreen',
			'https://use.typekit.net/yas5ftt.css',
			array(),
			null
		);

		$local_fonts_path = $theme_dir . '/assets/css/fonts.css';
		if ( file_exists( $local_fonts_path ) ) {
			wp_enqueue_style(
				'opencx-fonts-local',
				$theme_uri . '/assets/css/fonts.css',
				array(),
				(string) filemtime( $local_fonts_path )
			);
		}

		/*
		 * Remaining Google-hosted families. Widescreen was dropped from this
		 * query because it never resolved there; see above.
		 */
		wp_enqueue_style(
			'opencx-fonts',
			'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@300;400;500;600;700;800&family=Roboto:wght@400;500;600;700&display=swap',
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
			'navigation'              => '/assets/js/navigation.js',
			'testimonial-carousel'    => '/assets/js/testimonial-carousel.js',
			'stat-count-up'           => '/assets/js/stat-count-up.js',
			'logo-intro'              => '/assets/js/logo-intro.js',
			'why-accordion'           => '/assets/js/why-accordion.js',
			'ecosystem-nav'           => '/assets/js/ecosystem-nav.js',
			'industries-nav'          => '/assets/js/industries-nav.js',
			'industries-preview-loop' => '/assets/js/industries-preview-loop.js',
			'lottie'                  => '/assets/js/lottie.min.js',
			'hero-logo'               => '/assets/js/hero-logo.js',
			'hero-industry-selector'  => '/assets/js/hero-industry-selector.js',
		);

		foreach ( $scripts as $slug => $relative_path ) {
			$script_path = $theme_dir . $relative_path;

			if ( ! file_exists( $script_path ) ) {
				continue;
			}

			$deps = ( 'hero-logo' === $slug ) ? array( 'opencx-lottie' ) : array();

			wp_enqueue_script(
				'opencx-' . $slug,
				$theme_uri . $relative_path,
				$deps,
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

		$fonts_css = $theme_dir . '/assets/css/fonts.css';
		if ( file_exists( $fonts_css ) ) {
			wp_enqueue_style(
				'opencx-fonts-editor',
				get_template_directory_uri() . '/assets/css/fonts.css',
				array(),
				(string) filemtime( $fonts_css )
			);
		}

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
