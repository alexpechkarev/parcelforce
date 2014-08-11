<?php namespace Parcelforce\ExpressTransfer;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class ParcelforceServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
            $this->app['Parcelforce'] = $this->app->share(function($app)
            {                    
                return new Parcelforce(Config::get('parcelforce::config'));
            }); 
	}
        
        
        /**
         * Bootstrap the application events.
         *
         * @return void
         */                
         public function boot(){
            
            $this->package("parcelforce/expresstransfer");
            AliasLoader::getInstance()->alias('Parcelforce','Parcelforce\ExpressTransfer\Facade\ParcelforceFacade');            
            
        }         

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
