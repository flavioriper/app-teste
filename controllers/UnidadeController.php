<?php

namespace Controller;

class UnidadeController extends BaseController {

    public static function table() {
        /**----------------------------------------------------------
         * Variaveis necessárias para funcionamento do método
         *---------------------------------------------------------*/
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table = 'lancamentos_unidades';
        $table = $wpdb->prefix . $table;

        /**----------------------------------------------------------
         * Cria a tabela "lancamentos_unidades" no banco de dados
         *---------------------------------------------------------*/
        $sql = "CREATE TABLE IF NOT EXISTS $table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post mediumint(9) NOT NULL,
        unidade varchar(55) NOT NULL,
        dados json,
        updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        return $table;
    }

    private static function taxonomies() {
        
        $tax = [
            'tipo_de_unidade'   => 'tipo_imovel',
            'quartos'           => 'quartos',
            'vagas'             => 'vagas',
            'faixa_de_preco'    => 'faixa_preco',
        ];

        return $tax;
    }


    public static function create($unidade, $lancamento) {
        /**----------------------------------------------------------
         * Permite somente usuários logados e devidamente autorizados
         *---------------------------------------------------------*/
        if(!current_user_can( 'edit_posts' ))  
        die('Não autorizado');

        /**----------------------------------------------------------
         * Variaveis necessárias para funcionamento do método
         *---------------------------------------------------------*/
        global $wpdb;

        $_POST['post'] = $lancamento;
        $_POST['numero_da_unidade'] = $unidade;

        $dados  = json_encode($_POST);
        $post   = $_POST['post'];


        /**----------------------------------------------------------
         * Verifica se a unidade já existe no banco, caso não = NULL
         *---------------------------------------------------------*/
        $exists = $wpdb->get_row( $wpdb->prepare( "SELECT unidade FROM " . self::table() . " WHERE post = %d AND unidade = %d", $lancamento, $unidade));

        /**----------------------------------------------------------
         * Executa somente se a unidade foi encontada no banco
         *---------------------------------------------------------*/
        if(!empty($exists)) {
            echo json_encode([
                'status' => 'failed',
                'message'=> 'A unidade já existe, cada número é único e deve ser utilizado apenas para uma unidade'
            ]);
        } 
        
        /**----------------------------------------------------------
         * execute somente se a unidade em questão não for encontrada
         *---------------------------------------------------------*/
        else { 

            $wpdb->insert( 
                self::table(), 
                array( 
                    'post'      => $lancamento,
                    'unidade'   => $unidade,
                    'dados'     => $dados,
                    'updated_at'=> current_time( 'mysql' ), 
                ) 
            );

            unset($_POST['post']);
    
            foreach(self::taxonomies() as $key => $value) {
                if(isset($_POST[$key])) {
                    $term = get_term_by( 'slug', $_POST[$key], $value);
                    $_POST[$key] = $term;
                }
            }

            add_row('unidades', $_POST, $lancamento);

            echo $dados;

        }
    }


    public static function read($post) { 
        global $wpdb;
        $exists = $wpdb->get_results( $wpdb->prepare( "SELECT post, dados FROM " . self::table() . " WHERE post = %d ORDER BY id DESC", $post));

        if($exists)
            return $exists;
        
        return false;
    }

    public static function edit($lancamento, $unidade) {
        if(!current_user_can( 'edit_posts' ))  
        die('Não autorizado'); 
        
        global $wpdb;
        $dados = $wpdb->get_row( $wpdb->prepare( "SELECT dados FROM " . self::table() . " WHERE post = %d AND unidade = %d", $lancamento, $unidade));
        echo $dados->dados;
    }


    public static function update($lancamento, $unidade) {
        if(!current_user_can( 'edit_posts' ))  
        die('Não autorizado'); 

        global $wpdb;

        /** ------------------------------------------------------------
         * CONVERTE A REQUISIÇÃO PARA JSON, PARA RETORNAR PARA O JS
         * POSTERIORMENTE
         *------------------------------------------------------------*/
        $dados  = json_encode($_POST); 

        /** ------------------------------------------------------------
         * ATUALIZA A REQUISIÇÃO POST, CONVERTENDO O SLUG DE UMA
         * TAXONOMIA PARA UM OBJETO COMPLETO VIA GET_TERM_BY DO WP
         *------------------------------------------------------------*/
        foreach(self::taxonomies() as $key => $data) {
            if($term = get_term_by( 'slug', $_POST[$key], $data)) 
               $_POST[$key] = $term;
        }

        /** ------------------------------------------------------------
         * ATUALIZA A TABELA DE UNIDADES, BASEANDO-SE NO POST ATUAL BEM
         * COMO O NÚMERO DA UNIDADE REQUISITADA
         *------------------------------------------------------------*/
        $wpdb->update(
            self::table(), 
            array(
                'dados'=> $dados, 
                'unidade' => $_POST['numero_da_unidade']
            ), 
            array(
                'post'    => $lancamento,
                'unidade' => $_POST['updateUnidade']
            )
        );

        /** ------------------------------------------------------------
         * COLETA AS UNIDADES EM UM ARRAY DO ACF PARA FINS DE COMPARAÇÃO
         * E ATUALIZAÇÃO DA LINHA QUE BATE COM A UNIDADE REQUISITADA
         *------------------------------------------------------------*/
        $rows = get_field('unidades', $lancamento);
        
        for($i = 0; $i < count($rows); $i++) {
            if($rows[$i]['numero_da_unidade'] == $unidade) {
                update_row('unidades', ($i + 1), $_POST, $lancamento);
            }     
        } 
 
        /** ------------------------------------------------------------
         * RETORNA OS DADOS DA UNIDADE EM JSON PARA PREENCHIMENTO
         * E CRIAÇÃO DE LINHA DE UNIDADE
         *------------------------------------------------------------*/
        echo $dados;

    }


    public static function delete($lancamento, $unidade) {
        if(!current_user_can( 'edit_posts' ))  
        die('Não autorizado');
        
        global $wpdb;

        $wpdb->delete(
            self::table(), 
            array(
                'post'    => $lancamento,
                'unidade' => $unidade
            )
        );

        $rows = get_field('unidades', $lancamento);

        for($i = 0; $i < count($rows); $i++) {
            if($rows[$i]['numero_da_unidade'] == $unidade) {
                delete_row('unidades', ($i + 1), $lancamento);
            }     
        } 

        echo $unidade;

    }


    public static function unidade($lancamento, $unidade) {
        
        if(!current_user_can( 'edit_posts' ))  
        die('Não autorizado');
        
        global $wpdb;
        $exists = $wpdb->get_row( $wpdb->prepare( "SELECT dados FROM " . self::table() . " WHERE post = %d AND unidade = %d", $lancamento, $unidade));

        if($exists) {
            echo $exists->dados;
        }       
       
    }


    public static function create_tables() {
        /**----------------------------------------------------------------
         * Cria a tabela "lancamentos_unidades" no banco de dados
         *---------------------------------------------------------------*/
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS " . self::table() . " (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post mediumint(9) NOT NULL,
        unidade varchar(55) NOT NULL,
        dados json,
        updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

}