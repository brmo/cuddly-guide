<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfc_performers', function (Blueprint $table) {
            $table->foreignId('rfc_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('reminder_minutes')->default(30);
            $table->timestamps();

            $table->primary(['rfc_id', 'user_id']);
        });

        Schema::create('rfc_stakeholders', function (Blueprint $table) {
            $table->foreignId('rfc_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['rfc_id', 'user_id']);
        });

        Schema::create('rfc_approvers', function (Blueprint $table) {
            $table->foreignId('rfc_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['mandatory', 'optional', 'backup'])->default('optional');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('comments')->nullable();
            $table->integer('reminder_minutes')->default(30);
            $table->timestamps();

            $table->primary(['rfc_id', 'user_id']);
            $table->index(['rfc_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfc_approvers');
        Schema::dropIfExists('rfc_stakeholders');
        Schema::dropIfExists('rfc_performers');
    }
};
