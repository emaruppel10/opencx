<?php
/**
 * OpenCX Editorial Studio & Employee Portal Logic.
 *
 * Handles access control, session authentication, user attribution,
 * and unified content submission for Blog, Newsroom, and Client Perspectives.
 *
 * @package OpenCX
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enforce authentication on the Employee Portal page.
 * Unauthenticated users are redirected directly to wp-login with redirect_to.
 */
function opencx_protect_portal_access() {
	if ( is_page( 'portal' ) && ! is_user_logged_in() ) {
		wp_redirect( wp_login_url( home_url( '/portal/' ) ) );
		exit;
	}
}
add_action( 'template_redirect', 'opencx_protect_portal_access' );

/**
 * Output logged-in user credentials and REST nonce for the portal scripts.
 */
function opencx_output_portal_auth_context() {
	if ( is_page( 'portal' ) && is_user_logged_in() ) {
		$user = wp_get_current_user();
		$user_data = array(
			'id'          => $user->ID,
			'login'       => $user->user_login,
			'name'        => $user->display_name ?: $user->user_login,
			'email'       => $user->user_email,
			'avatar'      => get_avatar_url( $user->ID, array( 'size' => 80 ) ),
			'roles'       => (array) $user->roles,
			'role_label'  => ! empty( $user->roles ) ? ucfirst( $user->roles[0] ) : 'Staff',
			'nonce'       => wp_create_nonce( 'wp_rest' ),
			'rest_url'    => esc_url_raw( rest_url() ),
			'logout_url'  => wp_logout_url( home_url( '/portal/' ) ),
		);

		echo '<script id="opencx-portal-auth-data">window.OpenCX_User = ' . wp_json_encode( $user_data ) . ';</script>' . "\n";
	}
}
add_action( 'wp_footer', 'opencx_output_portal_auth_context', 5 );

/**
 * Register custom REST API routes for secure portal content submission and media uploads.
 */
function opencx_register_portal_rest_routes() {
	register_rest_route(
		'opencx/v1',
		'/submit-content',
		array(
			'methods'             => 'POST',
			'callback'            => 'opencx_handle_portal_submission',
			'permission_callback' => function() {
				return is_user_logged_in();
			},
		)
	);

	register_rest_route(
		'opencx/v1',
		'/upload-media',
		array(
			'methods'             => 'POST',
			'callback'            => 'opencx_handle_portal_media_upload',
			'permission_callback' => function() {
				return is_user_logged_in();
			},
		)
	);
}
add_action( 'rest_api_init', 'opencx_register_portal_rest_routes' );

/**
 * Handles uploading photos, videos, and podcast/audio files from the Portal editor.
 *
 * @param WP_REST_Request $request REST request object.
 * @return WP_REST_Response
 */
