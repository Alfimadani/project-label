<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

$username = isset($_GET['username']) ? trim($_GET['username']) : '';

if (empty($username)) {
    echo json_encode(['error' => 'Username / NIK tidak boleh kosong'], JSON_UNESCAPED_UNICODE);
    exit;
}

$sanitizedUser = preg_replace('/[^a-zA-Z0-9_\-\.\s]/', '', $username);

// Konfigurasi 3 domain
$domains = [
    [
        'name'   => 'obi.com',
        'server' => 'obi.com',
        'port'   => 389,
        'user'   => 'OBI\Helpdesktop',
        'pass'   => 'OBit#%78@',
        'baseDn' => 'DC=obi,DC=com'
    ],
    [
        'name'   => 'obfpt.com',
        'server' => 'obfpt.com',
        'port'   => 389,
        'user'   => 'OBFPT\Helpdesktop',
        'pass'   => 'OSTit#%78@',
        'baseDn' => 'DC=obfpt,DC=com'
    ],
    [
        'name'   => 'ad.lygend.com',
        'server' => 'ad.lygend.com',
        'port'   => 389,
        'user'   => 'ADLYGEND\administrator',
        'pass'   => 'LQzy90#&2!',
        'baseDn' => 'DC=ad,DC=lygend,DC=com'
    ]
];

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

function queryDomainMulti($config, $sanitizedUser)
{
    $ldapConn = @ldap_connect($config['server'], $config['port']);
    if (!$ldapConn) return [];

    ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

    $bind = @ldap_bind($ldapConn, $config['user'], $config['pass']);
    if (!$bind) {
        @ldap_close($ldapConn);
        return [];
    }

    // Menggunakan Wildcard (*) di akhir query NIK agar menangkap kelanjutan karakternya
    $filter = "(|(sAMAccountName=$sanitizedUser*)(employeeID=$sanitizedUser*)(cn=*$sanitizedUser*))";
    $search = @ldap_search($ldapConn, $config['baseDn'], $filter);

    $results = [];

    if ($search) {
        $entries = @ldap_get_entries($ldapConn, $search);
        @ldap_close($ldapConn);

        if ($entries && $entries['count'] > 0) {
            for ($i = 0; $i < $entries['count']; $i++) {
                $user = $entries[$i];
                $userData = [];

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

                if (!isset($userData['mail'])) {
                    $userData['mail'] = '-';
                }

                if (isset($userData['lastlogon'])) {
                    $userData['lastlogon_formatted'] = parseAdTimestamp($userData['lastlogon']);
                }
                if (isset($userData['pwdlastset'])) {
                    $userData['pwdlastset_formatted'] = parseAdTimestamp($userData['pwdlastset']);
                }

                $userData['source_domain'] = $config['name'];
                $results[] = $userData;
            }
        }
    } else {
        @ldap_close($ldapConn);
    }

    return $results;
}

$allResults = [];

// Loop pencarian ke semua domain dan gabungkan hasilnya
foreach ($domains as $domainConfig) {
    $domainResults = queryDomainMulti($domainConfig, $sanitizedUser);
    if (!empty($domainResults)) {
        $allResults = array_merge($allResults, $domainResults);
    }
}

if (empty($allResults)) {
    echo json_encode(['error' => "User dengan NIK/Kata kunci '$sanitizedUser' tidak ditemukan di domain mana pun."], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['count' => count($allResults), 'users' => $allResults], JSON_UNESCAPED_UNICODE);
exit;
