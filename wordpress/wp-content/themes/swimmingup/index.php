<?php get_header(); ?>

<section class="section section-news-header">
	<div class="section__header">
		<h1>News</h1>
		<p>Latest posts from CN Churriana de la Vega.</p>
	</div>
</section>

<section class="section section-news-grid">
	<?php if ( have_posts() ) : ?>
		<div class="masonry-grid">
			<?php
			while ( have_posts() ) :
				the_post();
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
		<p class="empty-state">No posts found.</p>
	<?php endif; ?>
</section>

<?php get_footer(); ?>
