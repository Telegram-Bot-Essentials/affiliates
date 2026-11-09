<?php

namespace TelegramBotEssentials\Affiliates\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use TelegramBotEssentials\Essence\Models\Bot;
use TelegramBotEssentials\Essence\Models\BotUser;

/**
 * @property int $id
 * @property int $bot_id
 * @property int $bot_user_id
 * @property string $referral_code
 * @property-read BotUser|null $botUser
 */
class Affiliate extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    /** @return BelongsTo<Bot, $this> */
    public function bot(): BelongsTo
    {
        return $this->belongsTo(Bot::class);
    }

    /** @return BelongsTo<BotUser, $this> */
    public function botUser(): BelongsTo
    {
        return $this->belongsTo(BotUser::class);
    }

    /** @return HasMany<Referral, $this> */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }
}
