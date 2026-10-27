<?php
/**
 * Dynamic Team Members & Testimonials Logic.
 *
 * Provides custom admin meta boxes, admin list columns, automatic database
 * seeding for default entries, and dynamic frontend shortcodes.
 *
 * @package OpenCX
 */

defined( 'ABSPATH' ) || exit;

/**
 * -----------------------------------------------------------------------------
 * 1. Admin Meta Boxes
 * -----------------------------------------------------------------------------
 */

/**
 * Register meta boxes for Team and Testimonial post types.
 */
function opencx_register_team_testimonial_meta_boxes() {
	add_meta_box(
		'opencx_team_meta_box',
		__( 'Team Member Information', 'opencx' ),
		'opencx_render_team_meta_box',
		'opencx_team',
		'normal',
		'high'
	);

	add_meta_box(
		'opencx_testimonial_meta_box',
		__( 'Testimonial Information', 'opencx' ),
		'opencx_render_testimonial_meta_box',
		'opencx_testimonial',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'opencx_register_team_testimonial_meta_boxes' );

/**
 * Render Team Member meta box.
 *
 * @param WP_Post $post Current post object.
 */
function opencx_render_team_meta_box( $post ) {
	wp_nonce_field( 'opencx_save_team_meta', 'opencx_team_nonce' );

	$role     = get_post_meta( $post->ID, 'opencx_team_role', true );
	$linkedin = get_post_meta( $post->ID, 'opencx_team_linkedin', true );
	$bio      = get_post_meta( $post->ID, 'opencx_team_bio', true ) ?: $post->post_content;
	$order    = $post->menu_order;
	?>
	<div style="padding: 10px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
		<p style="margin-bottom: 15px;">
			<label for="opencx_team_role" style="display: block; font-weight: 600; margin-bottom: 5px;">
				<?php esc_html_e( 'Role / Job Title', 'opencx' ); ?>:
			</label>
			<input type="text" id="opencx_team_role" name="opencx_team_role" value="<?php echo esc_attr( $role ); ?>" class="widefat" placeholder="e.g. CEO, Head of Sales, Executive Director" />
		</p>

		<p style="margin-bottom: 15px;">
			<label for="opencx_team_linkedin" style="display: block; font-weight: 600; margin-bottom: 5px;">
				<?php esc_html_e( 'LinkedIn Profile URL', 'opencx' ); ?>:
			</label>
			<input type="url" id="opencx_team_linkedin" name="opencx_team_linkedin" value="<?php echo esc_attr( $linkedin ); ?>" class="widefat" placeholder="https://www.linkedin.com/in/username" />
		</p>

		<p style="margin-bottom: 15px;">
			<label for="opencx_team_bio" style="display: block; font-weight: 600; margin-bottom: 5px;">
				<?php esc_html_e( 'Biography / Profile Summary', 'opencx' ); ?>:
			</label>
			<textarea id="opencx_team_bio" name="opencx_team_bio" rows="4" class="widefat" placeholder="Brief professional description..."><?php echo esc_textarea( $bio ); ?></textarea>
		</p>

		<p style="margin-bottom: 10px;">
			<label for="opencx_team_order" style="display: block; font-weight: 600; margin-bottom: 5px;">
				<?php esc_html_e( 'Display Order', 'opencx' ); ?>:
			</label>
			<input type="number" id="opencx_team_order" name="opencx_team_order" value="<?php echo esc_attr( $order ); ?>" style="width: 100px;" />
			<span class="description" style="margin-left: 10px; color: #666;"><?php esc_html_e( 'Lower numbers appear first (e.g. 1, 2, 3).', 'opencx' ); ?></span>
		</p>

		<div style="margin-top: 15px; padding: 10px 14px; background: #f0f6fc; border-left: 4px solid #72aee6; border-radius: 4px; font-size: 13px;">
			<strong><?php esc_html_e( 'Tip:', 'opencx' ); ?></strong> <?php esc_html_e( 'Use the "Featured Image" box in the right sidebar to set the member\'s high-resolution headshot photo.', 'opencx' ); ?>
		</div>
	</div>
	<?php
}

/**
 * Render Testimonial meta box.
 *
 * @param WP_Post $post Current post object.
 */
function opencx_render_testimonial_meta_box( $post ) {
	wp_nonce_field( 'opencx_save_testimonial_meta', 'opencx_testimonial_nonce' );

	$company = get_post_meta( $post->ID, 'opencx_testimonial_company', true ) ?: get_post_meta( $post->ID, '_ocx_testimonial_role', true );
	$quote   = get_post_meta( $post->ID, 'opencx_testimonial_quote', true ) ?: ( $post->post_excerpt ?: $post->post_content );
	$order   = $post->menu_order;
	?>
	<div style="padding: 10px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
		<p style="margin-bottom: 15px;">
			<label for="opencx_testimonial_company" style="display: block; font-weight: 600; margin-bottom: 5px;">
				<?php esc_html_e( 'Client Company & Role', 'opencx' ); ?>:
			</label>
			<input type="text" id="opencx_testimonial_company" name="opencx_testimonial_company" value="<?php echo esc_attr( $company ); ?>" class="widefat" placeholder="e.g. Nexx Burger or RL Funeral Services" />
		</p>

		<p style="margin-bottom: 15px;">
			<label for="opencx_testimonial_quote" style="display: block; font-weight: 600; margin-bottom: 5px;">
				<?php esc_html_e( 'Client Quote / Endorsement', 'opencx' ); ?>:
			</label>
			<textarea id="opencx_testimonial_quote" name="opencx_testimonial_quote" rows="5" class="widefat" placeholder="Quote statement..."><?php echo esc_textarea( $quote ); ?></textarea>
		</p>

		<p style="margin-bottom: 10px;">
			<label for="opencx_testimonial_order" style="display: block; font-weight: 600; margin-bottom: 5px;">
				<?php esc_html_e( 'Display Order', 'opencx' ); ?>:
			</label>
			<input type="number" id="opencx_testimonial_order" name="opencx_testimonial_order" value="<?php echo esc_attr( $order ); ?>" style="width: 100px;" />
			<span class="description" style="margin-left: 10px; color: #666;"><?php esc_html_e( 'Lower numbers appear first in the slider carousel.', 'opencx' ); ?></span>
		</p>
	</div>
	<?php
}

/**
 * Save meta fields for Team Members.
 *
 * @param int $post_id Post ID.
 */
function opencx_save_team_meta( $post_id ) {
	if ( ! isset( $_POST['opencx_team_nonce'] ) || ! wp_verify_nonce( $_POST['opencx_team_nonce'], 'opencx_save_team_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['opencx_team_role'] ) ) {
		update_post_meta( $post_id, 'opencx_team_role', sanitize_text_field( $_POST['opencx_team_role'] ) );
	}

	if ( isset( $_POST['opencx_team_linkedin'] ) ) {
		update_post_meta( $post_id, 'opencx_team_linkedin', esc_url_raw( $_POST['opencx_team_linkedin'] ) );
	}

	if ( isset( $_POST['opencx_team_bio'] ) ) {
		$bio = sanitize_textarea_field( $_POST['opencx_team_bio'] );
		update_post_meta( $post_id, 'opencx_team_bio', $bio );
	}

	if ( isset( $_POST['opencx_team_order'] ) ) {
		$order = intval( $_POST['opencx_team_order'] );
		// Unhook to avoid infinite loop
		remove_action( 'save_post_opencx_team', 'opencx_save_team_meta' );
		wp_update_post( array(
			'ID'         => $post_id,
			'menu_order' => $order,
		) );
		add_action( 'save_post_opencx_team', 'opencx_save_team_meta' );
	}
}
add_action( 'save_post_opencx_team', 'opencx_save_team_meta' );

/**
 * Save meta fields for Testimonials.
 *
 * @param int $post_id Post ID.
 */
function opencx_save_testimonial_meta( $post_id ) {
	if ( ! isset( $_POST['opencx_testimonial_nonce'] ) || ! wp_verify_nonce( $_POST['opencx_testimonial_nonce'], 'opencx_save_testimonial_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['opencx_testimonial_company'] ) ) {
		$company = sanitize_text_field( $_POST['opencx_testimonial_company'] );
		update_post_meta( $post_id, 'opencx_testimonial_company', $company );
		update_post_meta( $post_id, '_ocx_testimonial_role', $company );
	}

	$quote = null;
	if ( isset( $_POST['opencx_testimonial_quote'] ) ) {
		$quote = sanitize_textarea_field( $_POST['opencx_testimonial_quote'] );
		update_post_meta( $post_id, 'opencx_testimonial_quote', $quote );
	}

	if ( isset( $_POST['opencx_testimonial_order'] ) || null !== $quote ) {
		$order = isset( $_POST['opencx_testimonial_order'] ) ? intval( $_POST['opencx_testimonial_order'] ) : null;
		remove_action( 'save_post_opencx_testimonial', 'opencx_save_testimonial_meta' );
		$update_args = array( 'ID' => $post_id );
		if ( null !== $order ) {
			$update_args['menu_order'] = $order;
		}
		if ( null !== $quote ) {
			$update_args['post_excerpt'] = $quote;
		}
		wp_update_post( $update_args );
		add_action( 'save_post_opencx_testimonial', 'opencx_save_testimonial_meta' );
	}
}
add_action( 'save_post_opencx_testimonial', 'opencx_save_testimonial_meta' );


/**
 * -----------------------------------------------------------------------------
 * 2. Admin List Columns (Thumbnails, Roles, Order)
 * -----------------------------------------------------------------------------
 */

/**
 * Customize columns for Team post list.
 */
function opencx_team_admin_columns( $columns ) {
	$new_columns = array();
	$new_columns['cb']        = $columns['cb'];
	$new_columns['thumbnail'] = __( 'Photo', 'opencx' );
	$new_columns['title']     = __( 'Name', 'opencx' );
	$new_columns['role']      = __( 'Role / Title', 'opencx' );
	$new_columns['linkedin']  = __( 'LinkedIn', 'opencx' );
	$new_columns['order']     = __( 'Order', 'opencx' );
	$new_columns['date']      = $columns['date'];
	return $new_columns;
}
add_filter( 'manage_opencx_team_posts_columns', 'opencx_team_admin_columns' );

/**
 * Output custom column values for Team list.
 */
function opencx_team_custom_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'thumbnail':
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 44, 44 ), array( 'style' => 'border-radius: 50%; object-fit: cover;' ) );
			} else {
				echo '<span style="display:inline-block;width:44px;height:44px;border-radius:50%;background:#e0e0e0;line-height:44px;text-align:center;font-size:11px;color:#777;">No pic</span>';
			}
			break;
		case 'role':
			$role = get_post_meta( $post_id, 'opencx_team_role', true );
			echo esc_html( $role ?: '—' );
			break;
		case 'linkedin':
			$linkedin = get_post_meta( $post_id, 'opencx_team_linkedin', true );
			if ( $linkedin ) {
				echo '<a href="' . esc_url( $linkedin ) . '" target="_blank" rel="noopener">View Profile &nearr;</a>';
			} else {
				echo '—';
			}
			break;
		case 'order':
			$post = get_post( $post_id );
			echo intval( $post->menu_order );
			break;
	}
}
add_action( 'manage_opencx_team_posts_custom_column', 'opencx_team_custom_column_content', 10, 2 );

