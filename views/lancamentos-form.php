<?php

use Controller\BaseController as base;
use Controller\FormController;
use Controller\UnidadeController as unidades;

acf_form_head(); ?>

    <div class="app-content lancamentos">
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="page-description page-description-tabbed">
                            <h5>Adição de anúncio</h5>
                            <h1 id="get-step-title">Adicionar Imovel</h1>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="tab-content" id="myTabContent">

                            <div class="tab-pane fade show active" id="account" role="tabpanel" aria-labelledby="account-tab">
                                <form id="step-form">

                                    <?php 
                                    $field_groups = acf_get_field_groups();
                                    $exclude = [
                                        'tab' => [ 
                                            'Tipo de Negociação',
                                            'Cômodos',
                                            'Medidas',
                                            'Preço',
                                            'Características do Imóvel',
                                             
                                            //Remover da lista
                                            //'Localização',
                                            //'Informações'
                                        ], 
                                        'field' => [
                                            'tipo_imovel',
                                            'ano_da_construcao',
                                            'andar',
                                            'tem_mobilia'
                                        ],
                                        'options' => [
                                            'perfil_do_imovel'=> [
                                                'Industrial',
                                                'Rural',
                                                'Temporada'
                                            ],
                                            'tipo_de_unidade'=> [
                                                'casa'
                                            ]
                                        ]
                                        
                                    ];

                                    foreach ( $field_groups as $group ) {

                                        if( $group['ID'] = 62) {
                                            
                                        
                                            // DO NOT USE here: $fields = acf_get_fields($group['key']);
                                            // because it causes repeater field bugs and returns "trashed" fields
                                            $fields = get_posts(array(
                                                'posts_per_page'   => -1,
                                                'post_type'        => 'acf-field',
                                                'orderby'          => 'menu_order',
                                                'order'            => 'ASC',
                                                'suppress_filters' => true, // DO NOT allow WPML to modify the query
                                                'post_parent'      => $group['ID'],
                                                'post_status'      => 'any',
                                                'update_post_meta_cache' => false
                                            ));


                                            $items = array();
                                            $group = '';
                                            $tabs  = '';
                                            $count = 0;
                                            

                                            while($count < count($fields)) {
                                                $unserialize = maybe_unserialize($fields[$count]->post_content);
                                                
                                                if(isset($unserialize['type']) and $unserialize['type'] == 'tab')
                                                    $group = $fields[$count]->post_title;
                                                
                                                if($unserialize['type'] != 'tab')  {
                                                    if(!in_array($group, $exclude['tab'])) {
                                                        $items[$group][] = $fields[$count]; 
                                                    }
                                                }
                                                     
                                                $count++;
                                            }
                                        }
                                    }
                                    
                                    foreach($items as $key => $item) {

                                        echo '<h3>' . $key . '</h3>';
                                        echo '<section class="row">';
                                        echo '<input type="hidden" name="post" value="'.$id.'">';

                                        $is_repeater = false;

                                        foreach($item as $data) {

                                            $subfields = get_posts(array(
                                                'posts_per_page'   => -1,
                                                'post_type'        => 'acf-field',
                                                'orderby'          => 'menu_order',
                                                'order'            => 'ASC',
                                                'suppress_filters' => true, // DO NOT allow WPML to modify the query
                                                'post_parent'      => $data->ID,
                                                'post_status'      => 'any',
                                                'update_post_meta_cache' => false
                                            )); 

                                           
                                            $count = 0;
                                            $total = count($subfields);

                                            do {

                                                if($count > 0) {
                                                    $data = $subfields[$count - 1];
                                                }

                                                $options = [];
                                                $unserialize = maybe_unserialize($data->post_content);
                                            
                                                if(isset($exclude['options'][$data->post_excerpt])) {
                                                    if(isset($unserialize['choices'])) {
                                                        foreach($unserialize['choices'] as $choice) {
                                                            if(in_array($choice, $exclude['options'][$data->post_excerpt])) {
                                                                unset($unserialize['choices'][$choice]); 
                                                            }
                                                        } 
                                                    }
                                                }
                                                
 
                                                if( $unserialize['type'] == 'taxonomy' ) {
                                                    
                                                    $terms = get_terms( array(
                                                        'taxonomy' => $unserialize['taxonomy'],
                                                        'hide_empty' => false,
                                                    ));

                                                    foreach($terms as $term) {
                                                        /*----------------------------------------------*
                                                        * Executa somente se a taxonomia for faixa_preco
                                                        *----------------------------------------------*/
                                                        if($unserialize['taxonomy'] == 'faixa_preco') {
                                                            $array = explode('-',$term->slug);
                                                            //Somente adiciona caso não possua "Aluguel" na array
                                                            if(!in_array('aluguel', $array)) {
                                                                $options[$term->slug] = $term->name;
                                                            }
                                                        } else {
                                                            $options[$term->slug] = $term->name;
                                                        }  
                                                        
                                                    }

                                                }  
                                                
                                                if(!in_array($data->post_excerpt, $exclude['field']) and $unserialize['type'] != 'repeater') {
                                                    FormController::field(
                                                        post    : $id,
                                                        name    : $data->post_excerpt, 
                                                        label   : $data->post_title,
                                                        required: $unserialize['required'] == 1 ? true : false,
                                                        type    : isset($unserialize['field_type']) ? $unserialize['field_type'] : $unserialize['type'],
                                                        options : !empty($options) ? $options : (!empty($unserialize['choices']) ? $unserialize['choices'] : []),
                                                        class   : $unserialize['wrapper']['class'],  
                                                        append  : !empty($unserialize['append']) ? $unserialize['append'] : '',  
                                                        prepend : !empty($unserialize['prepend']) ? $unserialize['prepend'] : ''  
                                                    );
                                                }

                                                $count++;

                                            } while ($count <= $total);


                                            if(maybe_unserialize($item[0]->post_content)['type'] == 'repeater') { ?>
                                                <div class="col-12">
                                                    <button id="add-unidade" class="btn btn-secondary" type="button">
                                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                        Adicionar unidade
                                                    </button>
                                                    <button id="update-unidade" class="btn btn-secondary d-none" type="button">
                                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                        Salvar Modificações
                                                    </button>
                                                </div>
                                                <div id="lista-add-unidade" class="col-12">

                                                        <div id="no-data" class="text-center bg-white shadow-sm my-4 rounded p-5">
                                                            <img src="<?php echo base::assets('images/not-found-female.svg') ?>" />
                                                            <h3 class="text-muted fw-bold fs-5 mt-3">Nenhuma unidade foi cadastrada, clique em "Adicionar unidade" para inclui-las.</h3>
                                                        </div>

                                                        <div class="row ctable">
                                                            <div class="row head">
                                                                <div class="col">
                                                                    Número da unidade
                                                                </div>
                                                                <div class="col">
                                                                    Tipo de unidade
                                                                </div>
                                                                <div class="col-2">
                                                                    
                                                                </div>
                                                            </div>
                                                        
    
                                                            <?php if (!empty(unidades::read($id)) ) {
                                                            
                                                                foreach(unidades::read($id) as $unidade) { 
                                                                    $dados = json_decode($unidade->dados);
                                                                    ?> 
                                                                    <div class="row list align-items-center">
                                                                        <div class="col"><?php echo isset($dados->numero_da_unidade) ? $dados->numero_da_unidade : '' ?></div>
                                                                        <div class="col"><?php echo isset($dados->tipo_de_unidade) ? $dados->tipo_de_unidade : ''  ?></div>
                                                                        <div class="col-2 d-flex align-items-center justify-content-center justify-content-lg-end" lancamento="<?php echo isset($unidade->post) ? $dados->post : '' ?>" unidade="<?php echo isset($dados->numero_da_unidade) ? $dados->numero_da_unidade : '' ?>"> 
                                                                            <button update type="button" class="btn btn-primary btn-sm d-flex align-items-center justify-content-center me-0 me-lg-2">
                                                                                <span class="material-icons-two-tone icon-white">edit</span> Editar
                                                                            </button>
                                                                            <button delete type="button" class="btn btn-danger btn-sm d-flex align-items-center justify-content-center">
                                                                                <span class="material-icons-two-tone icon-white">delete</span> Remover
                                                                            </button> 
                                                                        </div>
                                                                    </div>
                                                                <?php } 
                                                            }?>
                                                        </div>

                                                </div>
    
                                            <?php }

                                        }

                                       

                                        if($key == 'Localização') {
                                            
                                            FormController::local(
                                                cidade  : 'Curitiba', 
                                                estado  : 'Parana'
                                            );
                                            
                                        }

                                        echo '</section>';
                                    }
                                    ?>
                                    
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>