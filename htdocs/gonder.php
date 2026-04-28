<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Telegram ayarları
$bot_token = "8761753927:AAFrVMhziZNflfozhQA6d1V1INQn7_iBi7A";
$chat_id = "6671499665";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Sadece POST istekleri kabul edilir']);
    exit;
}

$itiraf_text = isset($_POST['itiraf']) ? trim($_POST['itiraf']) : '';

if (empty($itiraf_text)) {
    echo json_encode(['success' => false, 'error' => 'İtiraf metni boş olamaz!']);
    exit;
}

// Mesajı hazırla
$message = "🆕 YENİ İTİRAF\n\n";
$message .= "📝 " . $itiraf_text;
$message .= "\n\n🕐 " . date('d.m.Y H:i');

// Metni gönder
$url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
$data = [
    'chat_id' => $chat_id,
    'text' => $message
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Telegram cevabını logla
file_put_contents('debug.log', 
    "TARIH: " . date('Y-m-d H:i:s') . "\n" .
    "HTTP: " . $http_code . "\n" .
    "CURL_ERR: " . $curl_error . "\n" .
    "RESPONSE: " . $result . "\n" .
    "MESAJ: " . $message . "\n\n",
    FILE_APPEND
);

$response = json_decode($result, true);

if ($response && $response['ok'] === true) {
    
    // Medya varsa gönder
    if (isset($_FILES['media']) && $_FILES['media']['error'] === 0) {
        $file_tmp = $_FILES['media']['tmp_name'];
        $file_name = $_FILES['media']['name'];
        $file_type = $_FILES['media']['type'];
        $file_size = $_FILES['media']['size'];
        
        if ($file_size <= 100 * 1024 * 1024) {
            if (strpos($file_type, 'image') !== false) {
                $media_url = "https://api.telegram.org/bot{$bot_token}/sendPhoto";
                $post_fields = [
                    'chat_id' => $chat_id,
                    'photo' => new CURLFile($file_tmp, $file_type, $file_name)
                ];
            } elseif (strpos($file_type, 'video') !== false) {
                $media_url = "https://api.telegram.org/bot{$bot_token}/sendVideo";
                $post_fields = [
                    'chat_id' => $chat_id,
                    'video' => new CURLFile($file_tmp, $file_type, $file_name)
                ];
            } else {
                $media_url = "https://api.telegram.org/bot{$bot_token}/sendDocument";
                $post_fields = [
                    'chat_id' => $chat_id,
                    'document' => new CURLFile($file_tmp, $file_type, $file_name)
                ];
            }
            
            $ch2 = curl_init($media_url);
            curl_setopt($ch2, CURLOPT_POST, 1);
            curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_fields);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch2, CURLOPT_TIMEOUT, 120);
            curl_exec($ch2);
            curl_close($ch2);
        }
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Telegram hatası: ' . ($curl_error ? $curl_error : $result)
    ]);
}
?>