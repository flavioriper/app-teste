<?php

namespace Controller;

use Flight;
use Controller\BaseController as base;
use Controller\AuthenticationController as auth;
use stdClass;
use WP_User_Query;

class CorretoresController extends BaseController {

    public static function middleware() {
         /**------------------------------------------------------
         * Permite somente imobiliárias com plano de imóveis
         *-----------------------------------------------------*/
        if(!current_user_can('manage_options') && !in_array('imovel', base::subscription(get_current_user_id()))) { 
            Flight::notAuthorized();
            exit;
        }

    }

    public static function list() {

        self::middleware();

        $users = new WP_User_Query( 
            array ( 
                'meta_key'  => 'parent', 
                'meta_value'=> get_current_user_id(),
                'search'    => isset($_GET['search']) ? '*' . $_GET['search'] . '*' : ''
            )  
        );
        
        $data = [
            'title' => 'Corretores',
            'users' => $users
        ]; 
        
        self::render($data, 'corretores/list');
        
    }

    public static function form() {

        /**------------------------------------------------------
         * Redireciona o usuario não logado para o login
         *-----------------------------------------------------*/
        if(!is_user_logged_in()) { 
            Flight::redirect('/login'); 
        }

        self::middleware();


        /**------------------------------------------------------
         * Atributos e detalhes da página para uso posterior
         *-----------------------------------------------------*/
        $data = [
            'title' => 'Adicionar novo corretor',
            'fields'=> array(
                array(
                    'name'=> 'name',
                    'label' => 'Nome',
                    'required' => true,
                    'type'  => 'text',
                    'class' => 'col-12 col-md-6'
                ),
                array(
                    'name'=> 'creci',
                    'label' => 'CRECI',
                    'required' => true,
                    'type'  => 'text',
                    'class' => 'col-12 col-md-6'
                ),
                array(
                    'name'=> 'whatsapp',
                    'label' => 'Whatsapp',
                    'required' => false,
                    'type'  => 'text',
                    'class' => 'col-12 col-md-6 mask-phone'
                ),
                array(
                    'name'=> 'email',
                    'label' => 'E-mail',
                    'required' => true,
                    'type'  => 'email',
                    'class' => 'col-12 col-md-6'
                ),
                array(
                    'name'=> 'pass1',
                    'label' => 'Senha',
                    'required' => true,
                    'type'  => 'password',
                    'class' => 'col-12 col-md-6'
                ),
                array(
                    'name'=> 'pass2',
                    'label' => 'Confirmar senha',
                    'required' => true,
                    'type'  => 'password',
                    'class' => 'col-12 col-md-6'
                ),
            )
        ];

        $data['error'] = base::error_message(isset($_GET['error']) ? $_GET['error'] : false);

        if(isset($_GET['id'])) {

            $user = get_user_by( 'ID', $_GET['id']);
            $data['fields'][0]['value'] = $user->display_name;
            $data['fields'][1]['value'] = get_user_meta( $user->ID, 'creci',  true);
            $data['fields'][2]['value'] = get_user_meta( $user->ID, 'whatsapp',  true);
            $data['fields'][3]['value'] = $user->user_email;

            unset( $data['fields'][4] );
            unset( $data['fields'][5] );

        }

        session_start();
        if(isset( $_SESSION['data']) ) {
            foreach($data['fields'] as $key => $field) {
                $data['fields'][$key]['value'] = $_SESSION["data"][$field['name']];
            } 
        }
        session_destroy();

        /**------------------------------------------------------
         * Renderiza a página com ou sem argumentos fornecidos
         *-----------------------------------------------------*/
        self::render($data, 'corretores/form');
    }

