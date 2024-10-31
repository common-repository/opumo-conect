<?php
if (!defined('WPINC')) {
	die;
}

class Opumo_Pixel
{
	/**
	 * @var Opumo_Pixel_PX $pixel object that controls tracking sale conversions
	 * @var Opumo_Pixel_Loader $loader Loader object that coordinates actions and filters between core plugin and admin classes
	 * @var string $plugin_slug WordPress Slug for this plugin
	 * @var string $version Plugin version
	 */
	private $pixel, $analytics, $loader, $plugin_slug, $version;

	public function __construct($version)
	{

		$this->plugin_slug = 'opumo-pixel-slug';
		$this->version     = $version;

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_woocommerce_hooks();
	}

	private function load_dependencies()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-opumo-pixel-admin.php';
		require_once plugin_dir_path(__FILE__) . 'class-opumo-pixel-px.php';
		require_once plugin_dir_path(__FILE__) . 'opumo-pixel-loader.php';

		$this->loader    = new Opumo_Pixel_Loader();
		//both define_frontend_hooks() and define_woocommerce_hooks() rely on $pixel and $analytics objects so instantiate them here instead
		$this->pixel     = new Opumo_Pixel_PX($this->version);
	}

	function define_admin_hooks()
	{
		$admin = new Opumo_Pixel_Admin($this->version);
		$this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_styles');
		$this->loader->add_action('admin_menu',            $admin, 'admin_menu');


		//admin filters
		$this->loader->add_filter('plugin_action_links_' . OPUMO_PIXEL_PLUGIN_FILENAME, $admin, 'render_settings_shortcut');
	}

	private function define_woocommerce_hooks()
	{
		//conversion tracking pixel
		$this->loader->add_action('woocommerce_thankyou', $this->pixel, 'woocommerce_thankyou');
		$this->loader->add_action('wp_head', $this->pixel, 'opumo_analytics_track_pageviews');
	}
	public function run()
	{
		$this->loader->run();
	}
}
