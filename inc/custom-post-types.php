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
		/**
		 * Filters the post types registered by the theme.
		 *
		 * @param array[] $post_types Post type definitions.
		 */
		return apply_filters( 'opencx_post_types', array() );
	}
}

if ( ! function_exists( 'opencx_get_taxonomies' ) ) {
	/**
	 * Returns the taxonomies owned by the theme.
	 *
	 * Expected shape for each entry:
	 *
	 *     array(
	 *         'slug'        => 'industry',
	 *         'object_type' => array( 'case-study' ),
	 *         'labels'      => array( 'name' => __( 'Industries', 'opencx' ) ),
	 *         'hierarchical' => true,
	 *         'rewrite'     => array( 'slug' => 'industry' ),
	 *     );
	 *
	 * @return array[] Array of taxonomy definitions keyed by slug.
	 */
	function opencx_get_taxonomies() {
		/**
		 * Filters the taxonomies registered by the theme.
		 *
		 * @param array[] $taxonomies Taxonomy definitions.
		 */
		return apply_filters( 'opencx_taxonomies', array() );
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
