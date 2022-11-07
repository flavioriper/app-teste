<?php

namespace Controller;

use Flight;
use Controller\BaseController as base;
use Controller\AuthenticationController as auth;
use Exception;
use stdClass;

class AluguelController extends BaseController {

    public $route = 'aluguel';

    public static function status($imovel) {

        if(parent::authorization($imovel, 'aluguel')) {

            $post = get_post($imovel);

            wp_update_post(array(
                'ID'            =>  $imovel,
                'post_status'   =>  ($post->post_status == "publish") ? "draft" : "publish"
            )); 

            echo json_encode(true);

        } else {

            echo json_encode(false);

        }

    }

    public static function exclusive($imovel) {
 
        $exclusive = get_post_meta($imovel, 'exclusive', true);
        if($exclusive) {
            update_post_meta($imovel, 'exclusive', false);
            $exclusive = false;
        } else {
            update_post_meta($imovel, 'exclusive', true);
            $exclusive = true;
        }
        
        echo json_encode($exclusive);

    }

    public static function verificaExclusividade() {
        $cep = $_POST['localizacao_cep'];
        $num = $_POST['localizacao_numero']; 
        $posts = get_posts(array(
            'numberposts'	=> -1,
            'post_type'		=> 'imoveis',
            'post_status'   => 'any', 
            'author__not_in'=> [get_current_user_id()],
            'meta_query'	=> array(
                'relation'		=> 'AND',
                array(
                    'key'	 	=> 'localizacao_cep',
                    'value'	  	=> $cep,
                    'compare' 	=> '=',
                ),
                array(
                    'key'	  	=> 'localizacao_numero',
                    'value'	  	=> $num,
                    'compare' 	=> '=',
                ),
            ),
        ));

        echo json_encode(($posts) ? true : false);
    }

    public static function list() {

        $authorized = self::authorization(false, 'imoveis');

        $user = get_user_meta(get_current_user_id(), 'parent', true) ? get_user_meta(get_current_user_id(), 'parent', true) : get_current_user_id();

        $data = [
            'title' => 'Aluguel Único',
            'base'  => base::class,
            'publish_data'  =>  base::publishData($user, 'aluguel'),
            'publish_verify' => base::publishVerify(base::publishData($user, 'aluguel')),

        ];
        
        $args = array(
            'author'        =>  get_current_user_ID(),
            'posts_per_page'=> -1,
            'meta_key'      => 'publish_type',
            'meta_value'    => 'aluguel',
            'post_type'     => 'imoveis',
            'post_status'   => 'any'
        );

        if(isset($_GET['search'])) {
            $args['s'] = $_GET['search'];
        }

        $data['imoveis'] = get_posts( $args );
        self::render($data, 'aluguel/list');
        
    }

