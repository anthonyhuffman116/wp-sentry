<?php

/**
 * WordPress Sentry Tracker Base class.
 */
abstract class WP_Sentry_Tracker_Base {

	/**
	 * Holds the sentry dsn.
	 *
	 * @var string
	 */
	private $dsn;

	/**
	 * Holds the sentry options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Holds the sentry context.
	 *
	 * @var array
	 */
	private $context = [
		'user'  => null,
		'tags'  => [],
		'extra' => [],
	];

	/**
	 * Class constructor.
	 *
	 * @param string $dsn    The sentry server dsn.
	 * @param array $options Optional. The sentry client options to use.
	 */
	protected function __construct( $dsn, array $options = [] ) {
		$this->dsn = $dsn;
		$this->options = wp_parse_args( $options, $this->get_default_options() );

		// Register WordPress hooks.
		$this->register_hooks();
	}

	/**
	 * Register WordPress hooks.
	 */
	protected function register_hooks() {
		// Set the current user when available.
		add_action( 'set_current_user', [ $this, 'on_set_current_user' ] );
	}

	/**
	 * Target of set_current_user action.
	 *
	 * @access private
	 */
	public function on_set_current_user() {
		$current_user = wp_get_current_user();

		// Default user context to anonymous.
		$user_context = [
			'id' => 0,
			'name' => 'anonymous',
		];

		// Determine whether the user is logged in assign their details.
		if ( $current_user instanceof WP_User ) {
			if ( $current_user->exists() ) {
				$user_context = [
					'id'    => $current_user->ID,
					'name'  => $current_user->display_name,
					'email' => $current_user->user_email,
				];
			}
		}

		// Filter the user context so that plugins that manage users on their own
		// can provide alternate user context. ie. members plugin
		if ( has_filter( 'wp_sentry_user_context' ) ) {
			$user_context = apply_filters( 'wp_sentry_user_context', $user_context );
		}

		// Finally assign the user context to the client.
		$this->set_user_context( $user_context );
	}

	/**
	 * Get sentry dsn.
	 *
	 * @return string
	 */
	public function get_dsn() {
		return $this->dsn;
	}

	/**
	 * Get sentry options.
	 *
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Get sentry default options.
	 *
	 * @return array
	 */
	public function get_default_options() {
		return [];
	}

	/**
	 * Get sentry context.
	 *
	 * @return array
	 */
	public function get_context() {
		return $this->context;
	}

	/**
	 * Sets the user context.
	 *
	 * @param array $data Associative array of user data
	 */
	public function set_user_context( array $data ) {
		$this->context['user'] = $data;
	}

	/**
	 * Get the user context.
	 *
	 * @return array|null
	 */
	public function get_user_context() {
		return $this->context['user'];
	}

	/**
	 * Appends the tags context.
	 *
	 * @param array $data Associative array of tags
	 */
	public function set_tags_context( array $data ) {
		$this->context['tags'] = array_merge( $this->context['tags'], $data );
	}

	/**
	 * Get the tags context.
	 *
	 * @return array
	 */
	public function get_tags_context() {
		return $this->context['tags'];
	}

	/**
	 * Appends the additional context.
	 *
	 * @param array $data Associative array of extra data
	 */
	public function set_extra_context( array $data ) {
		$this->context['extra'] = array_merge( $this->context['extra'], $data );
	}

	/**
	 * Get the additional context.
	 *
	 * @return array
	 */
	public function get_extra_context() {
		return $this->context['extra'];
	}
}
