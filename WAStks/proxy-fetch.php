<?php
// proxy-fetch.php
if (isset($_GET['get_remote_manifest'])) {
    $collection = urldecode($_GET['get_remote_manifest']);
    
    // Regex to check for a valid IPv4 address
    $ipRegex = '/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/';
    $isNumericIP = preg_match($ipRegex, $collection);

    $protocol = $isNumericIP ? 'http' : 'https';
    $portSuffix = $isNumericIP ? ':8269' : '';
    $url = "$protocol://$collection$portSuffix/fetch.php?get_manifest=1";
    
    $cookieValue = $_COOKIE['owner_auth'] ?? '';

    // --- Start cURL Update ---
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    // Pass the authentication cookie
    curl_setopt($ch, CURLOPT_COOKIE, "owner_auth=" . $cookieValue);

    // Identity and Security
    curl_setopt($ch, CURLOPT_USERAGENT, 'WAStks-Proxy/1.0');
    
    // CRITICAL: Bypasses SSL issues if SYSTEM doesn't trust zrok certs
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $result = curl_exec($ch);
    $errorMsg = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close($ch);
    // --- End cURL Update ---

    if ($result === false || $httpCode >= 400) {
        http_response_code(502);
        header('Content-Type: application/json');
        echo json_encode([
            "error" => "Could not reach remote host",
            "attempted_url" => $url,
            "http_code" => $httpCode,
            "curl_error" => $errorMsg ?: "Unknown Error"
        ]);
        exit;
    }
    
    echo $result;
    exit;
}
?>