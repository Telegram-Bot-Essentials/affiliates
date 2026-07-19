<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use TelegramBotEssentials\Affiliates\Models\Referral;
use TelegramBotEssentials\Billing\Models\Invoice;
use TelegramBotEssentials\Essence\Models\Bot;
use TelegramBotEssentials\Essence\Models\BotUser;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('affiliate_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Bot::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Referral::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(BotUser::class, 'recipient_bot_user_id')->constrained('bot_users')->cascadeOnDelete();
            $table->string('type');
            $table->foreignIdFor(Invoice::class)->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 65, 30);
            $table->string('status')->default('credited');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_transactions');
    }
};
