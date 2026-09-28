<?php
/**
 * The testimonial post type.
 *
 * @package OpenCX\Testimonials
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers `opencx_testimonial`.
 *
 * How the five configurable fields map onto WordPress:
 *
 *   Logo           -> `_ocx_testimonial_logo`   (attachment ID, registered meta)
 *   Name           -> the post title. A testimonial is a person, so the title is the
 *                     person's name: it labels the row in the admin list and it is what
 *                     the permalink is generated from, both of which would be useless if
 *                     the name lived in a separate field.
 *   Cargo / Role   -> `_ocx_testimonial_role`   (text, registered meta)
 *   Avatar         -> `_ocx_testimonial_avatar` (attachment ID, registered meta)
 *   Short comment  -> the post excerpt. The excerpt is exactly the "shorter version" slot
 *                     WordPress already has, and it is the field the slider renders.
 *   Full story     -> the post content, reserved for the single view.
 */
class OpenCX_Testimonials_Post_Type {

	/**
	 * Post type slug.
	 *
	 * @var string
	 */
	const POST_TYPE = 'opencx_testimonial';

	/**
	 * Meta key for the logo attachment ID.
	 *
	 * @var string
	 */
	const META_LOGO = '_ocx_testimonial_logo';

	/**
	 * Meta key for the role text.
	 *
	 * @var string
	 */
	const META_ROLE = '_ocx_testimonial_role';

	/**
	 * Meta key for the avatar attachment ID.
	 *
	 * @var string
	 */
	const META_AVATAR = '_ocx_testimonial_avatar';

	/**
	 * Hooks the registration in.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Registers the post type and its meta.
	 *
	 * @return void
	 */
	public static function register() {
		$labels = array(
			'name'               => __( 'Testimonials', 'opencx-testimonials' ),
			'singular_name'      => __( 'Testimonial', 'opencx-testimonials' ),
			/* translators: %s: singular post type name, "Testimonial". */
			'add_new_item'       => __( 'Add New %s', 'opencx-testimonials' ),
			/* translators: %s: singular post type name, "Testimonial". */
			'edit_item'          => __( 'Edit %s', 'opencx-testimonials' ),
			/* translators: %s: singular post type name, "Testimonial". */
			'new_item'           => __( 'New %s', 'opencx-testimonials' ),
			/* translators: %s: singular post type name, "Testimonial". */
			'view_item'          => __( 'View %s', 'opencx-testimonials' ),
			/* translators: %s: plural post type name, "Testimonials". */
			'search_items'       => __( 'Search %s', 'opencx-testimonials' ),
			/* translators: %s: plural post type name, "Testimonials". */
			'not_found'          => __( 'No %s yet', 'opencx-testimonials' ),
			/* translators: %s: plural post type name, "Testimonials". */
			'not_found_in_trash' => __( 'No %s in the trash', 'opencx-testimonials' ),
			'all_items'          => __( 'All %s', 'opencx-testimonials' ),
			'menu_name'          => __( 'Testimonials', 'opencx-testimonials' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'       => $labels,
				'public'       => true,
				'show_ui'      => true,
				'show_in_rest' => true,
				'menu_icon'    => 'format-quote',
				/*
				 * `excerpt` is what carries the short comment the slider shows, and
				 * `page-attributes` exposes the core "Order" field, which is what the
				 * slider sorts by. `thumbnail` is deliberately absent: the avatar is its
				 * own field, and a second image control in the sidebar would be ambiguous
				 * about which one is which.
				 */
				'supports'     => array( 'title', 'editor', 'excerpt', 'page-attributes' ),
				'has_archive'  => false,
				'rewrite'      => array( 'slug' => 'testimonials' ),
				'menu_position' => 20,
			)
		);

		self::register_meta( self::META_LOGO, 'integer' );
		self::register_meta( self::META_ROLE, 'string' );
		self::register_meta( self::META_AVATAR, 'integer' );
	}

	/**
	 * Registers one meta key with a sanitize callback and REST access.
	 *
	 * Registered rather than hand-written so the value is sanitised on the way in from
	 * both the meta box and the REST API, and so the block editor sees the same field.
	 *
	 * @param string $key    Meta key.
	 * @param string $type   Value type: `integer` or `string`.
	 * @return void
	 */
	private static function register_meta( $key, $type ) {
		register_post_meta(
			self::POST_TYPE,
			$key,
			array(
				'type'              => $type,
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'integer' === $type
					? 'absint'
					: 'sanitize_text_field',
				/*
				 * Without this, `register_post_meta` treats a meta key that starts with an
				 * underscore as protected and refuses the REST write, which would make the
				 * field read-only in the block editor.
				 */
				'auth_callback'     => static function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
