<?php
/**
 * Contains test class for espresso_addon_skeleton.php
 *
 * @since  		0.0.1.dev.002
 * @package 		EE4 Addon Skeleton
 * @subpackage 	tests
 */


/**
 * Test class for espresso_addon_skeleton.php
 *
 * @since 		0.0.1.dev.002
 * @package 		EE4 Addon Skeleton
 * @subpackage 	tests
 */
class espresso_promotions_tests extends EE_UnitTestCase {

	/**
	 * Tests the loading of the main file
	 *
	 * @since 0.0.1.dev.002
	 */
	function test_loading_custom_csv_importer() {
		$this->assertEquals( has_action('AHEE__EE_System__load_espresso_addons', 'load_espresso_custom_csv_importer'), 10 );
		$this->assertTrue( class_exists( 'EE_Custom_Csv_Importer' ) );
	}
}
