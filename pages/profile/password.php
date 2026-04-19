<?php
require '../../_base.php';

// ----------------------------------------------------------------------------
auth();
$table = (($_user->role == 'admin')||($_user->role == 'superadmin')) ? 'admin' : 'user';
$pk    = (($_user->role == 'admin')||($_user->role == 'superadmin')) ? 'a_id'  : 'u_id';
$id    = $_user->id;

if (is_post()) {
    $old_password = post('old_password');
    $password     = post('password');
    $confirm      = post('confirm');

    // 1. Validation
    if ($old_password == '') {
        $_err['old_password'] = 'Required';
    }
    if ($password == '') {
        $_err['password'] = 'Required';
    } else if (strlen($password) < 6) {
        $_err['password'] = 'Too short (min 6 characters)';
    }
    if ($confirm == '') {
        $_err['confirm'] = 'Required';
    } else if ($password != $confirm) {
        $_err['confirm'] = 'Passwords do not match';
    }

    // 2. Check old password against database
    if (!isset($_err['old_password'])) {
        $stm = $_db->prepare("SELECT password FROM $table WHERE $pk = ?");
        $stm->execute([$id]);
        $db_pass = $stm->fetchColumn();

        if (sha1($old_password) !== $db_pass) {
            $_err['old_password'] = 'Incorrect current password';
        }
    }

    // 3. Update Database
    if (!$_err) {
        $stm = $_db->prepare("UPDATE $table SET password = SHA1(?) WHERE $pk = ?");
        $stm->execute([$password, $id]);

        temp('info', 'Password updated successfully');
        redirect();
    }
}
// ----------------------------------------------------------------------------
if($_user->role=="admin" || $_user->role=="superadmin"){
    $title = 'Admin | Change Password';
    include '../../_adminhead.php';
    
}else{
    $_title = 'FourLeaves | Change Password';
    include '../../_head.php';
}

include 'profile_sidebar.php';
?>
<style>
    main{
        width:95%;
    }
    .login-container{
        background:none;
    }
    .pw-card {
        background: rgba(255, 255, 255, 0.15);
        padding: 2rem 3rem;
        width: 100%;
        max-width: 420px;
        text-align: center;
        color: white;
    }

    .input-group {
        display: flex;
        flex-direction: column;
        text-align: left;
        margin-bottom: 15px;
    }

    .input-group label {
        font-size: 0.85rem;
        margin-bottom: 5px;
        color: rgba(139, 139, 139, 0.9);
    }

    .input-group input {
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.9);
        border:1px solid #ababab;
        box-sizing: border-box;
    }
</style>
<main class="login-container">
    <div class="pw-card">
        <form method="post" class="form">
            <div class="input-group">
                <label for="old_password">Current Password</label> 
                <?= html_password('old_password', 'maxlength="100" placeholder="Enter current password"') ?>
                <?= err('old_password') ?>
            </div>  

            <div class="input-group">
                <label for="password">New Password</label>
                <?= html_password('password', 'maxlength="100" placeholder="Enter new password"') ?>
                <?= err('password') ?>
            </div>
            
            <div class="input-group">
                <label for="confirm">Confirm New Password</label>
                <?= html_password('confirm', 'maxlength="100" placeholder="Repeat new password"') ?>
                <?= err('confirm') ?>
            </div>

            <div class="button-group">
                <button class="btn-primary">Confirm Change</button>
                <button type="reset" class="btn-secondary">Reset</button>
            </div>
        </form>
    </div>
</main>
<?php
if($_user->role =="admin" || $_user->role=="superadmin"){
    include '../../_adminfoot.php';
}else{
    echo '</main>';
    echo '</div>';
    include '../../_foot.php';
}
?>