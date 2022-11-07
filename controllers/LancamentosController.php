<?php

namespace Controller;

use Flight;
use Controller\BaseController as base;
use Controller\AuthenticationController as auth;
use stdClass;

class LancamentosController extends BaseController {

    public static function table() {
        /**----------------------------------------------------------
         * Variaveis necessárias para funcionamento do método
         *---------------------------------------------------------*/
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table = 'lancamentos_imagens';
        $table = $wpdb->prefix . $table;

        /**----------------------------------------------------------
         * Cria a tabela "lancamentos_unidades" no banco de dados
         *---------------------------------------------------------*/
        $sql = "CREATE TABLE IF NOT EXISTS $table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post mediumint(9) NOT NULL,
        galeria varchar(255) NOT NULL,
        imagens json,
        updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        return $table;
    }

    public static function status($lancamento) {

        if(parent::authorization($lancamento)) {

            $post = get_post($lancamento);

            wp_update_post(array(
                'ID'            =>  $lancamento,
                'post_status'   =>  ($post->post_status == "publish") ? "draft" : "publish"
            )); 

            echo json_encode(true);

        } else {

            echo json_encode(false);

        }

    }

    public static function exclusive($lancamento) {
        
        $exclusive = get_post_meta($lancamento, 'exclusive', true);
        if($exclusive) {
            update_post_meta($lancamento, 'exclusive', false);
            $exclusive = false;
        } else {
            update_post_meta($lancamento, 'exclusive', true);
            $exclusive = true;
        }
        
        echo json_encode($exclusive);

    }

    public static function verificaExclusividade() {

        $user = get_user_meta(get_current_user_id(), 'parent', true) ? get_user_meta(get_current_user_id(), 'parent', true) : get_current_user_id();

        $cep = $_POST['localizacao_cep'];
        $num = $_POST['localizacao_numero']; 
        $posts = get_posts(array(
            'numberposts'	=> -1,
            'post_type'		=> 'imoveis',
            'post_status'   => 'any', 
            'author__not_in'=> [$user],
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
                array(
                     'key' => 'publish_type',
                     'compare' => 'NOT EXISTS'
                ),
                array(
                    'key' => 'exclusive',
                    'value' => '1',
                    'compare' => '='
               ),
            ),
        ));

