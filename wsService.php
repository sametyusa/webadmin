<?php
/*
Web Service Browser 
You need to edit db credentials and network zone names before installation.
Compatible with javascript datatables library.

MIT License

Copyright (c) 2020 Samet Atalar

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/
error_reporting(E_ERROR);
session_start();
global $allowedIP;
global $dbUser;
global $dbPass;
global $dbName;
$dbUser = "YOUR_DB_USER";
$dbPass = 'YOUR_DB_PASSWORD';
$dbName = "YOUR_DB_NAME";
$connection = new mysqli("localhost",$dbUser,$dbPass,$dbName);
function getWSList($getWhat) {
    /*
    Sadece sayım yapar
    */
    global $dbUser;
    global $dbPass;
    global $dbName;
    global $connection;
    $filetype = strip_tags($_GET["filetype"]); //optional parameter
    $filequery="";
    if ($getWhat=="all" || !isset($getWhat))  {
        if (strlen($filetype)>1) $result = $connection->query("select count(*) AS toplam from webservices") or die(json_encode(array("err"=>mysqli_error($connection))));
        else $result = $connection->query("select count(*) AS toplam from webservices") or die(json_encode(array("err"=>mysqli_error($connection))));
    }
    elseif ($getWhat=="internet")  $result = $connection->query("select  count(*) AS toplam from webservices where internetaccess=1 ") or die(json_encode(array("err"=>mysqli_error($connection))));
    elseif ($getWhat=="YOUR_NETWORK_ZONE_NAME_1")  $result = $connection->query("select  count(*) AS toplam from webservices where YOUR_NETWORK_ZONE_NAME_1=1 ") or die(json_encode(array("err"=>mysqli_error($connection))));
    elseif ($getWhat=="YOUR_NETWORK_ZONE_NAME_2")  $result = $connection->query("select  count(*) AS toplam from webservices where YOUR_NETWORK_ZONE_NAME_2=1 ") or die(json_encode(array("err"=>mysqli_error($connection))));
    elseif ($getWhat=="YOUR_NETWORK_ZONE_NAME_3")  $result = $connection->query("select  count(*) AS toplam from webservices where YOUR_NETWORK_ZONE_NAME_3=1 ") or die(json_encode(array("err"=>mysqli_error($connection))));
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
    elseif ($getWhat=="internet")  $result = $connection->query("select * from webservices where YOUR_NETWORK_ZONE_NAME_1=1") or die(json_encode(array("err"=>mysqli_error($connection))));
    elseif ($getWhat=="YOUR_NETWORK_ZONE_NAME_1")  $result = $connection->query("select  * from webservices where YOUR_NETWORK_ZONE_NAME_1=1") or die(json_encode(array("err"=>mysqli_error($connection))));
    elseif ($getWhat=="YOUR_NETWORK_ZONE_NAME_2")  $result = $connection->query("select * toplam from webservices where YOUR_NETWORK_ZONE_NAME_2=1") or die(json_encode(array("err"=>mysqli_error($connection))));
    elseif ($getWhat=="YOUR_NETWORK_ZONE_NAME_3")  $result = $connection->query("select  count(*) AS toplam from webservices where YOUR_NETWORK_ZONE_NAME_3=1") or die(json_encode(array("err"=>mysqli_error($connection))));
    while ($row = $result->fetch_row()) {
        $wsList[$i] = $row;
        $i++;
    }
    return $wsList;
}
function getWSListAsJson($getWhat,$startlimit,$stoplimit) {
    /*
    Json formatında veritabanındaki kayıtları iletir
    Datatables jquery kütüphanesiyle entegre çalışır
    */
    global $dbUser;
    global $dbPass;
    global $dbName;
    global $connection;
    $wsquery="";
    $wsqueryCount="";
    $search = strip_tags($_GET["search"]["value"]); //optional parameter
    $filetype = strip_tags($_GET["filetype"]); //optional parameter
    $order = $_GET["order"]; //optional parameter
    $filequery="";
    $orderext=""; //UI'dan sıralama istendi mi?
    if ($filetype=="asmx" || $filetype=="svc") { $filequery="(file LIKE '%.asmx' OR file LIKE '%.svc')";}
    if(count($order)>0) {
        //DB'den index numarasıyla çekmek istemiyoruz.
        //Eğer ekranda gösterilen sütunlarda değişiklik olursa buradaki dizide de değişiklik olmalı
        $colList=["fullurl","hostname","IP","folder","Appname","YOUR_NETWORK_ZONE_NAME_1","YOUR_NETWORK_ZONE_NAME_2","YOUR_NETWORK_ZONE_NAME_3","domain","YOUR_NETWORK_ZONE_NAME_4"];
        $colname=strip_tags($colList[$order[0][column]]);
        $orderdir=strip_tags($order[0][dir]);
        $orderext=" ORDER BY $colname $orderdir";
    }
    if ($getWhat=="all" || strlen($getWhat)<1) {
        if (strlen($search)>1){
            //Arama yapılıyor... 
            if ($filetype=="asmx" || $filetype=="svc") {
                //Buradaki sorguda sadece file sütunun sonuna bakılıyor
                $wsquery="select fullurl,hostname,IP,folder,Appname,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain,YOUR_NETWORK_ZONE_NAME_4 from webservices where CONCAT_WS(hostname,IP,folder,file,Appname,fullurl,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain) LIKE '%$search%' AND (file like '%.svc' OR file like '%.asmx')".$orderext." limit $startlimit,$stoplimit";
                $wsqueryCount="select fullurl,hostname,IP,folder,Appname,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain,YOUR_NETWORK_ZONE_NAME_4 from webservices where CONCAT_WS(hostname,IP,folder,file,Appname,fullurl,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain) LIKE '%$search%' AND (file like '%.svc' OR file like '%.asmx') ";
              
            } 
            else {
                $wsquery="select fullurl,hostname,IP,folder,Appname,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain,YOUR_NETWORK_ZONE_NAME_4 from webservices where CONCAT_WS(hostname,IP,folder,file,Appname,fullurl,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain) LIKE '%$search%' ".$orderext." limit $startlimit,$stoplimit";
                $wsqueryCount="select fullurl,hostname,IP,folder,Appname,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain,YOUR_NETWORK_ZONE_NAME_4 from webservices where CONCAT_WS(hostname,IP,folder,file,Appname,fullurl,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain) LIKE '%$search%'";
            }
        }
        else {
            /*
                Default davranış: Tüm kayıtlar istenmiş
            */  
            if ($filetype=="asmx" || $filetype=="svc") $nfilequery="WHERE ".$filequery;
            $wsquery="select fullurl,hostname,IP,folder,Appname,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain,YOUR_NETWORK_ZONE_NAME_4 from webservices ".$nfilequery.$orderext." limit $startlimit,$stoplimit";
            $wsqueryCount = "select fullurl,hostname,IP,folder,Appname,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain,YOUR_NETWORK_ZONE_NAME_4 from webservices ".$nfilequery.$orderext;
        }
    }
    elseif ($getWhat=="internet") {
        if ($filetype=="asmx" || $filetype=="svc")  $filequery="AND ".$filequery;
        /*
        $wsquery = "select * from webservices where internetaccess=1 ". $filequery;
       */
        if (strlen($search)>1){
            $wsquery = "select fullurl,hostname,IP,folder,Appname,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain from webservices where CONCAT_WS(hostname,IP,folder,file,Appname,fullurl,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain) LIKE '%$search%' AND internetaccess=1 ". $filequery." ". $orderext." limit $startlimit,$stoplimit";
            $wsqueryCount = "select fullurl,hostname,IP,folder,Appname,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain from webservices where CONCAT_WS(hostname,IP,folder,file,Appname,fullurl,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain) LIKE '%$search%' AND internetaccess=1 ".$filequery ." ".$orderext;
        }
        else {
            $wsquery = "select fullurl,hostname,IP,folder,Appname,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain from webservices where  internetaccess=1 ". $filequery ." ".$orderext." limit $startlimit,$stoplimit";
            $wsqueryCount = "select fullurl,hostname,IP,folder,Appname,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain from webservices where internetaccess=1 ". $filequery ." ".$orderext;
            
        }
    }
    elseif ($getWhat=="intertechclient")  $wsquery="select fullurl,hostname,IP,folder,Appname,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain from webservices where YOUR_NETWORK_ZONE_NAME_1=1 ".$orderext." limit $startlimit,$stoplimit";
    elseif ($getWhat=="denizprdoaccess")  $wsqueryCount ="select fullurl,hostname,IP,folder,Appname,YOUR_NETWORK_ZONE_NAME_1,YOUR_NETWORK_ZONE_NAME_2,internetaccess,domain from webservices where denizprdoaccess=1 ".$orderext ." limit $startlimit,$stoplimit";
    $result = $connection->query($wsquery) or die(json_encode(array("err"=>mysqli_error($connection))));
    $countResult = $connection->query($wsqueryCount) or die(json_encode(array("err"=>mysqli_error($connection))));
    $countRows = $connection->query($wsqueryCount) or die(json_encode(array("err"=>mysqli_error($connection))));
    $countFiltered = $countRows->num_rows;
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
    
    function exportCSV($data) {
        ob_end_clean();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="WebServiceList.csv"');
        $df = fopen("php://output", 'w');
        fputcsv($df, array('ID', 'ComputerName', 'IP','Folder','File','appname','IPRestrictionInIIS','fullURL','YOUR_NETWORK_ZONE_NAME_2','YOUR_NETWORK_ZONE_NAME_3','internetAccess','Scope'),$delimiter = ",",$enclosure = '"', $mysql_null = true);
        foreach ($data as $row) {
          fputcsv($df, $row);
        }
        fclose($df);
        exit;
      }
     
    if (isset($_GET["getList"]) AND $_SESSION["online"]==1){
        $start=(int)$_GET["start"];
        $stop=(int)$_GET["length"]; 
        if (strlen($_GET["getwhat"])>0) $getWhat = strip_tags($_GET["getwhat"]);
        else $getWhat = "all";
        getWSListAsJson($getWhat,$start,$stop);
    };
?>