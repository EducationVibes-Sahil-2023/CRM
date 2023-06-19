<div class="application_div">
    <div class="col-md-12">
        <h3>University Shortlisting</h3>
        <hr>
    </div>
    <form id="university_shortlisting_form" onsubmit="return false;">

        <?php if (!empty($university_shortlisting)) {
            $selected_vendor = !empty($profile_creation_data[0]["vendor"]) ? explode(",", $profile_creation_data[0]["vendor"]) : [];
            $select_dropdown_value = array_column($customer_vendors, "name", "id");
            foreach ($university_shortlisting as $key_u => $short_list) {
        ?>
                <div class="col-md-12 university_div_application mt-2">
                    <div class="col-md-4">
                        <input type="hidden" name="university_id[]" value="<?= $short_list["id"] ?>">
                        <input type="input" name="university_name" class="form-control" disabled value="<?= $short_list["university_name"] ?>">
                    </div>
                    <div class="col-md-4">
                        <?php
                        // echo render_select('select_university_vendor', $customer_vendors, array('id', 'name'), '', "", "", array(), '', '', "", "select_university_vendor");
                        $selected_vendor = !empty($short_list["vendor_id"]) ? $short_list["vendor_id"] : "";
                        // echo render_select('select_university_vendor', $customer_vendors, array('id', 'name'), "", $selected_vendor);
                        // echo $select_dropdown_value[$selected_vendor];

                        ?>
                        <input type="input" name="vendor_id" class="form-control" disabled value="<?= $select_dropdown_value[$selected_vendor] ?>">
                    </div>

                    <div class="col-md-4">
                        <?php

                        $selected_university_status_update = !empty($short_list["university_status"]) ? $short_list["university_status"] : "";
                        echo render_select('university_application_status[]', $university_status_update, array('id', 'name'), "", $selected_university_status_update); ?>
                    </div>

                </div>
            <?php }
            ?>

        <?php } ?>

        <div class="col-12 text-center mt-5">

            <button class="btn btn-primary" onclick="handleSubmit(event)">Update</button>
        </div>
    </form>
</div>

<script>
    var csrfToken = "<?= $this->security->get_csrf_hash() ?>";
    async function handleSubmit(event) {
        event.preventDefault();

        try {
            // Call the validateForm function and store the result in a variable
            let validationResult = await validateForm();
            console.log(validationResult);

            if (validationResult) {
                // Collect form data
                const formElement = document.getElementById('university_shortlisting_form');
                const formData = new FormData(formElement);

                formData.append("<?= $this->security->get_csrf_token_name(); ?>", csrfToken);


                let response = await $.ajax({
                    url: "<?= base_url("clients/university_shortlisting_update") ?>",
                    method: "POST",
                    data: formData,
                    contentType: false,
                    processData: false
                });
                response = JSON.parse(response);
                if (response.resp_code == "RCS") {
                    alert_float("success", response.resp_desc);
                } else {
                    alert_float("danger", response.resp_desc);
                }
            }
        } catch (error) {
            console.error(error);
            alert_float("danger", error);
            // Handle the error (e.g., display an error message to the user)
        }
    }

    function validateForm() {
        return new Promise((resolve, reject) => {
            $("select[name='university_application_status']").each(function(index) {
                if ($(this).val() == "") {
                    $(this).focus();
                    alert_float("danger", "Select university " + (index + 1) + " status is required.");
                    reject(false);
                    return false;
                }
            });
            resolve(true);
        });
    }
</script>