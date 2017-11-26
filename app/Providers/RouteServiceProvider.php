<?php

namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
        //

        parent::boot($router);
        $this->reConfigRouteByEnv($router);
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function ($router) {
            require app_path('Http/Routes/web.v1.php');
            require app_path('Http/Routes/global.php');
            require app_path('Http/Routes/admin.web.v1.php');

        });
    }

    /**
     * 根据环境设置对应的信息.
     *
     * @param Router $router
     */
    private function reConfigRouteByEnv(Router $router)
    {
        //        if (config('app.debug') && in_array(app()->environment(), ['development', 'testing'])) {
        if (in_array(app()->environment(), ['development', 'testing', 'staging', 'production'])) { //TODO:for test
            $routeCollection = $router->getRoutes();
            $routes = $routeCollection->getRoutes();
            array_map(function ($route) use ($router) {
                $path = '/debug/'.$route->getPath();
                $action = $route->getAction();
                $action['middleware'] = 'cauth.develop';
                $action['prefix'] = '';
                $router->any($path, $action);
            }, $routes);
        }
    }
}
