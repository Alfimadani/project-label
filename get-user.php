<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

$username = isset($_GET['username']) ? trim($_GET['username']) : '';

if (empty($username)) {
    echo json_encode(['error' => 'Username tidak boleh kosong'], JSON_UNESCAPED_UNICODE);
    exit;
}

$sanitizedUser = preg_replace('/[^a-zA-Z0-9_\-\.\s]/', '', $username);

$psScript = <<<POWERSHELL
\$ProgressPreference = 'SilentlyContinue';
\$ErrorActionPreference = 'Stop';

try {
    Import-Module ActiveDirectory;
    \$user = Get-ADUser -Identity "$sanitizedUser" -Properties *;

    if (\$user) {
        \$userObj = [PSCustomObject]@{
            SamAccountName    = \$user.SamAccountName
            DisplayName       = \$user.DisplayName
            Title             = \$user.Title
            Department        = \$user.Department
            EmailAddress      = \$user.EmailAddress
            Enabled           = \$user.Enabled
            DistinguishedName = \$user.DistinguishedName
            UserPrincipalName = \$user.UserPrincipalName
            SID               = if (\$user.SID) { \$user.SID.Value } else { "-" }
            PasswordLastSet   = if (\$user.PasswordLastSet) { \$user.PasswordLastSet.ToString("yyyy-MM-dd HH:mm:ss") } else { "-" }
            WhenCreated       = if (\$user.whenCreated) { \$user.whenCreated.ToString("yyyy-MM-dd HH:mm:ss") } else { "-" }
            LastLogonDate     = if (\$user.LastLogonDate) { \$user.LastLogonDate.ToString("yyyy-MM-dd HH:mm:ss") } else { "-" }
            EmployeeID        = \$user.EmployeeID
            Manager           = \$user.Manager
            MemberOf          = \$user.MemberOf
            DirectReports     = \$user.DirectReports
        }
        \$userObj | ConvertTo-Json -Compress -Depth 4
    } else {
        Write-Output "USER_NOT_FOUND"
    }
} catch {
    \$cleanErr = \$_ .Exception.Message -replace '[\r\n"]', ' '
    Write-Output "PS_ERROR: " + \$cleanErr
}
POWERSHELL;

$encodedCommand = base64_encode(mb_convert_encoding($psScript, 'UTF-16LE', 'UTF-8'));
$pwshPath = 'C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe';

$command = "\"$pwshPath\" -NoProfile -ExecutionPolicy Bypass -EncodedCommand $encodedCommand 2>&1";
$output = trim(shell_exec($command));

if (empty($output)) {
    echo json_encode(['error' => 'Tidak ada respon dari PowerShell server.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Menangkap error dari PowerShell
if (strpos($output, 'PS_ERROR:') !== false) {
    $errMessage = trim(str_replace('PS_ERROR:', '', $output));
    echo json_encode(['error' => "Akses Ditolak / Error AD: $errMessage"], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($output === 'USER_NOT_FOUND') {
    echo json_encode(['error' => "User '$sanitizedUser' tidak ditemukan di Active Directory."], JSON_UNESCAPED_UNICODE);
    exit;
}

$decoded = json_decode($output);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => "Gagal memproses data dari AD. Output: $output"], JSON_UNESCAPED_UNICODE);
    exit;
}

echo $output;
