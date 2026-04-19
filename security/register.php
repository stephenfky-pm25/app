<?php
require '../_base.php';

//----------------------------------------------------------------------------------
//Registration
if(is_post()) {
    $email = req('email');
    $password = req('password');
    $confirm = req('confirm');

    //Validation logic
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {               
        $_err['email'] = 'Invalid email';
    }

    if ($password == '') {                        
        $_err['password'] = 'Required';
    } else if (strlen($password) < 6) {
        $_err['password'] = 'Too short (min 6 characters)';
    } else if ($confirm == ''){
        $_err['confirm'] = 'Required';
    } else if ($password != $confirm) {                    
        $_err['confirm']  = 'Passwords do not match';
    }

    if(!$_err) {
        //check whether have duplicate email in admin and user table
        // 1. Check Admin Table
        $stm = $_db->prepare('SELECT email FROM admin WHERE email = ? UNION SELECT email FROM user WHERE email = ?');
        $stm->execute([$email, $email]);
        $u = $stm->fetchAll();
        if($u){
            temp('info', 'This email is already registered');
            redirect('/app/security/login.php');
        }else{
            //register account
            $stm = $_db->prepare('
                INSERT INTO user(email, password, role)
                VALUES (?, SHA1(?), "member")
            ');
            $stm->execute([$email, $password]);
            temp('info', 'Account created! Please login.');
            redirect('/app/security/login.php');
        }
    }
}
//-----------------------------------------------------------------------------------------------------------------------------------
$_title = 'Four Leaves | Register';
include '../_head.php';
?>
<script>
    
</script>
<main class="login-container">
    <div class="login-card">
        <img src="/app/images/icon/logo.jpeg" class="login-logo" alt="FourLeaves Logo">
        <h1>Register</h1>
        
        <form method="post" class="form">
            <div class="input-group">
                <label for="email">Email</label> 
                <?= html_text('email', 'maxlength="100" placeholder="name@example.com"') ?>
                <?= err('email') ?>
            </div>  

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
                <button class="btn-primary">Create Account</button>
                <button type="reset"  class="btn-secondary">Reset</button>
            </div>
        </form>

        <div class="form-footer">
            <div class="footer-row">
            <span>Already have an account?</span>
            <a href="/app/security/login.php">Login here</a>
        </div>
    </div>
</main>

<?php
include '../_foot.php';
?>