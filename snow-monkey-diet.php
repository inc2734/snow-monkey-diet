<?php
/**
 * Plugin name: Snow Monkey Diet
 * Description: You can stop unused functions of the Snow Monkey.
 * Version: 0.3.0
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

	public function __construct() {
		add_action( 'plugins_loaded', [ $this, '_plugins_loaded' ] );
	}

	public function _plugins_loaded() {
		load_plugin_textdomain( 'snow-monkey-diet', false, basename( __DIR__ ) . '/languages' );

		add_action( 'init', [ $this, '_activate_autoupdate' ] );

		$theme = wp_get_theme( get_template() );
		if ( 'snow-monkey' !== $theme->template && 'snow-monkey/resources' !== $theme->template ) {
			add_action( 'admin_notices', [ $this, '_admin_notice_no_snow_monkey' ] );
			return;
		}

		if ( ! version_compare( $theme->get( 'Version' ), '9.0.0', '>=' ) ) {
			add_action( 'admin_notices', [ $this, '_admin_notice_invalid_snow_monkey_version' ] );
			return;
		}

		add_action( 'admin_menu', [ $this, '_admin_menu' ] );
		add_action( 'admin_init', [ $this, '_admin_init' ] );

		$this->_deisable();
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
				<?php esc_html_e( '[Snow Monkey Diet] Needs the Snow Monkey v9.0.0 or more.', 'snow-monkey-diet' ); ?>
			</p>
		</div>
		<?php
	}

	public function _admin_menu() {
		add_options_page(
			__( 'Snow Monkey Diet', 'snow-monkey-diet' ),
			__( 'Snow Monkey Diet', 'snow-monkey-diet' ),
			'manage_options',
			'snow-monkey-diet',
			function() {
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

	public function _admin_init() {
		register_setting(
			'snow-monkey-diet',
			'snow-monkey-diet',
			function( $option ) {
				$get_posted_option = function( $key ) use ( $option ) {
					return isset( $option[ $key ] ) && '1' === $option[ $key ] ? (int) $option[ $key ] : false;
				};

				return [
					'disable-widget-areas'         => $get_posted_option( 'disable-widget-areas' ),
					'disable-custom-widgets'       => $get_posted_option( 'disable-custom-widgets' ),
					'disable-blog-card'            => $get_posted_option( 'disable-blog-card' ),
					'disable-customizer-styles'    => $get_posted_option( 'disable-customizer-styles' ),
					'disable-hash-nav'             => $get_posted_option( 'disable-hash-nav' ),
					'disable-support-forum-widget' => $get_posted_option( 'disable-support-forum-widget' ),
					'disable-page-top'             => $get_posted_option( 'disable-page-top' ),
					'disable-share-buttons'        => $get_posted_option( 'disable-share-buttons' ),
					'disable-nav-menus'            => $get_posted_option( 'disable-nav-menus' ),
					'disable-seo'                  => $get_posted_option( 'disable-seo' ),
					'disable-like-me-box'          => $get_posted_option( 'disable-like-me-box' ),
					'disable-profile-box'          => $get_posted_option( 'disable-profile-box' ),
					'disable-related-posts'        => $get_posted_option( 'disable-related-posts' ),
					'disable-related-posts'        => $get_posted_option( 'disable-related-posts' ),
					'disable-prev-next-nav'        => $get_posted_option( 'disable-prev-next-nav' ),
					'disable-infobar'              => $get_posted_option( 'disable-infobar' ),
					'disable-smooth-scroll'        => $get_posted_option( 'disable-smooth-scroll' ),
					'disable-advertisement'        => $get_posted_option( 'disable-advertisement' ),
				];
			}
		);

		add_settings_section(
			'snow-monkey-diet-disable',
			__( 'Settings', 'snow-monkey-diet' ),
			function() {
			},
			'snow-monkey-diet'
		);

		add_settings_field(
			'disable-widget-areas',
			__( 'Disable widget areas', 'snow-monkey-diet' ),
			function() {
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
			function() {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-custom-widgets]" value="1" <?php checked( 1, $this->_get_option( 'disable-custom-widgets' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);

		add_settings_field(
			'disable-blog-card',
			__( 'Disable blog card', 'snow-monkey-diet' ),
			function() {
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
			function() {
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
			function() {
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
			function() {
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
			function() {
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
			function() {
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
			function() {
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
			function() {
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
			function() {
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
			function() {
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
			function() {
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
			function() {
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
			function() {
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
			function() {
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
			function() {
				?>
				<input type="checkbox" name="snow-monkey-diet[disable-advertisement]" value="1" <?php checked( 1, $this->_get_option( 'disable-advertisement' ) ); ?>>
				<?php
			},
			'snow-monkey-diet',
			'snow-monkey-diet-disable'
		);
	}

	public function _deisable() {
		if ( 1 === $this->_get_option( 'disable-widget-areas' ) ) {
			add_action( 'snow_monkey_get_template_part_app/setup/widget-area', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-custom-widgets' ) ) {
			add_action( 'snow_monkey_get_template_part_app/setup/widgets', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-blog-card' ) ) {
			add_action( 'snow_monkey_get_template_part_app/setup/oembed', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-customizer-styles' ) ) {
			add_action( 'snow_monkey_get_template_part_app/setup/customizer-styles', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-hash-nav' ) ) {
			add_action( 'snow_monkey_get_template_part_app/setup/hash-nav', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-support-forum-widget' ) ) {
			add_action( 'snow_monkey_get_template_part_app/setup/support-forum-widget', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-page-top' ) ) {
			add_action( 'snow_monkey_get_template_part_app/customizer/design/sections/base-design/controls/display-page-top', '__return_false' );
			add_action( 'snow_monkey_get_template_part_app/setup/page-top', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-share-buttons' ) ) {
			add_action( 'snow_monkey_get_template_part_app/customizer/sns/sections/share-buttons/section', '__return_false' );
			add_action( 'snow_monkey_get_template_part_app/setup/share-buttons', '__return_false' );
			add_action( 'snow_monkey_get_template_part_template-parts/content/share-buttons', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-nav-menus' ) ) {
			add_action( 'snow_monkey_get_template_part_app/setup/nav-menus', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-seo' ) ) {
			add_action( 'snow_monkey_get_template_part_app/customizer/seo/panel', '__return_false' );
			add_action( 'snow_monkey_get_template_part_app/setup/seo', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-like-me-box' ) ) {
			add_action( 'snow_monkey_get_template_part_app/customizer/sns/sections/like-me-box/section', '__return_false' );
			add_action( 'snow_monkey_get_template_part_app/like-me-box', '__return_false' );
			add_action( 'snow_monkey_get_template_part_template-parts/common/like-me-box', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-profile-box' ) ) {
			add_action( 'snow_monkey_get_template_part_app/customizer/design/sections/post/controls/profile-box', '__return_false' );
			add_action( 'snow_monkey_get_template_part_template-parts/common/profile-box', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-related-posts' ) ) {
			add_action( 'snow_monkey_get_template_part_app/customizer/design/sections/post/controls/related-posts-layout', '__return_false' );
			add_action( 'snow_monkey_get_template_part_app/customizer/design/sections/post/controls/related-posts', '__return_false' );
			add_action( 'snow_monkey_get_template_part_template-parts/content/profile-box', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-prev-next-nav' ) ) {
			add_action( 'snow_monkey_get_template_part_template-parts/content/prev-next-nav', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-infobar' ) ) {
			add_action( 'snow_monkey_get_template_part_app/customizer/infobar/section', '__return_false' );
			add_action( 'snow_monkey_get_template_part_template-parts/common/infobar', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-smooth-scroll' ) ) {
			add_action( 'snow_monkey_get_template_part_app/setup/smooth-scroll', '__return_false' );
		}

		if ( 1 === $this->_get_option( 'disable-advertisement' ) ) {
			add_action( 'snow_monkey_get_template_part_app/customizer/advertisement/section', '__return_false' );
			add_action( 'snow_monkey_get_template_part_app/setup/google-adsense', '__return_false' );
			add_action( 'snow_monkey_get_template_part_app/setup/google-infeed-ads', '__return_false' );
			add_action( 'snow_monkey_get_template_part_template-parts/common/google-adsense', '__return_false' );
		}
	}

	protected function _get_option( $key ) {
		$option = get_option( 'snow-monkey-diet' );
		return isset( $option[ $key ] ) ? (int) $option[ $key ] : false;
	}

	/**
	 * Activate auto update using GitHub
	 *
	 * @return void
	 */
	public function _activate_autoupdate() {
		new Updater(
			plugin_basename( __FILE__ ),
			'inc2734',
			'snow-monkey-diet'
		);
	}
}

require_once( SNOW_MONKEY_DIET_PATH . '/vendor/autoload.php' );
new Bootstrap();
