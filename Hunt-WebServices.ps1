#
# Hunt-WebServices: IIS sunucularda *.asmx dosyalarını bulur, DB'ye yükler
#
#$ErrorActionPreference = "silentlycontinue"
param(
    [Parameter(Mandatory=$True)]
    [string]$ComputerList,
    [Parameter(Mandatory=$True)]
    [System.Management.Automation.PSCredential]$Credential
    )
$MyList = import-csv $ComputerList
foreach ($pc in $MyList) {
    if (test-wsman $pc.ComputerName) {
        Invoke-Command -ComputerName $pc.ComputerName -FilePath .\get-webservicelist.ps1 -Credential $Credential | .\Upload-WebServiceList.ps1
        Start-Sleep -Seconds 0.5
    }
    else { 
        $pcname = $pc.ComputerName
        Write-Output "Cannot access to $pcname" 
    }
}
 