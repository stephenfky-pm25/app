<?php
require '../_base.php';

$action = req('action');
$id = req('a_id');

if ($action == "reset" && $id) {
    $new_password = "123456";

    $stm = $_db->prepare("UPDATE admin SET password = SHA1(?) WHERE a_id = ?");
    if ($stm->execute([$new_password, $id])) {
        temp('info','Success: Password has been reset to 123456.');
    } else {
        temp('info','Error: Could not update password.');
    }
} else {
    temp('info','Error: No ID provided.');
}
redirect('/app/pages/admin/team_mng.php')
?>