<?php

class Controller_Lead_Offer extends Controller_Lead_Base {
    
    public function before()
    {
        parent::before();
    }

    public function action_index()
    {
        try{
            // Obter parâmetros da requisição e dados do lead
            $params    = self::parameters();
            //s($params);
            
            $lead_data = self::lead( $params );
            // s($lead_data);
            // die();
            
            //In Controller Website
            $config = $this->config_mail( $lead_data );
            
            $device = self::check_device( $lead_data['Lead']['usuarioPOID'] );
            
            // Categorias
            $categories = self::categories( $params );
            //s($categories);

            // Mesclar dados do lead com os parâmetros da requisição
            $data = array_merge($lead_data, $params, $config, $categories, $device);
           
            $format = $this->request->query('format');
            
            if ( $format == 'json' )
            {
                return $this->response->body( json_encode($data) );
            }
            
            // s($data);
            // die();
            
            // Carregar e renderizar a view com os dados mesclados
            $view = 'offer/index';
            
            $send = $this->request->query('enviar');
            
            $template = $this->render($view, $data);
            
            if ( $send )
            {
               $offer = self::send_offer($template, $data);
               //s($offer);
            }
            
            return $template;
            
        } catch (Exception $e) {
                return 'Ocorreu um erro: ' . $e->getMessage();
        }       
    }
    
