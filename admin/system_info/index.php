<?php
// HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $short_name = $_POST['short_name'];
    $about_us = $_POST['about_us'];
    $privacy_policy = $_POST['privacy_policy'];

    // Save texts
    file_put_contents(base_app . 'about.html', $about_us);
    file_put_contents(base_app . 'privacy_policy.html', $privacy_policy);

    $_settings->set_info('name', $name);
    $_settings->set_info('short_name', $short_name);

    // Logo Upload
    if (!empty($_FILES['img']['tmp_name'])) {
        $logo_path = 'uploads/logo.png';
        move_uploaded_file($_FILES['img']['tmp_name'], base_app . $logo_path);
        $_settings->set_info('logo', $logo_path);
    }

    // Cover Upload
    if (!empty($_FILES['cover']['tmp_name'])) {
        $cover_path = 'uploads/cover.jpg';
        move_uploaded_file($_FILES['cover']['tmp_name'], base_app . $cover_path);
        $_settings->set_info('cover', $cover_path);
    }

    // Banner Images Upload
    if (!empty($_FILES['banners']['tmp_name'][0])) {
        $upload_path = base_app . 'uploads/banner/';
        foreach ($_FILES['banners']['tmp_name'] as $index => $tmpPath) {
            $fileName = time() . '_' . $_FILES['banners']['name'][$index];
            move_uploaded_file($tmpPath, $upload_path . $fileName);
        }
    }

    $_settings->set_flashdata('success', 'System Information Successfully Updated.');
    echo "<script>location.replace('" . $_SERVER['REQUEST_URI'] . "')</script>";
    exit;
}
?>

<?php if($_settings->chk_flashdata('success')): ?>
<script>
	alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
</script>
<?php endif;?>

<style>
	img#cimg{
		height: 15vh;
		width: 15vh;
		object-fit: cover;
		border-radius: 100%;
	}
	img#cimg2{
		height: 50vh;
		width: 100%;
		object-fit: contain;
	}
</style>

<div class="col-lg-12">
	<div class="card card-outline card-primary">
		<div class="card-header">
			<h5 class="card-title">System Information</h5>
		</div>
		<div class="card-body">
			<form action="" id="system-frm" method="POST" enctype="multipart/form-data">
				<div id="msg" class="form-group"></div>

				<div class="form-group">
					<label for="name" class="control-label">System Name</label>
					<input type="text" class="form-control form-control-sm" name="name" id="name" value="<?php echo $_settings->info('name') ?>">
				</div>

				<div class="form-group">
					<label for="short_name" class="control-label">System Short Name</label>
					<input type="text" class="form-control form-control-sm" name="short_name" id="short_name" value="<?php echo $_settings->info('short_name') ?>">
				</div>

				<div class="form-group">
					<label for="about_us" class="control-label">About Us</label>
					<textarea name="about_us" cols="30" rows="2" class="form-control summernote"><?php echo is_file(base_app.'about.html') ? file_get_contents(base_app.'about.html') : "" ?></textarea>
				</div>

				<div class="form-group">
					<label for="privacy_policy" class="control-label">Privacy Policy</label>
					<textarea name="privacy_policy" cols="30" rows="2" class="form-control summernote"><?php echo is_file(base_app.'privacy_policy.html') ? file_get_contents(base_app.'privacy_policy.html') : "" ?></textarea>
				</div>

				<div class="form-group">
					<label class="control-label">System Logo</label>
					<div class="custom-file">
						<input type="file" class="custom-file-input" id="logoFile" name="img" onchange="displayImg(this, '#cimg')">
						<label class="custom-file-label" for="logoFile">Choose file</label>
					</div>
				</div>
				<div class="form-group d-flex justify-content-center">
					<img src="<?php echo validate_image($_settings->info('logo')) ?>" alt="" id="cimg" class="img-fluid img-thumbnail">
				</div>

				<div class="form-group">
					<label class="control-label">Website Cover</label>
					<div class="custom-file">
						<input type="file" class="custom-file-input" id="coverFile" name="cover" onchange="displayImg(this, '#cimg2')">
						<label class="custom-file-label" for="coverFile">Choose file</label>
					</div>
				</div>
				<div class="form-group d-flex justify-content-center">
					<img src="<?php echo validate_image($_settings->info('cover')) ?>" alt="" id="cimg2" class="img-fluid img-thumbnail">
				</div>

				<div class="form-group">
					<label class="control-label">Banner Images</label>
					<div class="custom-file">
						<input type="file" class="custom-file-input" id="bannerFiles" name="banners[]" multiple accept=".png,.jpg,.jpeg">
						<label class="custom-file-label" for="bannerFiles">Choose files</label>
					</div>
					<small><i>Choose to upload new banner images</i></small>
				</div>

				<!-- PREVIEW SECTION -->
				<div class="form-group d-flex flex-wrap banner-preview-container mb-3"></div>

				<?php 
				$upload_path = "uploads/banner";
				if(is_dir(base_app.$upload_path)): 
					$file= scandir(base_app.$upload_path);
					foreach($file as $img):
						if(in_array($img,array('.','..')))
							continue;
				?>
					<div class="d-flex w-100 align-items-center img-item">
						<span><img src="<?php echo base_url.$upload_path.'/'.$img ?>" width="150px" height="150px" style="object-fit:cover;" class="img-thumbnail" alt=""></span>
						<span class="ml-4">
							<button class="btn btn-sm btn-default text-danger rem_img" type="button" data-path="<?php echo base_app.$upload_path.'/'.$img ?>"><i class="fa fa-trash"></i></button>
						</span>
					</div>
				<?php endforeach; ?>
				<?php endif; ?>
			</form>
		</div>
		<div class="card-footer">
			<div class="row">
				<button class="btn btn-sm btn-primary" form="system-frm">Update</button>
			</div>
		</div>
	</div>
