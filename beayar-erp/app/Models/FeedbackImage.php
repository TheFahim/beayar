<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackImage extends BaseModel
{
    use BelongsToCompany, HasFactory;

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class);
    }
}
