<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-04-03
 * Time: 11:58
 */
use \Illuminate\Database\Seeder;
use \Illuminate\Support\Facades\DB;

class IbayTableSeeder extends Seeder
{
    public function run()
     {
         //������ݱ�ibay365_ebay_listing
         DB::table('ibay365_ebay_listing')->truncate();
         //��ȡibay365��eBay listing
         $step = 200;//ÿ��ȡ����������
         for ($i=0; ;$i++){
             $listingSql = "SELECT e.itemid,e.sku AS code,er.sku AS sku,listingtype,country,initialnumber,
                (CASE 
                    WHEN INSTR(er.sku,'*') > 0 THEN SUBSTR(er.sku,1,INSTR(er.sku,'*') - 1) 
                    WHEN INSTR(er.sku,'@') > 0 THEN SUBSTR(er.sku,1,INSTR(er.sku,'@') - 1) 
                    WHEN INSTR(er.sku,'#') > 0 THEN SUBSTR(er.sku,1,INSTR(er.sku,'#') - 1)
                    ELSE er.sku
                END) AS newSku
                FROM ebay_item e  
                LEFT JOIN ebay_fillquantity er ON er.itemid=e.itemid
                WHERE country='CN' AND e.sku IS NOT NULL AND er.sku IS NOT NULL AND initialnumber<>0 
                AND listingstatus = 'Active' 
                AND listingtype = 'FixedPriceItem' " . ' LIMIT ' . $step*$i . ',' . $step;
             $listing = DB::connection('mysql')->select($listingSql);
             $listing = array_map('get_object_vars',$listing);
             if(!$listing){
                 break;
             }else{
                 //�������ݱ���
                 DB::table('ibay365_ebay_listing')->insert($listing);
             }
         }

     }
}