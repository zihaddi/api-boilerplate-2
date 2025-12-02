<?php
namespace App\Utility;

final class Helper{


    private $bn = array("১", "২", "৩", "৪", "৫", "৬", "৭", "৮", "৯", "০");
    private $en = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
    
    private function bn2en($number) {
        return str_replace($this->bn, $this->en, $number);
    }
    
    private function en2bn($number) {
        return str_replace($this->en, $this->bn, $number);
    }

    final public static  function getTodayDate(){
        $month = date("m"); $day = date("d"); $year = date("Y");
        $today_date = $year . '-' . $month . '-' . $day;
        return $today_date;
    }
}