</div>

<script>
	function displayImg(input, selector) {
		if (input.files && input.files[0]) {
			let reader = new FileReader();
			reader.onload = function (e) {
				$(selector).attr('src', e.target.result);
				$(input).siblings('.custom-file-label').html(input.files[0].name);
			}
			reader.readAsDataURL(input.files[0]);
		}
	}

	document.getElementById('bannerFiles').addEventListener('change', function(e) {
		const container = document.querySelector('.banner-preview-container');
		container.innerHTML = "";
		Array.from(this.files).forEach(file => {
			let reader = new FileReader();
			reader.onload = function(event) {
				let img = document.createElement('img');
				img.src = event.target.result;
				img.className = "img-thumbnail m-1";
				img.style.width = "200px";
				img.style.height = "200px";
				img.style.objectFit = "cover";
				container.appendChild(img);
			}
			reader.readAsDataURL(file);
		});
		this.nextElementSibling.innerHTML = Array.from(this.files).map(f => f.name).join(', ');
	});

	$(document).ready(function(){
		$('.rem_img').click(function(){
			_conf("Are sure to delete this image permanently?", 'delete_img', ["'" + $(this).attr('data-path') + "'"])
		});

		$('.summernote').summernote({
			height: 200,
			toolbar: [
				['style', ['style']],
				['font', ['bold', 'italic', 'underline', 'clear']],
				['fontsize', ['fontsize']],
				['color', ['color']],
				['para', ['ul', 'ol', 'paragraph']],
				['view', ['fullscreen', 'codeview']]
			]
		});
	});

	function delete_img($path){
		start_loader()
		$.ajax({
			url: _base_url_ + 'classes/Master.php?f=delete_img',
			data: {path: $path},
			method: 'POST',
			dataType: "json",
			error: err => {
				console.log(err)
				alert_toast("An error occurred while deleting an image", "error");
				end_loader()
			},
			success: function(resp){
				if(resp.status == 'success'){
					$('[data-path="'+$path+'"]').closest('.img-item').hide('slow', function(){
						$(this).remove()
					})
					alert_toast("Image Successfully Deleted", "success");
				}else{
					alert_toast("Failed to delete image", "error");
				}
				end_loader()
			}
		})
	}
</script>
