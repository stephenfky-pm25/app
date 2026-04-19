<?php
require '../../_base.php';

// ----------------------------------------------------------------------------
auth();
$table = (($_user->role == 'admin')||($_user->role == 'superadmin')) ? 'admin' : 'user';
$pk    = (($_user->role == 'admin')||($_user->role == 'superadmin')) ? 'a_id'  : 'u_id';

if ($_user->role == 'admin' || $_user->role == 'superadmin') {
    
    $stm = $_db->prepare("
        SELECT a.*, b.name AS branch_name
        FROM admin a
        LEFT JOIN branch b ON a.b_id = b.b_id
        WHERE a.a_id = ?
    ");
} else {
    $stm = $_db->prepare("SELECT * FROM user WHERE u_id = ?");
}

$stm->execute([$_user->id]);
$u = $stm->fetch(PDO::FETCH_OBJ);

if (!$u) {
    redirect('/');
}

$email = $u->email;
$name  = $u->name;
$photo = $u->photo;
$contact = $u->contact;

// if admin, get branch name; if member then null
$branch_name = $u->branch_name ?? null;

$_SESSION['temp_photo'] = $u->photo;


if (is_post()) {
    $email = req('email');
    $name  = req('name');
    $contact = req('contact');
    $photo = $_SESSION['temp_photo'];
    $f = get_file('photo');

    // --- Validation: email ---
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    } else {
        // Check for duplicate email in BOTH tables
        $stm = $_db->prepare("
            SELECT (SELECT COUNT(*) FROM user WHERE email = ? AND u_id != ?) +
                   (SELECT COUNT(*) FROM admin WHERE email = ? AND a_id != ?)
        ");
        
        // If user is admin, we exclude their a_id. If member, exclude u_id.
        $exclude_uid = ($_user->role != 'admin' && $_user->role != 'superadmin') ? $_user->id : 0; 
        $exclude_aid = ($_user->role == 'admin' || $_user->role == 'superadmin') ? $_user->id : 0;
        
        $stm->execute([$email, $exclude_uid, $email, $exclude_aid]);

        if ($stm->fetchColumn() > 0) {
            $_err['email'] = 'Duplicated';
        }
    }

    // --- Validation: name ---
    if ($name == '') {
        $_err['name'] = 'Required';
    }

    // --- Validation: contact ---
        $contact_regex = "/^(011\d{8}|01[0,2-9]\d{7})$/";
        if ($contact == '') {
            $_err['contact'] = 'Required';
        }else if(!preg_match($contact_regex, $contact)){
            $_err['contact'] = 'Invalid format. Use 011 (11 digits) or 01x (10 digits).';
        }
        

    // --- DB operation ---
    if (!$_err) {
        if ($table == 'admin') {
            $subfolder = 'admin';
        } else {
            $subfolder = 'user'; 
        }
        $target_dir = "../../images/$subfolder";
    
    
        if ($f) {
            // Delete old photo if it exists and isn't the default
            if ($photo && $photo != 'defaultuser.webp' && file_exists("$target_dir/$photo")) {
                unlink("$target_dir/$photo");
            }

            $photo = save_photo($f, $target_dir);
        }
        
        // Update the correct table
       if ($table == 'user') {

            $stm = $_db->prepare("
                UPDATE user 
                SET email = ?, name = ?, contact = ?, photo = ? 
                WHERE u_id = ?
            ");
            $stm->execute([$email, $name, $contact, $photo, $_user->id]);
            $_user->contact = $contact;

        } else {

            $stm = $_db->prepare("
                UPDATE admin 
                SET email = ?, name = ?, contact=?, photo = ? 
                WHERE a_id = ?
            ");
            $stm->execute([$email, $name, $contact, $photo, $_user->id]);
        }

        // Update global session object so changes show up immediately
        $_user->email = $email;
        $_user->name = $name;
        $_user->photo = $photo;
        $_user->contact = $contact;
        $_SESSION['temp_photo'] = $photo;
        temp('info', 'Profile updated successfully');
        redirect('/app/pages/profile/profile.php'); 
    }
}

//----------------------------------------------------------------------

if($_user->role=="admin" || $_user->role=="superadmin"){
    $title = 'Admin | Profile';
    include '../../_adminhead.php';
    
}else{
    $_title = 'FourLeaves | Profile';
    include '../../_head.php';
}
include 'profile_sidebar.php';
?>
<style>
    main{
        width:95%;
    }
    
    .profile-container{
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
        padding: 20px;
    }
</style>
<main class="profile-container">

    <div class="profile-content">

        <!-- Top Profile Picture -->
        <div class="profile-picture">
            <?php
                if ($_user->role == 'admin' || $_user->role == 'superadmin') {
                    $role_folder = 'admin';
                } else {
                    $role_folder = 'user';
                }
                $img_src = "/app/images/$role_folder/" . ($photo ?: 'defaultuser.webp');
            ?>
            <img id="preview" src="<?=  $img_src ?>">
            <p>Profile Picture</p>
        </div>

        <form method="post" class="profile-form" enctype="multipart/form-data">

            <div class="form-row">
                <label>Photo:</label>
                <label class="upload">
                    <input type="file" name="photo" id="photoInput" accept="image/*" hidden>
                    <span class="browse-btn">Browse</span>
                </label>
            </div>
            <?= err('photo') ?>

            <div class="form-row">
                <label>Email:</label>
                <?= html_text('email', 'maxlength="100"') ?>
            </div>
            <?= err('email') ?>

            <div class="form-row">
                <label>Name:</label>
                <?= html_text('name', 'maxlength="100"') ?>
            </div>
            <?= err('name') ?>

            <div class="form-row">
                <label>Contact:</label>
                <?= html_text('contact', 'maxlength="11"') ?>
            </div>
            <?= err('contact') ?>

            <?php if (($_user->role =='admin' || $_user->role =='superadmin') && $branch_name): ?>
                <div class="form-row">
                    <label>Branch:</label>
                    <input type="text" value="<?=  $branch_name ?>" readonly
                            style="background-color: #f0f0f0; color: #666; cursor: not-allowed; border: 1px solid #ccc;">
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <button>Update Profile</button>
                <button type="reset">Reset</button>
            </div>

        </form>


<a href="/app/security/logout.php" class="logout-link">Logout</a>

<script>
document.getElementById('photoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        document.getElementById('preview').src = URL.createObjectURL(file);
    }
});
</script>

<?php
if($_user->role =="admin" || $_user->role=="superadmin"){
    include '../../_adminfoot.php';
}else{
    echo '</main>';
    echo '</div>';
    include '../../_foot.php';
}
?>