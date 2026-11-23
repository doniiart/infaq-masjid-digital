<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $record = static::query()->where('key', $key)->first();

        return $record ? $record->value : $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value]
        );
    }

    public static function getCommitteeMembers(): array
    {
        $raw = static::getValue('committee_members');

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [
            ['role' => 'Pelindung', 'name' => 'Pemerintah Desa & Tokoh Masyarakat'],
            ['role' => 'Ketua Takmir', 'name' => 'H. Ahmad Fauzi'],
            ['role' => 'Bendahara Pembangunan', 'name' => 'Ust. Budi Santoso'],
            ['role' => 'Koordinator Teknis & IT', 'name' => 'Tim Media & Digitalisasi Masjid'],
        ];
    }
}