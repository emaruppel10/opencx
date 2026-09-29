<?php
/**
 * The editable fields: logo, avatar and role.
 *
 * @package OpenCX\Testimonials
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds and saves the three meta fields, and teaches the editor that the title is the
 * person's name and the excerpt is the short comment.
 */
class OpenCX_Testimonials_Fields {

	/**
	 * Nonce action for the meta box.
	 *
	 * @var string
	 */
	const NONCE = 'opencx_testimonial_fields';

	/**
	 * Nonce request key.
	 *
	 * @var string
	 */
	const NONCE_KEY = 'opencx_testimonial_nonce';

	/**
	 * Reentrancy guard for the excerpt write.
	 *
	 * @var bool
	 */
	private static $saving = false;

	/**
	 * Hooks the field behaviour in.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post_' . OpenCX_Testimonials_Post_Type::POST_TYPE, array( __CLASS__, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		add_filter( 'manage_' . OpenCX_Testimonials_Post_Type::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . OpenCX_Testimonials_Post_Type::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_value' ), 10, 2 );
	}

	/**
	 * Declares the admin list columns.
	 *
	 * The default columns are the title and the date, and neither says anything about what
	 * the slider actually renders. The short comment is the card's quote, and the order is
	 * what sorts the carousel, so both are worth seeing without opening each testimonial.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public static function columns( $columns ) {
		$new = array();

		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;

			if ( 'title' === $key ) {
				$new['ocx_excerpt']    = __( 'Short comment', 'opencx-testimonials' );
				$new['ocx_role']       = __( 'Role', 'opencx-testimonials' );
				$new['ocx_media']      = __( 'Media', 'opencx-testimonials' );
			}
		}

		return $new;
	}

	/**
	 * Prints one admin list column.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post being listed.
	 * @return void
	 */
	public static function column_value( $column, $post_id ) {
		switch ( $column ) {
			case 'ocx_excerpt':
				$excerpt = get_post_field( 'post_excerpt', $post_id );

				if ( $excerpt ) {
					echo esc_html( wp_html_excerpt( $excerpt, 80, '&hellip;' ) );
				} else {
					echo '<span aria-hidden="true">&mdash;</span><span class="screen-reader-text">'
						. esc_html__( 'Empty', 'opencx-testimonials' ) . '</span>';
				}
				break;

			case 'ocx_role':
				$role = (string) get_post_meta( $post_id, OpenCX_Testimonials_Post_Type::META_ROLE, true );

				echo $role ? esc_html( $role ) : '<span aria-hidden="true">&mdash;</span><span class="screen-reader-text">'
					. esc_html__( 'Empty', 'opencx-testimonials' ) . '</span>';
				break;

			case 'ocx_media':
				$logo   = (int) get_post_meta( $post_id, OpenCX_Testimonials_Post_Type::META_LOGO, true );
				$avatar = (int) get_post_meta( $post_id, OpenCX_Testimonials_Post_Type::META_AVATAR, true );

				if ( ! $logo && ! $avatar ) {
					echo '<span aria-hidden="true">&mdash;</span><span class="screen-reader-text">'
						. esc_html__( 'Empty', 'opencx-testimonials' ) . '</span>';
					break;
				}

				$thumbs = array();

				if ( $avatar ) {
					$thumbs[] = wp_get_attachment_image( $avatar, array( 32, 32 ), false, array( 'alt' => '' ) );
				}

				if ( $logo ) {
					$thumbs[] = wp_get_attachment_image( $logo, array( 60, 32 ), false, array( 'alt' => '' ) );
				}

				echo '<span style="display:inline-flex;gap:6px;align-items:center">'
					. implode( '', $thumbs ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built by wp_get_attachment_image.
					. '</span>';
				break;
		}
	}

	/**
	 * Registers the meta box on the testimonial editor.
	 *
	 * @return void
	 */
	public static function add_meta_box() {
		add_meta_box(
			'opencx-testimonial-fields',
			__( 'Testimonial', 'opencx-testimonials' ),
			array( __CLASS__, 'render_meta_box' ),
			OpenCX_Testimonials_Post_Type::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Loads the media picker only on the testimonial editor.
	 *
	 * @param string $hook Current admin page.
	 * @return void
	 */
	public static function enqueue_admin_assets( $hook ) {
		$screen = get_current_screen();

		if ( ! $screen || OpenCX_Testimonials_Post_Type::POST_TYPE !== $screen->post_type ) {
			return;
		}

		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_script(
			'opencx-testimonials-fields',
			OPENCX_TESTIMONIALS_URL . 'assets/admin.js',
			array( 'jquery' ),
			OPENCX_TESTIMONIALS_VERSION,
			true
		);

		wp_localize_script(
			'opencx-testimonials-fields',
			'opencxTestimonials',
			array(
				'frameTitle'  => __( 'Choose an image', 'opencx-testimonials' ),
				'frameButton' => __( 'Use this image', 'opencx-testimonials' ),
			)
		);
	}

	/**
	 * Renders the fields.
	 *
	 * The name is the post title, so it is edited in the main column like any post title.
	 * The short comment is the post excerpt, but `excerpt` is not in the post type's supports
	 * (see the note in `OpenCX_Testimonials_Post_Type::register`), which keeps the block
	 * editor from drawing its own unlabeled "Excerpt" panel. That leaves this field as the
	 * only input for it, next to the logo, the avatar and the role.
	 *
	 * @param WP_Post $post Current post.
	 * @return void
	 */
	public static function render_meta_box( $post ) {
		$logo   = (int) get_post_meta( $post->ID, OpenCX_Testimonials_Post_Type::META_LOGO, true );
		$avatar = (int) get_post_meta( $post->ID, OpenCX_Testimonials_Post_Type::META_AVATAR, true );
		$role   = (string) get_post_meta( $post->ID, OpenCX_Testimonials_Post_Type::META_ROLE, true );

		wp_nonce_field( self::NONCE, self::NONCE_KEY );

		?>
		<p class="description" style="margin:0 0 16px">
			<?php esc_html_e( 'The title above is the person\'s name. Everything the slider card shows is below.', 'opencx-testimonials' ); ?>
		</p>

		<?php self::render_image_field( OpenCX_Testimonials_Post_Type::META_LOGO, __( 'Logo', 'opencx-testimonials' ), $logo ); ?>
		<?php self::render_image_field( OpenCX_Testimonials_Post_Type::META_AVATAR, __( 'Avatar', 'opencx-testimonials' ), $avatar ); ?>

		<p>
			<label class="opencx-field__label" for="opencx-testimonial-role" style="display:block;font-weight:600;margin-bottom:4px">
				<?php esc_html_e( 'Role', 'opencx-testimonials' ); ?>
			</label>
			<input
				type="text"
				class="widefat"
				id="opencx-testimonial-role"
				name="<?php echo esc_attr( OpenCX_Testimonials_Post_Type::META_ROLE ); ?>"
				value="<?php echo esc_attr( $role ); ?>"
				placeholder="<?php esc_attr_e( 'Director of Logistics, EduServe', 'opencx-testimonials' ); ?>"
			/>
		</p>

		<p style="margin:16px 0 0">
			<label class="opencx-field__label" for="opencx-testimonial-excerpt" style="display:block;font-weight:600;margin-bottom:4px">
				<?php esc_html_e( 'Short comment', 'opencx-testimonials' ); ?>
			</label>
			<textarea
				class="widefat"
				id="opencx-testimonial-excerpt"
				name="<?php echo esc_attr( OpenCX_Testimonials_Post_Type::FIELD_EXCERPT ); ?>"
				rows="5"
				placeholder="<?php esc_attr_e( '“I keep ownership of everything we’ve built. If anything ever changes, it comes with me.”', 'opencx-testimonials' ); ?>"
			><?php echo esc_textarea( $post->post_excerpt ); ?></textarea>
			<span class="description">
				<?php esc_html_e( 'This is the quote printed on the card.', 'opencx-testimonials' ); ?>
			</span>
		</p>
		<?php
	}

	/**
	 * Renders one image picker.
	 *
	 * The markup is a hidden input holding the attachment ID plus a preview and two buttons.
	 * It is a hidden input and not a visible text field because the ID is not something an
	 * editor should be pasting; `assets/admin.js` is the only thing that writes to it.
	 *
	 * @param string $meta_key Meta key.
	 * @param string $label    Field label.
	 * @param int    $value    Current attachment ID.
	 * @return void
	 */
	private static function render_image_field( $meta_key, $label, $value ) {
		$preview = $value ? wp_get_attachment_image( $value, 'medium', false, array( 'class' => 'opencx-field__preview' ) ) : '';
		?>
		<div class="opencx-field" style="margin:0 0 16px">
			<label class="opencx-field__label" for="<?php echo esc_attr( $meta_key ); ?>" style="display:block;font-weight:600;margin-bottom:4px">
				<?php echo esc_html( $label ); ?>
			</label>

			<div class="opencx-field__preview-wrap" style="display:flex;gap:12px;align-items:flex-start">
				<div class="opencx-field__preview" style="width:160px;min-height:64px;border:1px solid #dcdcde;background:#fff;display:flex;align-items:center;justify-content:center">
					<?php echo $preview; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built by wp_get_attachment_image. ?>
				</div>

				<div style="display:flex;flex-direction:column;gap:6px">
					<button type="button" class="button opencx-field__choose"><?php esc_html_e( 'Choose image', 'opencx-testimonials' ); ?></button>
					<button type="button" class="button-link-delete opencx-field__clear" <?php echo $value ? '' : 'hidden'; ?>>
						<?php esc_html_e( 'Remove', 'opencx-testimonials' ); ?>
					</button>
				</div>
			</div>

			<input
				type="hidden"
				id="<?php echo esc_attr( $meta_key ); ?>"
				class="opencx-field__value"
				name="<?php echo esc_attr( $meta_key ); ?>"
				value="<?php echo esc_attr( (string) $value ); ?>"
			/>
		</div>
		<?php
	}

	/**
	 * Saves the fields.
	 *
	 * The nonce, the post type and the capability are all checked before anything is
	 * written, and the values go through the same sanitising the registered meta defines.
	 *
	 * @param int $post_id Post being saved.
	 * @return void
	 */
	public static function save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		/*
		 * `wp_update_post()` below fires `save_post` again for the same post. Without this
		 * guard the handler would re-enter itself and save in a loop, because the second
		 * pass would still see the excerpt as out of date relative to what it just read.
		 */
		if ( self::$saving ) {
			return;
		}

		if ( ! isset( $_POST[ self::NONCE_KEY ] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_KEY ] ) );

		if ( ! wp_verify_nonce( $nonce, self::NONCE ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$post_type = get_post_type( $post_id );

		if ( OpenCX_Testimonials_Post_Type::POST_TYPE !== $post_type ) {
			return;
		}

		foreach ( array( OpenCX_Testimonials_Post_Type::META_LOGO, OpenCX_Testimonials_Post_Type::META_AVATAR ) as $key ) {
			/*
			 * An absent key means the form did not carry this field, which is not the same as
			 * carrying an empty value. Only the meta box posts these hidden inputs, so treating
			 * "missing" as "cleared" would silently delete the logo and the avatar on any save
			 * that does not come from that form.
			 */
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}

			$attachment_id = absint( wp_unslash( $_POST[ $key ] ) );

			if ( $attachment_id ) {
				update_post_meta( $post_id, $key, $attachment_id );
			} else {
				delete_post_meta( $post_id, $key );
			}
		}

		$role_key = OpenCX_Testimonials_Post_Type::META_ROLE;

		if ( isset( $_POST[ $role_key ] ) ) {
			update_post_meta( $post_id, $role_key, sanitize_text_field( wp_unslash( $_POST[ $role_key ] ) ) );
		}

		self::save_excerpt( $post_id );
	}

	/**
	 * Writes the short comment to the post excerpt.
	 *
	 * The excerpt is a post column rather than meta, so it needs `wp_update_post()`, which
	 * costs a second write. The value is compared first so a post that was saved without
	 * touching the field does not pay for it.
	 *
	 * @param int $post_id Post being saved.
	 * @return void
	 */
	private static function save_excerpt( $post_id ) {
		$field = OpenCX_Testimonials_Post_Type::FIELD_EXCERPT;

		if ( ! isset( $_POST[ $field ] ) ) {
			return;
		}

		$excerpt = sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) );
		$post    = get_post( $post_id );

		if ( ! $post || $excerpt === $post->post_excerpt ) {
			return;
		}

		self::$saving = true;

		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_excerpt' => $excerpt,
			)
		);

		self::$saving = false;
	}
}
