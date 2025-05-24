<?php
require_once 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['user_id'])) {
        $pelanggan_id = $_SESSION['user_id'];
        $nama = $_SESSION['user_name'];
        $email = $_SESSION['user_email'];
        $telepon = $_POST['telepon'];
    } else {
        $nama = $conn->real_escape_string($_POST['nama']);
        $email = $conn->real_escape_string($_POST['email']);
        $telepon = $conn->real_escape_string($_POST['telepon']);
        
        $sql_check = "SELECT id FROM pelanggan WHERE email = ?";
        $stmt = $conn->prepare($sql_check);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $pelanggan = $result->fetch_assoc();
            $pelanggan_id = $pelanggan['id'];
        } else {
            $sql_pelanggan = "INSERT INTO pelanggan (nama_lengkap, email, telepon) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql_pelanggan);
            $stmt->bind_param("sss", $nama, $email, $telepon);
            $stmt->execute();
            $pelanggan_id = $conn->insert_id;
        }
    }
    
    $tanggal_kunjungan = $conn->real_escape_string($_POST['tanggal']);
    $wahana_id = intval($_POST['wahana']);
    $total_harga = floatval($_POST['total_harga']);
    
    $conn->begin_transaction();
    
    try {
        $kode_transaksi = 'TRX-' . date('YmdHis') . '-' . rand(1000, 9999);
        
        $sql_transaksi = "INSERT INTO transaksi (kode_transaksi, pelanggan_id, tanggal_pembelian, tanggal_kunjungan, total_harga, status_pembayaran) 
                          VALUES (?, ?, NOW(), ?, ?, 'pending')";
        $stmt = $conn->prepare($sql_transaksi);
        $stmt->bind_param("sisd", $kode_transaksi, $pelanggan_id, $tanggal_kunjungan, $total_harga);
        $stmt->execute();
        $transaksi_id = $conn->insert_id;
        
        $sql_wahana = "SELECT * FROM wahana WHERE id = ?";
        $stmt = $conn->prepare($sql_wahana);
        $stmt->bind_param("i", $wahana_id);
        $stmt->execute();
        $wahana = $stmt->get_result()->fetch_assoc();
        
        $sql_detail = "INSERT INTO detail_transaksi (transaksi_id, wahana_id, jumlah_tiket, harga_satuan, subtotal) 
                       VALUES (?, ?, 1, ?, ?)";
        $stmt = $conn->prepare($sql_detail);
        $stmt->bind_param("iidd", $transaksi_id, $wahana_id, $wahana['harga'], $wahana['harga']);
        $stmt->execute();
        
        $conn->commit();
        
        $_SESSION['transaksi_id'] = $transaksi_id;
        $_SESSION['kode_transaksi'] = $kode_transaksi;
        
        header("Location: payment.php");
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: ticket.php");
    exit();
}
?>