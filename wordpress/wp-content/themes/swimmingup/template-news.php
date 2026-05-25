<?php
/**
 * Template Name: News Page
 * Template Post Type: page
 */

get_header();

$page_title = __( 'News', 'swimmingup' );
$page_url   = swimmingup_get_news_url();

if ( have_posts() ) {
	the_post();
	$page_title = get_the_title();
	$page_url   = get_permalink();
}

$selected_category_slug = swimmingup_get_selected_news_category_slug();
$categories             = get_categories(
	array(
		'hide_empty' => true,
	)
);

$news_query_args = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => -1,
	'orderby'             => 'date',
	'order'               => 'DESC',
	'ignore_sticky_posts' => true,
);

if ( ! empty( $selected_category_slug ) ) {
	$news_query_args['category_name'] = $selected_category_slug;
}

$news_query = new WP_Query( $news_query_args );
?>

<section class="section section-news-header">
	<div class="section__header">
		<h1><?php echo esc_html( $page_title ); ?></h1>
		<p>All swimming club updates sorted from newest to oldest.</p>
	</div>

	<form class="news-filter" method="get" action="<?php echo esc_url( $page_url ); ?>">
		<label for="news-category">Filter by category</label>
		<select id="news-category" name="news_category">
			<option value="all">All categories</option>
			<?php foreach ( $categories as $category ) : ?>
				<option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( $selected_category_slug, $category->slug ); ?>>
					<?php echo esc_html( $category->name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<button type="submit">Apply</button>
		<?php if ( ! empty( $selected_category_slug ) ) : ?>
			<a class="news-filter__clear" href="<?php echo esc_url( $page_url ); ?>">Clear</a>
		<?php endif; ?>
	</form>
</section>

<section class="section section-news-grid">
	<?php if ( $news_query->have_posts() ) : ?>
		<div class="masonry-grid">
			<?php
			while ( $news_query->have_posts() ) :
				$news_query->the_post();
				?>
				<article <?php post_class( 'masonry-card' ); ?>>
					<a class="masonry-card__image" href="<?php the_permalink(); ?>">
						<img src="<?php echo esc_url( swimmingup_get_post_image_url( get_the_ID(), 'large' ) ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
					</a>
					<div class="masonry-card__body">
						<div class="masonry-card__meta">
							<span><?php echo esc_html( get_the_date() ); ?></span>
							<span>By <?php the_author(); ?></span>
						</div>
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 26, '...' ) ); ?></p>
						<a class="masonry-card__link" href="<?php the_permalink(); ?>">Read full post</a>
					</div>
				</article>
				<?php
			endwhile;
			?>
		</div>
	<?php else : ?>
		<p class="empty-state">No posts found for this category yet.</p>
	<?php endif; ?>
</section>

<?php wp_reset_postdata(); ?>
<?php get_footer(); ?>
