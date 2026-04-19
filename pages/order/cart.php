<?php
require '../../_base.php';

// ----------------------------------------------------------------------------
if($_user && ($_user->role=="admin" || $_user->role=="superadmin")){
    redirect('/app/');
}

if (is_post()) {
    $btn = req('btn');
    if($btn == 'clear'){
        set_cart();
        redirect('?');
    }
    if($btn == 'delete'){
        $index = req('index');
        $cart = get_cart();
        if(isset($cart[$index])){
            unset($cart[$index]);
            $cart = array_values($cart);
            set_cart($cart);
        }
        redirect('?');
    }
    if($btn == 'update'){
        $index = req('index');
        $temp = req('opt_temp');
        $opt_ice = $temp == 'hot' ? null : req('opt_ice');
        $opt_sugar = req('opt_sugar');
        $toppings = req('opt_topping') ?? [];
        $qty = max(1, (int)req('qty'));
        $remark = req('remark');
        
        $cart = get_cart();
        if(isset($cart[$index])){
            // Update the item
            $cart[$index][1] = $temp;
            $cart[$index][2] = $opt_ice;
            $cart[$index][3] = $opt_sugar;
            $cart[$index][4] = $toppings;
            $cart[$index][5] = $qty;
            $cart[$index][6] = $remark;
            
            // Check for duplicates and merge
            $updated_item = $cart[$index];
            foreach($cart as $other_index => $other_details){
                if($other_index != $index && 
                   $other_details[0] == $updated_item[0] &&
                   $other_details[1] == $updated_item[1] &&
                   $other_details[2] == $updated_item[2] &&
                   $other_details[3] == $updated_item[3] &&
                   $other_details[4] == $updated_item[4] &&
                   $other_details[6] == $updated_item[6]){
                    $cart[$other_index][5] += $cart[$index][5];
                    unset($cart[$index]); // remove duplicate
                    break;
                }
            }
            
            // Reindex the array
            $cart = array_values($cart);
            set_cart($cart);
        }
        temp('info',"Updated");
        redirect('?');
    }
}

