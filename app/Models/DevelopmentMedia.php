<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevelopmentMedia extends Model
{
    protected $table = 'development_media';

    protected $fillable = [
        'title',
        'description',
        'media_type',
        'file_path',
        'external_url',
        'sort_order',
        'is_published',
        'uploaded_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected $appends = [
        'media_url',
        'youtube_embed_url',
    ];

    public function getMediaUrlAttribute(): ?string
    {
        if ($this->media_type === 'youtube') {
            return $this->external_url;
        }

        if ($this->file_path) {
            return '/storage/' . ltrim($this->file_path, '/');
        }

        return $this->external_url;
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if ($this->media_type !== 'youtube' || empty($this->external_url)) {
            return null;
        }

        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?|shorts)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $this->external_url, $match)) {
            return 'https://www.youtube.com/embed/' . $match[1];
        }

        return null;
    }
}