<?php

namespace Controller;

use Flight;
use Controller\BaseController as base;
use stdClass;

class VistaApiController extends BaseController {
    protected static $timeoutProtection = 1500;
    protected static $fields = [
        'Codigo','CEP','Numero','Cidade','Bairro','Status','TituloSite',
        'Categoria','AnoConstrucao','AptosAndar','Mobiliado','UF','DescricaoWeb',
        'Dormitorios','Suites','TotalBanheiros','Vagas','VagasCobertas','SalaTV',
        'SalaJantar','SalaEstar','Lavabo','AreaServico','Cozinha','Closet','Escritorio',
        'Copa','AreaConstruida','AreaPrivativa','AreaTotal','DependenciaDeEmpregada',
        'ValorVenda','ValorLocacao','ValorIptu','ValorCondominio','AceitaPermuta','AceitaFinanciamento'
    ];
    protected static $mediaFields = [['Foto' => ['Foto', 'FotoPequena', 'Destaque']], ['Video' => ['Codigo', 'Destaque', 'Descricao', 'DescricaoWeb', 'Video', 'Tipo']]];

    public static function getStatus() {
        global $wpdb;
        $user = get_user_meta(get_current_user_id(), 'parent', true) ? get_user_meta(get_current_user_id(), 'parent', true) : get_current_user_id();
        $sql = "SELECT * FROM integracao_vista WHERE user_id = $user";
        $result = $wpdb->get_row($sql);
        if ($result?->token == '' || $result?->host == '') return false;
        return $result?->is_running;
    }

    protected static function getCurrentPage() {
        global $wpdb;
        $user = get_user_meta(get_current_user_id(), 'parent', true) ? get_user_meta(get_current_user_id(), 'parent', true) : get_current_user_id();
        $sql = "SELECT last_page FROM integracao_vista WHERE user_id = $user";
        $result = $wpdb->get_row($sql);
        return $result?->last_page != null ? json_decode($result->last_page) : null;
    }

    protected static function updateStatus($status, $lastPage = null) {
        global $wpdb;
        $fields = "is_running = $status ";
        if ($status == 0) {
            $fields .= ", updated_at = '".date('Y-m-d H:i:s')."' ";
        }
        if ($lastPage) {
            $fields .= ", last_page = '".json_encode($lastPage)."' ";
        } else {
            $fields .= ", last_page = null ";
        }
        $user = get_user_meta(get_current_user_id(), 'parent', true) ? get_user_meta(get_current_user_id(), 'parent', true) : get_current_user_id();
        $sql = "UPDATE integracao_vista SET $fields WHERE user_id = $user";
        return $wpdb->query($sql);
    }

    public static function updateTokens($host, $token) {
        global $wpdb;
        $user = get_user_meta(get_current_user_id(), 'parent', true) ? get_user_meta(get_current_user_id(), 'parent', true) : get_current_user_id();
        $check = $sql = "SELECT id FROM integracao_vista WHERE user_id = $user";
        $check = $wpdb->get_row($check);
        if ($check) {
            $sql = "UPDATE integracao_vista SET host = '$host', token = '$token' WHERE user_id = $user";
        } else {
            $sql = "INSERT INTO integracao_vista (user_id, host, token, is_running) VALUES ($user, '$host', '$token', 0);";
        }
        return $wpdb->query($sql);
    }

    public static function getTokens() {
        global $wpdb;
        $user = get_user_meta(get_current_user_id(), 'parent', true) ? get_user_meta(get_current_user_id(), 'parent', true) : get_current_user_id();
        $sql = "SELECT * FROM integracao_vista WHERE user_id = $user";
        $result = $wpdb->get_row($sql);
        $data = ['token' => '', 'host' => ''];

        if ($result) {
            $data['token'] = $result->token;
            $data['host'] = $result->host;
        }

        return $data;
    }

    protected static function getApiUrlWithToken($pagina = 1) {
        global $wpdb;
        $token = '';
        $host = '';

        $tokens = static::getTokens();

        if ($tokens) {
            $token = $tokens['token'];
            $host = $tokens['host'];
        }

        $params = [];
        $params['fields'] = static::$fields;
        $params['paginacao'] = ['pagina' => $pagina, 'quantidade' => 50];
        $url = "https://$host-rest.vistahost.com.br/imoveis/listar?key=$token&showtotal=1&pesquisa=".json_encode($params);
        return $url;
    }

