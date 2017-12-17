<?php namespace adman9000\cryptopia;

/**
 * @author  adman9000
 */
use Illuminate\Support\ServiceProvider;

class CryptopiaServiceProvider extends ServiceProvider {

	public function boot() 
	{
		$this->publishes([
			__DIR__.'/../config/cryptopia.php' => config_path('cryptopia.php')
		]);
	} // boot

	public function register() 
	{
		$this->mergeConfigFrom(__DIR__.'/../config/cryptopia.php', 'cryptopia');
		$this->app->bind('cryptopia', function() {
			return new CryptopiaAPI(config('cryptopia'));
		});

		

	} // register
}