<?php

/**
 * Created by PhpStorm.
 * User: s.manczak
 * Date: 14.09.2017
 * Time: 13:21
 */
class Admin_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();

    }

    public function m_create($table,$data)
    {
        $this->db->insert($table ,$data);
    }

}