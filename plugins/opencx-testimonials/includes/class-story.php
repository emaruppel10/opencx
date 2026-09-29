<?php
/**
 * The single story shortcode.
 *
 * @package OpenCX\Testimonials
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the full story of one testimonial for `[opencx_testimonial]`.
 *
 * The story itself is the post content, edited in the block editor, and this class only
 * wraps it with the person's own data. The markup is the theme's existing components,
 * `.ocx-h64` for the hero and `.ocx-c4` for the body, so this view needs no new CSS from
 * the theme. What is *not* built here is the case study layout from the wireframe, the
 * "Portfolio Page / 1 /" with its hero image, tags, definition list, gallery and related
 * entries: those need fields the post type does not have yet.
 *
 * Called with no attributes it renders the post currently in the loop, which is what the
 * single template does.
 */
class OpenCX_Testimonials_Story {

	/**
	 * Shortcode tag.
	 *
	 * @var string
	 */
	const TAG = 'opencx_testimonial';

	/**
	 * Hooks the shortcode in.
	 *
	 * @return void
	 */
	public static function init() {
		add_shortcode( self::TAG, array( __CLASS__, 'render' ) );
	}

	/**
	 * Renders one testimonial.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string Story markup, or an empty string when there is no testimonial to show.
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts( array( 'id' => '' ), $atts, self::TAG );

		$post = self::resolve_post( (string) $atts['id'] );

		if ( ! $post ) {
			return '';
		}

		$setup = setup_postdata( $post );

		$out  = self::render_hero( $post );
		$out .= self::render_story( $post );

		wp_reset_postdata();

		unset( $setup );

		return $out;
	}

	/**
	 * Finds the testimonial to render.
	 *
	 * With no `id` it uses the post in the loop, which is the single template case. A numeric
	 * `id` is looked up directly; anything else is treated as a slug, so
	 * `[opencx_testimonial id="elena-rossi"]` also works.
	 *
	 * @param string $attribute The `id` attribute.
	 * @return WP_Post|null
	 */
	private static function resolve_post( $attribute ) {
		$attribute = trim( $attribute );

		if ( '' === $attribute ) {
			$post = get_post();

			return $post instanceof WP_Post
				&& OpenCX_Testimonials_Post_Type::POST_TYPE === $post->post_type
				? $post
				: null;
		}

		$post = ctype_digit( $attribute )
			? get_post( (int) $attribute )
			: get_page_by_path( $attribute, OBJECT, OpenCX_Testimonials_Post_Type::POST_TYPE );

		if ( ! $post instanceof WP_Post ) {
			return null;
		}

		if ( OpenCX_Testimonials_Post_Type::POST_TYPE !== $post->post_type ) {
			return null;
		}

		if ( 'publish' !== $post->post_status && ! current_user_can( 'read_post', $post->ID ) ) {
			return null;
		}

		return $post;
	}

	/**
	 * Renders the hero: logo, name, role, avatar and the short comment.
	 *
	 * The name is the H1 because the story page is about one person, and the short comment
	 * keeps the typographic quotes that the wireframe shows on the card.
	 *
	 * @param WP_Post $post Testimonial.
	 * @return string Hero markup.
	 */
	private static function render_hero( $post ) {
		$logo   = (int) get_post_meta( $post->ID, OpenCX_Testimonials_Post_Type::META_LOGO, true );
		$avatar = (int) get_post_meta( $post->ID, OpenCX_Testimonials_Post_Type::META_AVATAR, true );
		$role   = (string) get_post_meta( $post->ID, OpenCX_Testimonials_Post_Type::META_ROLE, true );
		$quote  = get_the_excerpt( $post );

		$out = '<div class="wp-block-group alignfull ocx-section ocx-section--stretch ocx-h64__section ocx-h64--center">';
		$out .= '<div class="wp-block-group ocx-h64__container">';
		$out .= '<div class="wp-block-group ocx-h64__component ocx-h64--center">';

		if ( $logo && wp_attachment_is_image( $logo ) ) {
			$out .= sprintf(
				'<figure class="wp-block-image size-large ocx-t23__logo">%s</figure>',
				wp_get_attachment_image( $logo, 'large', false, array( 'width' => 120, 'height' => 48 ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built by wp_get_attachment_image.
			);
		}

		$out .= sprintf(
			'<h1 class="wp-block-heading ocx-h64__title">%s</h1>',
			esc_html( get_the_title( $post ) )
		);

		if ( '' !== trim( $role ) ) {
			$out .= sprintf(
				'<p class="ocx-h64__lead">%s</p>',
				esc_html( $role )
			);
		}

		if ( '' !== trim( wp_strip_all_tags( $quote ) ) ) {
			$out .= '<div class="wp-block-group ocx-t23__avatar">';

			if ( $avatar && wp_attachment_is_image( $avatar ) ) {
				$out .= sprintf(
					'<figure class="wp-block-image size-large ocx-t23__avatar-img">%s</figure>',
					wp_get_attachment_image( $avatar, 'thumbnail', false, array( 'width' => 48, 'height' => 48 ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built by wp_get_attachment_image.
				);
			}

			$out .= sprintf( '<p class="ocx-t23__quote">%s</p>', wp_kses_post( $quote ) );
			$out .= '</div>';
		}

		$out .= '</div>';
		$out .= '</div>';
		$out .= '</div>';

		return $out;
	}

	/**
	 * Renders the story body.
	 *
	 * The section is dropped entirely when the post has no content, so a testimonial that
	 * only carries a quote still renders a valid page instead of an empty column.
	 *
	 * @param WP_Post $post Testimonial.
	 * @return string Body markup, or an empty string.
	 */
	private static function render_story( $post ) {
		if ( '' === trim( $post->post_content ) ) {
			return '';
		}

		$content = apply_filters( 'the_content', $post->post_content );

		if ( '' === trim( wp_strip_all_tags( $content ) ) ) {
			return '';
		}

		$out  = '<div class="wp-block-group alignfull ocx-section ocx-section--stretch ocx-c4__section">';
		$out .= '<div class="wp-block-group ocx-c4__container">';
		$out .= $content;
		$out .= '</div>';
		$out .= '</div>';

		return $out;
	}
}
