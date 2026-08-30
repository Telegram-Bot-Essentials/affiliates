<?php

namespace TelegramBotEssentials\Affiliates\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use TelegramBotEssentials\Billing\Models\Invoice;
use TelegramBotEssentials\Essence\Models\BotUser;

class AffiliateTransaction extends Model
{
    use BelongsToTenant;

    public const TYPE_PURCHASE_COMMISSION = 'purchase_commission';

    public const TYPE_REFERRER_SIGNUP_BONUS = 'referrer_signup_bonus';

    public const TYPE_REFERRED_SIGNUP_BONUS = 'referred_signup_bonus';

    public const STATUS_CREDITED = 'credited';

    public const STATUS_REVERSED = 'reversed';

    protected $guarded = [];

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(BotUser::class, 'recipient_bot_user_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
