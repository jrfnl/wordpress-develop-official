<?php

/**
 * @group formatting
 */
class Tests_Formatting_LikeEscape extends WP_UnitTestCase_Base {
	/**
	 * @ticket 10041
	 * @expectedDeprecated like_escape
	 */
	function test_like_escape() {

		$inputs   = array(
			'howdy%',              // Single percent.
			'howdy_',              // Single underscore.
			'howdy\\',             // Single slash.
			'howdy\\howdy%howdy_', // The works.
		);
		$expected = array(
			'howdy\\%',
			'howdy\\_',
			'howdy\\',
			'howdy\\howdy\\%howdy\\_',
		);

		foreach ( $inputs as $key => $input ) {
			$this->assertSame( $expected[ $key ], like_escape( $input ) );
		}
	}
}
