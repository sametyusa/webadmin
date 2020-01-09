<?php
/*
This file stores functions which listens data from powershell and python scripts.

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
global $allowedIP;
global $dbUser;
global $dbPass;
global $dbName;
$dbUser = "YOUR_DB_USER";
$dbPass = 'YOUR_DB_PASSWORD';
$dbName = "YOUR_DB_NAME";
$allowedIP = array("ADD YOUR IP LIST");
if (in_array($_SERVER['REMOTE_ADDR'],$allowedIP)) {
    header("Content-Type: application/json; charset=UTF-8");
    $connection = new mysqli("localhost",$dbUser,$dbPass,$dbName);

function insertDB($hostname,$ip,$folder,$file,$appname,$iprestriction,$fullurl,$domain) {
    global $dbUser;
    global $dbPass;
    global $dbName;
    global $connection;
         /*
            $hostname = server name
            $ip = server IP
            $folder = Folder which detected files on IIS server
            $file = File Name which detected  on IIS server
            $vpath = Virtual path on IIS server
            $iprestriction = Has IP Restriction set for site
            $fullurl = Generated URL includes data above
            $domain = Scan zone
        */
    
        $checkDuplicate = $connection->query("SELECT * from webservices where fullurl='$fullurl'") or die(json_encode(array("err"=>mysqli_error($connection))));
        if ($checkDuplicate->num_rows<1){
           /* $connection->query("INSERT INTO webservices(hostname,ip,folder,file,Appname,IPRestrictionInIIS,fullurl) 
            VALUES('$hostname','$ip','$folder','$file','$appname','$iprestriction','$fullurl')") or die(json_encode(array("err"=>mysqli_error($connection))));*/
            $stmt = $connection->prepare("INSERT INTO webservices(hostname,ip,folder,file,Appname,IPRestrictionInIIS,fullurl,domain) VALUES(?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssss",$hostname,$ip,$folder,$file,$appname,$iprestriction,$fullurl,$domain);
            $stmt->execute();

            if ($connection){
                echo json_encode(array("msg"=>"success"));
            }
        
            else {
                echo json_encode(array("msg"=>"hata"));
            }
        }
        else {
            echo json_encode(array("err"=>"duplicate","msg"=>$fullurl ." is duplicate"));
        }
      // $connection->close();
        
}

function updateAccessInfo($fullurl,$accessfrom,$accessStatus) {
    global $connection;
    $intAccessStatus = (int)$accessStatus;
    //if (is_int($accessStatus)) {
        if ($accessfrom == "intertechclient") {
            $connection->query("update webservices set YOUR_NETWORK_ZONE_NAME_1='$intAccessStatus' where fullurl='$fullurl'") or die(json_encode(array("err"=>mysqli_error($connection))));
            echo json_encode(array("msg"=>"success"));
        }
        elseif ($accessfrom == "denizbankprod") {
            $connection->query("update webservices set YOUR_NETWORK_ZONE_NAME_2='$intAccessStatus' where fullurl='$fullurl'") or die(json_encode(array("err"=>mysqli_error($connection))));
            echo json_encode(array("msg"=>"success"));
        }
        elseif ($accessfrom == "denizbankClient") {
	        $connection->query("update webservices set YOUR_NETWORK_ZONE_NAME_3='$intAccessStatus' where fullurl='$fullurl'") or die(json_encode(array("err"=>mysqli_error($connection))));
                echo json_encode(array("msg"=>"success"));

	}
	elseif ($accessfrom == "internet") {
            $connection->query("update webservices set internetaccess='$intAccessStatus' where fullurl='$fullurl'") or die(json_encode(array("err"=>mysqli_error($connection))));
            echo json_encode(array("msg"=>"success"));
        }
        else {
            echo json_encode(array("err"=>"unknown location","msg"=>"Wrong parameter or value"));
        }
        
   /* }
    else {
        echo json_encode(array("err"=>"bad variable","msg"=>"Unrecognized variable type or value of access status"));
    }
    */
}
function getWebServiceList($start,$stop,$domain,$filetype) {
    global $connection;
    $getWhat = htmlentities($_POST["getwhat"]);

    if (strlen($filetype)>1 && $getWhat == "all") $sorgu = "select * from webservices where file like '%.$filetype' and domain = '$domain' limit $start,$stop";
    elseif (strlen($filetype)>1 && $getWhat != "all") $sorgu = "select * from webservices where file like '%.$filetype' and domain = '$domain' limit $start,$stop";
    elseif(strlen($filetype)<1 && $getWhat == "all") $sorgu = "select * from webservices where domain = '$domain' limit $start,$stop";
    elseif(strlen($filetype)<1 && $getWhat !="all") $sorgu = "select fullurl from webservices where domain = '$domain' limit $start,$stop";
    $result = $connection->query($sorgu) or die(json_encode(array("err"=>mysqli_error($connection))));

    $urlList = array();
    $i=0;
    while ($row = $result->fetch_row()) {
        $urlList[$i] = $row;
        $i++;
    }
    echo json_encode(array("msg"=>"success","url"=>$urlList));
}

if ($connection->connect_errno) {
    echo json_encode(array("err"=>$connection->connect_error));
    exit();
}
else {
    if (isset($_POST['add']) && isset($connection)) {
        insertDB(
            htmlentities($_POST['hostname']),
            htmlentities($_POST['ip']),
            htmlentities($_POST['folder']),
            htmlentities($_POST['file']),
            htmlentities($_POST['appname']),
            htmlentities($_POST['iprestriction']),
            htmlentities($_POST['fullurl']),
            htmlentities($_POST['domain']));
        //echo json_encode(array("msg"=>"ok"));
     }
     elseif (isset($_POST["update"]) AND isset($_POST['fullurl'])  AND isset($_POST['accessfrom'])) {
        updateAccessInfo(
            htmlentities($_POST['fullurl']),
            htmlentities($_POST['accessfrom']),
            htmlentities($_POST['accessstatus'])
            );
     }
     elseif (isset($_POST['geturl']) AND isset($_POST['domain'])) {
        getWebServiceList(htmlentities($_POST['startlimit']),htmlentities($_POST['stoplimit']),htmlentities($_POST['domain']),htmlentities($_POST['filetype']));
     }
    
     
     else {
         #echo json_encode(print_r($_POST));
         echo json_encode(array("err"=>"parameter missing","msg"=>"request was incomplete"));
     }
}
}
else {
   /*
        Accessed denied
   */
}

?>
