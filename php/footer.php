<?php
// footer condiviso
$logged = isset($_SESSION['email']);
?>
<footer id="footer" class="py-5 bg-dark">
    <div class="container">
        <div class="row gy-4">
            <div class="col-md-3">
                <h4 class="widget-title text-white">Contatti</h4>
                <ul class="list-unstyled text-white-50 mb-0">
                    <li><i class="icon icon-map-marker"></i> Via G. Saragat 1, Ferrara</li>
                    <li><i class="icon icon-mobile"></i> +39 111 1111111</li>
                </ul>
            </div>
            <div class="col-md-3">
                <h4 class="widget-title text-white">Informazioni</h4>
                <ul class="list-unstyled mb-0">
                    <li><a href="allergeni.php" class="text-decoration-none text-white-50">Allergeni</a></li>
                    <li><a href="menu.php"      class="text-decoration-none text-white-50">Menu</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h4 class="widget-title text-white">Supporto</h4>
                <ul class="list-unstyled mb-0">
                    <li><a href="faq.php"   class="text-decoration-none text-white-50">FAQ</a></li>
                    <li><a href="terms.php" class="text-decoration-none text-white-50">Termini e privacy</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h4 class="widget-title text-white">Il tuo account</h4>
                <ul class="list-unstyled mb-0">
                    <?php if (!$logged): ?>
                        <li><a href="login.php" class="text-decoration-none text-white-50">Login</a></li>
                    <?php else: ?>
                        <li><a href="profile.php" class="text-decoration-none text-white-50">Profilo</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</footer>

<div class="footer-bottom">
    <div class="container">
        <div class="content">
            <div class="copyright">
                <p class="mb-0">&copy; 2026 Abarthas | idkleti on github</p>
            </div>
            <div class="payment-card">
                <img src="images/visa.png" class="cardImg" alt="Visa">
                <img src="images/american-express.png" class="cardImg" alt="American Express">
                <img src="images/master-card.png" class="cardImg" alt="Mastercard">
            </div>
        </div>
    </div>
</div>
