<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php

$knowledge = !empty($knowledge[0]) ? $knowledge[0] : [];
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php echo form_open_multipart($this->uri->uri_string(), array('class' => 'insurance-form', 'autocomplete' => 'off', 'return' => 'false')); ?>
            <?php if (isset($knowledge)) { ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($knowledge['id']); ?>">
            <?php } else { ?>

                <input type="hidden" name="id" value="">
            <?php } ?>

            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="publib-infor-title">
                            <span class="publib-infor"><?php echo _l('public_information'); ?></span>
                        </h4>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="" id="new_insurance">
                                    <div class="row">
                                        <br>
                                        <div class="col-md-12">
                                            <?php $title = isset($knowledge) ? $knowledge['title'] : '' ?>

                                            <?php
                                            echo render_input('title', '<span class="text-danger">*</span> Title', $title, '', array('required' => true)); ?>
                                        </div>
                                        <div class="col-md-12">
                                            <?php $description = isset($knowledge) ? $knowledge['description'] : '' ?>
                                            <?php echo render_textarea('description', 'Description', $description); ?>

                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <?php $group_ids = isset($knowledge['group_id']) ? explode(",", $knowledge['group_id']) : '' ?>
                                            <?php echo render_select('group_id[]', $knowledge_group, array('id', array('group_name')), '<span class="text-danger">*</span> Groups', $group_ids, array('multiple' => true, 'required' => true), array(), '', '', false); ?>


                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label>Status</label>
                                            <?php $status = isset($knowledge) ? $knowledge['status'] : '' ?>
                                            <select id="status" name="status" class="selectpicker" required="1" data-width="100%" data-none-selected-text="Non selected" data-live-search="true" tabindex="-98" aria-describedby="status-error">
                                                <option value="1" <?= $status != '' && $status == 1 ? 'checked' : '' ?>>Active</option>
                                                <option value="0" <?= $status != '' && $status == 0 ? 'checked' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row"> &nbsp;

                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 ">
                                            <?php $media = isset($knowledge) ? $knowledge['media'] : '' ?>
                                            <?php echo render_input('media', 'Media', '', 'file'); ?>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <?php $youtube_link = isset($knowledge) ? $knowledge['youtube_link'] : '' ?>
                                            <?php echo render_input('youtube_link', 'Youtube url', $youtube_link, 'text'); ?>

                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <?php $other_link = isset($knowledge) ? $knowledge['other_link'] : '' ?>
                                            <?php echo render_input('other_link', 'Other link', $other_link); ?>

                                        </div>
                                    </div>


                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                                    <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                                </div>
                            </div>
                        </div><!-- .modal-content -->
                    </div>
                    <?php echo form_close(); ?>


                    <!-- </div>
                </div>
            </div> -->
                </div>
            </div>
        </div>
        <?php init_tail(); ?>


        <script>
            $("form").submit(function(event) {
                event.preventDefault();
                var formData = new FormData($(this)[0]);
                formData.append("csrf_token_name", $('input[name="csrf_token_name"]').val());
                $.ajax({
                    url: admin_url + 'knowledge_base/create_knowledge_base',
                    method: 'post',
                    data: formData,
                    contentType: false,
                    processData: false
                }).done(function(response) {
                    response = JSON.parse(response);
                    if (response.success == true) {
                        alert_float('success', response.message);
                        setTimeout(() => {
                            window.location.href = admin_url + '/knowledge_base';

                        }, 300);
                    }

                })
            })
        </script>
        </body>

        </html>