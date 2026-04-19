<?php
require '../../_base.php';
//-------------------------------------------------------------
auth("superadmin","admin");



$search = get('search');
$min_p  = get('min_p');
$max_p  = get('max_p');
$status_f = get('status_f');

$sql = "SELECT * FROM product WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (name LIKE ? OR p_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($min_p !== '' && is_money($min_p)) {
    $sql .= " AND price >= ?";
    $params[] = $min_p;
}
if ($max_p !== '' && is_money($max_p)) {
    $sql .= " AND price <= ?";
    $params[] = $max_p;
}
if ($status_f) {
    $sql .= " AND status = ?";
    $params[] = $status_f;
}

if (is_post()) {

    
    $action = post('action');

    if (($_user->role == 'superadmin') && ($action == 'insert' || $action == 'update')) {
        $p_id   = post('p_id');
        $name   = post('name');
        $price  = post('price');
        $discount  = post('discount');
        $c_id   = post('c_id');
        $status = post('status');
        $cold = post('cold') ?: 0;
        $hot  = post('hot')  ?: 0;
        $t_ids  = post('t_ids') ?? [];
        $f      = get_file('image');

        if ($name == '') {
            $_err['name'] = 'Product name is required';
        } elseif (strlen($name) > 100) {
            $_err['name'] = 'Name too long (max 100 chars)';
        }

        if ($price == '') {
            $_err['price'] = 'Price is required';
        } elseif (!is_money($price)) {
            $_err['price'] = 'Invalid price format';
        }

        // if ($discount == '') {
        //     $_err['discount'] = 'Discound is required';
        // }

        if (!is_exists($c_id, 'category', 'c_id')) {
            $_err['c_id'] = 'Invalid category selected';
        }

        if (!in_array($status, ['available', 'unavailable'])) {
            $_err['status'] = 'Invalid status selected';
        }

        if (!$cold && !$hot) {
            $_err['temp'] = 'At least one must be selected';
            temp('info', "Unsuccessful, allow temperature are required (at least 1 checked)");
        }
        
        if (!$_err) {
// Inside your product update logic:
    $old_product = $_db->query("SELECT price, discount FROM product WHERE p_id = $p_id")->fetch();

            if ($action == 'insert') {
                $photo = 'default.jpg';
                if ($f) {
                    $photo = save_photo($f, root('/app/images/product'), 400, 400);
                }
                $stm = $_db->prepare('INSERT INTO product (name, price, discount, c_id, status, cold, hot, image) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stm->execute([$name, $price, $discount, $c_id, $status, $cold, $hot, $photo]);
                $p_id = $_db->lastInsertId();

                $stm = $_db->prepare('INSERT INTO topping_list (p_id, t_id) VALUES (?, ?)');
                foreach ($t_ids as $t_id) {
                    $stm->execute([$p_id, $t_id]);
                }

                temp('info', 'Product added!');
            }else if($action == 'update'){

                $stm = $_db->prepare('SELECT image FROM product WHERE p_id = ?');
                $stm->execute([$p_id]);
                $photo = $stm->fetchColumn();

                if ($f) {
                    $new_photo = save_photo($f, root('images/product'), 400, 400);
                    if ($photo && $photo != 'default.jpg' && file_exists(root("/app/images/product/$photo"))) {
                        unlink(root("/app/images/product/$photo"));
                    }
                    $photo = $new_photo;
                }

                $stm = $_db->prepare('UPDATE product SET name=?, price=?, discount=?, c_id=?, status=?, cold=?, hot=?, image=? WHERE p_id=?');
                $stm->execute([$name, $price, $discount, $c_id, $status, $cold, $hot, $photo, $p_id]);
                
                $stm = $_db->prepare('DELETE FROM topping_list WHERE p_id = ?');
                $stm->execute([$p_id]);

                $stm = $_db->prepare('INSERT INTO topping_list (p_id, t_id) VALUES (?, ?)');
                foreach ($t_ids as $t_id) {
                    $stm->execute([$p_id, $t_id]);
                }

                // Inside your product update logic:
                // $old_product = $_db->query("SELECT price, discount FROM product WHERE p_id = $p_id")->fetch();
                $new_price = post('price');
                $new_discount = post('discount');
                
                if ($old_product->price != $new_price || $old_product->discount != $new_discount) {
                    $log = $_db->prepare("
                        INSERT INTO price_log (a_id, p_id, old_price, new_price, old_discount, new_discount)
                        VALUES (?, ?, ?, ?, ?, ?)
                        ");
                    $log->execute([$_user->id, $p_id, $old_product->price, $new_price, $old_product->discount, $new_discount]);
}

                temp('info', 'Product updated successfully');
            }
            redirect();
        }
    }
}

$categories = $_db->query('SELECT * FROM category')->fetchAll();
$toppings   = $_db->query('SELECT * FROM topping ORDER BY name ASC')->fetchAll();
$order_clause = " ORDER BY status ASC, name ASC";

function get_linked_toppings($p_id) {
    global $_db;
    $stm = $_db->prepare('SELECT t_id FROM topping_list WHERE p_id = ?');
    $stm->execute([$p_id]);
    return $stm->fetchAll(PDO::FETCH_COLUMN);
}

//-------------------------------------------------------------
$_title = 'Admin | Product Maintenance';
include '../../_adminhead.php';
include '../../_adminsidebar.php';
?>

<style>
    main{
        padding: 30px;
        background-color: #ffffff;
        position:relative;
    }

    .tabs-container {
        display: flex;
        gap: 5px;
        margin-bottom: -1px;
    }

    .tab-btn {
        padding: 12px 25px;
        border: 1px solid #ccc;
        border-bottom: none;
        background: #e0e0e0;
        cursor: pointer;
        border-radius: 10px 10px 0 0;
        font-weight: bold;
        color: #666;
        transition: 0.3s;
    }

    .tab-btn.active {
        background: #ffffff; 
        color: #000;
        border-bottom: 2px solid #ffffff; 
        position: relative;
        z-index: 2;
    }

    .tab-content-wrapper {
        border: 1px solid #ccc;
        background: #ffffff;
        padding: 10px;
        border-radius: 10px;
        min-height: 400px;
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
    }

    .product-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        max-width: 1200px;
    }

    .card {
        display: inline-block;
        width: 230px;
        border: 1px solid #eee;
        border-radius: 10px;
        margin: 10px;
        text-align: center;
        transition: 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .img-preview {
        width: 300px; height: 300px;
        object-fit: cover;
        border-radius: 5px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
    }

    .form-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .list-item label {
        display: inline;
        font-weight: normal;
        margin-bottom: 0;
    }

    .view-only input, .view-only select {
        background-color: #f9f9f9;
        border: none;
        pointer-events: none;
    }
</style>
    
<script>
    $(()=>{
        let basePrice = 0;
        $('.tab-btn').click(function() {
        $('.tab-btn, .tab-pane').removeClass('active');
        $(this).addClass('active');
        $('#' + $(this).data('target')).addClass('active');
    });


    $('#addNewBtn').click(function() {
        $('#prodForm')[0].reset();
        $('#p_id').val('');
        $('#curr_img').attr('src', '/app/images/product/default.jpg');
        $('#drawer-title').text('Add New Product');
        $('#submitBtn').val('insert').text('Add Product');
        $('input[type="checkbox"]').prop('checked', false);

        $('#drawer').addClass('open');
        $('#overlay').fadeIn();
    });

    $('.card').click(function() {
        const d = $(this).data();
        basePrice = parseFloat(d.price);
        $('#p_id').val(d.id);
        $('#p_name').val(d.name);
        $('#p_price').val(d.price);
        $('#p_discount').val(d.discount);
        $('#p_cat').val(d.cat);
        $('#p_status').val(d.status);
        $('#curr_img').attr('src', '/app/images/product/' + d.img);
        
        $('input[type="checkbox"]').prop('checked', false);
        $('#cold').prop('checked', d.cold == 1);
        $('#hot').prop('checked', d.hot == 1);
        const linkedToppings = d.toppings; 
        if (linkedToppings) {
            linkedToppings.forEach(tid => {
                $(`#t-${tid}`).prop('checked', true);
            });
        }

        $('#drawer-title').text('Update Product');
        $('#submitBtn').val('update').text('Update Product');
        $('#drawer').addClass('open');
        $('#overlay').fadeIn();
        
    });

    $('#p_price').on('input', function() {
        const currentPrice = parseFloat($(this).val()) || 0;
        
        if (basePrice > 0 && currentPrice < basePrice) {
            // Formula: ((Original - New) / Original) * 100
            let diff = basePrice - currentPrice;
            let discountPercent = (diff / basePrice) * 100;
            
            // Update the discount box (rounded to 2 decimal places)
            $('#p_discount').val(discountPercent.toFixed(2));
        } else if (currentPrice >= basePrice) {
            // If price is same or higher, discount is 0
            $('#p_discount').val(0);
        }
    });

    
    $('#p_image').change(function(e) {
        const file = e.target.files[0];
        if (file) {
            $('#curr_img').attr('src', URL.createObjectURL(file));
        }
    });

    $('#closeDrawer, #overlay').click(() => {
        $('#drawer').removeClass('open');
        $('#overlay').fadeOut();
        $('#p_image').val('');
    });
});
</script>

<h1>PRODUCT</h1>

<div class="admin-bar">
    <form method="get" style="display: flex; gap: 10px; flex-wrap: wrap;">
        <div>
            <label>Search:</label><br>
            <input type="text" name="search" value="<?= encode($search) ?>" placeholder="ID or Name..." style="padding: 8px;">
        </div>
        <div>
            <label>Price Range:</label><br>
            <input type="number" name="min_p" value="<?= encode($min_p) ?>" placeholder="Min" style="width: 80px; padding: 8px;"> - 
            <input type="number" name="max_p" value="<?= encode($max_p) ?>" placeholder="Max" style="width: 80px; padding: 8px;">
        </div>
        <div>
            <label>Status:</label><br>
            <select name="status_f" style="padding: 8px;">
                <option value="">- All -</option>
                <option value="available" <?= $status_f == 'available' ? 'selected' : '' ?>>Available</option>
                <option value="unavailable" <?= $status_f == 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
            </select>
        </div>

        <button type="submit" class="btn-filter">Filter</button>
        <button class="btn-clear" id="btn-clear">Clear</button>
    </form>

    <?php if($_user->role == 'superadmin'): ?>
        <button id="addNewBtn" class="btn-add">+ Add Product</button>
    <?php endif; ?>
</div>

<div class="tabs-container">
    <?php foreach($categories as $index => $c): ?>
        <button class="tab-btn <?= $index === 0 ? 'active' : '' ?>" 
                data-target="cat-<?= $c->c_id ?>">
            <?= $c->name ?>
        </button>
    <?php endforeach; ?>
</div>

<div class="tab-content-wrapper">
    <?php foreach($categories as $index => $c): 
        $tab_sql = $sql . " AND c_id = ?" . $order_clause; 
        $stm = $_db->prepare($tab_sql);
        $stm->execute(array_merge($params, [$c->c_id]));
        $products = $stm->fetchAll();
    ?>
        <div class="tab-pane <?= $index === 0 ? 'active' : '' ?>" id="cat-<?= $c->c_id ?>">
            <div class="product-grid">
                <?php foreach($products as $p):
                    $linked_t = get_linked_toppings($p->p_id);
                ?>
                    <div class="card" 
                        data-id="<?= $p->p_id ?>" 
                        data-name="<?= encode($p->name) ?>" 
                        data-price="<?= $p->price ?>" 
                        data-discount="<?= $p->discount ?>" 
                        data-img="<?= $p->image ?>" 
                        data-cat="<?= $p->c_id ?>"
                        data-status="<?= $p->status ?>"
                        data-cold="<?= $p->cold ?>"
                        data-hot="<?= $p->hot ?>"
                        data-toppings='<?= json_encode($linked_t) ?>'>
                        <img src="/app/images/product/<?= $p->image ?>" alt="<?= $p->name ?>">
                        <h3 style="padding:10px 10px 0;"><?= $p->name ?></h3>
                        <p style="padding:0 10px; margin: 5px 0;">RM<?= sprintf('%.2f', $p->price) ?></p>
                        <span style="font-size: 12px; color: #888; padding-bottom: 10px; display: block;"><?= strtoupper($p->status) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div id="overlay" class="overlay"></div>

<div id="drawer" class="drawer">
    <div class="drawer-content">
        <h2 id="drawer-title">Product Details</h2>
        <form id="prodForm" method="post" enctype="multipart/form-data"  enctype="multipart/form-data">
            <input type="hidden" name="p_id" id="p_id">
            <input type="hidden" name="action" id="submitBtn" value="insert">

            <div class="form-group">
                <label>Current Image:</label>
                <img id="curr_img" src="/app/images/product/default.jpg"  class="img-preview"><br>
                <?php if($_user->role == 'superadmin'): ?>
                    <input type="file" name="image" id="p_image" accept="image/*"><span><?php err('image');?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Product Name:</label>
                <input type="text" name="name" id="p_name" required><span><?php err('name');?></span>
            </div>

            <div class="form-group">
                <label>Price (RM):</label>
                <input type="number" name="price" id="p_price" step="0.05" required><span><?php err('price');?></span>
            </div>

            <div class="form-group">
                <label>Discount (%):</label>
                <input type="number" name="discount" id="p_discount" step="0.05" readonly style="background-color: #eee; cursor: not-allowed;><span><?php err('discount');?></span>
            </div>

            <div class="form-group">
                <label>Category:</label>
                <select name="c_id" id="p_cat">
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c->c_id ?>"><?= $c->name ?></option>
                    <?php endforeach; ?>
                </select><span><?php err('c_id');?></span>
            </div>

            <div class="form-group">
                <label>Status:</label>
                <select name="status" id="p_status">
                    <option value="available">Available</option>
                    <option value="unavailable">Unavailable</option>
                </select><span><?php err('status');?></span>
            </div>

            <div class="form-group">
                <label>Allow temperature:</label>
                
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                    <?php 
                        $is_cold = is_post() ? post('cold') : (isset($p) ? $p->cold : 0);
                        $is_hot  = is_post() ? post('hot')  : (isset($p) ? $p->hot  : 0);
                    ?>
                    <input id="cold" name="cold" type="checkbox" value="1" <?= $is_cold == 1 ? "checked":"" ?>>
                    <label for="cold" style="margin: 0; font-weight: normal;">Cold</label>
                </div>

                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <input id="hot" name="hot" type="checkbox" value="1" <?= $is_hot == 1 ? "checked":"" ?>>
                    <label for="hot" style="margin: 0; font-weight: normal;">Hot</label>
                </div>
            </div>
            
            <div class="form-group">
                <label>Allow Toppings:</label>
                <div class="list-container">
                    <?php foreach ($toppings as $t): ?>
                        <div class="list-item" style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <input type="checkbox" name="t_ids[]" value="<?= $t->t_id ?>" id="t-<?= $t->t_id ?>" style="width: auto;">
                            <label for="t-<?= $t->t_id ?>" style="margin: 0; font-weight: normal; cursor: pointer;">
                                <?= encode($t->name) ?> (+RM<?= sprintf('%.2f',$t->price_per_unit) ?>)
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="drawer-footer">
                <button type="button" class="btn-back" id="closeDrawer">Close</button>
                <?php if($_user->role == 'superadmin'): ?>
                    <button type="submit" class="btn-add">Submit</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php
include '../../_adminfoot.php';
?>