<?php

// ============================================================================
// PHP Setups
// ============================================================================

date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

// ============================================================================
// General Page Functions
// ============================================================================

// Is GET request?
function is_get() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

// Is POST request?
function is_post() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

// Obtain GET parameter
function get($key, $value = null) {
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain POST parameter
function post($key, $value = null) {
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain REQUEST (GET and POST) parameter
function req($key, $value = null) {
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Redirect to URL
function redirect($url = null) {
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

// Set or get temporary session variable
function temp($key, $value = null) {
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    }
    else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}

// Obtain uploaded file --> cast to object
function get_file($key) {
    $f = $_FILES[$key] ?? null;
    
    if ($f && $f['error'] == 0) {
        return (object)$f;
    }

    return null;
}

// Crop, resize and save photo
function save_photo($f, $folder, $width = 200, $height = 200) {
    $photo = uniqid() . '.jpg';
    
    require_once 'lib/SimpleImage.php';
    $img = new SimpleImage();
    $img->fromFile($f->tmp_name)
        ->thumbnail($width, $height)
        ->toFile("$folder/$photo", 'image/jpeg');

    return $photo;
}

// Is money?
function is_money($value) {
    return preg_match('/^\-?\d+(\.\d{1,2})?$/', $value);
}

// Is email?
function is_email($value) {
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

// Is date?
function is_date($value, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $value);
    return $d && $d->format($format) == $value;
}

// Is time?
function is_time($value, $format = 'H:i') {
    $d = DateTime::createFromFormat($format, $value);
    return $d && $d->format($format) == $value;
}

// Return year list items
function get_years($min, $max, $reverse = false) {
    $arr = range($min, $max);

    if ($reverse) {
        $arr = array_reverse($arr);
    }

    return array_combine($arr, $arr);
}

// Return month list items
function get_months() {
    return [
        1  => 'January',
        2  => 'February',
        3  => 'March',
        4  => 'April',
        5  => 'May',
        6  => 'June',
        7  => 'July',
        8  => 'August',
        9  => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];
}

// Return local root path
function root($path = '') {
    return "$_SERVER[DOCUMENT_ROOT]/$path";
}

// Return base url (host + port)
function base($path = '') {
    return "http://$_SERVER[SERVER_NAME]:$_SERVER[SERVER_PORT]/$path";
}

// Return TRUE if ALL array elements meet the condition given
function array_all($arr, $fn) {
    foreach ($arr as $k => $v) {
        if (!$fn($v, $k)) {
            return false;
        }
    }
    return true;
}

// ============================================================================
// HTML Helpers
// ============================================================================

// Placeholder for TODO
function TODO() {
    echo '<span>TODO</span>';
}

// Encode HTML special characters
function encode($value) {
    return htmlentities($value);
}

// Generate <input type='hidden'>
function html_hidden($key, $attr = '') {
    $value ??= encode($GLOBALS[$key] ?? '');
    echo "<input type='hidden' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='text'>
function html_text($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='password'>
function html_password($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='password' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='number'>
function html_number($key, $min = '', $max = '', $step = '', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='number' id='$key' name='$key' value='$value'
                 min='$min' max='$max' step='$step' $attr>";
}

// Generate <input type='search'>
function html_search($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='search' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='date'>
function html_date($key, $min= '', $max = '', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='date' id='$key' name='$key' value='$value'
                 min='$min' max='$max' $attr>";
}

// Generate <input type='time'>
function html_time($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='time' id='$key' name='$key' value='$value' $attr>";
}

// Generate <textarea>
function html_textarea($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<textarea id='$key' name='$key' $attr>$value</textarea>";
}

// Generate SINGLE <input type='checkbox'>
function html_checkbox($key, $label = '', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    $status = $value == 1 ? 'checked' : '';
    echo "<label><input type='checkbox' id='$key' name='$key' value='1' $status $attr>$label</label>";
}

// Generate <input type='checkbox'> list
function html_checkboxes($key, $items, $br = false) {
    $values = $GLOBALS[$key] ?? [];
    if (!is_array($values)) $values = [];

    echo '<div>';
    foreach ($items as $id => $text) {
        $state = in_array($id, $values) ? 'checked' : '';
        echo "<label><input type='checkbox' id='{$key}_$id' name='{$key}[]' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

// Generate <input type='radio'> list
function html_radios($key, $items, $br = false) {
    $value = encode($GLOBALS[$key] ?? '');
    echo '<div>';
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'checked' : '';
        echo "<label><input type='radio' id='{$key}_$id' name='$key' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

// Generate <select>
function html_select($key, $items, $default = '- Select One -', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

// Generate <input type='file'>
function html_file($key, $accept = '', $attr = '') {
    echo "<input type='file' id='$key' name='$key' accept='$accept' $attr>";
}

// Generate table headers <th>
function table_headers($fields, $sort, $dir, $href = '') {
    foreach ($fields as $k => $v) {
        $d = 'asc'; // Default direction
        $c = '';    // Default class
        
        if ($k == $sort) {
            $d = $dir == 'asc' ? 'desc' : 'asc';
            $c = $dir;
        }

        echo "<th><a href='?sort=$k&dir=$d&$href' class='$c'>$v</a></th>";
    }
}

// ============================================================================
// Error Handlings
// ============================================================================

// Global error array
$_err = [];

// Generate <span class='err'>
function err($key) {
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='err'>$_err[$key]</span>";
    }
    else {
        echo '<span></span>';
    }
}

// ============================================================================
// Security
// ============================================================================

// Global user object
$_user = $_SESSION['user'] ?? null;

// Login user
function login($user, $url = '/app/pages/product/product.php') {
    $_SESSION['user'] = $user;
    redirect($url);
}

// Logout user
function logout($url = '/app/') {
    unset($_SESSION['user']);
    redirect($url);
}

// Authorization
function auth(...$roles) {
    global $_user;
    if ($_user) {
        if ($roles) {
            if (in_array($_user->role, $roles)) {
                return; // OK
            }
        }
        else {
            return; // OK
        }
    }
    
    redirect('/app/security/login.php');
}


// ============================================================================
// Email Functions
// ============================================================================

// My Account:
// --------------
// tanhc0318@gmail.com        jjrj mler ispi psxl

// Initialize and return mail object
function get_mail() {
    // require_once '/app/lib/PHPMailer.php';
    // require_once '/app/lib/SMTP.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/app/lib/PHPMailer.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/app/lib/SMTP.php';

    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->Host = 'smtp.gmail.com';
    $m->Port = 587;
    $m->Username = 'stephenkyfong2007@gmail.com';
    $m->Password = 'ijpc mbyz zzgi fnnk';
    $m->CharSet = 'utf-8';
    $m->setFrom($m->Username, '🧋FourLeaves Admin');
    return $m;
}

// ============================================================================
// Shopping Cart
// ============================================================================

// Get shopping cart
function get_cart() {
    return $_SESSION['cart'] ?? [];
}

// Set shopping cart
function set_cart($cart = []) {
    $_SESSION['cart'] = $cart;
}

// Update shopping cart
function update_cart($id, $temp, $opt_ice, $opt_sugar, $toppings, $qty, $remark) {
    $cart = get_cart();
    $updated = false;

    if ($qty >= 1 && is_exists($id, 'product', 'p_id')) {
        //check whether product exists
        foreach($cart as $index=>$details){
            //check details same or not
            if( $details[0]==$id &&
                $details[1]==$temp &&
                $details[2]==$opt_ice &&
                $details[3]==$opt_sugar &&
                $details[4]==$toppings &&
                $details[6]==$remark){
                    $details[5] += $qty;
                    $updated=true;
            }
        }
        if(!$updated){
            $cart[sizeof($cart)+1] = [$id,$temp, $opt_ice, $opt_sugar, $toppings, $qty, $remark];
        }
        
        usort($cart, function($a, $b) {
            return $a[0] <=> $b[0];
        });
    }
    else {
        $count=0;
        foreach($cart as $details){
            if($details[0]==$id){
                unset($cart[$count]);
            }
            $count++;
        }
        
    }
    set_cart($cart);
}

// ============================================================================
// Database Setups and Functions
// ============================================================================

// Global PDO object
$_db = new PDO('mysql:dbname=bobatea','root','',[
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);

// Is unique?
function is_unique($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

// Is exists?
function is_exists($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}

// ============================================================================
// Global Constants and Variables
// ============================================================================
$_banners = [
    'b1' => "./images/banner/clean.png",
    'b2' => "./images/banner/connection.png",
];
$_badges = [
    'MLKT' => "Milk Tea",
    'FRTT' => "Fruit Tea",
    'COFF' => "Coffee",
    'CHOC' => "Chocolate Series",
]; 

$_badgesdata = [
    'MLKT' => "30+",
    'FRTT' => "10+",
    'COFF' => "20+",
    'CHOC' => "10+",
]; 

$_gallery = [
    "Brown Sugar Pearl Milk Tea" => "brownsugarpearlmilktea.jpg",
    "Hazelnut Coffee" => "hazelnutcoffee.jpg",
    "Iced Latte" => "icedlatte.jpg",
    "Matcha Latte" => "matchalatte.jpg",
    "Mocha" => "mocha.jpg",
    "Oolong Tea" => "oolongtea.png",
    "Signature Chocolate" => "signaturechocolate.jpg",
    "Signature Boba Milk Tea" => "signaturebobamilktea.jpg",
    "Signature Mixed Fruit Tea" => "signaturemixedfruittea.png",
];
//--------------------
// branch contact
//--------------------
$_prefixes = [
    '+602-',
    '+603-',
    '+604-',
    '+605-',
    '+606-',
    '+607-',
    '+608-',
    '+609-',
];

//--------------------
//address handling
//--------------------
function load_local_malaysia_data() {
    $file_path = __DIR__ . '/postcodes.csv'; 
    $data = [];

    if (!file_exists($file_path)) return [];

    if (($handle = fopen($file_path, "r")) !== FALSE) {
        fgetcsv($handle); 

        while (($row = fgetcsv($handle)) !== FALSE) {
            $postcode = $row[0];
            $city     = $row[1];
            $state    = $row[2];

            if (!isset($data[$state][$city])) {
                $data[$state][$city] = [];
            }
            
            if (!in_array($postcode, $data[$state][$city])) {
                $data[$state][$city][] = $postcode;
            }
        }
        fclose($handle);
    }

    ksort($data);
    foreach ($data as $s => $cities) {
        ksort($data[$s]);
        foreach ($data[$s] as $c => $pcs) {
            sort($data[$s][$c]);
        }
    }
    return $data;
}

$_malaysia_address = load_local_malaysia_data();
//--------------------
//footer
//--------------------
$_googlemap = [
    '1001' => "https://www.google.com/maps/place/Mixue+Queensbay+Mall/@5.3805014,100.2850551,13z/data=!4m10!1m2!2m1!1smixue+bayan+lepas!3m6!1s0x304ac160e9b3e73f:0x6381e2941f46c4c2!8m2!3d5.3343425!4d100.3064914!15sChFtaXh1ZSBiYXlhbiBsZXBhcyIDiAEBWhMiEW1peHVlIGJheWFuIGxlcGFzkgEOaWNlX2NyZWFtX3Nob3DgAQA!16s%2Fg%2F11tfkk9yml?entry=ttu&g_ep=EgoyMDI2MDMwNC4xIKXMDSoASAFQAw%3D%3D",
    '1002' => "https://www.google.com/maps/place/Mixue+New+World+Park/@5.4202591,100.3165549,15z/data=!4m10!1m2!2m1!1smixue!3m6!1s0x304ac352622ddbb1:0xfb204efe29d4616d!8m2!3d5.4202584!4d100.3269681!15sCgVtaXh1ZSIDiAEBWgciBW1peHVlkgEOaWNlX2NyZWFtX3Nob3CaASNDaFpEU1VoTk1HOW5TMFZKUTBGblNVTmtPSFoxUmxsM0VBReABAPoBBAhQEEs!16s%2Fg%2F11vlt_cdrg?entry=ttu&g_ep=EgoyMDI2MDMwNC4xIKXMDSoASAFQAw%3D%3D",
    '1003' => "https://www.google.com/maps/place/Mixue+The+Promenade+Bayan+Lepas/@5.3246249,100.2118886,13z/data=!4m10!1m2!2m1!1smixue+bayan+lepas!3m6!1s0x304ac1a92860a607:0x9f7d24e51173cc37!8m2!3d5.3246249!4d100.2819264!15sChFtaXh1ZSBiYXlhbiBsZXBhcyIDiAEBWhMiEW1peHVlIGJheWFuIGxlcGFzkgEOaWNlX2NyZWFtX3Nob3CaAURDaTlEUVVsUlFVTnZaRU5vZEhsalJqbHZUMjB4TlZSc1VrTlJhMFpGWVd4a05scHJPWFJhVmtKWVdWZFNNMDR4UlJBQuABAPoBBAgAECA!16s%2Fg%2F11v4503vqm?entry=ttu&g_ep=EgoyMDI2MDMwNC4xIKXMDSoASAFQAw%3D%3D",
];

// Define root

define("BASE_URL", $_SERVER["DOCUMENT_ROOT"] . "/app");


