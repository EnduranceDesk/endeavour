<?php

namespace App\Models;

use App\Classes\Apache\Apache;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $appends = ["ssl","current_php"];
    public function getSslAttribute()
    {
        $apache = new Apache;
        return $apache->isSSLVhostExist($this->name);
    }
    public function getMetaData()
    {
        return json_decode($this->metadata);
    }
    public function getCurrentPHPAttribute()
    {
        return $this->getMetaData()->current_php;
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function emails()
    {
        return $this->hasMany(Email::class);
    }
    public function emailAliases()
    {
        return $this->hasMany(EmailAlias::class);
    }
}
