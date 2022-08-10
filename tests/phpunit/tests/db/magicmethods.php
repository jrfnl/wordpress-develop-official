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
}
