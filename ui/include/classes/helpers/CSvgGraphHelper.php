<?php declare(strict_types = 0);
/*
** Zabbix
** Copyright (C) 2001-2023 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


use Zabbix\Widgets\Fields\CWidgetFieldGraphDataSet;

/**
 * Class calculates graph data and makes SVG graph.
 */
class CSvgGraphHelper {

	/**
	 * Calculate graph data and draw SVG graph based on given graph configuration.
	 *
	 * @param array  $options                     Options for graph.
	 * @param array  $options['data_sets']        Graph data set options.
	 * @param int    $options['data_source']      Data source of graph.
	 * @param bool   $options['dashboard_time']   True if dashboard time is used.
	 * @param array  $options['time_period']      Graph time period used.
	 * @param array  $options['left_y_axis']      Options for graph left Y axis.
	 * @param array  $options['right_y_axis']     Options for graph right Y axis.
	 * @param array  $options['x_axis']           Options for graph X axis.
	 * @param array  $options['legend']           Options for graph legend.
	 * @param int    $options['legend_lines']     Number of lines in the legend.
	 * @param array  $options['problems']         Graph problems options.
	 * @param array  $options['overrides']        Graph override options.
	 *
	 * @throws Exception
	 */
	public static function get(array $options, int $width, int $height): array {
		$metrics = [];
		$errors = [];

		// Find which metrics will be shown in graph and calculate time periods and display options.
		self::getMetricsPattern($metrics, $options['data_sets'], $options['templateid'], $options['dynamic_hostid']);
		self::getMetricsItems($metrics, $options['data_sets'], $options['templateid'], $options['dynamic_hostid']);
		self::sortByDataset($metrics);
		// Apply overrides for previously selected $metrics.
		self::applyOverrides($metrics, $options['templateid'], $options['dynamic_hostid'], $options['overrides']);
		self::applyUnits($metrics, $options['axes']);
		// Apply time periods for each $metric, based on graph/dashboard time as well as metric level time shifts.
		self::getTimePeriods($metrics, $options['time_period']);
		// Find what data source (history or trends) will be used for each metric.
		self::getGraphDataSource($metrics, $errors, $options['data_source'], $width);
		// Load Data for each metric.
		self::getMetricsData($metrics, $width);
		// Load aggregated Data for each dataset.
		self::getMetricsAggregatedData($metrics, $width, $options['data_sets']);

		$legend = self::getLegend($metrics, $options['legend']);

		$svg_height = $height - ($legend !== null ? $legend->getLinesCount() * CSvgGraphLegend::LINE_HEIGHT : 0);

		if (0 > $svg_height) {
			$svg_height = 0;
		}

		$graph = (new CSvgGraph([
			'displaying' => $options['displaying'],
			'time_period' => $options['time_period'],
			'axes' => $options['axes']
		]))
			->setSize($width, $svg_height)
			->addMetrics($metrics)
			->addSimpleTriggers(self::getSimpleTriggers($metrics, $options['displaying']))
			->addProblems(self::getProblems($metrics, $options['problems'], $options['time_period'],
				$options['templateid'], $options['dynamic_hostid'])
			)
			->draw();

		// SBox available only for graphs without overridden relative time.
		if ($options['dashboard_time']) {
			$graph->addSBox();
		}

		// Add mouse following helper line.
		$graph->addHelper();

		return [
			'svg' => $graph,
			'legend' => $legend ?? '',
			'data' => [
				'dims' => [
					'x' => $graph->getCanvasX(),
					'y' => $graph->getCanvasY(),
					'w' => $graph->getCanvasWidth(),
					'h' => $graph->getCanvasHeight()
				],
				'spp' => ($options['time_period']['time_to'] - $options['time_period']['time_from'])
					/ $graph->getCanvasWidth()
			],
			'errors' => $errors
		];
	}

