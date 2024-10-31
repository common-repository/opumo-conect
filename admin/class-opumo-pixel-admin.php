<?php
if (!defined('WPINC')) {
	die;
}

class Opumo_Pixel_Admin
{
	/**
	 * @var string $version Plugin version
	 */
	private $version;

	public function __construct($version)
	{
		$this->version = $version;
		add_action('admin_init', array($this, 'admin_init'));
	}

	public function enqueue_styles($hook)
	{

		wp_enqueue_style(
			'opumo-pixel-admin',
			esc_url(plugin_dir_url(__FILE__) . 'css/opumo-pixel-admin.css'),
			array(),
			$this->version
		);
	}

	public function admin_init()
	{
		$options = get_option('opumo_connect_options');
		register_setting('opumo_connect_options', 'opumo_connect_options', array($this, 'sanitize_settings'));

		add_settings_section('opumo_connect_required', 'Required Info', array($this, 'render_settings_required_section_text'), 'opumo_pixel');
		add_settings_field(
			'merchant-id',
			'Merchant ID',
			array($this, 'render_settings_input'),
			'opumo_pixel',
			'opumo_connect_required',
			array(
				'label_for'   => 'merchant-id',
				'id'          => 'merchant-id',
				'name'        => 'merchant-id',
				'value'       => !empty($options['merchant-id']) ? $options['merchant-id'] : '',
				//mimicking WordPress's own " $type='$type'" output format for disabled(), checked(), and selected() functions...
				'status'      => "required='required' autocomplete='off' pattern=\d* maxlength=6",
				'size'        => 22,
				'type'        => 'text',
				'placeholder' => 'OPUMO Connect Merchant ID',
				'class'       => 'opumo-pixel-option',
			)
		);
	}


	public function admin_menu()
	{

		/** Add the top-level admin menu */
		$page_title = 'OPUMO Connect WooCommerce Tracker Settings';
		$menu_title = 'OPUMO Connect';
		$capability = 'manage_options';
		$menu_slug  = 'opumo_pixel';
		$callback   = array($this, 'render_settings_page');
		$icon_url   = 'none';
		add_menu_page($page_title, $menu_title, $capability, $menu_slug, $callback, $icon_url);
	}

	public function render_settings_page()
	{
		//must be included so regular setting saves show general 'Settings saved' notice
		//and also any setting errors on the stack without a slug (first arg in add_settings_error() function) are displayed using settings_errors()
		include_once 'options-head.php';
		if (!is_plugin_active('woocommerce/woocommerce.php')) {
			add_settings_error(
				'opumo_pixel_woocommerce_warning',
				esc_attr('woocommerce-warning'),
				'WooCommerce plugin must be installed and activated to use this plugin.'
			);
			settings_errors('opumo_pixel_woocommerce_warning', false, true);
			return;
		}

		require_once plugin_dir_path(__FILE__) . 'templates/opumo-pixel-settings.php';
	}

	public function sanitize_settings($new_settings = array())
	{
		$old_settings      = get_option('opumo_connect_options') ? get_option('opumo_connect_options') : array();
		$final_settings    = array_merge($old_settings, $new_settings);

		if (empty($final_settings['merchant-id'])) {
			add_settings_error(
				'opumo_pixel_merchant_id',
				esc_attr('merchant-id'),
				'You must enter an OPUMO Connect Merchant ID.'
			);
		}

		return $final_settings;
	}

	//add shortcut to settings page 
	public function render_settings_shortcut($links)
	{
		$settings_link = '<a href="' . esc_url(admin_url('admin.php?page=opumo_pixel')) . '">Settings</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	public function render_settings_input($attributes)
	{
		$template      = file_get_contents(plugin_dir_path(__FILE__) . 'templates/opumo-pixel-settings-input.php');
		$template_data = array_map('esc_attr', $attributes);

		foreach ($template_data as $macro => $value) {
			$template = str_replace("!!$macro!!", $value, $template);
		}

		echo wp_kses($template, array(
			'input' => array(
				'autocomplete' => true,
				'checked'      => true,
				'class'        => true,
				'disabled'     => true,
				'height'       => true,
				'id'           => true,
				'max'          => true,
				'maxlength'    => true,
				'min'          => true,
				'name'         => true,
				'pattern'      => true,
				'placeholder'  => true,
				'required'     => true,
				'size'         => true,
				'type'         => true,
				'value'        => true,
				'width'        => true,
			),
		));
	}

	public function render_settings_required_section_text()
	{
		require_once plugin_dir_path(__FILE__) . 'templates/opumo-pixel-settings-required-section-text.php';
	}
}
