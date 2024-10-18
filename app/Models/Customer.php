<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Customer extends Model

{
    public function getAllCustomers() {
    $customers = DB::table('customers')->get();//plamen 
    return $customers;
    }
    
    use HasFactory;
}
