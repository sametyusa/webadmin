 <?php
 error_reporting(E_ERROR);
 if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off"){
  //HTTP trafiğini HTTPS'e yönlendirir
  $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  header('HTTP/1.1 301 Moved Permanently');
  header('Location: ' . $redirect);
}
  session_start();
  session_regenerate_id();

  if(isset($_GET["getout"])) {
      session_destroy();
      header("Location: index.php");
  }
  function exportCSV($data) {
    ob_end_clean();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="WebServiceList.csv"');
    $df = fopen("php://output", 'w');
    fputcsv($df, array('ID', 'ComputerName', 'IP','Folder','File','appname','IPRestrictionInIIS','fullURL','DenizProdAccess','intertechClientAccess','internetAccess','Scope'),$delimiter = ",",$enclosure = '"', $mysql_null = true);
#      fputcsv($df, array_keys(reset($data)));
    foreach ($data as $row) {
      fputcsv($df, $row);
    }
    fclose($df);
    exit;
  }
?>
  
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <!-- use your own theme - SAMET YUSA -->
  <script src="../theme/vendor/jquery/jquery.min.js"></script>
  <script src="../DataTables/datatables.js"></script>
  <script src="../DataTables/DataTables-1.10.18/js/jquery.dataTables.js"></script>
  <style type="text/css">
  .error {
    margin: 10px 0;
    padding: 10px;
    border-radius: 3px 3px 3px 3px;
    color: #D8000C;
    background-color: #FFBABA;
  }
  </style>
  <title>Web Service Admin</title>
  <!-- use your own theme - SAMET YUSA -->

  <!-- Custom fonts for this template-->
  <link href="theme/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="theme/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <!-- Page level plugin CSS-->
  <link href="../DataTables/Buttons-1.5.6/css/buttons.jqueryui.css" rel="stylesheet">
  <!-- use your own theme - SAMET YUSA -->
  <!-- Custom styles for this template-->
  <link href="theme/css/sb-admin.css" rel="stylesheet">

