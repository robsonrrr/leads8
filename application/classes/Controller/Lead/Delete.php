<?php

class Controller_Lead_Delete extends Controller_Website {

    public function action_index()
    {
        $post['leadID'] =  $this->request->param('id') ;

        $products = self::delete_products_lead($post);
        $lead     = self::delete_lead($post);

        s($post, $lead, $products);
        
        //Slack
        $msg = ' Aviso -> *'.$_SESSION['MM_Nick'].'* deletou o Lead '.$post['leadID'];
        $log = $this->logSlack( $msg );

        die();

        $redirect  = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].'crm';
        return $this->redirect( $redirect, 302);
    }


    private function delete_lead($post)
    {
        $url = $_ENV["api_vallery_v1"].'/Leads/'.$post['leadID'];

        $json = Request::factory( $url )
            ->method('DELETE')
            ->execute()
            ->body();

        return json_decode($json,true);
    }



    private function delete_products_lead($post)
    {
        $url = $_ENV["api_vallery_v1"].'/Leads/'.$post['leadID'].'/Produtos';

        $json = Request::factory( $url )
            ->method('DELETE')
            ->execute()
            ->body();

        return json_decode($json,true);
    }


}