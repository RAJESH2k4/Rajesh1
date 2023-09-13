
<div class="h-100 d-flex flex-column">
    <div class="col-12">
            <div class="hs-5 fw-bold flex-grow-1 col-auto pe-3">Welcome to Messaging Web App</div>
    </div>
    <hr>
    <div class="col-12 flex-grow-1">
        <div class="row h-100">
            <div class="col-md-4 h-100 d-flex flex-column">
                <div class="col-auto position-relative" id="search-area">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="search" id="search" class="form-control rounded-0" autocomplete="off" placeholder="Search...">
                            <button type="button" class="btn bg-transparent" style="margin-left: -40px; z-index: 100;" onclick="$('#search').val('').trigger('focus')">
                            <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <div class="w-100 position-absolute bg-light d-none rounded-0  shadow border border-dark" id="search-suggest">
                            <div class="list-group rounded-0" id="list-search-items">
                                
                            </div>
                            <div class="text-center  d-none" id="search-loader">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div class="text-center d-none" id="search-no-data">
                                Unkown User
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card shadow-lg rounded-0 flex-grow-1">
                    <div class="card-header  rounded-0 py-1 w-100 d-flex align-items-center bg-primary bg-gradient text-light">
                        <h5 class="card-title">
                            Conversation List
                        </h5>
                    </div>
                    <div class="card-body  rounded-0 overflow-auto">
                    <div class="list-group" id="convo_list">
                        <?php 
                        if(isset($_GET['eid'])){
                            $conn->query("UPDATE `messages` set status = 1 where to_user = '{$_SESSION['id']}' and md5(`from_user`) = '{$_GET['eid']}' ");
                        }
                        $convo_qry = $conn->query("SELECT *,concat(firstname,' ',lastname) as name FROM users where id in (SELECT to_user FROM messages where from_user = '{$_SESSION['id']}') or id in (SELECT from_user FROM messages where to_user = '{$_SESSION['id']}')");
                        $convo_list_arr = array();
                        while($row=$convo_qry->fetch_assoc()):
                            $last_message_qry = $conn->query("SELECT * FROM messages where (from_user = '{$_SESSION['id']}' and to_user = '{$row['id']}') or (to_user = '{$_SESSION['id']}' and from_user = '{$row['id']}') order by unix_timestamp(`date_created`) desc limit 1");
                            $last_message_qry = $last_message_qry->num_rows > 0 ? $last_message_qry->fetch_array() : array();
                            $row['last_message_id'] =isset($last_message_qry['from_user']) ? $last_message_qry['from_user'] : '';
                            $row['last_message'] =isset($last_message_qry['message']) ? $last_message_qry['message'] : '';
                            $row['last_message_created'] =isset($last_message_qry['message']) ? $last_message_qry['date_created'] : '';
                            $un_read = $conn->query("SELECT * FROM messages where to_user = '{$_SESSION['id']}' and from_user = '{$row['id']}' and status = '0' ")->num_rows;
                             $row['un_read'] = $un_read > 0 ? $un_read : '';
                            $convo_list_arr[strtotime($row['last_message_created'])] = $row;
                        endwhile;
                        $conn->query("UPDATE messages set popped = 1 where to_user = '{$_SESSION['id']}' ");
                        krsort($convo_list_arr);
                        ?>
                        <?php foreach($convo_list_arr as $row): ?>
                            <a href="./?eid=<?php echo md5($row['id']) ?>" class="list-group-item list-group-item-action list-item rounded-0 convo_with" data-id = '<?php echo $row['id'] ?>'>
                                <div class="w-100 d-flex position-relative">
                                    <div class="col-2 px-1">
                                        <img src="<?php echo is_file('./uploads/avatars/'.$row['id'].'.png') ? './uploads/avatars/'.$row['id'].'.png?v='.(!is_null($row['date_updated']) ? strtotime($row['date_updated']) : strtotime($row['date_created'])) : './images/no-image-available.png' ?>" class='user-search-avatar rounded-circle bg-light border border-dark' alt="">
                                        <span class="rounded-circle notif-count badge bg-danger"><?php echo $row['un_read'] ?></span>
                                    </div>
                                    <div class="col-10 lh-1">
                                        <div class='text-truncate' title="<?php echo $row['name'] . ' ' . $row['email'] ?>"><b class="user-name"><?php echo $row['name']?> <span class="text-muted search-user-email">(<?php echo $row['email'] ?>)</span> </b></div>
                                        <div class="text-muted search-user-email text-truncate last-message-field" title="<?php echo $row['last_message'] ?>"><?php echo ($row['last_message_id'] == $_SESSION['id'] ? 'You: ':'').$row['last_message'] ?></div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8 h-100" id="right-panel">
                <?php 
                $tmp_messages_arr = array();
                $messages_arr = array();
                $messages_ids = array();
                $convo_with = null;
                $message_offset= 0;
                $message_limit = 25;
                $eid = isset($_GET['eid']) ? $_GET['eid'] :'-1';
                $qry = $conn->query("SELECT *,CONCAT(firstname,' ',middlename,' ',lastname) as fullname FROM  `users` where md5(id) = '{$eid}' ");
                if($qry->num_rows > 0):
                    foreach($qry->fetch_array() as $k => $v){
                        $user_to[$k] = $v;
                    }
                $convo_with = $user_to['id'];
                $messages = $conn->query("SELECT * FROM `messages` where (from_user = '{$_SESSION['id']}' and to_user = '{$user_to['id']}') OR (to_user = '{$_SESSION['id']}' and from_user = '{$user_to['id']}') order by unix_timestamp(date_created) desc limit {$message_limit} offset {$message_offset}");
                while($row=$messages->fetch_assoc()){
                    foreach($row as $k=>$v){
                        $row[$k] = $conn->real_escape_string($v);
                    }

                    $tmp_messages_arr[] = $row;
                    $messages_ids[] = $row['id'];
                }
                $user_to_avatar = is_file('./uploads/avatars/'.$user_to['id'].'.png') ? './uploads/avatars/'.$user_to['id'].'.png?v='.(!is_null($user_to['date_updated']) ? strtotime($user_to['date_updated']) : strtotime($user_to['date_created'])) : './images/no-image-available.png';
                $user_to_name = $user_to['firstname'].' '.$user_to['lastname'];
                krsort($tmp_messages_arr);
                foreach($tmp_messages_arr as $values){
                    $messages_arr[]=$values;
                }
                ?>
                <div class="card h-100 shadow-lg rounded-0">
                    <div class="card-header d-flex w-100 align-items-center">
                        <div class="col-auto px-1">
                            <img src="<?php echo is_file('./uploads/avatars/'.$user_to['id'].'.png') ? './uploads/avatars/'.$user_to['id'].'.png?v='.(!is_null($user_to['date_updated']) ? strtotime($user_to['date_updated']) : strtotime($user_to['date_created'])) : './images/no-image-available.png' ?>" class="rounded-circle border border-dark view-avatar" alt="">
                        </div>
                        <div class="col-auto flex-grow-1 lh-1">
                            <div><b class="user-name"><?php echo $user_to['fullname'] ?></b></div>
                            <span class="text-muted search-user-email"><?php echo $user_to['email'] ?></span>
                        </div>
                        <div class="col-auto">
                            <a href="./?page=profile&user=<?php echo $_GET['eid'] ?>" class="text-decoration-none text-muted me-3" title="View Profile"><span class="fa fa-user-circle"></span></a>
                            <a href="./" class="btn-close" title="Close Conversation"></a>
                        </div>
                    </div>
                    <!-- <div class="d-flex flex-column h-100 w-100 align-items-center justify-content-center">
                        <div style="font-size:9rem" class="text-muted"><span class="fa fa-comments"></span></div>
                        <div class="text-muted">Start a Conversation Now</div>
                    </div> -->
                    <div class="position-relative col-auto" id="prev-loader-holder">
                        <div class="position-absolute d-none" id="prev-loader">
                            <div class="d-flex justify-content-center">
                                <div class="spinner-grow spinner-grow-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column h-100 w-100">
                        <div class="col-auto flex-grow-1 px-1 py-1" id="">
                            <div id="convo-field">
                                <div class="" id="convo-box">
                                    
                                </div>
                                <div class="d-none" id="end-prev-data">
                                    <center><small class='text-muted'>You've reach the top messages.</small></center>
                                </div>
                            </div>
                        </div>
                    <div class="position-relative col-auto" id="scroll-bottom-holder">
                        <div class="position-absolute d-none" id="scroll-bottom">
                            <a href="javascript:void(0)" class="bg-info bg-gradient text-center text-decoration-none text-light">New Unread Message <i class="fa fa-angle-down"></i></a>
                        </div>
                    </div>
                        <div class="col-auto bg-light bg-gradient border-top px-1 py-1" id="message-form-holder">
                            <form action="" id="message-form">
                                <input type="hidden" name="user_to" value='<?php echo $user_to['id'] ?>'>
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                    </div>
                                    <div class="col-auto flex-grow-1 mx-0">
                                        <textarea name="message" autofocus id="message"rows="3" class="form-control"></textarea>
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-primary">Send</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="card h-100 shadow-lg rounded-0">
                    <div class="d-flex flex-column h-100 w-100 align-items-center justify-content-center">
                        <div style="font-size:9rem" class="text-muted"><span class="fa fa-comments"></span></div>
                        <div class="text-muted">Start a Conversation Now</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    </div>