    public static function form() {

        /**------------------------------------------------------
         * Redireciona o usuario não logado para o login
         *-----------------------------------------------------*/
        if(!is_user_logged_in()) { 
            Flight::redirect('/login'); 
        }

        /**------------------------------------------------------
         * Verifica se está autorizado a editar
         *-----------------------------------------------------*/
        if(isset($_GET['id'])) {
            self::authorization();
        }

        /**------------------------------------------------------
         * Coleta os dados do usuario atual ou de seu administrador
         *-----------------------------------------------------*/
        $user = get_user_meta(get_current_user_id(), 'parent', true) ? get_user_meta(get_current_user_id(), 'parent', true) : get_current_user_id();

         /**------------------------------------------------------
         * Caso não tenha mais posts disponíveis revoga acesso
         *-----------------------------------------------------*/
        if(!isset($_GET['id'])) {
            if(!base::publishVerify( base::publishData($user, 'aluguel') )) {
                Flight::redirect('/aluguel');
                exit();
            }
        }


        /**------------------------------------------------------
         * Atributos e detalhes da página para uso posterior
         *-----------------------------------------------------*/
        $data = [
            'title' => 'Adicionar novo aluguel',
        ];

        /**------------------------------------------------------
         * Variavel para criação de um novo Post
         *-----------------------------------------------------*/
        $post = array(
            'post_author'   => $user,
            'post_type'     => 'imoveis',
            'post_status'   => 'draft'
        ); 
        
        
        /**------------------------------------------------------
         * O Parametro ID não foi fornecido na array $_GET
         *-----------------------------------------------------*/
        if( !isset($_GET['id']) ) {

            $id = wp_insert_post( $post );
            update_post_meta($id, 'publish_type', 'aluguel');
            update_post_meta($id, 'list_authors', base::parent_users());
            header('Location:' . get_site_url() . $_SERVER['REQUEST_URI'] . (empty($_GET) ? '?id=' . $id : '&id=' . $id));
            exit;
        }
        
        /**------------------------------------------------------
         * O Parametro ID foi fornecido e existe no banco de
         * dados, porém o usuário atual não está autorizado
         *-----------------------------------------------------*/
        elseif( get_post_status ( $_GET['id'] ) &&  get_current_user_id() != get_post( $_GET['id'] )->post_author ) {
            Flight::notAuthorized();
            exit;
        }
        
        /**------------------------------------------------------
         * O Parametro ID foi fornecido e existe no banco de
         * dados, usuário tem autorização
         *-----------------------------------------------------*/
        elseif( get_post_status ( $_GET['id'] )) {
            $id = $_GET['id'];
        }
        
        /**------------------------------------------------------
         * O Parametro ID foi fornecido, mas não existe no banco
         *-----------------------------------------------------*/
        else {
            Flight::notFound('Aluguel não encontrado');
        }

        /**------------------------------------------------------
         * Pós condicionais, aqui executa o que será inserido na
         * array $data, enviando assim os dados para a view
         *-----------------------------------------------------*/
        $data['id'] = $id;

        /**------------------------------------------------------
         * Renderiza a página com ou sem argumentos fornecidos
         *-----------------------------------------------------*/
        self::render($data, 'aluguel/form');
    }

    public static function create($imovel) {

        $is_tax = [
            'tipo_imovel',
            'quartos',
            'vagas',
            'faixa_preco',
        ];

 
        wp_update_post(array(
            'ID'            => $imovel,
            'post_title'    => $_POST['info_titulo'],
        )); 

        update_post_meta($imovel, 'publish_type', 'aluguel');

        $term = get_term_by( 'slug', 'aluguel', 'oferta');
        update_field('oferta', $term, $imovel);
        

        foreach($_POST as $key => $value) {
            $term = get_term_by( 'slug', $value, $key);
            if($term) { 
                wp_set_object_terms( $imovel, $term->term_id, $term->taxonomy ); 
                //update_field($key, $term, $imovel);
            } else {
                update_field($key, $_POST[$key], $imovel);
            }
        }
        
        /**---------------------------------------------------
         * SET POST THUMBNAIL BASED ON FIRST GALLERY IMAGE ID 
         *--------------------------------------------------*/
        try {
            $featured_image = json_decode( self::get_images($imovel) )->{'featured_image'}[0];
            $featured_image = attachment_url_to_postid( $featured_image->url );
            set_post_thumbnail($imovel, $featured_image );
        } catch (Exception $e) {
            $featured_image = false;
        }

        if ( function_exists( 'gmw_update_post_location' ) ) {

            $city = '';
            foreach(json_decode(file_get_contents(__DIR__ . '/../json/municipios.json')) as $item) {
                if($item->id == $_POST['localizacao_cidade']) {
                    $city = $item->name;
                }
            }; 

            $address = array(
                'street'   => $_POST['localizacao_endereco'] . ', '. $_POST['localizacao_numero'],
                'zipcode'  => $_POST['localizacao_cep'],
                'city'     => $city,
                'country'  => 'Brasil',
            );
    
            gmw_update_post_location( $imovel, $address );
        } 

    }

