<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$format = isset($_GET['format']) ? $_GET['format'] : 'csv';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query
$query = "SELECT o.payment_id as order_id, o.created_at as order_date, 
                 o.payment_status, o.total_amount,
                 u.username, u.full_name, u.email, u.phone,
                 p.name as product_name, p.price
          FROM orders o
          JOIN users u ON o.user_id = u.id
          JOIN products p ON o.product_id = p.id
          WHERE 1=1";

$params = [];
$types = '';

if (!empty($statusFilter)) {
    $query .= " AND o.payment_status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if (!empty($dateFrom)) {
    $query .= " AND o.created_at >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}

if (!empty($dateTo)) {
    $query .= " AND o.created_at <= ?";
    $params[] = $dateTo . ' 23:59:59';
    $types .= 's';
}

$query .= " ORDER BY o.created_at DESC";

// Get orders
$stmt = $pdo->prepare($query);
if (!empty($params)) {
    $stmt->execute($params);
}
$stmt->execute();
$orders = $stmt->fetchAll();

// Set headers based on format
switch ($format) {
    case 'csv':
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        
        // CSV header
        fputcsv($output, [
            'Order ID', 'Date', 'Status', 'Amount', 
            'Customer Name', 'Username', 'Email', 'Phone',
            'Product', 'Price', 'Quantity'
        ]);
        
        // CSV data
        foreach ($orders as $order) {
            fputcsv($output, [
                $order['order_id'],
                $order['order_date'],
                $order['payment_status'],
                $order['total_amount'],
                $order['full_name'],
                $order['username'],
                $order['email'],
                $order['phone'],
                $order['product_name'],
                $order['price'],
                $order['total_amount'] / $order['price'] // Calculate quantity
            ]);
        }
        
        fclose($output);
        break;
        
    case 'excel':
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d') . '.xls"');
        
        echo '<table border="1">';
        echo '<tr>';
        echo '<th>Order ID</th><th>Date</th><th>Status</th><th>Amount</th>';
        echo '<th>Customer Name</th><th>Username</th><th>Email</th><th>Phone</th>';
        echo '<th>Product</th><th>Price</th><th>Quantity</th>';
        echo '</tr>';
        
        foreach ($orders as $order) {
            echo '<tr>';
            echo '<td>' . $order['order_id'] . '</td>';
            echo '<td>' . $order['order_date'] . '</td>';
            echo '<td>' . $order['payment_status'] . '</td>';
            echo '<td>' . $order['total_amount'] . '</td>';
            echo '<td>' . $order['full_name'] . '</td>';
            echo '<td>' . $order['username'] . '</td>';
            echo '<td>' . $order['email'] . '</td>';
            echo '<td>' . $order['phone'] . '</td>';
            echo '<td>' . $order['product_name'] . '</td>';
            echo '<td>' . $order['price'] . '</td>';
            echo '<td>' . ($order['total_amount'] / $order['price']) . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        break;
        
    case 'pdf':
        require_once '../vendor/autoload.php';
        
        $mpdf = new \Mpdf\Mpdf();
        $html = '
        <h1>Order Report</h1>
        <p>Generated: ' . date('Y-m-d H:i:s') . '</p>
        <table border="1" cellpadding="5" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Customer</th>
                    <th>Product</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($orders as $order) {
            $html .= '
                <tr>
                    <td>' . $order['order_id'] . '</td>
                    <td>' . date('M j, Y', strtotime($order['order_date'])) . '</td>
                    <td>' . $order['payment_status'] . '</td>
                    <td>₹' . number_format($order['total_amount'], 2) . '</td>
                    <td>' . $order['full_name'] . ' (@' . $order['username'] . ')</td>
                    <td>' . $order['product_name'] . '</td>
                </tr>';
        }
        
        $html .= '
            </tbody>
        </table>';
        
        $mpdf->WriteHTML($html);
        $mpdf->Output('orders_' . date('Y-m-d') . '.pdf', 'D');
        break;
        
    default:
        header('Location: orders.php');
        exit;
}
?>