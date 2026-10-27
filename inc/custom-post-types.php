<?php
/**
 * Custom post types and taxonomies.
 *
 * Kept deliberately declarative: post types are declared as data so the
 * Site Editor, the REST API and any future GraphQL layer see the exact
 * same definitions without duplicating configuration.
 *
 * @package OpenCX
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'opencx_get_post_types' ) ) {
	/**
	 * Returns the post types owned by the theme.
	 *
	 * Currently empty on purpose: no custom post type has been requested
	 * yet, and shipping an unused CPT would expose it in the admin menu
	 * and the Site Editor templates for no benefit.
	 *
	 * Expected shape for each entry:
	 *
	 *     array(
	 *         'slug'        => 'case-study',
	 *         'labels'      => array( 'name' => __( 'Case Studies', 'opencx' ) ),
	 *         'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
	 *         'has_archive' => true,
	 *         'rewrite'     => array( 'slug' => 'case-study' ),
	 *         'icon'        => 'portfolio',
	 *         'taxonomies'  => array( 'industry' ),
	 *     );
	 *
	 * @return array[] Array of post type definitions keyed by slug.
	 */
	function opencx_get_post_types() {
		$types = array(
			'opencx_news' => array(
				'slug'        => 'opencx_news',
				'labels'      => array(
					'name'               => __( 'Newsroom', 'opencx' ),
					'singular_name'      => __( 'News Item', 'opencx' ),
					'menu_name'          => __( 'Newsroom', 'opencx' ),
					'all_items'          => __( 'All News', 'opencx' ),
					'add_new'            => __( 'Add News Item', 'opencx' ),
					'add_new_item'       => __( 'Add New News Item', 'opencx' ),
					'edit_item'          => __( 'Edit News Item', 'opencx' ),
					'new_item'           => __( 'New News Item', 'opencx' ),
					'view_item'          => __( 'View News Item', 'opencx' ),
					'search_items'       => __( 'Search Newsroom', 'opencx' ),
					'not_found'          => __( 'No news items found', 'opencx' ),
					'not_found_in_trash' => __( 'No news items found in trash', 'opencx' ),
				),
				'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'author' ),
				'has_archive' => true,
				'rewrite'     => array( 'slug' => 'newsroom' ),
				'icon'        => 'dashicons-megaphone',
				'taxonomies'  => array( 'opencx_news_category', 'post_tag' ),
			),
			'opencx_interview' => array(
				'slug'        => 'opencx_interview',
				'labels'      => array(
					'name'               => __( 'Client Perspectives', 'opencx' ),
					'singular_name'      => __( 'Interview', 'opencx' ),
					'menu_name'          => __( 'Interviews', 'opencx' ),
					'all_items'          => __( 'All Interviews', 'opencx' ),
					'add_new'            => __( 'Add Interview', 'opencx' ),
					'add_new_item'       => __( 'Add New Interview', 'opencx' ),
					'edit_item'          => __( 'Edit Interview', 'opencx' ),
					'new_item'           => __( 'New Interview', 'opencx' ),
					'view_item'          => __( 'View Interview', 'opencx' ),
					'search_items'       => __( 'Search Interviews', 'opencx' ),
					'not_found'          => __( 'No interviews found', 'opencx' ),
					'not_found_in_trash' => __( 'No interviews found in trash', 'opencx' ),
				),
				'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'author' ),
				'has_archive' => true,
				'rewrite'     => array( 'slug' => 'perspective' ),
				'icon'        => 'dashicons-testimonial',
				'taxonomies'  => array( 'post_tag' ),
			),
		);

		/**
		 * Filters the post types registered by the theme.
		 *
		 * @param array[] $post_types Post type definitions.
		 */
		return apply_filters( 'opencx_post_types', $types );
	}
}

