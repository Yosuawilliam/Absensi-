<?php

namespace App\Providers;

use App\Models\Pengaturan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        View::composer('*', function ($view) {
            static $cache = null;

            if ($cache === null) {
                $cache = ['institusi' => 'Absensi', 'logoInstitusi' => null];
                try {
                    if (Schema::hasTable('pengaturans')) {
                        $cache['institusi'] = Pengaturan::ambil('nama_institusi');
                        $logo = Pengaturan::ambil('logo');
                        $cache['logoInstitusi'] = $logo ? asset($logo) : null;
                    }
                } catch (\Throwable) {
                    // Database belum siap; pakai nilai bawaan
                }
            }

            $view->with($cache);
        });
    }
}
