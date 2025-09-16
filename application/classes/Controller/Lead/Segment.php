<?php
class Controller_Lead_Segment extends Controller_Website {

    public function before()
    {
        parent::before();
    }

    public function action_index()
    {
        $leadID = $this->request->param('id');

        $array['leadID'] = $leadID;

        return $this->render( 'lead/segmento', $array);
    }

}