<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('id');
        Paginator::defaultView('vendor.pagination.civic');
        Paginator::defaultSimpleView('vendor.pagination.civic');

        // Tangkap atribut yang tidak fillable saat pengembangan, alih-alih diabaikan diam-diam.
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
    }
}
