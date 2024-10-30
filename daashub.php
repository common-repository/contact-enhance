<?php

if ( ! defined( 'ABSPATH' ) ) exit;
/*
  Plugin Name: Contact Enhance
  Description: This plugin works with Contact Form 7 to enhance the business data you collect with a variety of fields including company name, revenue, company description and many more available from daashub.io.
  Version: 1.0
 */

class DaashubIntegrator {

    function init() {
        
    }

    function extract_email_address($string) {
        foreach (preg_split('/\s/', $string) as $token) {
            $email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
            if ($email !== false) {
                $emails[] = $email;
            }
        }
        return $emails;
    }

    function set_content_type($content_type) {
        return 'text/html';
    }

    function email_filter($components, $current = false, $mail = false) {

        $emails = $this->extract_email_address(strip_tags($components['body']));
        /* find email in body */
  
        $token = get_option('daashub_token');

        if (!empty($emails) && trim($token) != '') {
            $email = array_shift($emails);
            $components['body'] .= '<br /><br /><b>Additional Info from Daashub API:</b>';

            $args = array(
                'html' => true,
                'exclude_blank' => false);
            $components['body'] = wpcf7_mail_replace_tags($components['body'], $args);
            $components['body'] = wpautop($components['body']);

            /* do API call */
            $domain = explode('@', $email);
            $domain = array_pop($domain);
            $domain = 'www.' . $domain;

            $item = get_option('daashub_cache_' . $domain);

            if (!$item) {




                $curl = curl_init();
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($curl, CURLOPT_AUTOREFERER, true);
                curl_setopt($curl, CURLOPT_VERBOSE, false);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Authorization: Token token=' . $token
                ));
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_URL, 'https://emailmovers-api.herokuapp.com/v1/companies?where=domain==' . $domain . '&limit=1');

                $results = json_decode(curl_exec($curl));


                if (isset($results->items) && !empty($results->items)) {
                    $item = $results->items[0];
                    update_option('daashub_cache_' . $domain, $item);
                }
            }

            if (!$item) {
                $components['body'] .= '<br />No results found.';
            } else {
                $components['body'] .= '<br />Found the following result:'
                        . '<br />';
                foreach ($item as $key => $value) {
                    $name = str_replace('_', ' ', $key);
                    $name = ucfirst($name);
                    $components['body'].='<br /><b>' . $name . '</b>: ' . $value;
                }
                //. '<br />Email: '.$item->email.'<br />Company: '.$item->company.'<br />Postcode: '.$item->postcode.'<br />Telephone: '.$item->telephone;
            }

            /* cache the email for future */

            add_filter('wp_mail_content_type', array($this, 'set_content_type'));
        }


        return $components;
    }

    function admin_menu() {
        add_menu_page('Contact Enhance', 'Contact Enhance', 'manage_options', 'daashub_settings', array($this, 'settings'));
    }

    function settings() {
        if (current_user_can('manage_options')) {
            if (isset($_POST['daashub_token'])) {
                update_option('daashub_token', sanitize_text_field($_POST['daashub_token']));
            }
            require_once dirname(__FILE__) . '/tmpl/settings.php';
        } else {
            die('Restricted access!');
        }
    }

}

$da = new DaashubIntegrator();
add_action('init', array($da, 'init'));
add_action('admin_menu', array($da, 'admin_menu'));
add_filter('wpcf7_mail_components', array($da, 'email_filter'));
