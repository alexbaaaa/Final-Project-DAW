<?php get_header(); ?>

<?php if ( have_posts() ) : ?>
	<?php
	while ( have_posts() ) :
		the_post();

		$post_id       = get_the_ID();
		$hero_image    = swimmingup_get_post_image_url( $post_id, 'full' );
		$category_ids  = wp_get_post_categories( $post_id );
		$post_author   = get_the_author();
		$post_date     = get_the_date();
		$exclude_posts = array( $post_id );

		$related_query_args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 4,
			'post__not_in'        => $exclude_posts,
			'ignore_sticky_posts' => true,
		);

		if ( ! empty( $category_ids ) ) {
			$related_query_args['category__in'] = $category_ids;
		}

		$related_query = new WP_Query( $related_query_args );

		$featured_query = new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => 4,
				'category_name'       => 'featured',
				'post__not_in'        => $exclude_posts,
				'ignore_sticky_posts' => true,
			)
		);

		$recent_query = new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => 5,
				'post__not_in'        => $exclude_posts,
				'ignore_sticky_posts' => true,
			)
		);
		?>

		<section class="single-hero" style="background-image: linear-gradient(120deg, rgba(0, 33, 92, 0.56), rgba(0, 56, 139, 0.7)), url('<?php echo esc_url( $hero_image ); ?>');">
			<div class="single-hero__content">
				<p class="hero-kicker">Club News</p>
				<h1><?php the_title(); ?></h1>
				<p class="hero-meta">
					<span><?php echo esc_html( $post_author ); ?></span>
					<span>&bull;</span>
					<span><?php echo esc_html( $post_date ); ?></span>
				</p>
			</div>
		</section>

		<section class="section section-single-content">
			<div class="single-layout">
				<article class="single-post-content">
					<?php the_content(); ?>

					<div class="single-post-taxonomy">
						<?php the_category( ', ' ); ?>
					</div>

					<?php
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</article>

				<aside class="single-sidebar">
					<section class="sidebar-block">
						<h2>Related Posts</h2>
						<?php if ( $related_query->have_posts() ) : ?>
							<ul>
								<?php
								while ( $related_query->have_posts() ) :
									$related_query->the_post();
									?>
									<li>
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										<span><?php echo esc_html( get_the_date() ); ?></span>
									</li>
								<?php endwhile; ?>
							</ul>
						<?php else : ?>
							<p>No related posts yet.</p>
						<?php endif; ?>
					</section>

					<section class="sidebar-block">
						<h2>Featured Posts</h2>
						<?php if ( $featured_query->have_posts() ) : ?>
							<ul>
								<?php
								while ( $featured_query->have_posts() ) :
									$featured_query->the_post();
									?>
									<li>
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										<span><?php echo esc_html( get_the_date() ); ?></span>
									</li>
								<?php endwhile; ?>
							</ul>
						<?php else : ?>
							<p>No featured posts available.</p>
						<?php endif; ?>
					</section>

					<section class="sidebar-block">
						<h2>Recent Posts</h2>
						<?php if ( $recent_query->have_posts() ) : ?>
							<ul>
								<?php
								while ( $recent_query->have_posts() ) :
									$recent_query->the_post();
									?>
									<li>
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										<span><?php echo esc_html( get_the_date() ); ?></span>
									</li>
								<?php endwhile; ?>
							</ul>
						<?php else : ?>
							<p>No recent posts available.</p>
						<?php endif; ?>
					</section>
				</aside>
			</div>
		</section>

		<?php wp_reset_postdata(); ?>
		<?php
	endwhile;
	?>
<?php endif; ?>

<?php get_footer(); ?>
