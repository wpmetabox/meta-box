<?php
namespace MetaBox\Integrations\BlockBindings;

/**
 * Registry for block bindings sources.
 */
class Registry {
	/**
	 * @var array<string, Source>
	 */
	private $sources = [];

	public function add( Source $source ): void {
		$this->sources[ $source->name() ] = $source;
	}

	/**
	 * @return array<string, Source>
	 */
	public function all(): array {
		return $this->sources;
	}
}