</div>
<div id="bubble_clone" class="d-none">
    <div class="w-100 bubble-from d-flex align-items-start my-2">
        <div class="col-auto bubble bg-gradient"></div>
        <div class="col-2 px-2"><a href="javascript:void(0)" class="text-muted delete-message"><div class="fa fa-trash-alt"></div></a></div>
    </div>
    <div class="w-100 bubble-to d-flex align-items-start my-2">
        <div class="col-auto bubble bg-gradient"></div>
        <div class="col-2"></div>
    </div>
</div>
<div id="user-item-clone" class="d-none">
    <a href="#" class="list-group-item list-group-item-action list-item rounded-0">
        <div class="w-100 d-flex">
            <div class="col-auto px-1">
                <img src="./images/no-image-available.png" class='user-search-avatar rounded-circle bg-light border border-dark' alt="">
            </div>
            <div class="flex-grow-1 lh-1">
                <div><b class="user-name"></b></div>
                <span class="text-muted search-user-email"></span>
            </div>
        </div>
    </a>
</div>
<div id="convo-user-clone" class=" d-none">
<a href="#" class="list-group-item list-group-item-action list-item rounded-0 convo_with" data-id = ''>
    <div class="w-100 d-flex">
        <div class="col-auto px-1">
            <img src="" class='user-search-avatar rounded-circle bg-light border border-dark' alt="">
            <span class="rounded-circle notif-count badge bg-danger"></span>
        </div>
        <div class="flex-grow-1 lh-1">
            <div class='text-truncate'><b><span class="user-name"></span><span class="text-muted search-user-email email-field"></span> </b></div>
            <div class="text-muted search-user-email last-message-field text-truncate"></div>
        </div>
    </div>
