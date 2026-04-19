<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saving_instalments', function (Blueprint $table) {
            // Date from which this instalment's amount should contribute to the ROI base.
            // For on-time/late payments this equals confirmed_at date.
            // For early payments (paid before due_date), this equals the due_date,
            // so ROI for that instalment only starts from its originally scheduled month.
            $table->date('roi_eligible_from')->nullable()->after('next_cycle_date');
        });
    }

    public function down(): void
    {
        Schema::table('saving_instalments', function (Blueprint $table) {
            $table->dropColumn('roi_eligible_from');
        });
    }
};
