<?php
require '../../_base.php';
//-----------------------------------------------------------------------------------
auth("member");

$oldOrdersStm = $_db->query("SELECT o_id FROM orders WHERE status = 'unpaid' AND datetime < DATE_SUB(NOW(), INTERVAL 1 DAY)");
$oldOrderIds = $oldOrdersStm->fetchAll(PDO::FETCH_COLUMN);

if ($oldOrderIds) {
    $placeholders = str_repeat('?,', count($oldOrderIds) - 1) . '?';

    $stm = $_db->prepare("DELETE FROM topping_item WHERE po_id IN (SELECT po_id FROM product_order WHERE o_id IN ($placeholders))");
    $stm->execute($oldOrderIds);

    $stm = $_db->prepare("DELETE FROM product_order WHERE o_id IN ($placeholders)");
    $stm->execute($oldOrderIds);

    $stm = $_db->prepare("DELETE FROM orders WHERE o_id IN ($placeholders) AND status = 'unpaid'");
    $stm->execute($oldOrderIds);
}

if (is_post() && post('action') == 'rate_order') {
    $id = post('id');
    $rating = post('rating');
    
    // Safety check: ensure rating is 1-5 and order belongs to user
    if ($rating >= 1 && $rating <= 5) {
        $stm = $_db->prepare("UPDATE orders SET rate = ? WHERE o_id = ? AND u_id = ?");
        $stm->execute([$rating, $id, $_user->u_id]);
    }
    exit; 
}

