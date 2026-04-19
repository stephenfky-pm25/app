<?php
require '../../_base.php';
//-----------------------------------------------------------------------------------
auth("superadmin", "admin");

// --- Handle Bulk Delete ---
if (is_post() && post('action') == 'delete') {
    $ids = post('ids'); // Array of o_id
    
    if ($ids) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        $stm = $_db->prepare("
            DELETE FROM topping_item 
            WHERE po_id IN (SELECT po_id FROM product_order WHERE o_id IN ($placeholders))
        ");
        $stm->execute($ids);

        $stm = $_db->prepare("DELETE FROM product_order WHERE o_id IN ($placeholders)");
        $stm->execute($ids);

        $stm = $_db->prepare("DELETE FROM orders WHERE o_id IN ($placeholders)");
        $stm->execute($ids);

        temp('info', count($ids) . " order(s) and associated records deleted.");
    } else {
        temp('info', 'No rows selected');
    }
    redirect();
}

if (is_post() && post('action') == 'toggle_status') {
    $id = post('id');
    $stm = $_db->prepare("
        UPDATE orders 
        SET status = 'paid', 
            payment_datetime = NOW() 
        WHERE o_id = ? AND status = 'unpaid'
    ");
    $stm->execute([$id]);
    exit;
}

$today = date('Y-m-d');

$fields = [
    'o_id'       => 'Order ID',
    'u_id'       => 'Customer ID',
    'datetime'   => 'Date',
    'total'      => 'Total (RM)',
    'status'     => 'Status',
    'rate'       => 'Rating',
    'payment_datetime'=> 'Payment',
];

$sort = req('sort');
key_exists($sort, $fields) || $sort = 'o_id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'desc';

$u_id   = get('u_id');
$min_p  = get('min_p');
$max_p  = get('max_p');
$date_f = get('date_f');
$p_id   = get('p_id');
$status = get('status');
$payment= get('payment_datetime');

//paging
$page = req('page');
if ($page < 1) $page = 1;
$limit = req('limit');
$allowed_limits = [5, 10, 20, 50, 100];
if (!in_array($limit, $allowed_limits)) $limit = 10;

$offset = ($page - 1) * $limit;

// Base query with a subquery to calculate total amount and average rating
$sql = "SELECT * FROM orders WHERE 1=1";
$params = [];

if ($u_id) { $sql .= " AND u_id = ?"; $params[] = $u_id; }
if ($date_f) { $sql .= " AND DATE(datetime) = ?"; $params[] = $date_f; }
if ($status) { $sql .= " AND status = ?"; $params[] = $status; }
if ($min_p) { $sql .= " AND total >= ?"; $params[] = $min_p; }
if ($max_p) { $sql .= " AND total <= ?"; $params[] = $max_p; }
if ($p_id) {$sql .= " AND o_id IN (SELECT o_id FROM product_order WHERE p_id = ?)";$params[] = $p_id;}

$stm_count = $_db->prepare($sql);
$stm_count->execute($params);
$total_records = $stm_count->rowCount();
$total_pages = ceil($total_records / $limit);

$sql .= " ORDER BY $sort $dir LIMIT $limit OFFSET $offset";
$stm = $_db->prepare($sql);
$stm->execute($params);
$orders = $stm->fetchAll();

$_title = 'Admin | Order Maintenance';
include '../../_adminhead.php';
include '../../_adminsidebar.php';
?>
<style>
    .badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        height:30px;
        cursor: pointer;
        transition: 0.2s; 
    }
    .badge:hover { opacity: 0.7; transform: scale(1.05); }
    .badge-paid { background-color: #e8fff6; color: #2e7d32; }
    .badge-unpaid { background-color: #fff0f0; color: #c62828; }
    #receipt-modal {
        display: none; position: fixed; z-index: 1000; left: 0; top: 0;
        width: 100%; height: 100%; background: rgba(0,0,0,0.5);
    }
    .modal-content {
        background: white; margin: 5% auto; padding: 20px; width: 400px; border-radius: 8px;
    }

    @media print {
        html, body {
            width: 100%;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            visibility: hidden;
        }
        body * {
            visibility: hidden;
        }

        .screen-only {
            display: none !important;
        }

        #receipt-modal, 
        #receipt-modal *, 
        #receipt-body, 
        #receipt-body * {
            visibility: visible !important;
        }

        #receipt-modal {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            display: block !important; 
            background: white;
        }

        #receipt-modal button, .admin-bar, .admin-table, .paging {
            display: none !important;
        }

        .modal-content {
            margin: 0;
            padding: 0;
            width: 100%;
            border: none;
        }
    }
