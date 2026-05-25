<?php get_header(); ?>

<?php
$latest_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 1,
		'ignore_sticky_posts' => true,
	)
);

$latest_post_id    = 0;
$latest_image_url  = swimmingup_get_default_image_url();
$latest_title      = __( 'No posts yet', 'swimmingup' );
$latest_excerpt    = __( 'Your latest club update will appear here once you publish the first post.', 'swimmingup' );
$latest_link       = '';
$latest_author     = '';
$latest_date       = '';

if ( $latest_query->have_posts() ) {
	$latest_query->the_post();

	$latest_post_id   = get_the_ID();
	$latest_image_url = swimmingup_get_post_image_url( $latest_post_id, 'full' );
	$latest_title     = get_the_title();
	$latest_excerpt   = wp_trim_words( get_the_excerpt(), 34, '...' );
	$latest_link      = get_permalink();
	$latest_author    = get_the_author();
	$latest_date      = get_the_date();
}
wp_reset_postdata();
?>

<section class="home-hero" style="background-image: linear-gradient(120deg, rgba(0, 33, 92, 0.7), rgba(0, 56, 139, 0.82)), url('<?php echo esc_url( $latest_image_url ); ?>');">
	<div class="home-hero__content">
		<p class="hero-kicker">Latest Club Update</p>
		<h1><?php echo esc_html( $latest_title ); ?></h1>
		<?php if ( ! empty( $latest_author ) || ! empty( $latest_date ) ) : ?>
			<p class="hero-meta">
				<?php echo esc_html( $latest_author ); ?>
				<?php if ( ! empty( $latest_author ) && ! empty( $latest_date ) ) : ?>
					<span>&bull;</span>
				<?php endif; ?>
				<?php echo esc_html( $latest_date ); ?>
			</p>
		<?php endif; ?>
		<p><?php echo esc_html( $latest_excerpt ); ?></p>
		<?php if ( ! empty( $latest_link ) ) : ?>
			<a class="button-primary" href="<?php echo esc_url( $latest_link ); ?>">Read full post</a>
		<?php endif; ?>
	</div>
</section>

<section class="section section-featured">
	<div class="section__header">
		<h2>Featured News</h2>
		<p>Highlighted updates and important announcements for the club.</p>
	</div>

	<div class="featured-grid">
		<?php
		$featured_args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 6,
			'ignore_sticky_posts' => true,
			'category_name'       => 'featured',
			'post__not_in'        => $latest_post_id ? array( $latest_post_id ) : array(),
		);

		$featured_query = new WP_Query( $featured_args );

		if ( ! $featured_query->have_posts() ) {
			$featured_query = new WP_Query(
				array(
					'post_type'           => 'post',
					'post_status'         => 'publish',
					'posts_per_page'      => 6,
					'ignore_sticky_posts' => true,
					'post__not_in'        => $latest_post_id ? array( $latest_post_id ) : array(),
				)
			);
		}

		if ( $featured_query->have_posts() ) :
			while ( $featured_query->have_posts() ) :
				$featured_query->the_post();
				?>
				<article class="featured-card">
					<a class="featured-card__image" href="<?php the_permalink(); ?>">
						<img src="<?php echo esc_url( swimmingup_get_post_image_url( get_the_ID(), 'large' ) ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
					</a>
					<div class="featured-card__body">
						<p class="featured-card__meta"><?php echo esc_html( get_the_date() ); ?></p>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '...' ) ); ?></p>
						<a class="featured-card__link" href="<?php the_permalink(); ?>">View post</a>
					</div>
				</article>
				<?php
			endwhile;
		else :
			?>
			<p class="empty-state">No featured posts found yet.</p>
			<?php
		endif;

		wp_reset_postdata();
		?>
	</div>
</section>

<?php get_footer(); ?>