</head>
<?php
if (isset($_SESSION["sessionid"])) {
  include "wsService.php";
  $getWhat = htmlentities($_GET["getwhat"], ENT_QUOTES);
  $countallresult = getWSList("all");
  $internetResult = getWSList("internet");

  if (isset($_GET["exportxls"])) {
    /*
    CSV çıktı üretir. 
    */
    $getwhat = htmlentities($_GET["getwhat"]);
    if ($getwhat=="internet") $result=getWSListAsCSV("internet");
    else $result=getWSListAsCSV("all");
    exportCSV($result);
   
  }
?>
<body id="page-top">

 <nav class="navbar navbar-expand navbar-light bg-light static-top">

<a class="navbar-brand mr-1">Web Service List</a>
<!-- Navbar Search -->
<form class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
  <div class="input-group">
   <!--  <input type="text" class="form-control" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
    <div class="input-group-append">
      <button class="btn btn-primary" type="button">
        <i class="fas fa-search"></i>
      </button> -->
    </div>
  </div>
</form>

<!-- Navbar -->
<ul class="navbar-nav ml-auto ml-md-0">
  
  <li class="nav-item dropdown no-arrow">
    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <?php echo $_SESSION["username"];?> <i class="fas fa-user-circle fa-fw"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
      <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">Logout</a>
    </div>
  </li>
</ul>

</nav>
  <div id="wrapper">

    <!-- Sidebar 
    <ul class="sidebar navbar-nav">
      
    </ul>-->

    <div id="content-wrapper">

      <div class="container-fluid">

        <!-- Breadcrumbs-->
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="#">Dashboard</a>
          </li>
          <li class="breadcrumb-item active">Overview</li>
        </ol>

        <!-- Icon Cards-->
        <div class="row">
          <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card text-white bg-primary o-hidden h-100">
              <div class="card-body">
                <div class="card-body-icon">
                  <i class="fas fa-fw fa-comments"></i>
                </div>
                <div class="mr-5"><?php echo $countallresult;?> Web Servisi keşfedildi</div>
              </div>
              <a class="card-footer text-white clearfix small z-1" href="?getwhat=all">
                <span class="float-left">Hepsini gör</span>
                <span class="float-right">
                  <i class="fas fa-angle-right"></i>
                </span>
              </a>
            </div>
       </div>  
          <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card text-white bg-warning o-hidden h-100">
              <div class="card-body">
                <div class="card-body-icon">
                  <i class="fas fa-fw fa-list"></i>
                </div>
                <div class="mr-5"><?php echo $internetResult; ?> Web Servisi İnternete Açık</div>
              </div>
             <a class="card-footer text-white clearfix small z-1" href="?getwhat=internet">
                <span class="float-left">Açık olanları gör</span>
                <span class="float-right">
                  <i class="fas fa-angle-right"></i>
                </span>
              </a>
            </div>
          </div>
          <!--
          <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card text-white bg-success o-hidden h-100">
              <div class="card-body">
                <div class="card-body-icon">
                  <i class="fas fa-fw fa-shopping-cart"></i>
                </div>
                <div class="mr-5">123 New Orders!</div>
              </div>
              <a class="card-footer text-white clearfix small z-1" href="#">
                <span class="float-left">View Details</span>
                <span class="float-right">
                  <i class="fas fa-angle-right"></i>
                </span>
              </a>
            </div>
          </div>
          <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card text-white bg-danger o-hidden h-100">
              <div class="card-body">
                <div class="card-body-icon">
                  <i class="fas fa-fw fa-life-ring"></i>
                </div>
                <div class="mr-5">13 New Tickets!</div>
              </div>
              <a class="card-footer text-white clearfix small z-1" href="#">
                <span class="float-left">View Details</span>
                <span class="float-right">
                  <i class="fas fa-angle-right"></i>
                </span>
              </a>
            </div>-->
          </div>
        </div>

    
        <!-- DataTables Example -->
        <div class="card mb-3">
          <div class="card-header">
            <i class="fas fa-table"></i>
            Web Service Listesi
            <form method="" method="POST">
            <input type="hidden" name="exportxls" value="1"> 
            <input type="hidden" name="getwhat" value="<?php echo $_GET['getwhat']; ?>"> 
            <input type="image" name="excelexport" value="ok" src="../ms_excel.png" alt="CSV export" title="CSVye aktar" align="right" width="32" height="32">
            </form>
            </div>
            
            <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">     
        <thead>
            <tr>
                <th>Tam URL</th>
                <th>Sunucu</th>
                <th>Sunucu IP</th>
                <th>Klasör</th>
                <th>Dosya</th>
                <th>Uygulama</th>
                <th>İntertech Client Erişimi</th>
                <th>Deniz Prod Erişimi</th>
                <th>İnternetten Erişim</th>
                <th>Kapsam</th>
            </tr>
        </thead>
        <!--<tfoot>
            <tr>
            <th>Tam URL</th>
                <th>Sunucu</th>
                <th>Sunucu IP</th>
                <th>Klasör</th>
                <th>Dosya</th>
                <th>Uygulama</th>
                <th>İntertech Client Erişimi</th>
                <th>Deniz Prod Erişimi</th>
                <th>İnternetten Erişim</th>
                <th>Kapsam</th>
            </tr>
        </tfoot>-->
    </table>
    </div>
    </div>
    </div>
    <script type="text/javascript">
      $(document).ready(function() {
          $('#dataTable').DataTable( {
              "processing": true,
              "serverSide": true,
              "ajax": 'wsService.php?getList=1&getwhat=<?php echo $getWhat; ?>'
          } );
      } );
      </script>
          <div class="card-footer small text-muted"></div>
        </div>

      </div>
      <!-- /.container-fluid -->

      <!-- Sticky Footer -->
      <footer class="sticky-footer">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
            <span>Copyright © Samet yusa </span>
          </div>
        </div>
      </footer>

    </div>
    <!-- /.content-wrapper -->

  </div>
  <!-- /#wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
          <a class="btn btn-primary" href="?getout=1">Logout</a>
        </div>
      </div>
    </div>
  </div>
              <?php } //oturum kontrolü bitti 
                    else {
                    

?>
<body class="bg-light">
<div class="container">
<div style="display: relative;margin-left: 34%;margin-top:5%; color:#000; font-size:22px; font-family:'Times New Roman', Times, serif;"><p>WEB SERVICE ADMINISTRATOR</p></div>

  <div class="card card-login mx-auto mt-5">
 
    
    <div class="card-header">Oturum Aç</div>
    <div class="card-body">
      <form method="POST" action="index.php">
        <div class="form-group">
          <div class="form-label-group">
            <input type="text" id="inputEmail" class="form-control" placeholder="Email address" required="required" name = "username" autofocus="autofocus">
            <label for="inputEmail">AD Kullanıcı Adı</label>
          </div>
        </div>
        <div class="form-group">
          <div class="form-label-group">
            <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required="required">
            <label for="inputPassword">Parola</label>
          </div>
        </div>
        <div class="form-group">
         
        </div>
        <input type="submit" class="btn btn-primary btn-block" value="Giriş">
      </form>
      <div class="text-center">
      <?php
      include "wsService.php";
      if(isset($_POST['username']) && isset($_POST['password'])){
                        $ldap = ldap_connect("ldap://your.ldap.domain");
                        ldap_set_option($ldap,LDAP_OPT_PROTOCOL_VERSION, 3);
                        $uname=htmlentities($_POST["username"]);
                        $pass=$_POST["password"];
                        $canUserLogIn = AuthUsers($uname);
                        if ($canUserLogIn==1){
                        if ($bind = ldap_bind($ldap, "yourdomain\\".$uname, $pass)) {
                          $_SESSION["sessionid"]=1;
                          $_SESSION["username"]=$uname;
                          header("Location: index.php");
                        } else {
                          echo "<br><p class='error'>Kullanıcı adı ya da parola yanlış</p>";
                        }
                      }
                      else echo "<p><br /><span class='error'>Erişim yetkiniz yok.</span><br /><br /><img src='../no-entry.jpg' width=300 height=200 alt='Access Denied'></p>";
                      } // login form kontrolü bitti
                      ?>
      </div>
    </div>
  </div>
</div>

<?php
            }
          ?>

          <!-- use your own theme -->
  <!-- Bootstrap core JavaScript-->
  <script src="theme/vendor/jquery/jquery.min.js"></script>
  <script src="theme/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="theme/vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Page level plugin JavaScript-->
  <script src="theme/vendor/chart.js/Chart.min.js"></script>
  <script src="theme/vendor/datatables/jquery.dataTables.js"></script>
  <script src="theme/vendor/datatables/dataTables.bootstrap4.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="theme/js/sb-admin.min.js"></script>

  <!-- Demo scripts for this page-->
  <script src="theme/js/demo/datatables-demo.js"></script>
  <script src="theme/js/demo/chart-area-demo.js"></script>
 
</body>

</html>
