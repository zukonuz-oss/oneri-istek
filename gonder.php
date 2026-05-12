<?php
$bot_token = "8761753927:AAFrVMhziZNflfozhQA6d1V1INQn7_iBi7A";
$chat_id = "6671499665";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Sadece POST kabul edilir']);
    exit;
}

$öneri_text = isset($_POST['öneri']) ? trim($_POST['öneri']) : '';
if (empty($öneri_text)) {
    echo json_encode(['success' => false, 'error' => 'Öneri • İstek metni boş olamaz!']);
    exit;
}


$message = "🆕 YENİ ÖNERİ • İSTEK\n\n📝 " . $öneri_text . "\n\n🕐 " . date('d.m.Y H:i');

$url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
$data = ['chat_id' => $chat_id, 'text' => $message];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$result = curl_exec($ch);
curl_close($ch);

if (isset($_FILES['media']) && $_FILES['media']['error'] === 0) {
    
    $file_tmp = $_FILES['media']['tmp_name'];
    $file_name = $_FILES['media']['name'];
    $file_type = $_FILES['media']['type'];
    $file_size = $_FILES['media']['size'];
    
    if ($file_size > 100 * 1024 * 1024) {
        echo json_encode(['success' => true, 'warning' => 'Dosya 100MB üzeri']);
        exit;
    }
    
    if (strpos($file_type, 'video') !== false) {
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
    
    $ch2 = curl_init();
    curl_setopt($ch2, CURLOPT_URL, $media_url);
    curl_setopt($ch2, CURLOPT_POST, 1);
    curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch2, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    $media_result = curl_exec($ch2);
    $curl_error = curl_error($ch2);
    curl_close($ch2);
    
    if ($curl_error) {
        echo json_encode(['success' => false, 'error' => 'Medya hatası: ' . $curl_error]);
        exit;
    }
}

echo json_encode(['success' => true]);
?>
