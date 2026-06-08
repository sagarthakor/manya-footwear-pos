<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('sale_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->date('return_date');
            $table->decimal('return_amount', 12, 2);
            $table->enum('return_type', ['refund','exchange'])->default('refund');
            $table->enum('refund_method', ['cash','upi','store_credit'])->default('cash');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('sale_returns'); }
};
