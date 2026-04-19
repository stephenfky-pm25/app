<?php
require '../../_base.php';
// ----------------------------------------------------------------------------
if($_user && ($_user->role=="admin" || $_user->role=="superadmin")){
    redirect('/');
}

if(is_post()){
    $id=req("p_id");
    $temp=req("opt_temp");
    if($temp=="hot"){
        $opt_ice=null;
    }else{
        $opt_ice=req("opt_ice");
    }
    $opt_sugar=req("opt_sugar");
    $toppings = req("opt_topping") ?? [];
    $qty=(int)req("qty");
    $remark=req("remark");
    update_cart($id,$temp, $opt_ice, $opt_sugar, $toppings, $qty, $remark);

    //display notification
    temp('info', 'Added to cart');
    redirect("/app/pages/product/product.php");
}

$p_id = req('p_id');
$stm = $_db->prepare('SELECT * FROM product WHERE p_id = ?');
$stm->execute([$p_id]);
$product = $stm->fetch();

$stm = $_db->prepare('SELECT t.t_id, t.name, t.price_per_unit FROM topping t JOIN topping_list tl ON t.t_id = tl.t_id JOIN product p ON tl.p_id = p.p_id WHERE tl.p_id = ? ORDER BY t.name');
$stm->execute([$p_id]);
$toppings = $stm->fetchAll();

if(!$product){
    redirect('/pages/product/product.php');
}
// ----------------------------------------------------------------------------
$_title = 'FourLeaves | Product Details';
include '../../_head.php';
?>

<main>
<style>
    .layout-container{
        display: flex; 
        align-items: center; 
        padding:10px;
        margin:10px;
        border:1px solid #eee;
        border-radius: 10px;
        box-shadow:0 2px 5px rgba(0,0,0,0.1);
    }

    .layout-container > div{
        display:inline-block;
        padding:10px;
        margin:10px;
        width:400px;
        height:fit-content;
    }

    .layout-container img{
        width: 400px;
        height: 500px;
        border-radius: 8px;
    }

    main{
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }
    
    .radio-group{
        display:grid;
        grid:auto / auto auto;
        justify-self: center;
        background-color: #b1d1b3;
        border-radius: 50px;
        height:100%;
        width: fit-content;
        padding:3px 0 5px;
    }

    .radio-group input{
        display: none;
        width:100%;
        height:100%;
    }

    .radio-group div{
        border-radius: 50px;
        cursor: pointer;
        margin:0;
        padding:12px 30px 10px;
    }

    .radio-group input:checked + div{
        background-color: #0e3c11;
        color:#fff;
        justify-self:center;
        box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
    }

    .config-option{
        margin: 15px 0;
    }

    .q-btn{
        width:25px;
        height:25px;
        border-radius:5px;
    }

    #remark{
        display: inline;
        margin: 5px 0;
    }

    .confirm-add-btn{
        background-color: #4CAF50;
        color:#eee;
        border-radius:50px;
        padding:10px 20px;
        margin:10px auto;
    }

    .back-btn{
        border-radius:50px;
        padding:10px 20px;
        margin:10px auto;
    }
</style>

<script>
    function changeQty(amt) {
        let q = parseInt($('#qty').val());
        q += amt;
        if (q < 1) q = 1;
        $('#qty').val(q);
    }

    function toggleIceSelector(temp) {
        if (temp == 'hot') {
            $('#ice-level-container').hide();
        } else {
            $('#ice-level-container').show();
        }
    }

</script>
<div class="layout-container">
    <div>
            <?php if ($product->image): ?>
                <img src="/app/images/product/<?= $product->image ?>" alt="image of <?= $product->name ?>">
            <?php endif; ?>
    </div>
    
    <div class="details-section">
        <h2><?= $product->name ?>&nbsp;&nbsp;&nbsp;RM<?= $product->price ?></h2>
        <form method="post" id="cart-form">
            <input type="hidden" name="p_id" id="p_id" value="<?= $product->p_id ?>">
            
            <div class="config-option">
                <div class="radio-group">
                    <label><input type="radio" name="opt_temp" value="cold" <?= $product->cold==1 ? "checked" :""?><?= $product->cold==0 ? "disabled" : "" ?> onclick="toggleIceSelector('cold')"><div style="display:inline;">❄️ Cold</div></label>
                    <label><input type="radio" name="opt_temp" value="hot" <?= $product->cold==0 ? "checked" :""?><?= $product->hot==0 ? "disabled" : "" ?> onclick="toggleIceSelector('hot')"><div style="display:inline;">🔥 Hot</div></label>
                </div>
            </div>

            <div class="config-option" id="ice-level-container">
                <label>Ice Level</label>
                <select name="opt_ice" style="width:100%; padding:8px; border-radius:8px; border:1px solid #eee;">
                    <option value="100">100% Regular Ice</option>
                    <option value="75">75% Less Ice</option>
                    <option value="50">50% Half Ice</option>
                    <option value="25">25% Quarter Ice</option>
                    <option value="0">0% No Ice</option>
                </select>
            </div>

            <div class="config-option">
                <label>Sugar Level</label>
                <select name="opt_sugar" style="width:100%; padding:8px; border-radius:8px; border:1px solid #eee;">
                    <option value="100">100% Regular</option>
                    <option value="75">75% Less Sugar</option>
                    <option value="50">50% Half Sugar</option>
                    <option value="25">25% Quarter Sugar</option>
                    <option value="0">0% No Sugar</option>
                </select>
            </div>

            <div class="config-option">
                <label>Topping (Hold Ctrl/Cmd to select multiple)</label>
                <select name="opt_topping[]" multiple style="width:100%; padding:8px; border-radius:8px; border:1px solid #eee;">
                    <option value="">None</option>
                    <?php foreach ($toppings as $t): ?>
                        <option value="<?= $t->t_id ?>"><?= htmlspecialchars($t->name) ?> (+ RM <?= number_format($t->price_per_unit, 2) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="action-bar">
                <div class="quantity-selector">
                    <button type="button" class="q-btn" onclick="changeQty(-1)">-</button>
                    <input type="text" name="qty" id="qty" class="q-num-input" value="1" readonly>
                    <button type="button" class="q-btn" onclick="changeQty(1)">+</button>
                    <input type="text" name="remark" id="remark" placeholder="Remarks" maxlength="100" value="">
                </div>
                <button formmethod="get" formaction="/pages/product/product.php" class="back-btn">Back</button>
                <button class="confirm-add-btn">+ Add to Cart</button>
            </div>
        </form>
    </div>
</div>
</main>
<?php
include '../../_foot.php';
?>