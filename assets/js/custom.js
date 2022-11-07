// Here goes your custom javascript
Dropzone.autoDiscover = false;

const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);
const modal = new bootstrap.Modal('#exampleModal');

var first = true; 
var form  = $("#step-form");
var title = $('#get-step-title');
var list  = [];
var place = $('.labelholder');
var size  = 0
var update= 0;
var imovel = urlParams.get('id');
var tipoImovel = '';
var target = '';
var faixas = {};
 

var localizationFields  = [
    {
        field : '#localizacao_cep',
        min : 9,
    },
    {
        field : '#localizacao_numero',
        min : 1,
    },
    
]
var localizationAthorized = false;

var input = {
    number : [
        '#localizacao_numero',
        '#andar',
        '.number input'
    ],
    money: [
        '#valor_da_unidade',
        '#valor_do_iptu',
        '.money input'
    ],
    postcode : ['#localizacao_cep'],
    decimal: [
        '#area_total', 
        '#area_privativa',
        '#area_construida',
        '.decimal input',
    ],
    year : [
        '#ano_da_construcao',
        '.year input'
    ],
    phone: [
        '#whatsapp',
        '.phone'  
    ]
}

var route = $('[route]').attr('route');
var named = ''; 

switch(route) { 
    case "imoveis":
        route = 'imoveis';
        named = 'imovel';
    break;
    case "aluguel":
        route = 'aluguel';
        named = 'aluguel';
    break;
    case "corretores":
        route = 'corretores';
        named = 'corretor'; 
    break;
    default : 
        route = 'lancamentos';
        named = 'lancamento';
}

form.children('h3').each( function(){
    list.push($(this).text());
});

form.on('change', 'select[required]', function(){
    $(this).valid();
})

form.find('div.sufix').each(function(){ 
    $(this).prevAll('input, label').css('padding-right', $(this).outerWidth() + 'px');
})

form.find('div.prefix').each(function(){ 
    $(this).nextAll('input, label').css('padding-left', $(this).outerWidth() + 'px');
})

form.validate({
    highlight: function(element, errorClass) {
        if($(element).hasClass('form-select')) { 
            $(element).next('.select2').find('.select2-selection').addClass('error');
        } else {
            $(element).addClass(errorClass);
            $(element).next('label').removeClass('d-none')
        }
    }, 
    success: function(element) {
        $(element).parent().find('.select2-selection').removeClass('error');
    },
    errorPlacement: function(error, element) {
        
        if($(element).hasClass('form-select')) {
            $(element).next('.select2').after( error );
        } else {
            element.after( error ); 
        }

        error.addClass('d-none');
        
    }
});


if($('.ctable .list').length == 0) {
    $('.ctable').hide(0);
} else { 
    $('#no-data').hide(0);
}

form.on('click', '#add-unidade', function(){
    var _el   = $(this);
    var _this = $(this).parents('section');
    var valid = _this.find('[required]').valid();
    var unidade = _this.find('#numero_da_unidade').val();
 
    if(valid) {
        _el.find('.spinner-border').addClass('loading');
        $.post( `${url}/unidade/${unidade}/${imovel}`, _this.find('[name]').serialize(), function( data ) {
            
            var result = JSON.parse(data);

            
            if('status' in result) {
                alert(result.message);
            } else {

                $('#no-data').hide(0);

                $('.ctable .head').after(`
                <div class="row list align-items-center">
                    <div class="col">${result.numero_da_unidade}</div>
                    <div class="col">${result.tipo_de_unidade}</div>
                    <div class="col-2 d-flex align-items-center justify-content-center justify-content-lg-end" lancamento="${result.post}" unidade="${result.numero_da_unidade}"> 
                        <button update type="button" class="btn btn-primary btn-sm d-flex align-items-center justify-content-center me-0 me-lg-2">
                            <span class="material-icons-two-tone icon-white">edit</span> Editar
                        </button>
                        <button delete type="button" class="btn btn-danger btn-sm d-flex align-items-center justify-content-center">
                            <span class="material-icons-two-tone icon-white">delete</span> Remover
                        </button> 
                    </div>
                </div>
                `);

                $('.ctable').show(0);
                _this.find('[name]').val('');
                _this.find('[name]').prev().show(0);
            }

            _el.find('.spinner-border').removeClass('loading');
            
        });
    }
});

