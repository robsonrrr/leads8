<?php
class Controller_Lead_Session extends Controller_Website {

    public function action_index()
    {
        if ( isset($_SESSION['check']) and $_SESSION['check'] == true)
        {
            $_SESSION['check'] = false;
        }else{
            $_SESSION['check'] = true;
        }

        return $this->response->body(json_encode($_SESSION['check']));
    }

}