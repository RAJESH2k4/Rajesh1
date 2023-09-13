<style>
    #restaurant-slider,#restaurant-slider *{
        height:60vh;
    }
    .avatar-img{
        width:75px;
        height:75px;
        object-fit:scale-down;
        object-position:center center;
        border-radius:50% 50%
    }
    
    .avatar-img-review{
        width:35px;
        height:35px;
        object-fit:scale-down;
        object-position:center center;
        border-radius:100%
    }
    .det-icons{
        width:35px;
    }
</style>
<?php 
require_once('./DBConnection.php');
$qry = $conn->query("SELECT * FROM `restaurant_list` where restaurant_id = '{$_GET['id']}'")->fetchArray();
foreach($qry as $k => $v){
    if(!is_numeric($k))
    $$k = $v;
}
$imgs = array();
$dir = './uploads/'.$restaurant_id.'/';
if(is_dir($dir)){
    $scandr = scandir($dir);
    foreach($scandr as $img){
        if(!in_array($img,array('.','..'))){
            $imgs[] = $img;
        }
    }
}
$user_qry = $conn->query("SELECT * FROM user_list where user_id = '{$user_id}'");
$user_meta_qry = $conn->query("SELECT * FROM user_meta where user_id = '{$user_id}'");
$user = array();
foreach($user_qry->fetchArray() as $k => $v){
    if(!is_numeric($k))
    $user[$k] = $v;
}
while($row = $user_meta_qry->fetchArray()){
    $user[$row['meta_field']] = $row['meta_value'];
}
$count = $conn->query("SELECT count(review_id) as count FROM `review_list` where restaurant_id = '{$restaurant_id}'")->fetchArray()['count'];
$total_rating = $conn->query("SELECT SUM(rating) as total FROM `review_list` where restaurant_id = '{$restaurant_id}'")->fetchArray()['total'];
$total_rating = $total_rating > 0 ? round($total_rating/$count,0) : 0;
?>
<div class="col-12">
    <?php if(count($imgs) > 0): ?>
    <div id="restaurant-slider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2500">
        <div class="carousel-inner">
            <?php 
            $hasActive = false;
            foreach($imgs as $img): 
            ?>
            <div class="carousel-item <?php echo (!$hasActive) ? "active" : '' ?>">
                <img src="<?php echo $dir.$img ?>" class="d-block w-100" alt="Restaurant Image">
            </div>
            <?php 
                $hasActive = true;
            endforeach; 
            ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#restaurant-slider" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#restaurant-slider" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    <?php endif; ?>
    <div class="clearfix my-4"></div>
    <div class="row mx-0">
        <div class="col-md-4">
            <img src="<?php echo is_file('./uploads/thumbnails/'.$restaurant_id.'.png') ? './uploads/thumbnails/'.$restaurant_id.'.png' : './images/no-image-available.png' ?>" class="img-thumbnail" alt="restaurant thumbnail">
            <div class="stars">
                <input value="5" class="star star-5" id="star-5" type="radio" <?php echo $total_rating ==5 ? "checked" : 0 ?>/> <label class="star star-5" for="star-5"></label> 
                <input value="4" class="star star-4" id="star-4" type="radio" <?php echo $total_rating ==4 ? "checked" : 0 ?> /> <label class="star star-4" for="star-4"></label> 
                <input value="3" class="star star-3" id="star-3" type="radio" <?php echo $total_rating ==3 ? "checked" : 0 ?> /> <label class="star star-3" for="star-3"></label> 
                <input value="2" class="star star-2" id="star-2" type="radio" <?php echo $total_rating ==2 ? "checked" : 0 ?> /> <label class="star star-2" for="star-2"></label> 
                <input value="1" class="star star-1" id="star-1" type="radio" <?php echo $total_rating ==1 ? "checked" : 0 ?> /> <label class="star star-1" for="star-1"></label> 
            </div>
            <div class="card rounded-0">
                <div class="card-body">
                    <center>
                        <img src="<?php echo is_file('./avatars/'.$user_id.'.png') ? './avatars/'.$user_id.'.png' : './images/no-image-available.png' ?>" class="avatar-img" alt="avatar">
                    </center>
                    <div class="lh-2">
                        <small><span class="fa fa-building det-icons"></span> <?php echo $user['restaurant_name'] ?></small> <br>
                        <small><span class="fa fa-phone det-icons"></span> <?php echo $user['contact'] ?></small> <br>
                        <small><span class="fa fa-envelope det-icons"></span> <a href="mailto:<?php echo $user['email'] ?>" target="_blank"><?php echo $user['email'] ?></a></small> <br>
                        <small><span class="fab fa-facebook-f det-icons"></span> <a href="<?php echo $user['fb_link'] ?>" target="_blank"><?php echo $user['fb_link'] ?></a></small> <br>
                        <small><span class="fab fa-twitter-square det-icons"></span> <a href="<?php echo $user['twitter_link'] ?>" target="_blank"><?php echo $user['twitter_link'] ?></a></small> <br>
                        <small><span class="fas fa-globe det-icons"></span> <a href="<?php echo $user['website'] ?>" target="_blank"><?php echo $user['website'] ?></a></small>
                    </div>
                </div>
                
            </div>
        </div>
        <div class="col-md-8">
            <h3><b><?php echo $name ?></b></h3>
            <hr class="border-primary bg-primary opacity-1">
            <div class="lh-1 mb-4">
                <small><span class="fa fa-map-marked-alt me-2"></span> <?php echo $location ?></small>
            </div>
            <div>
                <?php echo html_entity_decode($description) ?>
            </div>
            <hr>
            <h3>Review</h3>
            <?php 
            $u_review = array();
            $review = $conn->query("SELECT r.*,u.fullname FROM `review_list` r inner join user_list u on r.user_id = u.user_id where r.restaurant_id = '{$restaurant_id}' order by strftime('%s',r.date_created) asc");
            while($row = $review->fetchArray()):
                $u_review[] = $row['user_id'];
            ?>
            <div class="card rounded-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="w-100 d-flex align-items-center">
                        <div class="col-auto pe-2">
                            <img src="<?php echo is_file('./avatars/'.$row['user_id'].'.png') ? './avatars/'.$row['user_id'].'.png' : './images/no-image-available.png' ?>" alt="avatar" class="avatar-img-review bg-light border">
                        </div>
                        <div class="col-auto flex-grow-1 lh-1">
                            <div class="fs-6 fw-bold"><?php echo $row['fullname'] ?></div>
                            <small class="text-muted" style="font-size: 10px"><?php echo date("M d, Y H:i",strtotime($row['date_created'])) ?></small>
                        </div>
                    </div>
                    <hr>
                    <p class="lh-1 ps-4"><?php echo $row['review'] ?></p>
                    <div class="stars">
                        <input disabled value="5" class="star star-5" id="<?php echo $row['review_id'] ?>_star-5" type="radio" <?php echo $row['rating'] ==5 ? 'checked' : '' ?>/> <label class="star star-5" for="<?php echo $row['review_id'] ?>_star-5"></label> 
                        <input disabled value="4" class="star star-4" id="<?php echo $row['review_id'] ?>_star-4" type="radio" <?php echo $row['rating'] ==4 ? 'checked' : '' ?>/> <label class="star star-4" for="<?php echo $row['review_id'] ?>_star-4"></label> 
                        <input disabled value="3" class="star star-3" id="<?php echo $row['review_id'] ?>_star-3" type="radio" <?php echo $row['rating'] ==3 ? 'checked' : '' ?>/> <label class="star star-3" for="<?php echo $row['review_id'] ?>_star-3"></label> 
                        <input disabled value="2" class="star star-2" id="<?php echo $row['review_id'] ?>_star-2" type="radio" <?php echo $row['rating'] ==2 ? 'checked' : '' ?>/> <label class="star star-2" for="<?php echo $row['review_id'] ?>_star-2"></label> 
                        <input disabled value="1" class="star star-1" id="<?php echo $row['review_id'] ?>_star-1" type="radio" <?php echo $row['rating'] ==1 ? 'checked' : '' ?>/> <label class="star star-1" for="<?php echo $row['review_id'] ?>_star-1"></label> 
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            <h3>Write a Review</h3>
            <?php if(!in_array($_SESSION['user_id'],$u_review)): ?>
            <form action="" id="review-form">
                <input type="hidden" name="restaurant_id" value="<?php echo $restaurant_id ?>">
                <div class="form-group">
                    <div class="form-group">
                        <label for="rating" class="control-label">Rating</label>
                        <br>
                        <div class="stars">
                            <input value="5" class="star star-5" id="_star-5" type="radio" name="rating" /> <label class="star star-5" for="_star-5"></label> 
                            <input value="4" class="star star-4" id="_star-4" type="radio" name="rating" /> <label class="star star-4" for="_star-4"></label> 
                            <input value="3" class="star star-3" id="_star-3" type="radio" name="rating" /> <label class="star star-3" for="_star-3"></label> 
                            <input value="2" class="star star-2" id="_star-2" type="radio" name="rating" /> <label class="star star-2" for="_star-2"></label> 
                            <input value="1" class="star star-1" id="_star-1" type="radio" name="rating" checked/> <label class="star star-1" for="_star-1"></label> 
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="review" class="control-label">Review</label>
                        <textarea name="review" id="review" cols="30" rows="4" style="resize:none" class="form-control form-control-sm rounded-0" required></textarea>
                    </div>
                    <div class="form-group my-2 d-flex w-100 justify-content-end">
                        <button class="btn btn-sm btn-primary rounded-0 me-2">Submit</button>
                        <button class="btn btn-sm btn-dark rounded-0 me-2" type="reset">Cancel</button>
                    </div>
                </div>
            </form>
            <?php else: ?>
                <p class='text-muted'>You already submitted your review.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    function delete_data(){
        $('#confirm_modal button').attr('disabled',true)
        $.ajax({
            url:'./Actions.php?a=delete_restaurant',
            method:'POST',
            data:{id:<?php echo $restaurant_id ?>},
            dataType:'JSON',
            error:err=>{
                consolre.log(err)
                alert("An error occurred.")
                $('#confirm_modal button').attr('disabled',false)
            },
            success:function(resp){
                if(resp.status == 'success'){
                    location.replace('./?page=home')
                }else{
                    alert("An error occurred.")
                }
                $('#confirm_modal button').attr('disabled',false)
            }
        })
    }
    $(function(){
        $('.delete_data').click(function(){
            _conf("Are you sure to delete this restaurant?",'delete_data',[])
        })
        $('#review-form').submit(function(e){
            e.preventDefault();
            $('.pop_msg').remove()
            var _this = $(this)
            var _el = $('<div>')
                _el.addClass('pop_msg')
            _this.find('button').attr('disabled',true)
            _this.find('button[type="submit"]').text('submitting form...')
            $.ajax({
                url:'./Actions.php?a=save_review',
                method:'POST',
                data:$(this).serialize(),
                dataType:'JSON',
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
                            _this.get(0).reset()
                            setTimeout(() => {
                                location.reload()
                            }, (1500));
                    }else{
                        _el.addClass('alert alert-danger')
                    }
                    _el.text(resp.msg)

                    _el.hide()
                    _this.prepend(_el)
                    _el.show('slow')
                     _this.find('button').attr('disabled',false)
                     _this.find('button[type="submit"]').text('Submit')
                }
            })
        })
    })
</script>