let editarUnidade;
form.on('click', '#update-unidade', function(){
    var _el   = $(this);
    var _this = $(this).parents('section');
    var valid = _this.find('[required]').valid();
    let row = $(`[unidade="${editarUnidade}"]`).parent('.list'); 
 
    if(valid) {


        _el.find('.spinner-border').addClass('loading');
        $.post( `${url}/lancamento/${imovel}/update/unidade/${update}`, _this.find('[name]').serialize() + '&updateUnidade=' + editarUnidade, function( data ) {
            var result = JSON.parse(data);

            row.after(`
            <div class="row list align-items-center">
                <div class="col">${result.numero_da_unidade}</div>
                <div class="col">${result.tipo_de_unidade}</div>
                <div class="col-2 d-flex align-items-center justify-content-center justify-content-lg-end" lancamento="${result.post}" unidade="${result.numero_da_unidade}"> 
                    <button update type="button" class="btn btn-primary btn-sm d-flex align-items-center justify-content-center me-0 me-lg-2">
                        <span class="material-icons-two-tone icon-white">edit</span> Editar
                    </button>
                    <button delete type="button" class="btn btn-danger btn-sm d-flex align-items-center justify-content-center">
                        <span class="material-icons-two-tone icon-white">delete</span> Remover
                    </button> 
                </div>
            </div>
            `);

            row.remove(); 
            reorderUnidades();

            _this.find('[name]').val('');  
            _this.find('[name]').prev().show(0);
            $('#add-unidade').show(0);
            _el.addClass('d-none');
            _el.find('.spinner-border').removeClass('loading');
        });
    }
});

form.on('click', '[update]', function(){

    var _this   = $(this).parents('section');
    let _form   = $(this).parents('form'); 
    update      = $(this).parent().attr('unidade');
    imovel      = $(this).parent().attr('lancamento');

    editarUnidade = update;

    showLoader('#step-form');

    $("html, body").animate(
        { scrollTop: "0" }
    ); 


    $.post(`${url}/lancamento/${imovel}/unidade/${update}`, form.find('[name]').serialize() , function( data ) {

        var result = JSON.parse(data);

        $.each(result, function(index,item){
            _this.find('[name="'+ index +'"]').val(item).trigger( "input" );
            _this.find('[name="'+ index +'"]').val(item).change();
        }); 

        $('#add-unidade').hide();
        $('#update-unidade').removeClass('d-none');

        showLoader('#step-form', false);
          
    });
})

form.on('click', '[delete]', function(){

    var _this   = $(this).parents('section');
    update      = $(this).parent().attr('unidade');
    imovel  = $(this).parent().attr('lancamento'); 
    
    showLoader('#step-form');
    $.post(`${url}/lancamento/${imovel}/delete/unidade/${update}`, function( unidade ) {
        $(`[unidade="${unidade}"]`).parent().remove();     
        showLoader('#step-form', false);
    });
})

