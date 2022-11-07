<?php

/**
 * Controller: CorretoresController.php
 */

use Controller\FormController; ?>
    <div class="app-content" route="corretores">
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="page-description page-description-tabbed">
                            <h5>Imóveis</h5>
                            <h1>
                                <?php if(!isset($_GET['id'])) { ?>
                                    Adicionar corretor
                                <?php } else { ?>
                                    Atualizar corretor 
                                <?php } ?>
                            </h1>
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
                                    <span class="alert-text"><?= $error ?></span>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="card">
                            <form class="card-body" method="POST" action="" id="corretores">
                                 
                                <div class="row">
                                    <?php foreach($fields as $field) { 
                                        FormController::field(
                                            post    : false,
                                            value   : isset($field['value']) ? $field['value'] : '',
                                            name    : $field['name'], 
                                            label   : $field['label'],
                                            required: $field['required'],
                                            type    : $field['type'],
                                            class   : $field['class'],   
                                        );
                                    }?>
                                </div>    
                                
                                <div class="col-12">
                                    <button id="add-unidade" class="btn btn-secondary" type="submit">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        <?php if(!isset($_GET['id'])) { ?>
                                            Adicionar corretor
                                        <?php } else { ?>
                                            Atualizar corretor 
                                        <?php } ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
    </div>