<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Controller para enviar mensagens para gerência via WhatsApp
 */
class Controller_Lead_Whatsapp extends Controller_Lead_Base {

    /**
     * Método para processar o envio de mensagem para o grupo de WhatsApp da gerência
     */
    public function action_index() {
        // Verifica se é uma requisição POST
        if ($this->request->method() !== Request::POST) {
            $this->response->status(405); // Method Not Allowed
            $this->response->body(json_encode(['error' => 'Método não permitido']));
            return;
        }

        // ┌──────────────────────────────────────────────────────────────────────────────┐
        // │ $this->request->post()                                                       │
        // └──────────────────────────────────────────────────────────────────────────────┘
        // array (3) [
        //     'segmento_id' => string (1) "1"
        //     'produto_id' => string (7) "1650182"
        //     'vendedor_id' => string (2) "84"
        // ]

        //s( $this->request->post() );
        //die();

        // Obtém os dados do POST
        $produto  = $this->request->post('produto_id');
        $vendedor = $this->request->post('vendedor_id');
        $segmento = $this->request->post('segmento_id');

        $query = [
            'ajax' => true
        ];

        $json = Request::factory( '/lead/product/'.$produto. '/'. $segmento )
            ->method('GET')
            ->query($query)
            ->execute()
            ->body();

        $product = json_decode( $json, true);

        //s($product);
        //die();

        // ┌──────────────────────────────────────────────────────────────────────────────┐
        // │ $data                                                                        │
        // └──────────────────────────────────────────────────────────────────────────────┘
        // array (30) [
        //     'produtoPOID' => string (7) "1650182"
        //     'produtoNome' => string (52) "Overloque 4 Fios Ponto Cadeia com Motor Direct Drive"
        //     'produtoModelo' => string (11) "B9500-13/10"
        //     'produtoPeso' => string (7) "38.0000"
        //     'produtoMarca' => string (4) "ZOJE"
        //     'produtoCodigoBarra' => string (0) ""
        //     'produtoEmbalagem' => string (13) "Sem embalagem"
        //     'produtoNCM' => string (10) "8452.29.29"
        //     'ecommerce_name' => string (22) "Overloque Ponto Cadeia"
        //     'ecommerce_descriptions' => null
        //     'ecommerce_resume' => string (22) "com Motor Direct Drive"
        //     'ecommerce_complement' => null
        //     'ecommerce_dealer' => UTF-8 string (369) "A Máquina de Costura Overlock Ponto Cadeia Zoje B9500-13/10 é a solução perfeita para confecções que buscam acabamentos profissionais em tecidos leves e médios, como malhas, lã e roupas fitness. Com tecnologia avançada e design ergonômico, esta máquina combina velocidade, eficiência e precisão, sendo ideal para quem deseja otimizar a produção com costuras impecáveis."
        //     'ecommerce_short' => null
        //     'ecommerce_document' => string (95) "https://rolemak-manuais.s3.sa-east-1.amazonaws.com/overloque/b9500/tecnico-serie-B9500-v2.1.pdf"
        //     'ecommerce_video' => null
        //     'machine_functions' => string (12) "["17", "22"]"
        //     'machine_aplications' => null
        //     'product_similar' => null
        //     'product_parts' => null
        //     'categoriaNome' => string (9) "Overloque"
        //     'categoriaTitulo' => UTF-8 string (60) "Overloque Industrial Zoje. Compre Sua Máquina Overloque Zoje"
        //     'segmentoNome' => UTF-8 string (8) "Máquinas"
        //     'segmentoTitulo' => UTF-8 string (29) "Máquina de Costura Industrial"
        //     'features' => array (14) [
        //         0 => array (6) [
        //             'id' => string (5) "23437"
        //             'feature_id' => string (2) "12"
        //             'feature_name' => string (18) "Altura do Calcador"
        //             'feature_description' => UTF-8 string (155) "É a distância entre a chapa e a base do calcador ( levantado ), essa medida é usada para saber se o material para costura ( tecido ) pode ser usado ou não."
        //             'attribute_id' => string (3) "216"
        //             'attribute_name' => string (1) "6"
        //         ]
        //         1 => array (6) [
        //             'id' => string (5) "23438"
        //             'feature_id' => string (2) "43"
        //             'feature_name' => UTF-8 string (14) "Número de Fios"
        //             'feature_description' => UTF-8 string (53) "A quantidade de linha que a máquina usa para costurar"
        //             'attribute_id' => string (3) "203"
        //             'attribute_name' => string (6) "4 Fios"
        //         ]
        //         2 => array (6) [
        //             'id' => string (5) "23439"
        //             'feature_id' => string (2) "46"
        //             'feature_name' => UTF-8 string (17) "Número de Agulhas"
        //             'feature_description' => UTF-8 string (43) "Quantidade de agulhas que a máquina utiliza"
        //             'attribute_id' => string (3) "197"
        //             'attribute_name' => string (9) "2 Agulhas"
        //         ]
        //         3 => array (6) [
        //             'id' => string (5) "23440"
        //             'feature_id' => string (2) "49"
        //             'feature_name' => string (10) "Velocidade"
        //             'feature_description' => UTF-8 string (32) "Velocidade de costura da máquina"
        //             'attribute_id' => string (2) "18"
        //             'attribute_name' => string (5) "7.000"
        //         ]
        //         4 => array (6) [
        //             'id' => string (5) "23441"
        //             'feature_id' => string (4) "5225"
        //             'feature_name' => UTF-8 string (12) "Lubrificação"
        //             'feature_description' => UTF-8 string (41) "Sistema para lubrificar as peças internas"
        //             'attribute_id' => string (2) "19"
        //             'attribute_name' => UTF-8 string (10) "Automática"
        //         ]
        //         5 => array (6) [
        //             'id' => string (5) "23442"
        //             'feature_id' => string (4) "5228"
        //             'feature_name' => string (5) "Motor"
        //             'feature_description' => string (0) ""
        //             'attribute_id' => string (2) "23"
        //             'attribute_name' => string (12) "Direct Drive"
        //         ]
        //         6 => array (6) [
        //             'id' => string (5) "23443"
        //             'feature_id' => string (4) "5241"
        //             'feature_name' => UTF-8 string (17) "Número de Loopers"
        //             'feature_description' => string (0) ""
        //             'attribute_id' => string (2) "62"
        //             'attribute_name' => string (1) "2"
        //         ]
        //         7 => array (6) [
        //             'id' => string (5) "23444"
        //             'feature_id' => string (4) "5308"
        //             'feature_name' => UTF-8 string (6) "Tensão"
        //             'feature_description' => UTF-8 string (27) "Tensão de operação do motor"
        //             'attribute_id' => string (3) "181"
        //             'attribute_name' => string (3) "220"
        //         ]
        //         8 => array (6) [
        //             'id' => string (5) "23445"
        //             'feature_id' => string (4) "5309"
        //             'feature_name' => UTF-8 string (17) "Potência do Motor"
        //             'feature_description' => string (0) ""
        //             'attribute_id' => string (3) "105"
        //             'attribute_name' => string (3) "550"
        //         ]
        //         9 => array (6) [
        //             'id' => string (5) "23446"
        //             'feature_id' => string (4) "5310"
        //             'feature_name' => string (14) "Tipo de Agulha"
        //             'feature_description' => string (0) ""
        //             'attribute_id' => string (3) "152"
        //             'attribute_name' => string (5) "DCx27"
        //         ]
        //         10 => array (6) [
        //             'id' => string (5) "33987"
        //             'feature_id' => string (4) "5249"
        //             'feature_name' => string (13) "Tipo de Ponto"
        //             'feature_description' => string (0) ""
        //             'attribute_id' => string (2) "87"
        //             'attribute_name' => string (8) "Corrente"
        //         ]
        //         11 => array (6) [
        //             'id' => string (5) "33988"
        //             'feature_id' => string (4) "5401"
        //             'feature_name' => string (11) "diferencial"
        //             'feature_description' => string (0) ""
        //             'attribute_id' => string (3) "930"
        //             'attribute_name' => string (6) "4 fios"
        //         ]
        //         12 => array (6) [
        //             'id' => string (5) "34152"
        //             'feature_id' => string (4) "5399"
        //             'feature_name' => string (6) "Painel"
        //             'feature_description' => string (0) ""
        //             'attribute_id' => string (3) "879"
        //             'attribute_name' => string (5) "Tecla"
        //         ]
        //         13 => array (6) [
        //             'id' => string (5) "34156"
        //             'feature_id' => string (4) "5316"
        //             'feature_name' => string (8) "Material"
        //             'feature_description' => UTF-8 string (62) "Expessura de material, tecido ou outros que a máquina trabalha"
        //             'attribute_id' => string (3) "108"
        //             'attribute_name' => UTF-8 string (12) "Leve e Médio"
        //         ]
        //     ]
        //     'functions' => array (2) [
        //         0 => array (4) [
        //             'id' => string (2) "17"
        //             'seo' => string (12) "lubrificacao"
        //             'name' => UTF-8 string (12) "Lubrificação"
        //             'type' => string (1) "1"
        //         ]
        //         1 => array (4) [
        //             'id' => string (2) "22"
        //             'seo' => string (16) "parada-de-agulha"
        //             'name' => string (16) "Parada de Agulha"
        //             'type' => string (1) "1"
        //         ]
        //     ]
        //     'functionsByType' => array (1) [
        //         1 => array (2) [
        //             0 => array (4) [
        //                 'id' => string (2) "17"
        //                 'seo' => string (12) "lubrificacao"
        //                 'name' => UTF-8 string (12) "Lubrificação"
        //                 'type' => string (1) "1"
        //             ]
        //             1 => array (4) [
        //                 'id' => string (2) "22"
        //                 'seo' => string (16) "parada-de-agulha"
        //                 'name' => string (16) "Parada de Agulha"
        //                 'type' => string (1) "1"
        //             ]
        //         ]
        //     ]
        //     'applications' => array (0) []
        //     'mfeatures' => array (0) []
        //     'segmentoPOID' => string (1) "1"
        // ]

        try {

            // Gera a URL da imagem do produto
            $image_url = "https://img.rolemak.com.br/id/h480/{$product['produtoPOID']}.jpg?version=9.02";

            // Formata o texto do produto
            $product_text  = "🧵 *".$product['produtoModelo']."*" . " *" . $product['produtoMarca']."*" . "\n";
            // Usa ProdutoNome se Ecommerce.Nome estiver vazio
            $nome           = !empty($product['ecommerce_name']) ? $product['ecommerce_name'] : $product['produtoNome'];
            $descricao      = !empty($product['ecommerce_descriptions']) ? $product['ecommerce_descriptions'] : '';
            $resumo         = !empty($product['ecommerce_resume']) ? $product['ecommerce_resume'] : '';
            $complemento    = !empty($product['ecommerce_complement']) ? $product['ecommerce_complement'] : '';
            $product_text  .= $nome." ".$resumo." ".$descricao." ".$complemento."\n\n";

            $price = $product['price']['precoVistaFormatado'];

            //Verifica se tem estoque disponível
            if (!empty($product['stock']['estoqueTotal']) && $product['stock']['estoqueTotal'] > 0) {
                $product_text  .= "💰 *R$ " . $price . "*\n";
                $product_text  .= "_Estoque disponível_\n\n";
            } else {
                $product_text  .= "_Produto indisponível_\n\n";
            }

            // Acrescenta as funções do produto
            if (!empty($product['functions'])) {
                $product_text .= "▪️️ *Funções:* \n";
                foreach ($product['functions'] as $func) {
                    $product_text .= " - " . $func['name'] . "\n";
                }
                $product_text .= "\n";
            }

            // Acrescenta as funções do produto
            if (!empty($product['applications'])) {
                $product_text .= "▪️️ *Ideal para produzir:* \n";
                foreach ($product['applications'] as $func) {
                    $product_text .= " - " . $func['name'] . "\n";
                }
                $product_text .= "\n";
            }

            // https://www.youtube.com/watch?v=DqzxzCtXsaQ
            if (!empty($product['ecommerce_video'])) {
                $product_text .= "📹 *Video:*\nhttps://www.youtube.com/watch?v=" . $product['ecommerce_video']. "\n\n";
            }

            if (!empty($product['ecommerce_document'])) {
                $product_text .= "📁 *Manual:*\n" . $product['ecommerce_document'];
            }

            $details = self::user();

            $data = [
                'to'      => $details['to'],
                'message' => $product_text,
                'image'   => $image_url
            ];

            // Envia a requisição para a API
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $details['url'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json'
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                throw new Exception('Erro ao enviar mensagem: ' . $err);
            }

            // Retorna sucesso
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['success' => true]));

        } catch (Exception $e) {

            // Retorna erro
            $this->response->status(500);
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['error' => $e->getMessage()]));
        }
    }

    private function user()
    {

        $array = [];

        $sql = sprintf("SELECT id, nextel FROM mak.rolemak_users WHERE id = %s LIMIT 1", $_SESSION['MM_Userid']);

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        //s($response);
        //die();

        $user = !empty($response) ? $response[0] : null;

        $userId = $user['id'] ?? null;

        // Verifica se o usuário existe e tem número de WhatsApp cadastrado
        if ($userId !== null && !empty($user['nextel'])) {
            // Adiciona o prefixo 55 se o número não começar com ele
            $array['to'] = (substr($user['nextel'], 0, 2) !== '55') ? '55' . $user['nextel'] : $user['nextel'];

             // Configuração para envio da mensagem via API WhatsApp
            $array['url'] = $_ENV['dev_whatsapp']. '/api/sessions/5511964890813/send';

        } else {
            // Define o número padrão
            $array['to'] = '120363407044101025';

            // Configuração para envio da mensagem via API WhatsApp
            $array['url'] = $_ENV['dev_whatsapp']. '/api/sessions/5511964890813/send/group';
        }

        return $array;
    }



}
