<?php

/**
 * Basic abstract test class.
 *
 * This test class has become redundant, but is left in place so as not to break
 * BC for plugins/themes which extend the WP test suite for their integration tests.
 *
 * WP native test classes should extend directly from the `WP_UnitTestCase_Base` class.
 *
 * @deprecated 5.9.0
 */
abstract class WP_UnitTestCase extends WP_UnitTestCase_Base {}
