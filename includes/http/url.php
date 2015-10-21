<?
include_once"Request.php"; 
if(isset($_REQUEST[queryStr]) & $_REQUEST[queryStr]!='')
{
//print_r(substr($_SERVER['QUERY_STRING'],9));die;
$reqObj= new Ottocrat_Request($val);
$qStr=$_REQUEST[queryStr];
//$res=array("url"=>$reqObj->encryptLink(substr($qStr,1)));
//echo json_encode($res);
echo $reqObj->encryptLink($qStr);
}

?>