<?php 
session_start();
require_once('DBConnection.php');

Class Actions extends DBConnection{
    function __construct(){
        parent::__construct();
    }
    function __destruct(){
        parent::__destruct();
    }
    function login(){
        extract($_POST);
        $sql = "SELECT * FROM users where email = '{$email}' and `password` = '".md5($password)."' ";
        @$qry = $this->conn->query($sql)->fetch_array();
        if(!$qry){
            $resp['status'] = "failed";
            $resp['msg'] = "Invalid email or password.";
        }else{
            $resp['status'] = "success";
            $resp['msg'] = "Login successfully.";
            foreach($qry as $k => $v){
                if(!is_numeric($k))
                $_SESSION[$k] = $v;
            }
        }
        return json_encode($resp);
    }
    function logout(){
        session_destroy();
        header("location:./");
    }
    function save_user(){
        if(isset($_POST['cpassword']))
        unset($_POST['cpassword']);
        
        if(isset($_POST['password']) && !empty($_POST['password']))
        $_POST['password'] = md5($_POST['password']);
        if(isset($_POST['password']) && empty($_POST['password']))
        unset($_POST['password']);

        extract($_POST);
        $data = "";

        foreach($_POST as $k => $v){
            if(!in_array($k,array('id'))){
                if(!empty($data)) $data .= ",";
                $v = $this->conn->real_escape_string($v);
                $data .= " `{$k}` = '{$v}' ";
            }
        }
       
        @$check= $this->conn->query("SELECT * FROM users where `email` = '{$email}' ".($id > 0 ? " and id != '{$id}' " : ""))->num_rows;
        if(@$check> 0){
            $resp['status'] = 'failed';
            $resp['msg'] = "Email already exists.";
        }else{
            if(empty($id)){
                $sql = "INSERT INTO `users` set {$data}";
            }else{
                $sql = "UPDATE `users` set {$data} where user_id = '{$id}'";
            }
            @$save = $this->conn->query($sql);
            if($save){
                $resp['status'] = 'success';
                if(empty($id)){
                    $resp['msg'] = 'New User successfully saved.';
                    $user_id = $this->conn->insert_id;
                }else{
                    $resp['msg'] = 'User Details successfully updated.';
                    $user_id = $id;
                }
               if(isset($_FILES['avatar']['tmp_name']) && !empty($_FILES['avatar']['tmp_name'])){
                $dir_path = __DIR__.'/uploads/avatars/';
                if(!is_dir($dir_path))
                    mkdir($dir_path);
                $fname = $dir_path.$user_id.'.png';
                $upload = $_FILES['avatar']['tmp_name'];
                   $type = mime_content_type($upload);
                   $allowed = array('image/png','image/jpeg');
                   if(!in_array($type,$allowed)){
                       $resp['msg'].=" But Image failed to upload due to invalid file type.";
                   }else{
                        $new_height = 200; 
                        $new_width = 200; 
                
                        list($width, $height) = getimagesize($upload);
                        $t_image = imagecreatetruecolor($new_width, $new_height);
                        $gdImg = ($type == 'image/png')? imagecreatefrompng($upload) : imagecreatefromjpeg($upload);
                        imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                        if($gdImg){
                                if(is_file($fname))
                                unlink($fname);
                                imagepng($t_image,$fname);
                                imagedestroy($gdImg);
                                imagedestroy($t_image);
                        }else{
                        $resp['msg'].=" But Image failed to upload due to unkown reason.";
                        }
                   }
               }
            
            }else{
                $resp['status'] = 'failed';
                $resp['msg'] = 'Saving User Details Failed. Error: '.$this->conn->error;
                $resp['sql'] =$sql;
            }
        }
        return json_encode($resp);
    }
    function delete_user(){
        extract($_POST);

        @$delete = $this->conn->query("DELETE FROM `users` where id = '{$id}'");
        if($delete){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'User successfully deleted.';
        }else{
            $resp['status']='failed';
            $resp['error']=$this->lastErrorMsg();
        }
        return json_encode($resp);
    }
    function update_credentials(){
        extract($_POST);
        $data = "";
        foreach($_POST as $k => $v){
            if(!in_array($k,array('id','old_password')) && !empty($v)){
                if(!empty($data)) $data .= ",";
                if($k == 'password') $v = md5($v);
                $data .= " `{$k}` = '{$v}' ";
            }
        }
        if(!empty($password) && md5($old_password) != $_SESSION['password']){
            $resp['status'] = 'failed';
            $resp['msg'] = "Old password is incorrect.";
        }else{
            $sql = "UPDATE `users` set {$data} where id = '{$_SESSION['id']}'";
            @$save = $this->conn->query($sql);
            if($save){
                $resp['status'] = 'success';
                $_SESSION['flashdata']['type'] = 'success';
                $_SESSION['flashdata']['msg'] = 'Credential successfully updated.';
                foreach($_POST as $k => $v){
                    if(!in_array($k,array('id','old_password')) && !empty($v)){
                        if(!empty($data)) $data .= ",";
                        if($k == 'password') $v = md5($v);
                        $_SESSION[$k] = $v;
                    }
                }
                if(isset($_FILES['avatar']['tmp_name']) && !empty($_FILES['avatar']['tmp_name'])){
                    $dir_path = __DIR__.'/uploads/avatars/';
                    if(!is_dir($dir_path))
                        mkdir($dir_path);
                    $fname = $dir_path.$_SESSION['id'].'.png';
                    $upload = $_FILES['avatar']['tmp_name'];
                       $type = mime_content_type($upload);
                       $allowed = array('image/png','image/jpeg');
                       if(!in_array($type,$allowed)){
                           $resp['msg'].=" But Image failed to upload due to invalid file type.";
                       }else{
                            $new_height = 200; 
                            $new_width = 200; 
                    
                            list($width, $height) = getimagesize($upload);
                            $t_image = imagecreatetruecolor($new_width, $new_height);
                            $gdImg = ($type == 'image/png')? imagecreatefrompng($upload) : imagecreatefromjpeg($upload);
                            imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                            if($gdImg){
                                    if(is_file($fname))
                                    unlink($fname);
                                    imagepng($t_image,$fname);
                                    imagedestroy($gdImg);
                                    imagedestroy($t_image);
                            }else{
                            $resp['msg'].=" But Image failed to upload due to unkown reason.";
                            }
                       }
                   }
            }else{
                $resp['status'] = 'failed';
                $resp['msg'] = 'Updating Credentials Failed. Error: '.$this->conn->error;
                $resp['sql'] =$sql;
            }
        }
        return json_encode($resp);
    }
    function find_user(){
        extract($_POST);
        $sql = "SELECT *,CONCAT(firstname,' ',middlename,' ',lastname) as `name` FROM `users` where (CONCAT(firstname,' ',middlename,' ',lastname) LIKE '%{$keyword}%' OR CONCAT(lastname,' ',firstname,' ',middlename) LIKE '%{$keyword}%' OR email LIKE '%{$keyword}%') and id != '{$_SESSION['id']}'";
        $search = $this->conn->query($sql);
        $data = array();
        while($row = $search->fetch_assoc()){
            $row['avatar'] = is_file('./uploads/avatars/'.$row['id'].'.png') ? './uploads/avatars/'.$row['id'].'.png?v='.(!is_null($row['date_updated']) ? strtotime($row['date_updated']) : strtotime($row['date_created'])) : './images/no-image-available.png';
            $row['id'] = md5($row['id']);
            $data[] = $row;
        }
        return json_encode($data);
    }
    function send_message(){
        extract($_POST);
        $from_user = $_SESSION['id'];
        $to_user = $user_to;
        $type = 1;
        $message = $this->conn->real_escape_string(($message));
        $message = str_replace('/r','<br>',$message);
        $ins_message = $this->conn->query("INSERT INTO `messages` set from_user='{$from_user}', to_user='{$to_user}', `type` = '{$type}',`message` ='{$message}' ");
        if($ins_message){
            $resp['status'] = "success";
        }else{
            $resp['status'] = "failed";
            $resp['error'] = $this->conn->error;
        }
        return json_encode($resp);
    }
    function get_messages(){
        extract($_POST);
        $awhere = "";
        $last_id = empty($last_id) ? 0 :$last_id;
        $messages = $this->conn->query("SELECT * FROM `messages` where ((from_user = '{$_SESSION['id']}' and to_user = '{$convo_with}') OR (to_user = '{$_SESSION['id']}' and from_user = '{$convo_with}')) and id > {$last_id} order by unix_timestamp(date_created) asc ");
        $data = array();
        $ids = array();
        while($row = $messages->fetch_assoc()){
            $row['message'] = str_replace('\r','<br>',$row['message']);
            $data[] = $row;
            if($row['from_user'] != $_SESSION['id'])
            $ids[] = $row['id'];
        }
        if(count($ids) > 0)
        $this->conn->query("UPDATE `messages` set status = 1 where id in (".implode(',',$ids).") ");

        return json_encode($data);
    }
    function get_prev_messages(){
        extract($_POST);
        $messages = $this->conn->query("SELECT * FROM `messages` where (from_user = '{$_SESSION['id']}' and to_user = '{$convo_with}') OR (to_user = '{$_SESSION['id']}' and from_user = '{$convo_with}') order by unix_timestamp(date_created) desc limit {$message_limit} offset {$message_offset}");
        $data = array();
        while($row = $messages->fetch_assoc()){
            $data[] = $row;
        }
        return json_encode($data);

    }
    function get_unread(){
        $qry = $this->conn->query("SELECT distinct(m.from_user),m.*,concat(u.firstname,' ',u.lastname) as name FROM `messages` m inner join `users` u on m.from_user = u.id where m.to_user = '{$_SESSION['id']}' and m.popped = '0' and m.status =0 order by unix_timestamp(m.date_created) desc ");
        $data = array();
        while($row = $qry->fetch_assoc()){
            $row['avatar'] = is_file('./uploads/avatars/'.$row['from_user'].'.png') ? './uploads/avatars/'.$row['from_user'].'.png' : './images/no-image-available.png';
            $row['convo_with'] = $row['from_user'];
            $row['eid'] = md5($row['from_user']);
            $this->conn->query("UPDATE messages set popped = 1 where id <= '{$row['id']}' and from_user = '{$row['from_user']}' and to_user = '{$_SESSION['id']}' ");
            $un_read = $this->conn->query("SELECT * FROM messages where to_user = '{$_SESSION['id']}' and from_user = '{$row['from_user']}' and status = '0' ")->num_rows;
                             $row['un_read'] = $un_read > 0 ? $un_read : '';
            $data[] = $row;
        }
        return json_encode($data);
    }
    function delete_message(){
        extract($_POST);
        $delete = $this->conn->query("UPDATE messages set delete_flag = 1 where id ='{$id}'");
        if($delete){
            $resp['status'] ='success';
        }else{
            $resp['status'] ='failed';
            $resp['err'] =$this->conn->error;
        }
        return json_encode($resp);
    }
    function check_deleted(){
        extract($_POST);
        $data = array();
        if(!empty($ids)){
            $qry = $this->conn->query("SELECT * FROM messages where id in ({$ids}) and delete_flag=1");
            while($row = $qry->fetch_assoc()){
                $data[] = $row['id'];
            }
        }
        return json_encode($data);
    }
}
$a = isset($_GET['a']) ?$_GET['a'] : '';
$action = new Actions();
switch($a){
    case 'login':
        echo $action->login();
    break;
    case 'logout':
        echo $action->logout();
    break;
    case 'save_user':
        echo $action->save_user();
    break;
    case 'delete_user':
        echo $action->delete_user();
    break;
    case 'update_credentials':
        echo $action->update_credentials();
    break;
    case 'find_user':
        echo $action->find_user();
    break;
    case 'send_message':
        echo $action->send_message();
    break;
    case 'get_messages':
        echo $action->get_messages();
    break;
    case 'get_prev_messages':
        echo $action->get_prev_messages();
    break;
    case 'get_unread':
        echo $action->get_unread();
    break;
    case 'delete_message':
        echo $action->delete_message();
    break;
    case 'check_deleted':
        echo $action->check_deleted();
    break;
    default:
    // default action here
    break;
}