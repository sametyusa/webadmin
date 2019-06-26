<?php
//Admin paneline veri gönderir

error_reporting(E_ERROR);
session_start();
global $allowedIP;
global $dbUser;
global $dbPass;
global $dbName;
$dbUser = "YOUR_DB_USER";
$dbPass = 'YOUR_DB_PASS';
$dbName = "YOUR_DB_NAME";
//$allowedIP = array("10.0.2.12","10.0.30.220","127.0.0.1","172.20.34.44","10.0.41.47","10.0.113.147","10.0.41.48","172.19.71.39","172.20.34.60");
//if (in_array($_SERVER['REMOTE_ADDR'],$allowedIP)) {
    $connection = new mysqli("localhost",$dbUser,$dbPass,$dbName);
function getWSList($getWhat) {
    /*
    Sadece sayım yapar
    */
    global $dbUser;
    global $dbPass;
    global $dbName;
    global $connection;
    if ($getWhat=="all" || !isset($getWhat)) $result = $connection->query("select count(*) AS toplam from webservices") or die(json_encode(array("err"=>mysqli_error($connection))));
    elseif ($getWhat=="internet")  $result = $connection->query("select  count(*) AS toplam from webservices where internetaccess=1") or die(json_encode(array("err"=>mysqli_error($connection))));
    elseif ($getWhat=="intertechclient")  $result = $connection->query("select  count(*) AS toplam from webservices where clientaccess=1") or die(json_encode(array("err"=>mysqli_error($connection))));
    elseif ($getWhat=="denizprdoaccess")  $result = $connection->query("select  count(*) AS toplam from webservices where serveraccess=1") or die(json_encode(array("err"=>mysqli_error($connection))));
    $row = $result->fetch_array();
    return $row[0];
}

