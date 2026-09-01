<?php

namespace App\Models;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'type', 'status', 'name', 'slug', 'description', 'website_url',
        'country_id', 'administrative_area_id', 'city_id',
    ];

    protected function casts(): array
    {
        return ['type' => MediaType::class, 'status' => MediaStatus::class];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function administrativeArea(): BelongsTo
    {
        return $this->belongsTo(AdministrativeArea::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function radioStation(): HasOne
    {
        return $this->hasOne(RadioStation::class);
    }

    public function tvChannel(): HasOne
    {
        return $this->hasOne(TvChannel::class);
    }

    public function podcast(): HasOne
    {
        return $this->hasOne(Podcast::class);
    }

    public function podcastEpisode(): HasOne
    {
        return $this->hasOne(PodcastEpisode::class);
    }

    public function podcastEpisodes(): HasMany
    {
        return $this->hasMany(PodcastEpisode::class, 'podcast_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class)->withPivot('is_primary');
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(MediaArtwork::class);
    }

    public function sources(): HasMany
    {
        return $this->hasMany(MediaSource::class);
    }

    public function streamSources(): HasMany
    {
        return $this->hasMany(StreamSource::class);
    }

    public function primaryStream(): HasOne
    {
        return $this->hasOne(StreamSource::class)->where('is_primary', true);
    }
}