if ( ! function_exists( 'opencx_get_taxonomies' ) ) {
	/**
	 * Returns the taxonomies owned by the theme.
	 *
	 * @return array[] Array of taxonomy definitions keyed by slug.
	 */
	function opencx_get_taxonomies() {
		$taxonomies = array(
			'opencx_news_category' => array(
				'slug'        => 'opencx_news_category',
				'object_type' => array( 'opencx_news' ),
				'labels'      => array(
					'name'          => __( 'News Types', 'opencx' ),
					'singular_name' => __( 'News Type', 'opencx' ),
				),
				'hierarchical' => true,
				'rewrite'     => array( 'slug' => 'news-type' ),
			),
		);

		/**
		 * Filters the taxonomies registered by the theme.
		 *
		 * @param array[] $taxonomies Taxonomy definitions.
		 */
		return apply_filters( 'opencx_taxonomies', $taxonomies );
	}
}

if ( ! function_exists( 'opencx_register_content_types' ) ) {
	/**
	 * Registers the theme post types and taxonomies.
	 *
	 * @return void
	 */
	function opencx_register_content_types() {
		foreach ( opencx_get_taxonomies() as $slug => $taxonomy ) {
			register_taxonomy(
				$slug,
				$taxonomy['object_type'] ?? array( 'post' ),
				array(
					'labels'             => $taxonomy['labels'] ?? array(),
					'public'             => $taxonomy['public'] ?? true,
					'hierarchical'       => $taxonomy['hierarchical'] ?? false,
					'show_ui'            => $taxonomy['show_ui'] ?? true,
					'show_in_rest'       => $taxonomy['show_in_rest'] ?? true,
					'show_admin_column'  => $taxonomy['show_admin_column'] ?? true,
					'rewrite'            => $taxonomy['rewrite'] ?? array( 'slug' => $slug ),
				)
			);
		}

		foreach ( opencx_get_post_types() as $slug => $post_type ) {
			register_post_type(
				$slug,
				array(
					'labels'             => $post_type['labels'] ?? array(),
					'public'             => $post_type['public'] ?? true,
					'has_archive'        => $post_type['has_archive'] ?? true,
					'show_in_rest'       => $post_type['show_in_rest'] ?? true,
					'show_ui'            => $post_type['show_ui'] ?? true,
					'menu_icon'          => $post_type['icon'] ?? null,
					'menu_position'      => $post_type['menu_position'] ?? null,
					'supports'           => $post_type['supports'] ?? array( 'title', 'editor', 'thumbnail', 'excerpt' ),
					'rewrite'            => $post_type['rewrite'] ?? array( 'slug' => $slug ),
					'taxonomies'         => $post_type['taxonomies'] ?? array(),
					'template'           => $post_type['template'] ?? array(),
					'template_lock'      => $post_type['template_lock'] ?? false,
				)
			);
		}
	}
}
add_action( 'init', 'opencx_register_content_types' );

if ( ! function_exists( 'opencx_register_news_meta' ) ) {
	/**
	 * Registers REST API meta fields for opencx_news.
	 *
	 * @return void
	 */
	function opencx_register_news_meta() {
		$meta_fields = array(
			'opencx_news_type'          => 'string',
			'opencx_news_source'        => 'string',
			'opencx_news_external_url'  => 'string',
			'opencx_news_date_display'  => 'string',
			'opencx_news_press_contact' => 'string',
			'opencx_news_pdf_url'       => 'string',
		);

		foreach ( $meta_fields as $meta_key => $type ) {
			register_post_meta(
				'opencx_news',
				$meta_key,
				array(
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => $type,
					'auth_callback' => function() {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}

		$interview_meta = array(
			'opencx_interviewee_name'    => 'string',
			'opencx_interviewee_role'    => 'string',
			'opencx_interviewee_company' => 'string',
			'opencx_interview_qa'        => 'string', // JSON serialized Q&A list
		);

		foreach ( $interview_meta as $meta_key => $type ) {
			register_post_meta(
				'opencx_interview',
				$meta_key,
				array(
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => $type,
					'auth_callback' => function() {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
}
add_action( 'init', 'opencx_register_news_meta' );
