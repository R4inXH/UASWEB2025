<?php

class XenditConfig {
    const API_URL = 'https://api.xendit.co';
    const SECRET_KEY = 'xnd_development_7tzoMRi7UMlDmewnrQOynEQvvCZwyJ2YyleNcz5ovZWVJYUAH4qLM09Z1MfFcXV';
    
    const WEBHOOK_TOKEN = 'pnGwUCrZk2E88gF2e9Ymn0oTo6k5uYl0ouKRprJ5Hb8ziI1k'; // Ganti dengan token webhook Anda
    
    const PAYMENT_METHODS = [
        'BANK_TRANSFER' => [
            'BCA', 'BNI', 'BRI', 'MANDIRI', 'PERMATA', 'BSI', 'CIMB'
        ],
        'E_WALLET' => [
            'OVO', 'DANA', 'LINKAJA', 'SHOPEEPAY', 'GOPAY'
        ],
        'RETAIL_OUTLET' => [
            'ALFAMART', 'INDOMARET'
        ],
        'QR_CODE' => [
            'QRIS'
        ]
    ];
    
    public static function getAuthHeader() {
        return 'Basic ' . base64_encode(self::SECRET_KEY . ':');
    }
    
    public static function makeApiCall($endpoint, $data = null, $method = 'POST') {
        $url = self::API_URL . $endpoint;
        
        $headers = [
            'Authorization: ' . self::getAuthHeader(),
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status_code' => $http_code,
            'response' => json_decode($response, true)
        ];
    }
}