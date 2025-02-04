<div role="tabpanel" class="tab-pane student-data-div" id="student_details">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <h4>Personal Informations</h4>
                <hr>
                <div class="">
                    <form id="basic-information-form" onsubmit=" return false;">
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleInputFirstName">First Name <small class="text-danger">*</small></label>
                                    <input class="form-control" type="text" class="form-group" required-check required placeholder="First Name" name="first_name" id="first_name" value='<?php echo (isset($basicdetails)) ? $basicdetails->first_name : $contact->firstname; ?>'>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleInputLastName">Last Name</label>
                                    <input class="form-control" type="text" class="form-group" placeholder="Last Name" name="last_name" id="last_name" value='<?php echo (isset($basicdetails)) ? $basicdetails->last_name : $contact->lastname; ?>'>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleInputEmail">Email Address <small class="text-danger">*</small></label>
                                    <input class="form-control" type="text" class="form-group" required-check required placeholder="Email Address" name="email" value='<?php echo (isset($basicdetails)) ? $basicdetails->email : $contact->email; ?>'>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleInputMobileNumber">Mobile Number <small class="text-danger">*</small></label>
                                    <input class="form-control" type="tel" class="form-group" required-check required placeholder="Mobile Number" name="mobile" pattern="[0-9]{10}" maxlength="10" value='<?php echo (isset($basicdetails)) ? $basicdetails->mobile : $contact->phonenumber; ?>'>
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleInputDateOfBirth">Date Of Birth <small class="text-danger">*</small></label>
                                    <input type="date" class="form-control" name="dob" required value='<?php echo ($basicdetails->dob != '') ? $basicdetails->dob : ''; ?>' required required-check>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Gender <small class="text-danger">*</small></label>
                                    <select class="form-control" name="gender" id="gender" required required-check>
                                        <option value="">Select</option>
                                        <option <?php echo ($basicdetails->gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option <?php echo ($basicdetails->gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                                        <option <?php echo ($basicdetails->gender == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleInputMobileNumber">Parents Name</label>
                                    <input class="form-control" type="text" class="form-group" placeholder="Parents Name" name="father_name" value='<?php echo (isset($basicdetails)) ? $basicdetails->father_name : ''; ?>'>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleInputMobileNumber">Parents Contact</label>
                                    <input class="form-control" type="tel" pattern="[0-9]{10}" maxlength="10" class="form-group" placeholder="Parents Contact" name="fathers_mobile" value='<?php echo (isset($basicdetails)) ? $basicdetails->fathers_mobile : ''; ?>'>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleInputMobileNumber">Parents Email</label>
                                    <input class="form-control" type="text" class="form-group" placeholder="Parents Email" name="fathers_email" value='<?php echo (isset($basicdetails)) ? $basicdetails->fathers_email : ''; ?>'>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Passport <small class="text-danger">*</small></label>
                                    <select class="form-control" name="passport" onchange="change_passport_status()" id="passport" required required-check>
                                        <option value="">Select Passport Status</option>
                                        <?php foreach ($passport_stages as $p) {
                                            $selected = "";
                                            if ($p["id"] == $basicdetails->passport) {
                                                $selected = "selected";
                                            }
                                        ?>
                                            <option value="<?= $p["id"] ?>" data-passport_number_status="<?= $p['show_status'] ?>" <?= $selected ?>><?= $p["name"] ?></option>
                                        <?php

                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 passport_number_div" style="display:<?= !empty($basicdetails->passport_number) ? 'block' : 'none' ?>;">
                                <div class="form-group">
                                    <label for="exampleInputMobileNumber">Passport Number <small class="text-danger">*</small></label>
                                    <input class="form-control" type="text" onchange="validatePassportNumber(this)" class="form-group" placeholder="Passport Number" id="passport_number" name="passport_number" value='<?php echo (isset($basicdetails)) ? $basicdetails->passport_number : ''; ?>'>
                                </div>
                            </div>

                        </div>
                        <div class="">
                            <div class="col-md-12">
                                <button type="submit" onclick="save_basic_details()" class="btn btn-primary button-22">Save changes</button>
                            </div>
                        </div>
                    </form>


                </div>
            </div>
        </div>
    </div>
</div>