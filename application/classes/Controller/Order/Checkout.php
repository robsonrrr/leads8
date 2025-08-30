<?php

class Controller_Order_Checkout extends Controller_Order_Base {

    public function action_index()
    {
        $leadID    = $this->request->param('id');
        $segmentID = $this->request->param('segment');

        if ( ! $leadID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $leadID</h3>'));

        if ( $_SESSION['MM_Userid'] == 84 )
        {
            //d($response);

            /* $recalculate = Request::factory( '/lead/recalculate/'.$leadID.'/'.$segmentID )
                ->method('GET')
                ->execute()
                ->body();

            $response['recalculate'] = $recalculate; */

            //s($response);
        }

        if ( ! isset($_SESSION['check']) )
        {
            $_SESSION['check'] = true;
        }

        $data = array( 
            'check'   => $_SESSION['check'] ,
            'segment' => $segmentID
        );

        $json = Request::factory( '/lead/build/'.$leadID )
            ->method('GET')
            ->query($data)
            ->execute()
            ->body();

        $response = json_decode($json,true);

        //d($response);

        $response['distance'] = self::distance( $response );

        if ( $_SESSION['MM_Userid'] == 84 )
        {
            // $response['distance'] = self::distance( $response );
            //s($response);
            //die();
        }

        $error = $this->request->query('error');

        if ( isset($error))
        {
            $response['error'] = $this->request->query('error');
            $response['error_'.$this->request->query('error')] = true;
        }

        //Slack
        $msg = ' Aviso -> *'.$_SESSION['MM_Nick'].'* está preparando um Pedido número Lead <https://office.vallery.com.br/leads/leads5/'.$leadID.'/'.$segmentID.'|'.$leadID.'>';
        //$log = $this->logSlack( $msg );
        
        // $text = $_SESSION['MM_Nick'].' está preparando um Pedido com o Lead nº '.$leadID;
        // $link = 'https://office.vallery.com.br/leads/leads5/'.$leadID.'/'.$segmentID;

        // $this->notify( $text, $link );

        //$to      = '5511995206263';
        //$from    = '14155238886';
        //$msg2    = ' Aviso -> *'.$_SESSION['MM_Nick'].'* está preparando um Pedido, número Lead '.$leadID.'. Essa mensagem é automática não necessário responder.';

        //$twilio = $this->logTwilio( $to, $from, $msg2);

        //$profile = View::factory('profiler/stats');
        //s($response);

        if($_SESSION["MM_Userid"] == '225')
        {
            //s($response);
            //die();
        }


        return $this->render( 'order/checkout', $response );
    }

    public function distance( $array )
    {
        $json = Request::factory( 'http://office.vallery.com.br/transport/distance/index/'.$array['Lead']['unidadeEmitentePOID'].'/'.$array['Lead']['clientePOID'] )
            ->method('GET')
            ->execute()
            ->body();

        //s($json);


        $sql= sprintf("SELECT * FROM `google_markers` WHERE `idcli` = '%s' ", $array['Lead']['clientePOID']);

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        if (count($response) > 0 )
        {
            foreach ( $response as $k => $v)
            {
                if ( $v['distance_from_0109'] > 0 )
                {
                    $response[$k]['distance_from_0109'] = round($v['distance_from_0109'] / 1000 , 0 ). ' km';
                }
                
                if ( $v['distance_from_0370'] > 0 )
                {
                    $response[$k]['distance_from_0370'] = round($v['distance_from_0370'] / 1000 , 0 ). ' km';
                }
                
                if ( $v['distance_from_0532'] > 0 )
                {
                    $response[$k]['distance_from_0532'] = round($v['distance_from_0532'] / 1000 , 0 ). ' km';
                }
                
                if ( $v['distance_from_0702'] > 0 )
                {
                    $response[$k]['distance_from_0702'] = round($v['distance_from_0702'] / 1000 , 0 ). ' km';
                }
                
                if ( $v['distance_from_0885'] > 0 )
                {
                    $response[$k]['distance_from_0885'] = round($v['distance_from_0885'] / 1000 , 0 ). ' km';
                }
            }

            return $response[0];
        }

        return false;
    }

}