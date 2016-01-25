<?php

namespace ACPClient;

use Illuminate\Support\ServiceProvider;

class ACPClientServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Boot the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		#
		# Setup config publishing
		#
        $configPath = config_path('acpclient.php');

        $this->publishes([
            __DIR__.'/config/acpclient.php' => $configPath,
        ], 'config');

        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'acpclient');
        }
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		#
		# Initialise ACP REST client
		#
        $this->app->singleton('ACPClient\RESTClient', function ($app) {
            return new RESTClient(config('acpclient.api'));
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

}
