<?php
require '../../_base.php';

// ----------------------------------------------------------------------------
auth("member");

// When page loads, read all feedback_type from db first
$type = $_db->query('SELECT * FROM feedback_type')->fetchAll();

//----------------------------------------------------------------------
$_title = 'FourLeaves | Feedback';
include '../../_head.php';
include 'profile_sidebar.php';
?>
<style>
    main{
        width:100%;
    }
</style>
<main class="profile-content feedback-page">
    <div class="feedback-card">
        <h1>Submit Feedback</h1>

        <form action="./feedback_save.php" id="feedback-form" method="post" enctype="multipart/form-data">
            
            <div class="form-row">
                <label>Feedback Type</label>
                <select name="ft_id" required>
                    <option value="">-- Please Select --</option>
                    <?php foreach ($type as $t): ?>
                        <option value="<?= $t->ft_id ?>"><?= $t->name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <label>Message</label>
                <textarea name="message" id="message-text" required maxlength="150" 
                    placeholder="Describe your experience..."></textarea>
                <div style="text-align:right; font-size: 0.8rem; color: #bbb; margin-top:5px;">
                    <span id="char-count">0</span> / 150
                </div>
            </div>

            <div class="form-row">
                <label>Photos (Max 5)</label>
                <div class="upload-box">
                    <input type="file" id="photos-input" accept="image/*" style="display:none;">
                    <button type="button" class="btn-choose" onclick="document.getElementById('photos-input').click()">
                        Choose Photo
                    </button>
                    
                    <div id="file-list"></div>
                    <div id="hidden-inputs"></div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Send Feedback</button>
                <button type="reset" class="btn-reset" onclick="clearFiles()">Reset</button>
            </div>
        </form>
    </div>
</main>

<script>
    // Character count 
    const textarea = document.getElementById('message-text');
    const charCount = document.getElementById('char-count');

    textarea.addEventListener('input', function() {
        const len = this.value.length;
        charCount.textContent = len;
        charCount.style.color = len >= 150 ? '#e74c3c' : '#bbb';
    });

    // Photo upload handling
    let selectedFiles = [];
    const fileInput = document.getElementById('photos-input');
    const fileList = document.getElementById('file-list');
    const hiddenInputs = document.getElementById('hidden-inputs');

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            if (selectedFiles.length >= 5) {
                alert("Maximum 5 photos allowed.");
                return;
            }
            selectedFiles.push(this.files[0]);
            renderList();
            updateHiddenInputs();
            this.value = '';
        }
    });

    function renderList() {
        fileList.innerHTML = selectedFiles.map((f, index) => 
            `<div>
                <span><i class="fa fa-file-image-o"></i> ${f.name}</span> 
                <span style="color:#e74c3c; cursor:pointer; font-weight:bold;" onclick="removeFile(${index})">×</span>
            </div>`
        ).join('');
    }

    function updateHiddenInputs() {
        hiddenInputs.innerHTML = '';
        selectedFiles.forEach((file) => {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            const newInp = document.createElement('input');
            newInp.type = 'file';
            newInp.name = 'photos[]';
            newInp.files = dataTransfer.files;
            newInp.style.display = 'none';
            hiddenInputs.appendChild(newInp);
        });
    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        renderList();
        updateHiddenInputs();
    }

    function clearFiles() {
        selectedFiles = [];
        renderList();
        updateHiddenInputs();
        charCount.textContent = 0;
    }
</script>
</main>
</div>
<?php include '../../_foot.php';?>