/**
 * Customize columns for Testimonial post list.
 */
function opencx_testimonial_admin_columns( $columns ) {
	$new_columns = array();
	$new_columns['cb']      = $columns['cb'];
	$new_columns['title']   = __( 'Client Name', 'opencx' );
	$new_columns['company'] = __( 'Company / Role', 'opencx' );
	$new_columns['quote']   = __( 'Quote Snippet', 'opencx' );
	$new_columns['order']   = __( 'Order', 'opencx' );
	$new_columns['date']    = $columns['date'];
	return $new_columns;
}
add_filter( 'manage_opencx_testimonial_posts_columns', 'opencx_testimonial_admin_columns' );

/**
 * Output custom column values for Testimonial list.
 */
function opencx_testimonial_custom_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'company':
			$company = get_post_meta( $post_id, 'opencx_testimonial_company', true );
			echo esc_html( $company ?: '—' );
			break;
		case 'quote':
			$quote = get_post_meta( $post_id, 'opencx_testimonial_quote', true ) ?: get_post_field( 'post_content', $post_id );
			echo esc_html( wp_trim_words( $quote, 14, '...' ) ?: '—' );
			break;
		case 'order':
			$post = get_post( $post_id );
			echo intval( $post->menu_order );
			break;
	}
}
add_action( 'manage_opencx_testimonial_posts_custom_column', 'opencx_testimonial_custom_column_content', 10, 2 );


