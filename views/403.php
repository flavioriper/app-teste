<?php use Controller\BaseController as base; ?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Title -->
    <title><?= (empty($title) ? 'Acesso Negado': $title) ?> - Yuppins</title>

    <!-- Styles -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">
    <link href="<?php echo base::assets('plugins/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">

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
    <div class="app app-error align-content-center justify-content-center d-flex flex-wrap h-100">
        <div class="app-error-info text-center p-5">
            <img width="400" src="<?php echo base::assets('images/notAuthorized.svg') ?>" />
            <h5 class="pt-5">Acesso Negado!</h5>
            <span class="mt-0">Você não possui acesso a página ou conteudo <br> que está tentando acessar.</span>
            <a href="index.html" class="btn btn-dark">Voltar ao painel</a>
        </div>
        
    </div>
    
</body>
</html>