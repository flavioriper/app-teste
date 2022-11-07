(function($){
    
    "use strict";

    var inputEstado = $('[name="localizacao_estado"]');
    var inputCidade = $('[name="localizacao_cidade"]');
    var siglas;

    $('[pre-select]').each(function(){
        $(this).val($(this).attr('pre-select'));
        $(this).trigger('change');
    });
     
    $.getJSON( url + "/json/siglas.json", function( dados ) {
        siglas = dados;
        
    });   

    $.getJSON( url + "/json/estados.json", function( data ) {
        var first = 0;
        $.each( data, function( key, val ) { 
            if(inputEstado.attr('pre-select') == key) { 
                inputEstado.append(`<option selected sigla="${siglas[key]}" value="${key}">${val}</option>`);
            } else {
                inputEstado.append(`<option sigla="${siglas[key]}" value="${key}">${val}</option>`); 
            }

            if(first == 0) {
                if(inputEstado.attr('pre-select')) { 
                    recarregaMunicipios(inputCidade, inputEstado.attr('pre-select'));
                    inputCidade.on('cidadesAtualizadas', function(){
                        inputCidade.val(inputCidade.attr('pre-select'))
                        inputCidade.trigger('change');
                    });
                } else {
                    recarregaMunicipios(inputCidade, key);
                }
            }
            

            first++;
        });

    });

    inputEstado.on('change', function() {
        recarregaMunicipios(inputCidade, $(this).val());
    });

    $('#localizacao_cep').on('input', function() {

        var _el = $(this);
        var _pf = (field) => {return `[name="localizacao_${field}"]`}

        if(_el.val().replace('-','').length >= 8) {

            $.getJSON( `https://viacep.com.br/ws/${_el.val()}/json/`, function( dados ) {
                //console.log(dados);

                if(!('erro' in dados)) {
                    $(_pf('endereco')).val(dados.logradouro);
                    $(_pf('bairro')).val(dados.bairro); 
                    $(_pf('estado')).find(`[sigla="${dados.uf}"]`).prop('selected', true);
                    $(_pf('estado')).trigger('change');
                    
                    $(_pf('cidade')).on('cidadesAtualizadas', function(){
                        $(_pf('cidade')).find(`option:contains(${dados.localidade})`).prop('selected', true);
                        $(_pf('cidade')).trigger('change');
                    });

                    _el.parents('section').find('input').each(function() {
                        if($(this).val()) {
                            $(this).prev('label').hide();
                        }  
                    })

                }

            })
            .fail(function() {
                alert("Erro ao consultar o CEP, Não é possível conectar ao servidor.")
            });
        }

    });
    
    $('select').select2();

    function recarregaMunicipios(input, index) {
        var codigo = index;
        $(input).html('');
        $(input).append(`<option disabled selected>Município</option>`);
        $.getJSON( url + "/json/municipios.json", function( data ) {
            $.each( data, function( key, val ) {
                if(val.state_id == codigo) {
                    $(input).append(`<option value="${val.id}">${val.name}</option>`);
                }
            });
        }).done(function(){
            $(input).trigger("cidadesAtualizadas");
        });

        

    }

    function cmp(a, b) {
        return a[1].localeCompare(b[1]);
    }

})(jQuery);