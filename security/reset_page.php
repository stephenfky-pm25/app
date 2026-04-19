<?php
require '../_base.php';

//----------------------------------------------------------------------------------

// TODO: (1) Delete expired tokens
$_db->query('DELETE FROM token WHERE expire < NOW()');

$id = req('token_id');

// TODO: (2) Is token id valid?
if (!is_exists($id, 'token','token_id')) {
    temp('info', 'Invalid token. Try again');
    redirect('/');
}

if (is_post()) {
    $password = req('password');
    $confirm  = req('confirm');

    // Validate: password
    if ($password == '') {
        $_err['password'] = 'Required';
    }
    else if (strlen($password) < 5 || strlen($password) > 100) {
        $_err['password'] = 'Between 5-100 characters';
    }

    // Validate: confirm
    if ($confirm == '') {
        $_err['confirm'] = 'Required';
    }
    else if (strlen($confirm) < 5 || strlen($confirm) > 100) {
        $_err['confirm'] = 'Between 5-100 characters';
    }
    else if ($confirm != $password) {
        $_err['confirm'] = 'Not matched';
    }

    // DB operation
    if (!$_err) {
        // TODO: Update user (password) based on token id + delete token
        $stm = $_db->prepare('
            UPDATE user
            SET password = SHA1(?)
            WHERE u_id = (SELECT u_id FROM token WHERE token_id = ?);

            DELETE FROM token WHERE token_id = ?;
        ');
        $stm->execute([$password, $id, $id]);

        temp('info', 'Record updated');
        redirect('/security/login.php');
    }
}
//-----------------------------------------------------------------------------------------------------------------------------------
$_title = 'Four Leaves | Reset Password';
include '../_head.php';
?>
<main class="login-container">
    <form method="post">
        <div class="login-card">
            <img src="./images/icon/logo.jpeg" class="login-logo" alt="FourLeaves Logo">
            <h1> Reset Password</h1>
            <div class="input-group">
                <label for="password">Password</label>
                <?= html_password('password', 'maxlength="100" placeholder="Create password"') ?>
                <?= err('password') ?>
            </div>
            
            <div class="input-group">
                <label for="confirm">Confirm Password</label>
                <?= html_password('confirm', 'maxlength="100" placeholder="Repeat password"') ?>
                <?= err('confirm') ?>
            </div>
            
            <div class="button-group">
                <button class="btn-primary">Submit</button>
                <button type="reset" class="btn-secondary">Reset</button>
            </div>
        </div>
    </form>
</main>

<?php
include '../_foot.php';
?>