<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-03
 * Time: 11:58
 */

use \Illuminate\Database\Seeder;
use \Illuminate\Support\Facades\DB;

class WishOffShelfSeeder extends Seeder
{
    public function run()
    {
        //ibay365_wishOffShelfListing
        DB::table('guest.ibay365_wishOffShelfListing')->truncate();
        $maxID = DB::connection('pgsql')->table('wish_item')->max('id');
        $arr = [0];
        for ($i = 1; $i <= substr($maxID, 1, 1); $i++) {
            $arr[] = $i * 1000000;
        }
        //print_r($arr);exit;
        try {
            foreach ($arr as $k => $v) {
                $listingSql = "SELECT itemid,sku,
                (CASE 
                    WHEN strpos(sku,'*') > 0 THEN substring(sku,1,strpos(sku,'*') - 1) 
                    WHEN strpos(sku,'@') > 0 THEN substring(sku,1,strpos(sku,'@') - 1) 
                    WHEN strpos(sku,'#') > 0 THEN substring(sku,1,strpos(sku,'#') - 1)
                    ELSE sku
                END) AS newSku,
                now()::timestamp(0)without time zone AS updateDate,w.selleruserid
                FROM wish_item w
                LEFT JOIN aliexpress_user u ON u.selleruserid=w.selleruserid 
                WHERE  listingstatus='Ended' AND u.platform='wish' AND u.state1=1 ";
                if ($k == count($arr) - 1){
                    $listingSql .= " AND id>={$arr[$k]}";
                }else{
                    $listingSql .= "  AND id>={$arr[$k]} AND id<{$arr[$k+1]}";
                }
                $listing = DB::connection('pgsql')->select($listingSql);
                //print_r(count($listing));exit;
                $listing = array_map('get_object_vars', $listing);
                $number = count($listing);
                $size = 100;
                $step = ceil($number / $size);
                $reminder = $number % $size;
                for ($i = 0; $i < $step; $i++) {
                    $size = $i * $size < $number ? $size : $reminder - 1;
                    $rows = array_slice($listing, $i * $size, $size);
                    DB::connection('sqlsrv')->table('guest.ibay365_wishOffShelfListing')->insert($rows);
                }
            }
            $msg = date('Y-m-d H:i:s') . " Wish off shelf goods data migration successful\r\n";
        } catch (Exception $e) {
            $msg = date('Y-m-d H:i:s') . ' ' . $e->getMessage() . "\r\n";
        }
        echo $msg;
    }
}