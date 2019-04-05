<?php

namespace Exdeliver\Causeway\ServiceProviders;

use Exdeliver\Causeway\Domain\Entities\Forum\Category;
use Exdeliver\Causeway\Domain\Entities\Forum\Thread;
use Exdeliver\Causeway\Domain\Entities\Page\Page;
use Exdeliver\Causeway\Domain\Entities\PhotoAlbum\PhotoAlbum;
use Exdeliver\Causeway\Domain\Services\CausewayService;
use Exdeliver\Causeway\Events\CausewayRegistered;
use Exdeliver\Causeway\Listeners\AccountVerificationNotification;
use Exdeliver\Causeway\Middleware\Admin;
use Exdeliver\Causeway\Middleware\CausewayAuth;
use Exdeliver\Causeway\ViewComposers\NavigationComposer;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Class CausewayServiceProvider
 * @package Exdeliver\Causeway\ServiceProviders
 */
class CausewayServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $namespace = 'Exdeliver\Causeway\Controllers';

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        CausewayRegistered::class => [
            AccountVerificationNotification::class . '@handle',
        ],
    ];

    /**
     *
     */
    public function boot()
    {
        View::composer(
            'layouts.partials._navigation', NavigationComposer::class
        );

        $this->getConfiguration();
        $this->getClassBindings();
        $this->getCommands();
        $this->getRoutes();
        $this->getEventListeners();
    }

    /**
     * Configuration
     */
    public function getConfiguration()
    {
        $this->registerHelpers();
        $packageRootDir = __DIR__ . '/../..';
        $packageWorkingDir = __DIR__ . '/..';

        $this->publishes([
            $packageRootDir . '/config/causeway.php' => config_path('causeway.php'),
        ]);

        $this->publishes([
            $packageRootDir . '/assets/compiled' => public_path('vendor/exdeliver/causeway'),
        ], 'public');

        $this->loadViewsFrom($packageWorkingDir . '/Views', 'causeway');
        $this->loadTranslationsFrom($packageWorkingDir . '/Lang', 'causeway');

        $this->loadMigrationsFrom($packageRootDir . '/database');
        $this->registerEloquentFactoriesFrom($packageRootDir . '/database/factories');
    }

    /**
     * Helpers file.
     */
    public function registerHelpers()
    {
        $packageWorkingDir = __DIR__ . '/..';
        // Load the helpers in app/Http/helpers.php
        if (file_exists($packageWorkingDir . '/Helpers/helpers.php')) {
            include_once($packageWorkingDir . '/Helpers/helpers.php');
        }
    }

    /**
     * Register factories.
     *
     * @param string $path
     * @return void
     */
    protected function registerEloquentFactoriesFrom($path)
    {
        $this->app->make(EloquentFactory::class)->load($path);
    }

    /**
     * Registered commands.
     */
    protected function getCommands()
    {
//        if ($this->app->runningInConsole()) {
//            $this->commands([
//                FooCommand::class,
//                BarCommand::class,
//            ]);
//        }
    }

    /**
     * Route model bindings etc.
     */
    protected function getRoutes()
    {
        $packageWorkingDir = __DIR__ . '/..';

        $this->routeModelBindings();

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group($packageWorkingDir . '/Routes/web.php');

        Route::middleware('api')
            ->namespace($this->namespace)
            ->group($packageWorkingDir . '/Routes/api.php');

        $this->loadRoutesFrom($packageWorkingDir . '/Routes/channels.php');
    }

    /**
     * Route model bindings.
     */
    protected function routeModelBindings()
    {
        Route::bind('photoAlbum', function ($value) {
            return PhotoAlbum::where('label', $value)->first();
        });

        Route::bind('forumCategory', function ($value) {
            return Category::where('slug', $value)->first();
        });

        Route::bind('forumThread', function ($value) {
            return Thread::where('slug', $value)->first();
        });

        Route::bind('pageSlug', function ($value) {
            return Page::whereTranslation('slug', $value)->first();
        });
    }

    /**
     * Register method
     */
    public function register()
    {
        $this->registerMiddleware();
    }

    /**
     * Registered middleware
     */
    protected function registerMiddleware()
    {
        $this->app['router']->aliasMiddleware('admin', Admin::class);
        $this->app['router']->aliasMiddleware('causewayAuth', CausewayAuth::class);
    }

    /**
     * Event listeners.
     */
    public function getEventListeners()
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    /**
     * Class bindings for facades services.
     */
    public function getClassBindings()
    {
        $this->app->bind('causewayservice', function () {
            return app(CausewayService::class);
        });
    }
}