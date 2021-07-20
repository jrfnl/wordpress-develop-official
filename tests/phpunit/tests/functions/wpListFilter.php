<?php

/**
 * Test wp_filter_object_list(), wp_list_filter(), wp_list_pluck().
 *
 * @group functions.php
 * @covers ::wp_filter_object_list
 * @covers ::wp_list_pluck
 */
class Tests_Functions_wpListFilter extends WP_UnitTestCase_Base {
	public $object_list = array();
	public $array_list  = array();

	function setUp() {
		parent::setUp();
		$this->array_list['foo'] = array(
			'name'   => 'foo',
			'id'     => 'f',
			'field1' => true,
			'field2' => true,
			'field3' => true,
			'field4' => array( 'red' ),
		);
		$this->array_list['bar'] = array(
			'name'   => 'bar',
			'id'     => 'b',
			'field1' => true,
			'field2' => true,
			'field3' => false,
			'field4' => array( 'green' ),
		);
		$this->array_list['baz'] = array(
			'name'   => 'baz',
			'id'     => 'z',
			'field1' => true,
			'field2' => false,
			'field3' => false,
			'field4' => array( 'blue' ),
		);
		foreach ( $this->array_list as $key => $value ) {
			$this->object_list[ $key ] = (object) $value;
		}
	}

	function test_filter_object_list_and() {
		$list = wp_filter_object_list(
			$this->object_list,
			array(
				'field1' => true,
				'field2' => true,
			),
			'AND'
		);
		$this->assertCount( 2, $list );
		$this->assertArrayHasKey( 'foo', $list );
		$this->assertArrayHasKey( 'bar', $list );
	}

	function test_filter_object_list_or() {
		$list = wp_filter_object_list(
			$this->object_list,
			array(
				'field1' => true,
				'field2' => true,
			),
			'OR'
		);
		$this->assertCount( 3, $list );
		$this->assertArrayHasKey( 'foo', $list );
		$this->assertArrayHasKey( 'bar', $list );
		$this->assertArrayHasKey( 'baz', $list );
	}

	function test_filter_object_list_not() {
		$list = wp_filter_object_list(
			$this->object_list,
			array(
				'field2' => true,
				'field3' => true,
			),
			'NOT'
		);
		$this->assertCount( 1, $list );
		$this->assertArrayHasKey( 'baz', $list );
	}

	function test_filter_object_list_and_field() {
		$list = wp_filter_object_list(
			$this->object_list,
			array(
				'field1' => true,
				'field2' => true,
			),
			'AND',
			'name'
		);
		$this->assertSame(
			array(
				'foo' => 'foo',
				'bar' => 'bar',
			),
			$list
		);
	}

	function test_filter_object_list_or_field() {
		$list = wp_filter_object_list(
			$this->object_list,
			array(
				'field2' => true,
				'field3' => true,
			),
			'OR',
			'name'
		);
		$this->assertSame(
			array(
				'foo' => 'foo',
				'bar' => 'bar',
			),
			$list
		);
	}

	function test_filter_object_list_not_field() {
		$list = wp_filter_object_list(
			$this->object_list,
			array(
				'field2' => true,
				'field3' => true,
			),
			'NOT',
			'name'
		);
		$this->assertSame( array( 'baz' => 'baz' ), $list );
	}

	function test_wp_list_pluck() {
		$list = wp_list_pluck( $this->object_list, 'name' );
		$this->assertSame(
			array(
				'foo' => 'foo',
				'bar' => 'bar',
				'baz' => 'baz',
			),
			$list
		);

		$list = wp_list_pluck( $this->array_list, 'name' );
		$this->assertSame(
			array(
				'foo' => 'foo',
				'bar' => 'bar',
				'baz' => 'baz',
			),
			$list
		);
	}

	/**
	 * @ticket 28666
	 */
	function test_wp_list_pluck_index_key() {
		$list = wp_list_pluck( $this->array_list, 'name', 'id' );
		$this->assertSame(
			array(
				'f' => 'foo',
				'b' => 'bar',
				'z' => 'baz',
			),
			$list
		);
	}

	/**
	 * @ticket 28666
	 */
	function test_wp_list_pluck_object_index_key() {
		$list = wp_list_pluck( $this->object_list, 'name', 'id' );
		$this->assertSame(
			array(
				'f' => 'foo',
				'b' => 'bar',
				'z' => 'baz',
			),
			$list
		);
	}

	/**
	 * @ticket 28666
	 */
	function test_wp_list_pluck_missing_index_key() {
		$list = wp_list_pluck( $this->array_list, 'name', 'nonexistent' );
		$this->assertSame(
			array(
				0 => 'foo',
				1 => 'bar',
				2 => 'baz',
			),
			$list
		);
	}

