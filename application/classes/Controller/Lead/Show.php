<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Lead_Show extends Controller_Website {

    public function before() {
        parent::before();
    }

    public function action_index() {
        $leadID    = $this->request->param('id');
        $page      = $this->request->query('page');
        $segmentID = $this->request->param('segment');
        
        $response = $this->buildLeadResponse($leadID, $page, $segmentID);
        
        if ($response instanceof Response) {
            return $response;
        }

        $showPath = APPPATH.'/views/show.mustache';
        if (!file_exists($showPath)) {
            Kohana::$log->add(Log::ERROR, 'Missing show.mustache at '.$showPath);
            throw HTTP_Exception::factory(500, 'Missing show.mustache');
        }
        return $this->render('show', $response);
    }
}