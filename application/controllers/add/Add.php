<?php
/**
 * Created by PhpStorm.
 * User: s.manczak
 * Date: 28.09.2017
 * Time: 10:00
 */
defined('BASEPATH') OR exit('No direct script access allowed');


class Add extends Site_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('twig');
        $this->load->model('site/Site_model');
        $this->load->model('admin/Admin_model');

        if(!logged_inn())
        {
            redirect('account/login');
        }

    }

    public function index()
    {
        $ads =  $data['ads'] = $this->Site_model->get('ads'); // Pobieranie ogłoszen
        $category = $this->Site_model->get('ads_category'); // Pobieranie grup

        if(!empty($category)) {
            foreach ($category as $category) {
                $category_id_name_arr[$category->id] = $category->name;
            }
            $data['category_id_name_arr'] = $category_id_name_arr;
        }

        $data['user_category'] = $this->Site_model->get('ads_category');
        //$data['category'] = chceck_category(array('admin')); // jaka grupa może wykonywać jakąś akcję ;)

        $this->twig->display('site/ads/index',$data);
    }

    public function show($id='' ,$alias='')
    {
        if (empty($id) || empty($alias)) {
            redirect('add/add');
        }
            $where = array('id' => $id);
            $ad = $this->Site_model->get_single('ads', $where);

        if (logged_inn() == true)
        {
            if($ad->id_user == $_SESSION['id'] || !chceck_group(array('admin')))
            {
                $data['author'] = true;
            }
        }

            if (alias($ad->title) == $alias) {
                $data['ad'] = $ad;
                $this->twig->display('site/ads/show', $data);
            } else {
                redirect("add/add");
            }
    }

    public function create_ads()
    {
        //$data['category'] = $this->Site_model->get('ads_category');
        // var_dump($data['category']);

        if(!empty($_POST)){
            if($this->form_validation->run('site_ads_create')==TRUE)
            {
                $data = array(
                    'title'=> $this->input->post("title",true),
                    'description' => $this->input->post("description",true),
                    'id_user' => $this->session->id,
                );
                $this->session->set_flashdata('alert',"Dodano ogłoszenie");
                echo $this->Site_model->m_create("ads",$data);

                /*$where = array('email'=>$this->input->post("email",true));
                $user = $this->Admin_model->get_single('users',$where);

                $data_category['user_id'] = $user->id;
                $data_category['category_id'] = $data['category'];

                $this->Admin_model->m_create("user_category",$data_category);*/
            }
           else
            {
                $this->session->set_flashdata('alert',validation_errors());
            }

        }
       // print_r($_SESSION);
        $data['validation']= $this->session->flashdata('alert');
        $this->twig->display('site/ads/create',$data);
    }

    public function edit($id)
    {

        $where = array('id'=>$id);
        $data['edit_ads'] = $this->Site_model->get_single('ads',$where); //Pobieranie info o użytkowniku o danym ID

        $where = array('id' => $id);
        $ad = $this->Site_model->get_single('ads', $where);

        if (logged_inn() == true) {
            if ($ad->id_user == $_SESSION['id'] || !chceck_group(array('admin'))) {
                echo $ad->id_user . " i ID: ". $_SESSION['id'];

                if (!empty($_POST)) {

                    if ($this->form_validation->run('site_ads_edit') == TRUE) {
                        $data = array(
                            'title' => $this->input->post("title", true),
                            'category_id' => $this->input->post("category_id", true),
                            'thumb' => $this->input->post("thumb", true),
                            'description' => $this->input->post("description", true),
                        );

                        $this->Site_model->m_update("ads", $data, $where); //model od update grupy
                        $data_category = array(
                            'user_id' => $id,
                            'category_id' => $this->input->post("category", true),
                        );

                        $data['category'] = $this->Site_model->get('ads_category'); // Pobieranie wszystkich grup

                        $where = array('id' => $id);
                        $data['ads_category'] = $this->Site_model->get_single('ads_category', $where); // Pobieranie pojedynczego wpisu w user_category po user_id

                        /* if(empty($data['ads_category']))
                         {
                             $this->Site_model->m_create("ads_category",$data_category);
                         }
                         else
                         {
                             $where = array('user_id' => $id);
                             $this->Site_model->m_update("ads_category", $data_category, $where); //model od update grupy
                         }*/

                        /*  foreach ( $data['category'] as $category)
                          {
                              if($category->id == $data['user_category']->category_id)
                              {
                                  $data['category_id'] = $data['user_category']->category_id;
                              }
                              elseif ($data['user_category']->category_id === 0)
                              {
                                  $data['category_id'] = "a";
                              }
                          }

                          if (!isset( $data['category_id']))
                          {
                              $this->Admin_model->m_create("user_category",$data_category);
                          }
                          else
                          {
                              $where = array('user_id' => $id);
                              $this->Admin_model->m_update("user_category", $data_category, $where); //model od update grupy
                          }*/
                        $this->session->set_flashdata('alert', "Użytkownik został edytowany !");
                        redirect('add/add/');
                    } else {
                        $this->session->set_flashdata('alert', validation_errors());
                    }

                }

            }
            else
            {
                redirect('add/add');
            }
        }

        $data['category'] = $this->Site_model->get('ads_category'); // Pobieranie wszystkich grup

        /*$where = array('user_id'=>$id);
        $data['user_category'] = $this->Site_model->get_single('ads_category',$where); // Pobieranie pojedynczego wpisu w user_category po user_id*/

        /*foreach ( $data['category'] as $category)
        {
            if(!empty($data['ad_category'])) {
                if ($category->id == $data['user_category']->category_id) {
                    $data['category_id'] = $data['user_category']->category_id;
                }
                elseif ($data['user_category']->category_id == 0)
                {
                    $data['category_id'] = 0;
                }
            }
        }*/

        $data['validation']= $this->session->flashdata('alert');
        $this->twig->display('site/ads/edit',$data);


    }

    public function delete($id)
    {
        $where = array('id' => $id);
        $ad = $this->Site_model->get_single('ads', $where);

        if(czy_admin($ad))
        {
            echo "lala";
            $where = array('id' => $id);
            $this->Admin_model->m_delete("ads", $where); //model od usuwania ogłoszenia
            $this->session->set_flashdata('alert', "Ogłoszenie usuniete !");
            redirect('add/add', 200);
        }
        else
        {
            redirect('add/add');
        }
    }


}

function czy_admin($ad)
{
    if (logged_inn() == true)
    {
        if($ad->id_user == $_SESSION['id'] || !chceck_group(array('admin')))
        {
           return $data['author'] = true;
        }
    }
}