    public static function associarAssinatura($imovel) {
        foreach (wcs_get_users_subscriptions(get_current_user_id()) as $subscription){
            if ($subscription->has_status(array('active'))) {
                if(!self::verificarAssociacao($subscription->get_id())) {
                    
                }
            }
        }
    }

    public static function verificarAssociacao($assinatura) {

        $associada = false;

        $args = array(
            'author'        =>  get_current_user_id(),
            'posts_per_page' => 1
        );

        $posts = get_posts( $args );
        foreach ( $posts as $post ) :
            if( get_post_meta($post->ID, 'lancamento_assinatura', true) == $assinatura) {
                $associada = true;
            }
        endforeach;

        return $associada;
    }

    public static function assinaturasAtivas() {
        $ativas = [];
        foreach (wcs_get_users_subscriptions(get_current_user_id()) as $subscription){
            if ($subscription->has_status(array('active'))) {
                array_push($ativas, $subscription->get_id());
            }
        }

        return $ativas;
    }

    public static function delete($id) {

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

    public static function update($id = null) {
        
    }

    public static function image_upload($imovel) {

        $temp = [];
        //delete_post_meta($imovel, 'imovel_images');
        //update_post_meta($imovel, 'imovel_images', 1);
        $gallery = get_post_meta($imovel, 'imovel_images', true);
        $gallery = empty($gallery) ? [] : $gallery;
        $wordpress_upload_dir = wp_upload_dir();
        $i = 1;

        foreach($_FILES as $key => $array) {

            $image = $array;
            $new_file_path = $wordpress_upload_dir['path'] . '/' . $image['name'];
            $new_file_mime = mime_content_type( $image['tmp_name'] );

            if( empty( $image ) )
                die( 'File is not selected.' );
            
            if( $image['error'] )
                die( $image['error'] );
                
            if( $image['size'] > wp_max_upload_size() )
                die( 'It is too large than expected.' );
                
            if( !in_array( $new_file_mime, get_allowed_mime_types() ) )
                die( 'WordPress doesn\'t allow this type of uploads.' );
                
            while( file_exists( $new_file_path ) ) {
                $i++;
                $new_file_path = $wordpress_upload_dir['path'] . '/' . $i . '_' . $image['name'];
            }
            
            // looks like everything is OK
            if( move_uploaded_file( $image['tmp_name'], $new_file_path ) ) {
                $upload_id = wp_insert_attachment( array(
                    'guid'           => $new_file_path, 
                    'post_mime_type' => $new_file_mime,
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', $image['name'] ),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                ), $new_file_path, $imovel );
            
                // wp_generate_attachment_metadata() won't work if you do not include this file
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
            
                // Generate and save the attachment metas into the database
                wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $new_file_path ) );

                if($key == 'featured_image') {
                    $gallery[$key] = [];
                }

                $gallery[$key][] = $upload_id;
                update_post_meta($imovel, 'imovel_images', $gallery); 
            }
        }

        foreach($gallery as $key => $image) {

            if($key == 'featured_image') {
                update_field($key, $image[0], $imovel);
            } else {
                update_field($key, $image, $imovel);
            }

        }

        die();
    }

    public static function get_images($imovel) {
        global $wpdb;
        

        $response   = [];
        $galerias   = get_post_meta($imovel, 'imovel_images', true);
        $response   = [];

        foreach($galerias as $galeria => $imagem) { 

            if(!empty($galerias[$galeria])) {

                foreach($imagem as $id) {
                    $response[$galeria][] = [
                        "url" => wp_get_attachment_url( $id ),
                        "name"=> get_the_title( $id ),
                        "size"=> 1000,
                    ];
                }     
            }
        }

        return json_encode($response);
        die();

    }

    public static function delete_image($post, $attachment) {
        
        $old   = get_post_meta($post, 'imovel_images', true);

        foreach($old as $key => $galeries) {
            if($find = array_search($attachment, $galeries)) {
                unset($old[$key][$find]);
            }
        }

        foreach($old as $key => $galeries) {
            update_field($key, $old[$key], $post);    
        }
        
        wp_delete_attachment( $attachment, true );        

    }

}