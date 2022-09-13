<?php

class Controller_About extends Controller_Template
{

	public function action_index()
	{

        $data = array();

        $data['navigation'] = Request::forge('common/navigation')->execute();

        $this->template->active = 'about';
        $this->template->title = 'О проекте';
        $this->template->content = View::forge('about', $data);


	}

	public function action_404()
	{
		return Response::forge(Presenter::forge('welcome/404'), 404);
	}
}
