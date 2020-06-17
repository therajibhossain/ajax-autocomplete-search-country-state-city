<?php

function loginUser($login, $pass, $remember)
{
    $response['pass'] = false;
    $response['login'] = false;
    $response['email'] = false;
    $response['answer'] = false;
    //Get user by email
    $user = get_user_by('email', $login);
    if (empty($user)) {
        $user = get_user_by('login', $login);

        if (empty($user)) {
            return $response;
        }
    }
    $url = home_url();
    if ($user->roles[0] == "cs_employer") {
        $url .= '/employer-account/';
    }

    //User password check
    if ((wp_check_password($pass, $user->data->user_pass, $user->data->ID))) {
        //Login user
        $creds = [];
        $creds['user_login'] = $login;
        $creds['user_password'] = $pass;
        $creds['remember'] = $remember;

        wp_signon($creds, false);
        $response['login'] = true;
        $response['email'] = true;
        $response['answer'] = true;
        $response['pass'] = true;
        $response['url'] = $url;
    } else {
        $user_data = check_password_reset_key($pass, $user->data->user_login);

        if (!is_wp_error($user_data)) {
            $user_id = $user->data->ID;
            clean_user_cache($user_id);
            wp_clear_auth_cookie();
            wp_set_current_user($user_id, $user->user_login);
            wp_set_auth_cookie($user_id);

            $response['url'] = $url;
            $response['login'] = true;
            $response['email'] = true;
            $response['answer'] = true;
            $response['pass'] = true;
        } else {
            $response['answer'] = false;
            $response['pass'] = false;
            $response['email'] = false;
        }

    }

    //Send response
    return $response;
}

function employer_signup()
{
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    global $wpdb;
    $response = array();
    $response['answer'] = false;
    $response['login'] = true;
    $response['email'] = true;

    //User logged in check
    if (is_user_logged_in()) {
        $response['error'] = 'You already was logined!';
        echo json_encode($response);
        die();
    }

    if (empty($_POST)) {
        //If POST array is empty
        $response['error'] = 'Connecting error!';
        json_encode($response);
        die();
    } else {
        //Get user by email
        if ($user = get_user_by('email', $_POST['employer_email'])) {
            $response['email'] = false;
            echo json_encode($response);
            die();
        }
        if ($user = get_user_by('login', $_POST['employer_company_name'])) {
            $response['login'] = false;
            echo json_encode($response);
            die();
        }

        if ($response['login'] && $response['email']) {
            /*uploading*/
//            $file = uploadFile();
//            if($file['error']){
//                $response['error'] = $file['error'];
//                json_encode($response);
//                die();
//            }else{
//                $profile_pic = $file['profile_pic'];
//            }


            $user_data = array(
                'user_pass' => $_POST['employer_password'],
                'user_email' => $_POST['employer_email'],
                'user_login' => $_POST['employer_company_name'],
                'role' => 'cs_employer',
                'show_admin_bar_front' => 'false'
            );

            $user_id = wp_insert_user($user_data);
            rh_sk_employer_save_company_logo($user_id);

            update_user_meta($user_id, 'cs_phone_number', $_POST['employer_phone_number']);
            update_user_meta($user_id, 'cs_job_title', $_POST['employer_job']);
            update_user_meta($user_id, 'cs_department', $_POST['employer_department']);
            update_user_meta($user_id, 'company_name', $_POST['employer_company_name']);
            update_user_meta($user_id, 'cs_sponsors', $_POST['employer_sponsors']);
            update_user_meta($user_id, 'cs_user_status', 'active');

            extra_data($user_id);

            if (is_numeric($user_id)) {
                //Login store
                $response = loginUser($_POST['employer_company_name'], $_POST['employer_password'], true);
            }
            $wpdb->update($wpdb->prefix . 'users', array('user_status' => 1), array('ID' => esc_sql($user_id)));
        }
    }

    echo json_encode($response);
    die();
}

