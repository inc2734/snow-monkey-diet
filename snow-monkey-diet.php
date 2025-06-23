<?php
/**
 * Plugin name: Snow Monkey Diet
 * Description: You can stop unused functions of the Snow Monkey.
 * Version: 0.8.4
 * Tested up to: 6.7
 * Requires at least: 5.5
 * Requires PHP: 7.4
 * Author: inc2734
 * Author URI: https://2inc.org
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: snow-monkey-diet
 *
 * @package snow-monkey-diet
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\Diet;

use Inc2734\WP_GitHub_Plugin_Updater\Bootstrap as Updater;

define( 'SNOW_MONKEY_DIET_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'SNOW_MONKEY_DIET_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

class Bootstrap {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, '_plugins_loaded' ) );
	}

	/**
	 * Plugins loaded.
	 */
	public function _plugins_loaded() {
		add_action( 'init', array( $this, '_load_textdomain' ) );

		new App\Updater();

		$theme = wp_get_theme( get_template() );
		if ( 'snow-monkey' !== $theme->template && 'snow-monkey/resources' !== $theme->template ) {
			add_action( 'admin_notices', array( $this, '_admin_notice_no_snow_monkey' ) );
			return;
		}

		if ( ! version_compare( $theme->get( 'Version' ), '11.1.0', '>=' ) ) {
			add_action( 'admin_notices', array( $this, '_admin_notice_invalid_snow_monkey_version' ) );
			return;
		}

		add_action( 'admin_menu', array( $this, '_admin_menu' ) );
		add_action( 'admin_init', array( $this, '_admin_init' ) );

		$this->_disable();
	}

	/**
	 * Admin notice for no Snow Monkey
	 *
	 * @return void
	 */
	public function _admin_notice_no_snow_monkey() {
		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<?php esc_html_e( '[Snow Monkey Diet] Needs the Snow Monkey.', 'snow-monkey-diet' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Admin notice for invalid Snow Monkey version
	 *
	 * @return void
	 */
	public function _admin_notice_invalid_snow_monkey_version() {
		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<?php
				printf(
					// translators: %1$s: version.
					esc_html__( '[Snow Monkey Diet] Needs the Snow Monkey %1$s or more.', 'snow-monkey-diet' ),
					'v11.1.0'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Add admin menu.
	 */
	public function _admin_menu() {
		add_options_page(
			__( 'Snow Monkey Diet', 'snow-monkey-diet' ),
			__( 'Snow Monkey Diet', 'snow-monkey-diet' ),
			'manage_options',
			'snow-monkey-diet',
			function () {
				?>
				<div class="wrap">
					<h1><?php esc_html_e( 'Snow Monkey Diet', 'snow-monkey-diet' ); ?></h1>
					<p>
						<?php esc_html_e( 'Suspended setting items may need to be re-setting when re-enabled.', 'snow-monkey-diet' ); ?>
					</p>
					<form method="post" action="options.php">
						<?php
							settings_fields( 'snow-monkey-diet' );
							do_settings_sections( 'snow-monkey-diet' );
							submit_button();
						?>
					</form>
				</div>
				<?php
			}
		);
	}

	/**
	 * Register setting.
	 */
	public function _admin_init() {
		register_setting(
			'snow-monkey-diet',
			'snow-monkey-diet',
			function ( $option ) {
				$default_option = array(
					'disable-widget-areas'         => false,
					'disable-custom-widgets'       => false,
					'disable-drop-nav'             => false,
					'disable-blog-card'            => false,
					'disable-customizer-styles'    => false,
					'disable-hash-nav'             => false,
					'disable-support-forum-widget' => false,
					'disable-page-top'             => false,
					'disable-share-buttons'        => false,
					'disable-nav-menus'            => false,
					'disable-seo'                  => false,
					'disable-like-me-box'          => false,
					'disable-profile-box'          => false,
					'disable-related-posts'        => false,
					'disable-prev-next-nav'        => false,
					'disable-infobar'              => false,
					'disable-smooth-scroll'        => false,
					'disable-advertisement'        => false,
					'disable-theme-color'          => false,
					'disable-community'            => false,
				);

				$new_option = array();
				foreach ( $default_option as $key => $value ) {
					$new_option[ $key ] = ! empty( $option[ $key ] ) ? 1 : $value;
				}

				return $new_option;
			}
		);

		add_settings_section(
			'snow-monkey-diet-disable',
			__( 'Settings', 'snow-monkey-diet' ),
			function () {
			},
			'snow-monkey-diet'
		);

		add_settings_field(
			'disable-widget-areas',
			__( 'Disable widget areas', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-widget-areas]" value="1" <?php checked( 1, $this->_get_option( 'disable-widget-areas' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-custom-widgets',
			__( 'Disable custom widgets', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-custom-widgets]" value="1" <?php checked( 1, $this->_get_option( 'disable-custom-widgets' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-drop-nav',
			__( 'Disable drop navigation', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-drop-nav]" value="1" <?php checked( 1, $this->_get_option( 'disable-drop-nav' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-blog-card',
			__( 'Disable blog card', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-blog-card]" value="1" <?php checked( 1, $this->_get_option( 'disable-blog-card' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-customizer-styles',
			__( 'Disable CSS from the customizer', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-customizer-styles]" value="1" <?php checked( 1, $this->_get_option( 'disable-customizer-styles' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-hash-nav',
			__( 'Disable hash navs', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-hash-nav]" value="1" <?php checked( 1, $this->_get_option( 'disable-hash-nav' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-support-forum-widget',
			__( 'Disable support forum widget on the dashboard', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-support-forum-widget]" value="1" <?php checked( 1, $this->_get_option( 'disable-support-forum-widget' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-page-top',
			__( 'Disable page top button', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-page-top]" value="1" <?php checked( 1, $this->_get_option( 'disable-page-top' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-share-buttons',
			__( 'Disable share buttons', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-share-buttons]" value="1" <?php checked( 1, $this->_get_option( 'disable-share-buttons' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-nav-menus',
			__( 'Disable nav menus', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-nav-menus]" value="1" <?php checked( 1, $this->_get_option( 'disable-nav-menus' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-seo',
			__( 'Disable SEO', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-seo]" value="1" <?php checked( 1, $this->_get_option( 'disable-seo' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-like-me-box',
			__( 'Disable like me box', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-like-me-box]" value="1" <?php checked( 1, $this->_get_option( 'disable-like-me-box' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-profile-box',
			__( 'Disable profile box', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-profile-box]" value="1" <?php checked( 1, $this->_get_option( 'disable-profile-box' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-related-posts',
			__( 'Disable related posts', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-related-posts]" value="1" <?php checked( 1, $this->_get_option( 'disable-related-posts' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-prev-next-nav',
			__( 'Disable prev/next nav', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-prev-next-nav]" value="1" <?php checked( 1, $this->_get_option( 'disable-prev-next-nav' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-infobar',
			__( 'Disable infobar', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-infobar]" value="1" <?php checked( 1, $this->_get_option( 'disable-infobar' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-smooth-scroll',
			__( 'Disable smooth scroll', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-smooth-scroll]" value="1" <?php checked( 1, $this->_get_option( 'disable-smooth-scroll' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-advertisement',
			__( 'Disable advertisement', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-advertisement]" value="1" <?php checked( 1, $this->_get_option( 'disable-advertisement' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-theme-color',
			__( 'Disable mobile device browser color', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-theme-color]" value="1" <?php checked( 1, $this->_get_option( 'disable-theme-color' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-community',
			__( 'Disable Snow Monkey Community section in customizer', 'snow-monkey-diet' ),
			function () {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-community]" value="1" <?php checked( 1, $this->_get_option( 'disable-community' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);
	}

	/**
	 * Main processes.
	 */
	public function _disable() {
		if ( 1 === $this->_get_option( 'disable-widget-areas' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/setup/widget-area',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-custom-widgets' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/setup/widgets',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-drop-nav' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/setup/drop-nav',
				'__return_false'
			);
			add_filter( 'snow_monkey_has_drop_nav', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-blog-card' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/setup/oembed',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-customizer-styles' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/setup/customizer-styles',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-hash-nav' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/setup/hash-nav',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-support-forum-widget' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/setup/support-forum-widget',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-page-top' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/customizer/design/sections/base-design/controls/display-page-top',
				'__return_false'
			);
			add_filter(
				'snow_monkey_pre_template_part_render_app/setup/page-top',
				'__return_false'
			);
			add_filter(
				'snow_monkey_pre_template_part_render_template-parts/common/page-top',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-share-buttons' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/customizer/sns/sections/share-buttons/section',
				'__return_false'
			);
			add_filter(
				'snow_monkey_pre_template_part_render_app/setup/share-buttons',
				'__return_false'
			);
			add_filter(
				'snow_monkey_pre_template_part_render_template-parts/content/share-buttons',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-nav-menus' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/setup/nav-menus',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-seo' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/customizer/seo/panel',
				'__return_false'
			);
			add_filter(
				'snow_monkey_pre_template_part_render_app/setup/seo',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-like-me-box' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/customizer/sns/sections/like-me-box/section',
				'__return_false'
			);
			add_filter(
				'snow_monkey_pre_template_part_render_app/like-me-box',
				'__return_false'
			);
			add_filter(
				'snow_monkey_pre_template_part_render_template-parts/common/like-me-box',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-profile-box' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/customizer/design/sections/post/controls/profile-box',
				'__return_false'
			);
			add_filter(
				'snow_monkey_pre_template_part_render_template-parts/common/profile-box',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-related-posts' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/customizer/design/sections/post/controls/related-posts-layout',
				'__return_false'
			);
			add_filter(
				'snow_monkey_pre_template_part_render_app/customizer/design/sections/post/controls/related-posts',
				'__return_false'
			);
			add_filter(
				'snow_monkey_pre_template_part_render_template-parts/content/related-posts',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-prev-next-nav' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_template-parts/content/prev-next-nav',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-infobar' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/customizer/infobar/section',
				'__return_false'
			);
			add_filter(
				'snow_monkey_pre_template_part_render_template-parts/common/infobar',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-smooth-scroll' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/setup/smooth-scroll',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-advertisement' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/customizer/advertisement/section',
				'__return_false'
			);
			add_filter(
				'snow_monkey_pre_template_part_render_app/setup/google-adsense',
				'__return_false'
			);
			add_filter(
				'snow_monkey_pre_template_part_render_app/setup/google-infeed-ads',
				'__return_false'
			);
			add_filter(
				'snow_monkey_pre_template_part_render_template-parts/common/google-adsense',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-theme-color' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/setup/theme-color',
				'__return_false'
			);
		}

		if ( 1 === $this->_get_option( 'disable-community' ) ) {
			add_filter(
				'snow_monkey_pre_template_part_render_app/customizer/snow-monkey-community/section',
				'__return_false'
			);
		}
	}

	/**
	 * Return option.
	 *
	 * @param string $key The option key.
	 * @return mixed
	 */
	protected function _get_option( $key ) {
		$option = get_option( 'snow-monkey-diet' );
		return isset( $option[ $key ] ) ? (int) $option[ $key ] : false;
	}

	/**
	 * Load textdomain
	 */
	public function _load_textdomain() {
		load_plugin_textdomain( 'snow-monkey-diet', false, basename( __DIR__ ) . '/languages' );
	}
}

require_once SNOW_MONKEY_DIET_PATH . '/vendor/autoload.php';
new Bootstrap();
