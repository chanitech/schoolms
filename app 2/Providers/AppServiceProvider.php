<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middlewares\RoleMiddleware;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Student;
use App\Models\Staff;
use App\Models\User;


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
       // Relation::enforceMorphMap([
       // 'user' => User::class,     // 👈 add this line
        //'student' => Student::class,
        //'staff' => Staff::class,
    //]);
    }
}
