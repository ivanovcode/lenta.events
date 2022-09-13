<?php

class Controller_Common extends Controller
{

    public function action_navigation()
    {
        return Response::forge(Presenter::forge('common/navigation'));
    }

    public function action_catalog()
    {
        return Response::forge(Presenter::forge('common/catalog'));
    }

}
