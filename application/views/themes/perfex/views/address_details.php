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
<div class="container">
    <div class="row text-center">
      <div class="col-lg-1"></div>
      <div class="col-lg-2 col-xs-2">
        <div class="icon-box">
          <center>
            <img src="/uploads/company/basic_details.png" class="img-responsive">
            <p>Basic Details</p>
          </center>
        </div>
      </div>
      <div class="col-lg-2 col-xs-2">
        <div class="icon-box">
        <center>
          <img src="/uploads/company/Parents_details.png" class="img-responsive">
          <p>Parents Details</p>
        </center>
        </div>
      </div>
      <div class="col-lg-2 col-xs-2">
        <div class="icon-box">
        <center>
          <img src="/uploads/company/Address_details.png" class="img-responsive">
          <p>Address Details</p>
        </center>
        </div>
      </div>
      <div class="col-lg-2 col-xs-2">
        <div class="icon-box">
        <center>
          <img src="/uploads/company/Academics_details_gray.png" class="img-responsive">
          <p>Academics Details</p>
        </center>
        </div>
      </div>
      <div class="col-lg-2 col-xs-2">
        <center>
          <img src="/uploads/company/declaration_gray.png" class="img-responsive">
          <p>Declaration</p>
        </center>
      </div>
        <div class="col-lg-1"></div>
    </div>
  </div>
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <h2 class="heading">Online Application Form</h2>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
        <form>
          <h4>Address for Correspondence</h4>
            <div class="row">
          <div class="col-lg-4">
            <div class="form-group">
              <label for="exampleInputFirstName">Country</label>
              <select class="form-control" name="country" id="country" >
                <option>Select</option>
                <option value="102" selected>India</option>
                <option value="0">Other</option>
              </select>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="form-group">
              <label for="exampleInputMiddleName">State </label>
              <select data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-live-search="true" name="state" class="form-control" id="state">
                <option value="" selected="selected">Select State *</option>
                <option value="Andaman and Nicobar">Andaman and Nicobar</option>
                <option value="Andhra Pradesh">Andhra Pradesh</option>
                <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                <option value="Assam">Assam</option>
                <option value="Bihar">Bihar</option>
                <option value="Chandigarh">Chandigarh</option>
                <option value="Chhattisgarh">Chhattisgarh</option>
                <option value="Dadra And Nagar Haveli">Dadra And Nagar Haveli</option>
                <option value="Daman And Diu">Daman And Diu</option>
                <option value="Delhi">Delhi</option>
                <option value="Goa">Goa</option>
                <option value="Gujarat">Gujarat</option>
                <option value="Haryana">Haryana</option>
                <option value="Himachal Pradesh">Himachal Pradesh</option>
                <option value="Jammu">Jammu</option>
                <option value="Jharkhand">Jharkhand</option>
                <option value="Karnataka">Karnataka</option>
                <option value="Kerala">Kerala</option>
                <option value="Kashmir">Kashmir</option>
                <option value="Ladakh">Ladakh</option>
                <option value="Lakshadweep">Lakshadweep</option>
                <option value="Madhya Pradesh">Madhya Pradesh</option>
                <option value="Maharashtra">Maharashtra</option>
                <option value="Manipur">Manipur</option>
                <option value="Meghalaya">Meghalaya</option>
                <option value="Mizoram">Mizoram</option>
                <option value="Nagaland">Nagaland</option>
                <option value="Odisha">Odisha</option>
                <option value="Puducherry">Puducherry</option>
                <option value="Punjab">Punjab</option>
                <option value="Rajasthan">Rajasthan</option>
                <option value="Sikkim">Sikkim</option>
                <option value="Tamil Nadu">Tamil Nadu</option>
                <option value="Telangana">Telangana</option>
                <option value="Tripura">Tripura</option>
                <option value="Uttar Pradesh">Uttar Pradesh</option>
                <option value="Uttarakhand">Uttarakhand</option>
                <option value="West Bengal">West Bengal</option>
            </select>
          </div>
        </div>
          
            <div class="col-lg-4">
              <div class="form-group">
                <label for="exampleInputEmail">City </label>
                <input type="text" class="form-control" name="city" placeholder="Enter City Name" value="">
              </div>
            </div>
          </div>

          <div class="row">

          <div class="col-lg-4">
               <div class="form-group">
                <label for="exampleInputFirstName">Address Line 1</label>
            <input class="form-control" type="text" class="form-group" placeholder="Enter Address Line 1" name="addrlone">
          </div>
          </div>
          <div class="col-lg-4">
            <div class="form-group">
              <label for="exampleInputMiddleName">Address Line 2 </label>
            <input class="form-control" type="text" class="form-group" placeholder="Enter Address Line 2" name="addrltwo">
          </div>
        </div><div class="col-lg-4">
            <div class="form-group">
              <label for="exampleInputLastName">Pincode</label>
            <input class="form-control" type="text" class="form-group" placeholder="Enter Pincode" name="pincode">
          </div>
        </div>
          </div><hr>
                <div class="container">
                    <div class="row">
                      <label>Is Permanent Address Same As Address For Communication?</label><br>
                      <input type="radio" name="sameAddress" value="Yes">&nbsp;&nbsp;Yes&nbsp;&nbsp;
                      <input type="radio" name="sameAddress" value="No">&nbsp;&nbsp;No
                   </div>
                </div><br>
          <div class="row">
            <div class="col-lg-6 col-xs-6">
              <a href="" class="btn btn-primary button-23">Back</a>
              <button type="submit" class="btn btn-primary">Save & Next</button>
            </div>
            <div class="col-lg-6 col-xs-6" >
              <a href="<?=base_url()?>/clients/academic_details" class="btn btn-primary" style="float: right;">Next</a>
              
            </div>
          </div>
        </form>
      </div>
      </div>
    </div>
  </div>