    protected static function createResponseObject($response) {
        $retorno = new stdClass();
        $retorno->status = 200;
        $retorno->message = '';
        $retorno->payload = null;
    
        if (!$response) {
            $retorno->status = 404;
            $retorno->message = 'O host da API não foi encontrado, verifique.';
        } else if (isset($response->message)) {
          $retorno->status = $response->status;
          $retorno->message = $response->message;
        } else {
          $payload = new stdClass();
          $payload->total = $response->total;
          $payload->paginas = $response->paginas;
          $payload->pagina = $response->pagina;
          $payload->quantidade = $response->quantidade;
          unset($response->total);
          unset($response->paginas);
          unset($response->pagina);
          unset($response->quantidade);
          $payload->data = (array) $response;
    
          $retorno->payload = $payload;
        }
        return $retorno;
    }

    protected static function fetchImoveis($pagina = 1) {
        $url = static::getApiUrlWithToken($pagina);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        $response = curl_exec($ch);
        $response = json_decode($response);

        return static::createResponseObject($response);
    }

    protected static function getPostsAndMetaByCodigo($codigos, $user) {
        $result = [];
        $data = [
            'title' => 'Imoveis',
            'publish_data'  =>  base::publishData($user, 'imovel'),
            'publish_verify' => user_can( $user, 'administrator' ) ? true : base::publishVerify(
                base::publishData($user, 'imovel')
            )
        ];

        $args = array(
            'author'        =>  $user,
            'posts_per_page'=> -1,
            'meta_key'      => 'codigo',
            'meta_value'    => $codigos,
            'post_type'     => 'imoveis',
            'post_status'   => 'any',
        );

        foreach (get_posts($args) as $post) {
            $meta = get_post_meta($post->ID, 'codigo');
            if (count($meta) > 0) $result[$meta[0]] = $post->ID;
        }

        return $result;
    }

