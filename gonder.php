<?php
$bot_token = "8761753927:AAFrVMhziZNflfozhQA6d1V1INQn7_iBi7A";
$chat_id = "6671499665";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Sadece POST kabul edilir']);
    exit;
}

$itiraf_text = isset($_POST['itiraf']) ? trim($_POST['itiraf']) : '';
if (empty($itiraf_text)) {
    echo json_encode(['success' => false, 'error' => 'İtiraf metni boş olamaz!']);
    exit;
}

$message = "🆕 YENİ İTİRAF\n\n📝 " . $itiraf_text . "\n\n🕐 " . date('d.m.Y H:i');

// Metin gönder
$url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
$data = ['chat_id' => $chat_id, 'text' => $message];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

$response = json_decode($result, true);

if ($response && $response['ok'] === true) {
    // Medya varsa gönder
    if (isset($_FILES['media']) && $_FILES['media']['error'] === 0 && $_FILES['media']['size'] <= 100 * 1024 * 1024) {
        $file_tmp = $_FILES['media']['tmp_name'];
        $file_name = $_FILES['media']['name'];
        $file_type = $_FILES['media']['type'];
        
        if (strpos($file_type, 'image') !== false) {
            $media_url = "https://api.telegram.org/bot{$bot_token}/sendPhoto";
            $field = 'photo';
        } elseif (strpos($file_type, 'video') !== false) {
            $media_url = "https://api.telegram.org/bot{$bot_token}/sendVideo";
            $field = 'video';
        } else {
            $media_url = "https://api.telegram.org/bot{$bot_token}/sendDocument";
            $field = 'document';
        }
        
        $post_fields = [
            'chat_id' => $chat_id,
            $field => new CURLFile($file_tmp, $file_type, $file_name)
        ];
        
        $ch2 = curl_init($media_url);
        curl_setopt($ch2, CURLOPT_POST, 1);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_TIMEOUT, 120);
        curl_exec($ch2);
        curl_close($ch2);
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Telegram hatası: ' . $result]);
}
?>
