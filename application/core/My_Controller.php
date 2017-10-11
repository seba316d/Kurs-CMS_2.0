<?php

/**
 * Created by PhpStorm.
 * User: s.manczak
 * Date: 19.09.2017
 * Time: 11:27
 */
defined('BASEPATH') OR exit('No direct script access allowed');


class My_Controller extends CI_Controller
{

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     *        http://example.com/index.php/welcome
     *    - or -
     *        http://example.com/index.php/welcome/index
     *    - or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('site/Site_model');


        if (!empty(get_cookie('remember_me')))
        {

            $user = json_decode((get_cookie('remember_me')));

            $where = array('id'=>$user->id);
            $user_code_db = $this->Site_model->get_single('users',$where);

            if (get_cookie('remember_me') == true && $user->remember_code == $user_code_db->remember_code) {
                $data_login = array(
                    'id' => $user->id,
                    'email' => $user->email,
                    'group' => $user->group,
                    'logged_in' => true,
                    'remembe_me' => true,
                );

                $this->session->set_userdata($data_login);
            }
        }
        else
        {

            if($this->session->logged_in == 1 && $this->session->remember_me == 1)
            {

                $where = array('id'=>$this->session->id);
                $user_code_db = $this->Site_model->get_single('users',$where);

                echo '<pre>';
                print_r($user_code_db);
                echo '</pre>';

                $user_info = array(
                    'id' => $user_code_db->id,
                    'email' => $user_code_db->email,
                    'group' => $user_code_db -> group,
                    'logged_in' => true,
                    'remember_code' => $user_code_db->remember_code,
                );

                $user_info_json = json_encode($user_info);

                $data_cookie=array(
                    "name"=>'remember_me',
                    "value" =>$user_info_json,
                    "expire" => 60*60*24*365,
                    'path' => '/',
                );
                set_cookie($data_cookie);
            }
        }

    }
}


class Site_Controller extends My_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('site/Site_model');

        if(!logged_inn())
        {
            echo $segment = $this->uri->segment(2);
            if($segment == '' || $segment == 'logout' )
            {
                redirect('/');
            }
        }

    }


}

class Admin_Controller extends My_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('admin/Admin_model');


        if(logged_inn()) {

            if (chceck_group(array('admin', 'janusz')) == true) {
                $this->session->set_flashdata('alert', "Nie masz dostępu do tej części serwisu!");
                redirect('/');
            }
        }
        else
        {
            $this->session->set_flashdata('alert', "Nie jestes zalogowany przepraszam!");
            redirect('/');
        }


    }


}