	/**
	 * @ticket 28666
	 */
	function test_wp_list_pluck_partial_missing_index_key() {
		$array_list = $this->array_list;
		unset( $array_list['bar']['id'] );
		$list = wp_list_pluck( $array_list, 'name', 'id' );
		$this->assertSame(
			array(
				'f' => 'foo',
				0   => 'bar',
				'z' => 'baz',
			),
			$list
		);
	}

	/**
	 * @ticket 28666
	 */
	function test_wp_list_pluck_mixed_index_key() {
		$mixed_list        = $this->array_list;
		$mixed_list['bar'] = (object) $mixed_list['bar'];
		$list              = wp_list_pluck( $mixed_list, 'name', 'id' );
		$this->assertSame(
			array(
				'f' => 'foo',
				'b' => 'bar',
				'z' => 'baz',
			),
			$list
		);
	}

	/**
	 * @ticket 16895
	 */
	function test_wp_list_pluck_containing_references() {
		$ref_list = array(
			& $this->object_list['foo'],
			& $this->object_list['bar'],
		);

		$this->assertInstanceOf( 'stdClass', $ref_list[0] );
		$this->assertInstanceOf( 'stdClass', $ref_list[1] );

		$list = wp_list_pluck( $ref_list, 'name' );
		$this->assertSame(
			array(
				'foo',
				'bar',
			),
			$list
		);

		$this->assertInstanceOf( 'stdClass', $ref_list[0] );
		$this->assertInstanceOf( 'stdClass', $ref_list[1] );
	}

	/**
	 * @ticket 16895
	 */
	function test_wp_list_pluck_containing_references_keys() {
		$ref_list = array(
			& $this->object_list['foo'],
			& $this->object_list['bar'],
		);

		$this->assertInstanceOf( 'stdClass', $ref_list[0] );
		$this->assertInstanceOf( 'stdClass', $ref_list[1] );

		$list = wp_list_pluck( $ref_list, 'name', 'id' );
		$this->assertSame(
			array(
				'f' => 'foo',
				'b' => 'bar',
			),
			$list
		);

		$this->assertInstanceOf( 'stdClass', $ref_list[0] );
		$this->assertInstanceOf( 'stdClass', $ref_list[1] );
	}

	function test_filter_object_list_nested_array_and() {
		$list = wp_filter_object_list( $this->object_list, array( 'field4' => array( 'blue' ) ), 'AND' );
		$this->assertCount( 1, $list );
		$this->assertArrayHasKey( 'baz', $list );
	}

	function test_filter_object_list_nested_array_not() {
		$list = wp_filter_object_list( $this->object_list, array( 'field4' => array( 'red' ) ), 'NOT' );
		$this->assertCount( 2, $list );
		$this->assertArrayHasKey( 'bar', $list );
		$this->assertArrayHasKey( 'baz', $list );
	}

	function test_filter_object_list_nested_array_or() {
		$list = wp_filter_object_list(
			$this->object_list,
			array(
				'field3' => true,
				'field4' => array( 'blue' ),
			),
			'OR'
		);
		$this->assertCount( 2, $list );
		$this->assertArrayHasKey( 'foo', $list );
		$this->assertArrayHasKey( 'baz', $list );
	}

	function test_filter_object_list_nested_array_or_singular() {
		$list = wp_filter_object_list( $this->object_list, array( 'field4' => array( 'blue' ) ), 'OR' );
		$this->assertCount( 1, $list );
		$this->assertArrayHasKey( 'baz', $list );
	}

	function test_filter_object_list_nested_array_and_field() {
		$list = wp_filter_object_list( $this->object_list, array( 'field4' => array( 'blue' ) ), 'AND', 'name' );
		$this->assertSame( array( 'baz' => 'baz' ), $list );
	}

	function test_filter_object_list_nested_array_not_field() {
		$list = wp_filter_object_list( $this->object_list, array( 'field4' => array( 'green' ) ), 'NOT', 'name' );
		$this->assertSame(
			array(
				'foo' => 'foo',
				'baz' => 'baz',
			),
			$list
		);
	}

	function test_filter_object_list_nested_array_or_field() {
		$list = wp_filter_object_list(
			$this->object_list,
			array(
				'field3' => true,
				'field4' => array( 'blue' ),
			),
			'OR',
			'name'
		);
		$this->assertSame(
			array(
				'foo' => 'foo',
				'baz' => 'baz',
			),
			$list
		);
	}
}
