<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Symfony\Component\Mime\Address;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'profile_photo_path',
        'password',
        'phone',
        'role',
        'is_admin',
    ];
        public function cart(){
            return $this->hasOne(Cart::class);
        }
        public function orders(){
            return $this->hasMany(Orders::class);
        }
        public function addresses(){
            return $this->hasMany(Addresses::class);
        }
        public function isAdmin(){
            return $this->is_admin == 1;
        }

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
        ];
    }
    protected static function booted(){
        static::created(function($user){
            $user->cart()->create();
        });
    }
}
