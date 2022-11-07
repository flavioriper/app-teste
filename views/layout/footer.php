<?php use Controller\BaseController as base; ?>

</div>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
        </div>
        <div class="modal-footer"> 
            <button id="close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            <button id="continue" type="button" class="btn btn-primary">Prosseguir</button>
        </div>
        </div>
    </div>
    </div>

    <!-- Javascripts -->
    <script src="<?php echo base::assets('plugins/jquery/jquery-3.5.1.min.js') ?>"></script>
    <script src="<?php echo base::assets('plugins/bootstrap/js/popper.min.js') ?>"></script>
    <script src="<?php echo base::assets('plugins/bootstrap/js/bootstrap.min.js') ?>"></script>
    <script src="<?php echo base::assets('plugins/perfectscroll/perfect-scrollbar.min.js') ?>"></script>
    <script src="<?php echo base::assets('plugins/pace/pace.min.js') ?>"></script>
    <script src="<?php echo base::assets('plugins/highlight/highlight.pack.js') ?>"></script>
    <script src="<?php echo base::assets('plugins/select2/js/select2.full.min.js') ?>"></script>
    <script src="<?php echo base::assets('plugins/jquery-steps/jquery.steps.min.js') ?>"></script>
    <script src="<?php echo base::assets('plugins/jquery-validation/jquery.validate.min.js') ?>"></script>
    <script src="<?php echo base::assets('plugins/jquery-validation/additional-methods.min.js') ?>"></script>
    <script src="<?php echo base::assets('plugins/jquery-validation/localization/messages_pt_BR.min.js') ?>"></script>
    <script src="<?php echo base::assets('plugins/dropzone/min/dropzone.min.js') ?>"></script>
    <script src="<?php echo base::assets('plugins/jquery-mask/jquery.mask.min.js') ?>"></script>
    <script src="<?php echo base::assets('plugins/jquery-ui/jquery-ui.min.js') ?>"></script>
    <script src="<?php echo base::assets('js/main.js') ?>"></script>
    <script src="<?php echo base::assets('js/custom.js') ?>"></script> 
    <script src="<?php echo base::assets('js/pages/settings.js') ?>"></script>
</body>

</html>