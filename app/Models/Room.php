<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Room extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'rooms';

    protected $fillable = [
        'name',
        'owner_name',
        'is_flexible',
        'floor',
        'section_id',
        'order',
        'created_by',
        'deleted_by',
    ];

    public function owners()
    {
        return $this->belongsToMany(User::class, 'room_user');
    }

    public function features()
    {
        return $this->belongsToMany(Feature::class)->withPivot('value');
    }



    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function getCapacityAttribute(): int
    {
        if ($this->relationLoaded('features')) {
            $feature = $this->features->firstWhere('name', 'Capacity');
        } else {
            $feature = $this->features()->where('name', 'Capacity')->first();
        }

        return $feature ? (int)$feature->pivot->value : 0;
    }

    public function isBooked(): bool
    {
        return $this->activeBookings()->count() >= $this->capacity;
    }

    public function activeBookings()
    {
        $now = now();
        return $this->bookings()
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('is_ended', false);
    }

    public function activeBooking($userId = null)
    {
        $now = now();

        if ($this->relationLoaded('bookings')) {
            $active = $this->bookings->filter(function ($booking) use ($now) {
                return $booking->start_time <= $now && $booking->end_time >= $now && !$booking->is_ended;
            });

            if ($userId) {
                $myBooking = $active->where('user_id', $userId)->first();
                if ($myBooking) return $myBooking;
            }

            return $active->first();
        }

        $query = $this->activeBookings();

        if ($userId) {
            $myQuery = clone $query;
            $myBooking = $myQuery->where('user_id', $userId)->first();
            if ($myBooking) return $myBooking;
        }

        return $query->first();
    }

    public function isFull(): bool
    {
        return $this->activeBookings()->count() >= $this->capacity;
    }

    public function isFullAt($startTime, $endTime): bool
    {
        return $this->bookings()
            ->where('is_ended', false)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->count() >= $this->capacity;
    }

    public function isOccupied(): bool
    {
        return $this->activeBookings()->count() > 0;
    }

    public function occupancyCount(): int
    {
        return $this->activeBookings()->count();
    }

    public function hasFutureBookingByUser($userId): bool
    {
        $now = now();
        if ($this->relationLoaded('bookings')) {
            return $this->bookings->where('user_id', $userId)
                ->where('start_time', '>', $now)
                ->where('is_ended', false)
                ->isNotEmpty();
        }
        return $this->bookings()
            ->where('user_id', $userId)
            ->where('start_time', '>', $now)
            ->where('is_ended', false)
            ->exists();
    }

    public function hasPermaBooking(): bool
    {
        if ($this->status === 'out_of_order') {
            return true;
        }

        // If it has an owner and is NOT flexible, it's a permanent booking/office
        if ($this->owner_name && !$this->is_flexible) {
            return true;
        }

        return false;
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public static function boot()
    {
        parent::boot();
    }



}
