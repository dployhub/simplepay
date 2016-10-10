<?php
namespace Dploy\Simplepay;

use Dploy\Simplepay\Simplepay;
use Illuminate\Support\ServiceProvider;

class SimplepayServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Route
        $path = 'simplepay.php';
        $this->publishes([
            __DIR__.'/Config/simplepay.php' => app()->basePath() . '/config' . ($path ? '/' . $path : $path),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom( __DIR__.'/Config/simplepay.php', 'simplepay');
        $config = config('simplepay');
        $this->app->singleton('simplepay', function () use ($config) {
            return new Simplepay($config);
        });
    }
}
?>
