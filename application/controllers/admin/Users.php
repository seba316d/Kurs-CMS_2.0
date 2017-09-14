<?php

/**
 * Created by PhpStorm.
 * User: s.manczak
 * Date: 14.09.2017
 * Time: 09:17
 */

defined('BASEPATH') OR exit('No direct script access allowed');


class Users extends CI_Controller {


    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('twig');
    }


    public function create_user()
    {
        $data['base_url'] =  base_url('');


        $this->twig->display('admin/users/create',$data);

        if(!empty($_POST)){

            $this->form_validation->set_rules('password', 'password', array('required','min_length[5]'));

            if($this->form_validation->run()==TRUE)
            {
                $data = array(
                    'username'=> $this->input->post("username",true),
                    'password' => password_hash($this->input->post("password",true),PASSWORD_DEFAULT),
                    'group' => $this->input->post("group",true),
                    'create_date' => date("Y/m/d H:i:s")
                );

                $this->load->model('admin/Admin_model');
                $this->Admin_model->m_create("users",$data);

            }



        }




    }
}
