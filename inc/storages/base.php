<?php
defined( 'ABSPATH' ) || die;

/**
 * Base storage.
 */
class RWMB_Base_Storage implements RWMB_Storage_Interface {
	/**
	 * Object type.
	 *
	 * @var string
	 */
	protected $object_type;

	/**
	 * Retrieve metadata for the specified object.
	 *
	 * @param int        $object_id ID of the object metadata is for.
	 * @param string     $meta_key  Optional. Metadata key. If not specified, retrieve all metadata for
	 *                              the specified object.
	 * @param bool|array $args      Optional, default is false.
	 *                              If true, return only the first value of the specified meta_key.
	 *                              If is array, use the `single` element.
	 *                              This parameter has no effect if meta_key is not specified.
	 * @return mixed Single metadata value, or array of values.
	 *
	 * @see get_metadata()
	 */
	public function get( $object_id, $meta_key, $args = false ) {
		if ( is_array( $args ) ) {
			$single = ! empty( $args['single'] );
		} else {
			$single = (bool) $args;
		}

		return get_metadata( $this->object_type, $object_id, $meta_key, $single );
	}

	/**
	 * Add metadata
	 *
	 * @param int    $object_id  ID of the object metadata is for.
	 * @param string $meta_key   Metadata key.
	 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
	 * @param bool   $unique     Optional, default is false.
	 *                           Whether the specified metadata key should be unique for the object.
	 *                           If true, and the object already has a value for the specified metadata key,
	 *                           no change will be made.
	 * @return int|false The meta ID on success, false on failure.
	 *
	 * @see add_metadata()
	 */
	public function add( $object_id, $meta_key, $meta_value, $unique = false ) {
		return add_metadata( $this->object_type, $object_id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update metadata.
	 *
	 * @param int    $object_id  ID of the object metadata is for.
	 * @param string $meta_key   Metadata key.
	 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
	 * @param mixed  $prev_value Optional. If specified, only update existing metadata entries with
	 *                           the specified value. Otherwise, update all entries.
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 *
	 * @see update_metadata()
	 */
	public function update( $object_id, $meta_key, $meta_value, $prev_value = '' ) {
		return update_metadata( $this->object_type, $object_id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Delete metadata.
	 *
	 * @param int    $object_id  ID of the object metadata is for.
	 * @param string $meta_key   Metadata key.
	 * @param mixed  $meta_value Optional. Metadata value. Must be serializable if non-scalar. If specified, only delete
	 *                           metadata entries with this value. Otherwise, delete all entries with the specified meta_key.
	 *                           Pass `null, `false`, or an empty string to skip this check. (For backward compatibility,
	 *                           it is not possible to pass an empty string to delete those entries with an empty string
	 *                           for a value).
	 * @param bool   $delete_all Optional, default is false. If true, delete matching metadata entries for all objects,
	 *                           ignoring the specified object_id. Otherwise, only delete matching metadata entries for
	 *                           the specified object_id.
	 * @return bool True on successful delete, false on failure.
	 *
	 * @see delete_metadata()
	 */
	public function delete( $object_id, $meta_key, $meta_value = '', $delete_all = false ) {
		return delete_metadata( $this->object_type, $object_id, $meta_key, $meta_value, $delete_all );
	}
}
