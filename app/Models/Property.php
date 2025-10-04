<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Property extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'price',
        'address',
        'city',
        'postcode',
        'bedrooms',
        'bathrooms',
        'type',
        'status',
        'vendor_id',
        'landlord_id',
        'tenant_id',
        'applicant_id',
        'notes',
        'activity_log',
        'document',
        'latitude',
        'longitude',
        'valuation_estimate',
        'publish_to_portal',
        'send_marketing_campaign',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activity_log' => 'array',
        'publish_to_portal' => 'boolean',
        'send_marketing_campaign' => 'boolean',
    ];

    /**
     * Vendor contact relationship.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'vendor_id');
    }

    /**
     * Landlord contact relationship.
     */
    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'landlord_id');
    }

    /**
     * Applicant contact relationship.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'applicant_id');
    }

    /**
     * Property media relationship.
     */
    public function media(): HasMany
    {
        return $this->hasMany(PropertyMedia::class)->orderBy('order');
    }

    /**
     * Property feature relationship.
     */
    public function features(): HasMany
    {
        return $this->hasMany(PropertyFeature::class);
    }

    /**
     * Documents attached to the property.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
