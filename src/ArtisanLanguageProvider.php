<?php 

namespace Bestmomo\ArtisanLanguage;

use Illuminate\Support\ServiceProvider;

class ArtisanLanguageProvider extends ServiceProvider
{

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

	}

	/**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @return void
	 */
	public function boot()
	{
	    if ($this->app->runningInConsole()) {
	        $this->commands([
	            Commands\LanguageMake::class,
	            Commands\LanguageDiff::class,
	            Commands\LanguageStrings::class,
	            Commands\LanguageSync::class,
	        ]);
	    }
	}
}
