<?php

namespace MostViewedGoogleAnalytics\Controllers;

use MostViewedGoogleAnalytics\App;

defined('ABSPATH') || exit;

class Settings {

	public function __construct() {
		add_action('admin_menu', array($this, 'addSubmenu'));
		add_action('admin_init', array($this, 'registerSettings'));
	}

	/**
	 * addSubmenu
	 *
	 * @return void
	 */
	public function addSubmenu(): void {
		add_submenu_page(
			'options-general.php',
			__('Most Viewed by Google Analytics', App::$domain),
			__('Most Viewed by Google Analytics', App::$domain),
			'manage_options',
			App::$domain,
			array('MostViewedGoogleAnalytics\Views\Options', 'renderPage')
		);
	}

	/**
	 * registerSettings
	 *
	 * @return void
	 */
	public function registerSettings(): void {
		add_settings_section(
			App::$domain,
			'',
			null,
			App::$domain
		);

		register_setting(App::$domain, App::$domain . '_view_id');
		register_setting(App::$domain, App::$domain . '_property_id');
		register_setting(App::$domain, App::$domain . '_credentials');
		register_setting(App::$domain, App::$domain . '_exclude');
		register_setting(App::$domain, App::$domain . '_ga');

		add_settings_field(
			App::$domain . '_view_id',
			'View ID',
			array('MostViewedGoogleAnalytics\Views\Options', 'renderInput'),
			App::$domain,
			App::$domain,
			array(
				'label_for'   => App::$domain . '_view_id',
				'name' 		  => App::$domain . '_view_id',
				'placeholder' => 'Ex.: 219408844',
			)
		);

		add_settings_field(
			App::$domain . '_credentials',
			'credentials',
			array('MostViewedGoogleAnalytics\Views\Options', 'renderTextarea' ),
			App::$domain,
			App::$domain,
			array(
				'label_for'   => App::$domain . '_credentials',
				'name' 		  => App::$domain . '_credentials',
				'placeholder' => 'Insert here the json with the credentials',
			)
		);

		add_settings_field(
			App::$domain . '_exclude',
			'Ignore URLs',
			array('MostViewedGoogleAnalytics\Views\Options', 'renderTextarea'),
			App::$domain,
			App::$domain,
			array(
				'label_for'   => App::$domain . '_exclude',
				'name' 		  => App::$domain . '_exclude',
				'placeholder' => 'Enter the list of URLs that should be ignored here, one per line',
			)
		);

		add_settings_field(
			App::$domain . '_property_id',
			'Property ID',
			array('MostViewedGoogleAnalytics\Views\Options', 'renderInput'),
			App::$domain,
			App::$domain,
			array(
				'label_for'   => App::$domain . '_property_id',
				'name' 		  => App::$domain . '_property_id',
				'placeholder' => 'Ex.: 219408844',
			)
		);

		add_settings_field(
			App::$domain . '_ga',
			'Analitycs Version',
			array('MostViewedGoogleAnalytics\Views\Options', 'renderRadio'),
			App::$domain,
			App::$domain,
			array(
				'label_for'   => App::$domain . '_ga',
				'name' 		  => App::$domain . '_ga',
				'values'       => array(
					"GA4", "GA3"
				)
			)
		);
	}

}
