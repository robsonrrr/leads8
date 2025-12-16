<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Api_Test extends Controller {

    public function action_index()
    {
        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode(array(
            'success' => true,
            'message' => 'API Test is working'
        )));
    }
}