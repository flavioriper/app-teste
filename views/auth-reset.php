<?php use Controller\BaseController as base; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Responsive Admin Dashboard Template">
    <meta name="keywords" content="admin,dashboard">
    <meta name="author" content="stacks">
    <!-- The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    
    <!-- Title -->
    <title>Yuppins - Redefinição de Senha</title>

    <!-- Styles -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">
    <link href="<?php echo base::assets('plugins/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?php echo base::assets('plugins/perfectscroll/perfect-scrollbar.css') ?>" rel="stylesheet">
    <link href="<?php echo base::assets('plugins/pace/pace.css') ?>" rel="stylesheet">

    
    <!-- Theme Styles -->
    <link href="<?php echo base::assets('css/main.min.css') ?>" rel="stylesheet">
    <link href="<?php echo base::assets('css/custom.css') ?>" rel="stylesheet">

    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo base::assets('images/neptune.png') ?>" />
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo base::assets('images/neptune.png') ?>" />

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
    <div class="app app-auth-sign-in align-content-stretch d-flex flex-wrap justify-content-end">
        <div class="app-auth-background">

        </div>
        <div class="app-auth-container">
            <div class="logo">
                <a href="<?php echo base::url() ?>">
                    <img src="<?php echo base::assets('images/logo.svg') ?>" alt="Marca da Yuppins" />
                </a>
            </div>

            <?php if($authorized) { ?>
                <p class="auth-description">Recomendamos que utilize ao menos 1 letra maiúscula, um número, ao menos 6 dígitos e um caractere especial para uma senha forte</p>
            <?php } else { ?>
                 <p class="auth-description">Informe o email de cadastro para prosseguir com a redefinição de senha.</p>
            <?php } ?>

            <?php if(isset($_GET['error']) || isset($_GET['success'])) { ?>
                
                <?php if(isset($_GET['error'])) { ?>
                    <div class="alert alert-danger alert-style-light" role="alert">
                        <?php echo base::error_message($_GET['error']) ?>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-success alert-style-light mt-4" role="alert">
                        <?php echo base::error_message($_GET['success']) ?>
                    </div>
                <?php } ?>
            <?php } ?>

            <?php if( !isset($_GET['success'])) { ?>
                <form action="<?php echo base::url('auth/reset') ?>" method="POST">
                    <div class="auth-credentials m-b-xxl">
                        <?php if($authorized) { ?>
                            <label for="password" class="form-label">Nova senha</label>
                            <input name="password" required type="password" class="form-control m-b-md" id="password" aria-describedby="password">
                            <input name="user" value="<?= $user ?>" type="hidden" >
                            <input name="token" value="<?= $token ?>" type="hidden" >
                        <?php } else { ?>
                            <label for="signInEmail" class="form-label">E-mail</label>
                            <input name="email" required type="email" class="form-control m-b-md" id="signInEmail" aria-describedby="signInEmail" placeholder="seu@email.com.br">
                        <?php } ?>
                    </div>

                    <div class="auth-submit">
                        <?php if($authorized) { ?>
                            <button type="submit" class="btn btn-primary">Redefinir</button>
                        <?php } else { ?>
                            <button type="submit" class="btn btn-primary">Acessar</button> 
                            <a href="<?= base::url('login') ?>" class="auth-forgot-password float-end">Já tem uma conta?</a>
                        <?php } ?>
                    </div>
                </form>
            <?php } ?>
            
        </div>
    </div>
    
    <!-- Javascripts -->
    <script src="<?php echo base::assets('plugins/jquery/jquery-3.5.1.min.js') ?>"></script>
    <script src="<?php echo base::assets('plugins/bootstrap/js/bootstrap.min.js') ?>"></script>
    <script src="<?php echo base::assets('plugins/perfectscroll/perfect-scrollbar.min.js') ?>"></script>
    <script src="<?php echo base::assets('plugins/pace/pace.min.js') ?>"></script>
    <script src="<?php echo base::assets('js/main.min.js') ?>"></script>
    <script src="<?php echo base::assets('js/custom.js') ?>"></script>
</body>
</html>