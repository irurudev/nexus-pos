<?php

namespace App\Providers;

use App\Models\Barang;
use App\Models\Pelanggan;
use App\Policies\BarangPolicy;
use App\Policies\PelangganPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Barang::class => BarangPolicy::class,
        Pelanggan::class => PelangganPolicy::class,
        \App\Models\Kategori::class => \App\Policies\KategoriPolicy::class,
        \App\Models\Penjualan::class => \App\Policies\PenjualanPolicy::class,
        \App\Models\AuditLog::class => \App\Policies\AuditLogPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
