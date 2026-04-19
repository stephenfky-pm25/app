<style>
    .wrapper {
        display: flex;
        align-items: flex-start; 
        width: 100%;
    }
    .sidebar{
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
        padding: 0 5% 0;
        cursor: pointer; 
        transition: background 0.3s;
    }

    .sidebar-block:hover {
        background-color: #0e3c11;
        color: #fff;
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

<div class="wrapper">
    <div class="sidebar">
        <?php
        if($_user->role =="admin" || $_user->role =="superadmin"):?>
            <div class="sidebar-block" href="/app/pages/admin/dashboard.php">← Back to Dashboard</div>
        <?php else:?>
            <div class="sidebar-block" href="/app/">← Back to Home</div>
        <?php endif; ?>
        <div class="sidebar-block" href="/app/pages/profile/profile.php">Personal Info</div>
        <div class="sidebar-block" href="/app/pages/profile/password.php">Change Password</div>
        <?php
        if($_user->role =="member"):?>
            <div class="sidebar-block" href="/app/pages/profile/address.php">Manage Address</div>
            <div class="sidebar-block" href="/app/pages/profile/history.php">Order History</div>
            <div class="sidebar-block" href="/app/pages/profile/feedback.php">Feedback to Us</div>
        <?php endif;?>
    </div>
    <main>