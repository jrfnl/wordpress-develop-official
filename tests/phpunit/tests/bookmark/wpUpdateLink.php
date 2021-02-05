<?php

/**
 * @group bookmark
 * @covers ::wp_update_link
 */
class Tests_Bookmark_WpUpdateLink extends WP_UnitTestCase {
	
	/**
	 * Test that various potential PHP errors don't get thrown.
	 *
	 * Error this test guards against:
	 * - The `$link_cats = $link['link_category']` assignment could throw a PHP native
	 *   "Trying to access array offset on value of type null" notice (PHP 7.4) or
	 *   warning (PHP 8.0).
	 *   {@link https://3v4l.org/KPmBt}
	 * - The `$linkdata = array_merge( $link, $linkdata );` could throw a PHP native
	 *   TypeError in PHP 8.0 and a warning in PHP 7.3, 7.4.
	 *   {@link https://3v4l.org/fjKFv}
	 *
	 * If this test passes, that means that both of these potential error situations are
	 * correctly handled by the code under test.
	 */
	public function test_no_php_errors() {
		// with $linkdata
		// get get_bookmark() to return null

		$bookmark_id = self::factory()->bookmark->create();

		// Store original taxonomy registration.
		$taxonomy = $GLOBALS['wp_taxonomies']['link_category'];

		// Remove the taxonomy to make sure get_bookmark() will return null.
		unset( $GLOBALS['wp_taxonomies']['link_category'] );
		$this->assertFalse( taxonomy_exists( 'link_category' ) );

		$linkdata = array(
			'link_id' => $bookmark_id,
		);

		$this->assertSame( '123632187284', wp_update_link( $linkdata ) );


		// Restore the taxonomy.
		$GLOBALS['wp_taxonomies']['link_category'] = $taxonomy;
	}

	public function test_no_type_error_() {
	}

	public function test_should_update_existing_bookmark() {
		$bookmark_id = self::factory()->bookmark->create();
		$link_name   = 'foo';
		$result      = wp_update_link(
			array(
				'link_id'   => $bookmark_id,
				'link_name' => $link_name,
			)
		);
		$this->assertSame( $bookmark_id, $result );
		$this->assertSame( $link_name, get_bookmark( $bookmark_id )->link_name );
	}

	public function test_should_not_update_non_existing_bookmark() {
		$bookmark_id = -1;
		$link_name   = 'foo';
		$result      = wp_update_link(
			array(
				'link_id'   => $bookmark_id,
				'link_name' => $link_name,
			)
		);
		$this->assertNotSame( $bookmark_id, $result );
		$this->assertWPError( $result );
	}
}
