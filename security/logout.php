<?php
require '../_base.php';

// ----------------------------------------------------------------------------
logout('/app/security/login.php');

temp('info', 'Logout successfully');

// redirect('/app/security/login.php');
// ----------------------------------------------------------------------------
?>