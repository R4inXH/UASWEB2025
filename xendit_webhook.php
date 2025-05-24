<?php
require_once 'config.php';

function logWebhook($message) {
    $log = date('Y-m-d H:i:s') . " - " . $message . PHP_EOL;
    file_put_contents('webhook_log.txt', $log, FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    http_response_code(200);
    echo "Webhook endpoint is working! ✅<br>";
    echo "Current time: " . date('Y-m-d H:i:s') . "<br>";
    echo "URL accessed: " . $_SERVER['REQUEST_URI'];
    logWebhook("GET Test - Webhook endpoint accessed successfully");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    logWebhook("Webhook received: " . $input);
    
    $headers = getallheaders();
    logWebhook("Headers: " . json_encode($headers));
    
    if (!$data) {
        logWebhook("ERROR: Invalid JSON data");
        http_response_code(400);
        echo "Invalid JSON";
        exit();
    }
    
    try {
        $event = $data['event'] ?? '';
        $external_id = $data['external_id'] ?? '';
        
        logWebhook("Processing event: $event for external_id: $external_id");
        
        switch ($event) {
            case 'invoice.paid':
                handleInvoicePaid($data);
                break;
                
            case 'invoice.expired':
                handleInvoiceExpired($data);
                break;
                
            case 'invoice.failed':
                handleInvoiceFailed($data);
                break;
                
            default:
                logWebhook("Unknown event: $event");
        }
        
        http_response_code(200);
        echo "OK";
        
    } catch (Exception $e) {
        logWebhook("ERROR: " . $e->getMessage());
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
}

function handleInvoicePaid($data) {
    global $conn;
    
    $external_id = $data['external_id'];
    $invoice_id = $data['id'];
    $amount = $data['amount'];
    $paid_amount = $data['paid_amount'];
    $payment_method = $data['payment_method'] ?? '';
    
    logWebhook("Processing paid invoice: $invoice_id");
    
    $sql = "UPDATE transaksi SET 
            status_pembayaran = 'paid',
            payment_status_detail = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE xendit_external_id = ?";
    
    $status_detail = json_encode([
        'event' => 'invoice.paid',
        'invoice_id' => $invoice_id,
        'amount' => $amount,
        'paid_amount' => $paid_amount,
        'payment_method' => $payment_method,
        'paid_at' => $data['paid_at'] ?? date('Y-m-d H:i:s')
    ]);
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $status_detail, $external_id);
    
    if ($stmt->execute()) {
        logWebhook("SUCCESS: Payment status updated for $external_id");
        
        
    } else {
        logWebhook("ERROR: Failed to update payment status for $external_id");
        throw new Exception("Database update failed");
    }
}

function handleInvoiceExpired($data) {
    global $conn;
    
    $external_id = $data['external_id'];
    $invoice_id = $data['id'];
    
    logWebhook("Processing expired invoice: $invoice_id");
    
    $sql = "UPDATE transaksi SET 
            status_pembayaran = 'expired',
            payment_status_detail = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE xendit_external_id = ?";
    
    $status_detail = json_encode([
        'event' => 'invoice.expired',
        'invoice_id' => $invoice_id,
        'expired_at' => $data['expired_date'] ?? date('Y-m-d H:i:s')
    ]);
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $status_detail, $external_id);
    
    if ($stmt->execute()) {
        logWebhook("SUCCESS: Invoice expired status updated for $external_id");
    } else {
        logWebhook("ERROR: Failed to update expired status for $external_id");
        throw new Exception("Database update failed");
    }
}

function handleInvoiceFailed($data) {
    global $conn;
    
    $external_id = $data['external_id'];
    $invoice_id = $data['id'];
    $failure_reason = $data['failure_reason'] ?? 'Unknown';
    
    logWebhook("Processing failed invoice: $invoice_id - Reason: $failure_reason");
    
    $sql = "UPDATE transaksi SET 
            status_pembayaran = 'failed',
            payment_status_detail = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE xendit_external_id = ?";
    
    $status_detail = json_encode([
        'event' => 'invoice.failed',
        'invoice_id' => $invoice_id,
        'failure_reason' => $failure_reason,
        'failed_at' => date('Y-m-d H:i:s')
    ]);
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $status_detail, $external_id);
    
    if ($stmt->execute()) {
        logWebhook("SUCCESS: Invoice failed status updated for $external_id");
    } else {
        logWebhook("ERROR: Failed to update failed status for $external_id");
        throw new Exception("Database update failed");
    }
}
?>