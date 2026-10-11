<?php
class RWMB_Core {
	public function init() {
		add_filter( 'plugin_action_links_meta-box/meta-box.php', [ $this, 'plugin_links' ], 20 );

		// Uses priority 20 to support custom post types registered using the default priority.
		add_action( 'init', [ $this, 'register_meta_boxes' ], 20 );

		// Run last, so field groups added by any other filter callback can be reused.
		add_filter( 'rwmb_meta_boxes', [ $this, 'expand_field_groups' ], PHP_INT_MAX );

		add_action( 'edit_page_form', [ $this, 'fix_page_template' ] );
		$this->add_context_hooks();
	}

	public function plugin_links( array $links ): array {
		$links[] = '<a href="https://docs.metabox.io?utm_source=plugin_links&utm_medium=link&utm_campaign=meta_box" target="_blank">' . esc_html__( 'Docs', 'meta-box' ) . '</a>';

		$utc_timezone = new DateTimeZone( 'UTC' );
		$now          = new DateTime( 'now', $utc_timezone );
		$sale_end     = new DateTime( '2025-12-02 00:00:00', $utc_timezone );
		if ( $now < $sale_end ) {
			$links[] = '<a href="https://metabox.io/black-friday/?utm_source=plugin_links&utm_medium=link&utm_campaign=black_friday" target="_blank" style="color: #39b54a; font-weight: bold">' . esc_html__( 'Black Friday Sale', 'meta-box' ) . '</a>';
		}
		return $links;
	}

	public function register_meta_boxes() {
		$configs  = apply_filters( 'rwmb_meta_boxes', [] );
		$registry = rwmb_get_registry( 'meta_box' );

		foreach ( $configs as $config ) {
			if ( ! is_array( $config ) || empty( $config ) ) {
				continue;
			}
			$meta_box = $registry->make( $config );
			$meta_box->register_fields();
		}
	}

	/**
	 * Replace `[ 'use_field_group' => 'meta-box-id' ]` entries with the fields of that meta box.
	 * The referenced meta box can be left unassigned with `'post_types' => []`, so it works as a template.
	 *
	 * @param mixed $configs Meta box configs.
	 * @return mixed
	 */
	public function expand_field_groups( $configs ) {
		if ( ! is_array( $configs ) ) {
			return $configs;
		}

		// Use the same ID the meta box gets when registered, so titles and unsanitized IDs work as references.
		$ids       = [];
		$templates = [];
		foreach ( $configs as $key => $config ) {
			if ( is_array( $config ) && isset( $config['fields'] ) && is_array( $config['fields'] ) ) {
				$ids[ $key ]               = RW_Meta_Box::normalize( $config )['id'];
				$templates[ $ids[ $key ] ] = $config['fields'];
			}
		}

		foreach ( $configs as $key => $config ) {
			if ( isset( $ids[ $key ] ) && is_array( $config ) && is_array( $config['fields'] ) ) {
				$config['fields'] = $this->expand_fields( $config['fields'], $templates, [ $ids[ $key ] ] );
				$configs[ $key ]  = $config;
			}
		}

		return $configs;
	}

	/**
	 * Expand `use_field_group` references in a list of fields, including nested ones.
	 *
	 * @param array<int|string, mixed>                $fields    List of fields.
	 * @param array<string, array<int|string, mixed>> $templates Fields of every meta box, keyed by meta box ID.
	 * @param string[]                                $stack     IDs of the meta boxes being expanded, to stop circular references.
	 * @return array<int, mixed>
	 */
	private function expand_fields( array $fields, array $templates, array $stack ): array {
		$expanded = [];

		foreach ( $fields as $field ) {
			if ( ! is_array( $field ) ) {
				$expanded[] = $field;
				continue;
			}

			if ( array_key_exists( 'use_field_group', $field ) ) {
				$reference = $field['use_field_group'];
				$id        = is_string( $reference ) ? RWMB_Helpers_String::sanitize_id( $reference ) : '';

				if ( ! isset( $templates[ $id ] ) || in_array( $id, $stack, true ) ) {
					_doing_it_wrong(
						'use_field_group',
						sprintf(
							// translators: %s is the field group ID.
							esc_html__( 'Field group %s does not exist or includes itself. It was skipped.', 'meta-box' ),
							esc_html( is_string( $reference ) ? $reference : gettype( $reference ) )
						),
						'5.16.0'
					);
					continue;
				}

				$stack[]  = $id;
				$expanded = array_merge( $expanded, $this->expand_fields( $templates[ $id ], $templates, $stack ) );
				array_pop( $stack );
				continue;
			}

			if ( ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
				$field['fields'] = $this->expand_fields( $field['fields'], $templates, $stack );
			}
			$expanded[] = $field;
		}

		return $expanded;
	}

	/**
	 * WordPress will prevent post data saving if a page template has been selected that does not exist.
	 * This is especially a problem when switching themes, and old page templates are in the post data.
	 * Unset the page template if the page does not exist to allow the post to save.
	 */
	public function fix_page_template( WP_Post $post ) {
		$template       = get_post_meta( $post->ID, '_wp_page_template', true );
		$page_templates = wp_get_theme()->get_page_templates();

		// If the template doesn't exists, remove the data to allow WordPress to save.
		if ( ! isset( $page_templates[ $template ] ) ) {
			delete_post_meta( $post->ID, '_wp_page_template' );
		}
	}

	/**
	 * Get registered meta boxes via a filter.
	 * @deprecated No longer used. Keep for backward-compatibility with extensions.
	 */
	public static function get_meta_boxes(): array {
		$meta_boxes = rwmb_get_registry( 'meta_box' )->all();
		return wp_list_pluck( $meta_boxes, 'meta_box' );
	}

	public function add_context_hooks() {
		$hooks = [
			'edit_form_top',
			'edit_form_after_title',
			'edit_form_after_editor',
			'edit_form_before_permalink',
		];

		foreach ( $hooks as $hook ) {
			add_action( $hook, [ $this, 'render_meta_boxes_for_context' ] );
		}
	}

	public function render_meta_boxes_for_context( $post ) {
		$hook    = current_filter();
		$context = 'edit_form_top' === $hook ? 'form_top' : substr( $hook, 10 );
		do_meta_boxes( null, $context, $post );
	}
}
