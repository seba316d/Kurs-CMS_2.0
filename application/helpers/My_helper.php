<?php
/**
 * Created by PhpStorm.
 * User: s.manczak
 * Date: 15.09.2017
 * Time: 11:14
 */

function refresh()
{
    $CI =& get_instance();
    return redirect($CI->uri->uri_string(),'refresh');
}

function random_string()
{
    $string = '0123456789abcdefghijklmopqrstuvwxyz!@#$%^&*()';
    $random = '';

    for($i=0; $i<20; $i++)
    {
        $random .= $string[rand(0,strlen($string)-1)];
    }

    $time = time();

    return md5($time.$random);
}

function logged_inn()
{
    $CI =& get_instance();
    return $CI->session->userdata('logged_in');
}

function alias ($str)
{
    $str = convert_accented_characters($str);
    $str = url_title($str , '-' , true);
    return $str;
}

function chceck_group($alias_group)
{

    // Jezeli ta funkcja wyśle 1 to znaczy ze osoby nie ma w grupie. Jeżeli go nie ma to zwraca false czyli nic
    try {
        $CI =& get_instance();

        if (logged_inn() == true) {
            $admin = $alias_group;
            $user_id = $CI->session->userdata('id');

            foreach ($admin as $list_admin) {
                $where = array('alias' => $list_admin);
                $group_id = $CI->Admin_model->get_single('groups', $where);
                if(!empty($group_id)) {
                    $where = array(
                        'user_id' => $user_id,
                        'group_id' => $group_id->id,
                    );

                    $user_is_admin = $CI->Admin_model->get_single('user_groups', $where);
                }
                if (empty($user_is_admin)) {
                    return true;
                } else {
                    return false;
                }
            }


        } else {
            return false;
            /*$this->session->set_flashdata('alert',"Wstęp na strefe administracyjną zabroniony !!");
            echo "Dostęp do tej strony zabroniony !";
            redirect('/');
            exit();*/
        }
    }
    catch (Exception $e)
    {
        $this->session->set_flashdata('alert',"Wstęp na strefe administracyjną zabroniony !!");
        echo "Nie ma takiej grupy użytkowników".$e->getMessage();
    }
}