</a>
</div>
<script>
    var message_limit = '<?php echo $message_limit ?>';
    var message_offset = '<?php echo $message_offset ?>';
    var messages = $.parseJSON('<?php echo json_encode($messages_arr) ?>');
    var mids = $.parseJSON('<?php echo json_encode($messages_ids) ?>');
    var last_id = mids[0]
    var messageInterval;
    var nmInterval;
    var deleteInterval;
    function search_user($keyword){
        $('#list-search-items').html('')
        $('#search-loader').removeClass('d-none')
        $.ajax({
            url:'Actions.php?a=find_user',
            method:'POST',
            data:{'keyword':$keyword},
            dataType:'json',
            error:err=>{
                console.log(err)
            },
            success:function(resp){
                if(resp.length > 0){
                    Object.keys(resp).map((k)=>{
                        var list = $('#user-item-clone .list-item').clone()
                        list.attr('href','./?eid='+resp[k].id)
                        list.find('.user-name').text(resp[k].name)
                        list.find('.search-user-email').text(resp[k].email)
                        list.find('.user-search-avatar').attr('src',resp[k].avatar)
                        $('#list-search-items').append(list)
                    })
                    $('#search-no-data').addClass('d-none')
                }else{
                    $('#search-no-data').removeClass('d-none')
                }
            },
            complete:()=>{
                $('#search-loader').addClass('d-none')
            }
        })
    }
    window.addEventListener("resize", function(){
        $('#convo-field').height('100%')
        $('#convo-field').height($('#right-panel').height() - $('#right-panel .card-header').height() - $('#message-form-holder').height()-50)
    });
    if($("#convo-field").length > 0){
    document.getElementById('convo-field').addEventListener('scroll',function(){
        if($('#convo-field').get(0).scrollTop > -30 && $('#scroll-bottom').hasClass('d-none') == false){
            $('#scroll-bottom').addClass('d-none')
        }
        if(Math.abs($('#convo-field').get(0).scrollTop) + $('#convo-field').get(0).offsetHeight + 1 >= $('#convo-field').get(0).scrollHeight && $('#prev-loader').hasClass('d-none') == true && $('#end-prev-data').hasClass('d-none') == true){
            $('#prev-loader').removeClass('d-none')
            message_offset = parseFloat(message_limit) + parseFloat(message_offset)
            setTimeout(() => {
            var convo_with = '<?php echo $convo_with ?>'
            $.ajax({
                url:'Actions.php?a=get_prev_messages',
                method:'POST',
                data:{message_offset:message_offset,message_limit:message_limit,convo_with:convo_with},
                dataType:'json',
                error:err=>{
                    console.log(err)
                    $('#prev-loader').addClass('d-none')
                },
                success:function(resp){
                    if(resp.length > 0){
                        var sid = '<?php echo $_SESSION['id'] ?>';
                        var process = new Promise((resolve)=>{
                            Object.keys(resp).map(k=>{
                                if(resp[k].from_user == sid){
                                    var bubble = $('#bubble_clone .bubble-from').clone()
                                }else{
                                    var bubble = $('#bubble_clone .bubble-to').clone()
                                }
                                bubble.find('.bubble').text(resp[k].message)
                                $('#convo-box').prepend(bubble)
                            })
                            resolve()
                        }) 
                        process.then(()=>{
                        $('#convo-field').animate({scrollTop:$('#convo-field').get(0).scrollTop - 150},'fast')
                        $('#prev-loader').addClass('d-none')
                       })
                        
                    }else{
                        $('#end-prev-data').removeClass('d-none')
                        $('#prev-loader').addClass('d-none')
                    }
                }
            })
        }, 1500);
        }else{
            // console.log('Top false')
        }
    })
}
function delete_message($id =''){
    var _conf = confirm('Are you sure to delete this message?')
    if(_conf == true){
        $.ajax({
            url:'Actions.php?a=delete_message',
            method:'POST',
            data:{id:$id},
            dataType:'json',
            error:err=>{
                console.log(err)
                alert('Deleting Message Failed due to error occured while processing the action.')
            },
            success:function(resp){
                if(resp.status == 'success'){
                    $('.bubble-from[data-id="'+$id+'"] .bubble').addClass('deleted').text('This message has been deleted')
                    $('.bubble-from[data-id="'+$id+'"]').find('.delete-message').remove()
                    $('.bubble-from[data-id="'+$id+'"]').removeAttr('data-id')
                }else if(!!resp.err){
                alert('Deleting Message Failed due to error occured while processing the action. Error: '+resp.err)
                }else{
                alert('Deleting Message Failed due to error occured while processing the action.')
                }
            }
        })
    }
}
    $(function(){
        $('#scroll-bottom>a').click(function(){
            $('#convo-field').animate({scrollTop:1},'fast')
        })
        $('#convo-field').height($('#right-panel').height() - $('#right-panel .card-header').height() - $('#message-form-holder').height() - 50)
        Object.keys(messages).map(k=>{
            var sid = '<?php echo $_SESSION['id'] ?>';
            if(messages[k].from_user == sid){
                var bubble = $('#bubble_clone .bubble-from').clone()
            }else{
                var bubble = $('#bubble_clone .bubble-to').clone()
            }
            if(messages[k].delete_flag == 1){
                bubble.find('.bubble').addClass('deleted').text('This message has been deleted')
                bubble.find('.delete-message').remove()
            }else{
                bubble.attr('data-id',messages[k].id)
                bubble.find('.bubble').html((messages[k].message).replace('\r','<br>'))
            }
            $('#convo-box').append(bubble)
            bubble.find('.delete-message').click(function(){
                delete_message(messages[k].id)
            })
        })
        deleteInterval = setInterval(() => {
            var ids_arr = []
            $('.bubble-from,.bubble-to').each(function(){
                if($(this).attr('data-id') != undefined){
                    ids_arr.push($(this).attr('data-id'))
                }
            })
            var ids = ids_arr.join(',')
            $.ajax({
                url:'Actions.php?a=check_deleted',
                method:'POST',
                data:{ids:ids},
                dataType:'json',
                error:err=>{
                    console.log(err)
                    clearInterval(deleteInterval)
                },
                success:function(resp){
                    if(resp.length > 0){
                        Object.keys(resp).map(k=>{
                            $('.bubble-to[data-id="'+resp[k]+'"] .bubble').addClass('deleted').text('This message has been deleted')
                            $('.bubble-to[data-id="'+resp[k]+'"]').find('.delete-message').remove()
                            $('.bubble-to[data-id="'+resp[k]+'"]').removeAttr('data-id')
                        })
                    }
                }
            })
        }, 750);
        messageInterval =setInterval(() => {
            var convo_with = '<?php echo $convo_with ?>'
            $.ajax({
                url:'Actions.php?a=get_messages',
                method:'POST',
                data:{last_id:last_id,convo_with:convo_with},
                dataType:'json',
                error:err=>{
                    console.log(err)
                },
                success:function(resp){
                    if(resp.length > 0){
                        var sid = '<?php echo $_SESSION['id'] ?>';
                        Object.keys(resp).map(k=>{
                            if(resp[k].from_user == sid){
                                var bubble = $('#bubble_clone .bubble-from').clone()
                            }else{
                                var bubble = $('#bubble_clone .bubble-to').clone()
                            }
                            bubble.attr('data-id',resp[k].id)
                            if(resp[k].delete_flag == 1){
                                bubble.find('.bubble').addClass('deleted').text('This message has been deleted')
                                bubble.find('.delete-message').remove()
                            }else{
                                bubble.find('.bubble').html((resp[k].message).replace('\r','<br>'))
                            }
                            $('#convo-box').append(bubble)
                            bubble.find('.delete-message').click(function(){
                                delete_message(resp[k].id)
                            })
                                last_id = resp[k].id
                            resp[k].message = (resp[k].from_user == sid) ? "You: "+ resp[k].message : resp[k].message;
                            if($('.convo_with[data-id="'+convo_with+'"]').length >0){
                                var convo = $('.convo_with[data-id="'+convo_with+'"]').clone()
                                convo.find('.last-message-field').text(resp[k].message)
                                $('.convo_with[data-id="'+convo_with+'"]').remove()
                                $('#convo_list').prepend(convo)
                            }else{
                                var convo = $('#convo-user-clone .convo_with').clone()
                                convo.attr('href',"./?eid=<?php echo isset($_GET['eid']) ? $_GET['eid'] : '' ?>")
                                convo.attr('data-id','<?php echo isset($user_to['id']) ? $user_to['id'] :'' ?>')
                                convo.find('.last-message-field').text(resp[k].message)
                                convo.find('.user-search-avatar').attr('src','<?php echo isset($user_to_avatar) ? $user_to_avatar :'' ?>')
                                convo.find('.user-name').text('<?php echo isset($user_to_name) ? $user_to_name :'' ?>')
                                convo.find('.email-field').text('<?php echo isset($user_to['email']) ? $user_to['email'] :'' ?>')
                                $('#convo_list').prepend(convo)
                            }
                        })

                        if($('#convo-field').get(0).scrollTop < -200 && $('#scroll-bottom').hasClass('d-none') == true){
                            $('#scroll-bottom').removeClass('d-none')
                        }else{
                            $('#convo-field').animate({scrollTop:1},'fast')
                        }
                    }
                }
            })
        }, 750);
        nmInterval = setInterval(() => {
            $.ajax({
                url:'Actions.php?a=get_unread',
                data:{eid:'<?php echo isset($_GET['eid']) ? $_GET['eid'] : '' ?>'},
                method:'POST',
                dataType:'json',
                error:err=>{
                    console.log(err)
                    clearInterval(nmInterval)
                },
                success:function(resp){
                    if(resp.length > 0){
                        Object.keys(resp).map(k=>{
                            var convo_with = resp[k].convo_with
                            if($('.convo_with[data-id="'+convo_with+'"]').length >0){
                                var convo = $('.convo_with[data-id="'+convo_with+'"]').clone()
                                convo.find('.last-message-field').text(resp[k].message)
                                convo.find('.notif-count').text(resp[k].un_read > 0 ? resp[k].un_read : '')

                                $('.convo_with[data-id="'+convo_with+'"]').remove()
                                $('#convo_list').prepend(convo)
                            }else{
                                var convo = $('#convo-user-clone .convo_with').clone()
                                convo.attr('href',"./?eid="+resp[k].eid)
                                convo.attr('data-id',resp[k].convo_with)
                                convo.find('.notif-count').text(resp[k].un_read > 0 ? resp[k].un_read : '')
                                convo.find('.last-message-field').text(resp[k].message)
                                convo.find('.user-search-avatar').attr('src',resp[k].avatar)
                                convo.find('.user-name').text(resp[k].name)
                                convo.find('.email-field').text(resp[k].email)
                                $('#convo_list').prepend(convo)
                            }
                        })
                    }
                }
            })
        }, 750);
        $('#search').on('focus input',function(){
            if($(this).val() != ''){
                $('#search-suggest').removeClass('d-none')
                search_user($(this).val())
            }else{
                $('#search-suggest').addClass('d-none')
                $('#list-search-items').html('')
            }
        })
        $(document).click((e)=>{
            if($('#search-suggest').hasClass('d-none') != true && document.querySelector('#search-area').contains(e.target) == false)
            $('#search-suggest').addClass('d-none');
        })

        $('#message-form').submit(function(e){
            e.preventDefault();
            $('.pop_msg').remove()
            var _this = $(this)
            var _el = $('<div>')
                _el.addClass('pop_msg')
            _this.find('button').attr('disabled',true)
            _this.find('button[type="submit"]').text('Sending...')
            $.ajax({
                url:'./Actions.php?a=send_message',
                method:'POST',
                data:$(this).serialize(),
                dataType:'JSON',
                error:err=>{
                    console.log(err)
                   alert('An error occured')
                    _this.find('button[type="submit"]').text('Send')
                    _this.find('button[type="submit"]').attr('disabled',false)
                    $('#page-container,html,body').animate({scrollTop:0},'fast')
                },
                success:function(resp){
                    if(resp.status == 'success'){
                        _this.get(0).reset()
                        $('#message').trigger('focus')
                    }else{
                        _el.addClass('alert alert-danger')
                    }
                    $('#convo-field').animate({scrollTop:1},'fast')

                    
                    _this.find('button[type="submit"]').text('Send')
                    _this.find('button[type="submit"]').attr('disabled',false)
                }
            })
        })
        $('#message').on('keydown',function(e){
            if(e.which == 13 && e.shiftKey==false){
                e.preventDefault()
                $('#message-form').submit()
                // console.log(e)
            }
        })
    })
</script>