<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('transaction_id')->nullable()->unique();
            $table->string('payment_gateway_response')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'successful', 'failed', 'refunded'])->default('pending');
            $table->enum('payment_status', ['pending', 'successful', 'failed', 'refunded'])->default('pending');
            $table->timestamp('donated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
