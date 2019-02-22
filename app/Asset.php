<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    /**
     * Disabling the increment on the ID, we are using UUID
     *
     * @var boolean
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'original_name', 'path', 'content_type', 'size', 'duration'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['assetable_type', 'assetable_id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'public' => 'boolean',
    ];
    
    /**
     * Get the author of this asset.
     */
    public function user() {
        return $this->belongsTo('App\User');
    }

    /**
     * Get all of the owning assetable models.
     */
    public function assetable()
    {
        return $this->morphTo();
    }

    public function scopeAuthor($query, $userId) {
        return $query->where('assets.user_id', $userId);
    }
    
    public function scopeLatest($query) {
        return $query->orderBy('assets.created_at', 'DESC');
    }
}
