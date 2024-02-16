<?php

namespace MostViewedGoogleAnalytics\Helpers;

use MostViewedGoogleAnalytics\App;

defined('ABSPATH') || exit;

class Utils {

	/**
	 * filterResults
	 *
	 * @param  mixed $ga_results
	 * @return array
	 */
	public static function filterResults(array $ga_results): array {
		if(empty($ga_results))
			return [];

		$exclude = self::getExcludePaths();

		$ga_results = array_filter($ga_results, function($item) use ($exclude) {
			return !in_array($item['ga:pagePath'], $exclude);
		});

		return array_values($ga_results);
	}

	/**
	 * getExcludePaths
	 *
	 * @return array
	 */
	public static function getExcludePaths(): array {
		$paths   = ['/'];
		$exclude = explode("\r\n", get_option(App::$domain . '_exclude'));

		if(is_array($exclude) && count($exclude))
		  $paths = array_unique(array_merge($paths, array_values($exclude)));

		return $paths;
	}

	/**
	 * getRange
	 *
	 * @param  mixed $period
	 * @return string
	 */
	public function getRange(string $period): string {
		$ranges = [
			'year'    => date_i18n('Y-m-d', strtotime('-1 year')),
			'month'   => date_i18n('Y-m-d', strtotime('-29 days')),
			'week'    => date_i18n('Y-m-d', strtotime('-1 week')),
			'72hours' => date_i18n('Y-m-d', strtotime('-3 days')),
			'48hours' => date_i18n('Y-m-d', strtotime('-2 days')),
			'today'   => date_i18n('Y-m-d', strtotime('today')),
		];

		return $ranges[$period] ?? $ranges['month'];
	}

}