// ----------------------------------------------------------------------------
$_title = 'FourLeaves | Cart';
include_once $_SERVER['DOCUMENT_ROOT'] . '/app/_head.php';
?>
<main>
<style>
    main{
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .table{
        width:800px;
        height:fit-content;
    }

    .right{
        text-align: right;
    }

    .details-div{
        padding:10px;
        color:#888;
        width: fit-content;
        height: fit-content;
        display: none;
        cursor: pointer;
    }

    .readonly-details{
        cursor: pointer;
    }

    .edit-details{
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-top: 10px;
    }

    .config-option{
        margin: 10px 0;
    }

    .radio-group{
        display: flex;
        background-color: #b1d1b3;
        border-radius: 50px;
        width: fit-content;
        padding: 3px;
    }

    .radio-group label{
        margin: 0;
    }

    .radio-group input{
        display: none;
    }

    .radio-group div{
        border-radius: 50px;
        cursor: pointer;
        padding: 8px 20px;
        transition: all 0.3s;
    }

    .radio-group input:checked + div{
        background-color: #0e3c11;
        color: #fff;
        box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
    }

    .edit-buttons{
        text-align: center;
        margin-top: 15px;
    }

    .save-btn, .cancel-btn{
        border-radius: 20px;
        padding: 8px 16px;
        margin: 0 5px;
        border: none;
        cursor: pointer;
    }

    .quantity-selector{
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .q-btn{
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid #b1d1b3;
        background-color: #b1d1b3;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: bold;
        padding: 0;
        margin: 0;
        box-sizing: border-box;
        transition: all 0.2s ease;
    }

    .q-btn:hover{
        background-color: #0e3c11;
        border-color: #0e3c11;
        transform: scale(1.05);
    }

    .qty-input{
        width: 50px;
        text-align: center;
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    .save-btn{
        background-color: #4CAF50;
        color: white;
    }

    .cancel-btn{
        background-color: #f44336;
        color: white;
    }

    .disablerow{
        text-align:center;
        font-style:italic; 
        color:#888;
    }

    .checkout-btn, .clear-btn{
        border-radius:50px;
        padding:10px 20px;
        margin:10px 20px;
        color:#fff;
        justify-self: center;
    }

    .checkout-btn{
        background-color: #4CAF50;
    }

    .clear-btn{
        background-color: #f44336;
    }
</style>

<script>
    $(()=>{
        // Toggle details visibility on row click
        $(".row").on('click', function(e) {
            if (!$(e.target).closest('.details-div').length) {
                $(this).find('.details-div').toggle();
            }
        });

        // Enter edit mode when clicking the readonly details section only
        $(".details-div .readonly-details").on('click', function(e) {
            e.stopPropagation();
            var detailsDiv = $(this).closest('.details-div');
            populateEditForm(detailsDiv);
            detailsDiv.find('.readonly-details').hide();
            detailsDiv.find('.edit-details').show();
        });

        // Cancel edit mode
        $('.cancel-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var detailsDiv = $(this).closest('.details-div');
            populateEditForm(detailsDiv);
            detailsDiv.find('.edit-details').hide();
            detailsDiv.find('.readonly-details').show();
            return false;
        });

        // Delete on right click
        $(".row").on('contextmenu', function(e) {
            e.preventDefault();
            var index = $(this).data('index');
            var form = $('<form method="post"><input name="btn" value="delete"><input name="index" value="' + index + '"></form>');
            $('body').append(form);
            form.submit();
        });

        // Quantity buttons
        $(document).on('click', '.qty-plus', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var form = $(this).closest('form');
            changeQty(form, 1);
        });

        $(document).on('click', '.qty-minus', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var form = $(this).closest('form');
            changeQty(form, -1);
        });

        // Update prices when toppings change
        $(document).on('change', 'select[name="opt_topping[]"]', function() {
            var form = $(this).closest('form');
            updatePrices(form);
        });
    });

    // Toggle ice selector visibility
    function toggleIceSelector(form, temp) {
        var iceContainer = $(form).find('.ice-level-container');
        if (temp == 'hot') {
            iceContainer.hide();
        } else {
            iceContainer.show();
        }
    }

    function populateEditForm(detailsDiv) {
        var form = detailsDiv.find('form');
        var savedTemp = detailsDiv.data('saved-temp');
        var savedIce = detailsDiv.data('saved-ice');
        var savedSugar = detailsDiv.data('saved-sugar');
        var savedQty = detailsDiv.data('saved-qty');
        var savedRemark = detailsDiv.data('saved-remark');
        var savedToppings = [];

        try {
            savedToppings = JSON.parse(detailsDiv.attr('data-saved-toppings') || '[]');
            savedToppings = savedToppings.map(String);
        } catch (err) {
            savedToppings = [];
        }

        form.find('input[name="opt_temp"][value="' + savedTemp + '"]').prop('checked', true);
        toggleIceSelector(form, savedTemp);
        form.find('select[name="opt_ice"]').val(savedIce);
        form.find('select[name="opt_sugar"]').val(savedSugar);
        form.find('input[name="qty"]').val(savedQty);
        form.find('input[name="remark"]').val(savedRemark);

        form.find('select[name="opt_topping[]"] option').each(function() {
            var value = $(this).val();
            $(this).prop('selected', savedToppings.indexOf(value) !== -1);
        });

        // Keep quantity row current, but do not commit until save
        updatePrices(form);
    }

    // Handle quantity changes
    function changeQty(form, amt) {
        var qtyInput = $(form).find('.qty-input');
        var currentQty = parseInt(qtyInput.val());
        var newQty = currentQty + amt;
        if (newQty < 1) newQty = 1;
        qtyInput.val(newQty);
        
        // Update quantity in the table row preview
        var detailsDiv = $(form).closest('.details-div');
        var row = detailsDiv.closest('tr');
        row.find('td').eq(3).text(newQty);
        
        updatePrices(form);
    }

    // Update prices when quantity or toppings change
    function updatePrices(form) {
        var detailsDiv = $(form).closest('.details-div');
        var index = detailsDiv.data('index');
        var basePrice = parseFloat(detailsDiv.data('base-price'));
        
        // Get current values
        var qty = parseInt($(form).find('.qty-input').val());
        var selectedToppings = $(form).find('select[name="opt_topping[]"]').val() || [];
        
        // Calculate addon total
        var addonTotal = 0;
        selectedToppings.forEach(function(t_id) {
            if (t_id) {
                // Find the topping price from the option text
                var option = $(form).find('select[name="opt_topping[]"] option[value="' + t_id + '"]');
                var priceText = option.text().match(/\+ RM ([\d.]+)/);
                if (priceText) {
                    addonTotal += parseFloat(priceText[1]);
                }
            }
        });
        
        var subtotal = (basePrice + addonTotal) * qty;
        
        // Update subtotal in the row
        var row = detailsDiv.closest('tr');
        row.find('td').eq(4).text('RM' + subtotal.toFixed(2));
        
        // Update total
        updateTotal();
    }

    // Update the total price
    function updateTotal() {
        var total = 0;
        var count = 0;
        $('table tr').not(':first').not(':last').each(function() {  // Skip header and total rows
            var cells = $(this).find('td');
            if (cells.length >= 5) {
                var subtotalText = cells.eq(4).text();
                if (subtotalText.startsWith('RM')) {
                    total += parseFloat(subtotalText.replace('RM', ''));
                }
                var qtyText = cells.eq(3).text();
                if (!isNaN(qtyText)) {
                    count += parseInt(qtyText);
                }
            }
        });
        
        // Update total row
        var totalRow = $('table tr:last');
        totalRow.find('th').eq(1).text(count);
        totalRow.find('th').eq(2).text('RM' + total.toFixed(2));
    }