	private static function getMetricsPattern(array &$metrics, array $data_sets, string $templateid,
			string $dynamic_hostid): void {
		$max_metrics = SVG_GRAPH_MAX_NUMBER_OF_METRICS;

		foreach ($data_sets as $index => $data_set) {
			if ($data_set['dataset_type'] == CWidgetFieldGraphDataSet::DATASET_TYPE_SINGLE_ITEM) {
				continue;
			}

			if ($templateid === '') {
				if (!$data_set['hosts'] || !$data_set['items']) {
					continue;
				}
			}
			else {
				if (!$data_set['items']) {
					continue;
				}
			}

			if ($max_metrics == 0) {
				break;
			}

			$options = [
				'output' => ['itemid', 'hostid', 'name', 'history', 'trends', 'units', 'value_type'],
				'selectHosts' => ['name'],
				'webitems' => true,
				'filter' => [
					'value_type' => [ITEM_VALUE_TYPE_UINT64, ITEM_VALUE_TYPE_FLOAT]
				],
				'search' => [
					'name' => self::processPattern($data_set['items'])
				],
				'searchWildcardsEnabled' => true,
				'searchByAny' => true,
				'sortfield' => 'name',
				'sortorder' => ZBX_SORT_UP,
				'limit' => $max_metrics
			];

			if ($templateid === '') {
				// Find hosts.
				$hosts = API::Host()->get([
					'output' => [],
					'search' => [
						'name' => self::processPattern($data_set['hosts'])
					],
					'searchWildcardsEnabled' => true,
					'searchByAny' => true,
					'preservekeys' => true
				]);

				if ($hosts) {
					$options['hostids'] = array_keys($hosts);
				}
			}
			else {
				$options['hostids'] = $dynamic_hostid !== '' ? $dynamic_hostid : $templateid;
			}

			$items = null;

			if (array_key_exists('hostids', $options) && $options['hostids']) {
				$items = API::Item()->get($options);
			}

			if (!$items) {
				continue;
			}

			unset($data_set['itemids'], $data_set['items']);

			// The bigger transparency level, the less visible the metric is.
			$data_set['transparency'] = 10 - (int)$data_set['transparency'];

			$data_set['timeshift'] = ($data_set['timeshift'] !== '')
				? (int)timeUnitToSeconds($data_set['timeshift'])
				: 0;

			$colors = getColorVariations('#' . $data_set['color'], count($items));

			foreach ($items as $item) {
				$data_set['color'] = array_shift($colors);
				$metrics[] = $item + ['data_set' => $index, 'options' => $data_set];
				$max_metrics--;
			}
		}
	}

	private static function getMetricsItems(array &$metrics, array $data_sets, string $templateid,
			string $dynamic_hostid): void {
		$max_metrics = SVG_GRAPH_MAX_NUMBER_OF_METRICS;

		foreach ($data_sets as $index => $data_set) {
			if ($data_set['dataset_type'] == CWidgetFieldGraphDataSet::DATASET_TYPE_PATTERN_ITEM) {
				continue;
			}

			if (!$data_set['itemids']) {
				continue;
			}

			if ($max_metrics == 0) {
				break;
			}

			if ($templateid !== '' && $dynamic_hostid !== '') {
				$tmp_items = API::Item()->get([
					'output' => ['key_'],
					'itemids' => $data_set['itemids'],
					'webitems' => true
				]);

				if ($tmp_items) {
					$items = API::Item()->get([
						'output' => ['itemid'],
						'hostids' => [$dynamic_hostid],
						'webitems' => true,
						'filter' => [
							'key_' => array_column($tmp_items, 'key_')
						]
					]);
					$data_set['itemids'] = $items ? array_column($items, 'itemid') : null;
				}
			}

			$items_db = API::Item()->get([
				'output' => ['itemid', 'hostid', 'name', 'history', 'trends', 'units', 'value_type'],
				'selectHosts' => ['name'],
				'webitems' => true,
				'filter' => [
					'value_type' => [ITEM_VALUE_TYPE_UINT64, ITEM_VALUE_TYPE_FLOAT]
				],
				'itemids' => $data_set['itemids'],
				'preservekeys' => true,
				'limit' => $max_metrics
			]);

			$items = [];

			foreach ($data_set['itemids'] as $itemid) {
				if (array_key_exists($itemid, $items_db)) {
					$items[] = $items_db[$itemid];
				}
			}

			if (!$items) {
				continue;
			}

			unset($data_set['itemids']);

			// The bigger transparency level, the less visible the metric is.
			$data_set['transparency'] = 10 - (int) $data_set['transparency'];

			$data_set['timeshift'] = ($data_set['timeshift'] !== '')
				? (int) timeUnitToSeconds($data_set['timeshift'])
				: 0;

			$colors = $data_set['color'];

			foreach ($items as $item) {
				$data_set['color'] = '#'.array_shift($colors);
				$metrics[] = $item + ['data_set' => $index, 'options' => $data_set];
				$max_metrics--;
			}
		}
	}

