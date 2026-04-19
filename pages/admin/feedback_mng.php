<?php
require '../../_base.php';

// only admin and superadmin can access this page
auth('admin', 'superadmin'); 

$types = $_db->query('SELECT * FROM feedback_type')->fetchAll();

$ft_id = req('ft_id');
$params = [];

// Fetch feedback list with optional type filter
$sql = 'SELECT f.*, u.name as user_name, u.email as user_email, ft.name as type_name 
        FROM feedback f
        JOIN user u ON f.u_id = u.u_id
        JOIN feedback_type ft ON f.ft_id = ft.ft_id';


if ($ft_id != "") {
    $sql .= ' WHERE f.ft_id = ?';
    $params[] = $ft_id;
}
$sql .= ' ORDER BY f.date_create DESC';

$stmt = $_db->prepare($sql);
$stmt->execute($params);
$feedbacks = $stmt->fetchAll();

// If an ID is provided, fetch the specific feedback details for the reply view
$f_id = req('id');
$detail = null;
if ($f_id) {
    $stmt = $_db->prepare('
        SELECT f.*, u.name as user_name, u.email as user_email, ft.name as type_name 
        FROM feedback f
        JOIN user u ON f.u_id = u.u_id
        JOIN feedback_type ft ON f.ft_id = ft.ft_id
        WHERE f.f_id = ?
    ');
    $stmt->execute([$f_id]);
    $detail = $stmt->fetch();
}

$_title = 'Admin | Feedback Management';
include '../../_adminhead.php';
include '../../_adminsidebar.php';
?>

<main class="admin-content">
    <?php if (!$detail): ?>
        <div class="admin-table-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1>Feedback Management</h1>
                
                <?php if ($_user->role == 'superadmin'): ?>
                    <a href="feedback_type_mng.php" class="btn-submit" style="text-decoration:none; font-size: 0.8rem;">Manage Feedback Types</a>
                <?php endif; ?>
            </div>

            <form method="get" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
                <label>Filter by Type:</label>

                <select name="ft_id" style="padding: 5px; border-radius: 5px; border: 1px solid #ddd;">
                    <option value="">-- All Types --</option>
                       <?php foreach ($types as $t): ?>
                        <option value="<?= $t->ft_id ?>" <?= $t->ft_id == $ft_id ? 'selected' : '' ?>>
                            <?= $t->name ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                
                <button type="submit" class="btn-submit" style="padding: 5px 20px; cursor: pointer;">Filter</button>
            </form>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($feedbacks) > 0): ?>
                        <?php foreach ($feedbacks as $f): ?>
                    <tr>
                        <td><?= $f->f_id ?></td>
                        <td><?= $f->user_name ?></td>
                        <td><?= $f->type_name ?></td>
                        <td><?= $f->date_create ?></td>
                        <td>
                            <?php if ($f->reply): ?>
                                <span class="status-badge status-replied">Replied</span>
                            <?php else: ?>
                                <span class="status-badge status-pending">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?id=<?= $f->f_id ?>" class="btn-view">
                                <?= $f->reply ? 'View Reply' : 'View & Reply' ?>   
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 50px; color: #999; font-style: italic;">
                            No feedback found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <div class="feedback-detail-view">
            <a href="feedback_mng.php" style="color: #666;">← Back to List</a>
            <h2 style="margin-top: 15px;">Feedback from <?= $detail->user_name ?></h2>
            <p><strong>Type:</strong> <?= $detail->type_name ?></p>
            <p><strong>Date:</strong> <?= $detail->date_create ?></p>
            <hr>
            <div style="margin: 20px 0;">
                <strong>Message:</strong>
                <p style="background: #f9f9f9; padding: 15px; border-radius: 8px;"><?= nl2br(htmlentities($detail->message)) ?></p>
            </div>

            <div class="image-preview-group">
                <?php for($i=1; $i<=5; $i++): 
                    $imgField = "image$i";
                    if ($detail->$imgField): ?>
                        <img src="../../images/feedback/<?= $detail->$imgField ?>" alt="Feedback Image" style="width:100px; height:100px; object-fit:cover; margin-right:5px; border-radius:5px;">
                    <?php endif; 
                endfor; ?>
            </div>

            <hr>

            <?php if ($detail->reply): ?>
                <h3>Admin Reply:</h3>
                <div style="background: #e9f7ef; padding: 20px; border-radius: 10px; border-left: 5px solid #28a745; margin-bottom: 20px;">
                    <p style="margin: 0; white-space: pre-wrap; color: #333;"><?= htmlentities($detail->reply) ?></p>
                </div>
                <p style="color: #666; font-size: 0.9rem; font-style: italic;">
                    (This feedback has been handled and the user notified via email.)
                </p>
            <?php else: ?>
                <h3>Reply to User (via Email)</h3>
                <form action="feedback_reply_send.php" method="post">
                    <input type="hidden" name="f_id" value="<?= $detail->f_id ?>">
                    <input type="hidden" name="user_email" value="<?= $detail->user_email ?>">

                    <div class="form-row" style="margin-bottom: 15px;">
                        <label>Admin Reply Message:</label>
                        <textarea name="reply_text" styl="width:100%; height:150px; border-radius:10px; border:1px solid #ddd; padding:10px;" required placeholder="Enter your reply here..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        Send Email & Update Status
                    </button>
                </form>

            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

            

<?php include '../../_adminfoot.php'; ?>