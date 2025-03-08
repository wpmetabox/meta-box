<?php defined( 'ABSPATH' ) || die; ?>

<header class="mb-dashboard__header">
	<a href="https://metabox.io?utm_source=dashboard&utm_medium=logo&utm_campaign=meta_box" target="_blank">
		<img class="mb-dashboard__logo" src="<?php echo esc_attr( $this->assets_url ) ?>/logo.svg" alt="Meta Box" />
	</a>

	<div class="mb-dashboard__header__actions">
		
	</div>
</header>

<div class="mb-dashboard__body">

	<div class="mb-dashboard__main">

		<section class="mb-dashboard__actions">

		</section>

		<section class="mb-dashboard__info">

		</section>
	</div>

	<aside class="mb-dashboard__sidebar">
		<div class="mb-dashboard__widget mb-dashboard__widget--upgrade">
			<div class="mb-dashboard__widget-title"><?php esc_html_e( 'Wanna Advanced Features?', 'meta-box' ); ?></div>
			<div class="mb-dashboard__widget-body">
				<ul>
					<li><svg><use xlink:href="#check-circle"></use></svg><?php esc_html_e( 'Organize fields into groups', 'meta-box' ); ?></li>
					<li><svg><use xlink:href="#check-circle"></use></svg><?php esc_html_e( 'Conditional logic', 'meta-box' ); ?></li>
					<li><svg><use xlink:href="#check-circle"></use></svg><?php esc_html_e( 'Save fields to custom tables', 'meta-box' ); ?></li>
					<li><svg><use xlink:href="#check-circle"></use></svg><?php esc_html_e( 'Create custom Gutenbeg blocks', 'meta-box' ); ?></li>
					<li><svg><use xlink:href="#check-circle"></use></svg><?php esc_html_e( 'Create user profile pages', 'meta-box' ); ?></li>
					<li><svg><use xlink:href="#check-circle"></use></svg><?php esc_html_e( 'Create frontend forms', 'meta-box' ); ?></li>
					<li><svg><use xlink:href="#check-circle"></use></svg><?php esc_html_e( 'And much more!', 'meta-box' ); ?></li>
				</ul>
				<a class="mb-dashboard__button" target="_blank" href="https://metabox.io/pricing/?utm_source=dashboard&utm_medium=cta&utm_campaign=meta_box"><?php esc_html_e( 'Get Meta Box AIO', 'meta-box' ); ?></a>
			</div>
		</div>

		<div class="mb-dashboard__widget">
			<div class="mb-dashboard__widget-title"><?php esc_html_e( 'Our WordPress Products', 'meta-box' ) ?></div>
			<div class="mb-dashboard__widget-body">
				<p><?php esc_html_e( 'Like this plugin? Check out our other WordPress products:', 'meta-box' ) ?></p>
				<p><a href="https://wpslimseo.com?utm_source=dashboard&utm_medium=link&utm_campaign=meta_box" target="_blank">Slim SEO</a> - <?php esc_html_e( 'Automated & fast SEO plugin for WordPress', 'meta-box' ) ?></p>
				<p><a href="https://gretathemes.com?utm_source=dashboard&utm_medium=link&utm_campaign=meta_box" target="_blank">GretaThemes</a> - <?php esc_html_e( 'Simple, elegant and clean WordPress themes', 'meta-box' ) ?></p>
			</div>
		</div>

		<div class="mb-dashboard__widget">
			<div class="mb-dashboard__widget-title"><?php esc_html_e( 'Write a Review', 'meta-box' ) ?></div>
			<div class="mb-dashboard__widget-body">
				<p><?php esc_html_e( 'If you like Meta Box, please write a review on WordPress.org to help us spread the word. We really appreciate that!', 'meta-box' ) ?></p>
				<a href="https://wordpress.org/support/plugin/meta-box/reviews/?filter=5" class="button" target="_blank"><?php esc_html_e( 'Write a review', 'meta-box' ) ?></a>
			</div>
		</div>
	</aside>

</div>

<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
	<symbol id="check-circle" viewBox="0 0 24 24" fill="currentColor">
		<path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
	</symbol>
</svg>
