<?php

function checkExclusive($cep, $numero) {
    $posts = get_posts(array(
        'numberposts'	=> -1,
        'post_type'		=> 'imoveis',
        'post_status'   => 'any',
        'meta_query'	=> array(
            'relation'		=> 'AND',
            array(
                'key'	 	=> 'localizacao_cep',
                'value'	  	=> $cep,
            ),
            array(
                'key'	  	=> 'localizacao_numero',
                'value'	  	=> $numero,
            ),
            array(
                 'key' => 'publish_type',
                 'compare' => 'NOT EXISTS'
            ),
            array(
                'key' => 'exclusive',
                'value' => '1',
           ),
        ),
    ));

    if (count($posts) > 0) return true;
    return false;
}