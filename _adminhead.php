<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'FourLeaves' ?></title>
    <link rel="shortcut icon" href="/app/images/icon/logo.jpeg">
    <link rel="stylesheet" href="/app/css/app.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/app/js/app.js"></script>
</head>
<body>
    <!-- Flash message -->
    <div id="info" style="background-color:#fff; color:#222"><?= temp('info') ?></div>
    
    <header style="background: #222; color: #fff;">
        <h1 style="font-variant:small-caps; color:#90d895; background:none;">FourLeaves Enterprise</h1>
        <?php if ($_user): ?>
            <a href="/app/pages/profile/profile.php" title="User Profile">
            <div>
                <?= $_user->name ?><br>
                <?= $_user->role ?> <?= $_user->id ?>
            </div>
        </a>

        <?php
            $folder = (($_user->role == 'superadmin' || $_user->role == 'admin') ? 'admin' : 'user');
            $photo_src = "/app/images/$folder/" . ($_user->photo ?: 'defaultuser.webp');
        ?>

        <img src="<?= $photo_src ?>" class="hdr-avatar">
        <?php endif ?>
    </header>