<?php

if ( class_exists( 'PHPUnit\Runner\Version' ) && version_compare( PHPUnit\Runner\Version::id(), '6.0', '>=' ) ) {

	class_alias( 'PHPUnit\Framework\TestSuite', 'PHPUnit_Framework_TestSuite' );
	class_alias( 'PHPUnit\Framework\TestListener', 'PHPUnit_Framework_TestListener' );
	class_alias( 'PHPUnit\Util\GlobalState', 'PHPUnit_Util_GlobalState' );
	class_alias( 'PHPUnit\Util\Getopt', 'PHPUnit_Util_Getopt' );

	class PHPUnit_Util_Test {

		// phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		public static function getTickets( $class_name, $method_name ) {
			$annotations = PHPUnit\Util\Test::parseTestMethodAnnotations( $class_name, $method_name );

			$tickets = array();

			if ( isset( $annotations['class']['ticket'] ) ) {
				$tickets = $annotations['class']['ticket'];
			}

			if ( isset( $annotations['method']['ticket'] ) ) {
				$tickets = array_merge( $tickets, $annotations['method']['ticket'] );
			}

			return array_unique( $tickets );
		}

	}

}
