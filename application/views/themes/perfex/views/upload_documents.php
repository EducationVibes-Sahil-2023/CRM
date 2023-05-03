<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

  <style type="text/css">
    .card{border:1px solid #efefef;padding: 10px;}
    .heading{    font-size: 25px;
    font-weight: 600;}
    .btn-primary {
    color: #fff;
    background-color: #337ab7;
    border-color: #2e6da4;
    height: 40px;
    width: 120px;
    border-radius: initial;
}
.button-23{background: #415165;border-color: #415165;width: 100px;}
  </style>
</head>
<body>
   <div class="container hidden-xs">
    <div class="row text-center">
    <div class="col-lg-2 col-xs-2">
				<center>
        <a href="/clients/basic_details">
					<img src="/uploads/company/basic_details.png" class="img-responsive">
					<p>Basic Details</p>
          </a>
				</center>
			</div>
			<div class="col-lg-2 col-xs-2">
				<div class="icon-box">
				<center>
        <a href="/clients/admission_preferences">
					<img src="/uploads/company/Parents_details.png" class="img-responsive">
					<p>Admission Preferences</p>
        </a>
				</center>
				</div>
			</div>
			<!-- <div class="col-lg-2 col-xs-2">
				<div class="icon-box">
				<center>
					<img src="/uploads/company/Address_details.png" class="img-responsive">
					<p>Address Details</p>
				</center>
				</div>
			</div> -->
			<div class="col-lg-2 col-xs-2">
				<div class="icon-box">
				<center>
        <a href="/clients/academic_details">
					<img src="/uploads/company/Academics_details.png" class="img-responsive">
					<p>Academics Details</p>
        </a>  
				</center>
				</div>
			</div>
      <div class="col-lg-2 col-xs-2">
				<div class="icon-box">
				<center>
					<img src="/uploads/company/Address_details.png" class="img-responsive">
					<p>Upload Documents</p>
				</center>
				</div>
			</div>
      
			<div class="col-lg-2 col-xs-2">
				<center>
					<img src="/uploads/company/declaration_gray.png" class="img-responsive">
					<p>Declaration</p>
				</center>
			</div>
    </div>
  </div>
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <h2 class="heading">Upload Documents</h2>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="row">
      <div class="col-lg-12">

      <?php if(count($files) == 0){ ?>
        <hr class="hr-panel-heading" />
        <div class="text-center">
            <h4 class="no-margin"><?php echo _l('no_files_found'); ?></h4>
        </div>
    <?php } else { ?>
        <table class="table dt-table mtop15 table-files" data-order-col="1" data-order-type="desc">
           <thead>
            <tr>
                <th class="th-files-file"><?php echo _l('customer_attachments_file'); ?></th>
                <th class="th-files-date-uploaded"><?php echo _l('file_date_uploaded'); ?></th>
                <?php if(get_option('allow_contact_to_delete_files') == 1){ ?>
                    <th class="th-files-option"><?php echo _l('options'); ?></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach($files as $file){ ?>
                <tr>
                    <td>
                      <?php
                      $url = site_url() .'download/file/client/';
                      $path = get_upload_path_by_type('customer') . $file['rel_id'] . '/' . $file['file_name'];
                      $is_image = false;
                      if(!isset($file['external'])) {
                        $attachment_url = $url . $file['attachment_key'];
                        $is_image = is_image($path);
                        $img_url = site_url('download/preview_image?path='.protected_file_url_by_path($path,true).'&type='.$file['filetype']);
                    } else if(isset($file['external']) && !empty($file['external'])){
                        if(!empty($file['thumbnail_link'])){
                            $is_image = true;
                            $img_url = optimize_dropbox_thumbnail($file['thumbnail_link']);
                        }
                        $attachment_url = $file['external_link'];
                    }
                    if($is_image){
                        echo '<div class="preview_image">';
                    }
                    ?>
                    <a href="<?php echo $attachment_url; ?>"<?php echo (isset($file['external']) && !empty($file['external']) ? ' target="_blank"' : ''); ?>
                    class="display-block mbot5">
                    <?php if($is_image){ ?>
                        <div class="table-image">
                          <div class="text-center"><i class="fa fa-spinner fa-spin mtop30"></i></div>
                          <img src="#" class="img-table-loading" data-orig="<?php echo $img_url; ?>">
                      </div>
                  <?php } else { ?>
                    <i class="<?php echo get_mime_class($file['filetype']); ?>"></i> <?php echo $file['file_name']; ?>
                <?php } ?>
            </a>
            <?php if($is_image){ echo '</div>'; } ?>
        </td>
        <td data-order="<?php echo $file['dateadded']; ?>"><?php echo _dt($file['dateadded']); ?></td>
        <?php if(get_option('allow_contact_to_delete_files') == 1) { ?>
            <td>
                <?php if($file['contact_id'] == get_contact_user_id()){ ?>
                    <a href="<?php echo site_url('clients/delete_file/'.$file['id'].'/general'); ?>"
                        class="btn btn-danger btn-icon _delete file-delete"><i class="fa fa-remove"></i></a>
                    <?php } ?>
                </td>
            <?php } ?>
        </tr>
    <?php } ?>
</tbody>
</table>
<?php } ?>
        <div class="card">
        <?php echo form_open_multipart('clients/upload_docs',array('autocomplete'=>'off')); ?>
        <?php if(count($files) < 1){ ?>

          <div class="row">
            <div class="col-lg-6">
                <div class="form-group">
                  <label for="photo">Upload Your Recent Passport Size Photograph </label>
                  <input type="file"  name="photo" id="photo" class="form-control" required title="Please select your passport size photo">
                </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group">
                <label for="signature">Upload Your Signature </label>
              <input type="file" name="signature" id="signature" class="form-control" required title="Please select your Signature">
            </div>
        </div>
        
          </div>
            <div class="row">

            <div class="col-lg-6">
              <div class="form-group">
                <label for="tenth_marksheet">Upload Your 10th Marksheet</label>
              <input type="file" name="tenth_marksheet" id="tenth_marksheet" class="form-control" required title="Please select your Signature">
              </div>
            </div>

          <div class="col-lg-6">
                <div class="form-group">
                  <label for="twelth_marksheet">Upload Your 12th Marksheet  </label>
                  <input type="file" name="twelth_marksheet" id="twelth_marksheet" class="form-control" >
                </div>
          </div>
          </div><hr>
          <?php } ?>
          <div class="row">
            <div class="col-lg-6 col-xs-6">
              <!-- <button type="submit" class="btn btn-primary button-23">Back</button> -->
              <a href="/clients/academic_details" class="btn btn-primary button-23">Back</a>
              <?php if(count($files) < 1){ ?>
              <button type="submit" class="btn btn-primary">Save & Next</button>
              <?php } ?>
            </div>
            <div class="col-lg-6 col-xs-6" >
              <!-- <button type="submit" class="btn btn-primary" style="float: right;">Next</button>  -->
              <!-- <a href="/clients/declaration" class="btn btn-primary button-23 pull-right">Next</a> -->
              <a href="/clients/declaration" class="btn btn-primary button-23 pull-right">Next</a>

              
            </div>
          </div>
          <?php echo form_close(); ?>
      </div>
      </div>
    </div>

    

  </div>

