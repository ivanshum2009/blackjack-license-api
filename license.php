<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// 防止直接訪問
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    die(json_encode(['error' => '只允許 POST 和 GET 請求']));
}

$action = $_GET['action'] ?? '';

// 簡單的記憶體存儲（使用文件）
// 注意：Vercel 的文件系統是只讀的，實際使用時應該用數據庫
$storage_file = '/tmp/license_storage.json';

// 讀取現有數據
function loadLicenses() {
    global $storage_file;
    if (file_exists($storage_file)) {
        $content = file_get_contents($storage_file);
        return json_decode($content, true) ?? [];
    }
    return [];
}

// 保存數據
function saveLicenses($licenses) {
    global $storage_file;
    file_put_contents($storage_file, json_encode($licenses));
}

// GET 請求 - 顯示 API 狀態
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === '') {
        echo json_encode([
            'status' => 'BlackJack License API is running',
            'version' => '1.0',
            'time' => date('Y-m-d H:i:s'),
            'actions' => ['bind', 'check'],
            'usage' => [
                'bind' => 'POST ?action=bind with {"license_key":"...", "device_id":"..."}',
                'check' => 'POST ?action=check with {"license_key":"...", "device_id":"..."}'
            ]
        ]);
        exit;
    }
}

$input = json_decode(file_get_contents('php://input'), true);
$licenses = loadLicenses();

if ($action === 'bind') {
    $license_key = $input['license_key'] ?? '';
    $device_id = $input['device_id'] ?? '';
    
    if (empty($license_key) || empty($device_id)) {
        http_response_code(400);
        echo json_encode(['error' => '缺少必要參數']);
        exit;
    }
    
    // 驗證授權碼格式
    if (!preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $license_key)) {
        http_response_code(400);
        echo json_encode(['error' => '授權碼格式錯誤']);
        exit;
    }
    
    // 檢查是否已綁定
    if (isset($licenses[$license_key])) {
        if ($licenses[$license_key]['device_id'] !== $device_id) {
            http_response_code(403);
            echo json_encode(['error' => '授權碼已綁定到其他裝置']);
            exit;
        }
        // 更新時間
        $licenses[$license_key]['last_seen'] = time();
    } else {
        // 新綁定
        $licenses[$license_key] = [
            'device_id' => $device_id,
            'last_seen' => time(),
            'created_at' => time(),
            'ip_address' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
    }
    
    saveLicenses($licenses);
    echo json_encode(['success' => true]);
    
} elseif ($action === 'check') {
    $license_key = $input['license_key'] ?? '';
    $device_id = $input['device_id'] ?? '';
    
    if (empty($license_key) || empty($device_id)) {
        http_response_code(400);
        echo json_encode(['valid' => false, 'error' => '缺少必要參數']);
        exit;
    }
    
    if (!isset($licenses[$license_key])) {
        echo json_encode(['valid' => false, 'error' => '授權碼未綁定']);
    } elseif ($licenses[$license_key]['device_id'] !== $device_id) {
        echo json_encode(['valid' => false, 'error' => '授權碼已綁定到其他裝置']);
    } else {
        // 更新最後檢查時間
        $licenses[$license_key]['last_seen'] = time();
        saveLicenses($licenses);
        echo json_encode(['valid' => true]);
    }
    
} else {
    http_response_code(400);
    echo json_encode(['error' => '無效的操作']);
}
?>
