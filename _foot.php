    <footer>
        <?php 
        $stm = $_db->query('SELECT * FROM branch');
        $branches = $stm->fetchAll();
        ?>
        <h1>About Us</h1>
        <h1>Contact Info</h1>
        <h1>Our Location</h1>
        <div>
            <div>FourLeaves Enterprise was founded in 2024 by Albert Ainstein with a singular mission: to redefine the modern tea experience.<br> Born out of a passion for high-quality ingredients and creative mixology, we specialize in handcrafted boba teas that bridge the gap between traditional tea culture and contemporary flavors.<br> We operate from 11.00am to 9.00pm daily, ensuring your cravings are met with the freshest brew in town.</div>
        </div>
        <div class="contact_info">
            <div>
                <img src="/app/images/icon/phone.png" class="icon">
                <span>Phone: <a href="tel:+60123456789">+60123456789</a></span>
            </div>
            <div>
                <img src="/app/images/icon/email.png" class="icon">
                <span>Email: <a href="mailto:stephenkyfong2007@gmail.com">tanhc0318@gmail.com</a></span>
            </div>
            <div>
                <img src="/app/images/icon/facebook.svg" class="icon">
                <span>Facebook: <a href="https://www.facebook.com/profile.php?id=61560449501374" target="_blank">LIKE OUR PAGE!</a></span>
            </div>
            <div>
                <img src="/app/images/icon/instagram.png" class="icon">
                <span>Instagram: <a href="https://www.instagram.com/mixuemalaysia/" target="_blank">FOLLOW US!</a></span>
            </div>
        </div >
        <div>
        <?php 
            foreach($branches as $b):
                $stm = $_db->prepare('SELECT * FROM address WHERE b_id = ?');
                $stm->execute([$b->b_id]);
                $d= $stm->fetch();
                $map = $_googlemap[$b->b_id];?>
                <div>
                    <h3 style="margin: 0px; padding: 0px;"><?= $b->name ?></h3><br>
                    <address><?=$d->number?>, <?=$d->street?>, <?=$d->postcode?> <?=$d->city?>, <?=$d->state?> <a href=<?=$map?> target="_blank">Visit Us!</a></address><br>
                </div>
        <?php endforeach?>
        </div>
    </footer>
</body>
</html>