	private static function sortByDataset(array &$metrics): void {
		usort($metrics, static function(array $a, array $b): int {
			return $a['data_set'] <=> $b['data_set'];
		});
	}

	/**
	 * Apply overrides for each metric.
	 */
	private static function applyOverrides(array &$metrics, string $templateid, string $dynamic_hostid,
			array $overrides = []): void {
		if ($templateid !== '' && $dynamic_hostid !== '') {
			$dynamic_host = API::Host()->get([
				'output' => ['name'],
				'hostids' => [$dynamic_hostid]
			]);
		}

		foreach ($overrides as $override) {
			if ($templateid === '' || $templateid !== '' && $dynamic_hostid === '') {
				if ($override['hosts'] === '' || $override['items'] === '') {
					continue;
				}
			}
			else {
				if ($override['items'] === '') {
					continue;
				}
			}

			if ($templateid !== '' && $dynamic_hostid !== '') {
				$override['hosts'] = [$dynamic_host[0]['name']];
			}

			// Convert timeshift to seconds.
			if (array_key_exists('timeshift', $override)) {
				$override['timeshift'] = ($override['timeshift'] !== '')
					? (int) timeUnitToSeconds($override['timeshift'])
					: 0;
			}

			$hosts_patterns = self::processPattern($override['hosts']);
			$items_patterns = self::processPattern($override['items']);

			unset($override['hosts'], $override['items']);

			$metrics_matched = [];

			foreach ($metrics as $metric_num => $metric) {
				// If '*' used, apply options to all metrics.
				$host_matches = ($hosts_patterns === null);
				$item_matches = ($items_patterns === null);

				/**
				 * Find if host and item names matches one of given patterns.
				 *
				 * It currently checks if at least one of host pattern and at least one of item pattern matches,
				 * without checking relation between matching host and item.
				 */
				if ($hosts_patterns !== null) {
					for ($hosts_pattern = reset($hosts_patterns); !$host_matches && $hosts_pattern !== false;
						$hosts_pattern = next($hosts_patterns)) {
						$pattern = '/^'.str_replace('\*', '.*', preg_quote($hosts_pattern, '/')).'$/i';
						$host_matches = (bool) preg_match($pattern, $metric['hosts'][0]['name']);
					}
				}

				if ($items_patterns !== null && $host_matches) {
					for ($items_pattern = reset($items_patterns); !$item_matches && $items_pattern !== false;
						$items_pattern = next($items_patterns)) {
						$pattern = '/^'.str_replace('\*', '.*', preg_quote($items_pattern, '/')).'$/i';
						$item_matches = (bool) preg_match($pattern, $metric['name']);
					}
				}

				/**
				 * We need to know total amount of matched metrics to calculate variations of colors. That's why we
				 * first collect matching metrics and then override existing metric options.
				 */
				if ($host_matches && $item_matches) {
					$metrics_matched[] = $metric_num;
				}
			}

			// Apply override options to matching metrics.
			if ($metrics_matched) {
				$colors = (array_key_exists('color', $override) && $override['color'] !== '')
					? getColorVariations('#'.$override['color'], count($metrics_matched))
					: null;

				if (array_key_exists('transparency', $override)) {
					// The bigger transparency level, the less visible the metric is.
					$override['transparency'] = 10 - (int) $override['transparency'];
				}

				foreach ($metrics_matched as $metric_num) {
					$metric = &$metrics[$metric_num];

					if ($colors !== null) {
						$override['color'] = array_shift($colors);
					}
					$metric['options'] = $override + $metric['options'];

					// Fix missing options if draw type has changed.
					switch ($metric['options']['type']) {
						case SVG_GRAPH_TYPE_POINTS:
							if (!array_key_exists('pointsize', $metric['options'])) {
								$metric['options']['pointsize'] = SVG_GRAPH_DEFAULT_POINTSIZE;
							}
							break;

						case SVG_GRAPH_TYPE_LINE:
						case SVG_GRAPH_TYPE_STAIRCASE:
							if (!array_key_exists('fill', $metric['options'])) {
								$metric['options']['fill'] = SVG_GRAPH_DEFAULT_FILL;
							}
							if (!array_key_exists('missingdatafunc', $metric['options'])) {
								$metric['options']['missingdatafunc'] = SVG_GRAPH_MISSING_DATA_NONE;
							}
							if (!array_key_exists('width', $metric['options'])) {
								$metric['options']['width'] = SVG_GRAPH_DEFAULT_WIDTH;
							}
							break;
					}
				}
				unset($metric);
			}
		}
	}

