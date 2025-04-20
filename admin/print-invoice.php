<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$orderId = (int)$_GET['id'];

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.full_name, u.email, u.phone, u.address, 
           p.name as product_name, p.description as product_description, p.price, p.image
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN products p ON o.product_id = p.id
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Generate HTML invoice
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $order['payment_id'] ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); }
        .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #4e73df; }
        .invoice-title { font-size: 24px; font-weight: bold; }
        .addresses { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th { text-align: left; padding: 10px; background: #f5f5f5; }
        .table td { padding: 10px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .total { font-weight: bold; font-size: 18px; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #777; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <div class="logo"><?= SITE_NAME ?></div>
            <div>
                <div class="invoice-title">INVOICE</div>
                <div>#<?= $order['payment_id'] ?></div>
                <div>Date: <?= date('M j, Y', strtotime($order['created_at'])) ?></div>
            </div>
        </div>
        
        <div class="addresses">
            <div>
                <strong>From:</strong><br>
                <?= SITE_NAME ?><br>
                123 Business Street<br>
                City, State 12345<br>
                Phone: (123) 456-7890<br>
                Email: <?= SITE_EMAIL ?>
            </div>
            <div>
                <strong>To:</strong><br>
                <?= htmlspecialchars($order['full_name']) ?><br>
                <?= nl2br(htmlspecialchars($order['address'])) ?><br>
                Phone: <?= htmlspecialchars($order['phone']) ?><br>
                Email: <?= htmlspecialchars($order['email']) ?>
            </div>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($order['product_name']) ?></strong><br>
                        <small><?= htmlspecialchars($order['product_description']) ?></small>
                    </td>
                    <td>₹<?= number_format($order['price'], 2) ?></td>
                    <td><?= $order['quantity'] ?></td>
                    <td class="text-right">₹<?= number_format($order['total_amount'], 2) ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">Subtotal:</td>
                    <td class="text-right">₹<?= number_format($order['total_amount'] / 1.05, 2) ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">Tax (5%):</td>
                    <td class="text-right">₹<?= number_format($order['total_amount'] * 0.05, 2) ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right total">Total:</td>
                    <td class="text-right total">₹<?= number_format($order['total_amount'], 2) ?></td>
                </tr>
            </tbody>
        </table>
        
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Payment Status: <strong><?= ucfirst($order['payment_status']) ?></strong></p>
        </div>
        
        <div class="no-print" style="margin-top: 20px; text-align: center;">
            <button onclick="window.print()" class="btn btn-primary">Print Invoice</button>
            <button onclick="window.close()" class="btn btn-secondary">Close</button>
        </div>
    </div>
</body>
</html>