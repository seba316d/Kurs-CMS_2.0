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
        $this->load->model('admin/Admin_model');
    }

    public function index()
    {
        $data['users'] = $this->Admin_model->get('users');
        $this->twig->display('admin/users/index',$data);
    }


    public function create_user()
    {
        $data['base_url'] =  base_url('');


        if(!empty($_POST)){


            if($this->form_validation->run('admin_user_create')==TRUE)
            {
                $data = array(
                    'username'=> $this->input->post("username",true),
                    'password' => password_hash($this->input->post("password",true),PASSWORD_DEFAULT),
                    'group' => $this->input->post("group",true),
                    'create_date' => date("Y/m/d H:i:s"),
                    'email'=> $this->input->post("email",true),
                );


                $this->Admin_model->m_create("users",$data);
                $this->session->set_flashdata('alert',"Użytkownik został dodany !");
            }
            else
            {
                $this->session->set_flashdata('alert',validation_errors());
            }

        }

        $data['validation']= $this->session->flashdata('alert');

        $this->twig->display('admin/users/create',$data);
    }

    public function edit($id)
    {
        $where = array('id'=>$id);

        $data['edit_user'] = $this->Admin_model->get_single('users',$where);

        if(!empty($_POST)){

            $old_password = $data['edit_user']->password;

            if($this->form_validation->run('admin_user_edit')==TRUE)
            {
                $data = array(
                    'username'=> $this->input->post("username",true),
                    'password' => password_hash($this->input->post("password",true),PASSWORD_DEFAULT),
                    'group' => $this->input->post("group",true),
                    'create_date' => date("Y/m/d H:i:s"),
                    'email'=> $this->input->post("email",true),
                );

                if($_POST['password']== ""){
                    $data['password']=$old_password;
                }

                $where = array('id'=>$id);
                $this->Admin_model->m_update("users",$data,$where); //model od update użytkownika
                $this->session->set_flashdata('alert',"Użytkownik został edytowany !");
               redirect('admin/users');
            }
            else
            {
                $this->session->set_flashdata('alert',validation_errors());
            }

        }

        $data['validation']= $this->session->flashdata('alert');
        $this->twig->display('admin/users/edit',$data);


    }

    public function delete($id)
    {
        $where = array('id'=>$id);
        $this->Admin_model->m_delete("users",$where); //model od usuwania użytkownika
        redirect('/users',200);
    }

    public function email_edit($email)
    {
        $user_id = $this->uri->segment(4);

        $where = array('email'=>$email);

        $data['edit_user'] = $this->Admin_model->get_single('users',$where);

        if( !empty($data['edit_user']) && $data['edit_user']->id != $user_id )
        {
            $this->form_validation->set_message('email_edit', 'Ktoś posiada już taki e-mail');
            return false;
        }
        else
        {
            return true;
        }


    }
}
