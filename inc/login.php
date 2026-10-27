<?php
/**
 * OpenCX Custom Login Experience.
 *
 * Replaces default WordPress styling with OpenCX design tokens,
 * typography (Widescreen + Manrope), and the official animated Lottie logo.
 *
 * @package OpenCX
 */

defined( 'ABSPATH' ) || exit;

/**
 * Change the login logo URL to point to OpenCX homepage.
 */
add_filter( 'login_headerurl', function() {
	return home_url( '/' );
});

/**
 * Change the login logo tooltip to OpenCX.
 */
add_filter( 'login_headertext', function() {
	return 'OpenCX';
});

/**
 * Enqueue OpenCX typography, design tokens, and Lottie player on wp-login.php.
 */
function opencx_login_assets() {
	wp_enqueue_style( 'opencx-fonts', get_template_directory_uri() . '/assets/css/fonts.css', array(), '1.0.0' );
	wp_enqueue_style( 'opencx-tokens', get_template_directory_uri() . '/assets/css/tokens.css', array( 'opencx-fonts' ), '1.0.0' );
	wp_enqueue_script( 'lottie-web', 'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js', array(), '5.12.2', true );
}
add_action( 'login_enqueue_scripts', 'opencx_login_assets' );

/**
 * Custom CSS for wp-login.php strictly aligned with OpenCX aesthetics.
 */
