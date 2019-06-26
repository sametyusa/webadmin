#$ErrorActionPreference = "silentlycontinue"
if(get-command Get-WindowsFeature -ErrorAction SilentlyContinue) {
    $IsIISInstalled = (Get-WindowsFeature Web-Server).Installed
}
else {
    $IsIISInstalled = test-path "HKLM:\Software\Microsoft\Inetstp"
}
#$IISFolder = Get-Itemproperty -path HKLM:\SOFTWARE\Microsoft\INetStp -Name InstallPath | select InstallPath -ExpandProperty InstallPath

if ($IsIISInstalled -eq $true) {
    $SunucuAdi = Get-WMIObject –Class Win32_Bios | Select PSComputername -ExpandProperty PSComputerName
    $IP = Resolve-DnsNAme $SunucuAdi -Type A -NoHostsFile | where -Property IPAddress -NotLike "169.254*" | select IPAddress -ExpandProperty IPAddress
  
    $WebSiteList =  get-WebSite | select name -ExpandProperty Name
    $ServiceList = @()
   
    foreach ($site in $WebSiteList) {
        $AppFolder = Get-WebApplication -Site $site -ErrorAction SilentlyContinue | select physicalpath,path -ExpandProperty physicalpath
        $vpath = Get-WebApplication -Site $site  -ErrorAction SilentlyContinue | select path -ExpandProperty path
         
        foreach ($directory in $AppFolder){
       
            $file = Get-ChildItem -Path $directory -Include '*.asmx', '*.svc' -Recurse -ErrorAction SilentlyContinue| Select Name,Directory 
            if ($file -ne $null) {
               foreach ($found in $file) {
                $vPath =  $directory.Path # Uygulamanın IIS'teki adı
                $appPath = $directory.PhysicalPath #Uygulamanın çalıştığı klasör
                #$fullPath = $found.Directory #Servis dosyalarının tam yolu
                $fullPath= $found.Directory.FullName
          
                #$splitted = Split-Path $fullPath -NoQualifier
                $convertedPath = $splitted -replace "\\", "/"

                $IPRest = Get-WebConfigurationProperty -Filter 'system.webServer/security/ipSecurity' -PSPath 'IIS:\'  -Name 'allowUnlisted' -Location "$site/$vPath" -ErrorAction SilentlyContinue | select Value
                $Dosya = $found.Name 
           
                #$IISBinding = get-IISsite $site | select Bindings -ExpandProperty Bindings | where {$_.protocol -eq 'http' -or $_.protocol -eq 'https'} | select protocol,bindinginformation
               
                $IISBinding = get-website $site | select Bindings -ExpandProperty Bindings | select Collection -ExpandProperty Collection | where {$_.protocol -eq 'http' -or $_.protocol -eq 'https'} | select protocol,bindinginformation
                $portUnSplit = $IISBinding | select bindinginformation  -ExpandProperty bindinginformation
                $port = $portUnSplit.Split(':')
                $protocol = $IISBinding | select protocol -ExpandProperty protocol
                
                if($port[0] -eq '*') { $dnsname = $SunucuAdi }
                else {$dnsname = $port[0]}
                
                $realport = $port[1]
                if ($protocol -is [system.array]) {$firstProtocol = $protocol[0]}
                else { $firstProtocol = $protocol}

                if ($fullPath -ne $appPath -and ($fullpath -like "*$appPath*")) {
                     $splittedFull = Split-Path $fullPath -NoQualifier
                     $myuripath = $splittedFull.Replace($appPath,$vpath) -replace "\\", "/"
                     $explode = $myuripath.Split("/")
                     $vpathFormat = $vpath -replace "/",""
                     $AppIndexInArray = $explode.IndexOf($vpathFormat)
                     $uripath = ""
                     for ($i = $AppIndexInArray;$i -le $explode.Length;$i++){
                         $uripath += "/"+ $explode[$i] 
                     }
                    
                     
                 }
                 else {
                    $uripath = $vpath
                    
                 }

                $fullURL= "$firstProtocol`://$dnsname`:$realport$uripath/$Dosya"
                $myobj = New-Object -TypeName PSObject
                if ($IPRest.value -eq $true) { $Restriction = $false}
                elseif ($IPRest.value -eq $false) { $Restriction = $true}
                else { $Restriction = $null}
                Add-Member -InputObject $myobj -MemberType 'NoteProperty' -Name 'ComputerName' -Value $SunucuAdi
                Add-Member -InputObject $myobj -MemberType 'NoteProperty' -Name 'IP' -Value $IP
                Add-Member -InputObject $myobj -MemberType 'NoteProperty' -Name 'Folder' -Value $fullpath
                Add-Member -InputObject $myobj -MemberType 'NoteProperty' -Name 'File' -Value $Dosya
                Add-Member -InputObject $myobj -MemberType 'NoteProperty' -Name 'AppName' -Value $vPath
                Add-Member -InputObject $myobj -MemberType 'NoteProperty' -Name 'IPRestrictionInIIS' -Value $Restriction
                Add-Member -InputObject $myobj -MemberType 'NoteProperty' -Name 'FullURL' -Value $fullURL
                Add-Member -InputObject $myobj -MemberType 'NoteProperty' -Name 'IsIt' -Value $isit
                Add-Member -InputObject $myobj -MemberType 'NoteProperty' -Name 'AccessFromDenizbankProd' -Value ""
                $ServiceList += $myobj
               
                }
                #echo "$SunucuAdi,$IP,$directory,$Dosya,$vPath" |fl 
              }  

        } 
     }  
   $ServiceList
}