<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <!-- Favicons -->
    <link href="{{ asset('frontend/') }}/assets/img/logo.png" rel="icon">
    <link href="{{ asset('frontend/') }}/assets/img/logo.png" rel="apple-touch-icon">
    <!-- Favicons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/country-select-js/2.0.1/css/countrySelect.min.css" /> -->
    <link href="{{ asset('frontend/assets/css/jquery-countryselector.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">


    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap');

        body {
            font-size: 16px;
            font-family: "Roboto", sans-serif;
            background-color: #F9F7F0;
            color: #1A2B4B;
        }

        .custom-btn {
            color: #1A2B4B;
            background-color: #C5A86D;
            border-color: #C5A86D;
            border-radius: 999px;
            padding: 15px 24px;
            font-weight: 600;

        }

        .custom-btn:hover {
            background-color: #1A2B4B;
            color: #FFF;
            border-color: #1A2B4B;
        }

        /* Progress Bar */
        .progress-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .progress {
            height: 4px;
            background-color: #e5e0d5;
            max-width: 100%;
            margin: auto;
        }

        .progress-container .title {
            font-weight: 500;
            font-size: 26px;
        }

        @media (max-width:576px) {
            .progress {
                max-width: 100%;
            }
        }

        .progress-bar {
            background-color: #C5A86D;
        }

        /* Progress Bar */
        .step-one h3 {
            font-size: 32px;
            font-weight: 600;
            color: #1A2B4B;
        }

        .schoolbox.active {
            border: 1px solid #C5A86D;
        }

        /* Form */
        label {
            margin-bottom: 10px;
        }

        input,
        select {
            padding: 15px 24px !important;
            border-radius: 8px !important;
            background-color: #ffffff !important;
            color: #1A2B4B !important;
            border: 1px solid #ddd8cc !important;
        }

        .form-select {
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%231A2B4B' viewBox='0 0 16 16'%3e%3cpath d='M7.247 11.14l-4.796-5.481C2.071 5.253 2.522 4.5 3.2 4.5h9.6c.678 0 1.129.753.749 1.159l-4.796 5.481a1 1 0 0 1-1.506 0z'/%3e%3c/svg%3e") !important;
        }

        .iti--separate-dial-code {
            width: 100%;
        }

        .phone-input {
            padding-left: 85px !important;
        }

        /* Custom Checkbox Design */
        .custom-check {
            position: relative;
            display: inline-block;
            cursor: pointer;
            font-size: 16px;
            padding-left: 35px;
            user-select: none;
            color: #000;
            font-weight: 700;
        }

        .custom-check input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .custom-checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 26px;
            width: 26px;
            background-color: #fff;
            border: 2px solid #ae9a66;
            border-radius: 4px;
            /* চাইলে গোল করে নিতে পারো */
            transition: all 0.3s ease-in-out;
        }

        .custom-check input:checked~.custom-checkmark {
            background-color: #ae9a66;
            border-color: #ae9a66;
        }

        .custom-checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .custom-check input:checked~.custom-checkmark:after {
            display: block;
        }

        .custom-check .custom-checkmark:after {
            left: 8px;
            top: 2px;
            width: 8px;
            height: 12px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }





        /* Tanvir  */
        .custom-radio {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            cursor: pointer;
            width: 20px;
            height: 20px;
            border-radius: 50% !important;
            border: 1px solid #AE9A66 !important;
            position: relative;
            vertical-align: middle;
            transition: all 0.2s ease-in-out;
            padding: 0px !important;
        }


        /* Checked হলে */
        .custom-radio:checked {
            background-color: #AE9A66 !important;
            border-color: #AE9A66 !important;
        }


        .custom-chekhbox {
            width: 20px;
            height: 20px;
            background-color: #AE9A66 !important;
            border-color: #AE9A66 !important;
        }

        .error {
            color: #ae9a66;
        }

        .is-invalid {
            border: 2px solid #ae9a66 !important;
            /* লাল */
        }

        .is-valid {
            border: 2px solid #183E77 !important;
            /* সবুজ */
        }



        .custom-checks {
            position: relative;
            display: inline-block;
            cursor: pointer;
            font-size: 14px;
            padding-left: 35px;
            user-select: none;
            color: #6c757d;
            font-weight: 700;
        }

        .custom-checks input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .custom-checkmarks {
            position: absolute;
            top: 0;
            left: 0;
            height: 26px;
            width: 26px;
            background-color: #fff;
            border: 2px solid #ae9a66;
            border-radius: 4px;
            /* চাইলে গোল করে নিতে পারো */
            transition: all 0.3s ease-in-out;
        }

        .custom-checks input:checked~.custom-checkmarks {
            background-color: #ae9a66;
            border-color: #ae9a66;
        }

        .custom-checkmarks:after {
            content: "";
            position: absolute;
            display: none;
        }

        .custom-checks input:checked~.custom-checkmarks:after {
            display: block;
        }

        .custom-checks .custom-checkmarks:after {
            left: 8px;
            top: 2px;
            width: 8px;
            height: 12px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .subject-badge.active {
            background-color: #183E77 !important;
        }

        /* Phone */
        .select2-container--default .select2-selection--single {
            -webkit-appearance: none !important;
            appearance: none !important;
            background-color: #ffffff !important;
            border: 1px solid #ddd8cc !important;
            border-radius: 8px !important;
            color: #1A2B4B !important;
            height: 50px !important;
            letter-spacing: -.03125rem !important;
            padding: 12px 24px !important;
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1A2B4B;
        }

        .iti {
            display: block !important;
        }

        .iti input {
            padding-left: 95px !important;
        }

        .iti input:focus {
            padding-left: 95px !important;
        }

        .iti--separate-dial-code .iti__selected-dial-code {
            color: #1A2B4B;
        }

        .iti__country {
            color: #000 !important;
        }
    </style>
    @yield('css')
</head>

<body style="min-height: 100vh;background-color:#F9F7F0;">
    <a href="{{ route('form.step',1) }}" class="logo d-flex align-items-center m-auto" style="background: #ffffff;padding-top:10px;padding-bottom:10px;border-bottom:1px solid #e5e0d5;">
        <img src="{{ asset('frontend/') }}/assets/img/logo.png" alt="" width="70" style="margin:auto;">
    </a>
    @yield('student')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/country-select-js/2.0.1/js/countrySelect.min.js"></script> -->
    <script src="{{ asset('frontend/assets/js/jquery.countrySelector.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/js/select2.full.min.js"></script>

    <script>
        $(document).ready(function() {

            // Initialize form validation
            var validator = $("#myForm").validate({
                ignore: [], // hidden fields সহ সব validate হবে
                errorElement: "span",
                errorClass: "text-warning small mt-1 d-block",
                highlight: function(element) {
                    $(element).addClass("is-invalid").removeClass("is-valid");
                },
                unhighlight: function(element) {
                    $(element).removeClass("is-invalid").addClass("is-valid");
                },
                errorPlacement: function(error, element) {
                    error.insertAfter(element);
                }
            });

            // Auto-detect all required fields
            $("#myForm").find("input[required], select[required], textarea[required]").each(function() {
                var fieldName = $(this).attr("name");
                if (!fieldName) return;

                if (!validator.settings.rules[fieldName]) {
                    validator.settings.rules[fieldName] = {
                        required: true
                    };

                    // Custom message
                    validator.settings.messages[fieldName] = "This field is required*";
                }

                // Special case for confirm password
                if ($(this).attr("name") === "conf_password") {
                    validator.settings.rules[fieldName].equalTo = "#password";
                    validator.settings.messages[fieldName] = "Password and confirm password do not match*";
                }

                // Special case for email
                if ($(this).attr("type") === "email") {
                    validator.settings.rules[fieldName].email = true;
                    validator.settings.messages[fieldName] = "Please enter a valid email address*";
                }
            });

        });
    </script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>

    @yield('script')
</body>

</html>