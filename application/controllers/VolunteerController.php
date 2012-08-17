<?php

class VolunteerController extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/page/view.css');
    }

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/volunteer/index.css');
    }

    public function registerAction()
    {
        $form = new Form_Volunteer($this->view->user);

        $request = $this->getRequest();
        if($request->isPost()) {
            $post = $request->getPost();

            if($form->isValid($post)) {
                $data = $form->getValues();
                Zend_Debug::dump($data);
            }
        }

        $this->view->form = $form;
    }

    public function listAction()
    {

    }
}
