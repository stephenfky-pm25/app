<?php
require '../../_base.php';
//-------------------------------------------------------------

// admin and superadmin can access this page
auth('admin', 'superadmin');

$name = req('name');

$params = [];
$sql = 'SELECT * FROM category';
if ($name) {
    $sql .= ' WHERE name LIKE ?';
    $params[] = "%$name%";
}
$sql .= ' ORDER BY c_id DESC';

$stm = $_db->prepare($sql);
$stm->execute($params);
$categories = $stm->fetchALl();

$id = req('id');
$detail = null;
if ($id) {
    $stm = $_db->prepare('SELECT * FROM category WHERE c_id = ?');
    $stm->execute([$id]);
    $detail = $stm->fetch();
}

//-------------------------------------------------------------
$_title = 'Admin | Category Maintenance';
include '../../_adminhead.php';
include '../../_adminsidebar.php';
?>

<main class="admin-content">
    <?php if (!$detail && !isset($_GET['add'])): ?>
        <div class="admin-table-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1>Category Maintenance</h1>
                <?php if ($_user->role == 'superadmin'): ?>
                    <a href="?add=1" class="btn-submit" style="text-decoration:none;">+ Create New Category</a>
                <?php endif; ?>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">ID</th>
                        <th style="width: 25%;">Name</th>
                        <th style="width: 50%;">Description</th>
                        <th style="width: 15%; text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><?= $c->c_id ?></td>
                        <td><strong><?= $c->name ?></strong></td>
                        <td><?= $c->description ?></td>
                        <td style="text-align:center;">
                            <a href="?id=<?= $c->c_id ?>" class="btn-view">
                                <?= $_user->role == 'superadmin' ? 'Edit' : 'View Details' ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <div class="feedback-detail-view" style="max-width: 800px; margin: 0 auto; padding: 40px;">
            <a href="cat_main.php" style="color: #666; text-decoration: none;">← Back to List</a>
            
            <h2 style="margin-top: 20px; font-size: 28px;">
                <?php 
                    if (isset($_GET['add'])) echo "Create New Category";
                    else echo ($_user->role == 'superadmin' ? "Update Category" : "Category Details");
                ?>
            </h2>
            <hr style="margin-bottom: 30px;">

            <form action="category_save.php" method="post">
                <input type="hidden" name="c_id" value="<?= $detail->c_id ?? '' ?>">
                
                <div class="form-row" style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 10px; font-weight: bold; font-size: 18px;">Category Name:</label>
                    <input type="text" name="name" value="<?= $detail->name ?? '' ?>" 
                           style="width:100%; padding:15px; border-radius:8px; border:1px solid #ddd; font-size: 16px;"
                           <?= $_user->role != 'superadmin' ? 'disabled' : 'required' ?>>
                </div>

                <div class="form-row" style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 10px; font-weight: bold; font-size: 18px;">Description:</label>
                    <textarea name="description" style="width:100%; height:200px; padding:15px; border-radius:8px; border:1px solid #ddd; font-size: 16px; line-height: 1.5;"
                              <?= $_user->role != 'superadmin' ? 'disabled' : 'required' ?>><?= $detail->description ?? '' ?></textarea>
                </div>

                <?php if ($_user->role == 'superadmin'): ?>
                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn-submit" style="padding: 12px 30px; font-size: 18px; cursor: pointer;">
                            <?= $detail ? 'Update' : 'Create' ?>
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    <?php endif; ?>
</main>

<?php
include '../../_adminfoot.php';
?>