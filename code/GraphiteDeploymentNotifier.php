<?php

/**
 * Simple notifier based on a static method call.
 * In all likelihood this would get refactored in the future if we add other notifiers.
 * To do so now would be overengineering. ;-)
 */
class GraphiteDeploymentNotifier extends DataExtension {
	/**
	 * Config set in the .yml file
	 */
	private static $graphite_host = null;
	private static $graphite_port = null;

	/**
	 * Notify Graphite of the start of a deployment.
	 *
	 * @param  string $environment Environment, in the form "project:environment"
	 * @param  string $buildname   Name of the build
	 * @param  string $buildFile   Filename of the build
	 * @return void
	 */
	public static function notify_start($environment, $buildname, $buildFile, DNProject $project) {
		$projectName = $project->Name;
		$graphiteKey = "deploys.$projectName.$environment.start";
		$graphiteValue = self::buildname_to_counter($buildname, $project);
		if(!$graphiteValue) $graphiteValue = 1;

		self::graphite_metric($graphiteKey, $graphiteValue);
	}

	/**
	 * Notify Graphite of the end of a deployment.
	 *
	 * @param  string $environment Environment, in the form "project:environment"
	 * @param  string $buildname   Name of the build
	 * @param  string $buildFile   Filename of the build
	 * @return void
	 */
	public static function notify_end($environment, $buildname, $buildFile, DNProject $project) {
		$projectName = $project->Name;
		$graphiteKey = "deploys.$projectName.$environment.end";
		$graphiteValue = self::buildname_to_counter($buildname, $project);
		if(!$graphiteValue) $graphiteValue = 1;

		self::graphite_metric($graphiteKey, $graphiteValue);
	}

	/**
	 * Write the data to graphite
	 * @param  string $key       The graphite key - a series of string values namespaced by "."
	 * @param  float $value     The value - a number
	 * @param  int $timestamp The timestampe of the metric - defaults to now.
	 * @return void
	 */
	protected static function graphite_metric($key, $value, $timestamp = null) {
		if($timestamp === null) $timestamp = time();

		$graphiteHost = Config::inst()->get(get_class(), 'graphite_host');
		$graphitePort = Config::inst()->get(get_class(), 'graphite_port');
		if($graphiteHost && $graphitePort) {
			$socket = fsockopen($graphiteHost, $graphitePort);
			if($socket !== false) {
				fwrite($socket, "$key $value $timestamp\n");
				fclose($socket);
			} else {
				echo "Unable to connect to {$graphiteHost}:{$graphitePort}\n";
			}
		} else {
			echo "Graphite called but not configured: '$key $value $timestamp'\n";
		}
	}

	/**
	 * Return a numerical counter for the given build.
	 */
	protected static function buildname_to_counter($buildname, DNProject $project) {
		$builds = $project->DNBuildList()->reverse();
		$counter = 1;
		foreach($builds as $build) {
			if($build->FullName() == $buildname) return $counter;
			$counter++;
		}
	}

	public static function deployStart($args) {
		self::notify_start($args['environment'], $args['sha'], null, $args['project']);
	}

	public static function deployEnd($args) {
		self::notify_end($args['environment'], $args['sha'], null, $args['project']);
	}
}
