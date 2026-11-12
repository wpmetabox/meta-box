<?php
namespace MetaBox\Integrations\BlockBindings;

/**
 * Registry for block bindings sources.
 */
class Registry {
	/**
	 * Registered block bindings sources.
	 *
	 * @var Source[]
	 */
	private $sources = [];

	public function add( Source $source ): void {
		$this->sources[] = $source;
	}

	/**
	 * Get all registered sources.
	 *
	 * @return Source[]
	 */
	public function all(): array {
		return $this->sources;
	}
}
