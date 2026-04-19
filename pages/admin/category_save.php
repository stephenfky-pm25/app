<?php
require '../../_base.php';

// only superadmin can access this page
auth('superadmin');

if (is_post()) {
    $c_id        = req('c_id');
    $name        = req('name');
    $description = req('description');

    // if delete button is clicked, delete the category
    if (isset($_POST['delete'])) {
        $stmt = $_db->prepare('DELETE FROM category WHERE c_id = ?');
        $stmt->execute([$c_id]);
        temp('info', 'Category deleted successfully!');
    } 
    // if save/update button is clicked
    else {
        if ($c_id) {
            // Update
            $stmt = $_db->prepare('UPDATE category SET name = ?, description = ? WHERE c_id = ?');
            $stmt->execute([$name, $description, $c_id]);
            temp('info', 'Category updated successfully!');
        } else {
            // Create
            $stmt = $_db->prepare('INSERT INTO category (name, description) VALUES (?, ?)');
            $stmt->execute([$name, $description]);
            temp('info', 'New category created!');
        }
    }
}

redirect('cat_main.php');