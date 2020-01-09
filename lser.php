<?php
/*
Active Directory Authentication And Authorization Service for Web Service Browser

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

function AuthUsers($username) {
    //Can user login?
    global $dbUser;
    global $dbPass;
    global $dbName;
    global $connection;
    $huser=hash("SHA256",strip_tags($username));
    $result=$connection->query("select * from users where aduser='$huser'") or die(mysqli_error($connection));
    if($result->num_rows>0) return 1; 
    else return 0;
}

function loginAD($username,$pass) {
    /*
        Active Directory Log in Function
        If you have no Active Directory server you can ignore this function
    */
       $ldap = ldap_connect("ldap://fqdn.yourdomain.com");
       ldap_set_option($ldap,LDAP_OPT_PROTOCOL_VERSION, 3);
       $uname=strip_tags($username);
       $canUserLogIn = AuthUsers($uname);
       if ($canUserLogIn==1){
           if ($bind = ldap_bind($ldap, "DOMAIN\\".$uname, $pass)){
               ClearAccessRestriction($uname); //Checks for is account still locked.
               return true;
           }
           else  return false;
       }
   }
   /*
    Access Restriction Functions
    If a user fails to login n times  his/hers account will be locked for y minutes.
    DB Structure

    CREATE TABLE `accessres` (
    `id` int(11) NOT NULL,
    `ip` varchar(45) DEFAULT NULL,
    `endtime` datetime DEFAULT NULL,
    `username` varchar(45) DEFAULT NULL,
    `attempt` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username_UNIQUE` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='access restrictions';

    */
       
   function SetAccessRestriction($ip,$user,$endtime,$attempt) {
       /*
        Create access restriction by username. IP is for logging only
       */
       global $dbUser;
       global $dbPass;
       global $dbName;
       global $connection;

       $id=rand(0,1000);
       if ($attempt==1) {
           $dbquery=$connection->query("insert into accessres values('$id','$ip','$endtime','$user','$attempt')") or die(json_encode(array("err"=>mysqli_error($connection))));
       }
       elseif ($attempt==2){ 
           $dbquery=$connection->query("update accessres set attempt=2 where username='$user'") or die(json_encode(array("err"=>mysqli_error($connection))));
       }
       
   }
   
   function NewFailedLoginAttempt($ip,$user) {
       /*
       Logs failed login attempt
       Default table name is "accessres"
       */
       global $dbUser;
       global $dbPass;
       global $dbName;
       global $connection;
       $nip=strip_tags($ip);
       $nuser=strip_tags($user);
       $anyFail=$connection->query("select * from accessres where username='$nuser'") or die(json_encode(array("err"=>mysqli_error($connection))));
       //if ($anyFail->num_rows>0) {
           $failrow=$anyFail->fetch_assoc();
           $failtime=date("Y-m-d H:i:s");
           if($failrow["attempt"]==0 || $anyFail->num_rows==0) {
               SetAccessRestriction($nip,$nuser,$failtime,1);
               return true; // First time fail
           }
           elseif ($failrow["attempt"]==1) { //You must change this number with fail attempt limit you'd like to 
               SetAccessRestriction($nip,$nuser,$failtime,2);
               return false; //Access restricted
           }
       //}
   }
   function ClearAccessRestriction($user) {
       /*
        Kullanıcının kilidini kaldırır
       */
       global $dbUser;
       global $dbPass;
       global $dbName;
       global $connection;
       $nuser=strip_tags($user);
       $unlock=$connection->query("delete from accessres where username='$nuser'") or die(json_encode(array("err"=>mysqli_error($connection))));
       
   }
   function GetAccessRestriction($user) {
       /*
        Checks for access restriction.
       */
       global $dbUser;
       global $dbPass;
       global $dbName;
       global $connection;
       $nuser=strip_tags($user);
       $result=$connection->query("select * from accessres where username='$nuser'") or die(json_encode(array("err"=>mysqli_error($connection))));
       $failResult=$result->fetch_assoc();
       if ($result->num_rows>=1) {
           if ($failResult["attempt"]==1) return true;
           elseif ($failResult["attempt"]==2){
               // Checks time. Has 15 minutes passed?
               $lastFail= new DateTime($failResult["endtime"]);
               $nowDate= new DateTime(date("Y-m-d H:i:s"));
               $fark=$nowDate->diff($lastFail)->i;
               if ($fark<15) return false; //you must change this number with suspension time you'd like to
               else {
                   ClearAccessRestriction($nuser);
                   return true;
               }	
       }
       else return true;
       }
       else return true;
   }

?>