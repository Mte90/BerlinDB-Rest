<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Books_Schema extends \BerlinDB\Database\Schema {

	// EDIT New lines
	public $rest = array(
		'crud' => true, // TODO right now is just Create
		'shows_all' => true
	);
	// EDIT New lines

	public $columns = [

		//id
		'id'           => [
			'name'     => 'id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'extra'    => 'auto_increment',
			'primary'  => true,
			'sortable' => true,
			// EDIT New lines
			'rest' => array(
				'read' => true,
				'delete' => true,
				'update' => true
			)
			// EDIT New lines
		],

		//isbn
		'isbn'         => [
			'name'       => 'isbn',
			'type'       => 'tinytext',
			'unsigned'   => true,
			'searchable' => true,
			'sortable'   => true,
		],

		//title
		'title'         => [
			'name'       => 'title',
			'type'       => 'mediumtext',
			'unsigned'   => true,
			'searchable' => true,
			'sortable'   => true,
			// EDIT New lines
			'rest' => array(
				'search' => true,
			)
			// EDIT New lines
		],

		//author
		'author'         => [
			'name'       => 'author',
			'type'       => 'mediumtext',
			'unsigned'   => true,
			'searchable' => true,
			'sortable'   => true,
		],

		//date_created
		'date_created' => [
			'name'       => 'date_created',
			'type'       => 'datetime',
			'date_query' => true,
			'unsigned'   => true,
			'searchable' => true,
			'sortable'   => true,
		],

		//date_published
		'date_published' => [
			'name'       => 'date_published',
			'type'       => 'datetime',
			'date_query' => true,
			'unsigned'   => true,
			'searchable' => true,
			'sortable'   => true,
		],

	];

}