if (is_post() && post('action') == 'delete_unpaid') {
    $id = post('id');
    $u_id = $_user->u_id;

    // 1. Delete from topping_item first
    $stm = $_db->prepare("
        DELETE FROM topping_item 
        WHERE po_id IN (SELECT po_id FROM product_order WHERE o_id = ?)
    ");
    $stm->execute([$id]);

    // 2. Delete from product_order
    $stm = $_db->prepare("DELETE FROM product_order WHERE o_id = ?");
    $stm->execute([$id]);

    // 3. Finally, delete from orders (checking status and u_id for safety)
    $stm = $_db->prepare("DELETE FROM orders WHERE o_id = ? AND u_id = ? AND status = 'unpaid'");
    $stm->execute([$id, $u_id]);
    
    temp('info', 'Order cancelled and deleted successfully.');
    exit;
}

$today = date('Y-m-d');

$fields = [
    'o_id'       => 'Order ID',
    'datetime'   => 'Date',
    'total'      => 'Total (RM)',
    'status'     => 'Status',
    'rate'       => 'Rating',
];

$sort = req('sort');
key_exists($sort, $fields) || $sort = 'o_id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'desc';

$u_id   = $_user->u_id;
$min_p  = get('min_p');
$max_p  = get('max_p');
$date_f = get('date_f');
$p_id   = get('p_id');
$status = get('status');

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

$_title = 'FourLeaves | Order History';
include '../../_head.php';
include 'profile_sidebar.php';
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
    .status-container {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-delete-expired {
        background-color: #c62828(198, 40, 40, 0.2);
        border:none;
        color: rgba(198, 40, 40, 1.0);
        border-radius: 4px;
        width: 32px;
        height: 32px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s;
        padding: 0;
    }

    .btn-delete-expired:hover {
        background-color: #c62828(198, 40, 40, 0.4);
    }

    .icon-bin {
        width: 18px;
        height: 18px;
        background-color: currentColor;
        -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z' /%3E%3C/svg%3E") no-repeat center;
        mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z' /%3E%3C/svg%3E") no-repeat center;
    }
    #receipt-modal {
        display: none; position: fixed; z-index: 1000; left: 0; top: 0;
        width: 100%; height: 100%; background: rgba(0,0,0,0.5);
    }
    .modal-content {
        background: white; margin: 5% auto; padding: 20px; width: 400px; border-radius: 8px;
    }

    /* Rating Modal Styles */
    #rating-modal {
        display: none; position: fixed; z-index: 1100; left: 0; top: 0;
        width: 100%; height: 100%; background: rgba(0,0,0,0.6);
    }
    .rating-content {
        background: white; margin: 15% auto; padding: 30px; 
        width: 300px; border-radius: 12px; text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    .stars {
        font-size: 40px; color: #ddd; cursor: pointer;
        display: flex; justify-content: center; gap: 10px;
        margin: 20px 0;
    }
    .star:hover, .star.active { color: #f1c40f; }

    .rate-now-link {
        color: #6fb048; text-decoration: underline; cursor: pointer; font-weight: bold;
    }
    @media print {
        html, body {
            width: 100%;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            visibility: hidden;
        }

        .receipt-box {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 10px !important;
            box-sizing: border-box !important;
        }

        .item-table, .receipt-box table {
            width: 100% !important;
            table-layout: auto !important;
        }

        .receipt-box, .pickup-highlight, .cash-info {
            border: 1px solid #ccc !important;
            box-shadow: none !important;
        }

        .receipt-box, .receipt-box * {
            visibility: visible;
        }

        .receipt-box {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none;
            padding: 0;
        }

        .proceed-btn, .back-btn, header, footer, nav {
            display: none !important;
        }
        .screen-only {
            display: none !important;
        }
    }
</style>
<main>
    <h1>Order History</h1>

    <form class="admin-bar">
        <input type="date" name="date_f" value="<?= $date_f ?>" max="<?= $today ?>">
        <input type="number" name="min_p" placeholder="Min RM" step="0.01" value="<?= $min_p ?>">
        <input type="number" name="max_p" placeholder="Max RM" step="0.01" value="<?= $max_p ?>">
        
        <select name="p_id">
            <option value="">All Products</option>
            <?php foreach ($_db->query("SELECT p_id, name FROM product")->fetchAll() as $p): ?>
                <option value="<?= $p->p_id ?>" <?= $p_id == $p->p_id ? 'selected' : '' ?>><?= $p->name ?></option>
            <?php endforeach; ?>
        </select>
        
        <select name="limit" onchange="this.form.submit()">
            <?php foreach ([10, 20, 50, 100] as $l): ?>
                <option value="<?= $l ?>" <?= $limit == $l ? 'selected' : '' ?>><?= $l ?> per page</option>
            <?php endforeach; ?>
        </select>

        <button class="btn-add">Filter</button>
        <button type="button" class="btn-clear" id="btn-clear">Clear</button>
    </form>

    <form id="bulk-form" method="post">
        <table class="admin-table">
            <thead>
                <tr>
                    <?= table_headers($fields, $sort, $dir) ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr data-id="<?= $o->o_id ?>" class="order-row">
                    <?php 
                        // Define the action based on status
                        $isPaid = ($o->status == 'paid');
                        $rowAction = $isPaid 
                            ? "previewReceipt('{$o->o_id}')" 
                            : "goToPayment('{$o->o_id}')";
                        
                        $tooltip = $isPaid ? "View Receipt" : "Pay Now";
                    ?>
                    
                    <td onclick="<?= $rowAction ?>" title="<?= $tooltip ?>"><?= $o->o_id ?></td>
                    <td onclick="<?= $rowAction ?>" title="<?= $tooltip ?>"><?= $o->datetime ?></td>
                    <td onclick="<?= $rowAction ?>" title="<?= $tooltip ?>">RM <?= sprintf('%.2f', $o->total) ?></td>
                    <td onclick="<?= $rowAction ?>">
                        <div class="status-container">
                            <span class="badge badge-<?= $o->status ?>" data-id="<?= $o->o_id ?>">
                                <?= ucfirst($o->status) ?>
                            </span>

                            <?php 
                            $sessionKey = "pending_order_" . $o->o_id;
                            // Show delete button only if Unpaid AND Session has expired
                            if ($o->status == 'unpaid' && !isset($_SESSION[$sessionKey])): 
                            ?>
                                <button type="button" 
                                        class="btn-delete-expired" 
                                        title="Delete this expired order"
                                        onclick="event.stopPropagation(); deleteUnpaid('<?= $o->o_id ?>')">
                                    <div class="icon-bin"></div>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <?php if ($isPaid):?>
                            <?php if ($o->rate): ?>
                                <?= str_repeat('⭐', $o->rate) ?>
                            <?php else: ?>
                                <span class="rate-now-link" onclick="openRatingModal('<?= $o->o_id ?>')">Rate Now</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color: #ccc; font-style: italic; font-size: 0.9em;">Pay to rate</span>
                        <?php endif; ?>
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

<div id="rating-modal" onclick="closeRatingModal(event)">
    <div class="rating-content" onclick="event.stopPropagation()">
        <h3>Rate Your Order</h3>
        <p>Order ID: <span id="rating-order-id"></span></p>
        <div class="stars" id="star-container">
            <span class="star" data-value="1">☆</span>
            <span class="star" data-value="2">☆</span>
            <span class="star" data-value="3">☆</span>
            <span class="star" data-value="4">☆</span>
            <span class="star" data-value="5">☆</span>
        </div>
        <button class="btn-clear" onclick="closeRatingModal(null)">Cancel</button>
    </div>
</div>

<script>
// Modal Logic
function goToPayment(id) {
    // Redirects to payment page with the specific order ID
    window.location.href = `../order/payment.php?o_id=${id}`;
}

function previewReceipt(id) {
    $('#receipt-modal').show();
    $('#receipt-body').load('../order/order_receipt_content.php?o_id=' + id);
}

function deleteUnpaid(id) {
    if (confirm('Delete this unpaid order?')) {
        $.post('', { action: 'delete_unpaid', id: id }, function() {
            location.reload();
        });
    }
}

function closeModal() { $('#receipt-modal').hide(); }

function printReceipt() {
    // Add a temporary class to the body to identify we are in "Receipt Mode"
    document.body.classList.add('printing-receipt');
    
    // Trigger the print dialog
    window.print();
    
    // Remove the class after the print dialog closes
    document.body.classList.remove('printing-receipt');
}

// Rating Logic
let currentRatingOrderId = null;

function openRatingModal(id) {
    currentRatingOrderId = id;
    $('#rating-order-id').text(id);
    $('#rating-modal').fadeIn(200);
}

// Function to close modal if clicking outside
function closeRatingModal(e) {
    if (e === null || e.target.id === 'rating-modal') {
        $('#rating-modal').fadeOut(200);
        $('.star').removeClass('active').text('☆'); // Reset stars
    }
}

// Hover effect and click logic for stars
$('.star').on('mouseover', function() {
    let val = $(this).data('value');
    $('.star').each(function() {
        $(this).text($(this).data('value') <= val ? '★' : '☆');
    });
}).on('mouseout', function() {
    $('.star').text('☆');
}).on('click', function() {
    let rating = $(this).data('value');
    
    $.post('', { 
        action: 'rate_order', 
        id: currentRatingOrderId, 
        rating: rating 
    }, function() {
        // Refresh the page or update UI manually
        location.reload(); 
    });
});
</script>

</main>
</div>
<?php
include '../../_foot.php';
?>