<?php
require '../../_base.php';
//-------------------------------------------------------------

if (is_post() && req('action') == 'add') {
    $name    = post('name');
    $email   = post('email');
    $contact = post('contact');
    $role    = post('role');
    $b_id    = post('branch');
    $password = post('password');

    //validation
    if (!$name) $_err['name'] = 'Name is required';

    if (!$email) {
        $_err['email'] = 'Email is required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email format';
    } else if (!is_unique($email, 'admin', 'email')) {
        $_err['email'] = 'Email already exists';
    }

    $contact_regex = "/^(011\d{8}|01[0,2-9]\d{7})$/";
    if (!$contact){
        $_err['contact']='Contact is required;';
    }else if($contact && !preg_match($contact_regex, $contact)){
        $_err['contact'] = 'Invalid format. Use 011 (11 digits) or 01x (10 digits).';
    }else if(!is_unique($contact, 'admin','contact')){
        $_err['contact']='Contact already exists';
    }

    if (!$_err) {
        $stm = $_db->prepare('
            INSERT INTO admin (name, email, contact, password, role, b_id) 
            VALUES (?, ?, ?, SHA1(?), ?, ?)
        ');
        $stm->execute([$name, $email, $contact, $password, $role, $b_id]);

        temp('info', 'New admin added successfully');
        redirect('?'); 
    }
}

$action = req('action');
$target_id = req('a_id');
if ($action && $target_id) {
    if ($action == 'promote') {
        $stm = $_db->prepare('UPDATE admin SET role = "superadmin" WHERE a_id = ?');
        $stm->execute([$target_id]);
        temp('info', 'Admin promoted to Super Admin');
        redirect('?');
    }

    if ($action == 'demote') {
        $count = $_db->query('SELECT COUNT(a_id) FROM admin WHERE role = "superadmin"')->fetchColumn();
        if ($count > 1) {
            $stm = $_db->prepare('UPDATE admin SET role = "admin" WHERE a_id = ?');
            $stm->execute([$target_id]);
            temp('info', 'Super Admin demoted to Admin');
        }else{
            temp('info', 'At least 1 Super Admin must remain in the system');
        }redirect('?');
    }

    if ($action == 'remove') {
        $stm = $_db->prepare('SELECT role FROM admin WHERE a_id = ?');
        $stm->execute([$target_id]);
        $target = $stm->fetch();

        if ($target) {
            if ($target->role == 'superadmin') {
                $count = $_db->query('SELECT COUNT(a_id) FROM admin WHERE role = "superadmin"')->fetchColumn();
                if ($count > 1) {
                    $stm = $_db->prepare('DELETE FROM admin WHERE a_id = ?');
                    $stm->execute([$target_id]);
                    temp('info', 'Super Admin removed successfully');
                } else {
                    temp('info', 'At least 1 Super Admin must remain in the system');
                }
            } else {
                $stm = $_db->prepare('DELETE FROM admin WHERE a_id = ?');
                $stm->execute([$target_id]);
                temp('info', 'Admin removed successfully');
            }
        }
        redirect('?');
    }
}

// filter and search
$name = get('name');
$role = get('role');
$branch_id = get('branch');

$sql = 'SELECT * FROM admin WHERE 1=1';
$params = [];

if ($name) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$name%";
}
if ($role) {
    $sql .= " AND role = ?";
    $params[] = $role;
}
if ($branch_id) {
    $sql .= " AND b_id = ?";
    $params[] = $branch_id;
}

$stm = $_db->prepare($sql);
$stm->execute($params);
$admins = $stm->fetchAll();

//-------------------------------------------------------------
$_title = 'Admin | Team Management';
include '../../_adminhead.php';
include '../../_adminsidebar.php';
?>

