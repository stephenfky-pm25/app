<?php
require '../../_base.php';

// only superadmin can access this page
auth('superadmin'); 


if ($id = req('delete')) {
    $stmt = $_db->prepare('DELETE FROM feedback_type WHERE ft_id = ?');
    $stmt->execute([$id]);
    temp('info', 'Type deleted successfully');
    redirect('feedback_type_mng.php');
}

if (is_post()) {
    $ft_id = req('ft_id');
    $name = req('name');
    $description = req('description');

    if ($ft_id) {
        $stmt = $_db->prepare('UPDATE feedback_type SET name = ?, description = ? WHERE ft_id = ?');
        $stmt->execute([$name, $description, $ft_id]);
        temp('info', 'Type updated successfully');
    } else {
        $stmt = $_db->prepare('INSERT INTO feedback_type (name, description) VALUES (?, ?)');
        $stmt->execute([$name, $description]);
        temp('info', 'New type added successfully');
    }
    redirect('feedback_type_mng.php');
}

$types = $_db->query('SELECT * FROM feedback_type')->fetchAll();
$edit_type = null;
if ($edit_id = req('edit')) {
    foreach($types as $t) if($t->ft_id == $edit_id) $edit_type = $t;
}

$_title = 'Admin | Manage Feedback Types';
include '../../_adminhead.php';
include '../../_adminsidebar.php';
?>

<main class="admin-content">
    <div class="admin-table-card" style="max-width: 800px;">
        <a href="feedback_mng.php" style="color: #666;">← Back to Feedback</a>
        <h1>Manage Feedback Types</h1>

        <form method="post" style="background: #f9f9f9; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
            <input type="hidden" name="ft_id" value="<?= $edit_type->ft_id ?? '' ?>">
            <div style="margin-bottom: 10px;">
                <label>Type Name:</label><br>
                <input type="text" name="name" value="<?= $edit_type->name ?? '' ?>" required style="width: 100%; padding: 8px;">
            </div>
            <div style="margin-bottom: 10px;">
                <label>Description:</label><br>
                <textarea name="description" style="width: 100%; padding: 8px;"><?= $edit_type->description ?? '' ?></textarea>
            </div>
            <button class="btn-submit"><?= $edit_type ? 'Update' : 'Add New Type' ?></button>
            <?php if ($edit_type): ?><a href="feedback_type_mng.php">Cancel</a><?php endif; ?>
        </form>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($types as $t): ?>
                <tr>
                    <td><?= $t->ft_id ?></td>
                    <td><?= $t->name ?></td>
                    <td><?= $t->description ?></td>
                    <td>
                        <a href="?edit=<?= $t->ft_id ?>">Edit</a> | 
                        <a href="?delete=<?= $t->ft_id ?>" onclick="return confirm('Delete this type?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include '../../_adminfoot.php'; ?>