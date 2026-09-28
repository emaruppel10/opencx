<?php
/**
 * The slider shortcode.
 *
 * @package OpenCX\Testimonials
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders testimonial cards for the OpenCX "Testimonial / 23 /" slider.
 *
 * The shortcode emits **cards only**, not the whole section. The section, the viewport,
 * the track, the arrows and the dots stay in the block template, because that is what lets
 * an editor compose the section around them in the Site Editor, and because the theme's
 * `testimonial-carousel.js` wires itself to `.ocx-t23__section` and would not find the
 * arrows and the dots if they were generated here.
 *
 * The class names and the inline styles are copied verbatim from the block template so the
 * existing CSS applies with no additions. The one structural detail carried over is the
 * `.ocx-t23__group` wrapper: the theme wraps logo + content in it only when there is a
 * logo, because the group is the node with a 48px gap and with a single child that gap is
 * inert. Emitting it unconditionally would add a level the current markup does not have.
 */
class OpenCX_Testimonials_Slider {

	/**
	 * Shortcode tag.
	 *
	 * @var string
	 */
	const TAG = 'opencx_testimonials';

	/**
	 * Default number of cards.
	 *
	 * Six is what the wireframe ships and what the theme's dot markup was written for. The
	 * carousel script re-derives the dot count from the layout, so a different number needs
	 * no change on the JavaScript side.
	 *
	 * @var int
	 */
	const DEFAULT_LIMIT = 6;

	/**
	 * Card background and border, from the block attributes in the template.
	 *
	 * @var string
	 */
	const CARD_STYLE = 'background-color:#f2f2f2;border-color:rgba(0, 0, 0, 0.15);border-radius:16px;border-style:solid;border-width:1px';

	/**
	 * Hooks the shortcode in.
	 *
	 * @return void
	 */
	public static function init() {
		add_shortcode( self::TAG, array( __CLASS__, 'render' ) );
	}

	/**
	 * Renders the cards.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string Card markup, or an empty string when there is nothing to show.
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'limit' => self::DEFAULT_LIMIT,
				'order' => 'ASC',
				'link'  => 'on',
			),
			$atts,
			self::TAG
		);

		$limit = (int) $atts['limit'];

		if ( $limit <= 0 ) {
			return '';
		}

		$order = 'DESC' === strtoupper( (string) $atts['order'] ) ? 'DESC' : 'ASC';

		$query = new WP_Query(
			array(
				'post_type'           => OpenCX_Testimonials_Post_Type::POST_TYPE,
				'post_status'         => 'publish',
				'posts_per_page'      => $limit,
				'order'               => $order,
				'orderby'             => array(
					'menu_order' => $order,
					'date'       => $order,
				),
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			)
		);

		if ( ! $query->have_posts() ) {
			return '';
		}

		$cards = '';

		foreach ( $query->posts as $post ) {
			$cards .= self::render_card( $post, 'on' === strtolower( (string) $atts['link'] ) );
		}

		wp_reset_postdata();

		return $cards;
	}

	/**
	 * Renders one card.
	 *
	 * @param WP_Post $post     Testimonial.
	 * @param bool    $with_link Whether to render the trailing link.
	 * @return string Card markup.
	 */
	private static function render_card( $post, $with_link ) {
		$name  = get_the_title( $post );
		$role  = (string) get_post_meta( $post->ID, OpenCX_Testimonials_Post_Type::META_ROLE, true );
		$logo  = (int) get_post_meta( $post->ID, OpenCX_Testimonials_Post_Type::META_LOGO, true );
		$image = (int) get_post_meta( $post->ID, OpenCX_Testimonials_Post_Type::META_AVATAR, true );

		/*
		 * The short comment is the excerpt. It is output through `the_excerpt`-style
		 * formatting rather than raw, so a shortcode or a link inside it still renders, and
		 * the surrounding typographic quotes are expected to be part of the stored text
		 * because the wireframe shows the card with them.
		 */
		$quote = get_the_excerpt( $post );

		if ( '' === trim( wp_strip_all_tags( $quote ) ) ) {
			return '';
		}

		$out = sprintf(
			'<div class="wp-block-group ocx-t23__card has-background" style="%s">',
			esc_attr( self::CARD_STYLE )
		);

		$has_logo = $logo && wp_attachment_is_image( $logo );

		if ( $has_logo ) {
			$out .= '<div class="wp-block-group ocx-t23__group">';
			$out .= sprintf(
				'<figure class="wp-block-image size-large ocx-t23__logo">%s</figure>',
				self::render_attachment( $logo, 'large', array( 'width' => 120, 'height' => 48 ) )
			);
		}

		$out .= '<div class="wp-block-group ocx-t23__inner">';

		$out .= sprintf(
			'<p class="ocx-t23__quote">%s</p>',
			wp_kses_post( $quote )
		);

		$out .= '<div class="wp-block-group ocx-t23__avatar">';

		if ( $image && wp_attachment_is_image( $image ) ) {
			$out .= sprintf(
				'<figure class="wp-block-image size-large ocx-t23__avatar-img">%s</figure>',
				self::render_attachment( $image, 'thumbnail', array( 'width' => 48, 'height' => 48 ) )
			);
		}

		$out .= '<div class="wp-block-group ocx-t23__avatar-text">';
		$out .= sprintf( '<p class="ocx-t23__name">%s</p>', esc_html( $name ) );

		if ( '' !== trim( $role ) ) {
			$out .= sprintf( '<p class="ocx-t23__role">%s</p>', esc_html( $role ) );
		}

		$out .= '</div>';
		$out .= '</div>';
		$out .= '</div>';

		if ( $has_logo ) {
			$out .= '</div>';
		}

		if ( $with_link ) {
			$out .= self::render_link( $post );
		}

		$out .= '</div>';

		return $out;
	}

