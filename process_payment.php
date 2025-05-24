<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); 

require_once 'config.php';
require_once 'xendit_config.php';
session_start();

header('Content-Type: application/json');

function logError($message) {
    error_log("[PAYMENT ERROR] " . date('Y-m-d H:i:s') . " - " . $message);
}

function jsonResponse($success, $message, $data = []) {
    $response = array_merge([
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ], $data);
    
    echo json_encode($response);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    jsonResponse(false, 'Method not allowed');
}

try {
    if (!isset($_POST['transaksi_id']) || !isset($_POST['payment_method'])) {
        jsonResponse(false, 'Missing required parameters');
    }
    
    $transaksi_id = intval($_POST['transaksi_id']);
    $payment_method = trim($_POST['payment_method']);
    
    logError("Processing payment - Transaction ID: $transaksi_id, Method: $payment_method");
    
    if ($transaksi_id <= 0) {
        jsonResponse(false, 'Invalid transaction ID');
    }
    
    $sql = "SELECT t.*, p.nama_lengkap, p.email, p.telepon 
            FROM transaksi t 
            JOIN pelanggan p ON t.pelanggan_id = p.id 
            WHERE t.id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        logError("Database prepare error: " . $conn->error);
        jsonResponse(false, 'Database error occurred');
    }
    
    $stmt->bind_param("i", $transaksi_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $transaksi = $result->fetch_assoc();
    
    if (!$transaksi) {
        logError("Transaction not found: ID $transaksi_id");
        jsonResponse(false, 'Transaction not found');
    }
    
    logError("Transaction data: " . json_encode($transaksi));
    
    $amount = intval($transaksi['total_harga']);
    if ($amount <= 0) {
        logError("Invalid amount: $amount for transaction $transaksi_id");
        
        $fix_sql = "UPDATE transaksi t
                    JOIN (
                        SELECT transaksi_id, SUM(subtotal) as total_correct
                        FROM detail_transaksi 
                        WHERE transaksi_id = ?
                        GROUP BY transaksi_id
                    ) dt ON t.id = dt.transaksi_id
                    SET t.total_harga = dt.total_correct
                    WHERE t.id = ?";
        
        $fix_stmt = $conn->prepare($fix_sql);
        $fix_stmt->bind_param("ii", $transaksi_id, $transaksi_id);
        
        if ($fix_stmt->execute()) {
            logError("Amount auto-fixed for transaction $transaksi_id");
            
            $stmt->execute();
            $result = $stmt->get_result();
            $transaksi = $result->fetch_assoc();
            $amount = intval($transaksi['total_harga']);
            
            if ($amount <= 0) {
                jsonResponse(false, 'Transaction amount is invalid and cannot be fixed automatically. Please contact support.');
            }
        } else {
            jsonResponse(false, 'Transaction amount is invalid (Rp 0). Please try creating a new order.');
        }
    }
    
    logError("Validated amount: $amount");
    
    $external_id = 'LAMPUNG_WALK_' . $transaksi['kode_transaksi'] . '_' . time();
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $base_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
    
    $invoice_data = [
        'external_id' => $external_id,
        'amount' => $amount,
        'description' => 'Tiket Lampung Walk - ' . $transaksi['kode_transaksi'],
        'invoice_duration' => 86400, // 24 hours
        'customer' => [
            'given_names' => $transaksi['nama_lengkap'],
            'email' => $transaksi['email']
        ],
        'customer_notification_preference' => [
            'invoice_created' => ['email'],
            'invoice_reminder' => ['email'],
            'invoice_paid' => ['email']
        ],
        'success_redirect_url' => $base_url . '/payment_success.php?id=' . $transaksi_id,
        'failure_redirect_url' => $base_url . '/payment_failed.php?id=' . $transaksi_id,
        'currency' => 'IDR',
        'items' => []
    ];
    
    $sql_detail = "SELECT dt.*, w.nama_wahana 
                   FROM detail_transaksi dt 
                   JOIN wahana w ON dt.wahana_id = w.id 
                   WHERE dt.transaksi_id = ?";
    $stmt_detail = $conn->prepare($sql_detail);
    $stmt_detail->bind_param("i", $transaksi_id);
    $stmt_detail->execute();
    $result_detail = $stmt_detail->get_result();
    
    $total_check = 0;
    while ($row = $result_detail->fetch_assoc()) {
        $item_price = intval($row['harga_satuan']);
        $item_qty = intval($row['jumlah_tiket']);
        $item_total = $item_price * $item_qty;
        
        $invoice_data['items'][] = [
            'name' => $row['nama_wahana'],
            'quantity' => $item_qty,
            'price' => $item_price,
            'category' => 'Tiket Wahana'
        ];
        
        $total_check += $item_total;
    }
    
    if ($total_check != $amount) {
        logError("Items total mismatch: Expected $amount, Got $total_check");
    }
    
    $payment_methods = [];
    
    switch ($payment_method) {
        case 'bank_transfer':
            if (isset($_POST['bank_code']) && !empty($_POST['bank_code'])) {
                $payment_methods[] = strtoupper($_POST['bank_code']);
            } else {
                $payment_methods = XenditConfig::PAYMENT_METHODS['BANK_TRANSFER'];
            }
            break;
            
        case 'e_wallet':
            if (isset($_POST['ewallet_type']) && !empty($_POST['ewallet_type'])) {
                $payment_methods[] = strtoupper($_POST['ewallet_type']);
            } else {
                $payment_methods = XenditConfig::PAYMENT_METHODS['E_WALLET'];
            }
            break;
            
        case 'retail_outlet':
            if (isset($_POST['retail_outlet_name']) && !empty($_POST['retail_outlet_name'])) {
                $payment_methods[] = strtoupper($_POST['retail_outlet_name']);
            } else {
                $payment_methods = XenditConfig::PAYMENT_METHODS['RETAIL_OUTLET'];
            }
            break;
            
        case 'qr_code':
            $payment_methods[] = 'QRIS';
            break;
            
        default:
            jsonResponse(false, "Invalid payment method: $payment_method");
    }
    
    $invoice_data['payment_methods'] = $payment_methods;
    
    logError("Invoice data prepared: " . json_encode($invoice_data));
    
    $response = XenditConfig::makeApiCall('/v2/invoices', $invoice_data);
    
    logError("Xendit API response: Status " . $response['status_code'] . " - " . json_encode($response['response']));
    
    if (!in_array($response['status_code'], [200, 201])) {
        $error_msg = "Xendit API Error: " . json_encode($response['response']);
        logError($error_msg);
        
        $user_message = 'Failed to create payment invoice';
        if (isset($response['response']['message'])) {
            $user_message .= ': ' . $response['response']['message'];
        }
        
        jsonResponse(false, $user_message, [
            'xendit_error' => $response['response'],
            'status_code' => $response['status_code']
        ]);
    }
    
    $invoice = $response['response'];
    
    $invoice_url = $invoice['invoice_url'] ?? null;
    if (!$invoice_url && isset($invoice['id'])) {
        $invoice_url = "https://checkout.xendit.co/web/" . $invoice['id'];
        logError("Invoice URL not provided, constructing manual URL: $invoice_url");
    }
    
    if (!$invoice_url) {
        logError("No invoice URL available in response");
        jsonResponse(false, 'Payment invoice created but no payment URL available');
    }
    
    $sql_update = "UPDATE transaksi SET 
                   xendit_invoice_id = ?, 
                   xendit_invoice_url = ?, 
                   xendit_external_id = ?,
                   metode_pembayaran = ?,
                   status_pembayaran = 'processing',
                   updated_at = CURRENT_TIMESTAMP
                   WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    
    if (!$stmt_update) {
        logError("Update prepare error: " . $conn->error);
        jsonResponse(false, 'Database update error');
    }
    
    $stmt_update->bind_param("ssssi", 
        $invoice['id'], 
        $invoice_url, 
        $external_id,
        $payment_method,
        $transaksi_id
    );
    
    if (!$stmt_update->execute()) {
        logError("Update execute error: " . $stmt_update->error);
        jsonResponse(false, 'Failed to update transaction');
    }
    
    logError("Transaction updated successfully - Invoice ID: " . $invoice['id']);
    
    jsonResponse(true, 'Invoice created successfully', [
        'invoice_id' => $invoice['id'],
        'invoice_url' => $invoice_url,
        'external_id' => $external_id,
        'transaksi_id' => $transaksi_id,
        'amount' => $amount
    ]);
    
} catch (Exception $e) {
    logError("Exception caught: " . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine());
    jsonResponse(false, 'An unexpected error occurred: ' . $e->getMessage());
} catch (Error $e) {
    logError("Fatal error caught: " . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine());
    jsonResponse(false, 'A system error occurred. Please contact support.');
}
?>