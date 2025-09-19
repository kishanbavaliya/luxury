<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotIcludeOwner extends Model
{
    use HasFactory;
    
    protected $table = 'not_include_owners';

   protected $fillable = [
       'user_id','request_id'
   ];
}
