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
    Schema::create('companies', function (Blueprint $table) {

    $table->id();

    $table->string('name',150);
    $table->string('email')->nullable()->unique();
    $table->string('phone',20)->nullable();
    $table->string('website')->nullable();
    $table->string('logo')->nullable();
    $table->text('address')->nullable();

    $table->boolean('status')->default(true);

    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->foreignId('updated_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->softDeletes();
    $table->timestamps();
});
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
