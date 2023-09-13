<style>
    #logo-img{
        width:200px;
        height:200px;
    }
</style>
<div class="col-md-8 offset-2">
    <div class="card shadow-lg">
        <div class="card-header rounded-0">
            <div class="card-title">Manage Account Information & Credentials</div>
        </div>
        <div class="card-body">
            <form action="" class="" id="update-account">
                <div class="form-group row">
                    <div class="col-md-4">
                        <label for="lastname" class="control-label">Last Name</label>
                        <input type="text" id="lastname" autofocus name="lastname" class="form-control form-control-sm rounded-0" required value="<?php echo $_SESSION['lastname'] ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="firstname" class="control-label">First Name</label>
                        <input type="text" id="firstname" name="firstname" class="form-control form-control-sm rounded-0" required value="<?php echo $_SESSION['firstname'] ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="middlename" class="control-label">Middle Name</label>
                        <input type="text" id="middlename" name="middlename" class="form-control form-control-sm rounded-0" placeholder = "(optional)" value="<?php echo $_SESSION['middlename'] ?>">
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
                            <input type="text" id="contact" pattern="[0-9+/s//]+" name="contact" class="form-control form-control-sm rounded-0" required value="<?php echo $_SESSION['contact'] ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dob" class="control-label">Date of Birth</label>
                            <input type="date" id="dob" name="dob" class="form-control form-control-sm rounded-0" required value="<?php echo $_SESSION['dob'] ?>">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="control-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control form-control-sm rounded-0" required value="<?php echo $_SESSION['email'] ?>">
                </div>
                <div class="form-group">
                    <label for="password" class="control-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control form-control-sm rounded-0" >
                </div>
                <div class="form-group">
                    <label for="cpassword" class="control-label">Confirm Password</label>
                    <input type="password" id="cpassword" class="form-control form-control-sm rounded-0" >
                    <small class="text-info"><i>Leave Password and Confirm Password fields blank if you don't want to change you password</i></small>
                </div>
                <div class="form-group">
                    <label for="old_password" class="control-label">Old Password</label>
                    <input type="password" id="old_password" name="old_password" class="form-control form-control-sm rounded-0" required>
                </div>
                <div class="form-group">
                    <label for="avatar" class="control-label">User Avatar</label>
                    <input type="file" name="avatar" id="avatar" class="form-control form-control-sm rounded-0" accept="image/png,image/jpeg" onchange="display_img(this)">
                </div>
                <div class="form-group text-center mt-2">
                    <img src="<?php echo is_file('./uploads/avatars/'.$_SESSION['id'].'.png') ? './uploads/avatars/'.$_SESSION['id'].'.png?v='.(is_null($_SESSION['date_updated']) ? strtotime($_SESSION['date_created']) : strtotime($_SESSION['date_updated'])) : './images/no-image-available.png' ?>" id="logo-img" alt="Avatar">
                </div>
                <div class="form-group d-flex w-100 justify-content-end">
                    <button class="btn btn-sm btn-primary rounded-0 my-1">Update Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function display_img(input) {
	    if (input.files && input.files[0]) {
	        var reader = new FileReader();
	        reader.onload = function (e) {
	        	$('#logo-img').attr('src', e.target.result);
	        }

	        reader.readAsDataURL(input.files[0]);
	    }
	}
    $(function(){
        $('#update-account').submit(function(e){
            e.preventDefault();
            $('.pop_msg').remove()
            var _this = $(this)
            var _el = $('<div>')
                _el.addClass('pop_msg')
            _this.find('button').attr('disabled',true)
            _this.find('button[type="submit"]').text('Updating...')
            $.ajax({
                url:'./Actions.php?a=update_credentials',
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
                    $('#page-container,html,body').animate({scrollTop:0},'fast')
                },
                success:function(resp){
                    if(resp.status == 'success'){
                        location.href='./?page=profile';
                    }else{
                        _el.addClass('alert alert-danger')
                    }
                    _el.text(resp.msg)

                    _el.hide()
                    _this.prepend(_el)
                    _el.show('slow')
                    $('#page-container,html,body').animate({scrollTop:0},'fast')
                    _this.find('button').attr('disabled',false)
                    _this.find('button[type="submit"]').text('Save')
                }
            })
        })
    })
</script>