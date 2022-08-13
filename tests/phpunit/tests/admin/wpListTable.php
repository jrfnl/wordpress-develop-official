<?php

/**
 * @group admin
 */
class Tests_Admin_WpListTable extends WP_UnitTestCase {

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
	 * Dummy screen name.
	 *
	 * @var string
	 */
	const HOOK_SUFFIX = 'my-hook';

	/**
	 * List table.
	 *
	 * @var WP_List_Table $list_table
	 */
	protected static $list_table;

	public static function set_up_before_class() {
		global $hook_suffix;

		parent::set_up_before_class();

		require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

		$hook_suffix      = '_wp_tests';
		self::$list_table = new WP_List_Table();
	}

	/**
	 * Set prerequisite.
	 */
	public function set_up() {
		parent::set_up();

		/*
		 * Set a dummy value for the current screen in the admin to prevent
		 * `_get_list_table()` throwing.
		 */
		$GLOBALS['hook_suffix'] = self::HOOK_SUFFIX;
	}

	/**
	 * Reset to default state.
	 */
	public function tear_down() {
		unset( $GLOBALS['hook_suffix'] );
		parent::tear_down();
	}

	/**
	 * Tests that `WP_List_Table::get_column_info()` only adds the primary
	 * column header when necessary.
	 *
	 * @ticket 34564
	 *
	 * @dataProvider data_should_only_add_primary_column_when_needed
	 *
	 * @covers WP_List_Table::get_column_info
	 *
	 * @param string $list_class          The name of the WP_List_Table child class.
	 * @param array  $headers             A list of column headers.
	 * @param array  $expected            The expected column headers.
	 * @param int    $expected_hook_count The expected number of times the hook is called.
	 */
	public function test_should_only_add_primary_column_when_needed( $list_class, $headers, $expected, $expected_hook_count ) {
		$hook = new MockAction();
		add_filter( 'list_table_primary_column', array( $hook, 'filter' ) );

		$list_table = _get_list_table( $list_class );

		$column_headers = new ReflectionProperty( $list_table, '_column_headers' );
		$column_headers->setAccessible( true );
		$column_headers->setValue( $list_table, $headers );

		$column_info = new ReflectionMethod( $list_table, 'get_column_info' );
		$column_info->setAccessible( true );

		$this->assertSame( $expected, $column_info->invoke( $list_table ), 'The actual columns did not match the expected columns' );
		$this->assertSame( $expected_hook_count, $hook->get_call_count(), 'The hook was not called the expected number of times' );
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public function data_should_only_add_primary_column_when_needed() {
		/*
		 * `WP_Post_Comments_List_Table` overrides `get_column_info()` rather than
		 * use the default `WP_List_Table::get_column_info()`. Therefore it is
		 * untested.
		 */
		$list_primary_columns = array(
			'WP_Application_Passwords_List_Table'         => 'name',
			'WP_Comments_List_Table'                      => 'author',
			'WP_Links_List_Table'                         => 'name',
			'WP_Media_List_Table'                         => 'title',
			'WP_MS_Sites_List_Table'                      => 'blogname',
			'WP_MS_Themes_List_Table'                     => 'name',
			'WP_MS_Users_List_Table'                      => 'username',
			'WP_Plugin_Install_List_Table'                => '',
			'WP_Plugins_List_Table'                       => 'name',
			'WP_Posts_List_Table'                         => 'title',
			'WP_Privacy_Data_Export_Requests_List_Table'  => 'email',
			'WP_Privacy_Data_Removal_Requests_List_Table' => 'email',
			'WP_Terms_List_Table'                         => 'name',
			'WP_Theme_Install_List_Table'                 => '',
			'WP_Themes_List_Table'                        => '',
			'WP_Users_List_Table'                         => 'username',
		);

		$datasets = array();

		foreach ( $list_primary_columns as $list_class => $primary_column ) {
			$datasets[ $list_class . ' - three columns' ] = array(
				'list_class'          => $list_class,
				'headers'             => array( 'First', 'Second', 'Third' ),
				'expected'            => array( 'First', 'Second', 'Third', $primary_column ),
				'expected_hook_count' => 1,
			);

			$datasets[ $list_class . ' - four columns' ] = array(
				'list_class'          => $list_class,
				'headers'             => array( 'First', 'Second', 'Third', 'Fourth' ),
				'expected'            => array( 'First', 'Second', 'Third', 'Fourth' ),
				'expected_hook_count' => 0,
			);
		}

		/*
		 * `WP_MS_Themes_List_Table` and `WP_Plugins_List_Table` override the
		 * `get_primary_column_name()` method rather than use the default
		 * `WP_List_Table::get_primary_column_name()`. Neither include the
		 * `list_table_primary_column` hook.
		 */
		$datasets['WP_MS_Themes_List_Table - three columns']['expected_hook_count'] = 0;
		$datasets['WP_Plugins_List_Table - three columns']['expected_hook_count']   = 0;

		return $datasets;
	}

	/**
	 * Verify that the state of accessible declared properties can be checked and changed.
	 *
	 * @ticket 56034
	 *
	 * @covers WP_List_Table::__isset
	 * @covers WP_List_Table::__get
	 * @covers WP_List_Table::__set
	 *
	 * @dataProvider data_magic_methods_accessible_properties
	 *
	 * @param string $name    Property name.
	 * @param mixed  $default Default value for the property.
	 */
	public function test_magic_methods_for_accessible_properties( $name, $default = null ) {
		$obj = new WP_List_Table();

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
	 * @covers WP_List_Table::__isset
	 * @covers WP_List_Table::__unset
	 * @covers WP_List_Table::__get
	 * @covers WP_List_Table::__set
	 *
	 * @dataProvider data_magic_methods_accessible_properties
	 *
	 * @param string $name    Property name.
	 * @param mixed  $default Default value for the property.
	 */
	public function test_magic_unset_for_accessible_properties( $name ) {
		$obj = new WP_List_Table();

		// Make sure the properties all have an initial value.
		if ( isset( $obj->$name ) === false ) {
			$obj->$name = self::TEST_VALUE_1;
			$this->assertTrue( isset( $obj->$name ), 'Setting an initial value failed' );
		}

		// Unset the property value and verify the new state.
		unset( $obj->$name );
		$this->assertFalse( isset( $obj->$name ), 'Unsetting the property failed' );

		$expected_msg = 'Undefined property: WP_List_Table::$';
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
			'Compat property: protected $_args - should be accessible' => array(
				'name'    => '_args',
				'default' => array(
					'plural'   => self::HOOK_SUFFIX,
					'singular' => '',
					'ajax'     => false,
					'screen'   => null,
				),
			),
			'Compat property: protected $_pagination_args - should be accessible' => array(
				'name'    => '_pagination_args',
				'default' => array(),
			),
			'Compat property: protected $screen - should be accessible' => array(
				'name' => 'screen',
			),
			'Compat property: private $_actions - should be accessible' => array(
				'name' => '_actions',
			),
			'Compat property: private $_pagination - should be accessible' => array(
				'name' => '_pagination',
			),
		);
	}

	/**
	 * Verify that using `isset()` on inaccessible or undeclared properties always returns `false`.
	 *
	 * @ticket 56034
	 *
	 * @covers       WP_List_Table::__isset
	 * @dataProvider data_magic_methods_inaccessible_properties
	 * @dataProvider data_magic_methods_undeclared_properties
	 *
	 * @param string $name Property name.
	 */
	public function test_magic_isset_inaccessible_property( $name ) {
		$obj = new WP_List_Table();
		$this->assertFalse( isset( $obj->$name ) );
	}

	/**
	 * Verify that any attempt to access an inaccessible or undeclared property always yields `null`.
	 *
	 * @ticket 56034
	 *
	 * @covers       WP_List_Table::__get
	 * @dataProvider data_magic_methods_inaccessible_properties
	 * @dataProvider data_magic_methods_undeclared_properties
	 *
	 * @param string $name Property name.
	 */
	public function test_magic_get_inaccessible_property( $name ) {
		$obj = new WP_List_Table();
		$this->assertNull( $obj->$name );
	}

	/**
	 * Verify that attempting to write to an inaccessible property will fail.
	 *
	 * @ticket 56034
	 *
	 * @covers       WP_List_Table::__set
	 * @dataProvider data_magic_methods_inaccessible_properties
	 *
	 * @param string $name Property name.
	 */
	public function test_magic_set_inaccessible_property( $name ) {
		$obj        = new WP_List_Table();
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
	 * @covers       WP_List_Table::__unset
	 * @dataProvider data_magic_methods_inaccessible_properties
	 *
	 * @param string $name Property name.
	 */
	public function test_magic_unset_inaccessible_property( $name ) {
		$obj = new WP_List_Table();
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
			'Protected property: $_column_headers (no default value)' => array(
				'name' => '_column_headers',
			),
		);
	}

	/**
	 * Verify that attempting to dynamically set an undeclared property will fail.
	 *
	 * @ticket 56034
	 *
	 * @covers       WP_List_Table::__set
	 * @dataProvider data_magic_methods_undeclared_properties
	 *
	 * @param string $name Property name.
	 */
	public function test_magic_set_undeclared_property( $name ) {
		$obj        = new WP_List_Table();
		$obj->$name = self::TEST_VALUE_1;

		$this->expectException( ReflectionException::class );
		$this->expectExceptionMessage( 'Property WP_List_Table::$' . $name . ' does not exist' );

		// Verify that the property still doesn't exist.
		$refl_prop = new ReflectionProperty( $obj, $name );
	}

	/**
	 * Verify that attempting to unset an undeclared property will fail.
	 *
	 * @ticket 56034
	 *
	 * @covers       WP_List_Table::__unset
	 * @dataProvider data_magic_methods_undeclared_properties
	 *
	 * @param string $name Property name.
	 */
	public function test_magic_unset_undeclared_property( $name ) {
		$obj = new WP_List_Table();
		unset( $obj->$name );

		$this->expectException( ReflectionException::class );
		$this->expectExceptionMessage( 'Property WP_List_Table::$' . $name . ' does not exist' );

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

	/**
	 * Tests the "get_views_links()" method.
	 *
	 * @ticket 42066
	 *
	 * @covers WP_List_Table::get_views_links
	 *
	 * @dataProvider data_get_views_links
	 *
	 * @param array $link_data {
	 *     An array of link data.
	 *
	 *     @type string $url     The link URL.
	 *     @type string $label   The link label.
	 *     @type bool   $current Optional. Whether this is the currently selected view.
	 * }
	 * @param array $expected
	 */
	public function test_get_views_links( $link_data, $expected ) {
		$get_views_links = new ReflectionMethod( self::$list_table, 'get_views_links' );
		$get_views_links->setAccessible( true );

		$actual = $get_views_links->invokeArgs( self::$list_table, array( $link_data ) );

		$this->assertSameSetsWithIndex( $expected, $actual );
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public function data_get_views_links() {
		return array(
			'one "current" link'                           => array(
				'link_data' => array(
					'all'       => array(
						'url'     => 'https://example.org/',
						'label'   => 'All',
						'current' => true,
					),
					'activated' => array(
						'url'     => add_query_arg( 'status', 'activated', 'https://example.org/' ),
						'label'   => 'Activated',
						'current' => false,
					),
				),
				'expected'  => array(
					'all'       => '<a href="https://example.org/" class="current" aria-current="page">All</a>',
					'activated' => '<a href="https://example.org/?status=activated">Activated</a>',
				),
			),
			'two "current" links'                          => array(
				'link_data' => array(
					'all'       => array(
						'url'     => 'https://example.org/',
						'label'   => 'All',
						'current' => true,
					),
					'activated' => array(
						'url'     => add_query_arg( 'status', 'activated', 'https://example.org/' ),
						'label'   => 'Activated',
						'current' => true,
					),
				),
				'expected'  => array(
					'all'       => '<a href="https://example.org/" class="current" aria-current="page">All</a>',
					'activated' => '<a href="https://example.org/?status=activated" class="current" aria-current="page">Activated</a>',
				),
			),
			'one "current" link and one without "current" key' => array(
				'link_data' => array(
					'all'       => array(
						'url'     => 'https://example.org/',
						'label'   => 'All',
						'current' => true,
					),
					'activated' => array(
						'url'   => add_query_arg( 'status', 'activated', 'https://example.org/' ),
						'label' => 'Activated',
					),
				),
				'expected'  => array(
					'all'       => '<a href="https://example.org/" class="current" aria-current="page">All</a>',
					'activated' => '<a href="https://example.org/?status=activated">Activated</a>',
				),
			),
			'one "current" link with escapable characters' => array(
				'link_data' => array(
					'all'       => array(
						'url'     => 'https://example.org/',
						'label'   => 'All',
						'current' => true,
					),
					'activated' => array(
						'url'     => add_query_arg(
							array(
								'status' => 'activated',
								'sort'   => 'desc',
							),
							'https://example.org/'
						),
						'label'   => 'Activated',
						'current' => false,
					),
				),
				'expected'  => array(
					'all'       => '<a href="https://example.org/" class="current" aria-current="page">All</a>',
					'activated' => '<a href="https://example.org/?status=activated&#038;sort=desc">Activated</a>',
				),
			),
		);
	}

	/**
	 * Tests that "get_views_links()" throws a _doing_it_wrong().
	 *
	 * @ticket 42066
	 *
	 * @covers WP_List_Table::get_views_links
	 *
	 * @expectedIncorrectUsage WP_List_Table::get_views_links
	 *
	 * @dataProvider data_get_views_links_doing_it_wrong
	 *
	 * @param array $link_data {
	 *     An array of link data.
	 *
	 *     @type string $url     The link URL.
	 *     @type string $label   The link label.
	 *     @type bool   $current Optional. Whether this is the currently selected view.
	 * }
	 */
	public function test_get_views_links_doing_it_wrong( $link_data ) {
		$get_views_links = new ReflectionMethod( self::$list_table, 'get_views_links' );
		$get_views_links->setAccessible( true );
		$get_views_links->invokeArgs( self::$list_table, array( $link_data ) );
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public function data_get_views_links_doing_it_wrong() {
		return array(
			'non-array $link_data'               => array(
				'link_data' => 'https://example.org, All, class="current" aria-current="page"',
			),
			'a link with no URL'                 => array(
				'link_data' => array(
					'all' => array(
						'label'   => 'All',
						'current' => true,
					),
				),
			),
			'a link with an empty URL'           => array(
				'link_data' => array(
					'all' => array(
						'url'     => '',
						'label'   => 'All',
						'current' => true,
					),
				),
			),
			'a link with a URL of only spaces'   => array(
				'link_data' => array(
					'all' => array(
						'url'     => '  ',
						'label'   => 'All',
						'current' => true,
					),
				),
			),
			'a link with a non-string URL'       => array(
				'link_data' => array(
					'all' => array(
						'url'     => array(),
						'label'   => 'All',
						'current' => true,
					),
				),
			),
			'a link with no label'               => array(
				'link_data' => array(
					'all' => array(
						'url'     => 'https://example.org/',
						'current' => true,
					),
				),
			),
			'a link with an empty label'         => array(
				'link_data' => array(
					'all' => array(
						'url'     => 'https://example.org/',
						'label'   => '',
						'current' => true,
					),
				),
			),
			'a link with a label of only spaces' => array(
				'link_data' => array(
					'all' => array(
						'url'     => 'https://example.org/',
						'label'   => '  ',
						'current' => true,
					),
				),
			),
			'a link with a non-string label'     => array(
				'link_data' => array(
					'all' => array(
						'url'     => 'https://example.org/',
						'label'   => array(),
						'current' => true,
					),
				),
			),
		);
	}
}
