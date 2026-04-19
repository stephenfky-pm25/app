<style>
    .app-wrapper{
        display: flex;
        min-height: 100vh;
        width: 100%;
    }
    .sidebar{
        background-color: #222;
        position:relative;
        height:100vh;
        width:15%;
        padding:0;
    }

    .sidebar-block{
        height:5%;
        width:85%;
        margin:0 0;
        border-bottom:1px solid #888;
        align-content: center;
        color:#fff;
        padding: 0 5% 0;
        cursor: pointer; 
        transition: background 0.3s;
    }

    .sidebar-block:hover {
        background-color: #90d895;
        color:#222;
    }

    main{
        flex-grow: 1;
        background-color: #ffffff;
        overflow-y: auto;
        padding: 20px;
    }
</style>
<script>
    $(()=>{
    $('.sidebar-block').on('click', e => {
        const link = $(e.currentTarget).attr('href');
        if (link) {
            window.location.href = link;
        }
    });
});
</script>
<div class="app-wrapper">
    <div class="sidebar">
        <div class="sidebar-block" href="/app/pages/admin/dashboard.php">Dashboard</div>
        <div class="sidebar-block" href="/app/pages/admin/order_main.php">Order</div>
        <div class="sidebar-block" href="/app/pages/admin/prod_main.php">Product</div>
        <div class="sidebar-block" href="/app/pages/admin/cat_main.php">Category</div>
        <div class="sidebar-block" href="/app/pages/admin/topping_main.php">Toppings</div>
        <?php if($_user->role=="superadmin"):?>
            <div class="sidebar-block" href="/app/pages/admin/user_mng.php">User</div>
            <div class="sidebar-block" href="/app/pages/admin/team_mng.php">Team</div>
            <div class="sidebar-block" href="/app/pages/admin/branch_main.php">Branch</div>
        <?php endif;?>
        <div class="sidebar-block" href="/app/pages/admin/feedback_mng.php">Feedback</div>
    </div>

    <main>