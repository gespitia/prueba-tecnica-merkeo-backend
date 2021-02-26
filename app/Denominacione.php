<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Denominacione extends Model
{
    use SoftDeletes;
    protected $fillable = ["valor", "nombre", "existencia", "user_id"];
}