form.steps({
    headerTag: "h3",
    bodyTag: "section",
    autoFocus: true,
    labels: {
        cancel: "Cancelar",
        finish: "Salvar",
        next: "Próximo",
        previous: "<span class='material-icons-two-tone'>arrow_back_ios</span>Voltar",
        loading: "Carregando"
    },
    onInit: function (event, currentIndex) { 

        form.find('span.number').remove();
        title.text(list[currentIndex]);
        form.find('.steps').hide(0);

    },
    onStepChanging: function (event, currentIndex, newIndex)
    {

        var checked = false; 

        if(currentIndex > newIndex) {
            checked = true;
            return true;
        }

        
        if(!localizationAthorized) {
            return false;
        }

        if(list[currentIndex] == 'Unidades' && form.find('#lista-add-unidade').find('.list').length < 1) {
            showModal('Algo saiu errado!', 'É obrigatório o cadastro de ao menos uma unidade.');
            return false;
        } 

        if(list[currentIndex] == 'Unidades' && form.find('#lista-add-unidade').find('.list').length >= 1) {
            return true;
        }
        
        return form.valid();

    },
    onStepChanged: function (event, currentIndex, priorIndex)
    { 
        if(form.attr('form') == 'imoveis') { 
           if(tipoImovel != 'apartamento' && list[currentIndex] == 'Área Comum') {
                var to = (currentIndex > priorIndex ? "next" : "previous");
                $(this).steps(to);
                return;
           }
        }
        title.text(list[currentIndex]);
    },
    onFinished: function (event, currentIndex) { 
        
        showLoader('#step-form');

        $.post( `${url}/${named}/${imovel}/create`, form.find('[name]').serialize(), function( data ) {
            //console.log(data); 
        }).done(function(data){
            console.log(data);
            // window.location.replace(`${url}/${route}`);
        });
    }
});


$('[name="tipo_imovel"]').on('change', function() {
    tipoImovel = $(this).val();
    tipoImovel = tipoImovel.split('-');
    tipoImovel = tipoImovel[0];
});


exclusiveVerification();
$.each(localizationFields, function(key, val) {
    $(val.field).on('focusout', function() {

        if($(localizationFields[0].field).val().length >= localizationFields[0].min && $(localizationFields[1].field).val().length >= localizationFields[1].min) {
            exclusiveVerification();
        } 

    });
});

$.each(input.money, function( index, value ) {
    form.find(value).mask('#.##0,00', {reverse: true});
});

$.each(input.number, function( index, value ) {
    form.find(value).mask('0#');
});

$.each(input.postcode, function( index, value ) {
    form.find(value).mask('00000-000')
}); 

$.each(input.year, function( index, value ) {
    form.find(value).mask('0000')
}); 

$.each(input.decimal, function( index, value ) {
    $(value).on('input', function() {       
        var test = $(this).val().replace(/[^0-9,]/g, ""); 
        test = test.split(",");

        if(test.length >= 3) {
            test.push(",");
            test = test.join("");
        } 

        $(this).val(test);
    })
});

var SPMaskBehavior = function (val) {
  return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
},
spOptions = {
  onKeyPress: function(val, e, field, options) {
      field.mask(SPMaskBehavior.apply({}, arguments), options);
    }
};

$('#whatsapp, .phone').mask(SPMaskBehavior, spOptions);



/**-----------------------------------------------
 * Ações on Click do atributo data-action
 *----------------------------------------------*/ 
$('body').on('click', '[data-action]', function(){

    var action = $(this).attr('data-action');
    var target = $(this).attr('data-target'); 
    var id     = $(this).attr('data-id');
    var confirm= false;

    if(action == "edit") {
        window.location.href = url + '/' + route + '/adicionar?id=' + id;
    }

    if(action == "delete") {
        if (window.confirm("Tem certeza que deseja eliminar o " + named + "?")) {
            showLoader("body");
            $.post( `${url}/${named}/${id}/delete`, function( data ) {
                window.location.replace(`${url}/${route}`);
            }); 
        }  
    }

    if(action == "status") {

        var _this = $(this); 
        _this.addClass('d-none'); 
        _this.parent().prev().show(0); 
 
        $.post( `${url}/${named}/${id}/status/change`, function( data ) {
            var status = JSON.parse( data );
            
            if(status) {
                _this.parent().prev().hide(0);
                _this.removeClass('d-none');
            } else {

                if (_this.is(':checked')) {
                    _this.prop( "checked", false );
                } else {
                    _this.prop( "checked", true );
                }

            }

            //console.log(data);
        }); 

    }

    if(action == "exclusive") {

        var _this = $(this); 
        _this.addClass('d-none'); 
        _this.parent().prev().show(0); 
 
        $.post( `${url}/${named}/${id}/exclusive/change`, function( data ) {
            var status = JSON.parse( data );
            _this.parent().prev().hide(0);
            _this.removeClass('d-none');

            //console.log(data);
        }); 
    }

});
   

