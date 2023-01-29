<?php

namespace MostViewedGoogleAnalytics\Services;

defined('ABSPATH') || exit;

use Google_Client;
use Google_Service_AnalyticsReporting;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_ReportRequest;
use Google_Service_AnalyticsReporting_GetReportsRequest;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_Dimension;
use Google_Service_AnalyticsReporting_OrderBy;
use Google_Service_Exception;
use MostViewedGoogleAnalytics\App;

class PageViews {

	private static $instance = null;

	protected $credentials;

	protected $view_id;

	private function __construct() {
		$this->view_id     = get_option(App::$domain . '_view_id');
		$this->credentials = get_option(App::$domain . '_credentials');
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
			'week'    => date_i18n('Y-m-d', strtotime('-6 days')),
			'72hours' => date_i18n('Y-m-d', strtotime('-3 days')),
			'48hours' => date_i18n('Y-m-d', strtotime('-2 days')),
			'today'   => date_i18n('Y-m-d', strtotime('today')),
		];

		return $ranges[$period] ?? $ranges['month'];
	}

	/**
	 * getReports
	 *
	 * @param  mixed $period
	 * @return void
	 */
	public function getReports(string $period = 'month') {
		try {
			if(empty($this->credentials))
				return false;

			$client = new Google_Client();

			$client->setApplicationName('Most Viewed by Google Analytics');
			$client->setAuthConfig(json_decode($this->credentials, true));
			$client->setScopes(array('https://www.googleapis.com/auth/analytics.readonly'));

			$analytics = new Google_Service_AnalyticsReporting($client);

			$response = $analytics->reports->batchGet(
				$this->buildReport($this->getRange($period))
			);

			$results = $response->getReports();

			if(isset($results['message']))
				return false;

			$parsed = $this->parseResponse($results);

			if(empty($parsed))
				return false;

			return $parsed;
		}
		catch(Google_Service_Exception $e) {
			error_log('MostViewedGoogleAnalytics/getReports: ' . print_r($e->getErrors(), true));

			return $e->getMessage();
		}
	}

	/**
	 * buildReport
	 *
	 * @param  mixed $start_date
	 * @return void
	 */
	public function buildReport($start_date) {
		if(empty($this->view_id))
			return;

		$date_range = new Google_Service_AnalyticsReporting_DateRange();
		$date_range->setStartDate($start_date);
		$date_range->setEndDate('today');

		$session = new Google_Service_AnalyticsReporting_Metric();
		$session->setExpression('ga:pageviews');
		$session->setAlias('pageviews');

		$dimensionPagePath = new Google_Service_AnalyticsReporting_Dimension();
		$dimensionPagePath->setName('ga:pagePath');

		$dimensionPageTitle = new Google_Service_AnalyticsReporting_Dimension();
		$dimensionPageTitle->setName('ga:pageTitle');

		$ordering = new Google_Service_AnalyticsReporting_OrderBy();
		$ordering->setFieldName('ga:pageviews');
		$ordering->setOrderType('VALUE');
		$ordering->setSortOrder('DESCENDING');

		$request = new Google_Service_AnalyticsReporting_ReportRequest();
		$request->setViewId($this->view_id);
		$request->setDateRanges($date_range);
		$request->setMetrics(array($session));
		$request->setDimensions([$dimensionPagePath, $dimensionPageTitle]);
		$request->setOrderBys($ordering);
		$request->setPageSize(20);

		$body = new Google_Service_AnalyticsReporting_GetReportsRequest();

		try {
			$body->setReportRequests(array($request));

			return $body;
		}
		catch(Google_Service_Exception $e) {
			error_log('MostViewedGoogleAnalytics/buildReport: ' . print_r($e->getErrors(), true));

			return $e->getErrors();
		}
	}

	/**
	 * parseResponse
	 *
	 * @param  mixed $response
	 * @return void
	 */
	private function parseResponse($response) {
		if(empty($response) || ! isset($response[0]))
			return false;

		$report = $response[0];

		$header           = $report->getColumnHeader();
		$dimensionHeaders = $header->getDimensions();
		$rows             = $report->getData()->getRows();

		$data = [];

		foreach($rows as $row):
			$item = [];

			foreach($row->dimensions as $index => $dimensionValue)
				$item[$dimensionHeaders[$index ]] = $dimensionValue;

			$item['total'] = $row->metrics[0]['values'][0];
			$data[]        = $item;
		endforeach;

		return $data;
	}

	/**
	 * getInstance
	 *
	 * @return void
	 */
	public static function getInstance() {
		if(self::$instance === null)
			self::$instance = new self();

		return self::$instance;
	}
}
