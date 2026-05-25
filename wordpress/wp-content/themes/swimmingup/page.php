<?php get_header(); ?>

<?php if ( have_posts() ) : ?>
	<?php
	while ( have_posts() ) :
		the_post();
		$is_app_guide_page = is_page( array( 'how-to-use-the-app', 'learn-about-app', 'learn-about-the-app' ) );
		$content_is_empty  = '' === trim( wp_strip_all_tags( get_the_content() ) );
		?>
		<section class="section section-page">
			<div class="section__header">
				<h1><?php the_title(); ?></h1>
			</div>

			<div class="page-content<?php echo $is_app_guide_page ? ' page-content--app-guide' : ''; ?>">
				<?php if ( $is_app_guide_page && $content_is_empty ) : ?>
					<p class="empty-state">This page is ready for the app guide content.</p>
				<?php else : ?>
					<?php the_content(); ?>
				<?php endif; ?>
			</div>
		</section>
		<?php
	endwhile;
	?>
<?php endif; ?>

<?php get_footer(); ?>
