<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Category\Models\Category;
use Modules\Product\Models\Brand;
use Modules\Product\Models\Specialty;
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
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Product belongs to one category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Product has many specialties (with pivot value)
     */
    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'product_specialty')
            ->withPivot('value')
            ->withTimestamps();
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
    // SPECIALTIES HELPERS
    //======================================================================

    public function getSpecialtyValue($specialty): ?string
    {
        if (is_string($specialty)) {
            return $this->specialties()->where('name', $specialty)->first()?->pivot?->value;
        }

        if (is_numeric($specialty)) {
            return $this->specialties()->where('specialties.id', $specialty)->first()?->pivot?->value;
        }

        return null;
    }

    public function setSpecialtyValue($specialtyId, $value): void
    {
        $this->specialties()->updateExistingPivot($specialtyId, ['value' => $value]);
    }

    public function attachSpecialtyWithValue($specialtyId, $value = null): void
    {
        $this->specialties()->attach($specialtyId, ['value' => $value]);
    }

    public function syncSpecialtiesWithValues(array $specialties): void
    {
        $syncData = [];
        foreach ($specialties as $specialtyId => $value) {
            $syncData[$specialtyId] = ['value' => $value];
        }
        $this->specialties()->sync($syncData);
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
}