/*** `COMPANY LOGO` ***/
function rh_sk_employer_save_company_logo($cur_user_id)
{
    $response = [
        'response' => '',
        'message' => '',
        'errors' => '',
    ];
//    $data = array_merge($_POST, $_FILES);
    $data = $_FILES;
    $posted_data = isset($data) ? $data : [];

    // Nonce verification
    if (!wp_verify_nonce($posted_data['stemknot_nonce_secure'], STEMKNOT_NONCE_SECURE_HASH)) {
//        die('Stop!');
    }


    $company_logo_image = isset($posted_data['profile_pic']) ? $posted_data['profile_pic'] : '';
    //echo '<pre>', print_r($company_logo_image), '</pre>', exit();

    $attach_id = 0;
    if (isset($company_logo_image['name']) && !empty($company_logo_image['name'])) {

        global $plugin_user_images_directory;
        // Register our new path for user images.
        add_filter('upload_dir', 'cs_user_images_custom_directory');
        $json = array();
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        $cs_allowed_image_types = array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        );
        $status = wp_handle_upload($company_logo_image, array('test_form' => false, 'mimes' => $cs_allowed_image_types));

        if (empty($status['error'])) {
            $image = wp_get_image_editor($status['file']);
            if (!is_wp_error($image)) {
                $sizes_array = array(
                    array('width' => 270, 'height' => 203, 'crop' => true),
                    array('width' => 236, 'height' => 168, 'crop' => true),
                    array('width' => 200, 'height' => 200, 'crop' => true),
                    array('width' => 180, 'height' => 135, 'crop' => true),
                    array('width' => 150, 'height' => 113, 'crop' => true),
                );
                $resize = $image->multi_resize($sizes_array, true);
            }
            if (is_wp_error($image)) {
                echo '<span class="error-msg">' . $image->get_error_message() . '</span>';
            } else {
                $wp_upload_dir = wp_upload_dir();
                $img_resized_name = isset($resize[0]['file']) ? basename($resize[0]['file']) : '';
                $filename = '/' . $plugin_user_images_directory . '/' . $img_resized_name;
                $filetype = wp_check_filetype(basename($filename), null);
                if ($filename != '') {
                    // Prepare an array of post data for the attachment.
                    $attachment = array(
                        'guid' => $status['url'],
                        'post_mime_type' => $filetype['type'],
                        'post_title' => preg_replace('/\.[^.]+$/', '', ($filename)),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );
                    // Insert the attachment.
                    $attach_id = wp_insert_attachment($attachment, $filename);

                    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    // Generate the metadata for the attachment, and update the database record.
                    $attach_data = wp_generate_attachment_metadata($attach_id, $status['file']);
                    wp_update_attachment_metadata($attach_id, $attach_data);
                }
            }
        } else {
            $img_resized_name = '';
        }
        // Set everything back to normal.
        remove_filter('upload_dir', 'cs_user_images_custom_directory');
    }

    if ($cur_user_id) {
        $result = update_user_meta($cur_user_id, 'user_img', $attach_id);
    }

    if ($result) {
        // It's Ok
        $response['response'] = 'SUCCESS';
        $response['message'] = $result;
    } else {
        // Something went wrong
        $response['response'] = 'FAILED';
    }
    return $response;
//
//    echo json_encode($response);
//    wp_die();
}

function uploadFile()
{
    $res = array('error' => false, 'profile_pic' => false);
    $file = $_FILES['profile_pic'];
    if ($file['name'][0] != '') {
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($file, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            $explode = explode(site_url(), $movefile['url']);
            $res['profile_pic'] = $explode[1];
        } else {
            /**
             * Error generated by _wp_handle_upload()
             * @see _wp_handle_upload() in wp-admin/includes/file.php
             */
            $res = array('error' => $movefile['error']);
        }

    }
    return $res;
}


function extra_data($user_id)
{
    $extra_data = array();
    foreach ($_POST as $key => $post) {
        switch ($key) {
            case  'employer_password':
            case  'employer_email':
            case  'employer_company_name':
            case  'employer_phone_number':
            case  'employer_job':
            case  'employer_department':
            case  'employer_sponsors':
            case  'action':
                break;
            default:
                update_user_meta($user_id, $key, $post);
                $extra_data[$key] = $post;
        }
    }
    if ($extra_data) {
        //update_user_meta( $user_id, 'extra_data', $extra_data);
    }
}

add_action('wp_ajax_employer_signup', 'employer_signup');
add_action('wp_ajax_nopriv_employer_signup', 'employer_signup');

function check_employer_company_name()
{
    $company_name = trim($_POST['employer_company_name']);
    global $wpdb;
    $col = 'user_login';
    $sql = "SELECT {$col} FROM {$wpdb->prefix}users where $col like '%" . $company_name . "%' order by {$col} asc limit 5";
    $result = $wpdb->get_results($sql, ARRAY_A);
    echo json_encode($result);
    die;
}

add_action('wp_ajax_check_employer_company_name', 'check_employer_company_name');
add_action('wp_ajax_nopriv_check_employer_company_name', 'check_employer_company_name');

function rh_get_state_city()
{
    if (!empty($_POST["country_state_id"])) {
        global $wpdb;

        $country_state_id = $_POST["country_state_id"];
        $type = $_POST["type"];
        $col = 'country_id';
        $select = 'State';
        if($type == 'cities'){
            $col = 'state_id';
            $select = 'City';
        }

        $sql = "SELECT id, {$col}, name FROM {$wpdb->prefix}$type where $col = $country_state_id";
        echo $sql;
        $result = $wpdb->get_results($sql);
        echo '<option value="">Select '.$select.'</option>';
        if ($result) {
            foreach ($result as $row) {
                echo '<option value="' . $row->id . '">' . $row->name . '</option>';
            }
        }
    }
    return;
}

add_action('wp_ajax_rh_get_state_city', 'rh_get_state_city');
add_action('wp_ajax_nopriv_rh_get_state_city', 'rh_get_state_city');


