# BlackJack License API

BlackJack 遊戲授權驗證 API，部署在 Vercel 上。

## 功能

- 授權碼綁定到裝置
- 防止多機器使用同一授權碼
- 簡單的 RESTful API

## API 端點

### GET /api/license.php
顯示 API 狀態和使用說明

### POST /api/license.php?action=bind
綁定授權碼到裝置

**請求體**:
```json
{
  "license_key": "XXXX-XXXX-XXXX-XXXX",
  "device_id": "device_identifier"
}
```

**回應**:
```json
{
  "success": true
}
```

### POST /api/license.php?action=check
檢查授權碼綁定狀態

**請求體**:
```json
{
  "license_key": "XXXX-XXXX-XXXX-XXXX",
  "device_id": "device_identifier"
}
```

**回應**:
```json
{
  "valid": true
}
```

## 部署

1. Fork 這個 Repository
2. 連接到 Vercel
3. 自動部署

## 本地測試

```bash
# 測試 API 狀態
curl https://your-project.vercel.app/api/license.php

# 測試綁定
curl -X POST "https://your-project.vercel.app/api/license.php?action=bind" \
     -H "Content-Type: application/json" \
     -d '{"license_key":"TEST-1234-5678-ABCD","device_id":"test123"}'

# 測試檢查
curl -X POST "https://your-project.vercel.app/api/license.php?action=check" \
     -H "Content-Type: application/json" \
     -d '{"license_key":"TEST-1234-5678-ABCD","device_id":"test123"}'
```

## 注意事項

- 此版本使用臨時文件存儲，重啟後數據會丟失
- 生產環境建議使用數據庫（如 PlanetScale, MongoDB Atlas）
- 免費版本有使用限制
