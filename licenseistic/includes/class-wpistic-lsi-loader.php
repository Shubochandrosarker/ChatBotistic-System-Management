<?php
/**
 * Hook loader: collects actions/filters and registers them with WordPress.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_Loader
 */
class WPistic_LSI_Loader {

	/**
	 * Queued actions.
	 *
	 * @var array
	 */
	protected $actions = array();

	/**
	 * Queued filters.
	 *
	 * @var array
	 */
	protected $filters = array();

	/**
	 * Adds an action to the queue.
	 *
	 * @param string   $hook          Hook name.
	 * @param object   $component     Component instance.
	 * @param string   $callback      Method name.
	 * @param int      $priority      Priority.
	 * @param int      $accepted_args Accepted args.
	 * @return void
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions[] = compact( 'hook', 'component', 'callback', 'priority', 'accepted_args' );
	}

	/**
	 * Adds a filter to the queue.
	 *
	 * @param string   $hook          Hook name.
	 * @param object   $component     Component instance.
	 * @param string   $callback      Method name.
	 * @param int      $priority      Priority.
	 * @param int      $accepted_args Accepted args.
	 * @return void
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters[] = compact( 'hook', 'component', 'callback', 'priority', 'accepted_args' );
	}

	/**
	 * Registers all queued hooks with WordPress.
	 *
	 * @return void
	 */
	public function run() {
		foreach ( $this->actions as $h ) {
			add_action( $h['hook'], array( $h['component'], $h['callback'] ), $h['priority'], $h['accepted_args'] );
		}
		foreach ( $this->filters as $h ) {
			add_filter( $h['hook'], array( $h['component'], $h['callback'] ), $h['priority'], $h['accepted_args'] );
		}
	}
}
