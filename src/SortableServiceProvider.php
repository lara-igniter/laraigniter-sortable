<?php

namespace Laraigniter\Sortable;

use Elegant\Contracts\Hook\PostControllerConstructor;
use Elegant\Support\Facades\Blade;
use Elegant\Support\ServiceProvider;

class SortableServiceProvider extends ServiceProvider implements PostControllerConstructor
{
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
}

