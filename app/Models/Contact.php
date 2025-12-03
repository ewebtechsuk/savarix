<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class Contact extends Model
{
    use HasFactory;

    public const TYPES = [
        'landlord' => 'Landlord',
        'tenant' => 'Tenant',
        'applicant' => 'Applicant',
        'vendor' => 'Vendor',
        'contractor' => 'Contractor',
        'supplier' => 'Supplier',
    ];

    protected $fillable = [
        'type',
        'name',
        'first_name',
        'last_name',
        'company',
        'email',
        'phone',
        'address',
        'notes',
    ];

    protected function normaliseRelation(string $relation): void
    {
        if (array_key_exists($relation, $this->attributes)) {
            unset($this->attributes[$relation]);
        }

        if ($this->relationLoaded($relation)) {
            $value = $this->getRelation($relation);
            if (! $value instanceof Collection) {
                $this->setRelation($relation, collect($value ?? []));
            }
        }
    }

    public function groups()
    {
        $this->normaliseRelation('groups');

        return $this->belongsToMany(ContactGroup::class, 'contact_contact_group');
    }
    public function tags()
    {
        $this->normaliseRelation('tags');

        return $this->belongsToMany(ContactTag::class, 'contact_contact_tag');
    }
    public function properties()
    {
        $this->normaliseRelation('properties');

        // A landlord can have many properties where they are the landlord
        return $this->hasMany(Property::class, 'landlord_id');
    }
    public function notes()
    {
        $this->normaliseRelation('notes');

        return $this->hasMany(ContactNote::class);
    }
    public function communications()
    {
        $this->normaliseRelation('communications');

        return $this->hasMany(ContactCommunication::class);
    }
    public function viewings()
    {
        $this->normaliseRelation('viewings');

        return $this->hasMany(ContactViewing::class);
    }

    public function tenancies()
    {
        $this->normaliseRelation('tenancies');

        return $this->hasMany(SavarixTenancy::class, 'contact_id');
    }

    public function offers()
    {
        $this->normaliseRelation('offers');

        return $this->hasMany(Offer::class);
    }
}
