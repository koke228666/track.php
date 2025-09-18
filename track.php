<?php

/*
you can obtain those using this js script on track24.ru/.net


alert(`$${(new URL(window.location.href)).hostname.split('.').splice(-1).join('.').toLowerCase()}_cp_hash = "${cp}";
$${(new URL(window.location.href)).hostname.split('.').splice(-1).join('.').toLowerCase()}_tracking_key = "${(typeof trackingKey !== "undefined" ? trackingKey : key)}";
$${(new URL(window.location.href)).hostname.split('.').splice(-1).join('.').toUpperCase()}_TRACKING_URL = "${window.location.protocol + "//" + window.location.hostname + trackingUrl}";`); 

*/

$net_cp_hash = "-";
$net_tracking_key = "-";
$NET_TRACKING_URL = "-";

$ru_cp_hash = "-";
$ru_tracking_key = "-";
$RU_TRACKING_URL = "-";

function evp_kdf($password, $salt, $key_size = 32, $iv_size = 16, $iterations = 1, $hashfunc = "md5") {
    $target_bytes = $key_size + $iv_size;
    $derived = "";
    $block = "";
    while (strlen($derived) < $target_bytes) {
        if ($block) {
            $block = hash($hashfunc, $block . $password . $salt, true);
        } else {
            $block = hash($hashfunc, $password . $salt, true);
        }
        for ($i = 1; $i < $iterations; $i++) {
            $block = hash($hashfunc, $block, true);
        }
        $derived .= $block;
    }
    $key = substr($derived, 0, $key_size);
    $iv = substr($derived, $key_size, $iv_size);
    return [$key, $iv];
}

function decrypt_cryptojs_aes($encrypted_json, $password) {
    $ct = base64_decode($encrypted_json["ct"]);
    $iv = hex2bin($encrypted_json["iv"]);
    $salt = hex2bin($encrypted_json["s"]);

    list($key, $_iv) = evp_kdf($password, $salt, 32, 16);

    $plaintext = openssl_decrypt($ct, "aes-256-cbc", $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

    $pad_len = ord(substr($plaintext, -1));
    $plaintext = substr($plaintext, 0, -$pad_len);

    return $plaintext;
}

function get_tracking_data($track_code, $selected_service = "", $lng = "ru", $client_ip = "0.0.0.0", $useragent = null, $domain = "net") {
    global $net_tracking_key, $ru_tracking_key, $NET_TRACKING_URL, $RU_TRACKING_URL, $net_cp_hash, $ru_cp_hash;
	
	$tracking_key = $net_tracking_key;
	$TRACKING_URL = $NET_TRACKING_URL;
	$cp_hash = $net_cp_hash;
	
	if (strtolower($domain) == "ru") {
		$tracking_key = $ru_tracking_key;
		$TRACKING_URL = $RU_TRACKING_URL;
		$cp_hash = $ru_cp_hash;
	}

    $payload = [
        "code" => $track_code,
        "selectedService" => $selected_service,
        "lng" => $lng,
        "type" => "cache",
        "key" => $tracking_key,
        "clientIp" => $client_ip,
        "uuid" => uniqid()
    ];

    $headers = "User-Agent: " . ($useragent ?: "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0") . "\r\n" .
               "Content-type: application/x-www-form-urlencoded\r\n";

    $context = stream_context_create([
        "http" => [
            "method"  => "POST",
            "header"  => $headers,
            "content" => http_build_query($payload),
            "timeout" => 15
        ]
    ]);

    $response = @file_get_contents($TRACKING_URL, false, $context);

    if ($response === false) {
        return ["status" => "fetch error"];
    }

    $data = json_decode($response, true);
    if (!$data) {
        return ["status" => "failed to parse JSON"];
    }

    if (isset($data["ct"])) {
        $decrypted = decrypt_cryptojs_aes($data, $cp_hash);
        return json_decode($decrypted, true);;
    }

    return $data;
}

$trackcode = $_GET["code"] ?? null;
//$custom_useragent = $_GET["useragent"] ?? "";
$custom_useragent = "";
$service = $_GET["service"] ?? "";
if ($service == "auto") {$service = "";}
$language = $_GET["language"] ?? "en";
$domain = $_GET["domain"] ?? "net";

header("Content-Type: application/json; charset=utf-8");

if (!$trackcode) {
    echo json_encode(["status" => "no track code provided"]);
    exit;
}

$result = get_tracking_data($trackcode, $service, $language, "0.0.0.0", $custom_useragent, $domain);
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
