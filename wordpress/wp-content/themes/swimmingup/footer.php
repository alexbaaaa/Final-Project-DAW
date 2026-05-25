<?php
$privacy_url = get_privacy_policy_url();

if ( empty( $privacy_url ) ) {
	$privacy_url = home_url( '/privacy-policy/' );
}
?>

</main>

<footer class="site-footer">
	<div class="site-footer__inner">
		<div class="site-footer__column">
			<h2>SwimmingUp App</h2>
			<ul>
				<li><a href="https://instagram.com/swimmingup_app" target="_blank" rel="noopener noreferrer">Instagram @swimmingup_app</a></li>
				<li><a href="https://facebook.com/swimmingup.app" target="_blank" rel="noopener noreferrer">Facebook SwimmingUp App</a></li>
				<li><a href="https://x.com/swimmingup_app" target="_blank" rel="noopener noreferrer">X @swimmingup_app</a></li>
				<li><a href="https://youtube.com/@SwimmingUpApp" target="_blank" rel="noopener noreferrer">YouTube @SwimmingUpApp</a></li>
			</ul>
		</div>

		<div class="site-footer__column">
			<h2>CN Churriana de la Vega</h2>
			<ul>
				<li><a href="https://instagram.com/cnchurrianadelavega" target="_blank" rel="noopener noreferrer">Instagram @cnchurrianadelavega</a></li>
				<li><a href="https://facebook.com/cnchurrianavega" target="_blank" rel="noopener noreferrer">Facebook CN Churriana</a></li>
				<li><a href="https://x.com/cnchurrianavega" target="_blank" rel="noopener noreferrer">X @cnchurrianavega</a></li>
				<li><a href="https://youtube.com/@CNChurrianadelaVega" target="_blank" rel="noopener noreferrer">YouTube @CNChurrianadelaVega</a></li>
			</ul>
		</div>

		<div class="site-footer__column">
			<h2>Site</h2>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
				<li><a href="<?php echo esc_url( swimmingup_get_news_url() ); ?>">News</a></li>
				<li><a href="<?php echo esc_url( swimmingup_get_learn_url() ); ?>">How to use the app</a></li>
				<li><a href="<?php echo esc_url( $privacy_url ); ?>">Privacy Policy</a></li>
			</ul>
		</div>
	</div>

	<div class="site-footer__bottom">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> alexbaaaa. All rights reserved.</p>
		<p>Built for the CN Churriana de la Vega swimming community.</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
