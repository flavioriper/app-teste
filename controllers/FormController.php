<?php

namespace Controller;
use Controller\BaseController as base;

class FormController extends BaseController {

    static function local(
        String $rua     = '', 
        String $numero  = '', 
        String $bairro  = '', 
        String $cidade  = '', 
        String $estado  = '', 
        String $cep     = ''
    ){

        $endereco  = empty($rua)    ? "" : "$rua, ";
        $endereco .= empty($numero) ? "" : "$numero, ";
        $endereco .= empty($bairro) ? "" : "$bairro, ";
        $endereco .= empty($cidade) ? "" : "$cidade - ";
        $endereco .= empty($estado) ? "" : "$estado, ";
        $endereco .= empty($cep)    ? "" : "$cep";

        $endereco  = '<div class="col-12"><iframe class="map" width="640" height="500" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.it/maps?q=' . $endereco . '&output=embed"></iframe></div>';
        echo $endereco;
    }
    
    static function field(
        Int $post = 0,
        String $value = '',
        String $name = '', 
        String $label = '', 
        String $type = 'text', 
        Array $options = [], 
        String $class = '', 
        String $append = '',
        String $prepend = '',
        bool $required = false, 
        bool $inline = false
    ) {

        ob_start();

        if($type == 'radio' || $type == 'checkbox') : 
            $values = get_field($name, $post); ?>
            <div class="<?= empty($class) ? 'col-md-12' : $class ?>">
                <h5 class="card-title"><?= $label ?></h5>
                <div class="example-container mb-4">
                    <div class="example-content">
                        <?php foreach($options as $key => $option) : ?>
                            <div class="form-check <?= ($inline) ? 'form-check-inline' : '' ?> ">
                                <input <?= (is_array($values) ? (in_array($option, $values) ? 'checked' : '' ) : ($values == $option ? 'checked' : '') ) ?> value="<?= $option ?>" name="<?= $name ?><?= ($type == 'checkbox') ? '[]': '' ?>" class="form-check-input" type="<?= $type ?>" id="<?= $name . '_' . $key ?>">
                                <label class="form-check-label" for="<?= $name . '_' . $key ?>">
                                    <?= $option ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        
        <?php elseif ($type == 'select' || $name == 'localizacao_estado' || $name == 'localizacao_cidade' ) : 
            $term = get_the_terms( $post, $name );
            $term = is_wp_error($term) ? false : (empty($term[0]) ? false : $term[0]);
            ?> 
            <div class="mb-4 <?= empty($class) ? 'col-md-6' : $class ?>"> 
                <select <?= ($term ? 'pre-select="' . $term->slug . '"' : (get_field($name, $post) ? 'pre-select="'.get_field($name, $post).'"' : '') ) ?> <?= $required ? 'required' : '' ?> name="<?= $name ?>" class="form-select" aria-label="<?= $label ?>">
                    <option selected disabled><?= $label ?></option> 
                    <?php foreach($options as $key => $option) : ?>
                        <option value="<?= $key ?>"><?= $option ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php elseif ($type == 'textarea'): ?>

                <div class="mb-4 <?= empty($class) ? 'col-md-6' : $class ?>"> 
                    <label for="<?= $name ?>" class="form-label card-title input-text"><?= $label ?> <?= $required ? '<span>*</span>' : ''?></label>
                    <textarea rows="5" <?= $required ? 'required' : '' ?> class="form-control labelholder" name="<?= $name ?>" id="<?= $name ?>"><?php echo (get_field($name, $post) ? get_field($name, $post) : '' )?></textarea>
                </div>

            <?php elseif($type == 'gallery'): ?>
                <div class="mb-4 <?= empty($class) ? 'col-12' : $class ?>"> 
                    <p for="<?= $name ?>" class="form-label card-title input-text"><?= $label ?> <?= $required ? '<span>*</span>' : ''?></p>

                    
                    <div id="<?= $name ?>" class="dropzoneForm dropzone">
                        <div class="dz-message" data-dz-message>
                            <span>
                                <img class="me-3" width="50" src="<?=  base::assets('images/image.svg') ?>" />
                                Clique ou Arraste e solte as imagens aqui
                            </span>
                        </div>
                    </div>
 
                    <div class="position-relative">
                        <div class="<?= $name ?>Preview">

                            <?php if(get_field($name, $post)) {
                                foreach(get_field($name, $post) as $images) {?>
                                    <div class="dz-preview dz-image-preview">  
                                        <div class="dz-image">
                                            <?=$images['sizes']['thumbnail']?>
                                        </div>  
    
                                        <a class="dz-remove" href="javascript:undefined;" data-dz-post="<?= $post ?>" data-dz-remove="<?= $images['ID'] ?>">
                                            <button type="button" class="btn btn-danger btn-sm w-100 rounded-0">Remover</button>
                                        </a>
                                    </div>
                                <?php } 
                            }?>
                      
                        </div>
                    </div>

                </div>

            <?php elseif($type == 'image') : ?>

                <div class="mb-4 <?= empty($class) ? 'col-12' : $class ?>"> 
                    <p for="<?= $name ?>" class="form-label card-title input-text"><?= $label ?> <?= $required ? '<span>*</span>' : ''?></p>

                    <div id="<?= $name ?>" class="dropzoneForm single dropzone"> 
                        <div class="dz-message" data-dz-message>
                            <span>
                                <img class="me-3" width="50" src="<?=  base::assets('images/image.svg') ?>" />
                                Clique ou Arraste a imagem aqui
                            </span>
                        </div>
                    </div>

                    <div class="position-relative">
                        <div class="<?= $name ?>Preview">
                            <?php if($image = get_field($name, $post)) { ?>
                                <div class="dz-preview dz-image-preview">  
                                    <div class="dz-image">
                                        <?=$image['sizes']['thumbnail']?>
                                    </div>

                                    <a class="dz-remove" href="javascript:undefined;" data-dz-post="<?= $post ?>" data-dz-remove="<?= $image['ID'] ?>">
                                        <button type="button" class="btn btn-danger btn-sm w-100 rounded-0">Remover</button>
                                    </a>
                                </div>
                            <?php }?>
                        </div>
                    </div>

                </div>

            <?php else : ?>
                
            
            <div class="mb-4 position-relative <?= empty($class) ? 'col-md-6' : $class ?>"> 
                <?php if( !empty($prepend) ) { ?>
                    <div class="prefix position-absolute top-0 start-0  px-3 d-flex align-items-center justify-content-center bg-light"><?= $prepend ?></div>
                <?php } ?>
                <label for="<?= $name ?>" class="form-label card-title input-text"><?= $label ?> <?= $required ? '<span>*</span>' : ''?></label>

                <?php if(empty($value)) {
                    $value = (get_field($name, $post) ? get_field($name, $post) : '' );
                }?>

                <input value="<?= $value ?>" <?= $required ? 'required' : '' ?> type="<?= $type ?>" class="form-control labelholder" name="<?= $name ?>" id="<?= $name ?>">
                <?php if( !empty($append) ) { ?>
                    <div class="sufix position-absolute top-0 end-0  px-3 d-flex align-items-center justify-content-center bg-light"><?= $append ?></div>
                <?php } ?>
            </div>

        <?php endif;

        $form = ob_get_clean();
        echo $form;

    }


}