	/**
	 * Apply static units for each metric if selected.
	 */
	private static function applyUnits(array &$metrics, array $axis_options): void {
		foreach ($metrics as &$metric) {
			if ($metric['options']['axisy'] == GRAPH_YAXIS_SIDE_LEFT && $axis_options['left_y_units'] !== null) {
				$metric['units'] = trim(preg_replace('/\s+/', ' ', $axis_options['left_y_units']));
			}
			elseif ($metric['options']['axisy'] == GRAPH_YAXIS_SIDE_RIGHT && $axis_options['right_y_units'] !== null) {
				$metric['units'] = trim(preg_replace('/\s+/', ' ', $axis_options['right_y_units']));
			}
		}
		unset($metric);
	}

	/**
	 * Apply time period for each metric.
	 */
	private static function getTimePeriods(array &$metrics, array $options): void {
		foreach ($metrics as &$metric) {
			$metric['time_period'] = $options;

			if ($metric['options']['timeshift'] != 0) {
				$metric['time_period']['time_from'] += $metric['options']['timeshift'];
				$metric['time_period']['time_to'] += $metric['options']['timeshift'];
			}
		}
		unset($metric);
	}

	/**
	 * Calculate what data source must be used for each metric.
	 */
	private static function getGraphDataSource(array &$metrics, array &$errors, int $data_source, int $width): void {
		/**
		 * If data source is not specified, calculate it automatically. Otherwise, set given $data_source to each
		 * $metric.
		 */
		if ($data_source == SVG_GRAPH_DATA_SOURCE_AUTO) {
			/**
			 * First, if global configuration setting "Override item history period" is enabled, override globally
			 * specified "Data storage period" value to each metric custom history storage duration, converting it
			 * to seconds. If "Override item history period" is disabled, item level field 'history' will be used
			 * later, but now we are just storing the field name 'history' in array $to_resolve.
			 *
			 * Do the same with trends.
			 */
			$to_resolve = [];

			if (CHousekeepingHelper::get(CHousekeepingHelper::HK_HISTORY_GLOBAL)) {
				foreach ($metrics as &$metric) {
					$metric['history'] = timeUnitToSeconds(CHousekeepingHelper::get(CHousekeepingHelper::HK_HISTORY));
				}
				unset($metric);
			}
			else {
				$to_resolve[] = 'history';
			}

			if (CHousekeepingHelper::get(CHousekeepingHelper::HK_TRENDS_GLOBAL)) {
				foreach ($metrics as &$metric) {
					$metric['trends'] = timeUnitToSeconds(CHousekeepingHelper::get(CHousekeepingHelper::HK_TRENDS));
				}
				unset($metric);
			}
			else {
				$to_resolve[] = 'trends';
			}

			// If no global history and trend override enabled, resolve 'history' and/or 'trends' values for given $metric.
			if ($to_resolve) {
				$metrics = CMacrosResolverHelper::resolveTimeUnitMacros($metrics, $to_resolve);
				$simple_interval_parser = new CSimpleIntervalParser();

				foreach ($metrics as $num => &$metric) {
					// Convert its values to seconds.
					if (!CHousekeepingHelper::get(CHousekeepingHelper::HK_HISTORY_GLOBAL)) {
						if ($simple_interval_parser->parse($metric['history']) != CParser::PARSE_SUCCESS) {
							$errors[] = _s('Incorrect value for field "%1$s": %2$s.', 'history',
								_('invalid history storage period')
							);
							unset($metrics[$num]);
						}
						else {
							$metric['history'] = timeUnitToSeconds($metric['history']);
						}
					}

					if (!CHousekeepingHelper::get(CHousekeepingHelper::HK_TRENDS_GLOBAL)) {
						if ($simple_interval_parser->parse($metric['trends']) != CParser::PARSE_SUCCESS) {
							$errors[] = _s('Incorrect value for field "%1$s": %2$s.', 'trends',
								_('invalid trend storage period')
							);
							unset($metrics[$num]);
						}
						else {
							$metric['trends'] = timeUnitToSeconds($metric['trends']);
						}
					}
				}
				unset($metric);
			}

			foreach ($metrics as &$metric) {
				/**
				 * History as a data source is used in 2 cases:
				 * 1) if trends are disabled (set to 0) either for particular $metric item or globally;
				 * 2) if period for requested data is newer than the period of keeping history for particular $metric
				 *    item.
				 *
				 * Use trends otherwise.
				 */
				$history = $metric['history'];
				$trends = $metric['trends'];
				$time_from = $metric['time_period']['time_from'];
				$period = $metric['time_period']['time_to'] - $time_from;

				$metric['source'] = ($trends == 0 || (time() - $history < $time_from
						&& $period / $width <= ZBX_MAX_TREND_DIFF / ZBX_GRAPH_MAX_SKIP_CELL))
					? SVG_GRAPH_DATA_SOURCE_HISTORY
					: SVG_GRAPH_DATA_SOURCE_TRENDS;
			}
		}
		else {
			foreach ($metrics as &$metric) {
				$metric['source'] = $data_source;
			}
		}

		unset($metric);
	}

