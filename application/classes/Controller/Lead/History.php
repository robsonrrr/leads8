<?php

class Controller_Lead_History extends Controller_Lead_Base {

    public function before()
    {
        parent::before();
    }

    public function action_index()
    {
        $leadPOID = $this->request->param('id');

        //s($leadPOID);

        if ( ! $leadPOID )
            die( s('<h3 align="center" style="margin-top:100px">Sem $leadPOID</h3>'));

        //d($solr);

        $where = null;

        $sql= sprintf(" SELECT * 
                        FROM history.jeditable
                        WHERE 1=1
                        %s
                        AND PrimaryRecordId = %s
                        ORDER BY id DESC
                        LIMIT 10", $where, $leadPOID );

        $query = DB::query(Database::SELECT, $sql);
        $response = $query->execute()->as_array();

        //$profile = View::factory('profiler/stats');
        //s($response);
        //die();

        $count = 0;

        foreach ( $response as $k => $v )
        {
            $count++;
            $response[$k]['count'] = $count;
        }

        $array['response'] = $response;
        
        //s($array);

        $template = $this->render( 'lead/history', $array );
    }

}