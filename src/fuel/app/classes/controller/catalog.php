<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.8.2
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2019 Fuel Development Team
 * @link       https://fuelphp.com
 */

/**
 * The Welcome Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 *
 * @package  app
 * @extends  Controller
 */

use \Model\Catalog2;

class Controller_Catalog extends Controller_Template
{
    public function action_index()
    {
        $data = array();

        $config = array(
            'pagination_url' => NULL,
            'total_items'    => Model_Catalog::count(),
            'per_page'       => 10,
            'uri_segment'    => 3
        );

        $pagination = Pagination::forge('mypagination', $config);

        $data['catalog'] = Model_Catalog::query()
            ->rows_offset($pagination->offset)
            ->rows_limit($pagination->per_page)
            ->get();

        $data['navigation'] = Request::forge('common/navigation')->execute(array('catalog'=>true));

        $this->template->active = 'catalog';
        $this->template->title = 'Каталог';
        $this->template->content = View::forge('catalog', $data);

    }

}
