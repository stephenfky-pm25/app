<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'FourLeaves' ?></title>
    <link rel="shortcut icon" href="/app/images/icon/logo.ico">
    <link rel="stylesheet" href="/app/css/app.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/app/js/app.js"></script>
</head>
<body>
    <!-- Flash message -->
    <div id="info"><?= temp('info') ?></div>
    
    <header style="background: #90d895;">
        <img src = "/app/images/icon/logo_greenbg.png">
        <nav>
            <a href="/app/">Home</a>
            <a href="/app/index.php#branch">Branch</a>
            <a href="/app/index.php#about">About Us</a>

            <?php if(($_user ? $_user->role : '')!="admin" && ($_user ? $_user->role : '')!="superadmin" ):?>
                <a href="/app/pages/product/product.php">Products</a>
                <a href="/app/pages/order/cart.php">Cart
                <?php
                $cart = get_cart();
                $count = count($cart);
                if($count)echo"($count)";?>
            </a>
            <?php
            endif;
            if (!$_user) {
                echo '<a href="/app/security/login.php">Login</a>';
            }
            ?>
        </nav>
        <?php if ($_user): ?>
        <div class="user-info">
            <a href="/app/pages/profile/profile.php">
                <?= $_user->name ?><br>
                <?= $_user->role ?> <?= $_user->id ?>
            </a>
        </div>

        <?php
        $f=(($_user->role == 'superadmin' || $_user->role == 'admin') ? 'admin' : 'user');
        $final_path = "/app/images/$f/" . ($_user->photo ?: 'defaultuser.webp');
        ?>

        <img src="<?= $final_path ?>" class="hdr-avatar">
        <?php endif ?>
    </header>
