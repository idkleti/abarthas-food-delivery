<?php
/*
    Header condiviso.
    Imposta $page_active prima dell'include: 'home'|'menu'|'reservation'|'profile'|'admin'
*/
$page_active = $page_active ?? '';
$logged      = isset($_SESSION['email']);
$is_admin    = !empty($_SESSION['is_admin']);
?>
<header id="header-wrap">
    <div class="container">
        <nav class="navbar navbar-expand-lg">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.png" alt="Logo Abarthas">
            </a>

            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#slide-navbar-collapse"
                    aria-controls="slide-navbar-collapse"
                    aria-expanded="false"
                    aria-label="Apri menu">
                <span class="navbar-toggler-icon"><i class="icon icon-navicon"></i></span>
            </button>

            <div class="collapse navbar-collapse" id="slide-navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 light strong gap-4 ms-4">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link <?= $page_active === 'home' ? 'active' : '' ?>">HOME</a>
                    </li>
                    <li class="nav-item">
                        <a href="menu.php" class="nav-link <?= $page_active === 'menu' ? 'active' : '' ?>">MENU</a>
                    </li>
                    <?php if ($logged): ?>
                        <li class="nav-item">
                            <a href="reservation.php" class="nav-link <?= $page_active === 'reservation' ? 'active' : '' ?>">ORDINA</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($is_admin): ?>
                        <li class="nav-item">
                            <a href="admin.php" class="nav-link <?= $page_active === 'admin' ? 'active' : '' ?>">ADMIN</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav light strong mb-2 mb-lg-0 align-items-lg-center">
                    <?php if (!$logged): ?>
                        <li class="nav-item">
                            <a href="login.php" class="nav-link">ACCEDI</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="profile.php" class="nav-link <?= $page_active === 'profile' ? 'active' : '' ?>">PROFILO</a>
                        </li>
                        <li class="nav-item">
                            <form method="post" action="php/logout.php" class="d-inline m-0">
                                <?= csrf_field() ?>
                                <button type="submit" class="nav-link btn btn-link p-0 border-0 align-baseline">
                                    LOGOUT
                                </button>
                            </form>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </div>
</header>
