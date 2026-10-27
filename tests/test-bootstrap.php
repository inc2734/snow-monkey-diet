<?php
/**
 * Plugin behavior tests with the Snow Monkey theme.
 *
 * @package snow-monkey-diet
 */

use Snow_Monkey\Plugin\Diet\Bootstrap;

class Bootstrap_Test extends WP_UnitTestCase {

	public function test_theme_and_plugin_are_loaded() {
		$theme = wp_get_theme();
		$this->assertFalse( $theme->errors() );
		$this->assertSame( 'snow-monkey', $theme->get_template() );
		$this->assertTrue( class_exists( 'Framework\\Helper' ) );

		$plugin = new Bootstrap();
		$plugin->_plugins_loaded();
		$this->assertSame( 10, has_action( 'admin_menu', array( $plugin, '_admin_menu' ) ) );
	}

	public function test_settings_are_normalized() {
		$plugin = new Bootstrap();
		$plugin->_admin_init();

		$value = sanitize_option(
			'snow-monkey-diet',
			array(
				'disable-page-top' => '1',
				'unknown-setting'  => '1',
			)
		);

		$this->assertSame( 1, $value['disable-page-top'] );
		$this->assertFalse( $value['disable-widget-areas'] );
		$this->assertArrayNotHasKey( 'unknown-setting', $value );
		$this->assertCount( 21, $value );
	}

	/**
	 * @dataProvider disabled_features
	 */
	public function test_features_can_be_disabled( $option, $hooks ) {
		$plugin = new Bootstrap();
		delete_option( 'snow-monkey-diet' );
		$plugin->_disable();
		foreach ( $hooks as $hook ) {
			$this->assertSame( 'enabled', apply_filters( $hook, 'enabled' ) );
		}

		update_option( 'snow-monkey-diet', array( $option => 1 ) );
		$plugin->_disable();
		foreach ( $hooks as $hook ) {
			$this->assertFalse( apply_filters( $hook, 'enabled' ) );
		}
	}

	public function disabled_features() {
		$features = array(
			'widget-areas'         => array( 'app/setup/widget-area' ),
			'custom-widgets'       => array( 'app/setup/widgets' ),
			'drop-nav'             => array( 'app/setup/drop-nav' ),
			'blog-card'            => array( 'app/setup/oembed' ),
			'customizer-styles'    => array( 'app/setup/customizer-styles' ),
			'hash-nav'             => array( 'app/setup/hash-nav' ),
			'support-forum-widget' => array( 'app/setup/support-forum-widget' ),
			'page-top'             => array( 'app/customizer/design/sections/base-design/controls/display-page-top', 'app/setup/page-top', 'template-parts/common/page-top' ),
			'share-buttons'        => array( 'app/customizer/sns/sections/share-buttons/section', 'app/setup/share-buttons', 'template-parts/content/share-buttons' ),
			'nav-menus'            => array( 'app/setup/nav-menus' ),
			'seo'                  => array( 'app/customizer/seo/panel', 'app/setup/seo' ),
			'like-me-box'          => array( 'app/customizer/sns/sections/like-me-box/section', 'app/like-me-box', 'template-parts/common/like-me-box' ),
			'profile-box'          => array( 'app/customizer/design/sections/post/controls/profile-box', 'template-parts/common/profile-box' ),
			'related-posts'        => array( 'app/customizer/design/sections/post/controls/related-posts-layout', 'app/customizer/design/sections/post/controls/related-posts', 'template-parts/content/related-posts' ),
			'prev-next-nav'        => array( 'template-parts/content/prev-next-nav' ),
			'infobar'              => array( 'app/customizer/infobar/section', 'template-parts/common/infobar' ),
			'smooth-scroll'        => array( 'app/setup/smooth-scroll' ),
			'advertisement'        => array( 'app/customizer/advertisement/section', 'app/setup/google-adsense', 'app/setup/google-infeed-ads', 'template-parts/common/google-adsense' ),
			'theme-color'          => array( 'app/setup/theme-color' ),
			'community'            => array( 'app/customizer/snow-monkey-community/section' ),
		);

		$cases = array();
		foreach ( $features as $feature => $paths ) {
			$cases[ $feature ] = array(
				'disable-' . $feature,
				array_map(
					function ( $path ) {
						return 'snow_monkey_pre_template_part_render_' . $path;
					},
					$paths
				),
			);
		}
		return $cases;
	}

	/**
	 * @dataProvider style_hooks
	 */
	public function test_decorative_styles_are_removed_without_removing_other_styles( $hook ) {
		// Isolate the plugin callback from the theme's unbuilt asset files.
		remove_all_actions( $hook );
		wp_register_style( 'snow-monkey-test-theme', false );
		wp_register_style( 'snow-monkey-theme-test', false );
		wp_register_style( 'snow-monkey-test', false, array( 'snow-monkey-test-theme', 'snow-monkey-theme-test', 'external-test' ) );
		wp_register_style( 'external-test', false );
		wp_enqueue_style( 'snow-monkey-test' );
		wp_enqueue_style( 'snow-monkey-test-theme' );
		wp_enqueue_style( 'snow-monkey-theme-test' );

		$plugin = new Bootstrap();
		update_option( 'snow-monkey-diet', array( 'disable-theme-styles' => 1 ) );
		$plugin->_disable();
		do_action( $hook );

		$this->assertFalse( wp_style_is( 'snow-monkey-test-theme', 'registered' ) );
		$this->assertFalse( wp_style_is( 'snow-monkey-theme-test', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'snow-monkey-test', 'registered' ) );
		$this->assertTrue( wp_style_is( 'external-test', 'registered' ) );
		$this->assertSame( array( 2 => 'external-test' ), wp_styles()->registered['snow-monkey-test']->deps );
	}

	public function style_hooks() {
		return array(
			'front end'     => array( 'wp_enqueue_scripts' ),
			'block assets'  => array( 'enqueue_block_assets' ),
			'block editor'  => array( 'enqueue_block_editor_assets' ),
		);
	}
}
