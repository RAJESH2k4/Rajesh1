<?php
session_start();
if(isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0){
    header("Location:./");
    exit;
}
require_once('./DBConnection.php');
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Messaging Web Application</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/custom.css">
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/popper.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./js/script.js"></script>
    <style>
        html, body{
            height:100%;
        }
        #logo-img{
            width:75px;
            height:75px;
            object-fit:scale-down;
            background : var(--bs-light);
            object-position:center center;
            border:1px solid var(--bs-dark);
            border-radius:50% 50%;
        }
    </style>
</head>
<body class="bg-light bg-gradient">
   <div class="h-100 d-flex jsutify-content-center align-items-center">
       <div class='w-100'>
        <h3 class="py-5 text-center">Messaging Web Application - User Registration</h3>
        <div class="card my-3 col-md-8 offset-md-2">
            <div class="card-body">
                <form action="" id="register-form">
                    <input type="hidden" name="id" value="0">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="lastname" class="control-label">Last Name</label>
                            <input type="text" id="lastname" autofocus name="lastname" class="form-control form-control-sm rounded-0" required>
                        </div>
                        <div class="col-md-4">
                            <label for="firstname" class="control-label">First Name</label>
                            <input type="text" id="firstname" name="firstname" class="form-control form-control-sm rounded-0" required>
                        </div>
                        <div class="col-md-4">
                            <label for="middlename" class="control-label">Middle Name</label>
                            <input type="text" id="middlename" name="middlename" class="form-control form-control-sm rounded-0" placeholder = "(optional)">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender" class="control-label">Gender</label>
                                <select type="text" id="gender" name="gender" class="form-control form-control-sm rounded-0" required>
                                    <option>Male</option>
                                    <option>Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="contact" class="control-label">Contact #</label>
                                <input type="text" id="contact" pattern="[0-9+/s//]+" name="contact" class="form-control form-control-sm rounded-0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dob" class="control-label">Date of Birth</label>
                                <input type="date" id="dob" name="dob" class="form-control form-control-sm rounded-0" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="control-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control form-control-sm rounded-0" required>
                    </div>
                    <div class="form-group">
                        <label for="password" class="control-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control form-control-sm rounded-0" required>
                    </div>
                    <div class="form-group">
                        <label for="cpassword" class="control-label">Confirm Password</label>
                        <input type="password" id="cpassword" name="cpassword" class="form-control form-control-sm rounded-0" required>
                    </div>
                    <div class="form-group">
                        <label for="avatar" class="control-label">User Avatar</label>
                        <input type="file" name="avatar" id="avatar" required class="form-control form-control-sm rounded-0" accept="image/png,image/jpeg" <?php echo !isset($employee_id) ? 'required' : '' ?> onchange="display_img(this)">
                    </div>
                    <div class="form-group text-center mt-2">
                        <img src="././images/no-image-available.png" id="logo-img" alt="Avatar">
                    </div>
                    <div class="form-group d-flex w-100 justify-content-between">
                        <a href="./">Already has an Account?</a>
                        <button class="btn btn-sm btn-primary rounded-0 my-1">Save</button>
                    </div>
                </form>
            </div>
        </div>
       </div>
   </div>
</body>
<script>
    function display_img(input){
        if (input.files && input.files[0]) {
	        var reader = new FileReader();
	        reader.onload = function (e) {
	        	$('#logo-img').attr('src', e.target.result);
	        }

	        reader.readAsDataURL(input.files[0]);
	    }
    }
    $(function(){
        $('#register-form').submit(function(e){
            e.preventDefault();
            $('.pop_msg').remove()
            $('#password, #cpassword').removeClass('border-danger border-success')
            $('.err_msg').remove()
            if($('#password').val() != $('#cpassword').val()){
                $('#password, #cpassword').addClass('border-danger')
                $('#cpassword').after('<small class="text-danger err_msg">Password doesn\'t match</small>')
                return false;
            }
            var _this = $(this)
            var _el = $('<div>')
                _el.addClass('pop_msg')
            _this.find('button').attr('disabled',true)
            _this.find('button[type="submit"]').text('Saving data...')
            $.ajax({
                url:'././Actions.php?a=save_user',
                data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
                error:err=>{
                    console.log(err)
                    _el.addClass('alert alert-danger')
                    _el.text("An error occurred.")
                    _this.prepend(_el)
                    _el.show('slow')
                    _this.find('button').attr('disabled',false)
                    _this.find('button[type="submit"]').text('Save')
                },
                success:function(resp){
                    if(resp.status == 'success'){
                        _el.addClass('alert alert-success')
                        setTimeout(() => {
                            location.replace('./');
                        }, 2000);
                    }else{
                        _el.addClass('alert alert-danger')
                    }
                    _el.text(resp.msg)

                    _el.hide()
                    _this.prepend(_el)
                    _el.show('slow')
                    _this.find('button').attr('disabled',false)
                    _this.find('button[type="submit"]').text('Save')
                }
            })
        })
    })
</script>
</html>