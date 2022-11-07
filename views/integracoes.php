<?php

/**
 * Controller: IntegracoesController.php
 */

use Controller\FormController; ?>
    <div class="app-content" route="corretores">
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="page-description page-description-tabbed">
                            <h1>Editar Integrações</h1>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <?php if($error) { ?>
                            <div class="alert alert-custom alert-indicator-top indicator-danger alert-danger alert-style-light" role="alert">
                                <div class="custom-alert-icon icon-danger bg-danger text-white"><i class="material-icons-outlined">close</i></div>
                                <div class="alert-content">
                                    <span class="alert-title">Erro ao processar a solicitação</span>
                                    <span class="alert-text"><?=$error?></span>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="card">
                            <form class="card-body" id="vista-integration" method="POST" action="<?=$base::url('integracoes/vista')?>">
                                <div class="col-12">
                                    <div class="row">
                                        <div class="mb-4 position-relative col-12 col-md-6">
                                            <label for="name" class="form-label card-title input-text">Host <span>*</span></label>
                                            <input value="<?=$vista['host']?>" type="text" class="form-control labelholder" name="vista-host" id="vista-host">
                                            <div class="form-text">Identificação pessoal na url da api do vista: http://XXXX-rest.vistahost.com.br</div>
                                        </div>
                                        <div class="mb-4 position-relative col-12 col-md-6">
                                            <label for="name" class="form-label card-title input-text">Token <span>*</span></label>
                                            <input value="<?=$vista['token']?>" type="text" class="form-control labelholder" name="vista-token" id="vista-token">
                                            <div class="form-text">Token pessoal gerado pelo vista</div>
                                        </div>
                                    </div>
                                    <button class="btn btn-secondary" type="submit">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        Atualizar Vista
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
    </div>