<?php
use Controller\BaseController as base;
?>

<div class="app-container">
    <div class="search">
        <form>
            <input class="form-control" type="text" placeholder="Pesquisar..." aria-label="Search">
        </form>
        <a href="#" class="toggle-search"><i class="material-icons">close</i></a>
    </div>
    <div class="app-header">
    <nav class="navbar navbar-light navbar-expand-lg">
            <div class="container-fluid">
                <div class="navbar-nav" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link hide-sidebar-toggle-button" href="#"><i class="material-icons">first_page</i></a>
                        </li>
                    </ul>
    
                </div>
                <div class="d-flex">
                    <ul class="navbar-nav">
                        <li class="nav-item hidden-on-mobile">
                            <a class="nav-link" href="<?= get_site_url() ?>/anunciante ">Página de anúncios</a>
                        </li>
                        <li class="nav-item hidden-on-mobile">
                            <a class="nav-link" href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>">Gerenciar conta</a>
                        </li>
                        <li class="nav-item hidden-on-mobile">
                            <a class="nav-link" href="<?= get_site_url() ?>/central-de-ajuda">Central de ajuda</a>
                        </li> 
                    </ul>
                </div>
            </div>
        </nav>
    </div>