</style>
<main>
    <h1>Order Maintenance</h1>

    <form class="admin-bar">
        <input type="text" name="u_id" placeholder="Customer ID" value="<?= $u_id ?>">
        <input type="date" name="date_f" value="<?= $date_f ?>" max="<?= $today ?>">
        <input type="number" name="min_p" placeholder="Min RM" step="0.01" value="<?= $min_p ?>">
        <input type="number" name="max_p" placeholder="Max RM" step="0.01" value="<?= $max_p ?>">
        
        <select name="p_id">
            <option value="">All Products</option>
            <?php foreach ($_db->query("SELECT p_id, name FROM product")->fetchAll() as $p): ?>
                <option value="<?= $p->p_id ?>" <?= $p_id == $p->p_id ? 'selected' : '' ?>><?= $p->name ?></option>
            <?php endforeach; ?>
        </select>

        <select name="status">
            <option value="">All Status</option>
            <option value="unpaid" <?= $status == 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
            <option value="paid" <?= $status == 'paid' ? 'selected' : '' ?>>Paid</option>
        </select>
        
        <select name="limit" onchange="this.form.submit()">
            <?php foreach ([10, 20, 50, 100] as $l): ?>
                <option value="<?= $l ?>" <?= $limit == $l ? 'selected' : '' ?>><?= $l ?> per page</option>
            <?php endforeach; ?>
        </select>

        <button class="btn-add">Filter</button>
        <button type="button" class="btn-clear" id="btn-clear">Clear</button>
        <button type="submit" form="bulk-form" name="action" value="delete" 
                class="btn-delete-select" onclick="return confirm('Delete selected?')">
                Delete</button>
        <button class="btn-reset"data-post="reset_history.php" data-confirm>Reset</button>
        
    </form>

    <form id="bulk-form" method="post">
        <table class="admin-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="check-all"></th>
                    <?= table_headers($fields, $sort, $dir) ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr data-id="<?= $o->o_id ?>" class="order-row">
                    <td><input type="checkbox" name="ids[]" value="<?= $o->o_id ?>" class="check-item"title="Select for deletion"></td>
                    <td onclick="previewReceipt('<?= $o->o_id ?>')" title="Click to view or print receipt"><?= $o->o_id ?></td>
                    <td onclick="previewReceipt('<?= $o->o_id ?>')" title="Click to view or print receipt"><?= $o->u_id ?></td>
                    <td onclick="previewReceipt('<?= $o->o_id ?>')" title="Click to view or print receipt"><?= $o->datetime ?></td>
                    <td onclick="previewReceipt('<?= $o->o_id ?>')" title="Click to view or print receipt">RM <?= sprintf('%.2f', $o->total) ?></td>
                    <td>
                        <span class="badge badge-<?= $o->status ?> status-toggle"
                              data-id="<?= $o->o_id ?>" title="Click to update status">
                            <?= ucfirst($o->status) ?></span>
                    </td>
                    <td onclick="previewReceipt('<?= $o->o_id ?>')" title="Click to view or print receipt"><?= $o->rate ? str_repeat('⭐', $o->rate) : 'No Rating' ?></td>
                    <td onclick="previewReceipt('<?= $o->o_id ?>')" title="Click to view or print receipt">
                        <?= $o->payment_datetime ? date('Y-m-d', strtotime($o->payment_datetime)) : '' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <div class="paging" style="margin-top: 20px; display: flex; gap: 5px; align-items: center;">
                <?php
                $params = $_GET;
                ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php $params['page'] = $i; ?>
                    <a href="?<?= http_build_query($params) ?>" 
                    style="padding: 8px 12px; border: 1px solid #ddd; text-decoration: none; 
                            background: <?= $i == $page ? '#6fb048' : '#fff' ?>; 
                            color: <?= $i == $page ? '#fff' : '#333' ?>; border-radius: 4px;">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <span style="margin-left: 10px; color: #666;">
                    Showing <?= count($orders) ?> of <?= $total_records ?> records
                </span>
            </div>
        </table>
    </form>
</main>

<div id="receipt-modal">
    <div class="modal-content">
        <div id="receipt-body">Loading...</div>
        <hr>
        <button onclick="printReceipt()">Save as PDF</button>
        <button onclick="closeModal()">Close</button>
    </div>
</div>

<script>
$('.status-toggle').click(function() {
    const btn = $(this);
    const id = btn.data('id');
    
    if (btn.hasClass('badge-unpaid')) {
        $.post('', { action: 'toggle_status', id: id }, function() {
            btn.removeClass('badge-unpaid').addClass('badge-paid').text('Paid');
            btn.css('cursor', 'default').off('click');

            const today = new Date().toISOString().split('T')[0];
            btn.closest('tr').find('td:last-child').text(today);
        });
    } else {
        alert("This order is already paid and cannot be changed.");
    }
});

// Checkbox Logic
$('#check-all').click(function() {
    $('.check-item').prop('checked', this.checked);
});

// Modal Logic
function previewReceipt(id) {
    $('#receipt-modal').show();
    $('#receipt-body').load('../order/order_receipt_content.php?o_id=' + id);
}

function closeModal() { $('#receipt-modal').hide(); }

function printReceipt() {
    document.body.classList.add('printing-receipt');
    
    window.print();
    
    document.body.classList.remove('printing-receipt');
}

</script>

<?php include '../../_adminfoot.php'; ?>