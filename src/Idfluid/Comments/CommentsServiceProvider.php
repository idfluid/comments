<?php namespace Idfluid\Comments;

use Illuminate\Support\ServiceProvider;

class CommentsServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('idfluid/comments');
		include __DIR__ . '/../../routes.php';
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$app = $this->app;

		$app['comments'] = $app->share(function($app){
		    return new Comments($app['config']->get('comments::config'));
		});

		$app->booting(function(){
	  		$loader = \Illuminate\Foundation\AliasLoader::getInstance();
	  		$loader->alias('Comments', 'Idfluid\Comments\Facades\Comments');
		});
	}
	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('comments');
	}

}