    public static function create() {
        
 
        // REMOVE O ATRIBUTO ERRO CASO EXISTA DA URL
        $redirect = preg_replace("/\b\?error\=[^&]+&?/", '', Flight::request()->url);
        $redirect = preg_replace("/\berror\=[^&]+&?/", '', $redirect);

        // ARMAZENA OS DADOS DO FORMULÁRIO DE FORMA SEGURA EM UMNA SESSÃO
        session_start();
        $_SESSION["data"] = $_POST;

        if(isset($_GET['id'])) {
            self::update($_GET['id']);
            // REDIRECIONA PARA A LISTA DE CORRETORES
            Flight::redirect('/corretores');
            die();
        }

       
        // VERIFICA SE O CAMPO E-MAIL ESTÁ PREENCHIDO
        if(!isset(Flight::request()->data->email)) {
            Flight::redirect($redirect . '?error=101');
            die();
        }

        // VERIFICA SE O EMAIL INFORMADO É VÁLIDO
        if(!filter_var(Flight::request()->data->email, FILTER_VALIDATE_EMAIL)) {
            Flight::redirect($redirect . '?error=103');
            die();
        }

        // VERIFICA SE O CAMPO DE SENHA ESTÁ PREENCHIDO
        if(!isset(Flight::request()->data->pass1)) {
            Flight::redirect($redirect . '?error=102');
            die();
        }

        // VERIFICA SE JÁ EXISTE EMAIL CADASTRADO NO SISTEMA
        if( email_exists( Flight::request()->data->email ) ) {
            Flight::redirect($redirect . '?error=107');
            die();
        } 
 
        // SEPARA O NOME PARA VERIFICAÇÕES
        $nameArray = preg_split('/\s+/', Flight::request()->data->name);

        if(count($nameArray) < 2) {
            Flight::redirect($redirect . '?error=106');
            die();
        }


        $userlogin = explode("@", Flight::request()->data->email);
        $userlogin = $userlogin[0];

        while(username_exists( $userlogin )) {
            $userlogin = $userlogin + wp_rand( 10, 99 );
        }

        $firstname = $nameArray[0];
        unset($nameArray[0]);
        $lastname = implode(" ", $nameArray);

        // RECEBE AS CREDENCIAIS DO FORMULÁRIO
        $userdata = array(
            'user_login'            => $userlogin,
            'user_pass'             => Flight::request()->data->pass1,
            'user_email'            => Flight::request()->data->email, 
            'display_name'          => $firstname . ' ' . end($nameArray),
            'first_name'            => $firstname,
            'last_name'             => $lastname,
        );

        $user_id = wp_insert_user( $userdata );

        add_user_meta( $user_id, 'creci',  Flight::request()->data->creci);
        add_user_meta( $user_id, 'whatsapp',  Flight::request()->data->whatsapp);
        add_user_meta( $user_id, 'parent', get_current_user_id());

        // REDIRECIONA PARA A LISTA DE CORRETORES
        Flight::redirect('/corretores');

    }
 
    public static function delete($id) {

        global $wpdb;

        $posts = get_posts(array(
            'author'        =>  $id,
            'post_type'     => 'imoveis',
            'post_status'   => 'any'
        ));

        foreach($posts as $post) { 
            wp_update_post( array(
                'ID' => $post->ID,
                'post_author' => get_user_meta( $id, 'parent', true),
            ) );
        }

        $wpdb->delete( $wpdb->prefix . "users", array( 'ID' => $id ) );

    }

    public static function update($id = null) {

        $user = get_user_by('id', $id );

        // REMOVE O ATRIBUTO ERRO CASO EXISTA DA URL
        $redirect = preg_replace("/\b\?error\=[^&]+&?/", '', Flight::request()->url);
        $redirect = preg_replace("/\berror\=[^&]+&?/", '', $redirect);

        // VERIFICA SE O CAMPO E-MAIL ESTÁ PREENCHIDO
        if(!isset(Flight::request()->data->email)) {
            Flight::redirect($redirect . '?error=101');
            die();
        }

        // VERIFICA SE O EMAIL INFORMADO É VÁLIDO
        if(!filter_var(Flight::request()->data->email, FILTER_VALIDATE_EMAIL)) {
            Flight::redirect($redirect . '?error=103');
            die();
        }

        // VERIFICA SE JÁ EXISTE EMAIL CADASTRADO NO SISTEMA
        if( $user->user_email != Flight::request()->data->email ) {
            if( email_exists( Flight::request()->data->email ) ) {
                Flight::redirect($redirect . '?error=107');
                die();
            }
        } 

        // SEPARA O NOME PARA VERIFICAÇÕES
        $nameArray = preg_split('/\s+/', Flight::request()->data->name);

        if(count($nameArray) < 2) {
            Flight::redirect($redirect . '?error=106');
            die();
        }

        $firstname = $nameArray[0];
        unset($nameArray[0]);
        $lastname = implode(" ", $nameArray);

        $userdata = array(
            'ID'            => $id, 
            'user_email'    => Flight::request()->data->email, 
            'display_name'  => $firstname . ' ' . end($nameArray),
            'first_name'    => $firstname,
            'last_name'     => $lastname,
        );

        $user_id = wp_update_user( $userdata ); 

        update_user_meta( $user_id, 'creci',  Flight::request()->data->creci);
        update_user_meta( $user_id, 'whatsapp',  Flight::request()->data->whatsapp);
        
    }

}