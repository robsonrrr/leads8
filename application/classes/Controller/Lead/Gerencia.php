<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Controller para enviar mensagens para gerência via WhatsApp
 */
class Controller_Lead_Gerencia extends Controller_Lead_Base {

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

        // Obtém os dados do POST
        $leadId = $this->request->post('leadId');
        $mensagem = $this->request->post('mensagem');

        // Validação básica
        if (empty($leadId) || empty($mensagem)) {
            $this->response->status(400); // Bad Request
            $this->response->body(json_encode(['error' => 'Dados incompletos']));
            return;
        }

        try {
            // Obtém informações do lead usando o lead build como no Discount controller
            $data = array(
                'segment' => $this->request->post('segmentoPOID', null)
            );

            $json = Request::factory('/lead/build/'.$leadId)
                ->method('GET')
                ->query($data)
                ->execute()
                ->body();

            $lead = json_decode($json, true);

            if (empty($lead['Lead'])) {
                throw new Exception('Lead não encontrado');
            }

            $clienteNome  = isset($lead['Lead']['Cliente']['clienteNome']) ? $lead['Lead']['Cliente']['clienteNome'] : 'Cliente não identificado';
            $clienteLocal = isset($lead['Lead']['Cliente']['clienteCidade']) ? $lead['Lead']['Cliente']['clienteCidade'] . '/' . $lead['Lead']['Cliente']['clienteEstado'] : 'Local não identificado';

            // Extrai informações do Total do lead
            $valorPedido = isset($lead['Lead']['Total']['pedido']) ? $lead['Lead']['Total']['pedido'] : '0,00';
            $quantidade  = isset($lead['Lead']['Total']['quantidade']) ? $lead['Lead']['Total']['quantidade'] : 0;
            $variedade   = isset($lead['Lead']['Total']['variedade']) ? $lead['Lead']['Total']['variedade'] : 0;
            $peso        = isset($lead['Lead']['Total']['peso']) ? $lead['Lead']['Total']['peso'] : 0;

            $emitente = isset($lead['Lead']['Emitente']['emitenteFantasia']) ? $lead['Lead']['Emitente']['emitenteFantasia'] : 'Emitente não identificado';

            // Formata a mensagem para envio
            $mensagemCompleta  = "📢 *Lead Nº:* " . $leadId . "\n\n";
            $mensagemCompleta .= $emitente . "\n";
            $mensagemCompleta .= $clienteNome ."\n";
            $mensagemCompleta .= $clienteLocal . "\n";
            $mensagemCompleta .= "*R$ " . $valorPedido . "*\n";
            $mensagemCompleta .= "*Qtd.:* " . $quantidade . " / " ."*Itens:* " . $variedade . "\n";
            $mensagemCompleta .= "*Peso:* " . $peso . "\n";

            $clienteId  = $lead['Lead']['clientePOID'];
            $order      = self::order( $clienteId );

            if ( $order['dias_desde_ultimo_pedido'] > 0)
            {
              $mensagemCompleta .= "*Dias sem compra:* " . $order['dias_desde_ultimo_pedido'] . "\n\n";
            }

            //$mensagemCompleta .= "*Usuário:* " . $_SESSION['MM_Nick'] . "\n";
            $mensagemCompleta .=  $_SERVER["HTTP_X_FORWARDED_PROTO"].'://'.$_SERVER["HTTP_X_FORWARDED_HOST"]. "/leads/leads4/" . $leadId . "/" . $this->request->post('segmentoPOID') . "\n\n";
            $mensagemCompleta .= "*Mensagem:*\n" . $mensagem;

            // Map user IDs to WhatsApp numbers
            $numberMap = array(
                197 => '120363420449115159',
                131 => '120363417422945167',
                89  => '120363419412217738',
                117 => '120363401301871225',
                218 => '120363403474026794',
                107 => '120363401853600404',
            );

            $userId = $_SESSION['MM_Userid'];

            // Update $to if user ID exists in the map
            if ($userId !== null && array_key_exists($userId, $numberMap)) {
                $to = $numberMap[$userId];
            } else {
                $to = '120363305636837379';
            }

            // Configuração para envio da mensagem via API WhatsApp
            $url = $_ENV['whatsapp']. '/api/sessions/5511964890813/send/group';

            $data = [
                'to'      => $to,
                'message' => $mensagemCompleta
            ];

            // Envia a requisição para a API
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
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

            // Registra o envio no log
            KO7::$log->add(Log::INFO, 'Mensagem enviada para gerência. Lead ID: :leadId', [
                ':leadId' => $leadId
            ]);

            // Update lead
            $result = self::update($leadId, $mensagem);

            // Retorna sucesso
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['success' => true]));

        } catch (Exception $e) {
            // Log do erro
            KO7::$log->add(Log::ERROR, 'Erro ao enviar mensagem para gerência: :error', [
                ':error' => $e->getMessage()
            ]);

            // Retorna erro
            $this->response->status(500);
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['error' => $e->getMessage()]));
        }
    }


    /**
     * Método para processa pegar último dia de compra
     */
    private function order ( $clienteId )
    {

        // Valida o ID do cliente
        if ($clienteId <= 0) {
            throw new InvalidArgumentException('ID do cliente deve ser um número positivo');
        }

        $sql= sprintf("
                SELECT
                    idcli as cliente_id,
                    MAX(hoje.data) as data_ultimo_pedido,
                    DATEDIFF(CURDATE(), MAX(hoje.data)) as dias_desde_ultimo_pedido
                FROM mak.hoje
                WHERE valor > 1500
                    AND hoje.idcli = %s
                    AND hoje.nop   = 27
                GROUP BY cliente_id
                ORDER BY hoje.id DESC
                LIMIT 1
        ", $clienteId );

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        //s($response);
        //die();

        return !empty($response) ? $response[0] : null;
    }


    /**
     * Método para processar o envio de mensagem para o grupo de WhatsApp da gerência
     */
    private function update($leadPOID, $message)
    {
        // s($leadPOID, $message);
        // die();

        try {

            $data = [
                "observacaoGerente" => $message
            ];

            $url = $_ENV["api_vallery_v1"] . '/Leads/' . $leadPOID;

            $json = Request::factory($url)
                ->method('PUT')
                ->post($data)
                ->execute()
                ->body();

            $result = json_decode($json, true);

            return $result;

        } catch (Exception $e) {
            throw new Exception('API request failed: ' . $e->getMessage());
        }
    }
}
