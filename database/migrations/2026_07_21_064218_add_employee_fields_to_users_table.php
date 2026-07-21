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
    Schema::table('users', function (Blueprint $table) {

        $table->string('employee_id')
            ->unique()
            ->nullable()
            ->after('id');

        $table->string('designation')
            ->nullable()
            ->after('department_id');

        $table->string('phone')
            ->nullable()
            ->after('email');

        $table->enum('gender', [
            'Male',
            'Female',
            'Other'
        ])->nullable();

        $table->date('dob')
            ->nullable();

        $table->date('joining_date')
            ->nullable();

        $table->string('emergency_contact')
            ->nullable();

        $table->text('address')
            ->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('users', function (Blueprint $table) {

        $table->dropColumn([
            'employee_id',
            'designation',
            'phone',
            'gender',
            'dob',
            'joining_date',
            'emergency_contact',
            'address'
        ]);

    });
}
};
