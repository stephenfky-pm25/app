<?php
require '../../_base.php';

// --- Backend Logic: Insert / Update ---
if (is_post()) {
    $action  = post('action');
    $b_id    = post('b_id');
    $name    = post('name');
    $start   = post('start_time');
    $end     = post('end_time');
    $date    = post('branch_open_date');
    $rest_day = post('rest_day');
    
    $prefix  = post('contact_prefix');
    $phone   = post('contact_rest');
    $contact = $prefix . $phone; 

    $number  = post('number');
    $street  = post('street');
    $city    = post('city');
    $state   = post('state');
    $postcode = post('postcode');

    // --- Validation ---
    if (!$name) $_err['name'] = 'Required';
    if (!preg_match('/^\d{7,10}$/', $phone)) $_err['contact'] = '7-10 digits required';
    if (!$start || !$end) $_err['time'] = 'Required';
    if (!$date) $_err['branch_open_date'] = 'Required';
    if (!$number || !$street || !$city || !$state) $_err['address'] = 'Required';
    if (!preg_match('/^\d{5}$/', $postcode)) $_err['postcode'] = '5 digits';

    // Image Upload
    $photoName = post('old_photo'); 
    if (!empty($_FILES['photo']['name'])) {
        $file = $_FILES['photo'];
        if (str_starts_with($file['type'], 'image/')) {
            if ($action == 'update' && $photoName) @unlink("../../images/branch/" . $photoName);
            $photoName = uniqid() . '.jpg';
            move_uploaded_file($file['tmp_name'], "../../images/branch/" . $photoName);
        } else {
            $_err['photo'] = 'Invalid image';
        }
    } else if ($action == 'insert') {
        $_err['photo'] = 'Photo required';
    }

    if (!$_err) {
        if ($action == 'insert') {
            $stm = $_db->prepare("INSERT INTO branch(photo, name, start_time, end_time, branch_open_date, contact, rest_day) VALUES (?,?,?,?,?,?,?)");
            $stm->execute([$photoName, $name, $start, $end, $date, $contact, $rest_day]);
            $new_id = $_db->lastInsertId();
            
            $stm = $_db->prepare("INSERT INTO address(b_id, number, street, city, state, postcode) VALUES (?,?,?,?,?,?)");
            $stm->execute([$new_id, $number, $street, $city, $state, $postcode]);
            temp('info', 'Branch added');
        } else if ($action == 'update') {
            $stm = $_db->prepare("UPDATE branch SET photo=?, name=?, start_time=?, end_time=?, branch_open_date=?, contact=?, rest_day=? WHERE b_id=?");
            $stm->execute([$photoName, $name, $start, $end, $date, $contact, $rest_day, $b_id]);
            
            $stm = $_db->prepare("UPDATE address SET number=?, street=?, city=?, state=?, postcode=? WHERE b_id=?");
            $stm->execute([$number, $street, $city, $state, $postcode, $b_id]);
            temp('info', 'Branch updated');
        }
        redirect();
    }
}

// --- Delete Logic ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stm = $_db->prepare("SELECT photo FROM branch WHERE b_id = ?");
    $stm->execute([$id]);
    $photo = $stm->fetchColumn();
    if ($photo) @unlink("../../images/branch/" . $photo);
    $_db->prepare("DELETE FROM address WHERE b_id = ?")->execute([$id]);
    $_db->prepare("DELETE FROM branch WHERE b_id = ?")->execute([$id]);
    temp('info', 'Branch deleted');
    redirect('branch_main.php');
}

// ADDED: a.number to the query
$branches = $_db->query("SELECT b.*, a.number, a.street, a.city, a.state, a.postcode FROM branch b LEFT JOIN address a ON b.b_id = a.b_id")->fetchAll();
include '../../_adminhead.php';
include '../../_adminsidebar.php';
?>