function getWSListAsCSV($getWhat) {
    /*
    CSV çıktı üretilmesi için
    */
    global $dbUser;
    global $dbPass;
    global $dbName;
    global $connection;
    if ($getWhat=="all") $result = $connection->query("select * from webservices") or die(json_encode(array("err"=>mysqli_error($connection))));
    elseif ($getWhat=="internet")  $result = $connection->query("select * from webservices where internetaccess=1") or die(json_encode(array("err"=>mysqli_error($connection))));
    elseif ($getWhat=="client")  $result = $connection->query("select  * from webservices where clientaccess=1") or die(json_encode(array("err"=>mysqli_error($connection))));
    elseif ($getWhat=="server")  $result = $connection->query("select * toplam from webservices where serveraccess=1") or die(json_encode(array("err"=>mysqli_error($connection))));
    while ($row = $result->fetch_row()) {
        $wsList[$i] = $row;
        $i++;
    }
    return $wsList;
}
function getWSListAsJson($getWhat,$startlimit,$stoplimit) {
    /*
    Datatables jquery kütüphanesiyle entegre çalışır
    */
    global $dbUser;
    global $dbPass;
    global $dbName;
    global $connection;
    if ($getWhat=="all" || !isset($getWhat)) {
        $countRows = $connection->query("select * from webservices") or die(json_encode(array("err"=>mysqli_error($connection))));
        $countFiltered = $countRows->num_rows;
        $search = htmlentities($_GET["search"]["value"]);
        if (strlen($search)>1){
            $result = $connection->query("select fullurl,hostname,IP,folder,file,Appname,intertechaccess,denizprodaccess,internetaccess,domain from webservices where CONCAT_WS(hostname,IP,folder,file,Appname,fullurl,intertechaccess,denizprodaccess,internetaccess,domain) LIKE '%$search%' limit $startlimit,$stoplimit") or die(json_encode(array("err"=>mysqli_error($connection))));
            $countResult = $connection->query("select fullurl,hostname,IP,folder,file,Appname,intertechaccess,denizprodaccess,internetaccess,domain from webservices where CONCAT_WS(hostname,IP,folder,file,Appname,fullurl,intertechaccess,denizprodaccess,internetaccess,domain) LIKE '%$search%'") or die(json_encode(array("err"=>mysqli_error($connection))));
            $countFiltered = $countResult->num_rows;
        }
        else {
            $result = $connection->query("select fullurl,hostname,IP,folder,file,Appname,intertechaccess,denizprodaccess,internetaccess,domain from webservices limit $startlimit,$stoplimit") or die(json_encode(array("err"=>mysqli_error($connection))));
            $countResult = $connection->query("select fullurl,hostname,IP,folder,file,Appname,intertechaccess,denizprodaccess,internetaccess,domain from webservices") or die(json_encode(array("err"=>mysqli_error($connection))));
            $countFiltered = $countResult->num_rows;
        }
    }
    elseif ($getWhat=="internet") {
        $countRows = $connection->query("select * from webservices where internetaccess=1") or die(json_encode(array("err"=>mysqli_error($connection))));
        $countFiltered = $countRows->num_rows;
        $search = htmlentities($_GET["search"]["value"]);
        if (strlen($search)>1){
            $result = $connection->query("select fullurl,hostname,IP,folder,file,Appname,intertechaccess,denizprodaccess,internetaccess,domain from webservices where CONCAT_WS(hostname,IP,folder,file,Appname,fullurl,intertechaccess,denizprodaccess,internetaccess,domain) LIKE '%$search%' AND internetaccess=1 limit $startlimit,$stoplimit") or die(json_encode(array("err"=>mysqli_error($connection))));
            $countResult = $connection->query("select fullurl,hostname,IP,folder,file,Appname,intertechaccess,denizprodaccess,internetaccess,domain from webservices where CONCAT_WS(hostname,IP,folder,file,Appname,fullurl,intertechaccess,denizprodaccess,internetaccess,domain) LIKE '%$search%' AND internetaccess=1") or die(json_encode(array("err"=>mysqli_error($connection))));
            $countFiltered = $countResult->num_rows;
        }
        else {
            $result = $connection->query("select fullurl,hostname,IP,folder,file,Appname,intertechaccess,denizprodaccess,internetaccess,domain from webservices where  internetaccess=1 limit $startlimit,$stoplimit") or die(json_encode(array("err"=>mysqli_error($connection))));
            $countResult = $connection->query("select fullurl,hostname,IP,folder,file,Appname,intertechaccess,denizprodaccess,internetaccess,domain from webservices where internetaccess=1") or die(json_encode(array("err"=>mysqli_error($connection))));
            $countFiltered = $countResult->num_rows;
        }
    }
    elseif ($getWhat=="intertechclient")  $result = $connection->query("select fullurl,hostname,IP,folder,file,Appname,intertechaccess,denizprodaccess,internetaccess,domain from webservices where intertechaccess=1 limit $startlimit,$stoplimit") or die(json_encode(array("err"=>mysqli_error($connection))));
    elseif ($getWhat=="denizprdoaccess")  $result = $connection->query("select fullurl,hostname,IP,folder,file,Appname,intertechaccess,denizprodaccess,internetaccess,domain from webservices where denizprdoaccess=1 limit $startlimit,$stoplimit") or die(json_encode(array("err"=>mysqli_error($connection))));

    $wsList = array();
    $i=0;
    $wsList["draw"] = (int)$_GET["draw"];
    $wsList["recordsTotal"] = $countRows->num_rows; 
    $wsList["recordsFiltered"] =$countFiltered;
    while ($row = $result->fetch_row()) {
        $wsList["data"][$i] = $row;
        $i++;
    }
    $jsonlist = json_encode($wsList);
    echo $jsonlist;
}
    function AuthUsers($username) {
        //Kullanıcı oturum açabilir mi?
        global $dbUser;
        global $dbPass;
        global $dbName;
        global $connection;
        $huser=hash("SHA256",htmlentities($username));
        $result=$connection->query("select * from natadmin where aduser='$huser'") or die(mysqli_error($connection));
      //  $stmt->bind_param("s",$huser);
        //$stmt->execute();
        if($result->num_rows>0) return 1; 
        else return 0;
    }

    if (isset($_GET["getList"]) AND isset($_SESSION["sessionid"])){
        $start=(int)$_GET["start"];
        $stop=(int)$_GET["length"]; 
        if (strlen($_GET["getwhat"])>0) $getWhat = htmlentities($_GET["getwhat"]);
        else $getWhat = "all";
        getWSListAsJson($getWhat,$start,$stop);
    };
?>