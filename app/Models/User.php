<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens,HasFactory, Notifiable, HasRoles;
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'last_seen_at' => 'datetime',
        ];
    }

    public function adminlte_profile_url()
    {
        return route('profile.edit');
    }

    public function adminlte_image()
    {
        // Return a default image or null
        return 'https://picsum.photos/300/300';
        // Or return null to use the default AdminLTE image
        // return null;
    }

    /**
     * Get the user's description.
     *
     * @return string|null
     */
    public function adminlte_desc()
    {
        return 'ERP User';
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user');
    }

    public function sentChatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function receivedChatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'recipient_id');
    }

    public function canAccessTenant(?int $tenantId): bool
    {
        if (!$tenantId) {
            return false;
        }

        if ($this->hasRole('Super Admin')) {
            return $this->tenant_id === $tenantId
                || $this->tenants()->whereKey($tenantId)->exists();
        }

        return (int) $this->tenant_id === (int) $tenantId;
    }

    public function attachTenant(int $tenantId, bool $setPrimary = false): void
    {
        $this->tenants()->syncWithoutDetaching([$tenantId]);

        if ($setPrimary && !$this->tenant_id) {
            $this->tenant_id = $tenantId;
            $this->save();
        }
    }

}
