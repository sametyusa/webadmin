# 
# Web Servislerin Listesini Sunucuya Yükler
#


foreach ($data in $input) {
    $Body = @{
        hostname = $data.ComputerName
        ip = $data.IP
        folder = $data.Folder
        file = $data.File
        appname = $data.AppName
        iprestriction = $data.IPRestrictionInIIS
      #  accessfrom = 'interteasdchclient'
      #  accessstatus =0
        fullurl=$data.fullURL
        add = 1
    }
    
    $LoginResponse = Invoke-WebRequest 'http://YOUR_WEB_SERVER/WebServiceListener.php' -Body $Body -Method 'POST'
    echo $data.fullURL 
    $LoginResponse.AllElements |select outertext -ExpandProperty outertext

}