	/**
	 * Renders the trailing "read the story" link.
	 *
	 * The target is the testimonial's own permalink, which is where the full story is meant
	 * to live. That single view does not exist yet, so until it is built these links land on
	 * a 404; `link="off"` on the shortcode turns the whole button off in the meantime.
	 *
	 * @param WP_Post $post Testimonial.
	 * @return string Link markup.
	 */
	private static function render_link( $post ) {
		$url = get_permalink( $post );

		/**
		 * Filters where a testimonial card links to.
		 *
		 * @param string  $url  Destination URL.
		 * @param WP_Post $post Testimonial.
		 */
		$url = (string) apply_filters( 'opencx_testimonial_card_url', $url, $post );

		if ( '' === $url ) {
			return '';
		}

		$label = __( 'Read case study', 'opencx-testimonials' );

		/**
		 * Filters the card link label.
		 *
		 * @param string  $label Link text.
		 * @param WP_Post $post  Testimonial.
		 */
		$label = (string) apply_filters( 'opencx_testimonial_card_label', $label, $post );

		$icon = OPENCX_TESTIMONIALS_URL . 'assets/chevron-right.svg';

		return sprintf(
			'<div class="wp-block-button ocx-btn--link ocx-btn--link--plain"><a class="wp-block-button__link wp-element-button" href="%s">%s<img class="ocx-btn__icon" src="%s" alt="" width="24" height="24" /></a></div>',
			esc_url( $url ),
			esc_html( $label ),
			esc_url( $icon )
		);
	}

	/**
	 * Renders an attachment at a size, falling back to the full image when the size is
	 * missing.
	 *
	 * The width and height are passed so the markup always carries intrinsic dimensions and
	 * the card does not reflow when the image loads, which is what the template's hardcoded
	 * `width="48" height="48"` does.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $size          Registered image size.
	 * @param array  $dimensions    Intrinsic width and height to force.
	 * @return string Image markup, or an empty string.
	 */
	private static function render_attachment( $attachment_id, $size, $dimensions ) {
		$image = wp_get_attachment_image(
			$attachment_id,
			$size,
			false,
			array(
				'width'  => $dimensions['width'],
				'height' => $dimensions['height'],
			)
		);

		return $image ?: '';
	}
}
