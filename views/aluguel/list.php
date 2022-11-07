<?php

use Controller\FormController;
use Controller\BaseController as base;

acf_form_head(); ?>

    <div class="app-content" route="aluguel">
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="page-description page-description-tabbed">
                            <h1>Aluguel Único</h1> 
                        </div>
                    </div>
                    <div class="col text-end mx-5">
                        <a onclick="location.href = '<?= (!$publish_verify) ? get_site_url() . '/finalizar-contratacao/?add-to-cart=6671' : base::url('aluguel/adicionar') ?>';" id="add-unidade" class="btn btn-secondary" type="button">
                            <?= (!$publish_verify) ? 'Assinar adicional' : 'Adicionar Imóvel' ?>
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-4">
                        <div class="card widget widget-stats">
                            <div class="card-body">
                                <div class="widget-stats-container d-flex">
                                    <div class="widget-stats-icon <?= (($publish_data['active'] == 0) ? 'bg-danger' : (($publish_data['active'] > 1) ? 'bg-success' : 'bg-warning' )) ?>">
                                    </div>
                                    <div class="widget-stats-content flex-fill">
                                        <span class="widget-stats-title">Assinaturas Ativas</span>
                                        <span class="widget-stats-amount"><?= $publish_data['active'] ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card widget widget-stats">
                            <div class="card-body">
                                <div class="widget-stats-container d-flex">
                                    <div class="widget-stats-icon <?= (($publish_data['publish'] == 0) ? 'bg-danger' : (($publish_data['publish'] > 1) ? 'bg-success' : 'bg-warning' )) ?>">
                                    </div>
                                    <div class="widget-stats-content flex-fill">
                                        <span class="widget-stats-title">Imóveis Publicados</span>
                                        <span class="widget-stats-amount"><?= $publish_data['publish'] ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card widget widget-stats">
                            <div class="card-body">
                                <div class="widget-stats-container d-flex">
                                    <div class="widget-stats-icon <?= (($publish_data['avaible'] == 0) ? 'bg-danger' : (($publish_data['avaible'] > 1) ? 'bg-success' : 'bg-warning' )) ?>">
                                    </div>
                                    <div class="widget-stats-content flex-fill">
                                        <span class="widget-stats-title">Imóveis Disponíveis</span>
                                        <span class="widget-stats-amount"><?= $publish_data['avaible'] ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <form id="search" item="aluguel">
                        <div class="mb-4 mt-4 position-relative input-group"> 
                            <input value="<?= (isset($_GET['search']) ? $_GET['search'] : '' ) ?>" placeholder="Pesquisar imóvel..." type="text" class="form-control" name="search" id="search">
                            <button type="submit" class="btn btn-primary"><i style="width: 20px;" class="material-icons">search_outline</i>Pesquisar</button>
                        </div>
                    </form>
                </div>


                <div class="row">
                    <div class="col">
                        <div class="tab-content" id="myTabContent">

                            <div class="tab-pane fade show active" id="account" role="tabpanel" aria-labelledby="account-tab">

                                <div id="lista-add-unidade" class="col-12">

                                    <?php if(empty($imoveis)) { ?>

                                        <div id="no-data" class="text-center bg-white shadow-sm my-4 rounded p-5">
                                            <img src="<?php echo base::assets('images/not-found-female.svg') ?>" />
                                            <h3 class="text-muted fw-bold fs-5 mt-3">
                                                <?php if(isset($_GET['search'])) {
                                                    echo 'Não encontramos nenhum resultado com os termos solicitados';
                                                } else {
                                                    echo 'Nenhum imóvel cadastrado, clique em "Adicionar imóvel" para incluir novos.';
                                                }
                                                ?>
                                                
                                            </h3>
                                        </div>

                                    <?php }  else { ?>
                                
                                        <div style="display: block;" class="row ctable">
                                            <div class="row head">
                                                <div class="col">
                                                    Título
                                                </div>
                                                <div class="col-2">
                                                    Código
                                                </div>
                                                <div class="col">
                                                    Perfil
                                                </div>
                                                <div class="col-1 text-center">
                                                    Habilitado
                                                </div>
                                                <div class="col-3"></div>
                                            </div>


                                            <?php foreach($imoveis as $imovel) { 
                                                
                                                $info = get_post_meta($imovel->ID, 'info', true); ?>

                                                <div class="row list align-items-center"> 
                                                
                                                    <div class="col"><?= get_field('info_titulo', $imovel->ID) ? get_field('info_titulo', $imovel->ID) : 'Sem título'; ?></div>
                                                    <div class="col-2"><?= get_field('codigo', $imovel->ID) ? get_field('codigo', $imovel->ID) : 'Não informado'; ?></div>
                                                    <div class="col"><?= get_field('perfil_do_imovel', $imovel->ID) ? get_field('perfil_do_imovel', $imovel->ID) : 'Não informado'; ?></div>
                                                    <div class="col-1 d-flex align-items-center justify-content-center">
                                                        <span style="display: none;" class="spinner-border spinner-border-sm position-absolute" role="status" aria-hidden="true"></span>  
                                                        <div class="form-check form-switch ms-3">
                                                            <input data-action="status"  data-target="aluguel" data-id="<?= $imovel->ID ?>" class="form-check-input" type="checkbox" <?= ($imovel->post_status == 'publish') ? 'checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                    <div class="col-3 d-flex align-items-center justify-content-center justify-content-lg-end"> 

                                                        <button data-action="edit" data-target="aluguel" data-id="<?= $imovel->ID ?>" type="button" class="btn btn-primary btn-sm d-flex align-items-center justify-content-center me-0 me-lg-2">
                                                            <span class="material-icons-two-tone icon-white">edit</span> Editar
                                                        </button>
                                                        <button data-action="delete" data-target="aluguel" data-id="<?= $imovel->ID ?>" type="button" class="btn btn-danger btn-sm d-flex align-items-center justify-content-center">
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
