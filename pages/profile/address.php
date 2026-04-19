<?php
require '../../_base.php';

// ----------------------------------------------------------------------------
auth('member');
$u_id = $_user->u_id;

// 1. Fetch current addresses
$stm = $_db->prepare("SELECT * FROM address WHERE u_id = ?");
$stm->execute([$u_id]);
$addresses = $stm->fetchAll();

// 2. Handle Deletion
if (get('delete')) {
    $d_id = get('delete');
    $stm = $_db->prepare("DELETE FROM address WHERE d_id = ? AND u_id = ?");
    $stm->execute([$d_id, $u_id]);
    temp('info', 'Address deleted successfully');
    redirect('/pages/profile/address.php');
}

// 3. Handle Insertion
if (is_post()) {
    $nickname = post('nickname');
    $number   = post('number');
    $street   = post('street');
    $state    = post('state');
    $city     = post('city');
    $postcode = post('postcode');

    // Validations
    if (count($addresses) >= 3) $_err['nickname'] = "Maximum 3 addresses allowed.";
    if (!$nickname) $_err['nickname'] = "Nickname required (e.g., Home).";
    if (!$number)   $_err['number']   = "Unit/House number required.";
    if (!$street)   $_err['street']   = "Street name required.";
    if (!$postcode) $_err['postcode'] = "Please select a postcode.";

    if (!$_err) {
        $stm = $_db->prepare("INSERT INTO address (nickname, number, street, state, city, postcode, u_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stm->execute([$nickname, $number, $street, $state, $city, $postcode, $u_id]);
        temp('info', 'Address added successfully');
        redirect();
    }
}
// ----------------------------------------------------------------------------
$_title = 'FourLeaves | Manage Address';
include '../../_head.php';
include 'profile_sidebar.php';
?>

<div class="address-management-wrapper">
    <div class="existing-addresses-section">
        <h3>My Addresses (<?= count($addresses) ?>/3)</h3>
        <div class="address-row">
            <?php foreach ($addresses as $a): ?>
                <div class="address-card">
                    <div class="card-header">
                        <strong><?= encode($a->nickname) ?></strong>
                        <button type="button" class="btn-delete" onclick="openDeleteModal(<?= $a->d_id ?>)">Delete</button>
                    </div>
                    <p>
                        <?= encode($a->number) ?>, <?= encode($a->street) ?><br>
                        <?= encode($a->city) ?>, <?= $a->postcode ?><br>
                        <?= encode($a->state) ?>
                    </p>
                </div>
            <?php endforeach; ?>
            
            <?php if (count($addresses) == 0): ?>
                <p class="empty-msg">No addresses saved yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (count($addresses) < 3): ?>
    <div class="add-address-section">
        <h3>Add New Address</h3>
        <form method="post" class="address-form">
            <div class="form-grid">
                <div class="field">
                    <label>Nickname</label>
                    <?php html_text('nickname', 'placeholder="Home / School" required'); ?>
                </div>
                <div class="field">
                    <label>Unit/House No</label>
                    <?php html_text('number', 'placeholder="12A" required'); ?>
                </div>
                
                <div class="field full-width">
                    <label>Street</label>
                    <?php html_text('street', 'placeholder="Jalan Example 123" required'); ?>
                </div>

                <div class="dropdown-row">
                    <div class="field">
                        <label>State</label>
                        <select name="state" id="state" required>
                            <option value="">- Select -</option>
                            <?php foreach ($_malaysia_address as $state => $cities): ?>
                                <option value="<?= $state ?>"><?= $state ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>City</label>
                        <select name="city" id="city" disabled required>
                            <option value="">- Select -</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Postcode</label>
                        <select name="postcode" id="postcode" disabled required>
                            <option value="">- Select -</option>
                        </select>
                    </div>
                </div>

                <div class="field button-field full-width">
                    <button type="submit" class="btn-submit">Add Address</button>
                </div>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div></div>
<div id="delete-modal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon">!</div>
        <h3>Are you sure?</h3>
        <p>Do you really want to delete this address? This action cannot be undone.</p>
        <div class="modal-buttons">
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <a href="#" id="confirm-delete-btn" class="btn-confirm">Delete</a>
        </div>
    </div>
</div>
<style>
    
    .address-management-wrapper {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 30px;
        padding: 20px;
        text-align: left;
    }

    .existing-addresses-section {
        flex: 0 0 auto;
        max-width: 1200px; 
    }

    .address-row {
        display: flex;
        flex-direction: row;
        gap: 15px;
    }

    .address-card {
        background: #fff;
        width:400px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        position: relative;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }

    .add-address-section {
        flex: 1;
        background: #fdfdfd;
        border: 1px solid #eee;
        padding: 20px;
        border-radius: 8px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .full-width { grid-column: span 2; }
    
    .field { display: flex; flex-direction: column; min-width: 0;}
    .field label { font-weight: 600; font-size: 0.9em; margin-bottom: 5px; }
    
    input, select {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 100%;
        box-sizing: border-box;
        height: 40px; 

    }
    .dropdown-row {
        grid-column: span 2;
        display: grid;
        gap: 15px;
    }

    .btn-submit {
        background: #333;
        color: white;
        padding: 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
    }

    .btn-submit:hover { background: #555; }

    .btn-delete {
        font-weight: bold;
        font-size: 1em;
    }

    /* Responsive: Stack them on small screens */
    @media (max-width: 900px) {
        .address-management-wrapper {
            flex-direction: column;
        }
        .existing-addresses-section {
            max-width: 100%;
            width: 100%;
        }
    }
    .modal-overlay {
        display: none; /* Hidden by default */
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .modal-box {
        background: white;
        padding: 30px;
        border-radius: 12px;
        text-align: center;
        width: 350px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }

    .modal-icon {
        font-size: 50px;
        color: #f39c12;
        margin-bottom: 10px;
    }

    .modal-buttons {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 20px;
    }

    .btn-cancel {
        background: #ccc;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-confirm {
        background: #e74c3c;
        color: white;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 5px;
        display: inline-block;
    }
</style>

<script>
const addressData = <?= json_encode($_malaysia_address) ?>;

const stateEl    = document.getElementById('state');
const cityEl     = document.getElementById('city');
const postcodeEl = document.getElementById('postcode');

stateEl.onchange = () => {
    const selectedState = stateEl.value;
    const cities = addressData[selectedState];
    
    // 1. Clear existing options
    cityEl.innerHTML = '<option value="">- Select City -</option>';
    postcodeEl.innerHTML = '<option value="">- Select Postcode -</option>';
    
    // 2. Logic to Enable/Disable City
    if (selectedState && cities) {
        cityEl.disabled = false; // ENABLE City dropdown
        
        // Populate cities
        Object.keys(cities).sort().forEach(city => {
            const opt = document.createElement('option');
            opt.value = city;
            opt.textContent = city;
            cityEl.appendChild(opt);
        });
    } else {
        cityEl.disabled = true;
    }

    postcodeEl.disabled = true;
};

cityEl.onchange = () => {
    const selectedState = stateEl.value;
    const selectedCity = cityEl.value;
    const postcodes = addressData[selectedState] ? addressData[selectedState][selectedCity] : null;

    postcodeEl.innerHTML = '<option value="">- Select Postcode -</option>';
    
    if (selectedCity && postcodes) {
        postcodeEl.disabled = false;
        
        postcodes.sort().forEach(pc => {
            const opt = document.createElement('option');
            opt.value = pc;
            opt.textContent = pc;
            postcodeEl.appendChild(opt);
        });
    } else {
        postcodeEl.disabled = true;
    }
};
function openDeleteModal(id) {
    const modal = document.getElementById('delete-modal');
    const confirmBtn = document.getElementById('confirm-delete-btn');
    
    // Set the URL for the actual delete action
    confirmBtn.href = `?delete=${id}`;
    
    // Show the modal
    modal.style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('delete-modal').style.display = 'none';
}

// Close modal if user clicks outside the box
window.onclick = function(event) {
    const modal = document.getElementById('delete-modal');
    if (event.target == modal) {
        closeDeleteModal();
    }
}
</script>
</main>
<?php include '../../_foot.php'; ?>