	/**
	 * Select data to show in graph for each metric.
	 */
	private static function getMetricsData(array &$metrics, int $width): void {
		// To reduce number of requests, group metrics by time range.
		$tr_groups = [];

		foreach ($metrics as $index => &$metric) {
			if ($metric['options']['aggregate_function'] != AGGREGATE_NONE) {
				continue;
			}

			$metric['name'] = $metric['hosts'][0]['name'].NAME_DELIMITER.$metric['name'];
			$metric['points'] = [];

			$key = $metric['time_period']['time_from'].$metric['time_period']['time_to'];
			if (!array_key_exists($key, $tr_groups)) {
				$tr_groups[$key] = [
					'time' => [
						'from' => $metric['time_period']['time_from'],
						'to' => $metric['time_period']['time_to']
					]
				];
			}

			$tr_groups[$key]['items'][$index] = [
				'itemid' => $metric['itemid'],
				'value_type' => $metric['value_type'],
				'source' => ($metric['source'] == SVG_GRAPH_DATA_SOURCE_HISTORY) ? 'history' : 'trends'
			];
		}
		unset($metric);

		// Request data.
		foreach ($tr_groups as $tr_group) {
			$results = Manager::History()->getGraphAggregationByWidth($tr_group['items'], $tr_group['time']['from'],
				$tr_group['time']['to'], $width
			);

			if ($results) {
				foreach ($tr_group['items'] as $index => $item) {
					$metric = &$metrics[$index];

					// Collect and sort data points.
					if (array_key_exists($item['itemid'], $results)) {
						foreach ($results[$item['itemid']]['data'] as $point) {
							$metric['points'][$point['clock']] = [
								'min' => $point['min'],
								'avg' => $point['avg'],
								'max' => $point['max']
							];
						}
						ksort($metric['points'], SORT_NUMERIC);

						unset($metric['source'], $metric['history'], $metric['trends']);
					}
				}
				unset($metric);
			}
		}
	}

