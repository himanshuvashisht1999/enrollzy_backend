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
        // Expert Slots
        Schema::create('expert_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_id')->constrained('experts')->onDelete('cascade');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_day')->nullable(); // Monday, Tuesday...
            $table->string('status')->default('available'); // available, booked, blocked
            $table->string('mode')->default('video'); // video, audio, chat
            $table->decimal('cost', 10, 2)->default(0);
            $table->timestamps();
        });

        // Bookings
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_id')->unique(); // distinct from id, for public reference
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('expert_id')->constrained('experts')->onDelete('cascade');
            $table->foreignId('slot_id')->constrained('expert_slots')->onDelete('cascade');
            $table->timestamp('booking_date')->useCurrent();
            $table->string('status')->default('pending'); // pending, confirmed, completed, cancelled, no-show
            $table->decimal('amount', 10, 2);
            $table->decimal('platform_fee', 10, 2);
            $table->decimal('expert_earning', 10, 2);
            $table->string('payment_status')->default('pending'); // pending, paid, refunded, failed
            $table->string('meeting_link')->nullable();
            $table->text('notes')->nullable(); // Session purpose
            $table->timestamps();
        });

        // Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->nullable();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('INR');
            $table->string('gateway')->default('razorpay'); // razorpay, stripe, etc
            $table->string('status')->default('pending'); // pending, success, failed
            $table->text('response_log')->nullable();
            $table->timestamps();
        });

        // Payouts
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_id')->constrained('experts')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending, processing, paid
            $table->string('reference_id')->nullable(); // Bank Transaction ID
            $table->string('payout_method')->default('bank_transfer');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->timestamps();
        });

        // Reviews
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('expert_id')->constrained('experts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('rating', 2, 1); // 4.5
            $table->text('comment')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('expert_slots');
    }
};
