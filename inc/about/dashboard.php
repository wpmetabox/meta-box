<?php
class RWMB_Dashboard {
	private $feed_url;
	private $link;
	private $translations;
	private $slug;

	public function __construct( string $feed_url, string $link, array $translations ) {
		$this->feed_url     = $feed_url;
		$this->link         = $link;
		$this->translations = $translations;
		$this->slug         = sanitize_title( $translations['title'] );

		$transient_name = $this->get_transient_name();
		add_filter( "transient_$transient_name", [ $this, 'add_news' ] );
		add_action( "wp_ajax_{$this->slug}-dismiss-news", [ $this, 'ajax_dismiss' ] );
	}

	private function get_transient_name() : string {
		$locale = get_user_locale();
		return 'dash_v2_' . md5( "dashboard_primary_{$locale}" );
	}

	public function add_news( string $value ) : string {
		$is_dismissed = get_user_meta( get_current_user_id(), $this->slug . '_dismiss_news', true );
		$is_dismissed = apply_filters( 'rwmb_dismiss_dashboard_widget', $is_dismissed );
		if ( $is_dismissed ) {
			return $value;
		}

		ob_start();
		$this->output_script();
		$script = ob_get_clean();

		return $value . $this->get_html() . $script;
	}

	private function get_html() : string {
		$cache_key = $this->slug . '-news';
		$output    = get_transient( $cache_key );
		if ( false !== $output ) {
			return $output;
		}

		$feeds = [
			$this->slug => [
				'link'         => $this->link,
				'url'          => $this->feed_url,
				'title'        => $this->translations['title'],
				'items'        => 3,
				'show_summary' => 0,
				'show_author'  => 0,
				'show_date'    => 0,
			],
		];
		ob_start();
		wp_dashboard_primary_output( 'dashboard_primary', $feeds );
		$output = (string) ob_get_clean();

		$output = (string) preg_replace( '/<a(.+?)>(.+?)<\/a>/i', '<a$1>' . esc_html( $this->translations['title'] ) . ': $2</a>', $output );
		$output = str_replace( '<li>', '<li class="' . esc_attr( $this->slug ) . '-news-item"><a href="#" class="dashicons dashicons-no-alt" title="' . esc_attr( $this->translations['dismiss_tooltip'] ) . '" style="float: right; box-shadow: none; margin-left: 5px;"></a>', $output );

		set_transient( $cache_key, $output, DAY_IN_SECONDS );

		return $output;
	}

	private function output_script() {
		?>
		<script>
		document.addEventListener( 'click', e => {
			if ( !e.target.classList.contains( 'dashicons' ) || !e.target.closest( '.<?php echo esc_js( $this->slug ) ?>-news-item' ) ) {
				return;
			}
			e.preventDefault();
			if ( confirm( "<?php echo esc_js( $this->translations['dismiss_confirm'] ) ?>" ) ) {
				fetch( `${ ajaxurl }?action=<?php echo esc_js( $this->slug ) ?>-dismiss-news&_ajax_nonce=<?php echo esc_js( wp_create_nonce( 'dismiss' ) ) ?>` )
					.then( () => document.querySelectorAll( '.<?php echo esc_js( $this->slug ) ?>-news-item' ).forEach( el => el.remove() ) );
			}
		} );
		</script>
		<?php
	}

	public function ajax_dismiss() {
		check_ajax_referer( 'dismiss' );
		update_user_meta( get_current_user_id(), $this->slug . '_dismiss_news', 1 );
		wp_send_json_success();
	}
}
