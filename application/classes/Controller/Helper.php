<?php
class Controller_Helper extends Controller_Website {

    public function before()
    {
        parent::before();
    }

    public function action_invoice()
    {
        $string = 'Ajuda Invoice';

        return $this->response->body( json_encode($string) );
    }

    public function action_transporter()
    {
        $get = $this->request->query();

        $string = null;

        $sql= sprintf("	SELECT c.nome as cliente, t.nome as transportadora
                            FROM mak.clientes as c
                            LEFT JOIN mak.transportadora as t on( t.id = c.idtr )
                            WHERE c.id= %s
                            LIMIT 1", $get['clienteID']);

        $result =  $this->action_connect_SELECT($sql);

        if ( isset( $result[0]['transportadora'] ) )
            $string ='<p class="">Preferência é <br> <strong class="font-weight-bold color-primary">'.$result[0]['transportadora'].' </strong></p>'; 

        $sql= sprintf("	SELECT h.id as pedido, t.nome as transportadora
                            FROM mak.hoje as h
                            LEFT JOIN mak.transportadora as t on( t.id = h.idtr )
                            WHERE h.idcli= %s
                            ORDER BY h.id desc
                            LIMIT 1", $get['clienteID']);

        $result =  $this->action_connect_SELECT($sql); 

        if ( isset( $result[0]['transportadora'] ) )
            $string.='<p class="">Último pedido foi <br> <strong class="font-weight-bold color-primary">'.$result[0]['transportadora'].' </strong></p>';

        $sql= sprintf("	SELECT h.id as pedido, t.nome as transportadora
                            FROM mak.hoje as h
                            LEFT JOIN mak.transportadora as t on( t.id = h.idtr )
                            WHERE h.idcli= %s
                            ORDER BY h.id desc
                            LIMIT 10", $get['clienteID']);

        $result =  $this->action_connect_SELECT($sql);

        if ( isset( $result[0]['transportadora'] ) )
            $string.='<p class="">Mais útilizada foi <br> <strong class="font-weight-bold color-primary">'.$result[0]['transportadora'].' </strong></p>';

        //s($get, $result);
        //die();

        return $this->response->body( json_encode($string) );
    }

    public function action_terms()
    {
        $get = $this->request->query();

        $string = null;

        $sql= sprintf("	SELECT h.id as pedido, t.terms as nome
                            FROM mak.hoje as h
                            LEFT JOIN mak.terms as t on( t.id = h.prazo )
                            WHERE h.idcli= %s
                            ORDER BY h.id desc
                            LIMIT 1", $get['clienteID']);

        $result =  $this->action_connect_SELECT($sql);

        if ( isset( $result[0]['nome'] ) )
            $string.='<p class="">Último pedido foi <br> <strong class="font-weight-bold color-primary">'.$result[0]['nome'].' </strong></p>';

        //s($get, $result);
        //die();

        return $this->response->body( json_encode($string) );
    }

    public function action_payment()
    {
        $get = $this->request->query();

        $string = null;

        $sql= sprintf("	SELECT h.id as pedido, pt.payment_type as nome
                            FROM mak.hoje as h
                            LEFT JOIN mak.payment_types as pt on( pt.id_payment_type = h.pg )
                            WHERE h.idcli= %s
                            ORDER BY h.id desc
                            LIMIT 1", $get['clienteID']);

        $result =  $this->action_connect_SELECT($sql);

        if ( isset( $result[0]['nome'] ) )
            $string.='<p class="">Último pedido foi <br> <strong class="font-weight-bold color-primary">'.$result[0]['nome'].' </strong></p>';

        //s($get, $result);
        //die();

        return $this->response->body( json_encode($string) );
    }

    public function action_unity()
    {
        $get = $this->request->query();

        $string = null;

        $sql= sprintf("	SELECT h.id as pedido, e.Fantasia as nome
                            FROM mak.hoje as h
                            LEFT JOIN mak.Emitentes as e on( e.EmitentePOID = h.EmissorPOID )
                            WHERE h.idcli= %s
                            ORDER BY h.id desc
                            LIMIT 1", $get['clienteID']);

        $result =  $this->action_connect_SELECT($sql);

        if ( isset( $result[0]['nome'] ) )
            $string.='<p class="">Último pedido foi <br> <strong class="font-weight-bold color-primary">'.$result[0]['nome'].' </strong></p>';

        //s($get, $result);
        //die();

        return $this->response->body( json_encode($string) );
    }


}