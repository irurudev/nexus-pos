<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penjualans', function (Blueprint $table) {
            $table->string('id_nota', 20)->primary();
            $table->dateTime('tgl');
            $table->string('kode_pelanggan', 20)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('pajak', 15, 2)->default(0);
            $table->decimal('total_akhir', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('kode_pelanggan')->references('id_pelanggan')->on('pelanggans')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};
