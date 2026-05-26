<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'user_type', 'gender', 'blocked', 'photo_url', 'custom'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'blocked' => 'boolean',
            'custom' => 'array',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function getPhotoFullUrlAttribute()
    {
        if ($this->photo_url && Storage::disk('public')->exists("photos/{$this->photo_url}")) {
            return asset("storage/photos/{$this->photo_url}");
        }

        return asset("storage/photos/anonymous.png");
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'id', 'id')->withTrashed();
    }

    public function getAdminAttribute(): bool
    {
        return $this->user_type === 'A';
    }

    public function getTypeAttribute(): ?string
    {
        return $this->user_type;
    }

    public function isCustomer(): bool
    {
        return $this->user_type === 'C';
    }

    public function isEmployee(): bool
    {
        return $this->user_type === 'F';
    }

    public function isAdmin(): bool
    {
        return $this->user_type === 'A';
    }
}
