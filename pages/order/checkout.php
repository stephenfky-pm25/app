<?php
require '../../_base.php';

auth("member");

$stm = $_db->query('SELECT * FROM branch');
$branches = $stm->fetchAll();

$current_day  = date('l'); 
$current_time = date('H:i:s');
$any_branch_open = false;

foreach ($branches as $b) {
    $is_rest_day = (strcasecmp($b->rest_day, $current_day) == 0);
    $is_outside_hours = ($current_time < $b->start_time || $current_time > $b->end_time);
    
    if (!$is_rest_day && !$is_outside_hours) {
        $any_branch_open = true;
        break; 
    }
}

if (is_post()) {
    $way = req('prefer_way');
    $payment_method = req('payment');
    $addr_id = ($way == 'pickup') ? req('branch') : req('address');

    // --- VALIDATION ---
    if (!$any_branch_open) {
        $_err['address'] = "Sorry, all our branches are currently closed.";
    } else if (empty($addr_id)) {
        $_err['address'] = "Please select a address or branch.";
    }
    if($_err){
        temp('info', $_err['address']);
    }
    if($any_branch_open && $way == 'pickup'){ // Pickup
        if (empty($addr_id)) {
            $_err['branch'] = "Please select a branch.";
        } else {
            // Check specific branch hours
            $stm = $_db->prepare('SELECT * FROM branch WHERE b_id = ?');
            $stm->execute([$addr_id]);
            $b = $stm->fetch();
            
            if (strcasecmp($b->rest_day, $current_day) == 0 || 
                $current_time < $b->start_time || $current_time > $b->end_time) {
                $_err['branch'] = "The selected branch is currently closed.";
            }
        }if($_err){
            temp('info', $_err['branch']);
        }
    }

    if (!$_err) {
        $cart = get_cart();

        if (!empty($cart)) {
            $total = 0;
            $products_stmt = $_db->prepare('SELECT price FROM product WHERE p_id = ?');
            $topping_stmt = $_db->prepare('SELECT price_per_unit FROM topping WHERE t_id = ?');
            $subtotal = [];
            foreach ($cart as $item) {
                $p_id = $item[0];
                $toppings = $item[4];
                $qty = $item[5];

                $products_stmt->execute([$p_id]);
                $p = $products_stmt->fetch();
            
                $addon_total = 0;
                if (!empty($toppings)) {
                    // Handle both array and string formats for safety
                    $topping_ids = is_array($toppings) ? $toppings : explode(',', $toppings);
                    foreach (array_filter($topping_ids) as $t_id) {
                        $topping_stmt->execute([$t_id]);
                        $t = $topping_stmt->fetch();
                        if ($t) $addon_total += $t->price_per_unit;
                    }
                }
                $subtotal[] = ($p->price + $addon_total) * $qty;
                $total += ($p->price + $addon_total) * $qty;
            }
            
            // 2. Database Transaction for Order Creation
            $_db->beginTransaction();
            try {
                // A. Create Order Record
                $stm = $_db->prepare('
                    INSERT INTO orders (datetime, u_id, total, status)
                    VALUES (NOW(), ?, ?, ?)
                ');
                $stm->execute([
                    $_user->id, 
                    $total,
                    'unpaid'
                ]);
                $o_id = $_db->lastInsertId();
                $sessionKey = "pending_order_" . $o_id;
                $_SESSION[$sessionKey] = [
                    'way'     => $way,
                    'payment' => $payment_method,
                    'addr_id' => $addr_id
                ];

                // B. Create Product Order Records
                foreach ($cart as $i => $item) {
                    $p_id     = $item[0];
                    $ice      = $item[2];
                    $sugar    = $item[3];
                    $toppings = $item[4];
                    $qty      = $item[5];
                    $remark   = $item[6];
                    $item_total = $subtotal[$i];

                    $stm = $_db->prepare('
                        INSERT INTO product_order (o_id, p_id, quantity, ice, sugar, remark, subtotal)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ');
                    $stm->execute([$o_id, $p_id, $qty, $ice, $sugar, $remark,$item_total]);
                    $po_id = $_db->lastInsertId();

                    // C. Create Topping Item Records
                    if (!empty($toppings)) {
                        $topping_ids = is_array($toppings) ? $toppings : explode(',', $toppings);
                        foreach (array_filter($topping_ids) as $t_id) {
                            $stm = $_db->prepare('INSERT INTO topping_item (po_id, t_id) VALUES (?, ?)');
                            $stm->execute([$po_id, $t_id]);
                        }
                    }
                }

                $_db->commit();
                
                // 3. Clear Cart and Redirect to Payment
                set_cart([]); 
                redirect("payment.php?o_id=$o_id");

            } catch (Exception $e) {
                $_db->rollBack();
                $error = "Transaction failed: " . $e->getMessage();
            }
        }
    }
}

//address for delivery
$stm = $_db->prepare('SELECT * FROM address WHERE u_id=? ');
$stm->execute([$_user->id]);
$addresses = $stm->fetchAll();

//get branch
$stm = $_db->query('SELECT * FROM branch');
$branches = $stm->fetchAll();

$cart = get_cart();
// ----------------------------------------------------------------------------
$_title = 'FourLeaves | Checkout';
include '../../_head.php';

?>
<main>
<style>
    main{
        justify-content: center;
        align-items: center;
        padding: 20px;
    }
    
    .layout-container{
        align-items: center; 
        padding:10px;
        margin:10px;
        border:1px solid #eee;
        border-radius: 10px;
        box-shadow:0 2px 5px rgba(0,0,0,0.1);
    }

    div{
        padding:10px 20px;
    }

    main > div{
        padding:10%;
        width:100%;
        margin:10% 50px;
    }
    

    h2{
        color:#0e3c11;
        margin: 10px 20px;
    }

    .table{
        margin: 20px 20px;
        width: 80%;
        justify-self: center;
    }

    .table tbody:hover td {
        background: #ccc;
    }

    .radio-group{
        display:grid;
        grid:auto / auto auto;
        justify-self: center;
        background-color: #b1d1b3;
        border-radius: 50px;
        height:100%;
        width: fit-content;
        padding:3px 0 5px;
    }

    .radio-group input{
        display: none;
        width:100%;
        height:100%;
    }

    .radio-group div{
        border-radius: 50px;
        cursor: pointer;
        margin:0;
        padding:12px 30px 10px;
    }

    .radio-group input:checked + div{
        background-color: #0e3c11;
        color:#fff;
        justify-self:center;
        box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
    }

    #branch-container {
        display: none;
    }

    .config-option{
        margin: 20px 20px;
    }

    address{
        color:#eee;
        font-style: italic;
    }

    .proceed-btn{
        background-color: #4CAF50;
        color:#eee;
        border-radius:50px;
        padding:10px 20px;
        margin:10px auto;
        justify-self:right;
    }

    .back-btn{
        border-radius:50px;
        padding:10px 20px;
        margin:10px auto;
        justify-self:right;
    }
</style>

<script>

    $(()=>{
        const selectedWay = $('input[name="prefer_way"]:checked').val();
        toggleAddressSelector(selectedWay);
    });
    
    function toggleAddressSelector(way) {
        const cashOption = $('#payment-method option[value="cash"]');
        const paymentSelect = $('#payment-method');

        if (way == 'pickup') {
            $('#address-container').hide();
            $('#branch-container').show();
            
            // Enable "Pay at counter"
            cashOption.prop('disabled', false);
        } else {
            $('#address-container').show();
            $('#branch-container').hide();
            
            // Disable "Pay at counter"
            cashOption.prop('disabled', true);
            
            // If "cash" was selected, switch it back to "card" automatically
            if (paymentSelect.val() === 'cash') {
                paymentSelect.val('card');
            }
        }
    }
</script>
<h1>CHECKOUT</h1>
<div class = "layout-container">
    
    <div class="order-summary">
        <h2>Order Summary</h2>
        <!--table-->
        <table class="table">
            <tr>
                <th rowspan="2">ID</th>
                <th rowspan="2">Item</th>
                <th colspan="3">Details</th>
                <th rowspan="2">Quantity</th>
                <th rowspan="2">Price (RM)</th>
                <th rowspan="2">Subtotal (RM)</th>
            </tr>
            <tr>
                <th>Customize</th>
                <th>Add-on</th>
                <th>Add-on price (RM)</th>
            </tr>
            <?php 
            $total = 0;
            $products = $_db->prepare('SELECT * FROM product WHERE p_id=?');
            foreach($cart as $row):
                $id = $row[0];
                $temp = $row[1];
                $opt_ice = $row[2];
                $opt_sugar = $row[3];
                $toppings = $row[4];
                $qty = $row[5];
                $remark = $row[6];

                //get product details
                $products->execute([$id]);
                $p=$products->fetch();

                $toppings_array = is_array($toppings) ? array_filter($toppings) : array_filter(explode(',', $toppings));
                $num_of_addon = count($toppings_array);

                $t = [];
                if ($num_of_addon > 0) {
                    // 2. Create a string of placeholders like "?,?,?"
                    $placeholders = implode(',', array_fill(0, $num_of_addon, '?'));
                    
                    // 3. Use WHERE IN to find all matching toppings
                    $stm = $_db->prepare("SELECT * FROM topping WHERE t_id IN ($placeholders)");
                    
                    // 4. Pass the array of IDs directly to execute
                    $stm->execute(array_values($toppings_array));
                    $t = $stm->fetchAll();
                }
                
                $addon_total = 0;
                if($t){
                    foreach($t as $a_topp){
                        $addon_total+=$a_topp->price_per_unit;
                    }
                }
                if(($num_of_addon-2)>0):?>
                <tbody>
                    <tr>
                        <td rowspan="<?= $num_of_addon ?>"><?= $p->p_id ?></td>
                        <td rowspan="<?= $num_of_addon ?>"><?= $p->name ?></td>
                        <td><?= $temp=='cold'? "$temp: $opt_ice%":"$temp" ?>
                        <td><?= $t[0]->name?></td>
                        <td><?= sprintf('%.2f',$t[0]->price_per_unit) ?></td>
                        <td rowspan="<?= $num_of_addon ?>"><?= $qty ?></td>
                        <td rowspan="<?= $num_of_addon ?>"><?= sprintf('%.2f',$p->price) ?></td>
                        <?php 
                            $subtotal = (($p->price)+$addon_total)*$qty;
                            $total+=$subtotal;
                        ?>
                        <td rowspan="<?= $num_of_addon ?>"><?= $subtotal ?></td>
                    </tr>
                    <?php for($i=1; $i<$num_of_addon; $i++):?>
                    <tr>
                        <?php if($i==1):?>
                            <td rowspan="<?= $num_of_addon - 1 ?>" style="vertical-align: text-top;;">Sugar: <?= $opt_sugar ?>%</td>
                        <?php endif;?>
                        <td><?= isset($t[$i]) ? $t[$i]->name : ''?></td>
                        <td><?= isset($t[$i]) ? sprintf('%.2f',$t[$i]->price_per_unit) : '-'?></td>
                    </tr>
                    <?php endfor;?>
                </tbody>
                    <tr><td colspan = "8"></td></tr>
                
                <?php else:?>
                <tbody>
                    <tr>
                        <td rowspan="2"><?= $p->p_id ?></td>
                        <td rowspan="2"><?= $p->name ?></td>
                        <td><?= $temp=='cold'? "$temp: $opt_ice%":"$temp" ?>
                        <td><?= isset($t[0]) ? $t[0]->name : '-'?></td>
                        <td><?= isset($t[0]) ? sprintf('%.2f',$t[0]->price_per_unit) : '-'?></td>
                        <td rowspan="2"><?= $qty ?></td>
                        <td rowspan="2"><?= sprintf('%.2f',$p->price) ?></td>
                        <?php 
                            $subtotal = (($p->price)+$addon_total)*$qty;
                            $total+=$subtotal;
                        ?>
                        <td rowspan="2"><?= sprintf('%.2f',$subtotal) ?></td>
                    </tr>
                    <tr>
                        <td><?= "Sugar: $opt_sugar%" ?>
                        <td><?= isset($t[1]) ? $t[1]->name : ''?></td>
                        <td><?= isset($t[1]) ? sprintf('%.2f',$t[1]->price_per_unit) : ''?></td>
                    </tr>
                </tbody>
                    <tr><td colspan = "8"></td></tr>
                <?php endif?>
            <?php endforeach ?>
            <tfoot>
                <tr>
                    <td colspan="7">Total:</td>
                    <td><?= sprintf('%.2f',$total) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <form method="post">
        <div>
            <div class="radio-group">
                <label><input type="radio" name="prefer_way" value="pickup" onclick="toggleAddressSelector('pickup')"><div style="display:inline;">🫳Self-pickup</div></label>
                <label><input type="radio" name="prefer_way" value="delivery" checked onclick="toggleAddressSelector('delivery')"><div style="display:inline;">🛵Delivery</div></label>
            </div>
        </div>

        <div class="config-option" id="address-container">
            <label>Delivery Address</label>
            <select name="address" style="width:100%; padding:8px; border-radius:8px; border:1px solid #eee;">
                <?php foreach($addresses as $d): ?>
                    <option value="<?= $d->d_id ?>">
                        <div>
                            <?= $d->nickname ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <?= $d->number ?>, <?= $d->street ?>, <?= $d->city ?>, <?= $d->postcode ?> <?= $d->state ?>
                        </div>
                    </option>
                <?php endforeach ?>
            </select>
        </div>

        <div class="config-option" id="branch-container">
            <label>Pick-up Branch</label>
            <select name="branch" style="width:100%; padding:8px; border-radius:8px; border:1px solid #eee;">
                <?php 
                $current_day  = date('l'); 
                $current_time = date('H:i:s');

                foreach($branches as $b):
                    $is_rest_day = (strcasecmp($b->rest_day, $current_day) == 0);
                    
                    $is_outside_hours = ($current_time < $b->start_time || $current_time > $b->end_time);
                    
                    $disabled = ($is_rest_day || $is_outside_hours) ? 'disabled' : '';
                    
                    $status_msg = "";
                    if ($is_rest_day) {
                        $status_msg = " (Closed - Rest Day)";
                    } elseif ($is_outside_hours) {
                        $status_msg = " (Closed - Outside Business Hours)";
                    }
                ?>
                    <option value="<?= $b->b_id ?>" <?= $disabled ?>>
                        <?= $b->name ?><?= $status_msg ?>
                    </option>
                <?php endforeach;?>
            </select>
        </div>

        <div class="config-option" id="payment-container">
            <label>Payment method: </label>
            <select name="payment" id="payment-method" style="width:100%; padding:8px; border-radius:8px; border:1px solid #eee;">
                <option value="card" selected>Credit/Debit Card</option>
                <option value="cash">Pay at counter</option>
            </select>
            <div>
                <button formmethod="get" formaction="/pages/order/cart.php" class="back-btn">Back</button>
                <button class="proceed-btn">Proceed</button>
            </div>
        </div>
    </form>
</div>
</main>
<?php
include '../../_foot.php';
?>