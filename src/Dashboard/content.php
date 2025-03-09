<?php defined( 'ABSPATH' ) || die; ?>

<header class="mb-dashboard__header">
	<a href="https://metabox.io?utm_source=dashboard&utm_medium=logo&utm_campaign=meta_box" target="_blank">
		<img class="mb-dashboard__logo" src="<?php echo esc_attr( $this->assets_url ) ?>/logo.svg" alt="Meta Box" />
	</a>

	<input class="mb-dashboard__header__search" type="text" placeholder="<?php esc_attr_e( 'Need some help? Search here...', 'meta-box' ); ?>" />

	<div class="mb-dashboard__header__actions">
		<a href="https://www.facebook.com/groups/metaboxusers" target="_blank">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-label="Facebook">
				<path d="M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z"></path>
			</svg>
		</a>
		<a href="https://www.youtube.com/c/MetaBoxWP" target="_blank">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-label="Youtube">
				<path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"></path>
			</svg>
		</a>
		<a href="https://x.com/wpmetabox" target="_blank">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-label="X">
				<path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"></path>
			</svg>
		</a>
		<a href="https://www.linkedin.com/company/meta-box/" target="_blank">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-label="LinkedIn">
				<circle cx="4.983" cy="5.009" r="2.188"></circle>
				<path d="M9.237 8.855v12.139h3.769v-6.003c0-1.584.298-3.118 2.262-3.118 1.937 0 1.961 1.811 1.961 3.218v5.904H21v-6.657c0-3.27-.704-5.783-4.526-5.783-1.835 0-3.065 1.007-3.568 1.96h-.051v-1.66H9.237zm-6.142 0H6.87v12.139H3.095z"></path>
			</svg>
		</a>
	</div>
</header>

