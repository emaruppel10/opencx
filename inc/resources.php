<?php
/**
 * Dynamic Resources Rendering: Client Perspectives (Interviews), Blog, and Newsroom.
 *
 * Connects the WordPress database (CPTs and Posts) directly to the Resources page
 * with full in-page interactive expansion and related articles switcher.
 *
 * @package OpenCX
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode to render Client Perspective Interviews (Grid Cards + Expandable Panels).
 *
 * Usage: [opencx_resources_interviews]
 */
function opencx_resources_interviews_shortcode() {
	$interviews = get_posts( array(
		'post_type'      => 'opencx_interview',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'ASC',
	) );

	if ( empty( $interviews ) ) {
		return '<p class="ocx-res-empty">No interviews published yet.</p>';
	}

	ob_start();
	?>
	<!-- Interviews Cards Grid -->
	<div class="ocx-res-perspective__grid" id="perspective-grid">
		<?php foreach ( $interviews as $index => $item ) : 
			$slug = $item->post_name ?: 'interview-' . $item->ID;
			$thumb_url = get_the_post_thumbnail_url( $item->ID, 'medium' );
			if ( ! $thumb_url ) {
				$thumb_url = get_template_directory_uri() . '/assets/images/ins-dimitrios-politis.png';
			}
			$name = get_post_meta( $item->ID, 'opencx_interviewee_name', true ) ?: $item->post_title;
			$role = get_post_meta( $item->ID, 'opencx_interviewee_role', true );
			$company = get_post_meta( $item->ID, 'opencx_interviewee_company', true );
			$role_company = trim( $role . ( $role && $company ? ', ' : '' ) . $company );
		?>
			<article class="ocx-res-card" data-interview="<?php echo esc_attr( $slug ); ?>" tabindex="0" role="button" aria-expanded="false" aria-controls="interview-<?php echo esc_attr( $slug ); ?>">
				<div class="ocx-res-card__body">
					<span class="ocx-res-card__tagline">Interview</span>
					<h3 class="ocx-res-card__title">
						<?php echo esc_html( $name ); ?><?php if ( $role_company ) : ?>,<br><?php echo esc_html( $role_company ); ?><?php endif; ?>
					</h3>
				</div>
				<div class="ocx-res-card__media">
					<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $item->post_title ); ?>" class="ocx-res-card__img" width="174" height="186" loading="lazy" />
				</div>
			</article>
		<?php endforeach; ?>
	</div>

	<!-- Pagination for Client Perspective -->
	<nav class="ocx-pagination" id="perspective-pagination" aria-label="Client Perspective pagination" style="display: none;"></nav>

	<!-- Expandable In-Page Interview Panels Container -->
	<div class="ocx-interview-expand-container">
		<?php foreach ( $interviews as $item ) :
			$slug = $item->post_name ?: 'interview-' . $item->ID;
			$thumb_url = get_the_post_thumbnail_url( $item->ID, 'large' );
			if ( ! $thumb_url ) {
				$thumb_url = get_template_directory_uri() . '/assets/images/ins-dimitrios-politis.png';
			}
			$name = get_post_meta( $item->ID, 'opencx_interviewee_name', true ) ?: $item->post_title;
			$role = get_post_meta( $item->ID, 'opencx_interviewee_role', true );
			$company = get_post_meta( $item->ID, 'opencx_interviewee_company', true );
			$role_company = trim( $role . ( $role && $company ? ', ' : '' ) . $company );
			$raw_qa = get_post_meta( $item->ID, 'opencx_interview_qa', true );
			$qa_list = is_string( $raw_qa ) ? json_decode( $raw_qa, true ) : $raw_qa;
		?>
			<div class="ocx-interview-panel" id="interview-<?php echo esc_attr( $slug ); ?>" role="region" aria-label="Interview <?php echo esc_attr( $name ); ?>">
				<div class="ocx-interview-panel__content">
					<div class="ocx-interview__main">

						<!-- Top Navigation Bar -->
						<div class="ocx-interview-top-nav">
							<button type="button" class="ocx-interview-back-btn" data-close="<?php echo esc_attr( $slug ); ?>">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
								<span>Back to all perspectives</span>
							</button>
						</div>

						<!-- Author Header -->
						<div class="ocx-interview__author" id="author-<?php echo esc_attr( $slug ); ?>">
							<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $item->post_title ); ?>" class="ocx-interview__avatar" width="236" height="252" loading="lazy" />
							<div class="ocx-interview__meta">
								<span class="ocx-interview__tagline">Interview</span>
								<h3 class="ocx-interview__name">
									<?php echo esc_html( $name ); ?><?php if ( $role_company ) : ?>,<br><?php echo esc_html( $role_company ); ?><?php endif; ?>
								</h3>
							</div>
						</div>

						<!-- Q&A List -->
						<div class="ocx-interview__qa-list">
							<?php if ( ! empty( $qa_list ) && is_array( $qa_list ) ) : ?>
								<?php foreach ( $qa_list as $qa ) : ?>
									<div class="ocx-interview__qa-item">
										<h4 class="ocx-interview__qa-speaker"><?php echo esc_html( $qa['speaker'] ?? '' ); ?></h4>
										<p class="ocx-interview__qa-text"><?php echo esc_html( $qa['text'] ?? '' ); ?></p>
										<hr class="ocx-interview__qa-divider" />
									</div>
								<?php endforeach; ?>
							<?php else : ?>
								<div class="ocx-interview__body-content">
									<?php echo apply_filters( 'the_content', $item->post_content ); ?>
								</div>
							<?php endif; ?>
						</div>

						<!-- Actions -->
						<div class="ocx-interview__actions">
							<button type="button" class="ocx-interview__top-btn" data-scroll-top="author-<?php echo esc_attr( $slug ); ?>">
								<span>Top</span>
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
							</button>
						</div>

					</div>

					<!-- More Content Switcher Section -->
					<div class="ocx-interview__more">
						<div class="ocx-interview__more-header">
							<h3 class="ocx-interview__more-heading">Other Client Perspectives</h3>
						</div>
						<?php foreach ( $interviews as $other ) :
							if ( $other->ID === $item->ID ) continue;
							$other_slug = $other->post_name ?: 'interview-' . $other->ID;
							$other_thumb = get_the_post_thumbnail_url( $other->ID, 'medium' ) ?: get_template_directory_uri() . '/assets/images/ins-dimitrios-politis.png';
							$other_name = get_post_meta( $other->ID, 'opencx_interviewee_name', true ) ?: $other->post_title;
						?>
							<article class="ocx-interview__more-card" data-switch-interview="<?php echo esc_attr( $other_slug ); ?>" tabindex="0" role="button" aria-label="Switch to interview with <?php echo esc_attr( $other_name ); ?>">
								<div class="ocx-interview__more-body">
									<span class="ocx-interview__more-tagline">Interview</span>
									<h4 class="ocx-interview__more-title"><?php echo esc_html( $other_name ); ?></h4>
								</div>
								<img src="<?php echo esc_url( $other_thumb ); ?>" alt="<?php echo esc_attr( $other_name ); ?>" class="ocx-interview__more-img" width="174" height="186" loading="lazy" />
							</article>
						<?php endforeach; ?>
					</div>

				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'opencx_resources_interviews', 'opencx_resources_interviews_shortcode' );

