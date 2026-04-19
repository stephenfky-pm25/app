<?php
require '../../_base.php';

// Restricted to Superadmin only
if ($_user->role !== 'superadmin') {
    temp('info', 'Unauthorized access.');
    redirect('/app/pages/admin/dashboard.php');
}

$_title = 'Price & Discount Logs';
include '../../_adminhead.php';
include '../../_adminsidebar.php';

// Fetch logs with Admin name and Product name
$stm = $_db->prepare("
    SELECT l.*, a.name as admin_name, p.name as product_name 
    FROM price_log l
    JOIN admin a ON l.a_id = a.a_id
    JOIN product p ON l.p_id = p.p_id
    ORDER BY l.changed_at DESC
");
$stm->execute();
$logs = $stm->fetchAll();
?>

<div class="container">
    <h2 style="margin-bottom: 20px; color: #222; border-bottom: 2px solid #90d895; display: inline-block;">
        Price Audit History
    </h2>

    <table class="table" style="width:100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr style="background-color: #222; color: #fff; text-align: left;">
                <th style="padding: 12px;">Date & Time</th>
                <th style="padding: 12px;">Admin</th>
                <th style="padding: 12px;">Product</th>
                <th style="padding: 12px;">Price Change</th>
                <th style="padding: 12px;">Discount Change</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $l): ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 12px; font-size: 0.9em; color: #666;">
                        <?= date('d-M-Y H:i A', strtotime($l->changed_at)) ?>
                    </td>
                    <td style="padding: 12px;"><strong><?= $l->admin_name ?></strong></td>
                    <td style="padding: 12px;"><?= $l->product_name ?></td>
                    <td style="padding: 12px;">
                        <span style="color: #888;"><?= $l->old_price ?></span> 
                        <span style="color: #90d895;">➜</span> 
                        <strong><?= $l->new_price ?></strong>
                    </td>
                    <td style="padding: 12px;">
                        <span style="color: #888;"><?= $l->old_discount ?>%</span> 
                        <span style="color: #90d895;">➜</span> 
                        <strong><?= $l->new_discount ?>%</strong>
                    </td>
                </tr>
            <?php endforeach; ?>
            
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding: 20px; color: #999;">No logs found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../_adminfoot.php'; ?>