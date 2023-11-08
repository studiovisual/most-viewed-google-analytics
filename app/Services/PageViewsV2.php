<?php

namespace MostViewedGoogleAnalytics\Services;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\RunReportResponse;
use Google\ApiCore\ApiException;
use Google\ApiCore\CredentialsWrapper;
use Google\ApiCore\ValidationException;
use GuzzleHttp\Exception\RequestException;
use MostViewedGoogleAnalytics\App;
use MostViewedGoogleAnalytics\Helpers\Utils;

class PageViewsV2
{
	private static $instance = null;

	protected $property_id;
	protected $credentials;

	private function __construct() {
		$this->credentials = get_option(App::$domain . '_credentials');
		$this->property_id = get_option(App::$domain . '_property_id');;
	}

	public static function getInstance() {
		if(self::$instance === null)
			self::$instance = new self();

		return self::$instance;
	}

	/**
	 * @throws ApiException
	 * @throws ValidationException
	 */
	public function getReports(string $period = 'month') {

		try {
			$util = new Utils();

			$options = $this->buildCredentialsOptions();
			$client = new BetaAnalyticsDataClient($options);
			$request = $this->buildRequest($util->getRange($period));
			$response = $client->runReport($request);

			$parsed = $this->parseResponse($response);

			if(empty($parsed))
				return false;

			return $parsed;
		}
		catch(ValidationException $e) {
			error_log('MostViewedGoogleAnalytics/getReports: ' . print_r($e->getMessage(), true));
			dd($e->getMessage());
			return $e->getMessage();
		}
		catch (ApiException $e) {
			dd($e->getMessage());
		}
		catch (RequestException $e) {
			dd($e->getMessage());
		}
		catch (\Exception $e) {
			dd($e->getMessage());
		}
	}

	public function buildRequest($start_date) {
		$request = new RunReportRequest();

		$request->setProperty('properties/' . $this->property_id);

		$request->setDateRanges([
			new DateRange([
				'start_date' => $start_date,
				'end_date' => 'today',
			]),
		]);

		$request->setDimensions([
			new Dimension([
				'name' => 'pageTitle',
			]),
			new Dimension([
				'name' => 'pagePath',
			])
		]);

		$request->setMetrics([
			new Metric([
				'name' => 'screenPageViews'
			])
		]);

		$request->setLimit(20);

		return $request;
	}

	public function buildCredentialsOptions (){
		return [
			'credentials' => CredentialsWrapper::build( [
				'scopes'  => [
					'https://www.googleapis.com/auth/analytics.readonly',
				],
				'keyFile' => json_decode($this->credentials, true)
			] ),
		];
	}

	/**
	 * parseResponse
	 *
	 * @param  mixed $response
	 * @return void
	 */
	private function parseResponse(RunReportResponse $response) {
		if(empty($response) || ! isset($response))
			return false;

		$dimensionHeaders = $response->getDimensionHeaders();
		$rows             = $response->getRows();

		$data = [];

		foreach($rows as $row) {
			$item = [];

			foreach ($row->getDimensionValues() as $index => $dimensionValue) {
				$item['ga:'. $dimensionHeaders[$index]->getName()] = $dimensionValue->getValue();
			}
			$item['total'] = $row->getMetricValues()[0]->getValue();
			$data[] = $item;
		}
		return $data;
	}
}
