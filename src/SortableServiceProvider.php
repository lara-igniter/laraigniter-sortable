<?php

namespace Laraigniter\Sortable;

use Elegant\Contracts\Hook\PostControllerConstructor;
use Elegant\Support\Facades\Blade;
use Elegant\Support\ServiceProvider;

class SortableServiceProvider extends ServiceProvider implements PostControllerConstructor
{
    /**
     * Bootstrap package services.
     *
     * Mirrors PaginationServiceProvider::postControllerConstructor():
     *   – registers the package config as a fallback (app-level config/sortable.php wins)
     *   – registers the config file for vendor:publish
     *
     * @param array $params
     * @return void
     */
    public function postControllerConstructor(&$params): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/sortable.php',
            'sortable'
        );

        $this->publishes([
            __DIR__ . '/../config/sortable.php' => base_path('config/sortable.php'),
        ], 'sortable');

        Blade::directive('sortable', function ($expression) {
            $expression = ($expression[0] === '(') ? substr($expression, 1, -1) : $expression;

            return "<?php echo \Laraigniter\Sortable\SortableLink::render([{$expression}]);?>";
        });
    }

    // -------------------------------------------------------------------------
    // Config
    // -------------------------------------------------------------------------

    /**
     * Register a configuration file as package defaults, with app-level override support.
     *
     * Mirrors Laravel's ServiceProvider::mergeConfigFrom() behaviour adapted for
     * the Laraigniter / CodeIgniter config loading mechanism:
     *
     *   1. The package root (parent of the config/ directory) is prepended to
     *      CI's $_config_paths array so that it is searched before FCPATH.
     *   2. CI's lazy loader (invoked on the first config('sortable.xxx') call)
     *      finds the package file first and stores it under $ci->config[$key].
     *   3. If an app-level config/{key}.php exists (FCPATH), it is loaded next
     *      and merged on top via array_merge — app values always win.
     *   4. If no app-level override exists the package defaults are used as-is,
     *      and CI's loader returns TRUE without an error.
     *
     * Copying config/sortable.php into the app's config/ folder is sufficient
     * to override any package default.
     *
     * @param string $path Absolute path to the package config FILE (not the directory).
     * @param string $key  Config key — must match the filename (without .php).
     * @return void
     */
    protected function mergeConfigFrom(string $path, string $key): void
    {
        $ci = app('config');

        // Resolve the package root: the directory that contains the config/ folder.
        // CI searches for:  {root}config/{key}.php
        $packageRoot = realpath(dirname($path) . '/..') . DIRECTORY_SEPARATOR;

        if (!in_array($packageRoot, $ci->_config_paths, true)) {
            array_unshift($ci->_config_paths, $packageRoot);
        }
    }
}