	/**
	 * Select aggregated data to show in graph for each metric.
	 */
	private static function getMetricsAggregatedData(array &$metrics, int $width, array $data_sets): void {
		$dataset_metrics = [];

		foreach ($metrics as $metric_num => &$metric) {
			if ($metric['options']['aggregate_function'] == AGGREGATE_NONE) {
				continue;
			}

			$dataset_num = $metric['data_set'];

			if ($metric['options']['aggregate_grouping'] == GRAPH_AGGREGATE_BY_ITEM) {
				$name = item_aggr_fnc2str($metric['options']['aggregate_function']).
					'('.$metric['hosts'][0]['name'].NAME_DELIMITER.$metric['name'].')';
			}
			else {
				$name = $data_sets[$dataset_num]['data_set_label'] !== ''
					? $data_sets[$dataset_num]['data_set_label']
					: _('Data set').' #'.($dataset_num + 1);
			}

			$item = [
				'itemid' => $metric['itemid'],
				'value_type' => $metric['value_type'],
				'source' => ($metric['source'] == SVG_GRAPH_DATA_SOURCE_HISTORY) ? 'history' : 'trends'
			];

			if (!array_key_exists($dataset_num, $dataset_metrics)) {
				$metric = array_merge($metric, [
					'name' => $name,
					'items' => [],
					'points' => []
				]);

				$aggregate_interval = timeUnitToSeconds($metric['options']['aggregate_interval'], true);

				if ($aggregate_interval === null || $aggregate_interval < 1
						|| $aggregate_interval > ZBX_MAX_TIMESHIFT) {
					continue;
				}

				$metric['options']['aggregate_interval'] = (int) $aggregate_interval;

				if ($metric['options']['aggregate_grouping'] == GRAPH_AGGREGATE_BY_DATASET) {
					$dataset_metrics[$dataset_num] = $metric_num;
				}

				$metric['items'][] = $item;
			}
			else {
				$metrics[$dataset_metrics[$dataset_num]]['items'][] = $item;
				unset($metrics[$metric_num]);
			}
		}
		unset($metric);

		foreach ($metrics as &$metric) {
			if ($metric['options']['aggregate_function'] == AGGREGATE_NONE) {
				continue;
			}

			if (!$metric['items']) {
				continue;
			}

			$result = Manager::History()->getAggregationByInterval(
				$metric['items'], $metric['time_period']['time_from'], $metric['time_period']['time_to'],
				$metric['options']['aggregate_function'], $metric['options']['aggregate_interval']
			);

			if ($result) {
				$metric_points = [];

				$period = $metric['time_period']['time_to'] - $metric['time_period']['time_from'];
				$approximation_tick_delta = ($period / $metric['options']['aggregate_interval']) > $width
					? ceil($period / $width)
					: 0;

				foreach ($result as $points) {
					$tick = 0;

					usort($points['data'],
						static function (array $point_a, array $point_b): int {
							return $point_a['clock'] <=> $point_b['clock'];
						}
					);

					foreach ($points['data'] as $point) {
						if ($point['tick'] > ($tick + $approximation_tick_delta)) {
							$tick = $point['tick'];
						}
						if (array_key_exists('count', $point)) {
							$metric_points[$tick]['value'][] = $point['count'];
						}
						if (array_key_exists('value', $point)) {
							$metric_points[$tick]['value'][] = $point['value'];
						}
					}
				}

				foreach ($metric_points as $tick => $point) {
					$metric['points'][$tick] = [
						'min' => min($point['value']),
						'avg' => CMathHelper::safeAvg($point['value']),
						'max' => max($point['value'])
					];
				}

				ksort($metric['points'], SORT_NUMERIC);
			}
		}
	}

