<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Payment sheets uploaded by admins / data entry operators. Each accepted
     * sheet submits the next instalment of every listed member for admin
     * confirmation. A sheet is accepted only if every row is valid.
     */
    public function up(): void
    {
        Schema::create('instalment_upload_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('original_file_name');
            $table->string('stored_path')->nullable();          // copy of the sheet (private disk)
            $table->unsignedInteger('rows_count');
            $table->decimal('total_amount', 14, 2);
            $table->timestamps();
        });

        Schema::create('instalment_upload_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('instalment_upload_batches')->cascadeOnDelete();
            $table->unsignedInteger('row_number');               // row in the sheet (header = row 1)
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('saving_instalment_id')->constrained('saving_instalments');
            $table->string('username', 255);
            $table->string('email', 255)->nullable();
            $table->string('txn_id', 100)->nullable();           // as given in the sheet
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->index('saving_instalment_id');
        });

        // The existing (unused) 'operator' role is the Data Entry Operator.
        Role::findOrCreate('operator', 'web');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('instalment_upload_rows');
        Schema::dropIfExists('instalment_upload_batches');
    }
};