<style>
    .action-cell { display: flex; flex-direction: column; gap: 5px; align-items: center; }
    .action-cell a{ text-decoration: none;}
    .form-group { display: flex; align-items: center; margin-bottom: 12px; gap: 10px; }
    .form-group label { flex: 0 0 100px; font-weight: bold; font-size: 0.85em; color: #333; }
    .input-container { flex: 1; display: flex; flex-direction: column; }
    .form-group input, .form-group select { padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; height: 34px; width: 100%; box-sizing: border-box; }
    .multi-row { display: flex; gap: 15px; margin-bottom: 12px; }
    .multi-row .form-group { flex: 1; margin-bottom: 0; }
    #photo-preview { width: 80px; height: 80px; border: 1px dashed #aaa; margin-bottom: 5px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    #photo-preview img { width: 100%; height: 100%; object-fit: cover; }
    .err { color: red; font-size: 0.75em; }
    hr { border: 0; border-top: 1px solid #eee; margin: 15px 0; }
    h4 { margin: 0 0 10px 0; font-size: 0.9em; color: #666; text-transform: uppercase; }
</style>

<h1>BRANCH</h1>

<div class="admin-bar"><button id="btn-add" class="btn-add">+ Add Branch</button></div>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Info</th>
            <th>Operation</th>
            <th>Address</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($branches as $b): ?>
        <tr data-id="<?= $b->b_id ?>" 
            data-name="<?= $b->name ?>" 
            data-start="<?= $b->start_time ?>"
            data-end="<?= $b->end_time ?>" 
            data-date="<?= $b->branch_open_date ?>" 
            data-contact="<?= $b->contact ?>"
            data-rest_day="<?= $b->rest_day ?>" 
            data-number="<?= $b->number ?>" 
            data-street="<?= $b->street ?>" 
            data-city="<?= $b->city ?>"
            data-state="<?= $b->state ?>" 
            data-postcode="<?= $b->postcode ?>" 
            data-photo="<?= $b->photo ?>">
            
            <td><?= $b->b_id ?></td>
            <td><img src="/app/images/branch/<?= $b->photo ?>" width="70" height="70"></td>
            <td><strong><?= $b->name ?></strong><br><small><?= $b->contact ?></small></td>
            <td><small>Rest: <?= $b->rest_day ?></small><br><small><?= $b->start_time ?> - <?= $b->end_time ?></small></td>
            <td><small><?= $b->number ?>, <?= $b->street ?>,<br><?= $b->city ?>, <?= $b->postcode ?> <?= $b->state ?></small></td>
            <td>
                <div class="action-cell">
                    <button class="btn-upd">Update</button>
                    <a href="?delete=<?= $b->b_id ?>" class="btn-delete" onclick="return confirm('Delete permanently?')">Delete</a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div id="overlay" class="overlay"></div>
<div id="drawer" class="drawer <?= $_err ? 'open' : '' ?>"> <div class="drawer-content">
        <h2 id="drawer-title" style="margin-top:0;">
            <?= post('action') === 'update' ? 'Update Branch' : 'Add Branch' ?>
        </h2>
        
        <form id="branchForm" method="post" enctype="multipart/form-data">
            <input type="hidden" name="b_id" id="b_id" value="<?= post('b_id') ?>">
            <input type="hidden" name="old_photo" id="old_photo" value="<?= post('old_photo') ?>">
            <input type="hidden" name="action" id="form-action" value="<?= post('action') ?: 'insert' ?>">

            <div class="form-group">
                <label>Photo:</label>
                <div class="input-container">
                    <div id="photo-preview">
                        <?php if (post('old_photo')): ?>
                            <img src="/app/images/branch/<?= post('old_photo') ?>">
                        <?php else: ?>
                            <span>No image</span>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="photo" id="f_photo" accept="image/*">
                    <div class="err"><?= err('photo') ?></div>
                </div>
            </div>

            <div class="form-group">
                <label>Name:</label>
                <div class="input-container">
                    <input type="text" name="name" id="f_name" required value="<?= htmlspecialchars(post('name')) ?>">
                    <div class="err"><?= err('name') ?></div>
                </div>
            </div>

            <div class="form-group">
                <label>Contact:</label>
                <div class="input-container">
                    <div style="display: flex; gap: 5px;">
                        <select name="contact_prefix" id="f_prefix" style="width: 80px;">
                            <?php foreach ($_prefixes as $p): ?>
                                <option value="<?= $p ?>" <?= post('contact_prefix') == $p ? 'selected' : '' ?>><?= $p ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="contact_rest" id="f_phone" placeholder="1234567" required minlength="7" maxlength="10" value="<?= post('contact_rest') ?>">
                    </div>
                    <div class="err"><?= err('contact') ?></div>
                </div>
            </div>

            <div class="form-group">
                <label>Open Date:</label>
                <div class="input-container">
                    <input type="date" name="branch_open_date" id="f_date" required value="<?= post('branch_open_date') ?>">
                    <div class="err"><?= err('branch_open_date') ?></div>
                </div>
            </div>

            <div class="multi-row">
                <div class="form-group">
                    <label style="flex: 0 0 65px;">Rest:</label>
                    <select name="rest_day" id="f_rest_day">
                        <?php $days = ['None', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']; 
                        foreach($days as $d): ?>
                            <option value="<?= $d ?>" <?= post('rest_day') == $d ? 'selected' : '' ?>><?= $d=='None' ? $d : substr($d, 0, 3) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label style="flex: 0 0 45px;">Start:</label>
                    <input type="time" name="start_time" id="f_start" required value="<?= post('start_time') ?>">
                </div>
                <div class="form-group">
                    <label style="flex: 0 0 40px;">End:</label>
                    <input type="time" name="end_time" id="f_end" required value="<?= post('end_time') ?>">
                </div>
            </div>
            <div class="err"><?= err('time') ?></div>

            <hr>
            <h4>Address</h4>
            
            <div class="form-group">
                <label>Number:</label>
                <input type="text" name="number" id="f_number" required value="<?= htmlspecialchars(post('number')) ?>">
            </div>
            <div class="form-group">
                <label>Street:</label>
                <input type="text" name="street" id="f_street" required value="<?= htmlspecialchars(post('street')) ?>">
            </div>

            <div class="multi-row">
                <div class="form-group">
                    <label style="flex: 0 0 50px;">City:</label>
                    <input type="text" name="city" id="f_city" required value="<?= htmlspecialchars(post('city')) ?>">
                </div>
                <div class="form-group">
                    <label style="flex: 0 0 50px;">State:</label>
                    <input type="text" name="state" id="f_state" required value="<?= htmlspecialchars(post('state')) ?>">
                </div>
            </div>
            <div class="err"><?= err('address') ?></div>

            <div class="form-group">
                <label>Postcode:</label>
                <div class="input-container">
                    <input type="text" name="postcode" id="f_postcode" maxlength="5" style="width: 100px;" required value="<?= post('postcode') ?>">
                    <div class="err"><?= err('postcode') ?></div>
                </div>
            </div>

            <div class="drawer-footer">
                <button type="button" class="btn-back" id="btn-close">Back</button>
                <button type="submit" class="btn-submit" id="btn-submit-text">
                    <?= post('action') === 'update' ? 'Update Branch' : 'Save Branch' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(() => {
    const $drawer = $('#drawer'), $overlay = $('#overlay'), $preview = $('#photo-preview');

    // Helper to handle prefix and phone splitting
    function setPhoneFields(fullNumber) {
        if (!fullNumber) return;
        // Logic to handle both hyphenated and non-hyphenated numbers
        if (fullNumber.includes('-')) {
            const parts = fullNumber.split('-');
            $('#f_prefix').val(parts[0] + '-');
            $('#f_phone').val(parts[1]);
        } else {
            $('#f_phone').val(fullNumber);
        }
    }

    $("#f_photo").change(function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => $preview.html(`<img src="${e.target.result}">`);
            reader.readAsDataURL(this.files[0]);
        }
    });

    $('#btn-add').click(() => {
        $('#branchForm')[0].reset();
        $('#form-action').val('insert');
        $('#b_id').val('');
        $('#old_photo').val('');
        $('#drawer-title').text('Add Branch');
        $('#btn-submit-text').text('Save Branch');
        $preview.html('<span>No image</span>');
        $drawer.addClass('open'); 
        $overlay.fadeIn();
    });

    $('.btn-upd').click(function() {
        const row = $(this).closest('tr').data();
        
        // Populate fields
        $('#b_id').val(row.id);
        $('#old_photo').val(row.photo);
        $('#f_name').val(row.name);
        setPhoneFields(row.contact);
        $('#f_rest_day').val(row.rest_day);
        $('#f_start').val(row.start);
        $('#f_end').val(row.end);
        $('#f_date').val(row.date);
        $('#f_number').val(row.number);
        $('#f_street').val(row.street);
        $('#f_city').val(row.city);
        $('#f_state').val(row.state);
        $('#f_postcode').val(row.postcode);
        
        // Update UI state
        $('#form-action').val('update');
        $('#drawer-title').text('Update Branch');
        $('#btn-submit-text').text('Update Branch');
        $preview.html(row.photo ? `<img src="/app/images/branch/${row.photo}">` : '<span>No image</span>');
        
        $drawer.addClass('open'); 
        $overlay.fadeIn();
    });

    $('#btn-close, #overlay').click(() => { 
        $drawer.removeClass('open'); 
        $overlay.fadeOut(); 
    });

    // Handle initial state if validation failed (matching prod_main style)
    <?php if ($_err): ?>
        $overlay.show();
    <?php endif; ?>
});
</script>
<?php include '../../_adminfoot.php'; ?>