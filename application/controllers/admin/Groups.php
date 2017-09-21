<?php

/**
 * Created by PhpStorm.
 * User: s.manczak
 * Date: 20.09.2017
 * Time: 10:17
 */
defined('BASEPATH') OR exit('No direct script access allowed');


class Groups extends Admin_Controller {


    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('twig');
        $this->load->model('admin/Admin_model');
    }

    public function index()
    {
        $data['groups'] = $this->Admin_model->get('groups');
        $this->twig->display('admin/groups/index',$data);
    }


    public function create_groups()
    {

        if(!empty($_POST)){

            if(empty($_POST['alias'])) {
                $_POST['alias'] = alias($_POST['name']);
            }
            else {
                $_POST['alias'] = alias($_POST['alias']);
            }

            if($this->form_validation->run('admin_groups_create')==TRUE)
            {
                $data = array(
                    'name'=> $this->input->post("name",true),
                    'alias'=> $this->input->post("alias",true),
                );

                $this->Admin_model->m_create("groups",$data);
                $this->session->set_flashdata('alert',"Grupa została dodana !");
            }
            else
            {
                $this->session->set_flashdata('alert',validation_errors());
            }

        }

        $data['validation']= $this->session->flashdata('alert');

        $this->twig->display('admin/groups/create',$data);
    }

    public function edit($id)
    {
        $where = array('id'=>$id);

        $data['edit_group'] = $this->Admin_model->get_single('groups',$where);

        if(!empty($_POST)){

            if(empty($_POST['alias'])) {
                $_POST['alias'] = alias($_POST['name']);
            }
            else {
                $_POST['alias'] = alias($_POST['alias']);
            }

            if($this->form_validation->run('admin_groups_edit')==TRUE)
            {
                $data = array(
                    'name'=> $this->input->post("name",true),
                    'alias'=> $this->input->post("alias",true),

                );


                $where = array('id'=>$id);
                $this->Admin_model->m_update("groups",$data,$where); //model od update użytkownika
                $this->session->set_flashdata('alert',"Grupa została edytowana !");
                redirect('admin/groups');
            }
            else
            {
                $this->session->set_flashdata('alert',validation_errors());
            }

        }

        $data['validation']= $this->session->flashdata('alert');
        $this->twig->display('admin/groups/edit',$data);


    }

    public function delete($id)
    {
        $where = array('id'=>$id);
        $this->Admin_model->m_delete("groups",$where); //model od usuwania użytkownika
        redirect('admin/groups',200);
    }

    public function alias_edit($alias)
    {
        $user_id = $this->uri->segment(4);

        $where = array('alias'=>$alias);

        $data['edit_user'] = $this->Admin_model->get_single('groups',$where);

        if( !empty($data['edit_user']) && $data['edit_user']->id != $user_id )
        {
            $this->form_validation->set_message('alias_edit', 'Ktoś posiada już taki alias');
            return false;
        }
        else
        {
            return true;
        }


    }
}