<style>
    main{
        padding: 30px;
        background-color: #ffffff;
        position:relative;
    }

    .search-input, .filter-select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-right: 10px;
        outline: none;
    }

    .btn-add {
        background-color: #90d895;
        border: none;
        box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
        padding: 8px 20px;
        margin:0 10px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
    }

    .team-table {
        width: 100%;
        border-collapse: collapse;
        color: #333;
    }

    .team-table th {
        text-align: left;
        padding: 15px;
        border-bottom: 2px solid #f0f0f0;
        color: #888;
        font-size: 14px;
    }

    .team-table td {
        padding: 15px;
        border-bottom: 1px solid #f9f9f9;
    }

    .badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
    }
    .superadmin_row { background-color: #e8fff6; }
    .admin_row { background-color: #f8ffe9; }

    .btn-resetpw, .btn-promote, .btn-demote, .btn-delete {
        width: 85px;          
        padding: 6px 0;       
        border: none;
        border-radius: 4px;   
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        transition: opacity 0.2s;
        margin: 2px;          
        display: inline-block;
        text-align: center;
        box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
        color: white;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('searchName');
        const roleSelect = document.getElementById('filterRole');
        const branchSelect = document.getElementById('filterBranch');

        function updateFilters() {
            const params = new URLSearchParams(window.location.search);
            
            if (searchInput.value) params.set('name', searchInput.value);
            else params.delete('name');
            
            if (roleSelect.value) params.set('role', roleSelect.value);
            else params.delete('role');
            
            if (branchSelect.value) params.set('branch', branchSelect.value);
            else params.delete('branch');

            window.location.href = window.location.pathname + '?' + params.toString();
        }

        roleSelect.addEventListener('change', updateFilters);
        branchSelect.addEventListener('change', updateFilters);

        let timeout = null;
        searchInput.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(updateFilters, 500);
        });
    });

    $(()=>{

        $('.btn-resetpw').on('click', function() {
            const id = $(this).closest('tr').data('id');
            if(confirm('Reset this admin password to default?')){
                window.location.href = `/app/security/admin_reset.php?action=reset&a_id=${id}`;
            }
        });

        $('.btn-promote').on('click', function() {
            const id = $(this).closest('tr').data('id');
            if (confirm('Promote this admin to Super Admin?')) {
                window.location.href = `?action=promote&a_id=${id}`;
            }
        });

        $('.btn-demote').on('click', function() {
            const id = $(this).closest('tr').data('id');
            if (confirm('Demote this Super Admin to normal Admin?')) {
                window.location.href = `?action=demote&a_id=${id}`;
            }
        });

        $('.btn-delete').on('click', function() {
            const id = $(this).closest('tr').data('id');
            if (confirm('Are you sure you want to remove this admin? This action cannot be undone.')) {
                window.location.href = `?action=remove&a_id=${id}`;
            }
        });

        const openBtn = document.getElementById('btn-add');
        const closeBtn = document.getElementById('closeBtn');
        const clearBtn = document.getElementById('clearBtn');
        const overlay = document.getElementById('overlay');
        const drawer = document.getElementById('drawer');
        const adminForm = document.getElementById('adminForm');

        openBtn.addEventListener('click', () => {
            drawer.classList.add('open');
            overlay.style.display = 'block';
        });

        const closeDrawer = () => {
            drawer.classList.remove('open');
            overlay.style.display = 'none';
        };

        closeBtn.addEventListener('click', closeDrawer);
        overlay.addEventListener('click', closeDrawer);

        clearBtn.addEventListener('click', () => {
            adminForm.reset();
        });

        <?php if ($_err): ?>
            $('#drawer').addClass('open');
            $('#overlay').show();
        <?php endif; ?>
    });
</script>

<h1>TEAM</h1>

<div class="admin-bar">
    <div class="search-group">
        <input type="text" id="searchName" placeholder="Search by name..." class="search-input" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>">
        <select id="filterRole" class="filter-role">
            <option value="">All Role</option>
            <option value="admin" <?= (get('role') ?? '') == 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="superadmin" <?= (get('role') ?? '') == 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
        </select>
        <select id="filterBranch" class="filter-branch">
            <option value="">All Branch</option>
            <?php 
            $stm=$_db->query('SELECT * FROM branch');
            $branch=$stm->fetchAll();
            $currentBranch = get('branch') ?? '';
            foreach($branch as$b):?>
                <option value="<?= $b->b_id ?>" <?= $currentBranch == $b->b_id ? 'selected' : '' ?>><?= $b->name ?></option>
            <?php endforeach?>
        </select>
    </div>
    <button id="btn-add" class="btn-add">+ Add Admin</button>
    <div id="overlay" class="overlay"></div>

    <div id="drawer" class="drawer">
        <div class="drawer-content">
            <h2>ADD ADMIN</h2>
            <form id="adminForm" method="post" action="?action=add">
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" placeholder="Enter name" value="<?= htmlspecialchars(post('name')) ?? null?>"><span><?= err('name') ?></span>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="text" name="email" placeholder="Enter email" value="<?= htmlspecialchars(post('email')) ?? null ?>"><span><?= err('email') ?></span>
                </div>
                <div class="form-group">
                    <label>Contact:</label>
                    <input type="text" name="contact" placeholder="Enter phone no." value="<?= htmlspecialchars(post('contact')) ?? null ?>"><span><?= err('contact') ?></span>
                </div>
                <div class="form-group">
                    <label>Default Password:</label>
                    <input type="text" name="password" value="123456" readonly>
                </div>
                <div class="form-group">
                    <select name="role">
                        <option value="superadmin">Super Admin</option>
                        <option value="admin">Admin</option>
                    </select>
                    <select name="branch">
                        <?php
                            $stm=$_db->query('SELECT * FROM branch');
                            $branches=$stm->fetchAll();
                            foreach($branches as $b):
                        ?>
                        <option value="<?= $b->b_id ?>"><?= $b->name ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                    <div class="drawer-footer">
                    <button type="button" class="btn-back" id="closeBtn">Back</button>
                    <button type="reset" class="btn-clear" id="clearBtn">Clear</button>
                    <button class="btn-submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<table class="team-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Role</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Branch</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php

        $branch=$_db->prepare('SELECT * FROM branch WHERE b_id = ?');
        foreach($admins as $a):?>
        <tr class="<?= $a->role ?>_row" data-id="<?= $a->a_id ?>">
            <td><?= $a->a_id ?></td>
            <td><strong><?= htmlspecialchars($a->name) ?></strong></td>
            <td><?= $a->role=="superadmin" ? "Super Admin" : "Admin" ?></td>
            <td><?= htmlspecialchars($a->email) ?></td>
            <td><?= htmlspecialchars($a->contact) ?></td>
            <td>
                <?php
                $branch->execute([$a->b_id]);
                $b=$branch->fetch();
                echo "$b->name";
                ?>
            </td>
            <td>
                <?php if($a->role=="superadmin"):?>
                    <button class="btn-demote">Demote&nbsp;</button>
                <?php else:?>
                    <button class="btn-promote">Promote</button>
                <?php endif?>
                <button class="btn-resetpw">Reset PW</button>
                <button class="btn-delete">Remove</button>
            </td>
        </tr>
        <?php endforeach?>
    </tbody>
</table>

<?php
include '../../_adminfoot.php';
?>