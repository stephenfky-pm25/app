<?php
require '../_base.php';

// ----------------------------------------------------------------------------
if (is_post()) {
    $email = req('email');
    $password = req('password');

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    }
    else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }

    // Validate: password
    if ($password == '') {
        $_err['password'] = 'Required';
    }

    // Login user
    if (!$_err) {
        $u = null;

        // 1. Check Admin Table
        $stm = $_db->prepare('SELECT *, a_id AS id FROM admin WHERE email = ? AND password = SHA1(?)');
        $stm->execute([$email, $password]);
        $u = $stm->fetch(PDO::FETCH_OBJ);

       
        // 2. If not found in Admin, check User Table
        if (!$u) {
            $stm = $_db->prepare('SELECT *, u_id AS id FROM user WHERE email = ? AND password = SHA1(?)');
            $stm->execute([$email, $password]);
            $u = $stm->fetch(PDO::FETCH_OBJ);
            
            if($u) {
                $role = 'user';
            }
        }
   
        // 3. Process Login
        if ($u) {
            if($u->blacklist==1){
                temp('info', 'Your account has been blocked. Please contact support for more information.');
                redirect('/app/');
            }
            temp('info', 'Login successfully');
            if($u->role == "admin"|| $u->role == "superadmin"){
                login($u,"../pages/admin/dashboard.php");
            }else{
                login($u);
            }
        } else {
            $_err['password'] = 'Invalid email or password';
        }
    }
}
// ----------------------------------------------------------------------------
$_title = 'FourLeaves | Login';
include_once '../_head.php';
echo '<main class="login-container">';
?>

    <div class="login-card">
        <img src="/app/images/icon/logo.jpeg" class="login-logo" alt="FourLeaves Logo">
            <h1>Login</h1>
            <form method="post" class="form">
                <div class="input-group"> 
                <label for="email">Email</label>
                <?= html_text('email', 'placeholder="username@gmail.com"','maxlength="100"') ?>
                <?= err('email') ?>
            </div>
    
            <div class="input-group">
                <label for="password">Password</label>
                <?= html_password('password','maxlength="100" placeholder="................."') ?>
                <?= err('password') ?>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-primary">Login</button>
                <button type="reset" class="btn-secondary">Reset</button>
            </div>
        </form>

        <div class="form-footer">
            <div class="footer-row">
                Forgot your password? <a href="/app/security/verify_email.php" style="color: var(--white);">Reset password</a>   
            </div>
            <div class="footer-row">
                Want to create a new account? <a href="/app/security/register.php" style="color: var(--white);">Sign up</a>
            </div>
    </div>
</main>

<?php
include_once "../_foot.php";
?>