# Configuration

In your main repository, `mysite/_config/graphite.yml` (or similiar name), do the configuration

## Main Configuration

	GraphiteDeploymentNotifier:
	  graphite_host: localhost
	  graphite_port: 2003
	GraphiteProxy:
	  graphite_source: "http://graphite:2800/render"

When this is setup, deploynaut will send a start / end deploy message to a graphite server.
