<?php

namespace App\Models;

use App\Classes\Apache\Apache;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $append = "ssl";
    public function getSslAttribute()
    {
        $apache = new Apache;
        return $apache->isSSLVhostExist($this->name);
    }
}
