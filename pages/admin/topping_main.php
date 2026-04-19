<?php
require '../../_base.php';
//-------------------------------------------------------------
auth("superadmin", "admin");

// --- Backend Logic: Insert / Update ---
if (is_post()) {
    $action = post('action');
    $t_id   = post('t_id');
    $name   = post('name');
    $price  = post('price');
    $p_ids  = post('p_ids') ?? [];

    // Validation
    if (!$name) $_err['name'] = 'Topping name is required';
    if ($price === '' || !is_numeric($price)) $_err['price'] = 'Valid price is required';

    if (!$_err) {
        if ($action == 'insert') {
            $stm = $_db->prepare('INSERT INTO topping (name, price_per_unit) VALUES (?, ?)');
            $stm->execute([$name, $price]);
            $t_id = $_db->lastInsertId();
            
            // Insert relationships
            $stm = $_db->prepare('INSERT INTO topping_list (t_id, p_id) VALUES (?, ?)');
            foreach ($p_ids as $p_id) {
                $stm->execute([$t_id, $p_id]);
            }
            temp('info', 'Topping added successfully');

        } else if ($action == 'update') {
            // Update topping details
            $stm = $_db->prepare('UPDATE topping SET name = ?, price_per_unit = ? WHERE t_id = ?');
            $stm->execute([$name, $price, $t_id]);

            // Sync relationships: Delete old, Insert new
            $stm = $_db->prepare('DELETE FROM topping_list WHERE t_id = ?');
            $stm->execute([$t_id]);

            $stm = $_db->prepare('INSERT INTO topping_list (t_id, p_id) VALUES (?, ?)');
            foreach ($p_ids as $p_id) {
                $stm->execute([$t_id, $p_id]);
            }
            temp('info', 'Topping updated successfully');
        }
        redirect();
    }
}

// --- Backend Logic: Delete ---
if (req('action') == 'delete' && $id = req('t_id')) {
    $stm = $_db->prepare('DELETE FROM topping_list WHERE t_id = ?');
    $stm->execute([$id]);
    
    $stm = $_db->prepare('DELETE FROM topping WHERE t_id = ?');
    $stm->execute([$id]);
    
    temp('info', 'Topping removed successfully');
    redirect('?');
}

// --- Fetch Data ---
$search = get('search');
$sql = "SELECT * FROM topping WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$search%";
}
$sql .= " ORDER BY name ASC";

$stm = $_db->prepare($sql);
$stm->execute($params);
$toppings = $stm->fetchAll();

// Get all products for the checkboxes
$products = $_db->query('SELECT p_id, name FROM product ORDER BY c_id ASC')->fetchAll();

// Helper: Get product IDs linked to a topping for the frontend JS
function get_linked_products($t_id) {
    global $_db;
    $stm = $_db->prepare('SELECT p_id FROM topping_list WHERE t_id = ?');
    $stm->execute([$t_id]);
    return $stm->fetchAll(PDO::FETCH_COLUMN);
}

//-------------------------------------------------------------
$_title = 'Admin | Topping Maintenance';
include '../../_adminhead.php';
include '../../_adminsidebar.php';
?>

<style>
    main { padding: 30px; background-color: #ffffff; position: relative; }
    
    .product-list { max-height: 200px; overflow-y: auto; border: 1px solid #eee; padding: 10px; border-radius: 4px; }
    .product-item { display: flex; align-items: center; gap: 10px; margin-bottom: 5px; }
    .product-item input { width: auto; }
</style>

<h1>TOPPINGS</h1>

<div class="admin-bar">
    <form method="get" id="searchForm" style="display: flex; align-items: center;">
        <input type="text" name="search" placeholder="Search topping..." class="search-input" value="<?= encode($search) ?>">
        <button type="submit"class="btn-filter" style="margin-left: 10px;">Filter</button>
        <button type="button" class="btn-clear" id="btn-clear">Clear</button>
    </form>
    <button id="btn-add" class="btn-add">+ Add Topping</button>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price (RM)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($toppings as $t): 
            $linked = get_linked_products($t->t_id);
        ?>
        <tr data-id="<?= $t->t_id ?>" data-name="<?= encode($t->name) ?>" data-price="<?= sprintf('%.2f',$t->price_per_unit)?>" data-products='<?= json_encode($linked) ?>'>
            <td><?= $t->t_id ?></td>
            <td><strong><?= encode($t->name) ?></strong></td>
            <td><?= sprintf('%.2f',$t->price_per_unit) ?></td>
            <td>
                <button class="btn-upd">Update</button>
                <button class="btn-delete">Delete</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div id="overlay" class="overlay"></div>
<div id="drawer" class="drawer">
    <div class="drawer-content">
        <h2 id="drawer-title">Add Topping</h2>
        <form id="toppingForm" method="post">
            <input type="hidden" name="t_id" id="t_id">
            <input type="hidden" name="action" id="form-action" value="insert">

            <div class="form-group">
                <label>Topping Name:</label>
                <input type="text" name="name" id="t_name" required>
                <span class="err"><?= err('name') ?></span>
            </div>

            <div class="form-group">
                <label>Price per Unit (RM):</label>
                <input type="number" name="price" id="t_price" step="0.01" required>
                <span class="err"><?= err('price') ?></span>
            </div>

            <div class="form-group">
                <label>Apply to Products:</label>
                <div class="product-list">
                    <?php foreach ($products as $p): ?>
                        <div class="product-item">
                            <input type="checkbox" name="p_ids[]" value="<?= $p->p_id ?>" id="p-<?= $p->p_id ?>">
                            <label for="p-<?= $p->p_id ?>"><?= encode($p->name) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="drawer-footer">
                <button type="button" class="btn-back" id="btn-close">Back</button>
                <button type="submit" class="btn-submit" id="btn-submit">Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
$(() => {
    const $drawer = $('#drawer');
    const $overlay = $('#overlay');
    const $form = $('#toppingForm');

    // Open for Add
    $('#btn-add').click(() => {
        $form[0].reset();
        $('#t_id').val('');
        $('#form-action').val('insert');
        $('#drawer-title').text('Add New Topping');
        $('input[type="checkbox"]').prop('checked', false);
        $drawer.addClass('open');
        $overlay.fadeIn();
    });

    // Open for Update
    $('.btn-upd').click(function() {
        const row = $(this).closest('tr').data();
        $('#t_id').val(row.id);
        $('#t_name').val(row.name);
        $('#t_price').val(row.price);
        $('#form-action').val('update');
        $('#drawer-title').text('Update Topping');

        // Check relevant product checkboxes
        $('input[type="checkbox"]').prop('checked', false);
        const linkedProducts = row.products; // This is the array from data-products
        linkedProducts.forEach(pid => {
            $(`#p-${pid}`).prop('checked', true);
        });

        $drawer.addClass('open');
        $overlay.fadeIn();
    });

    // Delete Logic
    $('.btn-delete').click(function() {
        const id = $(this).closest('tr').data('id');
        if (confirm('Are you sure you want to delete this topping? This will remove it from all linked products.')) {
            window.location.href = `?action=delete&t_id=${id}`;
        }
    });

    // Close Logic
    $('#btn-close, #overlay').click(() => {
        $drawer.removeClass('open');
        $overlay.fadeOut();
    });

    <?php if ($_err): ?>
        $drawer.addClass('open');
        $overlay.show();
    <?php endif; ?>
});
</script>

<?php include '../../_adminfoot.php'; ?>