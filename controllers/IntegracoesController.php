<?php

namespace Controller;

use Flight;
use Controller\BaseController as base;
use Controller\VistaApiController as vista;
use stdClass;

class IntegracoesController extends BaseController {
    protected static $integracoes = ['vista'];

    public static function form() {
 
        /**------------------------------------------------------
         * Redireciona o usuario não logado para o login
         *-----------------------------------------------------*/
        if(!is_user_logged_in()) { 
            Flight::redirect('/login');
        }

        /**------------------------------------------------------
         * Coleta os dados do usuario atual ou de seu administrador
         *-----------------------------------------------------*/
        $user = get_user_meta(get_current_user_id(), 'parent', true) ? get_user_meta(get_current_user_id(), 'parent', true) : get_current_user_id();

        $data['title'] = 'Editar Integrações';
        $data['error'] = base::error_message(isset($_GET['error']) ? $_GET['error'] : false);
        $data['vista'] = vista::getTokens();

        /**------------------------------------------------------
         * Renderiza a página com ou sem argumentos fornecidos
         *-----------------------------------------------------*/
        self::render($data, 'integracoes');
    }

    public static function vista() {
        if (isset($_POST['host']) && isset($_POST['token'])) {
            $response = vista::updateTokens($_POST['host'], $_POST['token']);
            if ($response) {
                echo '200';
                exit;
            }
        }
        echo '500';
        exit;
    }
}