<?php
require '../../_base.php';

if (is_post()) {
    $ft_id   = req('ft_id');
    $message = req('message');
    $u_id    = $_user->id; 

    // Ensure message does not exceed 150 characters
    if (mb_strlen($message) > 150) {
        $message = mb_substr($message, 0, 150);
    }

    
    $imgs = [null, null, null, null, null];

    
    if (isset($_FILES['photos'])) {
        $files = $_FILES['photos'];
        
        $count = min(count($files['name']), 5); 

        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmp = $files['tmp_name'][$i];
                $filename = uniqid() . '.jpg';
                
                
                move_uploaded_file($tmp, "../../images/feedback/$filename");
                $imgs[$i] = $filename; 
            }
        }
    }

    
    $stmt = $_db->prepare('
        INSERT INTO feedback (ft_id, u_id, message, date_create, image1, image2, image3, image4, image5)
        VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?)
    ');
    
    $stmt->execute([
        $ft_id, 
        $u_id, 
        $message, 
        $imgs[0],
        $imgs[1], 
        $imgs[2], 
        $imgs[3], 
        $imgs[4]
    ]);

    temp('info', 'Feedback submitted successfully!');
    redirect('./feedback.php');
}