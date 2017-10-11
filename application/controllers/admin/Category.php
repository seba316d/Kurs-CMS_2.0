<?php

/**
 * Created by PhpStorm.
 * User: s.manczak
 * Date: 04.10.2017
 * Time: 12:55
 */

class Category extends Admin_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('twig');
        $this->load->model('admin/Admin_model');
    }

    public function index()
    {
        $data['ads_category'] = $this->Admin_model->get('ads_category');
        $this->twig->display('admin/category/index',$data);
    }
    
    public function create_ads_cats()
    {

        $first_cat_arr = array();
        $second_cat_arr = array();
        $third_cat_arr = array();

        //Poziom pierwszy
        $where = array('parent_id' => 0);
        $first_category = $this->Admin_model->get('ads_category',$where);

        foreach ($first_category as $first_cat)
        {
            $first_cat_arr[] = array(
                'id' =>$first_cat->id,
                'name' =>$first_cat->name,
                'alias' =>$first_cat->alias,
                'parent_id' =>$first_cat->parent_id,
            );


            //Poziomn drugi
            $where = array('parent_id' => $first_cat->id);
            $second_category = $this->Admin_model->get('ads_category',$where);

            foreach ($second_category as $second_cat)
            {
                $second_cat_arr[] = array(
                    'id' =>$second_cat->id,
                    'name' =>$second_cat->name,
                    'alias' =>$second_cat->alias,
                    'parent_id' =>$second_cat->parent_id,
                );

                //Trzeci poziom
                $where = array('parent_id' => $second_cat->id);
                $third_category = $this->Admin_model->get('ads_category',$where);

                foreach ($third_category as $third_cat)
                {
                    $third_cat_arr[] = array(
                        'id' =>$third_cat->id,
                        'name' =>$third_cat->name,
                        'alias' =>$third_cat->alias,
                        'parent_id' =>$third_cat->parent_id,
                    );

                }

            }

        }

        if(!empty($_POST)){

            if(empty($_POST['alias'])) {
                $_POST['alias'] = alias($_POST['name']);
            }
            else {
                $_POST['alias'] = alias($_POST['alias']);
            }

            if($this->form_validation->run('admin_ads_cats_create')==TRUE)
            {
                $data = array(
                    'name'=> $this->input->post("name",true),
                    'alias'=> $this->input->post("alias",true),
                    'parent_id' => $this->input->post("parent_id",true),
                );


                //Do trzeciego poziomu mozna tworzyć kategorie
                foreach ($third_cat_arr as $third_cat)
                {
                    if($data['parent_id'] == $third_cat['id'])
                    {
                        exit('BLADD');
                    }
                }

                $this->Admin_model->m_create("ads_category",$data);
                $this->session->set_flashdata('alert',"Kategoria została dodana !");


            }
            else
            {
                $this->session->set_flashdata('alert',validation_errors());
            }

        }

        $data['validation']= $this->session->flashdata('alert');

/*        echo "<pre>";
        print_r($second_cat_arr);
        echo "</pre>";

        echo "<pre>";
        print_r($third_cat_arr);
        echo "</pre>";*/

        $data['first_category'] = $first_cat_arr;
        $data['second_category'] = $second_cat_arr;
        $data['third_category'] = $third_cat_arr;
        $this->twig->display('admin/category/create',$data);
    }

    public function edit($id)
    {
        $where = array('id'=>$id);

        $data['edit_category'] = $this->Admin_model->get_single('ads_category',$where);
        if(!empty($_POST)){

            if(empty($_POST['alias'])) {
                $_POST['alias'] = alias($_POST['name']);
            }
            else {
                $_POST['alias'] = alias($_POST['alias']);
            }

            if($this->form_validation->run('admin_ads_cats_edit')==TRUE)
            {
                $data = array(
                    'name'=> $this->input->post("name",true),
                    'alias'=> $this->input->post("alias",true),

                );


                $where = array('id'=>$id);
                $this->Admin_model->m_update("ads_category",$data,$where); //model od update użytkownika
                $this->session->set_flashdata('alert',"Kategoria została edytowana !");
                redirect('admin/category');
            }
            else
            {
                $this->session->set_flashdata('alert',validation_errors());
            }

        }

        $data['validation']= $this->session->flashdata('alert');
        $this->twig->display('admin/category/edit',$data);


    }

    public function delete($id)
    {
        $where = array('id'=>$id);
        $this->Admin_model->m_delete("ads_category",$where); //model od usuwania użytkownika
        redirect('admin/category',200);
    }

    public function alias_edit($alias)
    {
        $cat_id = $this->uri->segment(4);
        $where = array('alias'=>$alias);

        $data['edit_category'] = $this->Admin_model->get_single('ads_category',$where);
        print_r($data['edit_category']);
        if( !empty($data['edit_category']) && $data['edit_category']->id != $cat_id )
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