#$ErrorActionPreference = "silentlycontinue"
param(
    [String]$Zone,
    [String]$domain,
    [int]$StartLimit,
    [int]$StopLimit
)
$Body = @{
   geturl = 1
   domain = $domain
   startlimit = $StartLimit
   stoplimit = $StopLimit
   getWhat = "fullurl"
   
}
$LoginResponse = Invoke-WebRequest 'http://YOUR_WEB_SERVER/WebServiceListener.php' -Body $Body -Method 'POST'
$fullURL = $LoginResponse.AllElements |select outertext -ExpandProperty outertext |convertfrom-json |select url -ExpandProperty url
#
# Web servislerin erişim Durumunu kontrol eder
#
foreach ($myURL in $fullURL) {
    echo "Testing $myURL"
    try {

        $CheckWebService = Invoke-WebRequest "$myURL" -TimeoutSec 4 
        Write-Output "Success to $myURL"
        $accesStatus = 1
    }
    catch { Write-Output "Failed to access $myURL"
        $accesStatus = 0  }
    finally { }
    #if ($?) {
   # if ($CheckWebService.StatusCode -eq 200) {
        
    #}
    #else { 
        
    #}
    $Body = @{
        accessfrom = $Zone
        accessstatus = $accesStatus
        fullurl=$myURL
        update = 1
    }
    $UpdateRequest = Invoke-WebRequest 'http://YOUR_WEB_SERVER/WebServiceListener.php' -Body $Body -Method 'POST'
    $UpdateRequest.AllElements |select outertext -ExpandProperty outertext
    Start-Sleep -s 0.5
}