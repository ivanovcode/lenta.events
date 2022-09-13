<?php


class Model_Catalog extends Orm\Model {

    protected static $_connection = 'production';
    protected static $_table_name = 'catalog';
    protected static $_primary_key = array('id');

    protected static $_properties = array (
        'id',
        'name' => array (
            'data_type' => 'varchar',
            'label' => 'Имя',
            'validation' => array (
                'required',
                'min_length' => array(3),
                'max_length' => array(80)
            ),

            'form' => array (
                'type' => 'text'
            ),
        ),
        'phone' => array (
            'data_type' => 'varchar',
            'label' => 'Телефон',
            'validation' => array (
                'required',
                'min_length' => array(3),
                'max_length' => array(80)
            ),

            'form' => array (
                'type' => 'text'
            ),
        ),
    );
    protected static $_observers = array('Orm\\Observer_Validation' => array (
        'events' => array('before_save')
    ));
}