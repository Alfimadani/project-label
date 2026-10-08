<?php
// Aktifkan reporting sementara untuk debugging jika terjadi error
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    echo json_encode(['error' => 'Gagal terhubung ke server Domain Controller LDAP.'], JSON_UNESCAPED_UNICODE);
    exit;
}

ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

// Binding Otentikasi
$bind = @ldap_bind($ldapConn, $domainUser, $domainPass);

if (!$bind) {
    $ldapErr = ldap_error($ldapConn);
    echo json_encode(['error' => "Gagal Authenticated Bind: $ldapErr. Cek kembali username/password service account."], JSON_UNESCAPED_UNICODE);
    exit;
}

// Query User Active Directory
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

// Fungsi pembantu untuk konversi Safe UTF-8 / String Handling
function cleanLdapValue($value)
{
    if (is_array($value)) {
        unset($value['count']);
        return array_map('cleanLdapValue', array_values($value));
    }
    // Konversi encoding ke UTF-8 jika bukan string murni UTF-8
    if (!mb_check_encoding($value, 'UTF-8')) {
        return utf8_encode($value);
    }
    return $value;
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

ldap_close($ldapConn);

// Output JSON dengan proteksi error encoding
$jsonOutput = json_encode($userData, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

if ($jsonOutput === false) {
    echo json_encode(['error' => 'JSON Encode Error: ' . json_last_error_msg()], JSON_UNESCAPED_UNICODE);
} else {
    echo $jsonOutput;
}