	private static function getLegend(array $metrics, array $legend_options): ?CSvgGraphLegend {
		if ($legend_options['show_legend'] != SVG_GRAPH_LEGEND_ON) {
			return null;
		}

		$items = [];

		foreach ($metrics as $metric) {
			$item = [
				'name' => $metric['name'],
				'color' => $metric['options']['color']
			];

			if ($metric['points']) {
				switch ($metric['options']['approximation']) {
					case APPROXIMATION_MIN:
						$values = array_column($metric['points'], 'min');
						break;
					case APPROXIMATION_MAX:
						$values = array_column($metric['points'], 'max');
						break;
					default:
						$values = array_column($metric['points'], 'avg');
				}

				$item += [
					'units' => $metric['units'],
					'min' => min($values),
					'avg' => array_sum($values) / count($values),
					'max' => max($values)
				];
			}

			$items[] = $item;
		}

		return (new CSvgGraphLegend($items))
			->setColumnsCount($legend_options['legend_columns'])
			->setLinesCount($legend_options['legend_lines'])
			->showStatistic($legend_options['legend_statistic']);
	}

	/**
	 * @throws Exception
	 */
	private static function getSimpleTriggers(array $metrics, $displaying_options): array {
		if ($displaying_options['show_simple_triggers'] != SVG_GRAPH_SIMPLE_TRIGGERS_ON) {
			return [];
		}

		$number_parser = new CNumberParser(['with_size_suffix' => true, 'with_time_suffix' => true]);
		$simple_triggers = [];
		$limit = 3;

		foreach ($metrics as &$metric) {
			if ($metric['options']['stacked'] == SVG_GRAPH_STACKED_ON) {
				continue;
			}

			$db_triggers = DBselect(
				'SELECT DISTINCT h.host,tr.description,tr.triggerid,tr.expression,tr.priority,tr.value'.
				' FROM triggers tr,functions f,items i,hosts h'.
				' WHERE tr.triggerid=f.triggerid'.
				" AND f.name IN ('last','min','avg','max')".
				' AND tr.status='.TRIGGER_STATUS_ENABLED.
				' AND i.itemid=f.itemid'.
				' AND h.hostid=i.hostid'.
				' AND f.itemid='.zbx_dbstr($metric['itemid']).
				' ORDER BY tr.priority'
			);

			while ($limit && ($trigger = DBfetch($db_triggers))) {
				$functions_count = DBfetch(DBselect(
					'SELECT COUNT(*) AS cnt'.
					' FROM functions f'.
					' WHERE f.triggerid='.zbx_dbstr($trigger['triggerid'])
				));

				if ($functions_count['cnt'] != 1) {
					continue;
				}

				$trigger['expression'] = CMacrosResolverHelper::resolveTriggerExpressions([$trigger],
					['resolve_usermacros' => true, 'resolve_functionids' => false]
				)[0]['expression'];

				if (!preg_match('/^\{\d+\}\s*(?<operator>[><]=?|=)\s*(?<constant>.*)$/', $trigger['expression'],
					$matches)) {
					continue;
				}

				if ($number_parser->parse($matches['constant']) != CParser::PARSE_SUCCESS) {
					continue;
				}

				$simple_triggers[] = [
					'axisy' => $metric['options']['axisy'],
					'value' => $number_parser->calcValue(),
					'color' => CSeverityHelper::getColor((int) $trigger['priority']),
					'description' => _('Trigger').NAME_DELIMITER.CMacrosResolverHelper::resolveTriggerName($trigger),
					'constant' => $matches['operator'].' '.$matches['constant']
				];

				$limit--;
			}
		}

		return $simple_triggers;
	}