/**
 * -----------------------------------------------------------------------------
 * 3. Frontend Dynamic Shortcodes
 * -----------------------------------------------------------------------------
 */

/**
 * Shortcode to render Team Members in responsive 3-column rows.
 *
 * Usage: [opencx_team_grid]
 *
 * @return string HTML output.
 */
function opencx_team_grid_shortcode() {
	$members = get_posts( array(
		'post_type'      => 'opencx_team',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order date',
		'order'          => 'ASC',
	) );

	// Fallback to initial team members if DB has none yet
	if ( empty( $members ) ) {
		$members_data = array(
			array(
				'name'     => 'Oscar Gomez',
				'role'     => 'CEO',
				'bio'      => 'Oscar is the founder of IntiCo, a leading Company on Customer Experience Technology Solutions for a broad variety of Industries like banking, insurance, retail, market research and global analytics',
				'linkedin' => 'https://www.linkedin.com/',
				'image'    => get_template_directory_uri() . '/assets/images/team-headshot-01.png',
			),
			array(
				'name'     => 'Raul Gomez',
				'role'     => 'Head of Sales',
				'bio'      => 'SaaS Sales Director with a proven track record in the tech industry. Skilled in negotiation, budgeting, and market planning. Fluent in English and Spanish, with intermediate proficiency in Portuguese.',
				'linkedin' => 'https://www.linkedin.com/',
				'image'    => get_template_directory_uri() . '/assets/images/team-headshot-02.png',
			),
			array(
				'name'     => 'Samuel Henostroza',
				'role'     => 'Executive Director',
				'bio'      => 'Over 10 years of experience in VoIP, SMS, Telecommunications Operators, Communication Platforms, and Cloud Solutions. I have led and held responsibilities in technical, operational, and commercial areas.',
				'linkedin' => 'https://www.linkedin.com/',
				'image'    => get_template_directory_uri() . '/assets/images/team-headshot-01.png',
			),
		);

		ob_start();
		?>
		<div class="ocx-about-team__container">
			<div class="ocx-about-team__row">
				<?php foreach ( $members_data as $m ) : ?>
					<div class="ocx-about-team__card">
						<div class="ocx-about-team__card-main">
							<div class="ocx-about-team__figure">
								<img src="<?php echo esc_url( $m['image'] ); ?>" alt="<?php echo esc_attr( $m['name'] ); ?>" class="ocx-about-team__image" width="352" height="352" loading="lazy" />
							</div>
							<div class="ocx-about-team__card-body">
								<div class="ocx-about-team__card-title">
									<p class="ocx-about-team__name"><?php echo esc_html( $m['name'] ); ?></p>
									<p class="ocx-about-team__role"><?php echo esc_html( $m['role'] ); ?></p>
								</div>
								<p class="ocx-about-team__bio"><?php echo esc_html( $m['bio'] ); ?></p>
							</div>
						</div>
						<div class="ocx-about-team__social">
							<a href="<?php echo esc_url( $m['linkedin'] ); ?>" class="ocx-about-team__social-link" aria-label="<?php echo esc_attr( $m['name'] ); ?> on LinkedIn">
								<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/linkedin.svg' ); ?>" alt="" width="24" height="24" />
							</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		$html = ob_get_clean();
		return preg_replace( '/>\s+</', '><', $html );
	}

	// Chunk members into rows of 3 to preserve design system
	$chunks = array_chunk( $members, 3 );
	$default_images = array(
		get_template_directory_uri() . '/assets/images/team-headshot-01.png',
		get_template_directory_uri() . '/assets/images/team-headshot-02.png',
		get_template_directory_uri() . '/assets/images/team-headshot-01.png',
	);

	ob_start();
	foreach ( $chunks as $row_idx => $chunk ) :
	?>
		<div class="ocx-about-team__container">
			<div class="ocx-about-team__row">
				<?php foreach ( $chunk as $idx => $item ) : 
					$name = $item->post_title;
					$role = get_post_meta( $item->ID, 'opencx_team_role', true );
					$bio  = get_post_meta( $item->ID, 'opencx_team_bio', true ) ?: $item->post_content;
					$linkedin = get_post_meta( $item->ID, 'opencx_team_linkedin', true );
					
					$img_url = get_the_post_thumbnail_url( $item->ID, 'large' );
					if ( ! $img_url ) {
						$fallback_idx = ( ( $row_idx * 3 ) + $idx ) % count( $default_images );
						$img_url = $default_images[ $fallback_idx ];
					}
				?>
					<div class="ocx-about-team__card">
						<div class="ocx-about-team__card-main">
							<div class="ocx-about-team__figure">
								<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="ocx-about-team__image" width="352" height="352" loading="lazy" />
							</div>
							<div class="ocx-about-team__card-body">
								<div class="ocx-about-team__card-title">
									<p class="ocx-about-team__name"><?php echo esc_html( $name ); ?></p>
									<?php if ( $role ) : ?>
										<p class="ocx-about-team__role"><?php echo esc_html( $role ); ?></p>
									<?php endif; ?>
								</div>
								<?php if ( $bio ) : ?>
									<p class="ocx-about-team__bio"><?php echo esc_html( $bio ); ?></p>
								<?php endif; ?>
							</div>
						</div>
						<div class="ocx-about-team__social">
							<a href="<?php echo esc_url( $linkedin ?: 'https://www.linkedin.com/' ); ?>" class="ocx-about-team__social-link" aria-label="<?php echo esc_attr( $name ); ?> on LinkedIn">
								<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/linkedin.svg' ); ?>" alt="" width="24" height="24" />
							</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php
	endforeach;
	$html = ob_get_clean();
	return trim( preg_replace( '/\s+/', ' ', preg_replace( '/>\s+</', '><', $html ) ) );
}
add_shortcode( 'opencx_team_grid', 'opencx_team_grid_shortcode' );

