<?php

namespace Controller;

use Flight;
use Controller\BaseController;
use Exception;

class AuthenticationController extends BaseController {

    public static function login() {
        if(is_user_logged_in()) {
            Flight::redirect('/');
        }
        
        Flight::render('auth-login.php');
    }

    public static function signon() {

        if(!isset(Flight::request()->data->email)) {
            Flight::redirect('/login?error=101');
            die();
        }

        if(!isset(Flight::request()->data->password)) {
            Flight::redirect('/login?error=102');
            die();
        }

        if(!filter_var(Flight::request()->data->email, FILTER_VALIDATE_EMAIL)) {
            Flight::redirect('/login?error=103');
            die();
        }

        if(!email_exists( Flight::request()->data->email )) {
            Flight::redirect('/login?error=104');
            die();
        }
        
        $creds = array(
            'user_login'    => Flight::request()->data->email,
            'user_password' => Flight::request()->data->password,
            'remember'      => true
        );
     
        $user = wp_signon( $creds, false );
     
        if ( is_wp_error( $user ) ) {
            if($user->get_error_code() == 'incorrect_password')
                Flight::redirect('/login?error=105');
            else
                Flight::redirect('/login?error=' . $user->get_error_code());
            die();
        }

        Flight::redirect('/');
    }

    public static function reset() {

        $authorized = false;

        if(Flight::request()->method == "POST") {
            
            $user  = isset(Flight::request()->data->user) ? Flight::request()->data->user : false;
            $token = isset(Flight::request()->data->token) ? Flight::request()->data->token : false;;
            $password = isset(Flight::request()->data->password) ? Flight::request()->data->password : false;

            if( $user && $token ) {
                if( $token ==  get_user_meta($user, 'reset_password_token', true) ) {
                    wp_set_password( $password, $user );
                    delete_user_meta($user, 'reset_password_token');
                    
                    Flight::redirect('/login');
                    die();
                    
                }
            } else {
            
                if(!isset(Flight::request()->data->email)) {
                    Flight::redirect('/auth/reset?error=101');
                    die();
                }

                if(!filter_var(Flight::request()->data->email, FILTER_VALIDATE_EMAIL)) {
                    Flight::redirect('/auth/reset?error=103');
                    die();
                }

                if(!email_exists( Flight::request()->data->email )) {
                    Flight::redirect('/auth/reset?error=104');
                    die();
                } 

                $user  = get_user_by('email', Flight::request()->data->email);
                $token = wp_generate_password( 50, false, false );
                update_user_meta( $user->ID, 'reset_password_token', $token);

                $mailer = WC()->mailer();
            
                $recipient = $user->user_email; 
                $subject = __("Redefinição de senha", 'avada');

                ob_start();

                Flight::render('emails/reset-password.php', [
                    'logo'  => BaseController::assets('images/logo.png'),
                    'name'  => $user->first_name,
                    'link'  => BaseController::url('auth/reset?user=' . $user->ID . '&token='). $token,
                    'title' => 'Prossiga com sua solicitação para redefinição de senha.',
                    'site_url' => get_site_url(), 
                ]);

                $content = ob_get_clean();
                
                $headers = "Content-Type: text/html\r\n";
                $mailer->send( $recipient, $subject, $content, $headers );
                
 
                Flight::redirect('/auth/reset?success=200');
                die();
            }

        } else {

            $user  = isset(Flight::request()->query->user) ? Flight::request()->query->user : false ;
            $token = isset(Flight::request()->query->token) ? Flight::request()->query->token : false;

            if($user && $token) {
                if( $token ==  get_user_meta($user, 'reset_password_token', true) ) {
                    $authorized = true;
                }
            }

        }

        Flight::render('auth-reset.php', [
            'authorized' => $authorized,
            'user'  => isset($user) ? $user : false,
            'token' => isset($token) ? $token : false
        ]);
        
    }

}