Dropzone.prototype.defaultOptions.dictDefaultMessage = "Drop files here to upload";
Dropzone.prototype.defaultOptions.dictFallbackMessage = "Your browser does not support drag'n'drop file uploads.";
Dropzone.prototype.defaultOptions.dictFallbackText = "Please use the fallback form below to upload your files like in the olden days.";
Dropzone.prototype.defaultOptions.dictFileTooBig = "Arquivo acima do limite: ({{filesize}}MB). Tamanho máximo permitido: {{maxFilesize}}MB.";
Dropzone.prototype.defaultOptions.dictInvalidFileType = "O tipo de arquivo não é permitido.";
Dropzone.prototype.defaultOptions.dictResponseError = "Server responded with {{statusCode}} code.";
Dropzone.prototype.defaultOptions.dictCancelUpload = "";
Dropzone.prototype.defaultOptions.dictCancelUploadConfirmation = "Realmente deseja cancelar o envio?";
Dropzone.prototype.defaultOptions.dictRemoveFile = '<button type="button" class="btn btn-danger btn-sm w-100 rounded-0">Remover</button>';
Dropzone.prototype.defaultOptions.dictMaxFilesExceeded = "You can not upload any more files.";



var uploads = {};
var myDropzone = {};

$('div.dropzoneForm').each(function(){

    var _el   = $(this);
    var limit = null;
    var preview = '.' + $(this).attr('id') + 'Preview';

    if(_el.hasClass('single')) {
        limit = 1  
    }

    var _this = _el.dropzone({  
        previewsContainer: preview,
        autoProcessQueue: true,
        addRemoveLinks: true, 
        parallelUploads: 1,
        maxFilesize: 2,
        uploadMultiple: false,
        paramName: $(this).attr('id'), 
        url: url + '/galeria/' + named + '/' + imovel,
        clickable: true, 
        accept: function(file, done) { 
            console.log('Accept', url + '/galeria/' + named + '/' + imovel, file);
            if($(preview).find('div.dz-preview').length > 1 && limit == 1) {
                $(preview).find('div.dz-preview')[0].remove();
            } 

            var css = {
                'opacity': 0.3,
                'pointer-events':'none'
            }
            _el.css(css);
            _el.next().css(css);
            $(".actions").css(css);

            done();
        },
        error: function(file, msg){
            console.log('ERROR', file, msg);
            this.removeFile(file);
            alert(msg);
        },
        success: function(file) {
            console.log('SEND', file);
            var css = {
                'opacity': 1,
                'pointer-events':'auto'
            }
            _el.css(css);
            _el.next().css(css);
            $(".actions").css(css);
        },
        init: function () {
            console.log('INIT', url + '/galeria/' + named + '/' + imovel);
            myDropzone[_el.attr('id')] = this;

            this.on("addedfile", function(file) {  
                if (this.files.length > 1 && limit == 1) { 
                    this.removeFile(this.files[0]); 
                }
            });  

        }
    }); 
});

$(document).on('click', '[data-dz-remove]', function() {
    var post        = $(this).attr('data-dz-post');
    var attachment  = $(this).attr('data-dz-remove');

    showLoader("body");
    $.post( `${url}/${named}/${post}/delete/attachment/${attachment}`, function( data ) {
        console.log('DELETE', post, attachment);
        showLoader("body", false);
    }); 
    
    $(this).parents('.dz-preview').remove();
});

$(window).on('load', function(){
    refreshMap();
});



form.on('blur', '[name="localizacao_cep"], [name="localizacao_endereco"], [name="localizacao_numero"], [name="localizacao_bairro"]', function(){
    refreshMap();
});


$('#step-form .labelholder').each( function() {
    checkPlaceholder($(this)); 
});