/**
 * Shortcode to render dynamic Testimonials slider carousel.
 *
 * Usage: [opencx_testimonials_slider]
 *
 * @return string HTML output.
 */
function opencx_testimonials_slider_shortcode() {
	$testimonials = get_posts( array(
		'post_type'      => 'opencx_testimonial',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order date',
		'order'          => 'ASC',
	) );

	// Fallback to default testimonials if none exist yet
	if ( empty( $testimonials ) ) {
		$testimonials_data = array(
			array(
				'name'    => 'Dimitrios Politis',
				'company' => 'Nexx Burger',
				'quote'   => '"Missing a phone call for our catering truck means losing a $1,750 order to a competitor. Since OpenCX, I\'m not losing that revenue, and my team gets to focus on the customer in front of them instead of running back and forth to the phone."',
			),
			array(
				'name'    => 'Catalina Aristizabal',
				'company' => 'RL Funeral Services',
				'quote'   => '“I keep ownership of everything we\'ve built. If anything ever changes, it comes with me. That\'s the part that won me over."',
			),
			array(
				'name'    => 'Carlos Rodriguez',
				'company' => 'Logistics & Fleet Operations',
				'quote'   => '"OpenCX streamlined our customer communication and driver dispatch from day one. Our response times dropped by 70% while keeping our team relaxed and focused."',
			),
		);
		$count = count( $testimonials_data );

		ob_start();
		?>
		<div class="ocx-testimonial__slider-container">
			<button type="button" class="ocx-testimonial__arrow-btn ocx-testimonial__arrow-btn--prev" aria-label="Previous Testimonial">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/slider-arrow-left.svg' ); ?>" alt="" width="48" height="48" />
			</button>

			<div class="ocx-testimonial__grid">
				<?php foreach ( $testimonials_data as $t ) : ?>
					<div class="ocx-testimonial__item">
						<p class="ocx-testimonial__quote"><?php echo esc_html( $t['quote'] ); ?></p>
						<div class="ocx-testimonial__author-info">
							<span class="ocx-testimonial__author-name"><?php echo esc_html( $t['name'] ); ?></span>
							<span class="ocx-testimonial__author-role"><?php echo esc_html( $t['company'] ); ?></span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<button type="button" class="ocx-testimonial__arrow-btn ocx-testimonial__arrow-btn--next" aria-label="Next Testimonial">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/slider-arrow-right.svg' ); ?>" alt="" width="48" height="48" />
			</button>
		</div>

		<div class="ocx-testimonial__dots">
			<?php for ( $i = 0; $i < $count; $i++ ) : ?>
				<span class="ocx-testimonial__dot<?php echo 0 === $i ? ' ocx-testimonial__dot--active' : ''; ?>"></span>
			<?php endfor; ?>
		</div>
		<?php
		$html = ob_get_clean();
		return preg_replace( '/>\s+</', '><', $html );
	}

	$count = count( $testimonials );

	ob_start();
	?>
	<div class="ocx-testimonial__slider-container">
		<button type="button" class="ocx-testimonial__arrow-btn ocx-testimonial__arrow-btn--prev" aria-label="Previous Testimonial">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/slider-arrow-left.svg' ); ?>" alt="" width="48" height="48" />
		</button>

		<div class="ocx-testimonial__grid">
			<?php foreach ( $testimonials as $item ) : 
				$name    = $item->post_title;
				$company = get_post_meta( $item->ID, 'opencx_testimonial_company', true ) ?: get_post_meta( $item->ID, '_ocx_testimonial_role', true );
				$quote   = get_post_meta( $item->ID, 'opencx_testimonial_quote', true ) ?: ( $item->post_excerpt ?: wp_strip_all_tags( $item->post_content ) );
			?>
				<div class="ocx-testimonial__item">
					<p class="ocx-testimonial__quote"><?php echo esc_html( $quote ); ?></p>
					<div class="ocx-testimonial__author-info">
						<span class="ocx-testimonial__author-name"><?php echo esc_html( $name ); ?></span>
						<?php if ( $company ) : ?>
							<span class="ocx-testimonial__author-role"><?php echo esc_html( $company ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<button type="button" class="ocx-testimonial__arrow-btn ocx-testimonial__arrow-btn--next" aria-label="Next Testimonial">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/slider-arrow-right.svg' ); ?>" alt="" width="48" height="48" />
		</button>
	</div>

	<div class="ocx-testimonial__dots">
		<?php for ( $i = 0; $i < $count; $i++ ) : ?>
			<span class="ocx-testimonial__dot<?php echo 0 === $i ? ' ocx-testimonial__dot--active' : ''; ?>"></span>
		<?php endfor; ?>
	</div>
	<?php
	$html = ob_get_clean();
	return trim( preg_replace( '/\s+/', ' ', preg_replace( '/>\s+</', '><', $html ) ) );
}
add_shortcode( 'opencx_testimonials_slider', 'opencx_testimonials_slider_shortcode' );

/**
 * Clean up rogue wpautop tags injected by WordPress block renderer around shortcodes.
 */
add_filter( 'render_block_core/shortcode', function( $block_content ) {
	return str_replace(
		array( '<p></p>', '</button></p>', '<p><div', '</div></p>', '<p><article', '</article></p>' ),
		array( '', '</button>', '<div', '</div>', '<article', '</article>' ),
		$block_content
	);
} );


/**
 * -----------------------------------------------------------------------------
 * 4. Automatic Database Seeding (Runs once if tables are empty)
 * -----------------------------------------------------------------------------
 */
function opencx_seed_initial_team_and_testimonials() {
	// Seed Team
	if ( ! get_option( 'opencx_team_seeded_v1' ) ) {
		$existing_team = count( get_posts( array(
			'post_type'      => 'opencx_team',
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 1,
		) ) );

		if ( 0 === $existing_team ) {
			$seed_team = array(
				array(
					'name'     => 'Oscar Gomez',
					'role'     => 'CEO',
					'bio'      => 'Oscar is the founder of IntiCo, a leading Company on Customer Experience Technology Solutions for a broad variety of Industries like banking, insurance, retail, market research and global analytics.',
					'linkedin' => 'https://www.linkedin.com/',
					'order'    => 1,
				),
				array(
					'name'     => 'Raul Gomez',
					'role'     => 'Head of Sales',
					'bio'      => 'SaaS Sales Director with a proven track record in the tech industry. Skilled in negotiation, budgeting, and market planning. Fluent in English and Spanish, with intermediate proficiency in Portuguese.',
					'linkedin' => 'https://www.linkedin.com/',
					'order'    => 2,
				),
				array(
					'name'     => 'Samuel Henostroza',
					'role'     => 'Executive Director',
					'bio'      => 'Over 10 years of experience in VoIP, SMS, Telecommunications Operators, Communication Platforms, and Cloud Solutions. I have led and held responsibilities in technical, operational, and commercial areas.',
					'linkedin' => 'https://www.linkedin.com/',
					'order'    => 3,
				),
			);

			foreach ( $seed_team as $t ) {
				$pid = wp_insert_post( array(
					'post_type'    => 'opencx_team',
					'post_title'   => $t['name'],
					'post_content' => $t['bio'],
					'post_status'  => 'publish',
					'menu_order'   => $t['order'],
				) );

				if ( ! is_wp_error( $pid ) ) {
					update_post_meta( $pid, 'opencx_team_role', $t['role'] );
					update_post_meta( $pid, 'opencx_team_linkedin', $t['linkedin'] );
					update_post_meta( $pid, 'opencx_team_bio', $t['bio'] );
				}
			}
		}

		update_option( 'opencx_team_seeded_v1', 1 );
	}

	// Seed Testimonials
	if ( ! get_option( 'opencx_testimonials_seeded_v1' ) ) {
		$existing_testimonials = count( get_posts( array(
			'post_type'      => 'opencx_testimonial',
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 1,
		) ) );

		if ( 0 === $existing_testimonials ) {
			$seed_testimonials = array(
				array(
					'name'    => 'Dimitrios Politis',
					'company' => 'Nexx Burger',
					'quote'   => '"Missing a phone call for our catering truck means losing a $1,750 order to a competitor. Since OpenCX, I\'m not losing that revenue, and my team gets to focus on the customer in front of them instead of running back and forth to the phone."',
					'order'   => 1,
				),
				array(
					'name'    => 'Catalina Aristizabal',
					'company' => 'RL Funeral Services',
					'quote'   => '“I keep ownership of everything we\'ve built. If anything ever changes, it comes with me. That\'s the part that won me over."',
					'order'   => 2,
				),
				array(
					'name'    => 'Carlos Rodriguez',
					'company' => 'Logistics & Fleet Operations',
					'quote'   => '"OpenCX streamlined our customer communication and driver dispatch from day one. Our response times dropped by 70% while keeping our team relaxed and focused."',
					'order'   => 3,
				),
			);

			foreach ( $seed_testimonials as $t ) {
				$pid = wp_insert_post( array(
					'post_type'    => 'opencx_testimonial',
					'post_title'   => $t['name'],
					'post_content' => $t['quote'],
					'post_status'  => 'publish',
					'menu_order'   => $t['order'],
				) );

				if ( ! is_wp_error( $pid ) ) {
					update_post_meta( $pid, 'opencx_testimonial_company', $t['company'] );
					update_post_meta( $pid, 'opencx_testimonial_quote', $t['quote'] );
				}
			}
		}

		update_option( 'opencx_testimonials_seeded_v1', 1 );
	}
}
add_action( 'init', 'opencx_seed_initial_team_and_testimonials', 20 );
