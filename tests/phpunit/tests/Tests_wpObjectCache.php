<?php

final class Tests_wpObjectCache extends WP_UnitTestCase {

	/**
	 * Test value for use in the magic methods tests.
	 *
	 * @var string
	 */
	const TEST_VALUE_1 = 'testing 1-2-3';

	/**
	 * Test value for use in the magic methods tests.
	 *
	 * @var string
	 */
	const TEST_VALUE_2 = 12345;

	/**
	 * Verify that public properties are not affected by the changes made in the magic methods.
	 *
	 * @ticket 56034
	 *
	 * @coversNothing
	 *
	 * @dataProvider data_public_properties_are_not_affected_by_changes_in_magic_methods
	 *
	 * @param string $name    Property name.
	 * @param mixed  $default Default value for the property.
	 */
	public function test_public_properties_are_not_affected_by_changes_in_magic_methods( $name, $default ) {
		$obj = new WP_Object_Cache();

		/*
		 * Verify initial state.
		 * Note: all public properties in this class have a default value.
		 */
		$this->assertTrue( isset( $obj->$name ), 'Unexpected initial state' );
		$this->assertSame( $default, $obj->$name, 'Initial value does not match expectations' );

		// Overwrite the property value and verify the new state.
		$obj->$name = self::TEST_VALUE_1;
		$this->assertTrue( isset( $obj->$name ), 'Setting the property failed [1]' );
		$this->assertSame( self::TEST_VALUE_1, $obj->$name, 'Property has not been assigned the first value' );

		// Unset the property and verify the new state.
		unset( $obj->$name );
		$this->assertFalse( isset( $obj->$name ), 'Unsetting the property failed' );

		// Set the property again and verify the updated state.
		$obj->$name = self::TEST_VALUE_2;
		$this->assertTrue( isset( $obj->$name ), 'Setting the property failed [2]' );
		$this->assertSame( self::TEST_VALUE_2, $obj->$name, 'Property has not been assigned the second value' );
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public function data_public_properties_are_not_affected_by_changes_in_magic_methods() {
		return array(
			'Declared public property: $cache_hits'   => array(
				'name'    => 'cache_hits',
				'default' => 0,
			),
			'Declared public property: $cache_misses' => array(
				'name'    => 'cache_misses',
				'default' => 0,
			),
		);
	}

	/**
	 * Verify that the state of accessible declared properties can be checked and changed.
	 *
	 * Please take note that this test does not represent the _desired_ behaviour, but the behaviour
	 * we unfortunately need to maintain for BC-reason.
	 *
	 * @ticket 56034
	 *
	 * @covers WP_Object_Cache::__isset
	 * @covers WP_Object_Cache::__get
	 * @covers WP_Object_Cache::__set
	 *
	 * @dataProvider data_magic_methods_declared_properties
	 *
	 * @param string $name    Property name.
	 * @param mixed  $default Default value for the property.
	 */
	public function test_magic_methods_declared_properties( $name, $default ) {
		$obj = new WP_Object_Cache();

		/*
		 * Verify initial state.
		 * Note: all supported properties have a default value or are initialized in the __construct().
		 */
		$this->assertTrue( isset( $obj->$name ), 'Unexpected initial state' );
		$this->assertSame( $default, $obj->$name, 'Initial value does not match expectations' );

		// Overwrite the property value and verify the new state.
		$obj->$name = self::TEST_VALUE_1;
		$this->assertTrue( isset( $obj->$name ), 'Setting the property failed [1]' );
		$this->assertSame( self::TEST_VALUE_1, $obj->$name, 'Property has not been assigned the first value' );

		// Overwrite the property value again and verify the updated state.
		$obj->$name = self::TEST_VALUE_2;
		$this->assertTrue( isset( $obj->$name ), 'Setting the property failed [2]' );
		$this->assertSame( self::TEST_VALUE_2, $obj->$name, 'Property has not been assigned the second value' );
	}

	/**
	 * Verify that accessible declared properties can be unset.
	 *
	 * Please take note that this test does not represent the _desired_ behaviour, but the behaviour
	 * we unfortunately need to maintain for BC-reason.
	 *
	 * Also note that as this test expects an error message, it cannot be combined with the
	 * test for the other magic methods.
	 *
	 * @ticket 56034
	 *
	 * @covers WP_Object_Cache::__unset
	 * @covers WP_Object_Cache::__isset
	 * @covers WP_Object_Cache::__get
	 *
	 * @dataProvider data_magic_methods_declared_properties
	 *
	 * @param string $name Property name.
	 */
	public function test_magic_unset_declared_properties( $name ) {
		$obj = new WP_Object_Cache();

		// Verify initial state (all have a default value).
		$this->assertTrue( isset( $obj->$name ), 'Unexpected initial state' );

		// Unset the property value and verify the new state.
		unset( $obj->$name );
		$this->assertFalse( isset( $obj->$name ), 'Unsetting the property failed' );

		// Make sure that useful PHP native error messages aren't being hidden away by the magic methods.
		$expected_msg = 'Undefined property: WP_Object_Cache::$';
		if ( PHP_VERSION_ID > 80000 ) {
			$this->expectWarning();
			$this->expectWarningMessage( $expected_msg );
		} else {
			$this->expectNotice();
			$this->expectNoticeMessage( $expected_msg );
		}

		$unused = $obj->$name;
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public function data_magic_methods_declared_properties() {
		return array(
			'Declared private property: $cache'           => array(
				'name'    => 'cache',
				'default' => array(),
			),
			'Declared protected property: $global_groups' => array(
				'name'    => 'global_groups',
				'default' => array(),
			),
			'Declared private property: $blog_prefix'     => array(
				'name'    => 'blog_prefix',
				'default' => '', // Value set in constructor.
			),
			'Declared private property: $multisite'       => array(
				'name'    => 'multisite',
				'default' => false, // Value set in constructor.
			),
		);
	}

	/**
	 * Verify that the state of undeclared properties can be checked and changed.
	 *
	 * Please take note that this test does not represent the _desired_ behaviour, but the behaviour
	 * we unfortunately need to maintain for BC-reason.
	 *
	 * @ticket 56034
	 *
	 * @covers WP_Object_Cache::__isset
	 * @covers WP_Object_Cache::__get
	 * @covers WP_Object_Cache::__set
	 */
	public function test_magic_methods_undeclared_properties() {
		$obj  = new WP_Object_Cache();
		$name = 'does_not_exist';

		// Verify initial state.
		$this->assertFalse( isset( $obj->$name ), 'Undeclared property shouldn\'t exist' );

		// Set the property value and verify the state.
		$obj->$name = self::TEST_VALUE_1;
		$this->assertTrue( isset( $obj->$name ), 'Setting the property failed [1]' );
		$this->assertSame( self::TEST_VALUE_1, $obj->$name, 'Property has not been assigned the first value' );

		// Overwrite the property value and verify the updated state.
		$obj->$name = self::TEST_VALUE_2;
		$this->assertTrue( isset( $obj->$name ), 'Setting the property failed [2]' );
		$this->assertSame( self::TEST_VALUE_2, $obj->$name, 'Property has not been assigned the second value' );
	}

	/**
	 * Verify that the state of undeclared properties can be checked and changed.
	 *
	 * Please take note that this test does not represent the _desired_ behaviour, but the behaviour
	 * we unfortunately need to maintain for BC-reason.
	 *
	 * Also note that as this test expects an error message, it cannot be combined with the
	 * test for the other magic methods.
	 *
	 * @ticket 56034
	 *
	 * @covers WP_Object_Cache::__unset
	 * @covers WP_Object_Cache::__isset
	 * @covers WP_Object_Cache::__get
	 * @covers WP_Object_Cache::__set
	 */
	public function test_magic_unset_undeclared_properties() {
		$obj  = new WP_Object_Cache();
		$name = 'does_not_exist';

		// Set the property value and verify the state.
		$obj->$name = self::TEST_VALUE_1;
		$this->assertTrue( isset( $obj->$name ), 'Setting the property failed' );

		// Unset the property value and verify the new state.
		unset( $obj->$name );
		$this->assertFalse( isset( $obj->$name ), 'Unsetting the property failed' );

		// Make sure that useful PHP native error messages aren't being hidden away by the magic methods.
		$expected_msg = 'Undefined property: WP_Object_Cache::$';
		if ( PHP_VERSION_ID > 80000 ) {
			$this->expectWarning();
			$this->expectWarningMessage( $expected_msg );
		} else {
			$this->expectNotice();
			$this->expectNoticeMessage( $expected_msg );
		}

		$unused = $obj->$name;
	}
}