$(document).on('input', '#step-form .labelholder', function() {
    checkPlaceholder($(this));
});

/*
$(document).on('submit', '#corretores', function(e) {
    e.preventDefault();
    var name = $(this).find('#name').val().split(/\s+/);
    if(name.filter(n => n).length < 2) {
        alert("Informe o nome e o sobrenome do corretor para prosseguir");
    } else {  
        e.currentTarget.submit();
    }
});
*/




var object = {};
object.aluguel   = new Array;
object.temporada = new Array;
object.universal = new Array;

var fpo = $('[name="faixa_preco"]');
var fpc = fpo.clone();


$.each( fpc.find(':not(:disabled)'), function( index, value ) {

    var key = value.value.split("-")[0];
    if(!(key in object)) { 
        object['universal'].push(value)
    } else {
        object[key].push(value);
    }

});

$(document).on('change', '[name="oferta"]', function() {

    var selected = $(this).find(":selected").val();
    fpo.find(':not(:disabled)').remove();
 
    if(!(selected in object)) { 
        selected = 'universal' ;
    } 

    $.each(object[selected], function( index, value ) {
        fpo.append("<option value='"+ value.value +"'>"+ value.text +"</option>");
    });

});


reorderUnidades();

$('#sincronizar-vista').click(function() {
    const path = $(this).attr('url-link');
    $.get(path);
    setTimeout(() => {
        location.reload();
    }, 2000);
})

function checkPlaceholder(item) { 
    if(item.val()) {
        item.parent().children('label').fadeOut(100);
    } else {
        item.parent().children('label').fadeIn(100);
    }    
}

function resizeForm() {
    size = form.find('.body').height() + form.find('.actions').height();
    form.find('.content').height(size - 25);
}

function refreshMap(){
    var endereco = '';

    if($('[name="localizacao_endereco"]').val()) {
        endereco += $('[name="localizacao_endereco"]').val() + ', ';
    } 
    
    if($('[name="localizacao_numero"]').val()) {
        endereco += $('[name="localizacao_numero"]').val() + ', ';
    }

    if($('[name="localizacao_cep"]').val()) {
        endereco += $('[name="localizacao_cep"]').val() + '';
    } 

    $('iframe.map').attr('src','https://maps.google.it/maps?q=' + endereco + '&output=embed');
}

function showModal(title, content, action = false) {
    var el = $('#exampleModal');
    el.find('#exampleModalLabel').text(title);
    el.find('.modal-body').text(content);
    if(!action) {
        el.find('#continue').hide(0);
    } else {
        el.find('#continue').show(0);
    }
    modal.show();
}

function showLoader(element, show = true) {
    if(show) {
        $(element).css('opacity', 0.4);
        $(element).css('pointer-events', 'none');
    } else {
        $(element).css('opacity', 1);
        $(element).css('pointer-events', 'all');
    }
}

function exclusiveVerification() {
    showLoader('#step-form');
    $.post( `${url}/lancamento/verifica/exclusividade`,  form.find('[name]').serialize(), function( data ) {
        var exists = JSON.parse( data );
        if(exists) {
            showModal('Cadastro não autorizado', 'O endereço fornecido já está cadastrado pela construtora responsável.');
            localizationAthorized = false;
        } else {
            localizationAthorized = true;
        }
        
        form.trigger("exclusividadeVerificada");
        showLoader('#step-form', false);
    }); 
} 

function reorderUnidades(){
    let array = new Array;

    $('#lista-add-unidade .list').each(function(){
        array.push($(this));
    });

    if(array.length > 1) {
    
        array.sort(function(a, b) {
            var c1 = parseInt(a.find('[unidade]').attr('unidade'));
            var c2 = parseInt(b.find('[unidade]').attr('unidade'));
        
            if (c1 < c2) 
            return -1; 
            if (c1 > c2)
            return 1;
            return 0; 
        });

        $('#lista-add-unidade .list').remove(); 
        $('#lista-add-unidade .head').after(array);
        
    }
}