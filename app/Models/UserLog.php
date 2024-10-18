<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserLog extends \App\GlobalModel
{
    
        public function getAllUserLogs() {
        $user_logs = DB::table('user_log')->get(); //plamen 
        return $user_logs;
    }
    
    
     public function delLog($id) {
     $this->sqlDelete('user_log', ['id' => $id]);
     
     //dd($user_logs);
  
    }
}
