<?php
require '../../_base.php';
//----------------------------------------------------------------------
if($_user && ($_user->role=="admin" || $_user->role=="superadmin")){
    redirect('/');
}

if (is_post()) {
    $p_id = post('p_id');
    $u_id = $_user->id; 

    if(!$u_id){
        temp('info',"Login to add favourites");
        redirect("/app/security/login.php");
    }else{
        $stm = $_db->prepare('SELECT * FROM favourite WHERE p_id = ? AND u_id = ?');
        $stm->execute([$p_id, $u_id]);
        
        if ($stm->fetch()) {
            $stm = $_db->prepare('DELETE FROM favourite WHERE p_id = ? AND u_id = ?');
            temp('info',"Item $p_id is removed from favourites");
        } else {
            $stm = $_db->prepare('INSERT INTO favourite (p_id, u_id) VALUES (?, ?)');
            temp('info',"Item $p_id is added to favourites");
        }
        $stm->execute([$p_id, $u_id]);

        redirect('/app/pages/product/product.php'); 
    }
}

// get category from database
$stm=$_db->query('SELECT * FROM category');
$categories=$stm->fetchAll();

// get products from database
$stm=$_db->query('SELECT * FROM product WHERE status="available"');
$products=$stm->fetchAll();

// get favourites of the user from database
if($_user){
    $stm=$_db->prepare('SELECT * FROM favourite f JOIN product p ON f.p_id = p.p_id WHERE f.u_id=? AND p.status="available" ');
    $stm->execute([$_user->id]);
    $favourites=$stm->fetchAll();
    
    $is_fav=[];
    foreach($products as $p){
        $is_fav[$p->p_id] = 'not-fav';
    }
    foreach($favourites as $f){
        $is_fav[$f->p_id] = 'fav';
    }
}else{
    $is_fav=[];
    foreach($products as $p){
        $is_fav[$p->p_id] = 'not-fav';
    }
}

//----------------------------------------------------------------------
$_title = 'FourLeaves | Product';
include '../../_head.php';
echo '<main>';
?>

<style>
    .layout-container{
        display:flex;
        align-items:stretch;
        width:100%;
        padding:0;
    }

    aside{
        flex: 0 0 300px;
        height:max-content;
        background-color:#dcffd9;
        border-radius: 20px;
        margin:20px;
    }

    .category-block{
        height: 10px;
        padding: 20px 50px;
        justify-content: center;
    }

    .category-block:hover{
        background-color: #0e3c11;
        color:#fff;
        cursor: pointer;
    }

    main{
        flex:1;
        padding:20px;
        justify-items: left;
        justify-self: end;
    }


    .card{
        display:inline-block;
        width:300px;
        height:340px;
        border:1px solid #ccc;
        border-radius:10px;
        margin:10px;
        text-align:center;
    }

    .card img{
        width:100%;
        height:240px;
        object-fit:cover;
        border-radius:5px 5px 0 0;
    }

    .card:hover{
        transform: translate(0, -10px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        z-index: 10;
    }

    .fav{
        cursor: pointer;
    }

    .not-fav{
        cursor: pointer;
    }
</style>
    
<script>
    $(()=>{
        $('.category-block').on('click', e => {
            const id = e.currentTarget.id;
            window.location.href = (id === 'favourites') ? '#favourites_item' : `#${id}_item`;
        });
    });
</script>

<div class="layout-container">
    <aside>
        <div>
            <h2 style="text-align: center;">CATEGORY</h2>
            <div id="favourites" name="favourites" class="category-block"><label>Favourites</label></div>
            <?php foreach($categories as $c):?>
                <div id="<?= $c->c_id ?>" name="<?= $c->c_id ?>" class="category-block"><label><?= $c->name ?></label></div>
            <?php endforeach;?>
        </div>
    </aside>
    <main>
        <h1>PRODUCT</h1>
        <?php if($_user && count($favourites)!=0): ?>
        <h2 id="favourites_item">Favourites</h2>
        <div>
            <?php foreach($favourites as $f):?>
                <div class="card" title="Click to add to cart" onclick="window.location.href='details.php?p_id=<?= $f->p_id ?>'">
                    <img src="/app/images/product/<?= $f->image ?>" alt="image of <?= $f->name ?>">
                    <h3><?= $f->name ?></h3>
                    <span>RM<?= $f->price ?></span>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="p_id" value="<?= $f->p_id ?>">
                        <label id="fav-btn_<?= $is_fav[$f->p_id] ?>" class="<?= $is_fav[$f->p_id] ?>"
                            title="Remove from favourites">♥️
                            <input type="submit" style="display:none;">
                        </label>
                    </form>
                </div>
            <?php endforeach;?>
        </div>
        <?php endif;?>
        <?php
        if(!$_user){
            echo '<p><a href="/app/security/login.php">Login</a> to view your favourites.</p>';}
        else if($_user && count($favourites) == 0){
            echo '<p>You have no favourites yet.</p>';}
        ?>
        
        <?php
        foreach($categories as $c):
            $stm=$_db->prepare('SELECT * FROM product WHERE status="available" AND c_id=?');
            $stm->execute([$c->c_id]);
            $products=$stm->fetchAll();?>
            <h2 id="<?= $c->c_id ?>_item"><?= $c->name ?></h2>
            <div>
                <?php foreach($products as $p):?>
                    <div class="card" title="Click to add to cart" onclick="window.location.href='details.php?p_id=<?= $p->p_id ?>'">
                        <img src="/app/images/product/<?= $p->image ?>" alt="image of <?= $p->name ?>">
                        <h3><?= $p->name ?></h3>
                        <span>RM<?= $p->price ?></span>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="p_id" value="<?= $p->p_id ?>">
                            <label id="fav-btn_<?= $is_fav[$p->p_id] ?>" class="<?= $is_fav[$p->p_id] ?>" 
                                title="<?= $is_fav[$p->p_id] == 'fav' ? 'Remove from favourites' : 'Add to favourites' ?>">
                                <?= $is_fav[$p->p_id] == 'fav' ? '♥️' : '🖤' ?>
                                <input type="submit" style="display:none;">
                            </label>
                        </form>
                    </div>
                <?php endforeach;?>
        <?php endforeach;?>
    </main>
</div>
</main>
<?php
include '../../_foot.php';
?>