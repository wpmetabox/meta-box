<?php
/**
 * Newsletter form
 *
 * @package Meta Box
 */

?>

<form method="post" action="https://app.zetamail.vn/form.php?form=77" target="_blank" class="newsletter">
	<input name="format" value="h" type="hidden">
	<p><?php esc_html_e( 'Subscribe to our newsletter to receive news and tutorials for Meta Box and WordPress.', 'meta-box' ); ?></p>
	<p>
		<input name="email" value="" placeholder="joe@gmail.com" required type="email" class="regular-text">
		<button class="button button-primary"><?php esc_html_e( 'Subscribe', 'meta-box' ); ?></button>
	</p>
</form>
