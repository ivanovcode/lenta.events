<?php


class Presenter_Common_Catalog extends Presenter
{

    public function view()
    {
        return Request::forge('common/catalog')->execute();
    }
}
