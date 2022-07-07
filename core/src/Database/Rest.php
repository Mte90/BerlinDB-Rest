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
	 * Array with default parameters for BerlinDB.
	 *
	 * @since 3.0.0
	 * @var   array
	 */
	protected $berlindb_default = array(
		// BerlinDB parameters
		'fields' => '',
		'number' => 100,
		'offset' => 0,
		'no_found_rows' => true,
		'orderby' => '',
		'order' => 'DESC',
		'update_item_cache' => false,
		'update_meta_cache' => false
	);

	/**
	 * Array with default parameters for REST.
	 *
	 * @since 3.0.0
	 * @var   array
	*/
	protected $rest_default = array(
		'page' => array(
			'default' => 1,
			'type' => 'int' // TODO this type exists?
		)
	);

	/**
	 * Array with Column parameters.
	 *
	 * @since 3.0.0
	 * @var   array
	 */
	protected $args = '';

	/**
	 * Array with all the columns (used by some specific feature).
	 *
	 * @since 3.0.0
	 * @var   array
	 */
	protected $all_columns = array();

	/**
	 * Initiliaze a new REST endpoint if the column is enabled.
	 *
	 * @since 3.0.0
	 */
	public function __construct( $args = array(), $global_action = '', $all_columns = array() ) {
		$this->args = $args;
		if ( !empty( $all_columns ) ) {
			$this->all_columns = $all_columns;
		}

		if ( empty( $this->args ) ) {
			if ( $global_action === 'create' ) {
				$this->rest_options[ 'create' ] = true;
			}

			if ( $global_action === 'shows_all' ) {
				$this->rest_options[ 'shows_all' ] = true;
			}

			if ( $global_action === 'enable_search' ) {
				$this->rest_options[ 'enable_search' ] = true;
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
					'methods' => \WP_REST_Server::CREATABLE,
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
					'methods' => \WP_REST_Server::READABLE,
					'callback' => array( $this, 'read_all' ),
					'args' => $this->generate_rest_args()
				)
			);
		}
		if ( isset( $this->rest_options[ 'enable_search' ] ) && $this->rest_options[ 'enable_search' ] ) {
			// TODO don't add if already exists for this endpoint
			\register_rest_route(
				'books', // TODO change with the table name
				'/search/',
					array(
					'methods' => \WP_REST_Server::READABLE,
					'callback' => array( $this, 'search' ),
					'args' => \wp_parse_args( $this->generate_rest_args(), array(
						's' => array(
							'description' => 'Search that string in that key',
							'type' => 'string' // TODO the types are the same for REST?
						),
						'columns' => array(
							'description' => 'Search on those columns',
							'type' => 'array' // TODO the types are the same for REST?
						)
					) )
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
					'methods' => \WP_REST_Server::READABLE,
					'callback' => array( $this, 'read' ),
					'args' => \wp_parse_args( $this->generate_rest_args(), array(
						$this->args[ 'name' ] => array(
							'type' => $this->args[ 'name' ] // TODO the types are the same for REST?
						)
					) )
				)
			);
		}
		if ( isset( $this->rest_options[ 'update' ] ) && $this->rest_options[ 'update' ] ) {
			\register_rest_route(
				'books', // TODO change with the table name
				'/(?P<' . $this->args[ 'name' ] .'>\d+)',
					array(
					'methods' => \WP_REST_Server::EDITABLE,
					'callback' => array( $this, 'update' ),
					'args' => \wp_parse_args( $this->generate_rest_args(), array(
						'meta' => array(
							'description' => 'Object',
							'type' => 'array' // TODO the types are the same for REST?
						)
					) )
				)
			);
		}
		if ( isset( $this->rest_options[ 'delete' ] ) && $this->rest_options[ 'delete' ] ) {
			\register_rest_route(
				'books', // TODO change with the table name
				'/(?P<' . $this->args[ 'name' ] .'>\d+)',
					array(
					'methods' => 'DELETE',
					'callback' => array( $this, 'delete' ),
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

	public function parse_args( \WP_REST_Request $request ) {
		$args = \wp_parse_args( $request->get_params(), $this->berlindb_default );

		// Add support for defacto search WordPress parameter
		if ( isset( $request[ 's' ] ) ) {
			$args[ 'search' ] = $request[ 's' ];
			unset( $args[ 's' ] );
		}

		$args[ 'search_columns' ] = $request[ 'search_columns' ];
		foreach ( $args[ 'search_columns' ] as $key => $name ) {
			if ( !in_array( $name, $this->all_columns ) ) {
				unset( $args[ 'search_columns' ][ $key ] );
			}
		}

		if ( empty( $args[ 'search_columns' ] ) ) {
			$columns_supported = array();
			foreach ( $this->all_columns as $name => $column ) {
				if ( isset( $column[ 'rest' ][ 'search' ] ) && $column[ 'rest' ][ 'search' ] ) {
					$columns_supported[ $column[ 'name' ] ] = true;
				}
			}
			$args[ 'search_columns' ] = $columns_supported;
		}

		if ( isset( $request[ 'page' ], $request[ 'offset' ] ) && $args[ 'offset' ] === 0 ) {
			$args[ 'offset' ] = $request[ 'offset' ] * $request[ 'page' ];
		}

		return $args;
	}

	public function generate_rest_args() {
		$args = $this->rest_default;
		foreach( $this->berlindb_default as $key => $value ) {
			$args[ $key ] = array(
				'default' => $value,
			);
		}

		return $args;
	}

	public function read( \WP_REST_Request $request ) {
		$args = $this->parse_args( $request );
		if ( isset( $this->args[ 'name' ] ) ) {
			$args[ $this->args[ 'name' ] ] = $request[ $this->args[ 'name' ] ];
		}

		$query = new \Book_Query( $args ); // TODO auto detect the query class
		foreach ( $query->items as $item ) {
			return \rest_ensure_response( $item );
		}
	}

	public function read_all( \WP_REST_Request $request ) {
		$args = $this->parse_args( $request );

		$query = new \Book_Query( $args ); // TODO auto detect the query class
		return \rest_ensure_response( $query->items );
	}

	public function create( \WP_REST_Request $request ) {
		$value = \apply_filters( 'berlindb_rest_books_create', $request[ 'value' ] );
		if ( isset( $request[ 'value' ] ) && !\is_wp_error( $value ) ) {
			$query = new \Book_Query(); // TODO auto detect the query class
			$query->add_item( apply_filters( 'berlindb_rest_books_create', $value ) ); // TODO auto detect the query class
			return \rest_ensure_response( array( 'success' => true ) ); // TODO We want strings or a custom text?
		}
		return \rest_ensure_response( array( 'fail' => true ) ); // TODO We want strings or a custom text?
	}

	public function delete( \WP_REST_Request $request ) {
		$delete = \apply_filters( 'berlindb_rest_books_delete', true, $request, $this );
		if ( $delete ) {
			$query = new \Book_Query(); // TODO auto detect the query class
			$query->delete_item( $request[ $this->args[ 'name' ] ] );
			return \rest_ensure_response( array( 'success' => true ) ); // TODO We want strings or a custom text?
		}
		return \rest_ensure_response( array( 'fail' => true ) ); // TODO We want strings or a custom text?
	}

	public function update( \WP_REST_Request $request ) {
		$update = \apply_filters( 'berlindb_rest_books_update', true, $request, $this );
		$item_meta = \apply_filters( 'berlindb_rest_books_update_value', $request[ 'value' ], $request, $this );
		if ( $update  && !\is_wp_error( $item_meta ) ) {
			$query = new \Book_Query(); // TODO auto detect the query class
			$query->update_item( $request[ $this->args[ 'name' ] ], $item_meta );
			return \rest_ensure_response( array( 'success' => true ) ); // TODO We want strings or a custom text?
		}
		return \rest_ensure_response( array( 'fail' => true ) ); // TODO We want strings or a custom text?
	}

	public function search( \WP_REST_Request $request ) {
		// TODO support the search only for the column supported
		$search = \apply_filters( 'berlindb_rest_books_search', true, $request, $this );
		$value = \apply_filters( 'berlindb_rest_books_search_value', $request[ 's' ], $request, $this );
		if ( $search && !empty( $value ) && !\is_wp_error( $value ) ) {
			$args = $this->parse_args( $request );
			$query = new \Book_Query( $args ); // TODO auto detect the query class);
			if ( !empty( $query->items ) ) {
				return \rest_ensure_response( $query->items );
			}
		}
		return \rest_ensure_response( array( 'fail' => true ) ); // TODO We want strings or a custom text?
	}

}
