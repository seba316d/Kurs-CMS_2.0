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

    public function m_update($table,$data,$where)
    {
        $this->db->where($where);
        $this->db->update($table,$data);
    }

    public function m_delete($table,$where)
    {
        $this->db->where($where);
        $this->db->delete($table);
    }

    public function get($table,$where = false)
    {
        if($where == true)
        {
            $this->db->where($where);
        }
        $query = $this->db->get($table);
        return $query->result();
    }

    public function get_single($table,$where)
    {
         $query = $this->db->get_where($table,$where);
         $this->db->get($table);
         return $query->row();
    }

}