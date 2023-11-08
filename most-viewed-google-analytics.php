<?php

/**
 * Plugin Name: Most Viewed by Google Analytics
 * Version: 1.0.0
 * Author: Studio Visual
 * Author URI:  https://studiovisual.com.br
 * Description: Integrates with Google Analytics to obtain the most viewed content
 * Text Domain: most-viewed-google-analytics
 * Domain Path: /languages
 * Requires PHP: 7.0
*/

use MostViewedGoogleAnalytics\App;
use MostViewedGoogleAnalytics\Helpers\Utils;
use MostViewedGoogleAnalytics\Services\PageViews;
use MostViewedGoogleAnalytics\Services\PageViewsGa4;

defined('ABSPATH') || exit;

if(file_exists(__DIR__ . '/vendor/autoload.php'))
	require __DIR__ . '/vendor/autoload.php';
else
	exit;

new App;

/**
 * Return a list of most viewed content
 *
 * @param string  $period Interval to get posts. Ex.: year, month, week, today
 * @param integer $quantity The max quantity is 10
 *
 * @return array
 */
function most_viewed(string $period = 'month', int $quantity = 5): array {
	if(!in_array($period, ['year', 'month', 'week', 'today']))
		throw new Exception('MostViewedGoogleAnalytics: Invalid interval. Choose one of valid options: year, month, week, 72hours, 48hours, today');

	if($quantity > 10)
		throw new Exception('MostViewedGoogleAnalytics: Invalid quantity. The max quantity is 10.');

	$transient_name  = App::$domain . '_' . $period;
	$transient_value = get_transient($transient_name);

	if(!empty($transient_value))
		return array_slice($transient_value, 0, $quantity);


	if (get_option(App::$domain . '_ga') == "GA4") {
		$service = PageViewsGa4::getInstance();
	} else {
		$service = PageViews::getInstance();
	}

	$views   = $service->getReports($period);

	if (!$views) {
		return [];
	}

	$results = Utils::filterResults($views);

	set_transient($transient_name, $results, HOUR_IN_SECONDS * 24);

	return array_slice($results, 0, $quantity);
}
