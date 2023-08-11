<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$documents_data = $documents;
$document_id = !empty($documents_data[0]["id"]) ? $documents_data[0]["id"] : '';
$document_files = !empty($documents_data[0]["documents"]) ? json_decode($documents_data[0]["documents"], true) : '';
?>
<style type="text/css">
  .card {
    border: 1px solid #efefef;
    padding: 10px;
  }

  .heading {
    font-size: 25px;
    font-weight: 600;
  }

  .btn-primary {
    color: #fff;
    background-color: #337ab7;
    border-color: #2e6da4;
    height: 40px;
    width: 120px;
    border-radius: initial;
  }

  .button-23 {
    background: #415165;
    border-color: #415165;
    width: 100px;
  }

  .add_document {
    height: 30px;
    width: 30px;
    display: inline-block;
    float: right;
    text-align: center;
    line-height: 0px;
    padding: 10px;
    margin: 10px;
  }
</style>
</head>

<body>
  <div class="container hidden-xs">
    <div class="row text-center">
      <div class="col-lg-2 col-xs-2">
        <center>
          <a href="<?= base_url() ?>/clients/basic_details">
            <img src="/uploads/company/basic_details.png" class="img-responsive">
            <p>Basic Details</p>
          </a>
        </center>
      </div>
      <div class="col-lg-2 col-xs-2">
        <div class="icon-box">
          <center>
            <a href="<?= base_url() ?>/clients/admission_preferences">
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
            <a href="<?= base_url() ?>/clients/academic_details">
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
        <h2 class="heading">Upload Documents</h2> <button class="col-md-2 add_document btn btn_primary add_document_btn float-right" style="display:block!important;" type="button" onclick="add_documents()"><i class="fa fa-plus" aria-hidden="true"></i></button>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="row">
      <div class="col-lg-12">



        <div class="card">
          <?php echo form_open_multipart('clients/upload_docs_new', array('autocomplete' => 'off')); ?>
          <input type="hidden" name="document_id" value="<?= $document_id ?>">
          <table class="table">
            <tbody class="document_upload_div">
              <?php if (!empty($document_files)) {
                foreach ($document_files as $doc_files) {
                  $doc_type = $doc_files["document_type"]
              ?>
                  <tr class="row">
                    <td class="col-4">
                      <select class="selectpicker form-control" name="document_type[]" required>
                        <option value="">Select Document</option>
                        <?php
                        if (!empty($document_type)) {
                          foreach ($document_type as $type) {
                            $select = "";
                            if ($doc_type == $type["id"]) {
                              $select = "selected";
                        ?>
                              <option <?= $select ?> value="<?= $type["id"] ?>"><?= $type["name"] ?></option>
                        <?php
                            }
                          }
                        }
                        ?>
                      </select>
                    </td>
                    <td class="col-4">
                      <input class="form-control" data-file-name="<?= !empty($doc_files['file_path']) ? $doc_files['file_path'] : '' ?>" name="media_file[]" type="file">
                      <a class="col-md-12 download_document" accept="image/*,application/pdf" href="javascript:void(0);" onclick="window.open(`<?= base_url($doc_files['file_path']) ?>`, '_blank');" type="button"><i class="fa fa-download" aria-hidden="true"></i></a>
                    </td>
                    <td class="col-4">
                      <button onclick="remove_document(this)"><i class="fa fa-trash text-danger"></i></button>
                    </td>
                  </tr>
                <?php
                }
              } else {
                ?>
                <tr class="row">
                  <td class="col-4">
                    <select class="selectpicker form-control" name="document_type[]" required>
                      <option value="">Select Document</option>
                      <?php
                      if (!empty($document_type)) {
                        foreach ($document_type as $type) {
                      ?>
                          <option value="<?= $type["id"] ?>"><?= $type["name"] ?></option>
                      <?php
                        }
                      }
                      ?>
                    </select>
                  </td>
                  <td class="col-4">
                    <input class="form-control" name="media_file[]" type="file" required>

                  </td>
                  <td class="col-4">
                    <button onclick="remove_document(this)"><i class="fa fa-trash text-danger"></i></button>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
          <div class="row">
            <div class="col-lg-6 col-xs-6">
              <!-- <button type="submit" class="btn btn-primary button-23">Back</button> -->
              <a href="<?= base_url() ?>/clients/academic_details" class="btn btn-primary button-23">Back</a>
              <?php if (count($files) < 1) { ?>
                <button type="submit" class="btn btn-primary">Save & Next</button>
              <?php } ?>
            </div>
            <div class="col-lg-6 col-xs-6">
              <!-- <button type="submit" class="btn btn-primary" style="float: right;">Next</button>  -->
              <!-- <a href="/clients/declaration" class="btn btn-primary button-23 pull-right">Next</a> -->
              <a href="<?= base_url() ?>/clients/declaration" class="btn btn-primary button-23 pull-right">Next</a>


            </div>
          </div>
          <?php echo form_close(); ?>
        </div>
      </div>
    </div>



  </div>

  <script>
    var document_type = <?= !empty($document_type) ? json_encode($document_type, true) : [] ?>;

    function validate_documents() {
      return new Promise((resolve, reject) => {
        let isValid = true;
        $(".document_upload_div tr").each(function() {
          let select_dropdown = $(this).find("select").val();
          let select_file = $(this).find("input[type='file']").get(0).files[0];
          let select_file_url = $(this).find("input[type='file']").attr("data-file-name");

          if (select_dropdown === undefined || select_dropdown === "") {
            $(this).find("select").focus();
            alertFloat("danger", "Select document type");
            isValid = false;
            return false; // Exit the loop immediately
          }

          if (select_file_url === undefined || select_file_url === "") {
            if (select_file === undefined || !select_file) {
              alertFloat("danger", "Select file media");
              $(this).find("input[type='file']").focus();
              isValid = false;
              return false; // Exit the loop immediately
            }
          }
        });

        if (isValid) {
          resolve(true); // Validation passed
        } else {
          reject(new Error("Validation failed: Some fields are empty."));
        }
      });
    }





    async function add_documents() {
      try {
        let status = await validate_documents();
        console.log("All document validations passed.");

        // Assuming 'document_type' is an array of objects with 'id' and 'name'
        let selected_dropdown = $(".selectpicker").map(function() {
          return $(this).val();
        }).get();

        let html = `<tr class="row">
      <td class="col-4">
        <select class="selectpicker form-control" name="document_type[]" required>
          <option value="">Select Document</option>`;

        if (document_type && document_type.length > 0) {
          document_type.forEach(type => {
            let disabled = "";
            if (selected_dropdown.includes(type.id)) {
              disabled = "disabled";
            }
            html += `<option ${disabled} value="${type.id}">${type.name}</option>`;
          });
        }

        html += `</select>
      </td>
      <td class="col-4">
        <input class="form-control" name="media_file[]" type="file" required>
      </td>
      <td class="col-4">
                  <button onclick="remove_document(this)" ><i class="fa fa-trash text-danger"></i></button>
                </td>
    </tr>`;

        $("tbody").append(html);
        $(".selectpicker").selectpicker('refresh'); // Refresh the Bootstrap Selectpicker

        // Continue with further processing if needed
      } catch (error) {
        console.error("Error during document validation:", error);
        // Handle the error
      }
    }

    function remove_document(obj) {
      $(obj).parents("tr").remove();
    }
    $("form").submit(function() {
      $(".selectpicker").each(function() {
        let title = $(this).find("option:selected").text();
        let input = '<input type="hidden" name="title[]" value="' + title + '" > ';
        $(this).after(input);
      });

      $("input[type='file']").each(function() {
        let media = $(this).attr("data-file-name");
        if (media === undefined) {
          media = "";
        }
        let input = '<input type="hidden" name="media_file[]" value="' + media + '" > ';
        $(this).after(input);
      });
    });
  </script>