    public function categories( $params )
    {
        // Construindo a consulta SQL
        $sql = sprintf("
            SELECT 
                categories.id AS categoriaID,
                categories.c_seo AS categoriaSEO,
                categories.c_name AS categoriaNome,
                categories.c_name_full AS categoriaNomeCompleto,
                categories.c_order AS categoriaOrdem,
                categories.c_title AS categoriaTitulo,
                segments.id AS segmentoID,
                segments.s_seo AS segmentoSEO,
                segments.s_name AS segmentoNome,
                segments.s_title AS segmentoTitulo
            FROM 
                Catalogo.categories
            LEFT JOIN 
                Catalogo.segments ON (segments.id = categories.segment)
            WHERE 1=1
                AND categories.c_seo IS NOT NULL
                AND categories.c_order > 0
                AND categories.segment = %s
                -- AND categories.rating  = 1
            GROUP BY 
                categories.id
                ORDER BY 
                categories.c_order
        ", $this->segmentID);
    
        // Executando a consulta
        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();
        
        // Verifica se o índice 0 existe em $response e se 'categorias' existe em $get['params']
        if (isset($response[0]) && isset($params['params']['categoria_destaque'])) {
            // Itera sobre cada elemento em $response
            foreach ($response as $key => $value) {
                // Verifica se o ID da categoria atual é igual à categoria fornecida em $get['params']
                if ($value['categoriaID'] == $params['params']['categoria_destaque']) {
                    // Define 'categoriaSelecionado' como verdadeiro para a categoria atual
                    $response[$key]['categoriaSelecionado'] = true;
                }
            }
        }
        
        // s($response);
        // die();
    
        return [
            'categories' => $response ?? []
        ];
    }

    private function send_offer( $template, $data )
    {
         try{
             
             if ( empty($template) )
             {
                // s($data);
                throw new Exception("Template Vazio");  
             }
             
            $channel = $this->request->query('canal');
        
            switch ($channel) {
                case 'email':
                    return self::send_email($template, $data);
                    break;
                case 'whatsapp':
                    return self::send_whatsapp($template, $data);
                    break;
                default:
                    throw new Exception("Canal não especificado.");
            }
            
            // Registra o envio da oferta
            // if ($result === true) {
            //     //self::register($data);
            // }
            
         } catch (Exception $e) {
                return 'Ocorreu um erro: ' . $e->getMessage();
        } 
    }
    // Função para recuperar dados do lead
    private function register()
    {
        
    }
    
    // Função para recuperar dados do lead
    private function lead( $params )
    {
        // Faz uma solicitação GET para obter os dados do lead
        $json = Request::factory('/lead/build/'.$this->leadID)
            ->method('GET')
            ->query(['segment' => $this->segmentID, 'check' => 'true']) // Passa o ID do segmento como consulta
            ->execute()
            ->body();
            
        // Decodifica a resposta JSON
        $response = json_decode($json, true);
        //s($response);
        
        // Verifica se o índice 0 existe em $response e se 'categorias' existe em $get['params']
        if (isset($response['Lead']['Produtos'][0]) && isset($params['params']['produto_destaque'])) {
            // Itera sobre cada elemento em $response
            foreach ($response['Lead']['Produtos'] as $key => $value) {
                // Verifica se o ID da categoria atual é igual à categoria fornecida em $get['params']
                if ($value['produtoPOID'] == $params['params']['produto_destaque']) {
                    // Define 'categoriaSelecionado' como verdadeiro para a categoria atual
                    $response['Lead']['Produtos'][$key]['produtoDestaque'] = true;
                }
            }
        }
        
        // Verifica se o índice 0 existe em $response e se 'ofertas_destaque' existe em $get['params']
        if (isset($response['Lead']['Produtos'][0]) && isset($params['params']['oferta_destaque'])) {
            // Itera sobre cada elemento em $response
            foreach ($response['Lead']['Produtos'] as $key => $value) {
                // Verifica se o ID da produto atual é igual à categoria fornecida em $get['params']
                if ($value['produtoPOID'] == $params['params']['oferta_destaque']) {
                    // Define 'categoriaSelecionado' como verdadeiro para a categoria atual
                    $response['Lead']['Produtos'][$key]['ofertaDestaque'] = true;
                }
            }
        }
        
        //s($response['Lead']['Produtos']);
        // Separar o valor das variáveis de preço de produtos por vírgula para estilizar os valores de reais e centavos
        foreach ($response['Lead']['Produtos'] as $key => &$value)
        {
            
            // Remover produtos com preço zero
            if ($value['produtoValor'] == 0) {
                unset($response['Lead']['Produtos'][$key]);
                continue;
            }
            
            // Processar valores para segmento específico ou todos os outros casos
            if ($this->segmentID == 3) {
                // Apenas para segmentID 3, processar ambos os valores
                $value['produtoValorUnitarioComImpostosSeparado'] = explode(",", $value['produtoValorUnitarioComImpostos']);
                $value['produtoValorFormatadoSeparado'] = explode(",", $value['produtoValorFormatado']);
            } else {
                // Para outros segmentIDs, verificar e processar cada valor individualmente
                if (isset($value['produtoValorUnitario']) && $value['produtoValorUnitario'] > 0) {
                    $value['produtoValorUnitarioComImpostosSeparado'] = explode(",", $value['produtoValorUnitario']);
                }
                if (isset($value['produtoValorFormatado']) && $value['produtoValorFormatado'] > 0) {
                    $value['produtoValorFormatadoSeparado'] = explode(",", $value['produtoValorFormatado']);
                }
            }
        }
        
        // Reindexar o array após a remoção de itens
        $response['Lead']['Produtos'] = array_values($response['Lead']['Produtos']);
        
        // Ordenar produtos (se necessário)
        sort($response['Lead']['Produtos']);

        return $response;
    }
    
    // Função para obter parâmetros da requisição
    private function parameters()
    {
        // Obtém IDs do lead e segmento da URL
        $this->leadID    = $this->request->param('id');
        $this->segmentID = $this->request->param('segment');
        
        // Obtém parâmetros da consulta (GET)
        $parameters = $this->request->query();
        
        // Retorna um array contendo os IDs do lead e segmento, além dos parâmetros da consulta
        return [
            'leadID'    => $this->leadID,
            'segmentID' => $this->segmentID,
            'params'    => $parameters
        ];
    }
    
    public function send_whatsapp($template, $data)
    {
        
        // s($template, $data);
        // die();
        
        try {
            $body = $template->body();
            
            $dom = new DOMDocument();
            @$dom->loadHTML(mb_convert_encoding($body, 'HTML-ENTITIES', 'UTF-8'));
            
            // Cria um novo DOMXPath para consultar o documento
            $xpath = new DOMXPath($dom);
            
            // Procura por uma div com o ID 'email'
            $query = "//*[@id='email']";
            $elements = $xpath->query($query);
            
            if ($elements->length > 0)
            {
                // Encontrou a div
                $html = $dom->saveHTML($elements->item(0));
            }
            
            $url = 'http://aws-s3/offers/create/'.$data['Lead']['id'].'/pdf';
            
            $json = Request::factory( $url )
                ->method('POST')
                ->body(json_encode([ 'html' => $html ]))
                ->execute()
                ->body();
            $response = json_decode($json,true);
            
            if($response['success'] === false)
            {
                throw new Exception($response['message']);
            }
            
            $material_url = $response['url'];
            
            // Dados do cliente e mensagem
            $client = $data['Lead']['Cliente'];
    
            // Configurações do envio via WhatsApp
            $user  = $data['device']['path'];//'geral-ti';
            $phone = $this->request->query('whatsapp_cliente') ?? '11995206263'; //$client['clienteWhatsapp'] ??
            
            $url      = "https://office.vallery.com.br/zapserver/devices/{$user}/send";
            $url_auth = 'https://office.vallery.com.br/zapserver/auth';
    
            // Credenciais para autenticação
            $credentials = [
                "username" => "robomak",
                "password" => "uo6spe5ja9"
            ];
    
            // Obter token de autenticação
            $headers = ['Content-Type: application/json'];
            $token = json_decode(self::curl_request($url_auth, $headers, $credentials), true)['token'];
    
            // Preparar a mensagem a ser enviada
            $message = [
                'message'  => '', //sprintf("Olá %s", $client['clienteNome']),
                'to'       => self::format_number($phone),
                'type'     => 'document',
                'source'   => $material_url,
                'fileName' => (string) $data['Lead']['id'],
            ];
            
            // Enviar a mensagem via cURL com o token de autenticação
            $response = self::curl_request($url, $headers, $message, 'POST', $token);
            
            return $response;
        } catch (Exception $e) {
            // Lidar com exceções, como registrar em log e retornar uma mensagem de erro
            return 'Ocorreu um erro: ' . $e->getMessage();
        }
    }

    public function curl_request($url, $headers = [], $data = [], $method = 'POST', $token = null) 
    {
        // Se o método for GET e houver dados, adicione os parâmetros na URL
        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        // Adicionar o token de autenticação ao header, se fornecido
        if ($token !== null) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        
        //s($headers);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
    
    public function format_number($number)
    {
        $onlyNumbers = preg_replace('/\D/', '', $number);
        
        if (strlen($onlyNumbers) <= 11) {
            $onlyNumbers = "55" . $onlyNumbers;
        }
        return $onlyNumbers . "@c.us";
    }
    
    public function send_email($template, $data)
    {
        // use Pelago\Emogrifier\CssInliner;
        // use Pelago\Emogrifier\HtmlProcessor\CssToAttributeConverter;
        // use Pelago\Emogrifier\HtmlProcessor\HtmlPruner;

        $html = $template->body();
        // $css = ''; // Caso o CSS esteja no <head>, não é necessário passar CSS aqui
        
        // try {
        //     // Converte os estilos CSS para inline
        //     $cssInliner = Pelago\Emogrifier\CssInliner::fromHtml($html)->inlineCss($css);
        //     $domDocument = $cssInliner->getDomDocument();
        //     Pelago\Emogrifier\HtmlProcessor\HtmlPruner::fromDomDocument($domDocument)->removeElementsWithDisplayNone()
        //       ->removeRedundantClassesAfterCssInlined($cssInliner);
        //     $finalHtml = Pelago\Emogrifier\HtmlProcessor\CssToAttributeConverter::fromDomDocument($domDocument)
        //       ->convertCssToVisualAttributes()->render();
            
            
        // } catch (Exception $e) {
        //     echo "Ocorreu um erro durante a conversão: " . $e->getMessage();
        // }
        
        // Carrega o HTML no DOMDocument
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        
        // Cria um novo DOMXPath para consultar o documento
        $xpath = new DOMXPath($dom);
        
        // Procura por uma div com o ID 'email'
        $query = "//*[@id='email']";
        $elements = $xpath->query($query);
        
        if ($elements->length > 0)
        {
            // Encontrou a div
            $html = $dom->saveHTML($elements->item(0));
        } 

        // echo $html;
        // die();
            
        $fields["addAddress"][] = $this->request->query('email_cliente');
        
        if (isset($data['Lead']['Cliente']['Gerente']['UsuarioEmailInterno']))
        {
             $fields["addReplyTo"][] = $data['Lead']['Cliente']['Gerente']['UsuarioEmailInterno'];
             $fields["addBCC"][]     = $data['Lead']['Cliente']['Gerente']['UsuarioEmailInterno'];
        }
        
        $fields["Subject"] = utf8_decode($this->request->query('titulo_email'));
        $fields["SMTPDebug"] = 0;
        $fields["isHTML"] = true; // Define o formato do email para HTML
        $fields["Body"] = utf8_decode($html); // Usa o HTML com estilos inline
        $fields["AltBody"] = 'Rolemak';

        // Envia o email via webservice
        $email = Request::factory($_ENV['sendmail'])
            ->method(Request::POST)
            ->post($fields)
            ->execute()
            ->body();
            
        //s($email);

        return $email;
    }
    
    public function check_device( $id )
    {
        $where = sprintf(" AND user_id = %s ", $id);
        // AND status = 1
        
        $sql = sprintf("
            SELECT
              `bots`.*,
              `rolemak_users`.`nick` AS nome,
              `rolemak_users`.`email`,
              `rolemak_users`.`nextel` AS phone
            FROM
              `crm`.`bots`
            LEFT JOIN 
               `mak`.`rolemak_users` ON (`rolemak_users`.`id` = `bots`.`user_id`)
            WHERE
                1=1
                {$where}
                -- AND bots.status = 2
            ORDER BY
              `id` ASC
            LIMIT
              1
            OFFSET
              0
       ");
       
        $query = DB::query(Database::SELECT, $sql);
        $devices = $query->execute()->as_array();
        
        // s($devices);
        // die();
        
        // ┌──────────────────────────────────────────────────────────────────────────────┐
        // │ $devices                                                                     │
        // └──────────────────────────────────────────────────────────────────────────────┘
        // array (1) [
        //     0 => array (11) [
        //         'id' => string (1) "2"
        //         'name' => string (8) "ZAP_MOTO"
        //         'user_id' => string (2) "73"
        //         'path' => string (13) "moto-marciogg"
        //         'type' => string (1) "2"
        //         'status' => string (1) "1"
        //         'last_update' => string (19) "2022-09-12 20:27:53"
        //         'bot_id' => string (36) "94f0a53f-0918-4fef-b9e7-cb01bb4fdf08"
        //         'nome' => string (9) "Marcio GG"
        //         'email' => string (16) "marciogg@rolemak"
        //         'phone' => string (10) "1120900644"
        //     ]
        // ]
       
        $user = null;

        foreach($devices as $k => $v)
        {
            $user = $v;
        }
        
        return [ 'device' => $user ];
    }
   

}