<div class="mb-dashboard__body">

	<div class="mb-dashboard__main">

		<section class="mb-dashboard__actions">
			<h2><?php esc_html_e( 'Getting Started', 'meta-box' ); ?></h2>

			<div class="mb-dashboard__actions__inner">
				<a class="mb-dashboard__action" href="<?= esc_attr( admin_url( 'post-new.php?post_type=mb-post-type' ) ); ?>">
					<span class="mb-dashboard__action__title"><?php esc_html_e( 'Create a post type', 'meta-box' ); ?></span>
					<span class="mb-dashboard__action__description"><?php esc_html_e( 'For a new type of content on your website', 'meta-box' ); ?></span>
				</a>
				<a class="mb-dashboard__action" href="<?= esc_attr( admin_url( 'post-new.php?post_type=mb-taxonomy' ) ); ?>">
					<span class="mb-dashboard__action__title"><?php esc_html_e( 'Create a taxonomy', 'meta-box' ); ?></span>
					<span class="mb-dashboard__action__description"><?php esc_html_e( 'To group or categorize your posts', 'meta-box' ); ?></span>
				</a>
				<a class="mb-dashboard__action" href="<?= esc_attr( admin_url( 'post-new.php?post_type=meta-box' ) ); ?>">
					<span class="mb-dashboard__action__title"><?php esc_html_e( 'Create a field group', 'meta-box' ); ?></span>
					<span class="mb-dashboard__action__description"><?php esc_html_e( 'To add custom fields to your posts', 'meta-box' ); ?></span>
				</a>
				<a class="mb-dashboard__action" href="<?= esc_attr( admin_url( 'post-new.php?post_type=mb-relationship' ) ); ?>">
					<span class="mb-dashboard__action__title"><?php esc_html_e( 'Create a relationship', 'meta-box' ); ?></span>
					<span class="mb-dashboard__action__description"><?php esc_html_e( 'To establish connections between posts', 'meta-box' ); ?></span>
				</a>
			</div>
		</section>

		<section class="mb-dashboard__info">
			<div class="mb-dashboard__tabs">
				<div class="mb-dashboard__tab mb-dashboard__tab--active" data-tab="tutorials">
					<?php esc_html_e( 'Tutorials', 'meta-box' ); ?>
				</div>
				<div class="mb-dashboard__tab" data-tab="videos">
					<?php esc_html_e( 'Videos', 'meta-box' ); ?>
				</div>
				<div class="mb-dashboard__tab" data-tab="compare">
					<?php esc_html_e( 'Lite vs AIO', 'meta-box' ); ?>
					<span class="mb-dashboard__tab__badge"><?php esc_html_e( 'Upgrade', 'meta-box' ); ?></span>
				</div>
			</div>
			<div class="mb-dashboard__tab-pane" data-tab="tutorials"></div>
			<div class="mb-dashboard__tab-pane mb-dashboard__tab-pane--hidden" data-tab="videos"></div>
			<div class="mb-dashboard__tab-pane mb-dashboard__tab-pane--hidden" data-tab="compare">
				<h2>
					<?php
					// Translators: %1$s - Meta Box Lite, %2$s - Meta Box AIO.
					echo esc_html( sprintf( __( '%1$s vs %2$s', 'meta-box' ), 'Meta Box Lite', 'Meta Box AIO' ) );
					?>
				</h2>
				<p><?php esc_html_e( 'Understand what\'s the difference between the two plugins and select what best suit your needs.', 'meta-box' ); ?></p>

				<a class="mb-dashboard__button" href="https://metabox.io/pricing/" target="_blank"><?php esc_html_e( 'Upgrade now', 'meta-box' ); ?></a>
				<table>
					<thead>
						<tr>
							<th align="left">Feature</th>
							<th align="center">Meta Box Lite</th>
							<th align="center">Meta Box AIO</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Pricing</td>
							<td align="center">Free</td>
							<td align="center">Paid</td>
						</tr>
						<tr>
							<td>The Meta Box framework</td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Create custom post types &amp; taxonomies</td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Create custom fields with UI</td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Migration from ACF, Pods, Toolset</td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Intergrations with Elementor, Divi, FacetWP, Yoast SEO,...</td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Relationships</td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Get/set meta values with REST API</td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Limit text length</td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Add custom fields to comments in WordPress</td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Auto update</td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Create custom fields for taxonomies</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Create custom fields for users</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Create custom settings pages</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Create Customizer sections &amp; panels</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Create user profile</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Create custom content models and save their data to custom tables</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Organize custom fields into repeatable groups</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Advanced location rules for field groups</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Toogle fields or field groups with conditional logic</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Frontend submission forms and dashboard</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Create templates and displaying fields with views</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Save post/term meta to custom tables</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Create custom Gutenberg blocks</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Display fields in admin columns</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>UI features: columns, tooltips, tabs</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Premium support</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
						<tr>
							<td>Licensing management system, including sub-licensing</td>
							<td align="center"><svg><use xlink:href="#x"></use></svg></td>
							<td align="center"><svg><use xlink:href="#check-circle"></use></svg></td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td><?php esc_html_e( 'Learn more', 'meta-box' ); ?></td>
							<td align="center">
								<a href="https://metabox.io/lite/" target="_blank">
									Meta Box Lite
									<svg><use xlink:href="#external-link"></use></svg>
								</a>
							</td>
							<td align="center">
								<a href="https://metabox.io/aio/" target="_blank">
									Meta Box AIO
									<svg><use xlink:href="#external-link"></use></svg>
								</a>
							</td>
						</tr>
					</tfoot>
				</table>
			</div>
		</section>
	</div>

	<aside class="mb-dashboard__sidebar">
		<div class="mb-dashboard__widget mb-dashboard__upgrade">
			<div class="mb-dashboard__widget-title"><?php esc_html_e( 'Wanna Advanced Features?', 'meta-box' ); ?></div>
			<div class="mb-dashboard__widget-body">
				<ul>
					<li><svg><use xlink:href="#check-circle-solid"></use></svg><?php esc_html_e( 'Organize fields into groups (repeater)', 'meta-box' ); ?></li>
					<li><svg><use xlink:href="#check-circle-solid"></use></svg><?php esc_html_e( 'Advanced location rules and conditional logic', 'meta-box' ); ?></li>
					<li><svg><use xlink:href="#check-circle-solid"></use></svg><?php esc_html_e( 'Custom tables to boost performance', 'meta-box' ); ?></li>
					<li><svg><use xlink:href="#check-circle-solid"></use></svg><?php esc_html_e( 'Create custom Gutenbeg blocks with low code', 'meta-box' ); ?></li>
					<li><svg><use xlink:href="#check-circle-solid"></use></svg><?php esc_html_e( 'Create login, register and edit profile pages', 'meta-box' ); ?></li>
					<li><svg><use xlink:href="#check-circle-solid"></use></svg><?php esc_html_e( 'Submit & manage posts on the frontend', 'meta-box' ); ?></li>
					<li><svg><use xlink:href="#check-circle-solid"></use></svg><?php esc_html_e( 'And much more!', 'meta-box' ); ?></li>
				</ul>
				<a class="mb-dashboard__button" target="_blank" href="https://metabox.io/pricing/?utm_source=dashboard&utm_medium=cta&utm_campaign=meta_box"><?php esc_html_e( 'Get Meta Box AIO', 'meta-box' ); ?></a>
			</div>
		</div>

		<div class="mb-dashboard__widget">
			<div class="mb-dashboard__widget-title"><?php esc_html_e( 'Leave us a review', 'meta-box' ) ?></div>
			<div class="mb-dashboard__widget-body">
				<p><?php esc_html_e( 'Are you are enjoying Meta Box? We would love to hear your feedback.', 'meta-box' ) ?></p>
				<a href="https://wordpress.org/support/plugin/meta-box/reviews/?filter=5" target="_blank">
					<?php esc_html_e( 'Submit a review', 'meta-box' ) ?>
					<svg><use xlink:href="#external-link"></use></svg>
				</a>
			</div>
		</div>

		<div class="mb-dashboard__widget mb-dashboard__plugins">
			<div class="mb-dashboard__widget-title"><?php esc_html_e( 'Recommended plugins', 'meta-box' ) ?></div>
			<div class="mb-dashboard__widget-body">
				<?php
				$plugins = [
					[
						'title'       => 'Slim SEO',
						'slug'        => 'slim-seo',
						'description' => __( 'Fast & Automated WordPress SEO Plugin', 'meta-box' ),
					],
					[
						'title'       => 'Falcon',
						'slug'        => 'falcon',
						'description' => __( 'WordPress Optimizations & Tweaks', 'meta-box' ),
					],
				];
				?>
				<?php foreach ( $plugins as $plugin ) : ?>
					<div class="mb-dashboard__plugin">
						<img src="<?php echo esc_attr( "{$this->assets_url}/{$plugin['slug']}.svg" ); ?>" alt="<?= esc_attr( $plugin['title'] ); ?>" />
						<div class="mb-dashboard__plugin__text">
							<div class="mb-dashboard__plugin__title"><?= esc_html( $plugin['title'] ); ?></div>
							<div class="mb-dashboard__plugin__description"><?= esc_html( $plugin['description'] ); ?></div>
						</div>
						<?php $status = $this->get_plugin_status( $plugin['slug'] ); ?>
						<span class="mb-dashboard__plugin__status" data-plugin="<?= esc_attr( $plugin['slug'] ); ?>" data-action="<?= esc_attr( $status['action'] ); ?>" data-processing="<?= esc_attr( $status['processing'] ); ?>" data-done="<?= esc_attr( $status['done'] ); ?>">
							<?= esc_html( $status['text'] ); ?>
						</span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="mb-dashboard__widget">
			<div class="mb-dashboard__widget-title"><?php esc_html_e( 'Join the community', 'meta-box' ) ?></div>
			<div class="mb-dashboard__widget-body">
				<p><?php esc_html_e( 'Share opinions, ask questions and help each other on our Meta Box community!', 'meta-box' ); ?></p>
				<a href="https://www.facebook.com/groups/metaboxusers" target="_blank">
					<?php esc_html_e( 'Join our Facebook Group', 'meta-box' ); ?>
					<svg><use xlink:href="#external-link"></use></svg>
				</a>
			</div>
		</div>
	</aside>

</div>

<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
	<symbol id="check-circle-solid" viewBox="0 0 24 24">
		<path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm-1.999 14.413-3.713-3.705L7.7 11.292l2.299 2.295 5.294-5.294 1.414 1.414-6.706 6.706z"></path>
	</symbol>
	<symbol id="check-circle" viewBox="0 0 24 24" fill="#16a34a">
		<path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"></path>
		<path d="M9.999 13.587 7.7 11.292l-1.412 1.416 3.713 3.705 6.706-6.706-1.414-1.414z"></path>
	</symbol>
	<symbol id="x" viewBox="0 0 24 24" fill="#dc2626">
		<path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
	</symbol>
	<symbol id="external-link" viewBox="0 0 24 24">
		<path d="m13 3 3.293 3.293-7 7 1.414 1.414 7-7L21 11V3z"></path>
		<path d="M19 19H5V5h7l-2-2H5c-1.103 0-2 .897-2 2v14c0 1.103.897 2 2 2h14c1.103 0 2-.897 2-2v-5l-2-2v7z"></path>
	</symbol>
</svg>