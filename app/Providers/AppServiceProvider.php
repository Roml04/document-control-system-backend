<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('index-comments', function (User $user) {
          return $user->role === "sysadmin";
        });

        Gate::define('update-role', function (User $user, User $model) {
          if($user->role !== "sysadmin" || $model->role === "sysadmin") {
            return Response::deny("You are not allowed to change this role");
          }

          return true;
        });
    }
}
