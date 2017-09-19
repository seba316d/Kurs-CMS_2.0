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