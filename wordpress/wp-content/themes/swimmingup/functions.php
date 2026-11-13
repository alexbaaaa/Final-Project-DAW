<?php
add_filter( 'show_admin_bar', '__return_false' );

function swimmingup_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	register_nav_menus(
		array(
			'main-menu' => __( 'Main Menu', 'swimmingup' ),
		)
	);
}
add_action( 'after_setup_theme', 'swimmingup_theme_setup' );

function swimmingup_enqueue_assets() {
	$theme_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'swimmingup-style',
		get_stylesheet_uri(),
		array(),
		$theme_version
	);

	wp_enqueue_script(
		'swimmingup-theme',
		get_template_directory_uri() . '/assets/js/theme.js',
		array(),
		$theme_version,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'swimmingup_enqueue_assets' );

function swimmingup_current_user_can_delete_comment( $comment ) {
	if ( ! $comment instanceof WP_Comment || ! is_user_logged_in() ) {
		return false;
	}

	$current_user_id = get_current_user_id();

	return $current_user_id > 0 && (int) $comment->user_id === $current_user_id;
}

function swimmingup_get_comment_redirect_url( $comment, $status ) {
	$post_url = get_permalink( (int) $comment->comment_post_ID );

	if ( empty( $post_url ) ) {
		$post_url = home_url( '/' );
	}

	return add_query_arg( 'comment_delete', sanitize_key( $status ), $post_url ) . '#comments';
}

function swimmingup_handle_delete_comment() {
	$comment_id = isset( $_POST['comment_id'] ) ? absint( wp_unslash( $_POST['comment_id'] ) ) : 0;
	$comment    = $comment_id > 0 ? get_comment( $comment_id ) : null;

	if ( ! $comment instanceof WP_Comment ) {
		wp_safe_redirect( add_query_arg( 'comment_delete', 'missing', home_url( '/' ) ) );
		exit;
	}

	check_admin_referer( 'swimmingup_delete_comment_' . $comment_id );

	if ( ! swimmingup_current_user_can_delete_comment( $comment ) ) {
		wp_safe_redirect( swimmingup_get_comment_redirect_url( $comment, 'forbidden' ) );
		exit;
	}

	$deleted = wp_delete_comment( $comment_id, true );

	wp_safe_redirect( swimmingup_get_comment_redirect_url( $comment, $deleted ? 'deleted' : 'failed' ) );
	exit;
}
add_action( 'admin_post_swimmingup_delete_comment', 'swimmingup_handle_delete_comment' );

function swimmingup_comment_delete_form( $comment ) {
	if ( ! swimmingup_current_user_can_delete_comment( $comment ) ) {
		return;
	}
	?>
	<form class="comment-delete-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="swimmingup_delete_comment">
		<input type="hidden" name="comment_id" value="<?php echo esc_attr( $comment->comment_ID ); ?>">
		<?php wp_nonce_field( 'swimmingup_delete_comment_' . $comment->comment_ID ); ?>
		<button class="comment-delete-button" type="submit" onclick="return confirm('<?php echo esc_js( __( 'Delete this comment?', 'swimmingup' ) ); ?>');">
			<?php esc_html_e( 'Delete', 'swimmingup' ); ?>
		</button>
	</form>
	<?php
}

function swimmingup_comment_callback( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	$tag                = 'div' === $args['style'] ? 'div' : 'li';
	$reply_link         = get_comment_reply_link(
		array_merge(
			$args,
			array(
				'add_below' => 'div-comment',
				'depth'     => $depth,
				'max_depth' => $args['max_depth'],
			)
		),
		$comment
	);
	$can_delete_comment = swimmingup_current_user_can_delete_comment( $comment );
	$edit_link_url      = get_edit_comment_link( $comment );
	$edit_link          = '';

	if ( ! empty( $edit_link_url ) ) {
		$edit_link = sprintf(
			'<a class="comment-edit-link" href="%1$s">%2$s</a>',
			esc_url( $edit_link_url ),
			esc_html__( 'Edit', 'swimmingup' )
		);
	}
	?>
	<<?php echo esc_attr( $tag ); ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( '', $comment ); ?>>
		<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
			<footer class="comment-meta">
				<?php if ( 0 !== (int) $args['avatar_size'] ) : ?>
					<div class="comment-avatar">
						<?php echo get_avatar( $comment, (int) $args['avatar_size'] ); ?>
					</div>
				<?php endif; ?>

				<div class="comment-meta-main">
					<div class="comment-author vcard">
						<div class="comment-author-row">
							<b class="fn"><?php comment_author_link( $comment ); ?></b>

							<?php if ( ! empty( $edit_link ) || $can_delete_comment ) : ?>
								<div class="comment-author-actions">
									<?php
									echo $edit_link;

									if ( $can_delete_comment ) {
										swimmingup_comment_delete_form( $comment );
									}
									?>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<div class="comment-metadata">
						<a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
							<time datetime="<?php echo esc_attr( get_comment_time( 'c', false, false, $comment ) ); ?>">
								<?php
								printf(
									esc_html__( '%1$s at %2$s', 'swimmingup' ),
									esc_html( get_comment_date( '', $comment ) ),
									esc_html( get_comment_time( '', false, true, $comment ) )
								);
								?>
							</time>
						</a>
					</div>
				</div>
			</footer>

			<?php if ( '0' === $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'swimmingup' ); ?></p>
			<?php endif; ?>

			<div class="comment-content">
				<?php comment_text( $comment ); ?>
			</div>

			<?php if ( $reply_link ) : ?>
				<div class="comment-actions">
					<?php
					echo $reply_link;
					?>
				</div>
			<?php endif; ?>
		</article>
	<?php
}

