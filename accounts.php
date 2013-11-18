<?php ini_set('display_errors','on'); 
include_once('data/CRMEntity.php'); 
include_once('modules/Accounts/Accounts.php'); 
include_once('include/database/PearDatabase.php'); 
global $adb,$current_user; 
$lines =file('Accounts.csv'); 
foreach($lines as $data) {$ij++; if($ij!=1){ $d=explode(';',$data); list($name[])= explode(',',$d[0]); 
$d[6]=str_replace('"','',$d[6]); //ind sede legale 
$d[7]=str_replace('"','',$d[7]);//leg citta 
$d[8]=str_replace('"','',$d[8]); //cod azienda 
$d[9]=str_replace('"','',$d[9]);//prov legale 
$d[10]=str_replace('"','',$d[10]);//ind oper 
$d[11]=str_replace('"','',$d[11]);//citta oper 
$d[12]=str_replace('"','',$d[12]);//prov oper 
$d[13]=str_replace('"','',$d[13]);//class $focus = new Accounts(); 
$focus->column_fields['account_name'] = $d[6]; //add all the fields you want to import $focus->column_fields["assigned_user_id"] = $current_user->id; $focus->saveentity("Accounts"); } } ?>
