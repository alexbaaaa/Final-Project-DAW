<?php
if ( post_password_required() ) {
	return;
}
?>

<section id="comments" class="comments-area">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			printf(
				esc_html(
					_n( '%s Comment', '%s Comments', get_comments_number(), 'swimmingup' )
				),
				esc_html( number_format_i18n( get_comments_number() ) )
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply'          => __( 'Leave a comment', 'swimmingup' ),
			'title_reply_before'   => '<h3 id="reply-title" class="comment-reply-title">',
			'title_reply_after'    => '</h3>',
			'class_submit'         => 'submit button-primary',
			'comment_notes_before' => '',
			'comment_notes_after'  => '',
			'label_submit'         => __( 'Post comment', 'swimmingup' ),
		)
	);
	?>
</section>
