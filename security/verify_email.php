<?php
require '../_base.php';

//------------------------------------------------------------------------------------
//Reset password/Forgot password
if(is_post()) {
    $email = req('email');

    //1. Validation
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    } else if ((!is_exists($email,'user','email')) && (!is_exists($email,'admin','email'))){
        $_err['email']='Not exists';
    }
    
    //2. Search for email in both tables
    if (!$_err) {
        // (1) Select user
        $stm = $_db->prepare('SELECT * FROM user WHERE email = ?');
        $stm->execute([$email]);
        $u = $stm->fetch();
        if(!$u){
            $stm = $_db->prepare('SELECT *  FROM admin WHERE email = ?');
            $stm->execute([$email]);
            $u=$stm->fetch();
            if($u){
                temp('info','Please find superadmin to reset password');
                redirect('/');
            }
        }else{
            // (2) Generate token id
            $id = sha1(uniqid() . rand());

            // (3) Delete old and insert new token
            $stm = $_db->prepare('
                DELETE FROM token WHERE u_id = ?;

                INSERT INTO token (token_id, expire, u_id)
                VALUES (?, ADDTIME(NOW(), "00:05"), ?);
            ');
            $stm->execute([$u->u_id, $id,$u->u_id]);

            // (4) Generate token url
            $url = base("/security/reset_page.php?token_id=$id");
            // (5) Send email
            $m = get_mail();
            $m->addAddress($u->email,$u->name);
            $photo=$u->photo??"defaultuser.webp";
            $m->addEmbeddedImage(root("images/user/$photo"),"photo");
            $m->isHTML(true);
            $m->Subject = 'Reset Password';
            $m->Body = "
                <img src='cid:photo'
                    style='width: 200px; height: 200px;
                            border: 1px solid #333'>
                <p>Dear $u->name,<p>
                <h1 style='color: red'>Reset Password</h1>
                <p>
                    Please click <a href='$url'>here</a>
                    to reset your password.
                </p>
                <p>From, 🧋 FourLeaves</p>
            ";
            $m->send();
            temp('info', 'Email sent');
            redirect('/');
        }
    }
}    

//-----------------------------------------------------------------------------------------------------------
$_title = 'Four Leaves | Forgot Password';
include '../_head.php';
?>

<main class="login-container">
    <div class="login-card">
        <img src="/app/images/icon/logo.jpeg" class="login-logo" alt="FourLeaves Logo">
        <h1> Reset Password</h1>
        <p class="insturction-text">Enter your registered email to make email verification</p>
    
        <form method="post" class="form">
            <div class="input-group">
                <label for="email">Email Address</label>
                <?= html_text('email', 'maxlength="100" placeholder="name@gmail.com"') ?>
                <?= err('email') ?>
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn-primary">Get email</button>
                <button type="reset"  class="btn-secondary">Reset</button>
            </div>
        </form>
        
        <div class="form-footer">
            <div class="footer-row">
            <span>Remember your password?</span>
            <a href="/app/security/login.php">Back to Login</a>
        </div>
    </div>
</main>

<?php
include '../_foot.php';
?>