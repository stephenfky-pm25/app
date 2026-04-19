<?php
require '../../_base.php';
//-------------------------------------------------------------

if (is_post()) {
    $action = post('action');
    $u_id = post('u_id');
    $selected_users = post('blacklist_users');

    if ($action == 'block' && $u_id) {
        $stm = $_db->prepare('UPDATE user SET blacklist = 1 WHERE u_id = ?');
        $stm->execute([$u_id]);
        temp('info', 'User added to blacklist.');
        redirect();
    }

    if ($action == 'unblock' && !empty($selected_users)) {
        $placeholders = implode(',', array_fill(0, count($selected_users), '?'));
        $stm = $_db->prepare("UPDATE user SET blacklist = NULL WHERE u_id IN ($placeholders)");
        $stm->execute($selected_users);
        temp('info', 'Selected users removed from blacklist.');
        redirect();
    }
}

$q = get('q');
$reg_date = get('reg_date');
$reg_type = get('reg_type');
$min_orders = get('min_orders');
$min_paid = get('min_paid');




$sql = "SELECT u.*, 
        COUNT(o.o_id) AS total_orders, 
        SUM(CASE WHEN o.status = 'paid' THEN o.total ELSE 0 END) AS total_paid
        FROM user u
        LEFT JOIN orders o ON u.u_id = o.u_id
        WHERE (u.blacklist IS NULL OR u.blacklist != 1)";
$params = [];

if ($q) {
    $sql .= " AND (u.u_id LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $params = array_merge($params, ["%$q%", "%$q%", "%$q%"]);
}

if ($reg_date) {
    $operator = ($reg_type == 'before') ? '<=' : '>=';
    $sql .= " AND u.register_date $operator ?";
    $params[] = $reg_date;
}

$sql .= " GROUP BY u.u_id HAVING 1=1";

if ($min_orders) {
    $sql .= " AND total_orders >= ?";
    $params[] = $min_orders;
}

if ($min_paid) {
    $sql .= " AND total_paid >= ?";
    $params[] = $min_paid;
}

$stm = $_db->prepare($sql);
$stm->execute($params);
$users = $stm->fetchAll();


$blacklisted = $_db->query('SELECT * FROM user WHERE blacklist = 1')->fetchAll();
//-------------------------------------------------------------
$_title = 'Admin | User Management';
include '../../_adminhead.php';
include '../../_adminsidebar.php';
?>

<main>
    <h1>USER</h1>
    <div class="admin-bar">
        <form id="filter-form" method="get">
            <input type="search" name="q" value="<?= $q ?>" placeholder="Search ID, Name, Email" class="search-input">
            
            <select name="reg_type" class="filter-select">
                <option value="after" <?= $reg_type == 'after' ? 'selected' : '' ?>>Register After</option>
                <option value="before" <?= $reg_type == 'before' ? 'selected' : '' ?>>Register Before</option>
            </select>
            <input type="date" name="reg_date" value="<?= $reg_date ?>" class="filter-select">

            <input type="number" name="min_orders" value="<?= $min_orders ?>" placeholder="Min Orders" class="filter-select" style="width: 100px;">
            <input type="number" name="min_paid" value="<?= $min_paid ?>" placeholder="Min Paid (RM)" class="filter-select" style="width: 100px;">
            
            <button type="submit" class="btn-filter">Filter</button>
            <a href="?" class="btn-clear">Clear</a>
            <button type="button" class="btn-add" id="openBlacklist">Blacklist</button>
        </form>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Register Date</th>
                <th>Total Orders</th>
                <th>Total Paid (RM)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u->u_id ?></td>
                <td><strong><?= htmlspecialchars($u->name) ?></strong><br><small><?= htmlspecialchars($u->email) ?></small></td>
                <td><?= htmlspecialchars($u->contact) ?></td>
                <td><?= $u->register_date ?></td>
                <td><?= $u->total_orders ?></td>
                <td><?= number_format($u->total_paid, 2) ?></td>
                <td>
                    <form method="post" onsubmit="return confirm('Blacklist this user?')">
                        <input type="hidden" name="u_id" value="<?= $u->u_id ?>">
                        <input type="hidden" name="action" value="block">
                        <button type="submit" class="btn-delete" style="width: 110px;">Add to Blacklist</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div id="overlay" class="overlay"></div>
    <div id="blacklist-drawer" class="drawer">
        <div class="drawer-content">
            <div class="drawer-header">
                <button type="button" id="closeDrawer" class="btn-back">Back</button>
                <h2>Blacklisted Users</h2>
            </div>
            <form method="post">
                <input type="hidden" name="action" value="unblock">
                <div class="drawer-body">
                    <?php if (empty($blacklisted)): ?>
                        <p>No users in blacklist.</p>
                    <?php else: ?>
                        <?php foreach ($blacklisted as $bu): ?>
                        <div class="blacklist-item">
                            <label>
                                <input type="checkbox" name="blacklist_users[]" value="<?= $bu->u_id ?>">
                                <?= htmlspecialchars($bu->name) ?> (<?= htmlspecialchars($bu->email) ?>)
                            </label>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="drawer-footer">
                    <button type="submit" class="btn-primary" <?= empty($blacklisted) ? 'disabled' : '' ?>>Remove Selected</button>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
    .btn-clear { text-decoration:none;}
    .blacklist-item { margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid #eee; }
    .btn-add{ background-color: black; color:white;}
</style>
<script>
$(() => {
    // Open drawer and show overlay
    $('#openBlacklist').click(function() {
        $('#blacklist-drawer').addClass('open');
        $('#overlay').fadeIn(); // Use jQuery fadeIn for the darkening effect
    });

    // Close drawer when clicking 'X' or the darkened overlay
    $('#closeDrawer, #overlay').click(() => {
        $('#blacklist-drawer').removeClass('open');
        $('#overlay').fadeOut(); // Smoothly hide the darkening effect
    });
});
</script>
<?php include '../../_adminfoot.php'; ?>