<?php

namespace MostViewedGoogleAnalytics;

use MostViewedGoogleAnalytics\Controllers\Settings;

Class App {

    // Plugin domain
    static $domain = 'most-viewed-google-analytics';

    public function __construct() {
        add_action('init', array($this, 'loadTextdomain'));

		new Settings;
    }

    /**
     * loadTextdomain Load languages
     *
     * @return void
     */
    function loadTextdomain(): void {
        \load_plugin_textdomain(self::$domain, false, self::$domain . '/languages');
    }

}