function swimmingup_register_query_vars( $query_vars ) {
	$query_vars[] = 'news_category';
	return $query_vars;
}
add_filter( 'query_vars', 'swimmingup_register_query_vars' );

function swimmingup_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'swimmingup_links',
		array(
			'title'    => __( 'SwimmingUp Links', 'swimmingup' ),
			'priority' => 35,
		)
	);

	$wp_customize->add_setting(
		'swimmingup_external_app_url',
		array(
			'default'           => 'http:/swimmingup',
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		'swimmingup_external_app_url',
		array(
			'label'       => __( 'External app URL', 'swimmingup' ),
			'section'     => 'swimmingup_links',
			'type'        => 'url',
			'description' => __( 'URL used for the highlighted menu button.', 'swimmingup' ),
		)
	);
}
add_action( 'customize_register', 'swimmingup_customize_register' );

function swimmingup_get_external_app_url() {
	$url = get_theme_mod( 'swimmingup_external_app_url', 'http:/swimmingup.hopto.org' );

	$legacy_defaults = array(
		'https://swimmingup.hopto.org',
		'http://swimmingup.hopto.org',
	);

	if ( empty( $url ) || in_array( untrailingslashit( $url ), $legacy_defaults, true ) ) {
		$url = 'http:/swimmingup.hopto.org';
	}

	if ( 1 === preg_match( '#^https?:/[^/]#i', $url ) ) {
		$url = preg_replace( '#^(https?):/#i', '$1://', $url, 1 );
	}

	return esc_url( $url );
}

function swimmingup_get_news_url() {
	$posts_page_id = (int) get_option( 'page_for_posts' );

	if ( $posts_page_id > 0 ) {
		return get_permalink( $posts_page_id );
	}

	$news_pages = get_pages(
		array(
			'meta_key'   => '_wp_page_template',
			'meta_value' => 'template-news.php',
			'number'     => 1,
		)
	);

	if ( ! empty( $news_pages ) ) {
		return get_permalink( $news_pages[0]->ID );
	}

	$news_page = get_page_by_path( 'news' );

	if ( $news_page instanceof WP_Post ) {
		return get_permalink( $news_page );
	}

	return home_url( '/news/' );
}

function swimmingup_get_learn_url() {
	$slugs = array(
		'how-to-use-the-app',
		'learn-about-app',
		'learn-about-the-app',
	);

	foreach ( $slugs as $slug ) {
		$page = get_page_by_path( $slug );

		if ( $page instanceof WP_Post ) {
			return get_permalink( $page );
		}
	}

	return home_url( '/how-to-use-the-app/' );
}

function swimmingup_get_selected_news_category_slug() {
	$requested_slug = get_query_var( 'news_category', '' );

	if ( empty( $requested_slug ) && isset( $_GET['news_category'] ) ) {
		$requested_slug = wp_unslash( $_GET['news_category'] );
	}

	if ( '' === $requested_slug ) {
		return '';
	}

	$requested_slug = sanitize_title( $requested_slug );

	if ( empty( $requested_slug ) || 'all' === $requested_slug ) {
		return '';
	}

	$term = get_category_by_slug( $requested_slug );

	if ( ! $term instanceof WP_Term ) {
		return '';
	}

	return $term->slug;
}

function swimmingup_get_default_image_url() {
	$candidates = array(
		'assets/img/default.jpg',
		'assets/img/defaultjpg.jpg',
	);

	foreach ( $candidates as $relative_path ) {
		$absolute_path = trailingslashit( get_template_directory() ) . $relative_path;

		if ( file_exists( $absolute_path ) ) {
			return trailingslashit( get_template_directory_uri() ) . $relative_path;
		}
	}

	return trailingslashit( get_template_directory_uri() ) . 'assets/img/default.jpg';
}

function swimmingup_get_post_image_url( $post_id, $size = 'large' ) {
	$image_url = get_the_post_thumbnail_url( $post_id, $size );

	if ( empty( $image_url ) ) {
		$image_url = swimmingup_get_default_image_url();
	}

	return $image_url;
}

function swimmingup_get_default_avatar_url() {
	$candidates = array(
		'assets/icon/defaultIcon.svg',
		'assets/icons/defaultIcon.svg',
	);

	foreach ( $candidates as $relative_path ) {
		$absolute_path = trailingslashit( get_template_directory() ) . $relative_path;

		if ( file_exists( $absolute_path ) ) {
			return trailingslashit( get_template_directory_uri() ) . $relative_path;
		}
	}

	return '';
}

function swimmingup_avatar_fallback( $args, $id_or_email ) {
	unset( $id_or_email );

	if ( ! empty( $args['found_avatar'] ) ) {
		return $args;
	}

	$avatar_url = swimmingup_get_default_avatar_url();

	if ( empty( $avatar_url ) ) {
		return $args;
	}

	$args['url']          = $avatar_url;
	$args['found_avatar'] = true;

	return $args;
}
add_filter( 'get_avatar_data', 'swimmingup_avatar_fallback', 20, 2 );
