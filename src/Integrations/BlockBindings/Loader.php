<?php
namespace MetaBox\Integrations\BlockBindings;

/**
 * Registers block binding sources and enqueues the shared editor script.
 *
 * Sources are stored in `rwmb_get_registry( 'block_bindings' )`.
 */
class Loader {
	public function __construct() {
		add_action( 'init', [ $this, 'register' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue' ] );
	}

	public function register(): void {
		$sources = rwmb_get_registry( 'block_bindings' )->all();
		foreach ( $sources as $source ) {
			$source->register();
		}
	}

	public function enqueue(): void {
		$data    = [];
		$sources = rwmb_get_registry( 'block_bindings' )->all();
		foreach ( $sources as $source ) {
			$item = $source->get_editor_data();
			if ( empty( $item['fields'] ) ) {
				continue;
			}
			$data[] = $item;
		}

		if ( ! $data ) {
			return;
		}

		wp_enqueue_script( 'rwmb-block-bindings', RWMB_JS_URL . 'block-bindings.js', [ 'wp-blocks', 'wp-i18n' ], RWMB_VER, true );
		wp_localize_script( 'rwmb-block-bindings', 'rwmbBlockBindings', [
			'sources' => $data,
		] );
	}
}
