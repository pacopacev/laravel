<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller {

//    private $param1;
//    private $param2;

//    public function __construct() {
//        $path = resource_path() . '/others/words_alpha.txt';
//        $file = file($path);
//        $k1 = array_rand($file);
//        $k2 = array_rand($file);
//        $this->param1 = $file[$k1]; // = "";//ако се махне празния стринг пуска произволни снимки
//        $this->param2 = $file[$k2]; // = "";//ако се махне празния стринг пуска произволни снимки
//        if (($file[$k1] || $file[$k2]) == "" or ($file[$k1] && $file[$k2]) == "") {
//            $this->param1 = "bulgaria";
//            $this->param2 = "bulgaria";
//        }
//    }

//    public function index() {
//        $param1 = $this->param1;
//        $param2 = $this->param2;
//        $pics = [];
//        $slides = @file_get_contents("https://pixabay.com/api/?key=44204412-f20354ab47543c01dc09c16fb&q=" . $param1 . "+" . $param2 . "&image_type=photo", true);
//        foreach (json_decode($slides, true) as $k => $v) {
//            if (is_array($v)) {
//                foreach ($v as $kk => $vv) {
//                    if (!isset($vv['webformatURL'])) {
//                        continue;
//                    }
//                    $pics[$kk] = $vv['webformatURL'];
//                }
//            }
//        }
//        if (!count($pics)) {
//            $pics[0] = "frontend/images/signin-image.jpg";
//        }
//        return view('auth.login', compact('pics'));
//    }

    public function getUserLogs() {
        $user_logs = app('App\Models\UserLog')->getAllUserLogs();
        return view('Admin.all_user_logs', compact('user_logs')); //ne e gotowo
    }

    public function delLog($id) {
        app('App\Models\UserLog')->delLog($id);
        $user_logs = app('App\Models\User')->getAllUserLogs();
        redirect('/all-userlogs');
        return view('Admin.all_user_logs', compact('user_logs')); //ne e gotowo
    }
    
}