    protected static function multi_download(array $urls, $path) {
        $multi_handle = curl_multi_init();
        $file_pointers = [];
        $curl_handles = [];
        $files = [];
        
        foreach ($urls as $key => $url) {
            
            $filename = basename($url);
            $file = $path . '/' . $filename;
            $files[] = $file;
            
            $curl_handles[$key] = curl_init($url);
            if(!file_exists($file)) {
                $file_pointers[$key] = fopen($file, "w");
                curl_setopt($curl_handles[$key], CURLOPT_AUTOREFERER, TRUE);
                curl_setopt($curl_handles[$key], CURLOPT_HEADER, 0);
                curl_setopt($curl_handles[$key], CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl_handles[$key], CURLOPT_TIMEOUT, 30); //30s
                curl_setopt($curl_handles[$key], CURLOPT_FOLLOWLOCATION, TRUE);  
                curl_setopt($curl_handles[$key], CURLOPT_SSL_VERIFYHOST, FALSE);     
                curl_setopt($curl_handles[$key], CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($curl_handles[$key], CURLOPT_FILE, $file_pointers[$key]);
                curl_multi_add_handle($multi_handle,$curl_handles[$key]);
            }
        }
        do {
            curl_multi_exec($multi_handle,$running);
        } while ($running > 0);
        foreach ($urls as $key => $url) {
            curl_multi_remove_handle($multi_handle, $curl_handles[$key]);
            curl_close($curl_handles[$key]);
            if($file_pointers[$key]){ 
              fclose ($file_pointers[$key]);
            }
        }
        curl_multi_close($multi_handle);
        return $files;
    }

    protected static function fetchMediaImoveis($imoveis) {
        $params['fields'] = static::$mediaFields;
        $mediaResult = [];
        $multiCurlImoveis = [];
        $mhImoveis = curl_multi_init();
        $token = '';
        $host = '';

        $tokens = static::getTokens();

        if ($tokens) {
            $token = $tokens['token'];
            $host = $tokens['host'];
        }
        
        foreach ($imoveis as $imovel) {
            $url = "https://$host-rest.vistahost.com.br/imoveis/detalhes?key=$token&imovel=".$imovel->Codigo.'&pesquisa='.json_encode($params);
            $indice = $imovel->Codigo;
            $multiCurlImoveis[$indice] = curl_init($url);
            curl_setopt($multiCurlImoveis[$indice], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($multiCurlImoveis[$indice], CURLOPT_HTTPHEADER, array('Accept: application/json'));
            curl_multi_add_handle($mhImoveis, $multiCurlImoveis[$indice]);
        }
        $index = null;
        do {
            curl_multi_exec($mhImoveis, $index);
        } while($index > 0);
        foreach($multiCurlImoveis as $codigo => $mediaImovel) {
            $response = json_decode(curl_multi_getcontent($mediaImovel));
            $mediaResult[$codigo] = $response;

            curl_multi_remove_handle($mhImoveis, $mediaImovel);
        }
        return $mediaResult;
    }

    protected static function sanitizeValue($value) {
        if ($value == 0) return "";
        return strval($value);
    }

    protected static function sanitizeBoolean($value) {
        return $value == 'Sim' ? $value : 'Não';
    }

    protected static function sanitizeBooleanToNumber($value) {
        if ($value == '' || $value == 'Nao' || $value == 0) return "";
        return "1";
    }

    protected static function sanitizeMoney($value) {
        if ($value == '' || $value == 0) return "";
        return number_format($value, 2, ',', '.');
    }

    protected static function sanitizeVagas($value) {
        if ($value > 4) $value = 5;
        if ($value > 1) return "$value-vagas";
        return "$value-vaga";
    }

    protected static function sanitizeDormitorios($value) {
        if ($value > 3) return "$value-dormitorios-suites";
        if ($value > 4) return "5-dormitorios-suites";
        if ($value > 1) return "$value-dormitorios";
        return "$value-dormitorio";
    }

    protected static function sanitizeTipo($value) {
        if (in_array($value, ['Apartamento', 'Apartamento com Varanda'])) return "Apartamento";
        if (in_array($value, ['Casa em Condomínio', 'Casa em Rua Fechada'])) return "Casa em Condomínio";
        if ($value == 'Cobertura') return "Apartamento Cobertura";
        if ($value == 'Duplex') return "Apartamento Duplex";
        if ($value == 'Garden') return "Apartamento Garden";
        if ($value == 'Sobrado') return "Sobrado";
        return 'Casa';
    }

    protected static function sanitizePerfil($value) {
        return 'Residencial';
    }

    protected static function sanitizeValueAluguelRange($value) {
        if ($value < 1000) return "aluguel-ate-r1000";
        if ($value >= 1000 && $value < 2000) return "aluguel-r1000-ate-r2000";
        if ($value >= 2000 && $value < 3000) return "aluguel-r2000-ate-r3000";
        if ($value >= 3000 && $value < 5000) return "aluguel-r3000-ate-r5000";
        if ($value >= 5000 && $value < 8000) return "aluguel-r5000-ate-r8000";
        if ($value >= 8000 && $value < 10000) return "aluguel-r8000-ate-r10000";
        if ($value >= 10000 && $value < 20000) return "aluguel-r10-mil-ate-r20-mil";
        return "aluguel-r20-mil";
    }

    protected static function sanitizeValueVendaRange($value) {
        if ($value >= 100000 && $value < 200000) return "r100mil-ate-r200mil";
        if ($value >= 200000 && $value < 300000) return "r200mil-ate-r300mil";
        if ($value >= 300000 && $value < 500000) return "r300mil-ate-r500mil";
        if ($value >= 500000 && $value < 750000) return "r500mil-ate-r750mil";
        if ($value >= 750000 && $value < 1000000) return "r750mil-ate-r1milhao";
        if ($value >= 1000000 && $value < 2000000) return "r1milhao-ate-r2milhoes";
        if ($value >= 3000000 && $value < 5000000) return "r3milhoes-ate-r5milhoes";
        if ($value >= 5000000 && $value < 7500000) return "r5milhoes-ate-r7-5milhoes";
        if ($value >= 7500000 && $value < 10000000) return "r7-5milhoes-ate-r10milhoes";
        return "r10milhoes";
    }

    public static function fetchImoveisVista($pagina = 1, $start = null) {
        if ($start == null) $start = microtime(true);
        $dbPage = static::getCurrentPage();
        if ($dbPage?->page > $pagina) {
            static::fetchImoveisVista($pagina + 1, $start);
            exit;
        }
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'app/utils/removeUploadDirFromImage.php');
        require_once(ABSPATH . 'app/utils/checkExclusive.php');
        $response = static::fetchImoveis($pagina);
        $user = get_user_meta(get_current_user_id(), 'parent', true) ? get_user_meta(get_current_user_id(), 'parent', true) : get_current_user_id();
        $jsonPath = get_site_url().'/app/json';
        $estados = (array) json_decode(@file_get_contents("$jsonPath/estados-siglas.json"));
        $municipios = (array) json_decode(@file_get_contents("$jsonPath/municipios.json"));
        $wordpress_upload_dir = wp_upload_dir();

        if ($response->status == 200) {
            $count = 0;
            $mediaResponse = static::fetchMediaImoveis($response->payload->data);
            $check = static::getPostsAndMetaByCodigo(array_keys($mediaResponse), $user);

            foreach ($response->payload->data as $imovel) {
                $count += 1;
                if ($imovel->Status == 'Venda e Aluguel') continue;
                if ($imovel->Numero == '' || $imovel->Numero == null) continue;
                if ($dbPage?->imovel > $count) continue;
                self::updateStatus(1, ['page' => $pagina, 'imovel' => $count]);

                $cidade = '';
                $logradouro = '';
                $estado = '';
                $bairro = '';

                try {
                    $cep = $imovel->CEP;
                    $endereco = @file_get_contents("https://viacep.com.br/ws/$cep/json/", false);
                    if ($endereco == false) continue;
                    $endereco = json_decode($endereco);
                    $logradouro = $endereco->logradouro;
                    $bairro = $endereco->bairro;
                    $estado = $estados[$endereco->uf];

                    $cidades = array_filter($municipios, function ($key) use ($estado, $endereco) {
                        return $key->state_id == $estado && $key->name == $endereco->localidade;
                    });

                    if (count($cidades) > 0) {
                        $cidade = $cidades[array_keys($cidades)[0]]->id;
                    } else {
                        continue;
                    }
                } catch (Exception $e) {
                    continue;
                }

                $checkExclusividade = checkExclusive($imovel->CEP, $imovel->Numero);
                if ($checkExclusividade) continue;

                if (isset($check[$imovel->Codigo])) {
                    $id = $check[$imovel->Codigo];
                    wp_update_post(array(
                        'ID'            => $id,
                        'post_title'    => $imovel->TituloSite,
                    ));
                } else {
                    $id = wp_insert_post([
                        'post_author'   => $user,
                        'post_type'     => 'imoveis',
                        'post_status'   => 'draft'
                    ]);
                    update_post_meta($id, 'publish_type', 'imovel');
                    update_post_meta($id, 'list_authors', base::parent_users());
                }

                $imovel->Fotos = (array) $mediaResponse[$imovel->Codigo]->Foto;
                $videos = (array) $mediaResponse[$imovel->Codigo]->Video;
                $imovel->Video = count($videos) > 0 ? $videos[array_keys($videos)[0]]->Video : '';

                $valorFaixaAluguel = static::sanitizeValueAluguelRange($imovel->ValorLocacao);
                $valorFaixaVenda = static::sanitizeValueVendaRange($imovel->ValorVenda);

                $dbImovel = [
                    'post' => $id,
                    'localizacao_cep' => $imovel->CEP,
                    'localizacao_endereco' => $logradouro,
                    'localizacao_numero' => $imovel->Numero,
                    'localizacao_bairro' => $bairro,
                    'localizacao_estado' => $estado,
                    'localizacao_cidade' => $cidade,
                    'oferta' => $imovel->Status,
                    'tipo_imovel' => static::sanitizeTipo($imovel->Categoria),
                    'info_titulo' => $imovel->TituloSite,
                    'perfil_do_imovel' => static::sanitizePerfil($imovel->Categoria),
                    'codigo' => $imovel->Codigo,
                    'ano_da_construcao' => strval($imovel->AnoConstrucao),
                    'andar' => static::sanitizeValue($imovel->AptosAndar),
                    'tem_mobilia' => static::sanitizeBoolean($imovel->Mobiliado),
                    'info_descricao' => static::sanitizeValue($imovel->DescricaoWeb),
                    'quartos' => static::sanitizeDormitorios($imovel->Dormitorios),
                    'suites' => static::sanitizeValue($imovel->Suites),
                    'banheiros' => static::sanitizeValue($imovel->TotalBanheiros),
                    'vagas' => static::sanitizeVagas($imovel->Vagas),
                    'garagem_coberta' => $imovel->VagasCobertas > 0 ? 'Sim' : 'Não',
                    'sala_de_tv' => static::sanitizeBooleanToNumber($imovel->SalaTV),
                    'sala_de_jantar' => static::sanitizeBooleanToNumber($imovel->SalaJantar),
                    'sala_de_estar' => static::sanitizeBooleanToNumber($imovel->SalaEstar),
                    'lavabo' => static::sanitizeBooleanToNumber($imovel->Lavabo),
                    'area_de_servico' => static::sanitizeBooleanToNumber($imovel->AreaServico),
                    'cozinha' => static::sanitizeBooleanToNumber($imovel->Cozinha),
                    'closet' => static::sanitizeValue($imovel->Closet),
                    'escritorio' => static::sanitizeBooleanToNumber($imovel->Escritorio),
                    'dependencia_p_empregada' => static::sanitizeBooleanToNumber($imovel->DependenciaDeEmpregada),
                    'copa' => static::sanitizeBooleanToNumber($imovel->Copa),
                    'area_construida' => static::sanitizeValue($imovel->AreaConstruida),
                    'area_privativa' => static::sanitizeValue($imovel->AreaPrivativa),
                    'area_total' => static::sanitizeValue($imovel->AreaTotal),
                    'faixa_preco' => $imovel->Status == 'Venda' ? $valorFaixaVenda : $valorFaixaAluguel,
                    'valor_do_imovel' => static::sanitizeMoney($imovel->Status == 'Venda' ? $imovel->ValorVenda : $imovel->ValorLocacao),
                    'valor_do_iptu' => static::sanitizeMoney($imovel->ValorIptu),
                    'valor_do_condominio' => static::sanitizeMoney($imovel->ValorCondominio),
                    'aceita_permuta' => static::sanitizeBoolean($imovel->AceitaPermuta),
                    'aceita_financiamento' => static::sanitizeBoolean($imovel->AceitaFinanciamento),
                    'video' => $imovel->Video,
                ];

                foreach ($dbImovel as $key => $value) {
                    $term = get_term_by('slug', $value, $key);
                    if ($term) {  
                        wp_set_object_terms($id, $term->term_id, $term->taxonomy); 
                    } else {
                        update_field($key, $value, $id);
                    }
                }

                $gallery = get_post_meta($id, 'imovel_images', true);
                $gallery = empty($gallery) ? [] : $gallery;

                foreach($gallery as $key => $galeries) {
                    foreach ($galeries as $attachment) {
                        wp_delete_attachment($attachment, true);
                    }
                }
                $gallery = [];

                $fotos = [];
                foreach ($imovel->Fotos as $foto) {
                    array_push($fotos, $foto->Foto);
                }

                $fotos = static::multi_download($fotos, $wordpress_upload_dir['path']);

                foreach ($fotos as $key => $foto) {
            
                    $new_file_path = $foto;
                    $post_title = basename($new_file_path);
                    $new_file_mime = mime_content_type($new_file_path);

                    $upload_id = wp_insert_attachment( array(
                        'guid'           => $new_file_path,
                        'post_mime_type' => $new_file_mime,
                        'post_title'     => preg_replace( '/\.[^.]+$/', '', $post_title),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    ), $new_file_path, $id);

                    wp_update_attachment_metadata($upload_id, wp_generate_attachment_metadata($upload_id, $new_file_path));

                    if($key == 0) {
                        $gallery['featured_image'] = [];
                        $gallery['featured_image'][] = $upload_id;
                    } else {
                        $gallery['galeria_interior'][] = $upload_id;
                    }
    
                    update_post_meta($id, 'imovel_images', $gallery);
                }

                foreach($gallery as $key => $image) {
                    if($key == 'featured_image') {
                        update_field($key, $image[0], $id);
                    } else {
                        update_field($key, $image, $id);
                    }
                }
                self::updateStatus(0, ['page' => $pagina, 'imovel' => $count]);
            }
        }
        if (number_format(microtime(true) - $start, 2) > static::$timeoutProtection) {
            self::updateStatus(0, ['page' => $pagina, 'imovel' => $count]);
            exit;
        }
        if ($response->payload->pagina < $response->payload->paginas) {
            static::fetchImoveisVista($pagina + 1, $start);
            exit;
        }
        self::updateStatus(0, null);
    }
}