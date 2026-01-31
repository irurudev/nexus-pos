<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\AuditObserver;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Pelanggan;
use App\Models\Penjualan;

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
        // Register audit observer for core models
        Barang::observe(AuditObserver::class);
        Kategori::observe(AuditObserver::class);
        Pelanggan::observe(AuditObserver::class);
        Penjualan::observe(AuditObserver::class);
    }
}
