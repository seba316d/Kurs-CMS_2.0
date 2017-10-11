<?php

/**
 * Created by PhpStorm.
 * User: s.manczak
 * Date: 18.09.2017
 * Time: 09:02
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends Site_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('twig');
        $this->load->model('site/Site_model');
    }

    public function index()
    {


        $id = $this->session->userdata('id');
        $where = array('id'=>$id);
        $data['edit_user'] = $this->Site_model->get_single('users',$where); //Pobieranie info o użytkowniku o danym ID

        if(!empty($_POST))
        {

            $old_password = $data['edit_user']->password;

            $zip_code = $this->input->post("zip_code",true);
            $phone = $this->input->post("phone",true) ;
            if(strpos($zip_code,'-')==false){
                $zip_code = number_format($zip_code,'0','','-');
            }
            if(strpos($phone,'-')==false){
                $phone = number_format($phone,'0','','-');
            }

            if($this->form_validation->run('site_user_edit')==TRUE)
            {

                $data = array(
                    'username'=> $this->input->post("username",true),
                    'password' => password_hash($this->input->post("password",true),PASSWORD_DEFAULT),
                    'modify_date' => time(),
                    'phone' => $phone,
                    'zip_code' => $zip_code,
                    'street' => $this->input->post("street",true),
                    'city' => $this->input->post("city",true),
                );

                if($_POST['password']== ""){
                    $data['password']=$old_password;
                }

                $where = array('id'=>$id);
                $this->Site_model->m_update("users",$data,$where); //model od update użytkownika

                $this->session->set_flashdata('alert',"Użytkownik został edytowany !");
                redirect('/account');
            }
            else
            {
                $this->session->set_flashdata('alert',validation_errors());
            }

        }


        $data['validation']= $this->session->flashdata('alert');
        $data['session'] = $this->session->userdata('logged_in');
        $this->twig->display('site/account/index',$data);
    }

    public function registration()
    {

        if ($this->logged_in()==1)
            redirect('/');

        if(!empty($_POST)){

            if($this->form_validation->run('site_user_create')==TRUE)
            {

                $activation_code = random_string(); // tworzenie unikalnego kodu do aktywacji

                $data = array(
                    'username'=> $this->input->post("username",true),
                    'password' => password_hash($this->input->post("password",true),PASSWORD_DEFAULT),
                    'group' => 2,
                    'create_date' => time(),
                    'email'=> $this->input->post("email",true),
                    'activation_code' => $activation_code,
                );


                $this->Site_model->m_create("users",$data);
                $this->session->set_flashdata('alert',"Gratulacje! Zarejestrowałeś się  portalu. !");


                //Wysyłka e-mail potwierdzającego rejestracje

                $subject = 'Aktywacja konta';
                $message = '<p>Witaj ' . $data['username'].'. Aby aktytować konto kliknij w poniższy link:'
                .base_url('account/activation/'.$activation_code ).'</p>';


                $result = $this->email
                    ->from('sebamanczak2@gmail.com')
                    ->to($data['email'])
                    ->subject($subject)
                    ->message($message)
                    ->send();
            }
            else
            {
                $this->session->set_flashdata('alert',validation_errors());
            }

        }

        $data['validation']= $this->session->flashdata('alert');

        $this->twig->display('site/registration',$data);
    }

    public function login()
    {
            if($this->logged_in() == 1){
                redirect('/');
            }

        if(!empty($_POST)){

            if($this->form_validation->run('site_user_login')==TRUE)
            {
                $email = $this->input->post('email', true);
                $password = $this->input->post("password",true);

                $where = array('email'=>$email);
                $user = $this->Site_model->get_single('users',$where);

                if(!empty($user))
                {

                    if(password_verify($password,$user->password))
                    {
                        //Czy konto jest aktywne
                        if($user->active == 1)
                        {
                            $data_login = array(
                                'id' => $user->id,
                                'email' => $user->email,
                                'group' => $user -> group,
                                'logged_in' => true
                            );

                            $this->session->set_userdata($data_login);
                            $this->session->set_flashdata('alert',"Zalogowałeś się poprawnie");

                            if ($this->input->post('remember_me',true)==1)
                            {

                                $remember_code = random_string();

                                //Automatyczne logowanie uzytkownika
                                $user_info = array(
                                    'id' => $user->id,
                                    'email' => $user->email,
                                    'group' => $user -> group,
                                    'logged_in' => true,
                                    'remember_code' => $remember_code,
                                    'remember_me' => true,
                                );

                                $user_info_json = json_encode($user_info);

                                $data_cookie=array(
                                    "name"=>'remember_me',
                                    "value" =>$user_info_json,
                                    "expire" => 60*60*24*365,
                                    'path' => '/',
                                );
                                set_cookie($data_cookie);
                                $data = array('remember_code'=>$remember_code);
                                $where = array('id'=>$user->id);
                                $this->Site_model->m_update("users",$data,$where); //model od update użytkownika
                            }
                            else
                            {
                                redirect('/');
                            }
                        }
                        else
                        {
                            $this->session->set_flashdata('alert',"Musisz aktywować konto");
                            //redirect('account');
                        }
                    }
                    else
                    {
                        //bledne hasło
                        $this->session->set_flashdata('alert',"Błędne hasło");
                    }

                }
                else
                {
                    $this->session->set_flashdata('alert',"Użytkownik z podanym adresem e-mail nie istnieje");
                }
            }
            else
            {
                $this->session->set_flashdata('alert',validation_errors());
            }

        }

        $data['validation']= $this->session->flashdata('alert');
        $this->twig->display('site/login',$data);
    }

    public function activation($code){

        $where = array("activation_code" =>$code);
        $user = $this->Site_model->get_single("users",$where);

        if($user == '' ){

            echo "Twoje kod aktywacyjny jest niepoprawny";
            exit;
        }
        else
        {
            if($user->active == 1)
            {
                echo "Twoje konto zostało już aktywowane";
            }
            else
            {
                echo "Gratulację !! Twoje konto zostało aktywowane";
            }

        }

        $data = array('active'=>1);
        $this->Site_model->m_update("users",$data,$where);

    }

    public function logout()
    {

        $this->session->sess_destroy();
        delete_cookie("remember_me");
        redirect('account/login');

    }

    public function forgot_password()
    {
        if(!empty($_POST)){

            if($this->form_validation->run('site_user_forgot')==TRUE)
            {

                $email = $this->input->post('email',true);

                $where = array('email'=>$email);
                $user = $this->Site_model->get_single('users',$where);

                if(!empty($user))
                {

                    $reset_password_code = random_string(); // tworzenie unikalnego kodu do resetowania

                    $where = array('email'=>$email);
                    $data = array('reset_password_code'=>$reset_password_code);
                    $this->Site_model->m_update("users",$data,$where); //model od update użytkownika

                    //Wysyłka e-mail potwierdzającego rejestracje
                    $subject = 'Reset hasła';
                    $message = '<p>Witaj ' . $user->username.'. Aby zresetować konto kliknij w poniższy link:'
                        .base_url('account/reset-password/'.$reset_password_code ).'</p>';


                    $result = $this->email
                        ->from('sebamanczak2@gmail.com')
                        ->to($email)
                        ->subject($subject)
                        ->message($message)
                        ->send();

                    $this->session->set_flashdata('alert',"Sprawdź swoją skrzynkę odbiorczą");
                }
                else
                {
                    $this->session->set_flashdata('alert',"Adres e-mail nie istnieje !");
                }


            }
            else
            {
                $this->session->set_flashdata('alert',validation_errors());
                redirect($this->uri->uri_string(),'refresh');
            }
        }

        $data['validation']= $this->session->flashdata('alert');

        $this->twig->display('site/forgot_password',$data);

    }

    public function reset_password($reset_password_code)
    {
        $this->session->set_flashdata('alert',"");
        $where = array('reset_password_code'=>$reset_password_code);
        $user = $this->Site_model->get_single('users',$where);

        if(!empty($user))
        {
            if(!empty($_POST))
            {

                if ($this->form_validation->run('site_user_reset')==TRUE)
                {
                    $this->session->set_flashdata('alert',''); //Resetowanie informacji o walidacji

                    $data = array(
                        'password' => password_hash($this->input->post("password",true),PASSWORD_DEFAULT),
                        'reset_password_code' => '',
                    );

                    $where = array('id'=>$user->id);
                    $this->Site_model->m_update("users",$data,$where); //model od update użytkownika`
                    $this->session->set_flashdata('alert','Hasło zostało zmienione poprawnie :)');
                    redirect('/');
                }
                else
                {
                    $this->session->set_flashdata('alert',validation_errors());
                }

            }
            $data_code['code'] = $reset_password_code;
            $data_code['validation']= $this->session->flashdata('alert');
            $this->twig->display('site/reset_password',$data_code);
        }
        else
        {
            $this->session->set_flashdata('alert',"Podany kod nie istnieje !");
             redirect('/');
        }
    }

    public function logged_in()
    {
        return $this->session->userdata('logged_in');
    }

}