<?php
/**
 * Template Name: Employer Register
 */

ob_start();
get_header();
$cs_employer_cus_fields = get_option("cs_employer_cus_fields");
$input_fields = array(
        array('type'=>'text', 'name'=> 'show_employer_name','class'=>'sk_employer_name', 'required'=>'required', 'placeholder'=>'Enter your Full Name', 'label'=>'Full Name'),
        array('type'=>'email', 'name'=> 'employer_email','class'=>'sk_employer_name', 'required'=>'required', 'placeholder'=>'Enter your Email Address', 'label'=>'Email Address'),
        array('type'=>'text', 'name'=> $id = 'employer_company_name','class'=>'sk_employer_name', 'required'=>'required', 'placeholder'=>'Company Name', 'label'=>'Company Name'),
        array('type'=>'select', 'name'=> $id = 'show_employer_job_role','class'=>'', 'required'=>'required', 'label'=>'Job Role', 'options'=> rh_cs_employer_profile_custom_fields($id)),
        array('type'=>'select', 'name'=> $id = 'show_employer_company_location','class'=>'', 'required'=>'required', 'label'=>'Company Location', 'options'=> rh_cs_employer_profile_custom_fields($id)),
        array('type'=>'select', 'name'=> $id = 'show_employer_company_state','class'=>'', 'required'=>'required', 'label'=>'Company State', 'options'=> array()),
        array('type'=>'select', 'name'=> 'show_employer_company_city','class'=>'', 'required'=>'required', 'label'=>'Company City', 'options'=> array()),

        array('type'=>'select', 'name'=> $id = 'show_employer_industry','class'=>'', 'required'=>'required', 'label'=>'Company Industry', 'options'=> rh_cs_employer_profile_custom_fields($id)),
        array('type'=>'date', 'name'=> 'show_employer_job_start','class'=>'sk_employer_name', 'required'=>'required', 'placeholder'=>'Start Date', 'label'=>'How long have you been with the company?'),
        array('type'=>'text', 'name'=> 'show_employer_skills','class'=>'sk_employer_name', 'required'=>'required', 'placeholder'=>'Enter Skill/roles separate by comma', 'label'=>'Skill / Roles I hire for'),
        array('type'=>'select', 'name'=> $id = 'show_employer_levels','class'=>'', 'required'=>'required', 'label'=>'Lavels I hire for', 'options'=> rh_cs_employer_profile_custom_fields($id)),
        array('type'=>'select', 'name'=> 'employer_sponsors','class'=>'', 'required'=>'required', 'label'=>'Does your company provide sponsorship!', 'options'=> sponsors()),
        array('type'=>'text', 'name'=> 'show_employer_functions','class'=>'', 'required'=>'required', 'placeholder'=>'Enter functional areas separate by comma', 'label'=>'Functional Areas'),
);
?>

    <!-- Content Section Start Here
       ---------------------------------------------------------------- -->
    <section class="contentArea">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="contenWrap">
                        <div class="contentWrapTitle">
                            <h3>Recruiter Sign Up</h3>
                        </div>
                        <form
                                id="jq_employer_reg_form"
                                class="stemknot_validate_form"
                                data-form-validate="true"
                                role="form"
                                action="<?php echo esc_url( admin_url('admin-post.php') ); ?>"
                                method="POST"
                                enctype="multipart/form-data"
                        >
                        <div class="contentinnerWrap">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="profileInof">
                                        <div class="profielimg"><img src="<?php echo CHILD_ASSETS ?>images/profile_blank.png"></div>
<!--                                        <a href="#" class="stbtn">Upload Profile picture</a>-->
                                        <input type="file" name="profile_pic" id="fileToUpload" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <?php
                                    foreach($input_fields as $key => $field){ ?>
                                        <div class="col-lg-6 col-md-12 col-sm-12">
                                            <div class="input-col">
                                                <?php
                                                    $name = $field['name'];

                                                    $type = $field['type'];
                                                    $input = '<label for="'.$name.'">'.$field['label'].'</label>';
                                                    if($type == 'select'){
                                                        $onchange = '';
                                                        if($name == 'show_employer_company_location'){
                                                            $onchange = 'onchange="getState(this.value);"';
                                                        }
                                                        if($name == 'show_employer_company_state'){
                                                            $onchange = 'onchange="getState(this.value, 1)";';
                                                        }
                                                        $input.= '<div class="select-style">
                                                            <select name="'.$name.'" id="'.$name.'" class="'.$field['class'].'" '.$onchange.'>';
                                                                foreach($field['options'] as $k => $option){
                                                                    $input.= '<option value="'.$k.'">'.$option.'</option>';
                                                                }
                                                            $input.='</select>
                                                        </div>';
                                                    }else{
                                                        $input.= '<input name="'.$name.'" type="'.$field['type'].'" placeholder="'.$field['placeholder'].'" id="'.$name.'" >
                                                        <span class="notice warning"></span>';
                                                    }
                                                    if($name == 'employer_company_name'){
                                                        $input.= '<ul id="searchResult"></ul>';
                                                    }
                                                   echo $input;
                                                ?>
                                            </div>
                                        </div>
                                   <?php }
                                ?>

                                <div class="col-lg-12">
                                    <div class="inputbtn">
                                        <input type="submit" id="jq_employer_sign_up_form" class="stbtn" name="register" value="Sign Up">
                                    </div>
                                </div>


                                <div class="display_errors">

                                </div>


                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Content Section Start Here
    ---------------------------------------------------------------- -->
<style>
    .warning{
        color: red;
    }
    #searchResult{
        list-style: none;
        padding: 0px;
        margin: 0;
    }

    #searchResult li{
        background: lavender;
        padding: 4px;
        margin-bottom: 1px;
    }

    #searchResult li:nth-child(even){
        background: #55A747;
        color: white;
    }

    #searchResult li:hover{
        cursor: pointer;
    }
</style>
<?php get_footer();