<?php

use Controller\FormController;
use Controller\BaseController as base;

acf_form_head(); ?>

    <div class="app-content" route="corretores">
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="page-description page-description-tabbed">
                            <h1>Corretores</h1> 
                        </div>
                    </div>
                    <div class="col text-end mx-5">
                        <a onclick="location.href = '<?= base::url('corretores/adicionar') ?>';" id="add-corretor" class="btn btn-secondary" type="button">
                            Adicionar Corretor
                        </a>
                    </div>
                </div>


                <div class="row">
                    <form id="search" item="aluguel">
                        <div class="mb-4 mt-4 position-relative input-group"> 
                            <input value="<?= (isset($_GET['search']) ? $_GET['search'] : '' ) ?>" placeholder="Pesquisar corretor..." type="text" class="form-control" name="search" id="search">
                            <button type="submit" class="btn btn-primary"><i style="width: 20px;" class="material-icons">search_outline</i>Pesquisar</button>
                        </div>
                    </form>
                </div>


                <div class="row">
                    <div class="col">
                        <div class="tab-content" id="myTabContent">

                            <div class="tab-pane fade show active" id="account" role="tabpanel" aria-labelledby="account-tab">

                                <div id="lista-add-unidade" class="col-12">

                                    <?php if(empty($users->get_results())) { ?>

                                        <div id="no-data" class="text-center bg-white shadow-sm my-4 rounded p-5">

                                            <img src="<?php echo base::assets('images/not-found-female.svg') ?>" />
                                            <h3 class="text-muted fw-bold fs-5 mt-3">
                                                <?php if(isset($_GET['search'])) {
                                                    echo 'Não encontramos nenhum resultado com os termos solicitados';
                                                } else {
                                                    echo 'Nenhum corretor cadastrado, clique em "Adicionar corretor" para incluir novos.';
                                                }
                                                ?>
                                                
                                            </h3>
                                        </div>

                                    <?php }  else { ?>
                                
                                        <div style="display: block;" class="row ctable">
                                            <div class="row head">
                                                <div class="col">
                                                    NOME
                                                </div>
                                                <div class="col">
                                                    E-MAIL
                                                </div>
                                                <div class="col-2"> 
                                                    CRECI
                                                </div>
                                                <div class="col-3"></div>
                                            </div>


                                            <?php foreach($users->get_results() as $user) { 
                                                
                                                $info = get_userdata( $user->ID ); ?>

                                                <div class="row list align-items-center"> 
                                                
                                                    <div class="col">
                                                        <?= $info->display_name ?>
                                                    </div>
                                                    <div class="col">
                                                        <?= $info->user_email ?>
                                                    </div>
                                                    <div class="col-2">
                                                        <?= get_user_meta($user->ID, 'creci', true) ?>
                                                    </div>
                                                    <div class="col-3 d-flex align-items-center justify-content-center justify-content-lg-end"> 

                                                        <button data-action="edit" data-target="corretor" data-id="<?= $user->ID ?>" type="button" class="btn btn-primary btn-sm d-flex align-items-center justify-content-center me-0 me-lg-2">
                                                            <span class="material-icons-two-tone icon-white">edit</span> Editar
                                                        </button>
                                                        <button data-action="delete" data-target="corretor" data-id="<?= $user->ID ?>" type="button" class="btn btn-danger btn-sm d-flex align-items-center justify-content-center">
                                                            <span class="material-icons-two-tone icon-white">delete</span> Remover
                                                        </button> 

                                                    </div>
                                                </div>

                                            <?php } ?>

                                        </div>

                                    <?php } ?>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
