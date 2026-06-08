<?php

namespace App\Providers;

use App\Helpers\Module;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // @module('key') ... @endmodule  — shows block only if module is enabled
        Blade::if('module', function (string $key) {
            return Module::isActive($key);
        });
    }
}