	/**
	 * Find problems at given time period that matches specified problem options.
	 */
	private static function getProblems(array $metrics, array $problem_options, array $time_period, string $templateid,
			string $dynamic_hostid): array {
		if ($problem_options['show_problems'] == SVG_GRAPH_PROBLEMS_OFF) {
			return [];
		}

		if ($templateid !== '' && $dynamic_hostid !== '') {
			$dynamic_host = API::Host()->get([
				'output' => ['name'],
				'hostids' => [$dynamic_hostid]
			]);
			$problem_options['problemhosts'] = [$dynamic_host[0]['name']];
		}

		$options = [
			'output' => ['eventid', 'objectid', 'name', 'severity', 'clock', 'r_eventid'],
			'selectAcknowledges' => ['action'],
			'problem_time_from' => $time_period['time_from'],
			'problem_time_till' => $time_period['time_to'],
			'symptom' => false,
			'preservekeys' => true
		];

		// Find triggers involved.
		if ($problem_options['problemhosts'] !== '') {
			$options['hostids'] = array_keys(API::Host()->get([
				'output' => [],
				'searchWildcardsEnabled' => true,
				'searchByAny' => true,
				'search' => [
					'name' => self::processPattern($problem_options['problemhosts'])
				],
				'preservekeys' => true
			]));

			// Return if no hosts found.
			if (!$options['hostids']) {
				return [];
			}
		}

		if ($problem_options['graph_item_problems'] == SVG_GRAPH_SELECTED_ITEM_PROBLEMS) {
			$itemids = [];

			foreach ($metrics as $metric) {
				$itemids += $metric['options']['aggregate_function'] != AGGREGATE_NONE
					? array_column($metric['items'], 'itemid', 'itemid')
					: [$metric['itemid'] => $metric['itemid']];
			}
			$itemids = array_values($itemids);
		}
		else {
			$itemids = null;
		}

		$options['objectids'] = array_keys(API::Trigger()->get([
			'output' => [],
			'hostids' => $options['hostids'] ?? null,
			'itemids' => $itemids,
			'monitored' => true,
			'preservekeys' => true
		]));

		unset($options['hostids']);

		// Return if no triggers found.
		if (!$options['objectids']) {
			return [];
		}

		// Add severity filter.
		if ($problem_options['severities']) {
			$options['severities'] = $problem_options['severities'];
		}

		// Add problem name filter.
		if ($problem_options['problem_name'] !== '') {
			$options['searchWildcardsEnabled'] = true;
			$options['search']['name'] = $problem_options['problem_name'];
		}

		// Add tags filter.
		if ($problem_options['tags']) {
			$options['evaltype'] = $problem_options['evaltype'];
			$options['tags'] = $problem_options['tags'];
		}

		$events = API::Event()->get($options);

		// Find end time for each problem.
		if ($events) {
			$r_events = API::Event()->get([
				'output' => ['clock'],
				'eventids' => array_column($events, 'r_eventid'),
				'preservekeys' => true
			]);

			// Add recovery times for each problem event.
			foreach ($events as &$event) {
				$event['r_clock'] = array_key_exists($event['r_eventid'], $r_events)
					? $r_events[$event['r_eventid']]['clock']
					: 0;
			}
			unset($event);
		}

		CArrayHelper::sort($events, [['field' => 'clock', 'order' => ZBX_SORT_DOWN]]);

		return $events;
	}

	/**
	 * Prepare an array to be used for hosts/items filtering.
	 *
	 * @param array  $patterns  Array of strings containing hosts/items patterns.
	 *
	 * @return array|mixed  Returns array of patterns.
	 *                      Returns NULL if array contains '*' (so any possible host/item search matches).
	 */
	private static function processPattern(array $patterns): ?array {
		return in_array('*', $patterns, true) ? null : $patterns;
	}

	/**
	 * Sort data points by clock field.
	 * Do not use this function directly. It serves as value_compare_func function for usort.
	 */
	private static function sortByClock(array $a, array $b): int {
		if ($a['clock'] == $b['clock']) {
			return 0;
		}

		return ($a['clock'] < $b['clock']) ? -1 : 1;
	}
}
