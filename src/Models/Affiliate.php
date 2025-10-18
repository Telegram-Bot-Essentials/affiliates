<?php

namespace TelegramBotEssentials\Affiliates\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Affiliate extends Model
{
    use BelongsToTenant;

    protected $guarded = [];
}
