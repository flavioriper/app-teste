<?php

namespace Controller;

use Automattic\WooCommerce\Admin\Overrides\Order;
use DateTime;
use Flight;
use stdClass;
use WC_Subscriptions_Manager;
use WP_Query;
use WP_User_Query;

class BaseController {

    public static function assets($content = false) {
        if($content)
            return self::url() . '/assets/' . $content; 
        else
            return self::url();
    }

    public static function url($route = false){
        
        if(isset($_SERVER['HTTPS'])){
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        }
        else{
            $protocol = 'http';
        }
 
        $protocol = $protocol . "://" . $_SERVER['HTTP_HOST'] . Flight::request()->base;
        
        if($route)
        $protocol = $protocol . '/' . $route;
        
        return $protocol;

    }

    public static function error_message($code) {
       
        switch ($code) {
            case 101 : 
                $message = 'Um ou mais campos obrigatórios não foram preenchidos.';
                break;
            case 102 : 
                $message = 'Um ou mais campos obrigatórios não foram preenchidos.';
                break;
            case 103 : 
                $message = 'E-mail fornecido é inválido.';
                break;
            case 104 : 
                $message = 'E-mail inválido ou não registrado no sistema.';
                break;  
            case 105 :
                $message = 'Dados fornecidos não conferem, verifique seu e-mail e senha e tente novamente.';
                break;
            case 106 :
                $message = 'O campo "Nome" necessita estar preencido com seu nome e sobrenome';
                break;
            case 107 :
                $message = 'O e-mail informado já existe no sistema.';
                break;
            case 200 :
                $message = 'Enviamos um e-mail para dar sequencia a redefinição de senha, caso não esteja em sua caixa de entrada verifique o lixo eletrônico ou "Spam';
                break;
            default : 
                $message = false;
        }

        return $message;
    }

    public static function authorization($post = false, $route = false) {
        
        $return = false;

        /**
         * Finaliza a execução e retorna erro caso não esteja logado
         */
        if(!is_user_logged_in()) {
            Flight::notLogged();
            exit;
        } 

        /**
         * Permite acesso total ao administrador
         */
        elseif(current_user_can( 'administrator' )) {
            return true;
        }

        /**
         * Verifica se o usuário atual é autor do post ou se o pai do autor é o mesmo pai do usuário logado
         */
        elseif($post && get_current_user_id() == get_post( $post )->post_author || $post && get_current_user_id() == get_user_meta(get_post( $post )->post_author, 'parent', true)) {
            return true;
        }

        /**
         * Retorna falso caso não faça nenhuma verificação
         */
        return false;

    }

    public static function subscription($user = false) {

        $relation = [];

        $args = array(
            'customer_id' => ($user ? $user : ( get_user_meta(get_current_user_id(), 'parent', true) ? get_user_meta(get_current_user_id(), 'parent', true) : get_current_user_id() )),
            'limit' => -1, 
        );

        $orders = wc_get_orders($args);

        foreach($orders as $order) {
            foreach($order->get_items() as $item) {
                $type = get_post_meta($item->get_product_id(), 'publish_type', true);
                $relation[] = $type;
            }
        }

        return array_unique($relation);

    }

    public static function get_menu($filter = []) {

        $menu = [];
        $list =  array(
            array(
                "menu" => "Lançamentos",
                "slug" => "lancamento",
                "link" => "lancamentos",
                "icon" => "business"
            ),
            array(
                "menu" => "Imóveis",
                "slug" => "imovel",
                "link" => "imoveis",
                "icon" => "home",
            ),
            array(
                "menu" => "Aluguel",
                "slug" => "aluguel",
                "link" => "aluguel",
                "icon" => "deck",
            ),
            array(
                "menu" => "Corretores",
                "slug" => "corretor",
                "link" => "corretores",
                "icon" => "group",
            )
        );
        
        if( current_user_can('administrator') ) {
            $menu =  $list;
        } else {
            foreach($list as $key => $item) {

                if($item['slug'] == 'corretor' && in_array('imovel', self::subscription(get_current_user_id()))) {
                    $menu[] = $item;
                } elseif(in_array(self::subscription()[0], $item)) {
                    $menu[] = $item;
                } 
                        
            }
        }

        return $menu;

    }

