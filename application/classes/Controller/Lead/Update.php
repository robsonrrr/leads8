<?php
class Controller_Lead_Update extends Controller_Website {

    public function action_index()
    {
        $this->auto_render = FALSE;

        $table     = $this->request->param('id');
        $leadPOID  = $this->request->param('segment');
        $segmentID = $this->request->param('complement');
        $get       = $this->request->query();
        // s($leadPOID,$segmentID);
        // die();

        $data[$get['field']] =$get['value'];

        // s($get['field']);
        // die();
        
        if ( ! $table and !$leadPOID )
            die(s('Favor Informar $table e $leadPOID'));
            
        if ( isset( $data['valorComissao'] ))
        {
           // $data['valorComissao'] = str_replace( $get['value'] , 2, '.', '');
        }

        //s($table,$leadPOID,$get,$data);
        //die();

        $lead = self::update( $table, $leadPOID, $data );
        //self::log();
        //s($lead);
        //die();
        
        if ( isset( $get['field'] ) and 'valorFrete' == $get['field'] )
        {
            $url = sprintf('/lead/recalculate/%s/%s', $leadPOID, $segmentID);
        
            $json = Request::factory( $url )
                ->method('GET')
                //->query( array( 'price'=> true ))
                ->execute()
                ->body();
        }

        return $this->response->body(json_encode(true));
    }

    private function update( $table,$leadPOID, $data)
    {
        $url = $_ENV["api_vallery_v1"].'/'.$table.'/'.$leadPOID;

        $json = Request::factory( $url )
            ->method('PUT')
            ->post($data)
            ->execute()
            ->body();

        return json_decode($json,true);
    }
}