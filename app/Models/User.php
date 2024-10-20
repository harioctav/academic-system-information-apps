<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\GeneralConstant;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use Ramsey\Uuid\Uuid as Generator;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasName
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable, HasRoles;

  public function canAccessPanel(Panel $panel): bool
  {
    return $this->status->value === GeneralConstant::Active->value;
  }

  protected static function boot()
  {
    parent::boot();

    static::creating(function ($user) {
      $user->uuid = Generator::uuid4()->toString();
    });

    static::deleting(function ($user) {
      if ($user->avatar) {
        if (Storage::disk('public')->exists($user->avatar)) {
          Storage::disk('public')->delete($user->avatar);
        }
      }
    });
  }

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'uuid',
    'name',
    'email',
    'phone',
    'password',
    'avatar',
    'status',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

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
      'status' => GeneralConstant::class,
    ];
  }

  /**
   * Get the route key for the model.
   */
  public function getRouteKeyName(): string
  {
    return 'uuid';
  }

  protected ?string $cachedAvatarUrl = null;

  /**
   * Get Filament avatar URL.
   */
  public function getFilamentAvatarUrl(): ?string
  {
    return $this->getAvatarUrl();
  }

  public function getFilamentName(): string
  {
    return "{$this->name}";
  }

  /**
   * Get user avatar URL.
   */
  public function getUserAvatar(): string
  {
    return $this->getAvatarUrl();
  }

  /**
   * Get avatar URL (internal method).
   */
  protected function getAvatarUrl(): string
  {
    if ($this->cachedAvatarUrl === null) {
      $this->cachedAvatarUrl = $this->avatar
        ? Storage::url($this->avatar)
        : asset('assets/images/placeholders/default-avatar.png');
    }

    return $this->cachedAvatarUrl;
  }
}
