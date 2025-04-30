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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone_number');
            $table->text('address')->nullable();
            $table->foreignId('package_id')->constrained('packages');
            $table->decimal('bill', 10, 2);
            $table->boolean('status')->default(true);

            $table->index('full_name');
            $table->index('email');
            $table->index('phone_number');
            // Removed address index as TEXT columns need length specification
            $table->index('package_id');
            $table->index('status');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
