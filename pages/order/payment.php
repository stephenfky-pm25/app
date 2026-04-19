<?php
require_once '../../_base.php';

// ----------------------------------------------------------------------------
auth("member");

$o_id = req('o_id');
$sessionKey = "pending_order_" . $o_id;
$stm = $_db->prepare("SELECT * FROM orders WHERE o_id = ? AND u_id = ?");
$stm->execute([$o_id, $_user->id]);
$o = $stm->fetch();
if (!$o) redirect('/pages/order/cart.php');

if (isset($_SESSION[$sessionKey])) {
    $extraInfo = $_SESSION[$sessionKey];
    $way = $extraInfo['way'];
    $payment_method = $extraInfo['payment'];
    $addr_id = $extraInfo['addr_id'];
} else {
    temp('info', 'Session expired. Please re-select your shipping options.');
    redirect('/pages/order/cart.php');
}
// Handle Fake Card Payment Submission
function isValidCard($cardNumber) {
    // 1. Remove any spaces or dashes just in case
    $number = preg_replace('/\D/', '', $cardNumber);

    // 2. Check length
    $length = strlen($number);
    if ($length < 13 || $length > 19 ) {
        return false;
    }

    $sum = 0;
    $shouldDouble = false;

    // 3. Loop through digits from right to left
    for ($i = $length - 1; $i >= 0; $i--) {
        $digit = (int)$number[$i];

        if ($shouldDouble) {
            $digit *= 2;
            // If doubling results in a number > 9, subtract 9 (same as adding digits)
            if ($digit > 9) {
                $digit -= 9;
            }
        }

        $sum += $digit;
        $shouldDouble = !$shouldDouble; // Alternate every other digit
    }

    // 4. If total sum is a multiple of 10, it is valid
    return ($sum % 10 === 0);
}

function isExpired($expiry) {
    list($month, $year) = explode('/', $expiry);
    
    // Convert YY to 20YY
    $year = "20" . $year;
    
    // Get last day of that month
    $expiryTimestamp = strtotime("$year-$month-01 +1 month -1 day");
    $currentTimestamp = time();

    return $expiryTimestamp < $currentTimestamp;
}

if (is_post()) {
    $cardholder = req('cardholder');
    $cardNumber = req('c1') . req('c2') . req('c3') . req('c4');
    $expiry = req('expiry');
    $cvv = req('cvv');
    if(!isValidCard($cardNumber)){
        $_err['cardNumber']="Invalid card number";
    }else if(!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry)) {
        $_err['expiry'] = "Invalid format (MM/YY).";
    } 
    else if (isExpired($expiry)) {
        $_err['expiry'] = "This card has expired.";
    }
    else if (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
        $_err['cvv'] = "Invalid CVV.";
    }
    
    if (!$_err) {
        $stm=$_db->prepare('
            UPDATE orders 
            SET status = "paid", 
                payment_datetime = NOW()
            WHERE o_id = ? AND u_id = ?
        ');
        if($stm->execute([$o_id, $_user->id])){
            temp('info', 'Payment successful');
            redirect('/app/pages/profile/history.php');
        }
    }
}
// ----------------------------------------------------------------------------
$_title = "Payment | " . ($payment_method == 'cash' ? 'Pay At Counter' : 'Card');
include '../../_head.php';
?>
<style>
    .payment-container {
        max-width: 450px; 
        margin: 0 auto;
        font-family: sans-serif;
    }
    .input-group {
        margin-bottom: 15px;
        display:inline-block;
    }
    .input-group label, .card-input-container label {
        display: block;
        margin-bottom: 5px;
        margin-top: 5px;
        font-size: 16px;
        color:#777;
    }
    .input-group  input {
        padding: 10px;
        border: 1px solid #ccc;
        width: 100%;
        box-sizing: border-box;
        font-size: 16px;
        border-radius: 4px;
    }

    .error-msg {
        color: red;
        font-size: 0.8em;
    }

    .card-payment-box{
        max-width: 450px;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 30px;
        margin: 0 auto;
    }

    .card-input-container .flex-row {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-top: 5px;
    }

    .card-segment {
        width: 60px;
        text-align: center;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .expiry-cvv-row {
        display: flex;
        gap: 15px;
    }

    .proceed-btn{
        width:100%;
        margin-top:20px;
        background:#4CAF50; 
        color:white;
        border:none;
        padding:15px;
        border-radius:5px;
        cursor:pointer;
    }

    @media print {
        html, body {
            width: 100%;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            visibility: hidden;
        }

        .receipt-box {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 10px !important;
            box-sizing: border-box !important;
        }

        .item-table, .receipt-box table {
            width: 100% !important;
            table-layout: auto !important;
        }

        .receipt-box, .pickup-highlight, .cash-info {
            border: 1px solid #ccc !important;
            box-shadow: none !important;
        }

        .receipt-box, .receipt-box * {
            visibility: visible;
        }

        .receipt-box {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none;
            padding: 0;
        }

        .proceed-btn, .back-btn, header, footer, nav {
            display: none !important;
        }
        .screen-only {
            display: none !important;
        }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const segments = document.querySelectorAll('.card-segment');
        const expiryInput = document.getElementById('expiry');
        const cvvInput = document.querySelector('input[name="cvv"]');

        // Helper function to block non-numeric key presses
        const blockNonNumbers = (e) => {
            // Allow: Backspace, Tab, Enter, Escape, Arrow keys
            const allowedKeys = ['Backspace', 'Tab', 'Enter', 'Escape', 'ArrowLeft', 'ArrowRight'];
            if (allowedKeys.includes(e.key)) return;

            // Block if not a number
            if (!/^\d$/.test(e.key)) {
                e.preventDefault();
            }
        };

        // 1. Card Segments Logic
        segments.forEach((input, index) => {
            input.addEventListener('keydown', (e) => {
                blockNonNumbers(e); // Stop alphabets before they appear
                
                // Handle Backspace auto-focus
                if (e.key === 'Backspace' && input.value.length === 0 && index > 0) {
                    segments[index - 1].focus();
                }
            });

            input.addEventListener('input', () => {
                // Auto-tabbing
                if (input.value.length === 4 && index < segments.length - 1) {
                    segments[index + 1].focus();
                }
            });
        });

        // 2. Expiry Date (MM/YY)
        expiryInput.addEventListener('keydown', blockNonNumbers);
        expiryInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                e.target.value = value.slice(0, 2) + '/' + value.slice(2, 4);
            } else {
                e.target.value = value;
            }
        });

        // 3. CVV Logic
        cvvInput.addEventListener('keydown', blockNonNumbers);
    });
