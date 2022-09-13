<?php


class Presenter_Common_Navigation extends Presenter
{

    public function view()
    {
        return Request::forge('common/navigation')->execute();
    }
}
