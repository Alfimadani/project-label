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

// Konfigurasi AD LDAP
$ldapServer = 'obi.com';
$ldapPort   = 389;
$domainUser = 'OBI\Helpdesktop';
$domainPass = 'OBit#%78@';
$baseDn     = 'DC=obi,DC=com';

$ldapConn = ldap_connect($ldapServer, $ldapPort);

if (!$ldapConn) {
    echo json_encode(['error' => 'Gagal terhubung ke Domain Controller LDAP.'], JSON_UNESCAPED_UNICODE);
    exit;
}

ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

$bind = @ldap_bind($ldapConn, $domainUser, $domainPass);

if (!$bind) {
    $ldapErr = ldap_error($ldapConn);
    echo json_encode(['error' => "Gagal Authenticated Bind: $ldapErr"], JSON_UNESCAPED_UNICODE);
    exit;
}

$filter = "(sAMAccountName=$sanitizedUser)";
$search = @ldap_search($ldapConn, $baseDn, $filter);

if (!$search) {
    echo json_encode(['error' => 'Gagal melakukan ldap_search pada Base DN.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$entries = ldap_get_entries($ldapConn, $search);

if (!$entries || $entries['count'] === 0) {
    echo json_encode(['error' => "User '$sanitizedUser' tidak ditemukan di Active Directory."], JSON_UNESCAPED_UNICODE);
    exit;
}

function cleanLdapValue($value)
{
    if (is_array($value)) {
        unset($value['count']);
        return array_map('cleanLdapValue', array_values($value));
    }
    if (!mb_check_encoding($value, 'UTF-8')) {
        return utf8_encode($value);
    }
    return $value;
}

function parseAdTimestamp($filetime)
{
    if (!$filetime || $filetime == "0" || $filetime == "9223372036854775807") return "-";
    $winTicks = (float)$filetime;
    $unixTimestamp = ($winTicks / 10000000) - 11644473600;
    return date('Y-m-d H:i:s', $unixTimestamp);
}

$userData = [];
$user = $entries[0];

foreach ($user as $key => $val) {
    if (is_numeric($key)) continue;

    if (isset($val['count'])) {
        if ($val['count'] == 1) {
            $userData[$key] = cleanLdapValue($val[0]);
        } else {
            $userData[$key] = cleanLdapValue($val);
        }
    }
}

// Pastikan key 'mail' diset meskipun kosong di AD
if (!isset($userData['mail'])) {
    $userData['mail'] = '-';
}

if (isset($userData['lastlogon'])) {
    $userData['lastlogon_formatted'] = parseAdTimestamp($userData['lastlogon']);
}
if (isset($userData['pwdlastset'])) {
    $userData['pwdlastset_formatted'] = parseAdTimestamp($userData['pwdlastset']);
}

ldap_close($ldapConn);

echo json_encode($userData, JSON_UNESCAPED_UNICODE);
exit;
