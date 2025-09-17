<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Category\Models\Category;
use Modules\Product\Models\Brand;
use Modules\Product\Models\Specialty;
use Modules\Product\Models\SpecialtyItem;
use Modules\Store\Models\Store;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    //======================================================================
    // MODEL CONFIGURATION
    //======================================================================

    protected $fillable = [
        'title',
        'price',
        'discount',
        'availability_status',
        'status',
        'description',
        'brand_id',
        'weight',
        'category_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'status' => 'boolean',
    ];

    //======================================================================
    // MEDIA LIBRARY CONFIGURATION
    //======================================================================

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main_image')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)->height(300)->sharpen(10)
            ->performOnCollections('main_image', 'gallery');

        $this->addMediaConversion('medium')
            ->width(600)->height(600)->sharpen(10)
            ->performOnCollections('main_image', 'gallery');

        $this->addMediaConversion('large')
            ->width(1200)->height(1200)->sharpen(10)
            ->performOnCollections('main_image', 'gallery');
    }

    //======================================================================
    // RELATIONSHIPS
    //======================================================================

    /**
     * Product belongs to one brand
     */
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'brand_product', 'product_id', 'brand_id');
    }


    /**
     * Product belongs to one category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Product has many specialties (with enhanced pivot including specialty_item_id)
     */
    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'product_specialty')
            ->withPivot(['value', 'specialty_item_id'])
            ->withTimestamps();
    }

    /**
     * Direct relationship to product specialty pivot records
     */
    public function productSpecialties(): HasMany
    {
        return $this->hasMany(ProductSpecialty::class);
    }

    /**
     * Product has one store (inventory)
     */
    public function store()
    {
        return $this->hasOne(Store::class);
    }

    //======================================================================
    // MEDIA HELPERS
    //======================================================================

    public function getMainImageUrl(?string $conversion = null): ?string
    {
        return $this->getFirstMediaUrl('main_image', $conversion ?: '');
    }

    public function getMainImageUrls(): array
    {
        $mainImage = $this->getFirstMedia('main_image');

        if (!$mainImage) {
            return [
                'original' => null,
                'large' => null,
                'medium' => null,
                'thumb' => null,
            ];
        }

        return [
            'original' => $mainImage->getUrl(),
            'large'    => $mainImage->getUrl('large'),
            'medium'   => $mainImage->getUrl('medium'),
            'thumb'    => $mainImage->getUrl('thumb'),
        ];
    }

    public function getGalleryImages()
    {
        return $this->getMedia('gallery');
    }

    public function getGalleryImageUrls(): array
    {
        return $this->getMedia('gallery')->map(function ($media) {
            return [
                'id'       => $media->id,
                'name'     => $media->name,
                'original' => $media->getUrl(),
                'large'    => $media->getUrl('large'),
                'medium'   => $media->getUrl('medium'),
                'thumb'    => $media->getUrl('thumb'),
            ];
        })->toArray();
    }

    public function hasMainImage(): bool
    {
        return $this->getMedia('main_image')->isNotEmpty();
    }

    public function hasGalleryImages(): bool
    {
        return $this->getMedia('gallery')->isNotEmpty();
    }

    public function getGalleryImagesCount(): int
    {
        return $this->getMedia('gallery')->count();
    }



    //======================================================================
    // ENHANCED SPECIALTIES HELPERS
    //======================================================================

    /**
     * Get specialty value (either custom text or selected item value)
     */
    public function getSpecialtyValue($specialty): ?string
    {
        $pivot = $this->getSpecialtyPivot($specialty);

        if (!$pivot) {
            return null;
        }

        // If specialty_item_id is set, get the value from specialty_item
        if ($pivot->specialty_item_id) {
            $specialtyItem = SpecialtyItem::find($pivot->specialty_item_id);
            return $specialtyItem?->value;
        }

        // Otherwise return the direct value
        return $pivot->value;
    }

    /**
     * Get specialty pivot record
     */
    private function getSpecialtyPivot($specialty)
    {
        if (is_string($specialty)) {
            return $this->specialties()->where('name', $specialty)->first()?->pivot;
        } elseif (is_numeric($specialty)) {
            return $this->specialties()->where('specialties.id', $specialty)->first()?->pivot;
        }

        return null;
    }

    /**
     * Get specialty display value with detailed information
     */
    public function getSpecialtyDisplayValue($specialty): array
    {
        $pivot = null;
        $specialtyModel = null;

        if (is_string($specialty)) {
            $specialtyModel = $this->specialties()->where('name', $specialty)->first();
        } elseif (is_numeric($specialty)) {
            $specialtyModel = $this->specialties()->where('specialties.id', $specialty)->first();
        }

        if (!$specialtyModel) {
            return [
                'value' => null,
                'type' => null,
                'item_id' => null,
                'custom_value' => null,
                'display_value' => null,
                'specialty_name' => null
            ];
        }

        $pivot = $specialtyModel->pivot;
        $displayValue = null;

        if ($pivot->specialty_item_id) {
            $specialtyItem = SpecialtyItem::find($pivot->specialty_item_id);
            $displayValue = $specialtyItem?->value;
        } else {
            $displayValue = $pivot->value;
        }

        return [
            'value' => $pivot->value,
            'type' => $specialtyModel->type,
            'item_id' => $pivot->specialty_item_id,
            'custom_value' => $pivot->value,
            'display_value' => $displayValue,
            'specialty_name' => $specialtyModel->name
        ];
    }

    /**
     * Set specialty value (for text type specialties)
     */
    public function setSpecialtyValue($specialtyId, $value): void
    {
        $this->specialties()->updateExistingPivot($specialtyId, [
            'value' => $value,
            'specialty_item_id' => null // Clear item selection when setting custom value
        ]);
    }

    /**
     * Set specialty item (for select type specialties)
     */
    public function setSpecialtyItem($specialtyId, $itemId, $customValue = null): void
    {
        $this->specialties()->updateExistingPivot($specialtyId, [
            'value' => $customValue,
            'specialty_item_id' => $itemId
        ]);
    }

    /**
     * Attach specialty with value or item
     */
    public function attachSpecialtyWithValue($specialtyId, $value = null, $itemId = null): void
    {
        $this->specialties()->attach($specialtyId, [
            'value' => $value,
            'specialty_item_id' => $itemId
        ]);
    }

    /**
     * Enhanced sync specialties with their values and items
     * Expected format: [specialty_id => ['value' => '...', 'item_id' => ...], ...]
     * OR old format: [specialty_id => 'value', ...] (backward compatibility)
     */
    public function syncSpecialtiesWithValues(array $specialties): void
    {
        $syncData = [];

        foreach ($specialties as $specialtyId => $data) {
            // Handle both new and old formats
            if (is_array($data)) {
                // New format with value and item_id
                $syncData[$specialtyId] = [
                    'value' => $data['value'] ?? null,
                    'specialty_item_id' => $data['item_id'] ?? null
                ];
            } else {
                // Old format - backward compatibility
                $syncData[$specialtyId] = [
                    'value' => $data,
                    'specialty_item_id' => null
                ];
            }
        }

        $this->specialties()->sync($syncData);
    }

    /**
     * Get all specialty values formatted for display
     */
    public function getFormattedSpecialties(): array
    {
        return $this->specialties->map(function ($specialty) {
            $displayValue = $this->getSpecialtyDisplayValue($specialty->id);

            return [
                'id' => $specialty->id,
                'name' => $specialty->name,
                'type' => $specialty->type,
                'type_label' => $specialty->getTypeLabelAttribute(),
                'value' => $displayValue['value'],
                'custom_value' => $displayValue['custom_value'],
                'item_id' => $displayValue['item_id'],
                'display_value' => $displayValue['display_value']
            ];
        })->toArray();
    }

    /**
     * Check if product has a specific specialty
     */
    public function hasSpecialty($specialtyId): bool
    {
        return $this->specialties()->where('specialties.id', $specialtyId)->exists();
    }

    /**
     * Get specialty items for select type specialties of this product
     */
    public function getSpecialtyItems($specialtyId): array
    {
        $specialty = Specialty::find($specialtyId);

        if (!$specialty || !$specialty->isSelectType()) {
            return [];
        }

        return $specialty->items()->get(['id', 'value'])->toArray();
    }

    /**
     * Get specialties grouped by type
     */
    public function getSpecialtiesByType(): array
    {
        $formatted = $this->getFormattedSpecialties();

        return [
            'text' => array_filter($formatted, fn($s) => $s['type'] === 'text'),
            'select' => array_filter($formatted, fn($s) => $s['type'] === 'select')
        ];
    }

    //======================================================================
    // BUSINESS LOGIC
    //======================================================================

    const COMING_SOON = 'coming_soon';
    const AVAILABLE   = 'available';
    const UNAVAILABLE = 'unavailable';

    public static function getAvailabilityStatuses(): array
    {
        return [
            self::COMING_SOON => 'به‌زودی',
            self::AVAILABLE   => 'موجود',
            self::UNAVAILABLE => 'ناموجود',
        ];
    }

    public function getAvailabilityStatusLabelAttribute(): string
    {
        return self::getAvailabilityStatuses()[$this->availability_status] ?? $this->availability_status;
    }

    public function getFinalPriceAttribute(): float
    {
        if ($this->discount > 0) {
            return $this->price - ($this->price * $this->discount / 100);
        }
        return $this->price;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->discount > 0;
    }

    public function getDiscountAmountAttribute(): float
    {
        return $this->discount > 0 ? ($this->price * $this->discount / 100) : 0;
    }

    //======================================================================
    // SCOPES
    //======================================================================

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('availability_status', self::AVAILABLE);
    }

    public function scopeWithMainImage($query)
    {
        return $query->whereHas('media', function ($q) {
            $q->where('collection_name', 'main_image');
        });
    }

    public function scopeOnSale($query)
    {
        return $query->where('discount', '>', 0);
    }

    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeWithSpecialty($query, $specialtyId)
    {
        return $query->whereHas('specialties', function ($q) use ($specialtyId) {
            $q->where('specialties.id', $specialtyId);
        });
    }

    /**
     * Scope for products with text type specialty values
     */
    public function scopeWithSpecialtyValue($query, $specialtyId, $value)
    {
        return $query->whereHas('specialties', function ($q) use ($specialtyId, $value) {
            $q->where('specialties.id', $specialtyId)
                ->wherePivot('value', $value)
                ->wherePivot('specialty_item_id', null);
        });
    }

    /**
     * Scope for products with specific specialty item
     */
    public function scopeWithSpecialtyItem($query, $specialtyId, $itemId)
    {
        return $query->whereHas('specialties', function ($q) use ($specialtyId, $itemId) {
            $q->where('specialties.id', $specialtyId)
                ->wherePivot('specialty_item_id', $itemId);
        });
    }

    public function getAvailabilityAttribute()
    {
        return $this->availability_status;
    }

    public function setAvailabilityAttribute($value)
    {
        $this->attributes['availability_status'] = $value;
    }
}