        echo json_encode(($posts) ? true : false);
    }

    public static function list() {

        self::authorization(false, 'lancamentos');
        $user = get_user_meta(get_current_user_id(), 'parent', true) ? get_user_meta(get_current_user_id(), 'parent', true) : get_current_user_id();

        $data = [
            'title' => 'Lançamentos',
            'publish_data'  =>  base::publishData($user, 'lancamento'),
            'publish_verify' => base::publishVerify( base::publishData($user, 'lancamento')),
        ];
        
        $args = array(
            'author'        =>  get_current_user_ID(),
            'posts_per_page'=> -1,
            'post_type'     => 'imoveis',
            'meta_query' => array(
                array(
                 'key' => 'publish_type',
                 'compare' => 'NOT EXISTS'
                ),
            ),
            'post_status'   => 'any'
        );

        if(isset($_GET['search'])) {
            $args['s'] = $_GET['search'];
        }

        $data['lancamentos'] = get_posts( $args );
        self::render($data, 'lancamentos-list');
        
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
         * Caso não tenha mais posts disponíveis revoga acesso
         *-----------------------------------------------------*/
        if(!isset($_GET['id'])) {
            if(!base::publishVerify( base::publishData(get_current_user_id(), 'lancamento') )) {
                Flight::redirect('/lancamentos');
                exit();
            }
        }

        /**------------------------------------------------------
         * Atributos e detalhes da página para uso posterior
         *-----------------------------------------------------*/
        $data = [
            'title' => 'Adicionar novo lançamento',
        ];

        /**------------------------------------------------------
         * Variavel para criação de um novo lançamento
         *-----------------------------------------------------*/
        $post = array(
            'post_author'   => get_current_user_id(),
            'post_type'     => 'imoveis',
            'post_status'   => 'draft'
        );
        
        /**------------------------------------------------------
         * O Parametro ID não foi fornecido na array $_GET
         *-----------------------------------------------------*/
        if( !isset($_GET['id']) ) {
            $id = wp_insert_post( $post );
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
            Flight::notFound('Lançamento não encontrado');
        }

        /**------------------------------------------------------
         * Pós condicionais, aqui executa o que será inserido na
         * array $data, enviando assim os dados para a view
         *-----------------------------------------------------*/
        $data['id'] = $id;

        /**------------------------------------------------------
         * Renderiza a página com ou sem argumentos fornecidos
         *-----------------------------------------------------*/
        self::render($data, 'lancamentos-form');
    }

    public static function create($lancamento) {
        
        global $wpdb;
        $table = 'lancamentos_unidades';
        $table = $wpdb->prefix . $table;
 
        $dados = [];

        $images     = $wpdb->get_results( $wpdb->prepare( "SELECT galeria, imagens FROM " . self::table() . " WHERE post = %d", $lancamento));
        $data       = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $table . " WHERE post = %d", $lancamento));
        $temp       = [];

        foreach($images as $image) {
            if($image->galeria == 'featured_image') {
                set_post_thumbnail( $lancamento, json_decode($image->imagens)[0]);
            }
        }


        for($i = 0; $i < count($data); $i++) {
            
            $dados  = json_decode($data[$i]->dados);

            $temp['tipo_imovel'][]  = get_term_by( 'slug', $dados->tipo_de_unidade, 'tipo_imovel')->term_id;
            $temp['quartos'][]      = get_term_by( 'slug', $dados->quartos, 'quartos')->term_id;
            $temp['vagas'][]        = get_term_by( 'slug', $dados->vagas, 'vagas')->term_id;
            $temp['faixa_preco'][]  = get_term_by( 'slug', $dados->faixa_de_preco, 'faixa_preco')->term_id;

        }


        //TIPO DE NEGOCIAÇÃO
        $tipos = [
            get_term_by( 'slug', 'lancamento', 'oferta')->term_id,
            get_term_by( 'slug', 'venda', 'oferta')->term_id
        ];

        wp_set_post_terms( $lancamento, $tipos,  'oferta', false );
 
        wp_set_post_terms( $lancamento, $temp['tipo_imovel'],   'tipo_imovel', false ); 
        wp_set_post_terms( $lancamento, $temp['quartos'],       'quartos', false ); 
        wp_set_post_terms( $lancamento, $temp['vagas'],         'vagas', false ); 
        wp_set_post_terms( $lancamento, $temp['faixa_preco'],   'faixa_preco', false ); 
  
            
        if(isset($_POST['localizacao_cep']))
        update_field('localizacao_cep', $_POST['localizacao_cep'], $lancamento);

        if(isset($_POST['localizacao_endereco']))
        update_field('localizacao_endereco', $_POST['localizacao_endereco'], $lancamento);

        if(isset($_POST['localizacao_numero']))
        update_field('localizacao_numero', $_POST['localizacao_numero'], $lancamento);

        if(isset($_POST['localizacao_bairro']))
        update_field('localizacao_bairro', $_POST['localizacao_bairro'], $lancamento);

        if(isset($_POST['localizacao_estado']))
        update_field('localizacao_estado', $_POST['localizacao_estado'], $lancamento);

        if(isset($_POST['localizacao_cidade']))
        update_field('localizacao_cidade', $_POST['localizacao_cidade'], $lancamento);

        if(isset($_POST['info_titulo']))
        update_field('info_titulo', $_POST['info_titulo'], $lancamento);

        if(isset($_POST['perfil_do_imovel']))
        update_field('perfil_do_imovel', $_POST['perfil_do_imovel'], $lancamento);

        if(isset($_POST['codigo']))
        update_field('codigo', $_POST['codigo'], $lancamento);

        if(isset($_POST['info_descricao']))
        update_field('info_descricao', $_POST['info_descricao'], $lancamento);

        if(isset($_POST['caracteristicas_do_condominio']))
        update_field('caracteristicas_do_condominio', $_POST['caracteristicas_do_condominio'], $lancamento);

        if(isset($_POST['video']))
        update_field('video', $_POST['video'], $lancamento);

        if(isset($_POST['360']))
        update_field('360', $_POST['360'], $lancamento);

        if ( function_exists( 'gmw_update_post_location' ) ) {

            $city = '';
            foreach(json_decode(file_get_contents(__DIR__ . '/../json/municipios.json')) as $item) {
                if($item->id == $_POST['localizacao_cidade']) {
                    $city = $item->name;
                }
            }; 

            $address = array(
                'street'   => $_POST['localizacao_endereco'] . ', '. $_POST['localizacao_numero'],
                'city'     => $city,
                'zipcode'  => $_POST['localizacao_cep'],
                'country'  => 'Brasil',
            );
    
            gmw_update_post_location( $lancamento, $address );
        }

    }

    public static function associarAssinatura($lancamento) {
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

        wp_delete_attachment( $id );
        wp_delete_post( $id );

    }

    public static function update($id = null) {
        
    }

    public static function image_upload($lancamento) {

        $gallery = [
            'featured_image',
            'galeria_interior',
            'galeria_exterior',
            'galeria_plantas'
        ];

        $wordpress_upload_dir = wp_upload_dir();
        $i = 1;

        foreach($_FILES as $key => $array) {

            if(in_array($key, $gallery)) {
                
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
                    ), $new_file_path, $lancamento );
                
                    // wp_generate_attachment_metadata() won't work if you do not include this file
                    require_once( ABSPATH . 'wp-admin/includes/image.php' );
                
                    // Generate and save the attachment metas into the database
                    wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $new_file_path ) );
                    
                    global $wpdb; 
                    $hasData = $wpdb->get_row( $wpdb->prepare( "SELECT galeria, imagens FROM " . self::table() . " WHERE post = %d AND galeria = '%s'", $lancamento, $key));

                    if(empty($hasData)) {
                        $wpdb->insert( 
                            self::table(), 
                            array( 
                                'post'      => $lancamento,
                                'galeria'   => $key,
                                'imagens'   => json_encode([$upload_id]),
                                'updated_at'=> current_time( 'mysql' ), 
                            ) 
                        );
                    } else {

                        if ( $key == 'featured_image' ) { 
                            $imagens = [];
                            array_push($imagens, $upload_id);
                        } else {
                            $imagens = json_decode($hasData->imagens);
                            array_push($imagens, $upload_id);
                        }

                        $wpdb->update(
                            self::table(), 
                            array(
                                'imagens'=> json_encode($imagens), 
                                'updated_at'=> current_time( 'mysql' )
                            ), 
                            array(
                                'post'    => $lancamento,
                                'galeria' => $key
                            )
                        );
                    }
                }
            }
        }

        foreach($gallery as $image) { 
            $verify = $wpdb->get_row( $wpdb->prepare( "SELECT imagens FROM " . self::table() . " WHERE post = %d AND galeria = '%s' ", $lancamento, $image));

            if($verify) {
                if($image == 'featured_image') {
                    update_field($image, json_decode($verify->imagens)[0], $lancamento);
                } else {
                    update_field($image,json_decode($verify->imagens), $lancamento);
                }
            }

        }       

        die();

    }

    public static function get_images($lancamento) {
        global $wpdb;

        $array     = [];
        $response  = [];
        $galerias  = [
            "featured_image",
            "galeria_interior",
            "galeria_exterior", 
            "galeria_plantas"
        ];

        foreach($galerias as $galeria) { 
            $array[$galeria] = $wpdb->get_row( $wpdb->prepare( "SELECT imagens FROM " . self::table() . " WHERE post = %d AND galeria = '%s' ", $lancamento, $galeria));

            if(!empty($array[$galeria])) {
                foreach(json_decode($array[$galeria]->imagens) as $imagens) {
                    $response[$galeria][] = [
                        "url" => wp_get_attachment_url( $imagens ),
                        "name"=> get_the_title( $imagens ),
                        "size"=> 1000,
                    ];
                }    
            }
        }

        echo json_encode($response);
        die();

    }
  

}