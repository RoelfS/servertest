<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id', 'name', 'description', 'project', 'disk', 'ram', 'vpcu', 'plan_id', 'plan_name', 'status', 'ips', 
        'location', 'os'
    ];
}
