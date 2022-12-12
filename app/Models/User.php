<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory, HasApiTokens, SoftDeletes;

    protected $fillable = ['name','email','password','contact_no','picture'];

    public function sendEmailVerificationNotification()
{
  $this->notify(new VerifyEmailQueued);
}
    

}
