<?php

/**
 * Test WPDB methods
 *
 * @group wpdb
 *
 * @covers wpdb::__isset
 * @covers wpdb::__unset
 * @covers wpdb::__get
 * @covers wpdb::__set
 */
final class Tests_DB_MagicMethods extends WP_UnitTestCase {

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
	 * Helper function to test the wpdb class without making a connection to the database.
	 *
	 * @return MockBuilder|wpdb
	 */
	private function get_clean_wpdb() {
		$mock_obj = $this->getMockBuilder( wpdb::class )
			->disableOriginalConstructor()
			->setMethods( array( 'load_col_info' ) )
			->getMock();

		$mock_obj->expects( $this->any() )
			->method( 'load_col_info' );

		return $mock_obj;
	}

	/**
	 * Verify that public properties are not affected by the changes made in the magic methods.
	 *
	 * @ticket 56034
	 *
	 * @dataProvider data_public_properties_are_not_affected_by_changes_in_magic_methods
	 *
	 * @param string $name    Property name.
	 * @param mixed  $default Default value for the property.
	 */
	public function test_public_properties_are_not_affected_by_changes_in_magic_methods( $name, $default = null ) {
		$obj = $this->get_clean_wpdb();

		// Verify initial state.
		if ( null !== $default ) {
			$this->assertTrue( isset( $obj->$name ), 'Unexpected initial state' );
			$this->assertSame( $default, $obj->$name, 'Initial value does not match expectations' );
		} else {
			$this->assertFalse( isset( $obj->$name ), 'Unexpected initial state' );
			$this->assertNull( $obj->$name, 'Initial value does not match expectations' );
		}

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
			'Declared public property: $show_errors (has default value)'   => array(
				'name'    => 'show_errors',
				'default' => false,
			),
			'Declared public property: $charset (no default value)' => array(
				'name' => 'charset',
			),
			'Declared public property: $is_mysql (has default value: null)' => array(
				'name' => 'is_mysql',
			),
		);
	}

	/**
	 * @ticket 18510
	 */
	public function test_wpdb_supposedly_protected_properties() {
		global $wpdb;

		$this->assertNotEmpty( $wpdb->dbh );
		$dbh = $wpdb->dbh;
		$this->assertNotEmpty( $dbh );
		$this->assertTrue( isset( $wpdb->dbh ) ); // Test __isset().
		unset( $wpdb->dbh );
		$this->assertTrue( empty( $wpdb->dbh ) );
		$wpdb->dbh = $dbh;
		$this->assertNotEmpty( $wpdb->dbh );
	}

	/**
	 * Verify that the state of accessible declared properties can be checked and changed.
	 *
	 * Please take note that this test does not represent the _desired_ behaviour, but the behaviour
	 * we unfortunately need to maintain for BC-reason. This is NOT how magic methods are supposed
	 * to be implemented!
	 *
	 * @ticket 56034
	 *
	 * @dataProvider data_magic_methods_declared_settable_properties
	 *
	 * @param string $name    Property name.
	 * @param mixed  $default Default value for the property.
	 */
	public function test_magic_methods_declared_properties( $name, $default = null ) {
		$obj = $this->get_clean_wpdb();

		// Verify initial state.
		if ( null !== $default ) {
			$this->assertTrue( isset( $obj->$name ), 'Unexpected initial state' );
			$this->assertSame( $default, $obj->$name, 'Initial value does not match expectations' );
		} else {
			$this->assertFalse( isset( $obj->$name ), 'Unexpected initial state' );
			$this->assertNull( $obj->$name, 'Initial value does not match expectations' );
		}

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
	 * we unfortunately need to maintain for BC-reason. This is NOT how magic methods are supposed
	 * to be implemented!
	 *
	 * Also note that as this test expects an error message, it cannot be combined with the
	 * test for the other magic methods.
	 *
	 * @ticket 56034
	 *
	 * @dataProvider data_magic_methods_declared_settable_properties
	 *
	 * @param string $name       Property name.
	 * @param mixed  $default    Unused. Default value for the property.
	 * @param string $visibility The visibility of the property.
	 */
	public function test_magic_unset_declared_properties( $name, $default = null, $visibility = 'protected' ) {
		$obj = $this->get_clean_wpdb();

		// Make sure the properties all have an initial value.
		if ( isset( $obj->$name ) === false ) {
			$obj->$name = self::TEST_VALUE_1;
			$this->assertTrue( isset( $obj->$name ), 'Setting an initial value failed' );
		}

		// Unset the property value and verify the new state.
		unset( $obj->$name );
		$this->assertFalse( isset( $obj->$name ), 'Unsetting the property failed' );

		// Make sure that useful PHP native error messages aren't being hidden away by the magic methods.
		$expected_msg = 'Undefined property: ';
		if ( PHP_VERSION_ID > 80000 || 'private' === $visibility ) {
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
	public function data_magic_methods_declared_settable_properties() {
		return array(
			'Declared protected property: $col_info'    => array(
				'name' => 'col_info',
			),
			'Declared protected property: $dbh'         => array(
				'name' => 'dbh',
			),
			'Declared protected property: $dbhost'      => array(
				'name' => 'dbhost',
			),
			'Declared protected property: $dbname'      => array(
				'name' => 'dbname',
			),
			'Declared protected property: $dbpassword'  => array(
				'name' => 'dbpassword',
			),
			'Declared protected property: $dbuser'      => array(
				'name' => 'dbuser',
			),
			'Declared protected property: $reconnect_retries' => array(
				'name'    => 'reconnect_retries',
				'default' => 5,
			),
			'Declared protected property: $result'      => array(
				'name' => 'result',
			),
			'Declared private property: $checking_collation' => array(
				'name'       => 'checking_collation',
				'default'    => false,
				'visibility' => 'private',
			),
			'Declared private property: $has_connected' => array(
				'name'       => 'has_connected',
				'default'    => false,
				'visibility' => 'private',
			),
			'Declared private property: $use_mysqli'    => array(
				'name'       => 'use_mysqli',
				'default'    => false,
				'visibility' => 'private',
			),
		);
	}

	/**
	 * @ticket 21212
	 */
	public function test_wpdb_actually_protected_properties() {
		global $wpdb;

		$new_meta = "HAHA I HOPE THIS DOESN'T WORK";

		$col_meta       = $wpdb->col_meta;
		$wpdb->col_meta = $new_meta;

		$this->assertNotEquals( $col_meta, $new_meta );
		$this->assertSame( $col_meta, $wpdb->col_meta );
	}

	/**
	 * Verify that the state of select declared properties can be checked, but not changed.
	 *
	 * @ticket 56034
	 *
	 * @dataProvider data_magic_methods_declared_non_settable_properties
	 *
	 * @param string $name    Property name.
	 * @param mixed  $default Default value for the property.
	 */
	public function test_magic_methods_declared_properties_non_settable( $name, $default ) {
		$obj = $this->get_clean_wpdb();

		// Verify initial state. Note: each of these properties has a default value.
		$this->assertTrue( isset( $obj->$name ), 'Unexpected initial state' );
		$this->assertSame( $default, $obj->$name, 'Initial value does not match expectations' );

		// Attempt to overwrite the property value and verify that this fails.
		$obj->$name = self::TEST_VALUE_1;
		$this->assertSame( $default, $obj->$name, 'Property has been assigned a new value' );
	}

	/**
	 * Verify that select declared properties cannot be unset.
	 *
	 * Also note that as this test expects an error message, it cannot be combined with the
	 * test for the other magic methods.
	 *
	 * @ticket 56034
	 *
	 * @dataProvider data_magic_methods_declared_non_settable_properties
	 *
	 * @param string $name    Property name.
	 * @param mixed  $default Default value for the property.
	 */
	public function test_magic_unset_declared_properties_non_settable( $name, $default ) {
		$obj = $this->get_clean_wpdb();

		// Verify initial state. Note: each of these properties has a default value.
		$this->assertTrue( isset( $obj->$name ), 'Unexpected initial state' );

		// Unset the property value and verify the new state.
		unset( $obj->$name );
		$this->assertTrue( isset( $obj->$name ), 'Unsetting the property succeeded' );
		$this->assertSame( $default, $obj->$name, 'Property has been unset' );
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public function data_magic_methods_declared_non_settable_properties() {
		return array(
			'Declared protected property: $col_meta'      => array(
				'name'    => 'col_meta',
				'default' => array(),
			),
			'Declared protected property: $table_charset' => array(
				'name'    => 'table_charset',
				'default' => array(),
			),
			'Declared protected property: $check_current_query' => array(
				'name'    => 'check_current_query',
				'default' => true,
			),
		);
	}

	/**
	 * @ticket 18510
	 */
	public function test_wpdb_nonexistent_properties() {
		global $wpdb;

		$this->assertTrue( empty( $wpdb->nonexistent_property ) );
		$wpdb->nonexistent_property = true;
		$this->assertTrue( $wpdb->nonexistent_property );
		$this->assertTrue( isset( $wpdb->nonexistent_property ) );
		unset( $wpdb->nonexistent_property );
		$this->assertTrue( empty( $wpdb->nonexistent_property ) );
	}

	/**
	 * Verify that the state of undeclared properties can be checked and changed.
	 *
	 * Please take note that this test does not represent the _desired_ behaviour, but the behaviour
	 * we unfortunately need to maintain for BC-reason. This is NOT how magic methods are supposed
	 * to be implemented!
	 *
	 * @ticket 56034
	 */
	public function test_magic_methods_undeclared_properties() {
		$obj  = $this->get_clean_wpdb();
		$name = 'does_not_exist';

		// Verify initial state.
		$this->assertFalse( isset( $obj->$name ), 'Undeclared property shouldn\'t exist' );

		// Set the property value and verify the state.
		$obj->$name = self::TEST_VALUE_1;
		$this->assertTrue( isset( $obj->$name ), 'Setting the property failed [1]' );
		$this->assertSame( self::TEST_VALUE_1, $obj->$name, 'Property has not been assigned the first value' );

		// Update the property value and verify the updated state.
		$obj->$name = self::TEST_VALUE_2;
		$this->assertTrue( isset( $obj->$name ), 'Setting the property failed [2]' );
		$this->assertSame( self::TEST_VALUE_2, $obj->$name, 'Property has not been assigned the second value' );
	}

	/**
	 * Verify that undeclared properties can be unset.
	 *
	 * Please take note that this test does not represent the _desired_ behaviour, but the behaviour
	 * we unfortunately need to maintain for BC-reason. This is NOT how magic methods are supposed
	 * to be implemented!
	 *
	 * Also note that as this test expects an error message, it cannot be combined with the
	 * test for the other magic methods.
	 *
	 * @ticket 56034
	 */
	public function test_magic_unset_undeclared_properties() {
		$obj  = $this->get_clean_wpdb();
		$name = 'does_not_exist';

		// Verify initial state.
		$this->assertFalse( isset( $obj->$name ), 'Undeclared property shouldn\'t exist' );

		// Set the property value and verify the state.
		$obj->$name = self::TEST_VALUE_1;
		$this->assertTrue( isset( $obj->$name ), 'Setting the property failed' );

		// Unset the property value and verify the new state.
		unset( $obj->$name );
		$this->assertFalse( isset( $obj->$name ), 'Unsetting the property failed' );

		// Make sure that useful PHP native error messages aren't being hidden away by the magic methods.
		$this->expectWarning();
		$this->expectWarningMessage( 'Undefined property: ' );

		$unused = $obj->$name;
	}
}