function opencx_custom_login_styles() {
	?>
	<style type="text/css">
		/* Base Page Styling */
		html, body.login {
			background-color: var(--Color-OpenCX-Dune-Lightest, #F8F6F0) !important;
			background-image: 
				radial-gradient(circle at 10% 20%, rgba(74, 43, 79, 0.04) 0%, transparent 40%),
				radial-gradient(circle at 90% 80%, rgba(182, 158, 82, 0.06) 0%, transparent 40%),
				linear-gradient(rgba(203, 187, 133, 0.12) 1px, transparent 1px),
				linear-gradient(90deg, rgba(203, 187, 133, 0.12) 1px, transparent 1px) !important;
			background-size: 100% 100%, 100% 100%, 32px 32px, 32px 32px !important;
			font-family: var(--wp--preset--font-family--manrope, 'Manrope', sans-serif) !important;
			color: var(--Color-Neutral-Darkest, #000000) !important;
			min-height: 100vh;
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			margin: 0;
			padding: 24px 16px;
			box-sizing: border-box;
		}

		/* Main Login Container */
		#login {
			width: 100% !important;
			max-width: 440px !important;
			padding: 0 !important;
			margin: auto !important;
		}

		/* Remove default WP Logo & Inject Animated Container */
		#login h1 {
			margin-bottom: 20px !important;
		}

		#login h1 a {
			display: none !important;
		}

		.ocx-login-header-wrap {
			text-align: center;
			margin-bottom: 24px;
		}

		.ocx-login-lottie-container {
			width: 220px;
			height: 70px;
			margin: 0 auto 12px auto;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.ocx-login-badge {
			display: inline-block;
			font-family: var(--wp--preset--font-family--widescreen, 'Widescreen', sans-serif);
			font-size: 11px;
			text-transform: uppercase;
			letter-spacing: 0.12em;
			color: var(--Color-Blackberry-Light, #8F459C);
			background: #FFFFFF;
			padding: 4px 12px;
			border-radius: 0px !important;
			border: 0.5px solid var(--Color-OpenCX-Dune-Light, #CBBB85);
			margin-bottom: 8px;
		}

		.ocx-login-subtitle {
			font-family: var(--wp--preset--font-family--widescreen, 'Widescreen', sans-serif);
			font-size: 20px;
			font-weight: 400;
			color: var(--Color-Neutral-Darkest, #000000);
			margin: 2px 0 0 0;
			letter-spacing: -0.01em;
		}

		/* Login Card (Crisp Square Aesthetic) */
		#loginform {
			background: #FFFFFF !important;
			border: var(--Stroke-Hairline, 0.5px) solid var(--Color-OpenCX-Dune-Light, #CBBB85) !important;
			border-radius: 0px !important;
			padding: 36px 32px 32px 32px !important;
			box-shadow: 0 16px 36px rgba(74, 43, 79, 0.06), 0 2px 6px rgba(0, 0, 0, 0.02) !important;
			margin: 0 !important;
		}

		/* Labels & Text Inputs */
		#loginform label {
			font-family: var(--wp--preset--font-family--widescreen, 'Widescreen', sans-serif) !important;
			font-size: 14px !important;
			font-weight: 400 !important;
			color: var(--Color-Neutral-Darkest, #000000) !important;
			margin-bottom: 8px !important;
			display: block !important;
			letter-spacing: -0.01em !important;
		}

		#loginform .input,
		#loginform input[type="text"],
		#loginform input[type="password"] {
			width: 100% !important;
			background: #FAF8F4 !important;
			border: var(--Stroke-Hairline, 0.5px) solid var(--Color-OpenCX-Dune-Light, #CBBB85) !important;
			border-radius: 0px !important;
			padding: 12px 16px !important;
			font-family: var(--wp--preset--font-family--manrope, sans-serif) !important;
			font-size: 15px !important;
			color: var(--Color-Neutral-Darkest, #000000) !important;
			box-sizing: border-box !important;
			outline: none !important;
			margin-top: 6px !important;
			margin-bottom: 18px !important;
			transition: all 0.2s ease !important;
		}

		#loginform input[type="text"]:focus,
		#loginform input[type="password"]:focus {
			background: #FFFFFF !important;
			border-color: var(--Color-Blackberry-Light, #8F459C) !important;
			box-shadow: 0 0 0 2px rgba(143, 69, 156, 0.15) !important;
		}

		/* Remember Me Checkbox */
		.forgetmenot {
			float: none !important;
			margin-bottom: 20px !important;
			display: flex !important;
			align-items: center !important;
		}

		.forgetmenot label {
			font-family: var(--wp--preset--font-family--manrope, sans-serif) !important;
			font-size: 13px !important;
			color: var(--Color-Neutral, #7F7F7F) !important;
			display: inline-flex !important;
			align-items: center !important;
			gap: 8px !important;
			margin: 0 !important;
			cursor: pointer !important;
		}

		#rememberme {
			border-radius: 0px !important;
			border: 0.5px solid var(--Color-OpenCX-Dune-Light, #CBBB85) !important;
			accent-color: var(--Color-Blackberry-Light, #8F459C) !important;
			width: 16px !important;
			height: 16px !important;
			margin: 0 !important;
		}

		/* Submit Button */
		.submit {
			float: none !important;
			margin: 0 !important;
			padding: 0 !important;
		}

		#wp-submit {
			width: 100% !important;
			background: var(--Color-OpenCX-Dune, #B69E52) !important;
			color: var(--Color-OpenCX-White, #FCFBF9) !important;
			font-family: var(--wp--preset--font-family--widescreen, 'Widescreen', sans-serif) !important;
			font-size: 15px !important;
			font-weight: 400 !important;
			padding: 14px 24px !important;
			border-radius: 0px !important;
			border: none !important;
			cursor: pointer !important;
			text-shadow: none !important;
			box-shadow: none !important;
			transition: background-color 0.2s ease, transform 0.15s ease !important;
			height: auto !important;
			line-height: 1.2 !important;
		}

		#wp-submit:hover {
			background: var(--Color-Husk-Dark, #917E41) !important;
			transform: translateY(-1px) !important;
		}

		/* Password reveal button */
		.wp-hide-pw {
			top: 50% !important;
			transform: translateY(-50%) !important;
			color: var(--Color-Neutral, #7F7F7F) !important;
		}

		/* Bottom Links */
		#nav, #backtoblog {
			text-align: center !important;
			margin: 18px 0 0 0 !important;
			padding: 0 !important;
			font-family: var(--wp--preset--font-family--manrope, sans-serif) !important;
			font-size: 13px !important;
		}

		#nav a, #backtoblog a {
			color: var(--Color-Neutral, #7F7F7F) !important;
			text-decoration: none !important;
			transition: color 0.15s ease !important;
		}

		#nav a:hover, #backtoblog a:hover {
			color: var(--Color-Blackberry-Light, #8F459C) !important;
			text-decoration: underline !important;
		}

		/* Notice and Error Messages */
		.login .message,
		.login .notice,
		.login #login_error {
			border-radius: 0px !important;
			padding: 14px 18px !important;
			font-family: var(--wp--preset--font-family--manrope, sans-serif) !important;
			font-size: 13px !important;
			line-height: 1.4 !important;
			margin-bottom: 20px !important;
			box-shadow: none !important;
		}

		.login #login_error {
			border: 0.5px solid #E74C3C !important;
			background-color: #FDEDEC !important;
			color: #922B21 !important;
			border-left-width: 0.5px !important;
		}

		.login .message {
			border: 0.5px solid var(--Color-OpenCX-Dune-Light, #CBBB85) !important;
			background-color: #FAF8F4 !important;
			color: var(--Color-Neutral-Darkest, #000000) !important;
			border-left-width: 0.5px !important;
		}

		/* Privacy policy link */
		.privacy-policy-page-link {
			text-align: center !important;
			margin-top: 14px !important;
		}

		.privacy-policy-page-link a {
			color: var(--Color-Neutral, #7F7F7F) !important;
			font-size: 12px !important;
			text-decoration: none !important;
		}
	</style>
	<?php
}

/**
 * Inject the OpenCX Animated Logo container and script in the login footer.
 */
function opencx_custom_login_scripts() {
	$animation_json_url = get_template_directory_uri() . '/assets/images/logo-animation.json';
	?>
	<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function() {
		// Replace default H1 content with the OpenCX branded header & Lottie animation
		const h1 = document.querySelector('#login h1');
		if (h1) {
			const headerWrap = document.createElement('div');
			headerWrap.className = 'ocx-login-header-wrap';
			headerWrap.innerHTML = `
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="OpenCX Home">
					<div id="opencx-lottie-logo" class="ocx-login-lottie-container"></div>
				</a>
				<span class="ocx-login-badge">Editorial Studio</span>
				<h2 class="ocx-login-subtitle">Sign In to OpenCX</h2>
			`;
			h1.parentNode.insertBefore(headerWrap, h1.nextSibling);
			h1.style.display = 'none';

			// Initialize Lottie Animation
			if (typeof lottie !== 'undefined') {
				lottie.loadAnimation({
					container: document.getElementById('opencx-lottie-logo'),
					renderer: 'svg',
					loop: true,
					autoplay: true,
					path: '<?php echo esc_url( $animation_json_url ); ?>'
				});
			} else {
				// Fallback to static SVG if Lottie is still loading
				document.getElementById('opencx-lottie-logo').innerHTML = `
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-opencx.svg' ); ?>" alt="OpenCX" style="height: 38px; width: auto;" />
				`;
			}
		}

		// Update button label
		const submitBtn = document.getElementById('wp-submit');
		if (submitBtn) {
			submitBtn.value = 'Sign In';
		}
	});
	</script>
	<?php
}
add_action( 'login_head', 'opencx_custom_login_styles' );
add_action( 'login_footer', 'opencx_custom_login_scripts' );

