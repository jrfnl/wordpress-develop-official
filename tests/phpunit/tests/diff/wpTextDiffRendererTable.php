<?php

/**
 * @group diff
 */
final class Tests_Diff_wpTextDiffRendererTable extends WP_UnitTestCase {

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
	 * Load the class under test.
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();

		require_once ABSPATH . 'wp-includes/Text/Diff/Renderer.php';
		require_once ABSPATH . 'wp-includes/class-wp-text-diff-renderer-table.php';
	}

	/**
	 * Verify that the state of accessible declared properties can be checked and changed.
	 *
	 * @ticket 56034
	 *
	 * @covers WP_Text_Diff_Renderer_Table::__isset
	 * @covers WP_Text_Diff_Renderer_Table::__get
	 * @covers WP_Text_Diff_Renderer_Table::__set
	 *
	 * @dataProvider data_magic_methods_accessible_properties
	 *
	 * @param string $name    Property name.
	 * @param mixed  $default Default value for the property.
	 */
	public function test_magic_methods_for_accessible_properties( $name, $default = null ) {
		$obj = new WP_Text_Diff_Renderer_Table();

		// Verify initial state.
		$expectation_isset = false;
		if ( null !== $default || 'screen' === $name ) {
			$expectation_isset = true;
		}
		$this->assertSame( $expectation_isset, isset( $obj->$name ), 'Unexpected initial state' );

		if ( 'screen' === $name ) {
			$this->assertInstanceOf( 'WP_Screen', $obj->$name, 'Initial value does not match expectations' );
		} else {
			$this->assertSame( $default, $obj->$name, 'Initial value does not match expectations' );
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
	 * Note that as this test expects an error message, it cannot be combined with the
	 * test for the other magic methods.
	 *
	 * @ticket 56034
	 *
	 * @covers WP_Text_Diff_Renderer_Table::__isset
	 * @covers WP_Text_Diff_Renderer_Table::__unset
	 * @covers WP_Text_Diff_Renderer_Table::__get
	 * @covers WP_Text_Diff_Renderer_Table::__set
	 *
	 * @dataProvider data_magic_methods_accessible_properties
	 *
	 * @param string $name    Property name.
	 * @param mixed  $default Default value for the property.
	 */
	public function test_magic_unset_for_accessible_properties( $name ) {
		$obj = new WP_Text_Diff_Renderer_Table();

		// Make sure the properties all have an initial value.
		if ( isset( $obj->$name ) === false ) {
			$obj->$name = self::TEST_VALUE_1;
			$this->assertTrue( isset( $obj->$name ), 'Setting an initial value failed' );
		}

		// Unset the property value and verify the new state.
		unset( $obj->$name );
		$this->assertFalse( isset( $obj->$name ), 'Unsetting the property failed' );

		$expected_msg = 'Undefined property: WP_Text_Diff_Renderer_Table::$';
		if ( PHP_VERSION_ID > 80000 ) {
			$this->expectWarning();
			$this->expectWarningMessage( $expected_msg );
		} else {
			$this->expectNotice();
			$this->expectNoticeMessage( $expected_msg );
		}

		$this->assertNull( $obj->$name, 'Property has not really been unset' );
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public function data_magic_methods_accessible_properties() {
		return array(
			'Compat property: protected $_diff_threshold - should be accessible' => array(
				'name'    => '_diff_threshold',
				'default' => 0.6,
			),
			'Compat property: protected $inline_diff_renderer - should be accessible' => array(
				'name'    => 'inline_diff_renderer',
				'default' => 'WP_Text_Diff_Renderer_inline',
			),
			'Compat property: protected $_show_split_view - should be accessible' => array(
				'name'    => '_show_split_view',
				'default' => true,
			),
		);
	}

	/**
	 * Verify that using `isset()` on inaccessible or undeclared properties always returns `false`.
	 *
	 * @ticket 56034
	 *
	 * @covers       WP_Text_Diff_Renderer_Table::__isset
	 * @dataProvider data_magic_methods_inaccessible_properties
	 * @dataProvider data_magic_methods_undeclared_properties
	 *
	 * @param string $name Property name.
	 */
	public function test_magic_isset_inaccessible_property( $name ) {
		$obj = new WP_Text_Diff_Renderer_Table();
		$this->assertFalse( isset( $obj->$name ) );
	}

	/**
	 * Verify that any attempt to access an inaccessible or undeclared property always yields `null`.
	 *
	 * @ticket 56034
	 *
	 * @covers       WP_Text_Diff_Renderer_Table::__get
	 * @dataProvider data_magic_methods_inaccessible_properties
	 * @dataProvider data_magic_methods_undeclared_properties
	 *
	 * @param string $name Property name.
	 */
	public function test_magic_get_inaccessible_property( $name ) {
		$obj = new WP_Text_Diff_Renderer_Table();
		$this->assertNull( $obj->$name );
	}

	/**
	 * Verify that attempting to write to an inaccessible property will fail.
	 *
	 * @ticket 56034
	 *
	 * @covers       WP_Text_Diff_Renderer_Table::__set
	 * @dataProvider data_magic_methods_inaccessible_properties
	 *
	 * @param string $name Property name.
	 */
	public function test_magic_set_inaccessible_property( $name ) {
		$obj        = new WP_Text_Diff_Renderer_Table();
		$obj->$name = self::TEST_VALUE_1;

		// Verify that the property value was not changed.
		$refl_prop = new ReflectionProperty( $obj, $name );
		$refl_prop->setAccessible( true );
		if ( method_exists( $refl_prop, 'getDefaultValue' ) ) {
			// PHP 8.0+.
			$default_value = $refl_prop->getDefaultValue();
		} else {
			// PHP < 8.0.
			$all_properties = $refl_prop->getDeclaringClass()->getDefaultProperties();
			$default_value  = null;
			if ( isset( $all_properties[ $name ] ) ) {
				$default_value = $all_properties[ $name ];
			}
		}
		$current_value = $refl_prop->getValue( $obj );
		$refl_prop->setAccessible( false );

		$this->assertSame( $default_value, $current_value );
		$this->assertNotSame( self::TEST_VALUE_1, $current_value );
	}

	/**
	 * Verify that attempting to unset an inaccessible property will fail.
	 *
	 * @ticket 56034
	 *
	 * @covers       WP_Text_Diff_Renderer_Table::__unset
	 * @dataProvider data_magic_methods_inaccessible_properties
	 *
	 * @param string $name Property name.
	 */
	public function test_magic_unset_inaccessible_property( $name ) {
		$obj = new WP_Text_Diff_Renderer_Table();
		unset( $obj->$name );

		// Verify that the property value was not changed.
		$refl_prop = new ReflectionProperty( $obj, $name );
		$refl_prop->setAccessible( true );
		if ( method_exists( $refl_prop, 'getDefaultValue' ) ) {
			// PHP 8.0+.
			$default_value = $refl_prop->getDefaultValue();
		} else {
			// PHP < 8.0.
			$all_properties = $refl_prop->getDeclaringClass()->getDefaultProperties();
			$default_value  = null;
			if ( isset( $all_properties[ $name ] ) ) {
				$default_value = $all_properties[ $name ];
			}
		}
		$current_value = $refl_prop->getValue( $obj );
		$refl_prop->setAccessible( false );

		$this->assertSame( $default_value, $current_value );
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public function data_magic_methods_inaccessible_properties() {
		return array(
			'Protected property: $compat_fields (has default value)' => array(
				'name' => 'compat_fields',
			),
			'Protected property: $count_cache (has default value)' => array(
				'name' => 'count_cache',
			),
			'Protected property: $difference_cache (has default value)' => array(
				'name' => 'difference_cache',
			),
		);
	}

	/**
	 * Verify that attempting to dynamically set an undeclared property will fail.
	 *
	 * @ticket 56034
	 *
	 * @covers       WP_Text_Diff_Renderer_Table::__set
	 * @dataProvider data_magic_methods_undeclared_properties
	 *
	 * @param string $name Property name.
	 */
	public function test_magic_set_undeclared_property( $name ) {
		$obj        = new WP_Text_Diff_Renderer_Table();
		$obj->$name = self::TEST_VALUE_1;

		$this->expectException( ReflectionException::class );
		$this->expectExceptionMessage( 'Property WP_Text_Diff_Renderer_Table::$' . $name . ' does not exist' );

		// Verify that the property still doesn't exist.
		$refl_prop = new ReflectionProperty( $obj, $name );
	}

	/**
	 * Verify that attempting to unset an undeclared property will fail.
	 *
	 * @ticket 56034
	 *
	 * @covers       WP_Text_Diff_Renderer_Table::__unset
	 * @dataProvider data_magic_methods_undeclared_properties
	 *
	 * @param string $name Property name.
	 */
	public function test_magic_unset_undeclared_property( $name ) {
		$obj = new WP_Text_Diff_Renderer_Table();
		unset( $obj->$name );

		$this->expectException( ReflectionException::class );
		$this->expectExceptionMessage( 'Property WP_Text_Diff_Renderer_Table::$' . $name . ' does not exist' );

		// Verifies that the property doesn't exist instead of: exists, but has a null value or is uninitialized.
		$refl_prop = new ReflectionProperty( $obj, $name );
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public function data_magic_methods_undeclared_properties() {
		return array(
			'Undeclared property: $does_not_exist' => array(
				'name' => 'does_not_exist',
			),
		);
	}
}
