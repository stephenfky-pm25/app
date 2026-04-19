<?php
require_once '../../_base.php';
$o_id = req('o_id');

// Fetch Order & Customer Details
$stm = $_db->prepare("SELECT o.*, u.name as customer_name FROM orders o JOIN user u ON o.u_id = u.u_id WHERE o.o_id = ?");
$stm->execute([$o_id]);
$o = $stm->fetch();

if (!$o) die("Order not found");

// Fetch Items & Toppings
$stm = $_db->prepare("SELECT po.*, p.name, p.price
                        FROM product_order po
                        JOIN product p ON po.p_id = p.p_id 
                        WHERE po.o_id = ?");
$stm->execute([$o_id]);
$items = $stm->fetchAll();

//for pickup payment
$call_number = str_pad($o->o_id, 4, '0', STR_PAD_LEFT); 
$pickup_time = date('H:i', strtotime($o->datetime) + (15 * 60));
?>

<style>
    .receipt-box {
        max-width: 100%;
        margin: auto;
        padding: 10px;
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        color: #555;
    }

    .text-right { text-align: right; }
    .invoice-title { font-size: 24px; color: #6fb048; font-weight: bold; }
    .item-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .item-table td { padding: 8px; border-bottom: 1px solid #eee; font-size: 13px; }
    .heading td { background: #f8f8f8; font-weight: bold; }
    .total-row td { font-weight: bold; border-top: 2px solid #6fb048; padding-top: 10px; }
    .topping-info { font-size: 11px; color: #888; display: block; }
    
    .pickup-highlight {
        background: #f0f9eb; 
        border: 1px solid #c2e7b0; 
        padding: 15px; 
        margin: 15px 0; 
        border-radius: 5px; 
        text-align: center;
    }
    
    .cash-info {
        background: #f9f9f9;
        border: 2px dashed #6fb048;
        padding: 15px;
        text-align: center;
        margin-bottom: 20px;
        border-radius: 8px;
    }
    .call-number { font-size: 32px; font-weight: bold; color: #333; }
</style>


<?php if ($o->status == 'unpaid' && $_user->role=='member'):?>
        <div class="screen-only">
            <div class="cash-info">
                <p>Please present this at the counter for payment</p>
                <div class="call-number">#<?= $call_number ?></div>
                <p>Estimated Pickup: <strong><?= $pickup_time ?></strong></p>
            </div>
        </div>
<?php endif; ?>
<div class="receipt-box">
    <table style="width: 100%;">
        <tr>
            <td class="invoice-title">🧋 FOUR LEAVES</td>
            <td class="text-right" style="font-size: 12px;">
                <strong>Order ID:</strong> <?= $o->o_id ?><br>
                <strong>Date:</strong> <?= date('d-M-Y H:i', strtotime($o->datetime)) ?>
            </td>
        </tr>
    </table>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">

    <?php if (isset($_SESSION['pickup_info'])): ?>
        <?php $info = $_SESSION['pickup_info']; ?>
        <div class="pickup-highlight">
            <h3 style="margin: 0; color: #6fb048; font-size: 16px;">PICKUP SLIP</h3>
            <p style="font-size: 22px; margin: 5px 0;"><strong># <?= $info['call_number'] ?></strong></p>
            <p style="margin: 0; font-size: 12px;">Ready around: <strong><?= $info['est_time'] ?></strong></p>
        </div>
        <?php unset($_SESSION['pickup_info']); // Clear after showing once ?>
    <?php endif; ?>

    <table style="width: 100%; font-size: 13px; margin-bottom: 15px;">
        <tr>
            <td>
                <strong>Billed To:</strong><br>
                <?= htmlspecialchars($o->customer_name) ?><br>
                <small>Customer ID: <?= $o->u_id ?></small>
            </td>
            <td class="text-right">
                <strong>Status:</strong><br>
                <span style="color: <?= $o->status == 'paid' ? '#2e7d32' : '#c62828' ?>; font-weight: bold;">
                    <?= strtoupper($o->status) ?>
                </span>
            </td>
        </tr>
    </table>

    <table class="item-table">
        <tr class="heading">
            <td>Description</td>
            <td class="text-right">Qty</td>
            <td class="text-right">Subtotal</td>
        </tr>

        <?php foreach ($items as $i): ?>
        <?php
            // Fetch toppings for this specific item
            $stm_top = $_db->prepare("SELECT t.name FROM topping_item ti JOIN topping t ON ti.t_id = t.t_id WHERE ti.po_id = ?");
            $stm_top->execute([$i->po_id]);
            $toppings = $stm_top->fetchAll();
        ?>
        <tr>
            <td>
                <strong><?= $i->name ?></strong>
                <?php foreach ($toppings as $t): ?>
                    <span class="topping-info">+ <?= $t->name ?></span>
                <?php endforeach; ?>
            </td>
            <td class="text-right"><?= $i->quantity ?></td>
            <td class="text-right">RM <?= number_format($i->subtotal, 2) ?></td>
        </tr>
        <?php endforeach; ?>

        <tr class="total-row">
            <td colspan="2" class="text-right">GRAND TOTAL</td>
            <td class="text-right">RM <?= number_format($o->total, 2) ?></td>
        </tr>
    </table>

    <div style="margin-top: 30px; text-align: center; font-size: 11px; color: #999; border-top: 1px dashed #eee; padding-top: 10px;">
        <p>This is a computer-generated receipt.<br>Thank you for choosing Four Leaves!</p>
    </div>
</div>