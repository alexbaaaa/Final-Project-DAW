<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
$news_url        = swimmingup_get_news_url();
$learn_url       = swimmingup_get_learn_url();
$external_app_url = swimmingup_get_external_app_url();

$home_active  = is_front_page();
$news_active  = ( is_home() && ! is_front_page() ) || is_category() || is_tag() || is_singular( 'post' ) || is_archive() || is_page_template( 'template-news.php' );
$learn_active = is_page( array( 'how-to-use-the-app', 'learn-about-app', 'learn-about-the-app' ) );
?>

<header class="site-header">
	<div class="site-header__inner">
		<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			SwimmingUp Blog
		</a>

		<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation">
			<span class="menu-toggle__label">Menu</span>
			<span class="menu-toggle__icon" aria-hidden="true"></span>
		</button>

		<nav id="primary-navigation" class="site-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'swimmingup' ); ?>">
			<ul class="site-navigation__list">
				<li>
					<a class="<?php echo $home_active ? 'is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						Home
					</a>
				</li>
				<li>
					<a class="<?php echo $news_active ? 'is-active' : ''; ?>" href="<?php echo esc_url( $news_url ); ?>">
						News
					</a>
				</li>
				<li>
					<a class="<?php echo $learn_active ? 'is-active' : ''; ?>" href="<?php echo esc_url( $learn_url ); ?>">
						How to use the app
					</a>
				</li>
				<li>
					<a class="app-link" href="<?php echo esc_url( $external_app_url ); ?>" target="_blank" rel="noopener noreferrer">
						SwimmingUp Start
					</a>
				</li>
			</ul>
		</nav>
	</div>
</header>

<main class="site-main">
