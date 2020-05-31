<?php

namespace App\Models;

use App\Classes\LinuxUser\LinuxUser;
use Illuminate\Database\Eloquent\Model;

class LoginVerification extends Model
{
    protected $table = "login_verifications";
    public function scopePending()
    {
        return $this->where("progress", "pending");
    }
    public function getPassword()
    {
        return json_decode($this->payload)->password;
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function process()
    {
        $check = LinuxUser::validate($this->user->username,$this->getPassword() );
        if ($check) {
            $this->progress = "success";
            $this->success = true;
            $this->remark .= "| ENDEAVOUR: SUCCESS AT " . now() . "|";
            $this->save();
        } else {
            $this->progress = "failed";
            $this->remark .= "| ENDEAVOUR: FAILED AT " . now() . "|";
            $this->save();
        }
        return $check;
    }
}
