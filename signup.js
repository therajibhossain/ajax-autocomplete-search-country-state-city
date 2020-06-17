jQuery(document).ready(function ($) {

    function validate(form) {
        var answer = true;
        var pass1 = '';
        var pass2 = '';

        $(form).find('input').each(function () {
            var input = $(this);
            var value = input.val();
            input.focus();
            if (value.length < 0) {
                input.addClass('warning');
                input.siblings('span.notice.notice.warning').html('Empty field');
                answer = false;
            } else if (input.attr('type') == 'email') {
                if (value.indexOf('@') == -1) {
                    input.addClass('warning');
                    input.siblings('span.notice.notice.warning').html('Type the correct email');
                    answer = false;
                }
            } else if (input.attr('type') == 'password') {
                if (pass1 == '') {
                    pass1 = input.val();
                } else {
                    pass2 = input.val();
                }
            }
        });
        if (pass1 != pass2) {
            $(form).find('input[type="password"]').each(function () {
                $(this).addClass('warning');
                $(this).siblings('span.notice.notice.warning').html('Passwords do not match');
                answer = false;
            });
        }

        return answer;
    }

    $('body').on('focus', '.form-group input.warning', function () {
        $(this).removeClass('warning');
        $(this).siblings('span.notice.notice.warning').html('');
    });

    $('body').on('submit', '#jq_employer_reg_form', function (e) {
        e.preventDefault();

        var form = $(this);
        var valid = validate(form);

        if (valid) {
            let file_data = $('#fileToUpload').prop('files')[0];

            var form_data = new FormData(this);
            form_data.append('profile_pic', file_data);
            form_data.append('action', 'employer_signup');

            $.ajax({
                type: 'POST',
                url: MyAjax.ajaxurl,
                data: form_data,

                // dataType: "JSON",
                processData: false,
                contentType: false,

                success: function (data) {
                    var response = JSON.parse(data);
                    if (response.answer) {
                        window.location.href = response.url;
                    }
                    if (!response.login) {
                        let message = 'This name is already registered.';
                        form.find('#employer_company_name').addClass('warning');
                        form.find('#employer_company_name').siblings('span.notice.notice.warning').html(message);
                        display_errors(message);
                    }
                    if (!response.email) {
                        let message = 'This email is already registered.';
                        form.find('#employer_email').addClass('warning');
                        form.find('#employer_email').siblings('span.notice.notice.warning').html(message);
                        display_errors(message);
                    }

                    if (response.error) {
                        display_errors(response.error);
                    } else {
                        $('.display_errors').html('');
                    }
                },
                error: function (error) {
                    console.log('error: ', error);
                }
            });


        }
    });

    function display_errors(message) {
        let html = ' <div class="alert alert-danger alert-dismissible fade show" role="alert">\n' +
            '                                    <strong>' + message + '</strong>\n' +
            '                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">\n' +
            '                                        <span aria-hidden="true">×</span>\n' +
            '                                    </button>\n' +
            '                                </div>';
        $('.display_errors').html(html);
    }

    let emp_company_name = $('#employer_company_name');
    if (emp_company_name) {
        let searchResult = $('#searchResult');
        emp_company_name.on('keyup', function (e) {
            let search = $(this).val().trim(), form_data = new FormData();
            if (search == "") return;

            form_data.append('employer_company_name', search);
            form_data.append('action', 'check_employer_company_name');

            $.ajax({
                type: 'POST',
                url: MyAjax.ajaxurl,
                data: form_data,

                // dataType: "JSON",
                processData: false,
                contentType: false,

                success: function (data) {
                    let response = JSON.parse(data);
                    searchResult.empty();
                    for (let i = 0; i < response.length; i++) {
                        let name = response[i]['user_login'];
                        searchResult.append("<li>" + name + "</li>");
                    }

                    $("#searchResult li").bind("click", function () {
                        setCompanyNameText(this);
                    });
                },
                error: function (error) {
                    console.log('error: ', error);
                }
            });

        });

        // Set Text to search box and get details
        function setCompanyNameText(element) {
            emp_company_name.val($(element).text());
            searchResult.empty();
        }
    }


    function getCity(val) {
        $.ajax({
            type: "POST",
            url: "./ajax/get-city-ep.php",
            data: 'state_id=' + val,
            beforeSend: function () {
                $("#city-list").addClass("loader");
            },
            success: function (data) {
                $("#city-list").html(data);
                $("#city-list").removeClass("loader");
            }
        });
    }

});

function getState(val, type = 'states') {
    console.log(type);
    let elem_id = "#show_employer_company_";
    let state_city = elem_id + 'city';

    let remove_city = false;
    if (type == 'states') {
        state_city = elem_id + 'state';
        remove_city = $(elem_id + 'city');
    }else{
        type =  'cities';
    }
    form_data = new FormData();
    form_data.append('country_state_id', val);
    form_data.append('type', type);
    form_data.append('action', 'rh_get_state_city');

    $.ajax({
        type: "POST",
        url: MyAjax.ajaxurl,
        data: form_data,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $("#state-list").addClass("loader");
        },
        success: function (data) {
            console.log(state_city);
            $(state_city).html(data);
            if(remove_city){
                remove_city.find('option[value]').remove();
            }
            // $('#city-list').find('option[value]').remove();
            // $("#state-list").removeClass("loader");
        }
    });
}