/**
 * Shortcode to render Blog Cards Grid + In-Page Expandable Panels.
 *
 * Usage: [opencx_resources_blog]
 */
function opencx_resources_blog_shortcode() {
	$blogs = get_posts( array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	if ( empty( $blogs ) ) {
		return '<p class="ocx-res-empty">No blog articles published yet.</p>';
	}

	ob_start();
	?>
	<!-- Active Filter Bar for Blog Grid -->
	<div class="ocx-res-active-filter-bar" id="blog-active-filter-bar" style="display: none;">
		<div class="ocx-res-filter-chip">
			<span class="ocx-res-filter-chip-label" id="blog-filter-label"></span>
			<button type="button" class="ocx-res-filter-clear-btn" id="blog-filter-clear-btn" aria-label="Clear filter" title="Clear filter">&times;</button>
		</div>
		<span class="ocx-res-filter-count" id="blog-filter-count"></span>
	</div>

	<!-- Blog Cards Grid -->
	<div class="ocx-res-perspective__grid" id="blog-grid">
		<?php foreach ( $blogs as $item ) :
			$slug = $item->post_name ?: 'blog-' . $item->ID;
			$thumb_url = get_the_post_thumbnail_url( $item->ID, 'medium' );
			if ( ! $thumb_url ) {
				$thumb_url = get_template_directory_uri() . '/assets/images/about-human-led-ai.webp';
			}
			$tagline = get_post_meta( $item->ID, '_opencx_blog_tagline', true ) ?: 'Blog';
			$read_time = get_post_meta( $item->ID, '_opencx_blog_read_time', true ) ?: '5 min read';
			$author_name = get_the_author_meta( 'display_name', $item->post_author ) ?: 'OpenCX Staff';

			// Get tags
			$post_tag_objs = get_the_tags( $item->ID ) ?: array();
			$tag_names = array();
			foreach ( $post_tag_objs as $t_obj ) {
				$tag_names[] = $t_obj->name;
			}
			$meta_tags = get_post_meta( $item->ID, '_opencx_tags', true );
			if ( ! empty( $meta_tags ) ) {
				$split_meta = array_map( 'trim', explode( ',', $meta_tags ) );
				foreach ( $split_meta as $sm ) {
					if ( ! empty( $sm ) && ! in_array( $sm, $tag_names, true ) ) {
						$tag_names[] = $sm;
					}
				}
			}
			$tags_attr = esc_attr( strtolower( implode( ',', $tag_names ) ) );
			$author_attr = esc_attr( strtolower( $author_name ) );
		?>
			<article class="ocx-res-card ocx-res-card--blog" data-blog="<?php echo esc_attr( $slug ); ?>" data-author="<?php echo $author_attr; ?>" data-tags="<?php echo $tags_attr; ?>" tabindex="0" role="button" aria-expanded="false" aria-controls="blog-<?php echo esc_attr( $slug ); ?>">
				<div class="ocx-res-card__body">
					<span class="ocx-res-card__tagline"><?php echo esc_html( $tagline ); ?> &bull; <?php echo esc_html( $read_time ); ?></span>
					<h3 class="ocx-res-card__title"><?php echo esc_html( $item->post_title ); ?></h3>
				</div>
				<div class="ocx-res-card__media">
					<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $item->post_title ); ?>" class="ocx-res-card__img" width="174" height="186" loading="lazy" />
				</div>
			</article>
		<?php endforeach; ?>
	</div>

	<!-- Pagination for Blog -->
	<nav class="ocx-pagination" id="blog-pagination" aria-label="Blog pagination" style="display: none;"></nav>

	<!-- Expandable In-Page Blog Panels Container -->
	<div class="ocx-interview-expand-container">
		<?php foreach ( $blogs as $item ) :
			$slug = $item->post_name ?: 'blog-' . $item->ID;
			$thumb_large = get_the_post_thumbnail_url( $item->ID, 'large' );
			$tagline = get_post_meta( $item->ID, '_opencx_blog_tagline', true ) ?: 'Blog';
			$read_time = get_post_meta( $item->ID, '_opencx_blog_read_time', true ) ?: '5 min read';
			$date_disp = get_the_date( 'F j, Y', $item->ID );
			$author_name = get_the_author_meta( 'display_name', $item->post_author ) ?: 'OpenCX Editorial Team';

			// Get tags
			$post_tag_objs = get_the_tags( $item->ID ) ?: array();
			$tag_names = array();
			foreach ( $post_tag_objs as $t_obj ) {
				$tag_names[] = $t_obj->name;
			}
			$meta_tags = get_post_meta( $item->ID, '_opencx_tags', true );
			if ( ! empty( $meta_tags ) ) {
				$split_meta = array_map( 'trim', explode( ',', $meta_tags ) );
				foreach ( $split_meta as $sm ) {
					if ( ! empty( $sm ) && ! in_array( $sm, $tag_names, true ) ) {
						$tag_names[] = $sm;
					}
				}
			}
		?>
			<div class="ocx-interview-panel" id="blog-<?php echo esc_attr( $slug ); ?>" role="region" aria-label="Blog post <?php echo esc_attr( $item->post_title ); ?>">
				<div class="ocx-interview-panel__content">
					<div class="ocx-interview__main">

						<!-- Top Navigation Bar -->
						<div class="ocx-interview-top-nav">
							<button type="button" class="ocx-interview-back-btn" data-close-blog="<?php echo esc_attr( $slug ); ?>">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
								<span>Back to all blog articles</span>
							</button>
						</div>

						<!-- Article Header -->
						<div class="ocx-article__header" id="header-blog-<?php echo esc_attr( $slug ); ?>">
							<div class="ocx-article__meta">
								<span class="ocx-interview__tagline"><?php echo esc_html( $tagline ); ?> &bull; <?php echo esc_html( $read_time ); ?> &bull; <?php echo esc_html( $date_disp ); ?></span>
								<h1 class="ocx-article__title"><?php echo esc_html( $item->post_title ); ?></h1>
								<div class="ocx-article__byline">
									<span>Written by <button type="button" class="ocx-author-filter-btn ocx-filter-trigger" data-filter-type="author" data-filter-value="<?php echo esc_attr( $author_name ); ?>" title="Filter all articles by <?php echo esc_attr( $author_name ); ?>"><strong><?php echo esc_html( $author_name ); ?></strong></button></span>
								</div>
								<?php if ( ! empty( $tag_names ) ) : ?>
									<div class="ocx-article__tags">
										<span class="ocx-article__tags-label">Tags:</span>
										<?php foreach ( $tag_names as $t_name ) : ?>
											<button type="button" class="ocx-tag-pill ocx-filter-trigger" data-filter-type="tag" data-filter-value="<?php echo esc_attr( $t_name ); ?>" title="Filter by tag: <?php echo esc_attr( $t_name ); ?>">
												<span class="ocx-tag-pill__hash">#</span><span class="ocx-tag-pill__text"><?php echo esc_html( $t_name ); ?></span>
											</button>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>

						<!-- Featured Image Artwork -->
						<?php if ( $thumb_large ) : ?>
							<div class="ocx-article__featured-wrap">
								<img src="<?php echo esc_url( $thumb_large ); ?>" alt="<?php echo esc_attr( $item->post_title ); ?>" class="ocx-article__featured-img" loading="lazy" />
							</div>
						<?php endif; ?>

						<!-- Article Content Body -->
						<div class="ocx-article__content">
							<?php echo apply_filters( 'the_content', $item->post_content ); ?>
						</div>

						<!-- Actions -->
						<div class="ocx-interview__actions">
							<button type="button" class="ocx-interview__top-btn" data-scroll-top="header-blog-<?php echo esc_attr( $slug ); ?>">
								<span>Top</span>
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
							</button>
						</div>

					</div>

					<!-- Other Blog Articles Switcher Section -->
					<div class="ocx-interview__more">
						<div class="ocx-interview__more-header">
							<h3 class="ocx-interview__more-heading">Other Blog Articles</h3>
						</div>
						<?php foreach ( $blogs as $other ) :
							if ( $other->ID === $item->ID ) continue;
							$other_slug = $other->post_name ?: 'blog-' . $other->ID;
							$other_thumb = get_the_post_thumbnail_url( $other->ID, 'medium' ) ?: get_template_directory_uri() . '/assets/images/about-human-led-ai.webp';
							$other_tagline = get_post_meta( $other->ID, '_opencx_blog_tagline', true ) ?: 'Blog';
							$other_read = get_post_meta( $other->ID, '_opencx_blog_read_time', true ) ?: '5 min read';
						?>
							<article class="ocx-interview__more-card" data-switch-blog="<?php echo esc_attr( $other_slug ); ?>" tabindex="0" role="button" aria-label="Read article: <?php echo esc_attr( $other->post_title ); ?>">
								<div class="ocx-interview__more-body">
									<span class="ocx-interview__more-tagline"><?php echo esc_html( $other_tagline ); ?> &bull; <?php echo esc_html( $other_read ); ?></span>
									<h4 class="ocx-interview__more-title"><?php echo esc_html( $other->post_title ); ?></h4>
								</div>
								<img src="<?php echo esc_url( $other_thumb ); ?>" alt="<?php echo esc_attr( $other->post_title ); ?>" class="ocx-interview__more-img" width="174" height="186" loading="lazy" />
							</article>
						<?php endforeach; ?>
					</div>

				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'opencx_resources_blog', 'opencx_resources_blog_shortcode' );