    public static function publishData($user, $publish_type = 'lancamento') {

        $user = get_user_meta($user, 'parent', true) ? get_user_meta($user, 'parent', true) : $user;
        $subs = wcs_get_users_subscriptions($user);

        $total = [];
        $total['total']     = 0;
        $total['avaible']   = 0;
        $total['active']    = 0;
        $total['publish']   = 0;
        $total['plan']      = 0;

        $limit              = 0;

        $args = array(
            'author'        => $user,
            'posts_per_page'=> -1,
            'meta_query' => array(
                ($publish_type == 'lancamento' ) ? array(
                 'key' => 'publish_type',
                 'compare' => 'NOT EXISTS'
                ) : array(
                    'key'     => 'publish_type',
                    'value'   => $publish_type,
                    'compare' => '='
                ),
            ),
            'post_type'     => 'imoveis',
            'post_status'   => 'any'
        );

        $posts = get_posts( $args );
        $total['total']  = count($posts);

        foreach($posts as $post) {
            if( $post->post_status == 'publish' ) {
                $total['publish']++;
            }
        }

        
        foreach($subs as $id => $sub) {

            if($sub->get_status() == 'active') {
                $order = new Order($sub->get_parent_id());

                foreach ( $order->get_items() as $item ) {

                    $limit  += intval(get_post_meta($item->get_product_id(), 'publish_limit', true));
                    $type   = get_post_meta($item->get_product_id(), 'publish_type', true);

                    if($publish_type == $type) {
                        $total['plan'] = $item->get_name();
                        $total['active']++;
                        $total['avaible'] += ($limit - $total['total']); 
                    }     
                }
            }
        }


        $total['plan'] = isset(explode("-", $total['plan'])[1]) ? explode("-", $total['plan'])[1] : explode("-", $total['plan'])[0];
        $total['avaible'] = ($total['avaible'] < 0) ? 0 : $total['avaible'];


        return $total;    
    }

    public static function publishVerify($data) {

        $user = get_user_meta(get_current_user_id(), 'parent', true) ? get_user_meta(get_current_user_id(), 'parent', true) : get_current_user_id();
        if( $data['avaible'] > 0 || user_can( $user, 'administrator' ) ) {
            return true;    
        } 
        
        return false;

    }

    public static function checkSubscription() {

        $users = get_users();

        foreach($users as $user) {
            $user_id = $user->ID;
            $subscriptions = wcs_get_users_subscriptions($user_id);
            foreach ($subscriptions as $subscription){
                if ($subscription->has_status(array('on-hold'))) {

                    $today      = date("Y-m-d H:i:s");
                    $referrer   = $subscription->get_time('next_payment');
                    $referrer   = date('Y-m-d H:i:s', $referrer);
                    $blockDate  = date('Y-m-d H:i:s', strtotime($referrer . ' + 5 days') );
                    $removeDate = date('Y-m-d H:i:s', strtotime($referrer . ' + 60 days') );

                    
                    if($today >= $removeDate) {
                        
                    } 
                    elseif($today >= $blockDate) {
                        
                    }
                    
                }
            } 
        }
    }

    public static function delete_post($id) {

        $attachments = get_posts( array(
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_parent' => $id,
            'exclude'     => get_post_thumbnail_id()
        ));

        foreach($attachments as $attachment) {
            wp_delete_attachment( $attachment->ID );
        }
        
        wp_delete_post( $id, true );

    }

    public static function render(array $data = [], string $view) {

        $data['menu'] =  self::get_menu();
        $data['base'] =  self::class;

        Flight::render('layout/header', $data);
        Flight::render('layout/sidebar', $data);
        Flight::render('layout/menu');
        Flight::render($view);
        Flight::render('layout/footer');
    }

    public static function parent_users() {

        if($parent = get_user_meta(get_current_user_id(), 'parent', true)) {
            return array($parent, get_current_user_id());
        } else {
            return array(get_current_user_id());
        }
       
    }

}