</script>

<h1>SHOPPING CART</h1>
<table class="table">
    <tr>
        <th>Id</th>
        <th>Name</th>
        <th>Price (RM)</th>
        <th>Quantity</th>
        <th>Subtotal (RM)</th>
    </tr>
    
    <?php
    $count = 0;
    $total = 0;

    $stm = $_db->prepare('SELECT * FROM product WHERE p_id = ?');
    $cart = get_cart();
    if(!$cart):?>
        <td colspan="5" style="text-align:center;">Your cart is empty. <a href="/app/pages/product/product.php">Click</a> to take a drink!</td>
    <?php
    endif;
    $topp = $_db->prepare('SELECT * FROM topping WHERE t_id = ?');
    $toppings_stm = $_db->prepare('SELECT t.t_id, t.name, t.price_per_unit FROM topping t JOIN topping_list tl ON t.t_id = tl.t_id WHERE tl.p_id = ? ORDER BY t.name');
    $index = 0;
    foreach ($cart as $details):
        $stm->execute([$details[0]]);
        $p = $stm->fetch();
        $toppings_stm->execute([$details[0]]);
        $available_toppings = $toppings_stm->fetchAll();
        $selected_toppings = $details[4];
        $addon_total = 0;
        ?>
        <tr class="<?= $p->status=="available" ? 'row' : 'disablerow' ?>" data-index="<?= $index ?>" title="Click to see details. Click on details to edit. Right click to remove from cart">
            <?php if($p->status=="available"):
                if (isset($selected_toppings) && is_array($selected_toppings)) {    
                    foreach ($selected_toppings as $t_id) {
                        $topp->execute([$t_id]);
                        $t = $topp->fetch();
                        if ($t) {
                            $addon_total += (float)$t->price_per_unit;
                        }
                    }
                }
                $subtotal = (($p->price) + $addon_total) * (int)$details[5];
                $count += (int)$details[5];
                $total += $subtotal;
            ?>
                <td><?= $p->p_id ?></td>
                <td>
                    <?= $p->name ?>
                    <div class="details-div" data-index="<?= $index ?>" data-base-price="<?= $p->price ?>" data-saved-temp="<?= htmlspecialchars($details[1], ENT_QUOTES) ?>" data-saved-ice="<?= htmlspecialchars($details[2], ENT_QUOTES) ?>" data-saved-sugar="<?= htmlspecialchars($details[3], ENT_QUOTES) ?>" data-saved-toppings='<?= htmlspecialchars(json_encode($selected_toppings ?? []), ENT_QUOTES) ?>' data-saved-qty="<?= htmlspecialchars($details[5], ENT_QUOTES) ?>" data-saved-remark="<?= htmlspecialchars($details[6] ?? '', ENT_QUOTES) ?>">
                        <div class="readonly-details">
                            <?= $details[1] ?><br>
                            <?php if($details[1]=="cold"):?>
                                <span>Ice:<?= $details[2] ?>%</span><br>
                            <?php endif; ?>
                            Sugar:<?= $details[3] ?>%<br>
                            <?php
                                $selected_toppings = $details[4];
                                if (isset($selected_toppings) && is_array($selected_toppings) && ($selected_toppings[0]!="")):?>
                                    <span>Add on: <br>
                                    <?php
                                    foreach ($selected_toppings as $t_id):
                                        $topp->execute([$t_id]);
                                        $t = $topp->fetch();
                                        if ($t):
                                        echo "$t->name<br>";
                                        endif;?>
                                    <?php endforeach; ?>
                                    </span>
                            <?php endif; 
                                if($details[6]):?>
                                    <span>Remarks:<br><?= $details[6] ?></span>
                                <?php endif;?>
                        </div>
                        <div class="edit-details" style="display: none;" title="Update details">
                            <form class="edit-form" method="post" >
                                <input type="hidden" name="btn" value="update">
                                <input type="hidden" name="index" value="<?= $index ?>">
                                
                                <div class="config-option">
                                    <div class="radio-group">
                                        <label><input type="radio" name="opt_temp" value="cold" <?= $details[1] == 'cold' ? 'checked' : '' ?> <?= $p->cold==0 ? 'disabled' : '' ?> onclick="toggleIceSelector(this.form, 'cold')"><div>❄️ Cold</div></label>
                                        <label><input type="radio" name="opt_temp" value="hot" <?= $details[1] == 'hot' ? 'checked' : '' ?> <?= $p->hot==0 ? 'disabled' : '' ?> onclick="toggleIceSelector(this.form, 'hot')"><div>🔥 Hot</div></label>
                                    </div>
                                </div>

                                <div class="config-option ice-level-container" <?= $details[1] == 'hot' ? 'style="display: none;"' : '' ?>>
                                    <label>Ice Level</label>
                                    <select name="opt_ice" style="width:100%; padding:8px; border-radius:8px; border:1px solid #eee;">
                                        <option value="100" <?= $details[2] == '100' ? 'selected' : '' ?>>100% Regular Ice</option>
                                        <option value="75" <?= $details[2] == '75' ? 'selected' : '' ?>>75% Less Ice</option>
                                        <option value="50" <?= $details[2] == '50' ? 'selected' : '' ?>>50% Half Ice</option>
                                        <option value="25" <?= $details[2] == '25' ? 'selected' : '' ?>>25% Quarter Ice</option>
                                        <option value="0" <?= $details[2] == '0' ? 'selected' : '' ?>>0% No Ice</option>
                                    </select>
                                </div>

                                <div class="config-option">
                                    <label>Sugar Level</label>
                                    <select name="opt_sugar" style="width:100%; padding:8px; border-radius:8px; border:1px solid #eee;">
                                        <option value="100" <?= $details[3] == '100' ? 'selected' : '' ?>>100% Regular</option>
                                        <option value="75" <?= $details[3] == '75' ? 'selected' : '' ?>>75% Less Sugar</option>
                                        <option value="50" <?= $details[3] == '50' ? 'selected' : '' ?>>50% Half Sugar</option>
                                        <option value="25" <?= $details[3] == '25' ? 'selected' : '' ?>>25% Quarter Sugar</option>
                                        <option value="0" <?= $details[3] == '0' ? 'selected' : '' ?>>0% No Sugar</option>
                                    </select>
                                </div>

                                <div class="config-option">
                                    <label>Topping (Hold Ctrl/Cmd to select multiple)</label>
                                    <select name="opt_topping[]" multiple style="width:100%; padding:8px; border-radius:8px; border:1px solid #eee;">
                                        <option value="">None</option>
                                        <?php foreach ($available_toppings as $t): ?>
                                            <option value="<?= $t->t_id ?>" <?= in_array($t->t_id, (array)($selected_toppings ?? [])) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($t->name) ?> (+ RM <?= sprintf('%.2f',$t->price_per_unit) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="config-option">
                                    <label>Quantity</label>
                                    <div class="quantity-selector">
                                        <button type="button" class="q-btn qty-minus">-</button>
                                        <input type="text" name="qty" class="qty-input" value="<?= $details[5] ?>" readonly>
                                        <button type="button" class="q-btn qty-plus">+</button>
                                    </div>
                                </div>

                                <div class="config-option">
                                    <label>Remarks</label>
                                    <input type="text" name="remark" value="<?= htmlspecialchars($details[6] ?? '') ?>" maxlength="100" style="width:95%; padding:8px; border-radius:8px; border:1px solid #eee;">
                                </div>

                                <div class="edit-buttons">
                                    <button type="button" class="cancel-btn" onclick="return false;">Cancel</button>
                                    <button type="submit" class="save-btn">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </td>
                <td class="right"><?= $p->price ?></td>
                <td class="right"><?= $details[5] ?></td>
                <td class="right"><?= sprintf('%.2f',$subtotal) ?></td>
                <?php else:?>
                    <td><?= $p->p_id ?></td>
                    <td><?= $p->name ?></td>
                    <td colspan="3">This item is currently unavailable.</td>
                    <?php update_cart($details[0],$details[1],$details[2],$details[3],$details[4], 0, $details[6]);?>
                <?php endif;?>
            </tr>
            
    <?php $index++; endforeach ?>

    <tr>
        <th colspan="3"></th>
        <th class="right"><?= $count ?></th>
        <th class="right"><?= sprintf('%.2f',$total) ?></th>
    </tr>
</table>
<div>
    <?php if ($cart): ?>
        <button class="clear-btn" data-post="?btn=clear">Clear</button>

        <?php if ($_user?->role == 'member'): ?>
            <button class="checkout-btn" data-get="checkout.php">Checkout</button>
        <?php else: ?>
            Please <a href="/security/login.php">login</a> as member to checkout
        <?php endif ?>
    <?php endif ?>
</div>

</main>
<?php
include '../../_foot.php';
?>