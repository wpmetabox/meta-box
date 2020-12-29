<?php
class RWMB_Dashboard {
	private $feed_url;
	private $link;
	private $title;
	private $slug;

	public function __construct( $feed_url, $link, $title ) {
		$this->feed_url = $feed_url;
		$this->link = $link;
		$this->title = $title;
		$this->slug = sanitize_title( $title );

		$transient_name = $this->get_transient_name();
		add_filter( "transient_$transient_name", array( $this, 'transient_for_dashboard_news' ) );
	}

	private function get_transient_name() {
		include ABSPATH . WPINC . '/version.php';
		global $wp_version;

		$locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$prefix = version_compare( $wp_version, '4.8', '>=') ? 'dash_v2_' : 'dash_';
		$widget_id = 'dashboard_primary';

		return version_compare( $wp_version, '4.3', '>=' ) ? $prefix . md5( "{$widget_id}_{$locale}" ) : 'dash_' . md5( $widget_id );
	}

	public function transient_for_dashboard_news($value) {
		return $value . $this->get_dashboard_news_html();
	}

	private function get_dashboard_news_html() {
		$cache_key = $this->slug . '_dashboard_news';
		$output = get_transient( $cache_key );
		if ( false !== $output) {
			return $output;
		}

		$feeds = array(
			$this->slug => array(
				'link' => $this->link,
				'url' => $this->feed_url,
				'title' => $this->title['title'],
				'items' => 3,
				'show_summary' => 0,
				'show_author' => 0,
				'show_date' => 0,
			)
		);
		ob_start();
		wp_dashboard_primary_output( 'dashboard_primary', $feeds );
		$output = ob_get_clean();
		set_transient( $cache_key, $output, DAY_IN_SECONDS );

		return $output;
	}
}