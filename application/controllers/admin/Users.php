<?php

/**
 * Created by PhpStorm.
 * User: s.manczak
 * Date: 14.09.2017
 * Time: 09:17
 */

defined('BASEPATH') OR exit('No direct script access allowed');


class Users extends Admin_Controller {


    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('twig');
        $this->load->model('admin/Admin_model');
    }

    public function index()
    {
        $data['users'] = $this->Admin_model->get('users'); // Pobieranie uzytkownikow
        $groups = $this->Admin_model->get('groups'); // Pobieranie grup

        if(!empty($groups)) {
            foreach ($groups as $group) {
                $groups_id_name_arr[$group->id] = $group->name;
            }
            $data['groups_id_name_arr'] = $groups_id_name_arr;
        }
        $data['user_groups'] = $this->Admin_model->get('user_groups');

        $data['group'] = chceck_group(array('admin')); // jaka grupa może wykonywać jakąś akcję ;)

        $this->twig->display('admin/users/index',$data);
    }

    public function create_user()
    {

        $data['group'] = $this->Admin_model->get('groups');
       // var_dump($data['group']);

        if(!empty($_POST)){

            if($this->form_validation->run('admin_user_create')==TRUE)
            {
                $data = array(
                    'username'=> $this->input->post("username",true),
                    'password' => password_hash($this->input->post("password",true),PASSWORD_DEFAULT),
                    'group' => $this->input->post("group",true),
                    'create_date' => time(),
                    'email'=> $this->input->post("email",true),
                );

                $this->Admin_model->m_create("users",$data);

                $where = array('email'=>$this->input->post("email",true));
                $user = $this->Admin_model->get_single('users',$where);

                $data_group['user_id'] = $user->id;
                $data_group['group_id'] = $data['group'];


                $this->Admin_model->m_create("user_groups",$data_group);
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
        $data['edit_user'] = $this->Admin_model->get_single('users',$where); //Pobieranie info o użytkowniku o danym ID

        if(!empty($_POST))
        {

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


                $data_group = array(
                    'user_id'=> $id,
                    'group_id'=>$this->input->post("group",true),
                );

                $data['group'] = $this->Admin_model->get('groups'); // Pobieranie wszystkich grup

                $where = array('user_id'=>$id);
                $data['user_group'] = $this->Admin_model->get_single('user_groups',$where); // Pobieranie pojedynczego wpisu w user_groups po user_id

                if(empty($data['user_group']))
                {
                    $this->Admin_model->m_create("user_groups",$data_group);
                }
                else
                {
                    $where = array('user_id' => $id);
                    $this->Admin_model->m_update("user_groups", $data_group, $where); //model od update grupy
                }

              /*  foreach ( $data['group'] as $group)
                {
                    if($group->id == $data['user_group']->group_id)
                    {
                        $data['group_id'] = $data['user_group']->group_id;
                    }
                    elseif ($data['user_group']->group_id === 0)
                    {
                        $data['group_id'] = "a";
                    }
                }

                if (!isset( $data['group_id']))
                {
                    $this->Admin_model->m_create("user_groups",$data_group);
                }
                else
                {
                    $where = array('user_id' => $id);
                    $this->Admin_model->m_update("user_groups", $data_group, $where); //model od update grupy
                }*/
                $this->session->set_flashdata('alert',"Użytkownik został edytowany !");
               redirect('admin/users');
            }
            else
            {
                $this->session->set_flashdata('alert',validation_errors());
            }

        }

        $data['group'] = $this->Admin_model->get('groups'); // Pobieranie wszystkich grup

        $where = array('user_id'=>$id);
        $data['user_group'] = $this->Admin_model->get_single('user_groups',$where); // Pobieranie pojedynczego wpisu w user_groups po user_id

        foreach ( $data['group'] as $group)
        {
            if(!empty($data['user_group'])) {
                if ($group->id == $data['user_group']->group_id) {
                    $data['group_id'] = $data['user_group']->group_id;
                }
                elseif ($data['user_group']->group_id == 0)
                {
                    $data['group_id'] = 0;
                }
            }
        }


        $data['validation']= $this->session->flashdata('alert');
        $this->twig->display('admin/users/edit',$data);


    }

    public function delete($id)
    {
        $where = array('id'=>$id);
        $this->Admin_model->m_delete("users",$where); //model od usuwania użytkownika
        redirect('admin/users',200);
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
