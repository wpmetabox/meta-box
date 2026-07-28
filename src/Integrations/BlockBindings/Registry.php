<?php
namespace MetaBox\Integrations\BlockBindings;

/**
 * Registry for block bindings sources.
 */
class Registry {
	/**
	 * @var Source[]
	 */
	private $sources = [];

	public function add( Source $source ): void {
		$this->sources[] = $source;
	}

	/**
	 * @return Source[]
	 */
	public function all(): array {
		return $this->sources;
	}
}
