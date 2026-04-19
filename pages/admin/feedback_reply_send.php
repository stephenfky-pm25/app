<?php
require '../../_base.php';

// both admin and superadmin can access this page
auth('admin', 'superadmin'); 

if (is_post()) {
    $f_id = req('f_id');
    $user_email = req('user_email');
    $reply_text = req('reply_text');

    // Update feedback with reply and admin id
    $stmt = $_db->prepare('UPDATE feedback SET reply = ?, a_id = ? WHERE f_id = ?');
    $stmt->execute([$reply_text, $_user->id, $f_id]);

    // get user name
    $stm = $_db->prepare('
        SELECT u.name
        FROM feedback f
        JOIN user u ON f.u_id = u.u_id
        WHERE f.f_id = ?
    ');
    $stm->execute([$f_id]);
    $user_name = $stm->fetchColumn() ?: 'Customer';

    // use get_mail() to send email
    $m = get_mail();
    $m->addAddress($user_email, $user_name);
    $m->isHTML(true); // use HTML format, better formatting
    $m->Subject = "Reply to your Feedback - FourLeaves";

    $html_reply = nl2br(htmlentities($reply_text));
    $m->Body = "
        <div style='font-family: sans-serif; line-height: 1.6; color: #333;'>
            <p>Dear <strong>$user_name</strong>,</p>
            <p>Thank you for reaching out to us. Here is out reply to your feedback:</p>
            <div style='background: #f4f4f4; padding: 15px; border-left: 4px solid #4CAF50; margin: 20px 0;'>
                $html_reply
            </div>
            <p>Best regards,<br><strong>FourLeaves Management</strong></p>
            <hr style='border: none; border-top: 1px solid #eee;'>
            <p style='font-size: 0.8rem; color: #999;'>This is an automated message, please do not reply directly to this email.</p>
        </div>
    ";

    // send and check result
    if ($m->send()) {
        temp('info', 'Reply send and email delivered successfully!');
    } else {
        // if email fails, we can log the error for debugging
        temp('info', 'Database updated, but email failed to send.');
    }

    redirect('feedback_mng.php');
}