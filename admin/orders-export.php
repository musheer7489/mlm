<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get all orders for export
$orders = getAllOrdersForExport();

// Set headers for download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="orders_export_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, [
    'Order ID',
    'Order Date',
    'Customer Name',
    'Customer Email',
    'Customer Phone',
    'Product',
    'Quantity',
    'Unit Price',
    'Total Amount',
    'Payment Status',
    'Payment Method',
    'Payment ID',
    'Shipping Status',
    'Tracking Number',
    'Shipping Address',
    'Billing Address'
]);

// Write order data
foreach ($orders as $order) {
    fputcsv($output, [
        $order['id'],
        $order['created_at'],
        $order['full_name'],
        $order['email'],
        $order['phone'],
        $order['product_name'],
        $order['quantity'],
        $order['unit_price'],
        $order['total_amount'],
        $order['payment_status'],
        $order['payment_method'],
        $order['payment_id'],
        $order['shipping_status'],
        $order['tracking_number'],
        str_replace("\n", " ", $order['shipping_address']),
        str_replace("\n", " ", $order['billing_address'])
    ]);
}

fclose($output);
exit;

function getAllOrdersForExport() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT o.*, u.full_name, u.email, u.phone,
               p.name as product_name, oi.quantity, oi.price as unit_price
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        ORDER BY o.created_at DESC
    ");
    
    return $stmt->fetchAll();
}