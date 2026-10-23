<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No student accounts exist, so a subscriber is just a confirmed email and/or phone.
        Schema::create('cns_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable()->unique();
            $table->dateTime('email_confirmed_at')->nullable();
            $table->string('phone', 20)->nullable()->unique();
            $table->dateTime('phone_confirmed_at')->nullable();
            $table->dateTime('whatsapp_opt_in_at')->nullable();          // explicit opt-in, recorded when the phone is verified
            $table->string('phone_code_hash', 64)->nullable();
            $table->dateTime('phone_code_expires_at')->nullable();
            $table->dateTime('phone_code_sent_at')->nullable();
            $table->unsignedTinyInteger('phone_code_attempts')->default(0);
            $table->unsignedSmallInteger('token_version')->default(1);   // bump to revoke every inbox link of this subscriber
            $table->timestamps();
        });

        // One row per followed counselling / exam, with a switch per channel (also the per-channel unsubscribe).
        Schema::create('cns_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained('cns_subscribers')->cascadeOnDelete();
            $table->enum('target_type', ['counselling', 'exam']);
            $table->unsignedBigInteger('target_id');
            $table->boolean('via_email')->default(false);
            $table->boolean('via_whatsapp')->default(false);
            $table->boolean('via_inapp')->default(false);
            $table->json('alert_types')->nullable();                      // null = every kind
            $table->timestamps();
            $table->unique(['subscriber_id', 'target_type', 'target_id'], 'cns_subscriptions_unique');
            $table->index(['target_type', 'target_id']);
        });

        Schema::create('cns_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 30);
            $table->enum('target_type', ['counselling', 'exam']);
            $table->unsignedBigInteger('target_id');
            $table->string('subject_key')->unique();                      // what makes an alert "the same one": never sent twice
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('link_url', 500)->nullable();
            $table->string('source_url', 500)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['target_type', 'target_id']);
        });

        Schema::create('cns_alert_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained('cns_alerts')->cascadeOnDelete();
            $table->foreignId('subscriber_id')->constrained('cns_subscribers')->cascadeOnDelete();
            $table->enum('channel', ['email', 'whatsapp', 'inapp']);
            $table->enum('status', ['pending', 'sent', 'failed', 'skipped'])->default('pending');
            $table->string('error', 500)->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('read_at')->nullable();                      // in-app inbox
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['alert_id', 'subscriber_id', 'channel'], 'cns_deliveries_unique');
            $table->index(['status', 'channel']);
            $table->index(['subscriber_id', 'channel']);
        });

        // Last state the scanner has seen, to detect "date changed" and "postponed" without guessing.
        Schema::create('cns_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type', 30);
            $table->unsignedBigInteger('subject_id');
            $table->json('state');
            $table->timestamp('updated_at')->useCurrent();
            $table->unique(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        foreach (['cns_snapshots', 'cns_alert_deliveries', 'cns_alerts', 'cns_subscriptions', 'cns_subscribers'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