/**
 * Shortcode to render Newsroom Cards Grid + In-Page Expandable Panels.
 *
 * Usage: [opencx_resources_newsroom]
 */
function opencx_resources_newsroom_shortcode() {
	$news = get_posts( array(
		'post_type'      => 'opencx_news',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	if ( empty( $news ) ) {
		return '<p class="ocx-res-empty">No newsroom items published yet.</p>';
	}

	ob_start();
	?>
	<!-- Active Filter Bar for Newsroom Grid -->
	<div class="ocx-res-active-filter-bar" id="newsroom-active-filter-bar" style="display: none;">
		<div class="ocx-res-filter-chip">
			<span class="ocx-res-filter-chip-label" id="newsroom-filter-label"></span>
			<button type="button" class="ocx-res-filter-clear-btn" id="newsroom-filter-clear-btn" aria-label="Clear filter" title="Clear filter">&times;</button>
		</div>
		<span class="ocx-res-filter-count" id="newsroom-filter-count"></span>
	</div>

	<!-- Newsroom Cards Grid -->
	<div class="ocx-res-perspective__grid" id="newsroom-grid">
		<?php foreach ( $news as $item ) :
			$slug = $item->post_name ?: 'news-' . $item->ID;
			$thumb_url = get_the_post_thumbnail_url( $item->ID, 'medium' );
			if ( ! $thumb_url ) {
				$thumb_url = get_template_directory_uri() . '/assets/images/solaas-agent-behavior.webp';
			}
			$news_type = get_post_meta( $item->ID, 'opencx_news_type', true ) ?: 'Press Release';
			$date_disp = get_post_meta( $item->ID, 'opencx_news_date_display', true ) ?: get_the_date( 'M Y', $item->ID );

			// Tags
			$post_tag_objs = get_the_tags( $item->ID ) ?: array();
			$tag_names = array();
			foreach ( $post_tag_objs as $t_obj ) {
				$tag_names[] = $t_obj->name;
			}
			$meta_tags = get_post_meta( $item->ID, '_opencx_tags', true );
			if ( ! empty( $meta_tags ) ) {
				$split_meta = array_map( 'trim', explode( ',', $meta_tags ) );
				foreach ( $split_meta as $sm ) {
					if ( ! empty( $sm ) && ! in_array( $sm, $tag_names, true ) ) {
						$tag_names[] = $sm;
					}
				}
			}
			$tags_attr = esc_attr( strtolower( implode( ',', $tag_names ) ) );
		?>
			<article class="ocx-res-card ocx-res-card--newsroom" data-news="<?php echo esc_attr( $slug ); ?>" data-tags="<?php echo $tags_attr; ?>" tabindex="0" role="button" aria-expanded="false" aria-controls="news-<?php echo esc_attr( $slug ); ?>">
				<div class="ocx-res-card__body">
					<span class="ocx-res-card__tagline"><?php echo esc_html( $news_type ); ?> &bull; <?php echo esc_html( $date_disp ); ?></span>
					<h3 class="ocx-res-card__title"><?php echo esc_html( $item->post_title ); ?></h3>
				</div>
				<div class="ocx-res-card__media">
					<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $item->post_title ); ?>" class="ocx-res-card__img" width="174" height="186" loading="lazy" />
				</div>
			</article>
		<?php endforeach; ?>
	</div>

	<!-- Pagination for Newsroom -->
	<nav class="ocx-pagination" id="newsroom-pagination" aria-label="Newsroom pagination" style="display: none;"></nav>

	<!-- Expandable In-Page Newsroom Panels Container -->
	<div class="ocx-interview-expand-container">
		<?php foreach ( $news as $item ) :
			$slug = $item->post_name ?: 'news-' . $item->ID;
			$thumb_large = get_the_post_thumbnail_url( $item->ID, 'large' );
			$news_type = get_post_meta( $item->ID, 'opencx_news_type', true ) ?: 'Press Release';
			$date_disp = get_post_meta( $item->ID, 'opencx_news_date_display', true ) ?: get_the_date( 'F j, Y', $item->ID );
			$source    = get_post_meta( $item->ID, 'opencx_news_source', true ) ?: 'OpenCX Communications';
			$contact   = get_post_meta( $item->ID, 'opencx_news_press_contact', true );
			$ext_url   = get_post_meta( $item->ID, 'opencx_news_external_url', true );

			// Tags
			$post_tag_objs = get_the_tags( $item->ID ) ?: array();
			$tag_names = array();
			foreach ( $post_tag_objs as $t_obj ) {
				$tag_names[] = $t_obj->name;
			}
			$meta_tags = get_post_meta( $item->ID, '_opencx_tags', true );
			if ( ! empty( $meta_tags ) ) {
				$split_meta = array_map( 'trim', explode( ',', $meta_tags ) );
				foreach ( $split_meta as $sm ) {
					if ( ! empty( $sm ) && ! in_array( $sm, $tag_names, true ) ) {
						$tag_names[] = $sm;
					}
				}
			}
		?>
			<div class="ocx-interview-panel" id="news-<?php echo esc_attr( $slug ); ?>" role="region" aria-label="News item <?php echo esc_attr( $item->post_title ); ?>">
				<div class="ocx-interview-panel__content">
					<div class="ocx-interview__main">

						<!-- Top Navigation Bar -->
						<div class="ocx-interview-top-nav">
							<button type="button" class="ocx-interview-back-btn" data-close-news="<?php echo esc_attr( $slug ); ?>">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
								<span>Back to all news</span>
							</button>
						</div>

						<!-- News Header -->
						<div class="ocx-article__header" id="header-news-<?php echo esc_attr( $slug ); ?>">
							<div class="ocx-article__meta">
								<span class="ocx-interview__tagline"><?php echo esc_html( $news_type ); ?> &bull; <?php echo esc_html( $date_disp ); ?></span>
								<h1 class="ocx-article__title"><?php echo esc_html( $item->post_title ); ?></h1>
								<div class="ocx-article__byline">
									<span>Source: <strong><?php echo esc_html( $source ); ?></strong></span>
									<?php if ( $contact ) : ?>
										<span class="ocx-article__sep">&bull;</span>
										<span>Press: <a href="mailto:<?php echo esc_attr( $contact ); ?>"><?php echo esc_html( $contact ); ?></a></span>
									<?php endif; ?>
								</div>
								<?php if ( ! empty( $tag_names ) ) : ?>
									<div class="ocx-article__tags">
										<span class="ocx-article__tags-label">Tags:</span>
										<?php foreach ( $tag_names as $t_name ) : ?>
											<button type="button" class="ocx-tag-pill ocx-filter-trigger" data-filter-type="tag" data-filter-value="<?php echo esc_attr( $t_name ); ?>" title="Filter by tag: <?php echo esc_attr( $t_name ); ?>">
												<span class="ocx-tag-pill__hash">#</span><span class="ocx-tag-pill__text"><?php echo esc_html( $t_name ); ?></span>
											</button>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>

						<!-- Featured Image Artwork -->
						<?php if ( $thumb_large ) : ?>
							<div class="ocx-article__featured-wrap">
								<img src="<?php echo esc_url( $thumb_large ); ?>" alt="<?php echo esc_attr( $item->post_title ); ?>" class="ocx-article__featured-img" loading="lazy" />
							</div>
						<?php endif; ?>

						<!-- News Content Body -->
						<div class="ocx-article__content">
							<?php if ( ! empty( $item->post_excerpt ) ) : ?>
								<p class="ocx-article__lead"><?php echo esc_html( $item->post_excerpt ); ?></p>
							<?php endif; ?>
							<?php echo apply_filters( 'the_content', $item->post_content ); ?>
							<?php if ( ! empty( $ext_url ) ) : ?>
								<div class="ocx-article__ext-action">
									<a href="<?php echo esc_url( $ext_url ); ?>" class="ocx-btn-secondary" target="_blank" rel="noopener noreferrer">
										<span>Read Original Coverage</span>
										<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></svg>
									</a>
								</div>
							<?php endif; ?>
						</div>

						<!-- Actions -->
						<div class="ocx-interview__actions">
							<button type="button" class="ocx-interview__top-btn" data-scroll-top="header-news-<?php echo esc_attr( $slug ); ?>">
								<span>Top</span>
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
							</button>
						</div>

					</div>

					<!-- Other Newsroom Articles Switcher Section -->
					<div class="ocx-interview__more">
						<div class="ocx-interview__more-header">
							<h3 class="ocx-interview__more-heading">Other News & Announcements</h3>
						</div>
						<?php foreach ( $news as $other ) :
							if ( $other->ID === $item->ID ) continue;
							$other_slug = $other->post_name ?: 'news-' . $other->ID;
							$other_thumb = get_the_post_thumbnail_url( $other->ID, 'medium' ) ?: get_template_directory_uri() . '/assets/images/solaas-agent-behavior.webp';
							$other_type = get_post_meta( $other->ID, 'opencx_news_type', true ) ?: 'Press Release';
							$other_date = get_post_meta( $other->ID, 'opencx_news_date_display', true ) ?: get_the_date( 'M Y', $other->ID );
						?>
							<article class="ocx-interview__more-card" data-switch-news="<?php echo esc_attr( $other_slug ); ?>" tabindex="0" role="button" aria-label="Read announcement: <?php echo esc_attr( $other->post_title ); ?>">
								<div class="ocx-interview__more-body">
									<span class="ocx-interview__more-tagline"><?php echo esc_html( $other_type ); ?> &bull; <?php echo esc_html( $other_date ); ?></span>
									<h4 class="ocx-interview__more-title"><?php echo esc_html( $other->post_title ); ?></h4>
								</div>
								<img src="<?php echo esc_url( $other_thumb ); ?>" alt="<?php echo esc_attr( $other->post_title ); ?>" class="ocx-interview__more-img" width="174" height="186" loading="lazy" />
							</article>
						<?php endforeach; ?>
					</div>

				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'opencx_resources_newsroom', 'opencx_resources_newsroom_shortcode' );
