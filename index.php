<?php

use Controller\ImoveisController as imoveis;
use Controller\AluguelController as aluguel;
use Controller\AuthenticationController as auth;
use Controller\LancamentosController as lancamentos;
use Controller\UnidadeController as unidade;
use Controller\AppController as app;
use Controller\BaseController as base;
use Controller\CorretoresController as corretores;
use Controller\IntegracoesController as integracoes;
use Controller\VistaApiController as vista;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/../wp-load.php';
 
/**------------------------------------------------
 * Register system  to use outside of this file
 *-----------------------------------------------*/
define('BASE_DIR', __DIR__ );

/**------------------------------------------------
 * Map custom functions with Flight Map method
 *-----------------------------------------------*/
Flight::map('notAuthorized', function($title = ''){
    Flight::render('403', array('title' => $title));
});

Flight::map('notFound', function($title = ''){
    Flight::render('404', array('title' => $title));
});

Flight::map('notLogged', function($title = ''){
    Flight::render('auth-login', array('title' => $title));
});



/**------------------------------------------------
 * Register system GET routes with Flight::route
 *-----------------------------------------------*/
Flight::route('GET /', array(app::class, 'page'));
Flight::route('GET /login', array(auth::class, 'login'));
Flight::route('GET /auth/reset', array(auth::class, 'reset'));
Flight::route('GET /lancamentos', array(lancamentos::class, 'list'));
Flight::route('GET /lancamentos/adicionar', array(lancamentos::class, 'form'));
Flight::route('GET /lancamento/@lancamento/unidade/@id', array(unidade::class, 'unidade'));

Flight::route('GET /imoveis/adicionar', array(imoveis::class, 'form'));
Flight::route('GET /imoveis', array(imoveis::class, 'list'));

Flight::route('GET /aluguel/adicionar', array(aluguel::class, 'form'));
Flight::route('GET /aluguel', array(aluguel::class, 'list'));

Flight::route('GET /corretores/adicionar', array(corretores::class, 'form'));
Flight::route('GET /corretores', array(corretores::class, 'list'));

Flight::route('GET /integracoes', array(integracoes::class, 'form'));


Flight::route('GET /cron/check/subscriptions', array(base::class, 'checkSubscription'));
Flight::route('GET /api/vista/sync-imoveis', array(vista::class, 'fetchImoveisVista'));

/**------------------------------------------------
 * Register system POST routes with Flight::route
 *-----------------------------------------------*/
Flight::route('POST /login', array(auth::class, 'signon'));
Flight::route('POST /auth/reset', array(auth::class, 'reset'));
Flight::route('POST /lancamento/@lancamento/create', array(lancamentos::class, 'create'));
Flight::route('POST /lancamento/@lancamento/delete', array(lancamentos::class, 'delete'));
//Flight::route('POST /lancamento/@lancamento/delete/attachment/@attachment', array(lancamentos::class, 'delete_image'));
Flight::route('POST /lancamento/@lancamento/delete/unidade/@unidade', array(unidade::class, 'delete'));
Flight::route('POST /lancamento/@lancamento/update/unidade/@unidade', array(unidade::class, 'update'));
Flight::route('POST /lancamento/@lancamento/unidade/@unidade', array(unidade::class, 'edit'));
Flight::route('POST /lancamento/@lancamento/status/change', array(lancamentos::class, 'status'));
Flight::route('POST /lancamento/@lancamento/exclusive/change', array(lancamentos::class, 'exclusive'));
Flight::route('POST /lancamento/verifica/exclusividade', array(lancamentos::class, 'verificaExclusividade'));
Flight::route('POST /unidade/@unidade/@lancamento', array(unidade::class, 'create'));
Flight::route('POST /galeria/lancamento/@lancamento', array(lancamentos::class, 'image_upload'));
Flight::route('POST /galeria/lancamento/@lancamento/imagens', array(lancamentos::class, 'get_images'));


Flight::route('POST /imovel/@imovel/create', array(imoveis::class, 'create'));
Flight::route('POST /imovel/@imovel/delete', array(imoveis::class, 'delete'));
Flight::route('POST /imovel/@imovel/status/change', array(imoveis::class, 'status'));
Flight::route('POST /imovel/@imovel/delete/attachment/@attachment', array(imoveis::class, 'delete_image'));

Flight::route('POST /galeria/imovel/@imovel', array(imoveis::class, 'image_upload'));
Flight::route('POST /galeria/imovel/@imovel/imagens', array(imoveis::class, 'get_images'));

Flight::route('POST /aluguel/@aluguel/create', array(aluguel::class, 'create'));
Flight::route('POST /aluguel/@aluguel/delete', array(aluguel::class, 'delete'));
Flight::route('POST /aluguel/@aluguel/delete/attachment/@attachment', array(aluguel::class, 'delete_image'));
Flight::route('POST /aluguel/@aluguel/status/change', array(aluguel::class, 'status'));
Flight::route('POST /galeria/aluguel/@aluguel', array(aluguel::class, 'image_upload'));
Flight::route('POST /galeria/aluguel/@aluguel/imagens', array(aluguel::class, 'get_images'));
  
Flight::route('POST /corretores/adicionar', array(corretores::class, 'create'));
Flight::route('POST /corretor/@corretor/delete', array(corretores::class, 'delete'));

Flight::route('POST /integracoes/vista', array(integracoes::class, 'vista'));

Flight::start();


