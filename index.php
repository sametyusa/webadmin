<?php
/*
Web Service Browser UI. You MUST customize for your theme.

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

error_reporting("E_ERROR");
include "../lser.php"; //Login Class 
if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off"){
  //HTTP trafiğini HTTPS'e yönlendirir
  $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  header('HTTP/1.1 301 Moved Permanently');
  header('Location: ' . $redirect);
}
  session_start();
  session_regenerate_id();
  include "wsService.php"; //Main Service

  if(isset($_POST["getout"])) {
      session_destroy();
      header("Location: index.php");
  }
  function Login() {
      /*
        Account Availability Control. If account isn't locked access will be granted.
      */
	    $isLocked=GetAccessRestriction(strip_tags($_POST["username"]));
		if ($isLocked == true) { //It's not locked.
			$canLogin = loginAD(strip_tags($_POST["username"]),$_POST["password"]);
			if ($canLogin == true) { 
			  return 1;
			}
			else {
				$ip=$_SERVER['REMOTE_ADDR'];
				$failAttempt=NewFailedLoginAttempt($ip,strip_tags($_POST["username"]));
				if ($failAttempt==true) return 0; //Can still login
				else return 2; //Account locked
			}
		}
		else return 2;
	}
?>

<?php
/*
Session control.
*/
if ($_SESSION["online"]==1) {
    $getWhat = htmlentities($_GET["getwhat"], ENT_QUOTES);
    //you can use variables below for count of services.
    $countallresult = getWSList("all"); 
    $internetResult = getWSList("internet");

?>

    

<?php
if (isset($_GET["getwhat"])) {
    if ($_GET["getwhat"]=="all") echo "<h2>URL Listesi</h2> <br /><a class='btn btn-primary notika-btn-primary waves-effect' href='?filetype=asmx'>Sadece Web Servisleri Gör</a>";
    elseif ($_GET["getwhat"]=="internet") echo "<h2>İnternete Açık URL Listesi</h2><br /> <a class='btn btn-primary notika-btn-primary waves-effect' href='?getwhat=internet&filetype=asmx'>Sadece İnternete Açık Web Servisleri Gör</a>"; 
}  
else echo "<h2>URL Listesi</h2> <br /><a class='btn btn-primary notika-btn-primary waves-effect' href='?filetype=asmx'>Sadece Web Servisleri Gör</a>";
?>
<!--
    Codes below needs datatables library download from www.datatables.net
-->
<script type="text/javascript">
    $(document).ready(function() {
        $('#yourtableid').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url":"wsService.php?getList=1&getwhat=<?php echo $getWhat; ?>&filetype=<?php echo strip_tags($_GET["filetype"]); ?>",
                "dataSrc": function(json) {
                    var hayirText="No"; //Could not be accessed
                    var evetText="Yes"; //Access granted
                    var bilmemText="?"; //Not Tested
                    for ( var i=0, ien=json.data.length ; i<ien ; i++ ) {
                        //Edit index numbers in compatible with your database and wsservice.php
                        var YOUR_NETWORK_ZONE_NAME_1=parseInt(json.data[i][5]);
                        var YOUR_NETWORK_ZONE_NAME_2=parseInt(json.data[i][6]);
                        var internetaccess=parseInt(json.data[i][7]);
                        switch(YOUR_NETWORK_ZONE_NAME_1) {
                            case 0:
                                json.data[i][5]=hayirText;
                                break;
                            case 1:
                                json.data[i][5]=evetText;
                                break;
                            default:
                                json.data[i][5]=bilmemText;
                                break;
                        }

                        switch(YOUR_NETWORK_ZONE_NAME_2) {
                            case 0:
                                json.data[i][6]=hayirText;
                                break;
                            case 1:
                                json.data[i][6]=evetText;
                                break;
                                default:
                                    json.data[i][6]=bilmemText;
                                    break;
                        }

                        switch(internetaccess) {
                            case 0:
                                json.data[i][7]=hayirText;
                                break;
                            case 1:
                                json.data[i][7]=evetText;
                                break;
                            default:
                                    json.data[i][7]=bilmemText;
                                    break;
                        }
                        

                    }
                    return json.data;
                }
            }
        });
    });
</script>

<?php } //Ends session control
	else { //User not logged in insert login form below
	
?>

        <?php 
        if (strlen($_POST["username"])>1 AND strlen($_POST["password"])>1) { 
            $canlogin=Login();
            if ($canlogin==1) {
                $_SESSION["online"]=1;
                $_SESSION["username"]=strip_tags($_POST["username"]);
                header("refresh: 0");
            }
            elseif($canlogin==0) { //Username or password wrong
                ?>
            <!-- Insert here username or password is wrong message -->
                <?php
            }
            else { //Access locked for 15 minutes.
                    ?>
                <!-- Insert here access locked message -->
<?php
            }
        }				
} 
?>