</script>
<main style="max-width: 800px; margin: auto; padding: 20px;">
    <?php if ($payment_method == 'cash'): ?>
        <div style="max-width: 600px; margin: 20px auto; background: white; padding: 20px; border-radius: 8px;">
            <h2 style="text-align: center; color: #6fb048;">Cash Payment Instructions</h2>
            
            <?php 
                // 1. Prepare data for the receipt
                // The order_receipt_content.php relies on $o_id and session data
                if ($o && $payment_method == 'cash' && isset($_SESSION[$sessionKey])) {
                    
                    // Set the pickup info session for the receipt content to display the SLIP
                    $pickup_info = [
                        'call_number' => str_pad($o->o_id, 4, '0', STR_PAD_LEFT),
                        'est_time'    => date('H:i', strtotime($o->datetime) + (15 * 60))
                    ];
                    $_SESSION['pickup_info'] = $pickup_info;

                    // 2. Capture the receipt HTML content
                    ob_start();
                    include 'order_receipt_content.php'; 
                    $receipt_html = ob_get_clean();

                    // 3. Display the receipt on the page
                    echo $receipt_html;

                    // 4. Send Email (Prevent resending on page refresh)
                    if (!isset($_SESSION['email_sent_' . $o_id])) {
                        try {
                            $m = get_mail();
                            $m->addAddress($_user->email, $_user->name);
                            $m->isHTML(true);
                            $m->Subject = "Your Four Leaves Order Receipt - #" . $pickup_info['call_number'];
                            $m->Body    = "
                                <h2>Thank you for your order!</h2>
                                <p>Please present the receipt below at the counter for payment.</p>
                                <hr>" . $receipt_html;
                            if ($m->send()) {
                                $_SESSION['email_sent_' . $o_id] = true;
                            }
                        } catch (Exception $e) {
                            error_log($e->getMessage());
                        }
                    }
                }
            ?>
            
            <div style="text-align: center; margin-top: 20px;">
                <button onclick="window.print()" class="proceed-btn">Print Receipt</button>
                <a href="/app/pages/profile/history.php" class="back-btn" style="text-decoration: none; display: inline-block;">Back to History</a>
            </div>
        </div>

    <?php else: ?>
        <div class="payment-container">
            <h1>Credit/Debit Card Payment</h1>
        </div>
    <?php endif; ?>
    <!--card-->
    <?php if ($payment_method == 'card' && $o->status == 'unpaid'): ?>
        <div class="card-payment-box">
            <h2>Credit/Debit Card Payment</h2>
            <p>Order Total: <strong>RM <?= number_format($o->total, 2) ?></strong></p>
            <form method="post">
                <input type="hidden" name="action" value="pay_card">
                <div class="input-group">
                    <label for="cardholder" >Cardholder Name</label>
                    <input type="text" id="cardholder" name="cardholder" placeholder="Name" maxlength="50" required value="<?= req('cardholder') ?? '' ?>">
                </div>
                <div class="card-input-container">
                    <label>Card Number</label>
                    <div class="flex-row">
                        <input type="text" class="card-segment" name="c1" placeholder="0000" maxlength="4" required value="<?= req('c1') ?? '' ?>">
                        <input type="text" class="card-segment" name="c2" placeholder="0000" maxlength="4" required value="<?= req('c2') ?? '' ?>">
                        <input type="text" class="card-segment" name="c3" placeholder="0000" maxlength="4" required value="<?= req('c3') ?? '' ?>">
                        <input type="text" class="card-segment" name="c4" placeholder="0000" maxlength="4" required value="<?= req('c4') ?? '' ?>">
                        <span><?php err('cardNumber');?></span>
                    </div>
                    
                </div>

                <div class="flex-row">
                    <div class="input-group" style="flex: 2;">
                        <label for="expiry">Expiry Date</label>
                        <input type="text" 
                            id="expiry" 
                            name="expiry" 
                            placeholder="MM/YY" 
                            inputmode="numeric"
                            maxlength="5"
                            required
                            value="<?= req('expiry') ?? '' ?>">
                        <span><?php err('expiry');?></span>
                    </div>

                    <div class="input-group" style="flex: 1;">
                        <label for="cvv">CVV</label>
                        <input type="text" 
                            name="cvv" 
                            placeholder="000" 
                            inputmode="numeric"
                            maxlength="4"
                            required
                            value="<?= req('cvv') ?? '' ?>">
                        <span><?php err('cvv');?></span>
                    </div>
                </div>
            </div>
                <button class="proceed-btn">Pay Now</button>
            </form>
        </div>

    <?php endif; ?>

</main>
<?php
include '../../_foot.php';
?>