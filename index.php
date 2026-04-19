<?php
require '_base.php';
//----------------------------------------------------------------------

//----------------------------------------------------------------------

$_title = 'FourLeaves | Home';
include_once BASE_URL . '/_head.php';
echo '<main>';
?>

<div class = "banner-container">
    <?php
    $totalBanners = count($_banners);
    $duration = 5;
    $totalTime = $totalBanners * $duration;

    $i = 0;
    foreach ($_banners as $id => $banner) {
        $delay = $i * $duration;
        
        echo "<img src='$banner' 
                   id='$id' 
                   class='banner' 
                   style='animation-duration: {$totalTime}s; animation-delay: {$delay}s;'>";
        $i++;
    }
    ?>
</div>

<div class="badge-container">
    <?php
    foreach($_badges as $badge=>$title):?>
        <div class = 'badge'>
            <label for='<?=$badge?>'><?= $title ?></label>
            <p id='<?= $badge ?>'><?= $_badgesdata[$badge] ?></p>
        </div>
    <?php endforeach ?>
</div>

<h1>Product Gallery</h1>
<div class="gallery">
    <?php
    foreach($_gallery as $name=>$gal){
        echo "<img src='./images/productgallery/$gal' alt='$name' title='$name'>";
    }
    ?>
</div>

<h1 id="about" style="margin-top: 10px; font-size: 50px;" >About Us</h1>
<div class="container" style="display:block; margin-bottom:20px;color:#0e3c11; font-size:20px">
    <p style="justify-self:center; margin:0 50px;">FourLeaves Enterprise was founded in 2024 by Albert Ainstein with a singular mission: to redefine the modern tea experience. Born out of a passion for high-quality ingredients and creative mixology, we specialize in handcrafted boba teas that bridge the gap between traditional tea culture and contemporary flavors. We operate from 11.00am to 9.00pm daily, ensuring your cravings are met with the freshest brew in town.</p><br>
    <blockquote style="margin:0 50px;">
        <ul>
            <li>Quality First: We prioritize premium loose-leaf teas and house-made syrups to ensure every sip is a masterpiece.</li>
            <li>Commitment to Safety: We adhere to the highest food safety standards, prioritizing hygiene and freshness in every cup we serve.</li>
            <li>Our Vision: To become the most beloved boba destination in Malaysia, known for innovation, consistency, and a welcoming atmosphere.</li>
            <li>Cultural Fusion: Our goal is to celebrate the diverse flavors of Malaysia by incorporating local ingredients that appeal to residents and visitors alike.</li>
            <li>Our Key Objective: 'To craft the finest quality beverages that bring joy and refreshment to our community.'</li>
            <li>Value-Driven: We pledge to provide an exceptional experience and premium products that offer true value for money.</li>
        </ul>
    </blockquote>
</div>

<h1 id="branch">Our Branch</h1>
<div class="container">
    <div class="track">
        <?php
        $stm = $_db->query('SELECT * FROM branch');
        $branches = $stm->fetchAll();

        foreach($branches as $b):?>
            <div class='card'>
               <img src='./images/branch/<?=$b->photo?>' alt='<?=$b->name?>'>
               <h3><?=$b->name?></h3>
               <p>Rest day:<?=$b->rest_day?><br>
                  Operation hours: <?=$b->start_time?> - <?=$b->end_time?>
                  Since <?=$b->branch_open_date?></p>
            </div>
        <?php endforeach;?>
    </div>
</div>

<h1>Our Team</h1>
<div class="container">
    <div class="track">
        <div class='card'>
            <img src='./images/team/founder.png' alt='Photo of founder'>
            <div class='info'>
                <h3>Albert Ainstein</h3>
                <h4>Founder of FourLeaves Enterprise</h3>
            </div>
        </div>
        <div class='card'>
            <img src='./images/team/co-founder.jpg' alt='Photo of cofounder'>
            <div class='info'>
                <h3>Siti Aminah</h3>
                <h4>Co-founder of FourLeaves Enterprise</h3>
            </div>
        </div>
        <div class='card'>
            <img src='./images/team/branchmanager1.jpg' alt='Photo of Branch Manager'>
            <div class='info'>
                <h3>Stephy Teo</h3>
                <h4>Branch Manager</h3>
            </div>
        </div>
        <div class='card'>
            <img src='./images/team/branchmanager2.jpg' alt='Photo of Branch Manager'>
            <div class='info'>
                <h3>Robin Hoo</h3>
                <h4>Branch Manager</h3>
            </div>
        </div>
        <div class='card'>
            <img src='./images/team/branchmanager3.jpg!d' alt='Photo of Branch Manager'>
            <div class='info'>
                <h3>Alvin Chin</h3>
                <h4>Branch Manager</h3>
            </div>
        </div>
    </div>
</div>
</main>
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/app/_foot.php';
?>