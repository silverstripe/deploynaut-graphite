<?php

/**
 * An extension for DNEnvrionment that adds Graphite servers
 */
class GraphiteEnvironmentExtension extends DataExtension {
	public static $db = array(
		'GraphiteServers' => 'Text',
	);

	/**
	 * Does this environment have a graphite server configuration
	 *
	 * @return GraphiteList
	 */
	public function HasMetrics() {
		return trim($this->owner->GraphiteServers) != "";
	}

	/**
	 * All graphite graphs
	 *
	 * @return GraphiteList
	 */
	public function Graphs() {
		if (!$this->HasMetrics()) {
			return null;
		}
		$serverList = preg_split('/\s+/', trim($this->owner->GraphiteServers));
		return new GraphiteList($serverList);
	}

	/**
	 * Graphs, grouped by server
	 *
	 * @todo refactor out the hardcoded aa exception
	 *
	 * @return ArrayList
	 */
	public function GraphServers() {
		if (!$this->HasMetrics()) {
			return null;
		}
		$serverList = preg_split('/\s+/', trim($this->owner->GraphiteServers));
		$output = new ArrayList;
		foreach($serverList as $server) {
			// Hardcoded reference to db
			if(strpos($server,'nzaadb') !== false) {
				$metricList = array("Load average", "CPU Usage", "Memory Free", "Physical Memory Used", "Swapping");
			} else {
				$metricList = array("Apache", "Load average", "CPU Usage", "Memory Free", "Physical Memory Used", "Swapping");
			}

			$output->push(new ArrayData(array(
				'Server' => $server,
				'ServerName' => substr($server,strrpos($server,'.')+1),
				'Graphs' => new GraphiteList(array($server), $metricList),
			)));
		}
		return $output;
	}

	public function updateCMSFields(FieldList $fields) {
		// The Extra.GraphiteServers
		$graphiteServerField = $fields->fieldByName('Root.Main.GraphiteServers');
		$fields->removeByName('Root.Main.GraphiteServers');
		$graphiteServerField->setDescription(
			'Find the relevant graphite servers at '.
			'<a href="http://graphite.silverstripe.com/" target="_blank">graphite.silverstripe.com</a>'.
			' and enter them one per line, e.g. "server.wgtn.oscar"'
		);
		$fields->addFieldToTab('Root.Extra', $graphiteServerField);
		return $fields;
	}
}