function opencx_handle_portal_media_upload( $request ) {
	$current_user_id = get_current_user_id();
	if ( ! $current_user_id ) {
		return new WP_REST_Response( array( 'error' => 'Unauthorized: Please log in.' ), 401 );
	}

	if ( empty( $_FILES['media_file'] ) || empty( $_FILES['media_file']['name'] ) ) {
		return new WP_REST_Response( array( 'error' => 'No file was provided for upload.' ), 400 );
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$file = $_FILES['media_file'];

	// Determine file type category (image, video, audio)
	$file_type = wp_check_filetype( $file['name'] );
	$mime = $file_type['type'] ?? '';
	$type = 'unknown';

	if ( strpos( $mime, 'image/' ) === 0 ) {
		$type = 'image';
	} elseif ( strpos( $mime, 'video/' ) === 0 ) {
		$type = 'video';
	} elseif ( strpos( $mime, 'audio/' ) === 0 ) {
		$type = 'audio';
	}

	$attachment_id = media_handle_upload( 'media_file', 0, array(
		'post_author' => $current_user_id,
	) );

	if ( is_wp_error( $attachment_id ) ) {
		return new WP_REST_Response( array( 'error' => $attachment_id->get_error_message() ), 500 );
	}

	$url = wp_get_attachment_url( $attachment_id );

	return new WP_REST_Response(
		array(
			'success'  => true,
			'id'       => $attachment_id,
			'url'      => $url,
			'type'     => $type,
			'mime'     => $mime,
			'filename' => sanitize_file_name( $file['name'] ),
		),
		200
	);
}

/**
 * Handles employee submission of Blog, Newsroom, and Client Perspective content.
 *
 * @param WP_REST_Request $request REST request object.
 * @return WP_REST_Response
 */
function opencx_handle_portal_submission( $request ) {
	$current_user_id = get_current_user_id();
	if ( ! $current_user_id ) {
		return new WP_REST_Response( array( 'error' => 'Unauthorized: Please log in.' ), 401 );
	}

	$params  = $request->get_params();
	$channel = sanitize_key( $params['channel'] ?? 'blog' );
	$status  = ( isset( $params['status'] ) && 'draft' === $params['status'] ) ? 'draft' : 'pending';
	$title   = sanitize_text_field( $params['title'] ?? '' );
	$content = wp_kses_post( $params['content'] ?? '' );
	$excerpt = sanitize_textarea_field( $params['excerpt'] ?? '' );

	if ( empty( $title ) ) {
		return new WP_REST_Response( array( 'error' => 'A headline or title is required.' ), 400 );
	}

	// Determine post type according to channel
	$post_type = 'post';
	if ( 'newsroom' === $channel ) {
		$post_type = 'opencx_news';
	} elseif ( 'client-perspective' === $channel ) {
		$post_type = 'opencx_interview';
	}

	$post_data = array(
		'post_title'   => $title,
		'post_content' => $content,
		'post_excerpt' => $excerpt,
		'post_status'  => $status,
		'post_type'    => $post_type,
		'post_author'  => $current_user_id,
	);

	$post_id = wp_insert_post( $post_data, true );
	if ( is_wp_error( $post_id ) ) {
		return new WP_REST_Response( array( 'error' => $post_id->get_error_message() ), 500 );
	}

	// Handle File Upload if provided in $_FILES
	if ( ! empty( $_FILES['cover_artwork'] ) && ! empty( $_FILES['cover_artwork']['name'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$attachment_id = media_handle_upload( 'cover_artwork', $post_id );
		if ( ! is_wp_error( $attachment_id ) ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}
	}

	// Channel-specific meta storage
	if ( 'blog' === $channel ) {
		$tagline = sanitize_text_field( $params['tagline'] ?? 'Blog' );
		$read_time = sanitize_text_field( $params['read_time'] ?? '5 min read' );
		update_post_meta( $post_id, '_opencx_blog_tagline', $tagline );
		update_post_meta( $post_id, '_opencx_blog_read_time', $read_time );
	} elseif ( 'newsroom' === $channel ) {
		$news_type     = sanitize_text_field( $params['news_type'] ?? 'Press Release' );
		$source        = sanitize_text_field( $params['source'] ?? '' );
		$external_url  = esc_url_raw( $params['external_url'] ?? '' );
		$press_contact = sanitize_email( $params['press_contact'] ?? '' );

		update_post_meta( $post_id, 'opencx_news_type', $news_type );
		update_post_meta( $post_id, 'opencx_news_source', $source );
		update_post_meta( $post_id, 'opencx_news_external_url', $external_url );
		update_post_meta( $post_id, 'opencx_news_press_contact', $press_contact );
		update_post_meta( $post_id, 'opencx_news_date_display', gmdate( 'M Y' ) );
	} elseif ( 'client-perspective' === $channel ) {
		$interviewee_name    = sanitize_text_field( $params['interviewee_name'] ?? '' );
		$interviewee_role    = sanitize_text_field( $params['interviewee_role'] ?? '' );
		$interviewee_company = sanitize_text_field( $params['interviewee_company'] ?? '' );
		$qa_json             = wp_unslash( $params['qa_json'] ?? '[]' );

		update_post_meta( $post_id, 'opencx_interviewee_name', $interviewee_name );
		update_post_meta( $post_id, 'opencx_interviewee_role', $interviewee_role );
		update_post_meta( $post_id, 'opencx_interviewee_company', $interviewee_company );
		update_post_meta( $post_id, 'opencx_interview_qa', $qa_json );
	}

	// Save Tags / Topics (comma-separated)
	$raw_tags = sanitize_text_field( $params['tags'] ?? '' );
	if ( ! empty( $raw_tags ) ) {
		$tag_list = array_map( 'trim', explode( ',', $raw_tags ) );
		$tag_list = array_filter( $tag_list );
		if ( ! empty( $tag_list ) ) {
			wp_set_object_terms( $post_id, $tag_list, 'post_tag', false );
			update_post_meta( $post_id, '_opencx_tags', implode( ', ', $tag_list ) );
		}
	}

	return new WP_REST_Response(
		array(
			'success'     => true,
			'post_id'     => $post_id,
			'post_status' => $status,
			'channel'     => $channel,
			'author_id'   => $current_user_id,
			'author_name' => wp_get_current_user()->display_name,
			'message'     => 'Post successfully saved as ' . ( 'pending' === $status ? 'Pending Review' : 'Draft' ),
		),
		200
	);
}

/**
 * Strict Author Isolation for Contributors:
 * Ensure users without 'edit_others_posts' only see their own content in wp-admin
 * across all post types (Blog, Newsroom, Client Perspective Interviews).
 */
add_action( 'pre_get_posts', function( $query ) {
	if ( is_admin() && $query->is_main_query() && ! current_user_can( 'edit_others_posts' ) ) {
		$query->set( 'author', get_current_user_id() );
	}
} );

/**
 * Filter post status counts (All, Published, Pending, Draft) in wp-admin tables
 * so contributors only see counts of their own articles/interviews.
 */
add_filter( 'wp_count_posts', function( $counts, $type, $perm ) {
	if ( is_admin() && ! current_user_can( 'edit_others_posts' ) ) {
		global $wpdb;
		$user_id = get_current_user_id();
		if ( $user_id ) {
			$cache_key   = 'opencx_user_counts_' . $type . '_' . $user_id;
			$user_counts = wp_cache_get( $cache_key, 'counts' );
			if ( false === $user_counts ) {
				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT post_status, COUNT(*) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s AND post_author = %d GROUP BY post_status",
						$type,
						$user_id
					)
				);
				$user_counts = array_fill_keys( get_post_stati(), 0 );
				foreach ( (array) $results as $row ) {
					$user_counts[ $row->post_status ] = (int) $row->num_posts;
				}
				$user_counts = (object) $user_counts;
				wp_cache_set( $cache_key, $user_counts, 'counts' );
			}
			return $user_counts;
		}
	}
	return $counts;
}, 10, 3 );

/**
 * Restrict Media Library so contributors only see their own uploaded media files.
 */
add_filter( 'ajax_query_attachments_args', function( $query ) {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		$query['author'] = get_current_user_id();
	}
	return $query;
} );
