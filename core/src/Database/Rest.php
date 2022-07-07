<?php
/**
 * Base Custom Database Table Rest Class.
 *
 * @package     Database
 * @subpackage  Rest
 * @copyright   Copyright (c) 2021
 * @license     https://opensource.org/licenses/MIT MIT
 * @since       3.0.0
 */
namespace BerlinDB\Database;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * A base database table rest class, which houses the creation of rest
 * endpoints that a table is made out of.
 *
 * This class is intended to be extended for each unique database table,
 * including global tables for multisite, and users tables.
 *
 * @since 3.0.0
 */
class Rest extends Base {

	/**
	 * String with the field key name for this column.
	 *
	 * @since 3.0.0
	 * @var   string
	 */
	protected $field = '';

	/**
	 * Array with options ofr REST initialization.
	 *
	 * @since 3.0.0
	 * @var   array
	 */
	protected $rest_options = array();

	/**
	 * Array with Column parameters.
	 *
	 * @since 3.0.0
	 * @var   array
	 */
	protected $args = '';

	/**
	 * Initiliaze a new REST endpoint if the column is enabled.
	 *
	 * @since 3.0.0
	 */
	public function __construct( $args = array(), $global_action = '' ) {
		$this->args = $args;
		if ( empty( $this->args ) ) {
			if ( $global_action === 'create' ) {
				$this->rest_options[ 'create' ] = true;
			}

			if ( $global_action === 'shows_all' ) {
				$this->rest_options[ 'shows_all' ] = true;
			}

			\add_action( 'rest_api_init', array( $this, 'initialize_global' ) );
			return;
		}

		if ( !isset( $this->args[ 'rest' ] ) ) {
			return;
		}

		$this->rest_options = $this->args[ 'rest' ];

		if ( isset( $args[ 'crud' ] ) && $args[ 'crud' ] ) {
			$this->rest_options[ 'create' ] = true;
			$this->rest_options[ 'read' ] = true;
			$this->rest_options[ 'update' ] = true;
			$this->rest_options[ 'delete' ] = true;
		}

		\add_action( 'rest_api_init', array( $this, 'initialize_column_value' ) );
	}

	/**
	 * Create a REST Endpoint
	 *
	 * @since 3.0.0
	 *
	 */
	public function initialize_global() {
		if ( isset( $this->rest_options[ 'create' ] ) && $this->rest_options[ 'create' ] ) {
			\register_rest_route(
				'books', // TODO change with the table name
				'create',
				array(
					'methods' => 'POST',
					'callback' => array( $this, 'create' ),
					'args' => array(
						'books' => array( // TODO change with the table name
							'description' => 'Object',
							'type' => 'array' // TODO it is a valid type?
						)
					)
				)
			);
		}
		if ( isset( $this->rest_options[ 'shows_all' ] ) && $this->rest_options[ 'shows_all' ] ) {
			\register_rest_route(
				'books', // TODO change with the table name
				'all',
				array(
					'methods' => 'GET',
					'callback' => array( $this, 'read_all' ),
					'args' => array(
						'offset' => array(
							'default' => 0,
							'type' => 'int' // TODO this type exists?
						),
						'page' => array(
							'default' => 1,
							'type' => 'int' // TODO this type exists?
						)
					)
				)
			);
		}
	}

	public function initialize_column_value() {
		if ( isset( $this->rest_options[ 'read' ] ) && $this->rest_options[ 'read' ] ) {
			\register_rest_route(
				'books', // TODO change with the table name
				'/(?P<' . $this->args[ 'name' ] .'>\d+)',
				array(
					'methods' => 'GET',
					'callback' => array( $this, 'read' ),
					'args' => array(
						$this->args[ 'name' ] => array(
							'description' => $this->args[ 'name' ] . ' key',
							'type' => $this->args[ 'name' ] // TODO the types are the same for REST?
						)
					)
				)
			);
		}
	}

	public function read( \WP_REST_Request $request ) {
		$args = [
			'order'   => 'asc', // TODO this should be a custom argument
		];
		if ( isset( $this->args[ 'name' ] ) ) {
			$args[ $this->args[ 'name' ] ] = $request[ $this->args[ 'name' ] ];
		}

		$query = new \Book_Query( $args ); // TODO auto detect the query class
		foreach ( $query->items as $item ) {
			return \rest_ensure_response( $item );
		}
	}

	public function read_all( \WP_REST_Request $request ) {
		if ( isset( $request['page'] ) ) {
			$request['offset'] = $request['offset'] * $request['page'];
		}

		$args = [
			'order'   => 'asc', // TODO this should be a custom argument
			'offset'  => $request['offset']
		];

		$query = new \Book_Query( $args ); // TODO auto detect the query class
		return \rest_ensure_response( $query->items );
	}

	public function create( \WP_REST_Request $request ) {
		if ( isset( $request[ 'value' ] ) ) {
			$query = new \Book_Query(); // TODO auto detect the query class
			$query->add_item( apply_filters( 'berlindb_rest_books_create', $request[ 'value' ] ) ); // TODO auto detect the query class
			return \rest_ensure_response( array( 'success' => true ) ); // TODO We want strings or a custom text?
		}
		return \rest_ensure_response( array( 'fail' => true ) ); // TODO We want strings or a custom text?
	}

}
