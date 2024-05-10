<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$has_permission_edit = has_permission('knowledge_base', '', 'edit');
$has_permission_create = has_permission('knowledge_base', '', 'create');
?>

<style>
    thead th:nth-child(2),
    tbody td:nth-child(2) {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        height: 5rem;
        -webkit-line-clamp: 3;
        width: 350px;
        line-clamp: 2 !important;
        -webkit-box-orient: vertical;
    }

    .card-folders .card-body>.breadcrumb {
        margin-left: -1.25em;
        margin-right: -1.25em;
        margin-top: -1.25em;
        border-radius: 0;
    }

    .folder-container {
        text-align: center;
        margin-left: 1rem;
        margin-right: 1rem;
        margin-bottom: 1.5rem;
        width: 100px;
        padding: 0;
        align-self: start;
        background: none;
        border: none;
        outline-color: transparent !important;
        cursor: pointer;
    }

    .folder-icon {
        font-size: 3em;
        line-height: 1.25em;
    }

    .folder-icon-color {
        color: #ffc107;
        text-shadow: 1px 1px 0px #e0a800;
    }

    .folder-name {
        overflow-wrap: break-word;
        word-wrap: break-word;
        hyphens: auto;
    }

    .flex-column .folder-container {
        display: flex;
        width: auto;
        min-width: 100px;
        text-align: left;
        margin: 0;
        margin-bottom: 1rem;
    }

    .flex-column .folder-icon,
    .flex-column .folder-name {
        display: inline-flex;
    }

    .flex-column .folder-icon {
        font-size: 1.4em;
        margin-right: 1rem;
    }

    .file-icon-color {
        color: #999;
    }

    .align-items-stretch {
        display: inline-flex !important;
    }

    .show-empty-dir {
        padding: 10px;
        /* border: 1px solid black; */
        width: 100% !important;
        text-align: center !important;
        font-size: 30px;
        text-transform: capitalize;
        box-shadow: 3px 3px 13px lightgrey;
        text-shadow: 1px 2px -5px black;
    }

    .edit_folder_data {
        position: relative;
        left: 100%;
        bottom: 25%;
        font-size: large;
        cursor: pointer;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s mtop5">
                    <div class="panel-body">
                        <div class="_buttons">
                            <?php if ($has_permission_create) { ?>
                                <!-- <a href="<?php echo admin_url('knowledge_base/create_knowledge_base'); ?>" class="btn btn-info mright5"><?php echo _l('kb_new_knowledge'); ?></a> -->
                            <?php } ?>
                            <?php if ($has_permission_create) { ?>
                                <a href="<?php echo admin_url('knowledge_base/manage_knowledge_groups'); ?>" class="btn btn-info mright5"><?php echo _l('kb_knowledge_group'); ?></a>
                                <a href="#" onclick="set_modal('folder')" data-toggle="modal" data-target="#create_dir" class="btn btn-info mright5"><i class="fa fa-folder"></i> <?php echo _l('create_dir'); ?></a>
                                <a href="#" onclick="set_modal('upload')" data-toggle="modal" data-target="#create_dir" class="btn btn-info mright5"><i class="fa fa-upload"></i> <?php echo _l('upload_dir_files'); ?></a>
                            <?php } ?>


                        </div>
                        <hr class="hr-panel-heading" />

                        <div class="container">

                            <div class="card card-folders">
                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col mr-auto">
                                            <h4 class="card-title m-0">Folders</h4>
                                        </div>
                                        <div class="col col-auto pr-2 hide">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-secondary" id="btn-list"><i class="fa fa-th-list fa-lg"></i></button>
                                                <button class="btn btn-sm btn-outline-secondary outline-none active" id="btn-grid"><i class="fa fa-th-large fa-lg"></i></button>
                                            </div>
                                            <hr>
                                            <br>

                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="card-body" id="foldersGroup">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item active" onclick="breadcrumb_click(this)" data-id="0"><i class="fa fa-folder"></i>&nbsp; Knowledge_base</li>
                                    </ol>
                                    <div id="main-folders" class="d-flex align-items-stretch flex-wrap">

                                    </div>
                                    <div id="main-files" class="d-flex align-items-stretch flex-wrap">
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="create_dir" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <?php echo form_open('Knowledge_base/create_folder', array('id' => 'createFolderForm')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close close-modal" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Create & Upload Files</h4>
            </div>
            <div class="modal-body">
                <div class='modal-form modal-form-folder'>
                    <input type="hidden" id="folder_id">
                    <div class="form-group">
                        <?php $group_ids = isset($knowledge['group_id']) ? explode(",", $knowledge['group_id']) : '' ?>
                        <?php echo render_select('group_id[]', $knowledge_group, array('id', array('group_name')), '<span class="text-danger">*</span> Groups', $group_ids, array('multiple' => true, 'required' => true), array(), '', '', false, "group_id"); ?>

                    </div>
                    <div class="form-group">
                        <label for="folderName"><span class="text-danger">*</span> Folder Name</label>
                        <br>
                        <span>Knowledge_base/</span><span class='current_dir'></span>
                        <input type="text" id="folderName" required name="folderName" required class="form-control" placeholder="Folder Name">
                        <br>
                        <button type="button" id="createFolderBtn" class="btn btn-primary">Create Folder</button>
                    </div>
                </div>
                <div class="modal-form modal-form-upload">
                    <input type="file" id="upload_file" required multiple class="form-control" accept=".jpg,.jpeg,.png,.gif,.pdf,.mp4,.avi,.mov,.doc,.docx,.xls,.xlsx">
                    <br>
                    <button type="button" id="uploadFolderBtn" class="btn btn-primary ">Upload</button>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <!-- <button type="submit" class="btn btn-primary"><?php echo _l('confirm'); ?></button> -->
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div>
<?php init_tail(); ?>
<script>
    function set_modal(target) {
        // Hide all modal forms
        $(".modal-form").hide();

        // Clear input fields in the modal body
        $(".modal-body input").val('');

        // Reset selectpicker values in the modal body
        $(".modal-body select").selectpicker('val', '');

        // Show the modal form based on the target
        $(".modal-form-" + target).show();

    }

    function edit_folder(id, name, group_ids) {
        set_modal("folder");
        let group_id = group_ids.split(",");
        $("#folder_id").val(id);
        $("#folderName").val(name);
        $('#group_id').selectpicker('val', group_id);
    }

    function set_folder(folder_data) {

        if (folder_data.length > 0) {
            let html = "";
            for (i = 0; i < folder_data.length; i++) {
                let html_edit = ``;
                <?php if ($has_permission_edit) { ?>
                    if (folder_data[i].edit == 1) {
                        html_edit = "<i class='fa fa-edit edit_folder_data' data-toggle='modal' data-target='#create_dir' onclick=\"edit_folder('" + folder_data[i].id + "','" + folder_data[i].folder_name + "','" + folder_data[i].group_ids + "')\"></i>";
                    }
                <?php } ?>
                html += `<div class="d-inline-flex">
                ` + html_edit + `
                        <button class="folder-container" onclick="folder_click(this)" data-id="` + folder_data[i].id + `" >
                            <div class="folder-icon">
                                <i class="fa fa-folder folder-icon-color"></i>
                            </div>
                            <div class="folder-name">` + folder_data[i].folder_name + `</div>
                        </button>
                    </div>`;
            }
            $("#main-folders").html(html); // Use html() instead of appendTo()
            hide_loader();

            // set_functionality();
        }
    }

    function set_files(files) {

        if (files.length > 0) {
            let html = "";
            for (i = 0; i < files.length; i++) {
                let html_edit = ``;

                html += `<div class="d-inline-flex">
                ` + html_edit + `
                <a href='` + files[i].path + `' target='_blank'>
                                            <button class="folder-container">
                                                <div class="folder-icon">
                                                    <i class="fa fa-file file-icon-color"></i>
                                                </div>
                                                <div class="folder-name">` + files[i].file_name + `</div>
                                            </button>
                                            </a>
                                        </div>`;
            }

            $("#main-files").html(html); // Use html() instead of appendTo()
            // set_functionality();
            hide_loader();
        }
    }

    function save_and_show_files() {
        show_loader();
        let index = $('#foldersGroup .breadcrumb li.breadcrumb-item.active').attr("data-id");
        // Get the file input element
        var input = $('#upload_file')[0];
        var current_dir = $('.current_dir').text();
        // Create a new FormData object
        var formData = new FormData();
        // Loop through each file in the input element
        for (var i = 0; i < input.files.length; i++) {
            // Append each file to the FormData object
            formData.append('files[]', input.files[i]);
        }
        formData.append('index', index);
        formData.append('current_dir', current_dir);
        formData.append('csrf_token_name', $("input[name='csrf_token_name']").val());

        $.ajax({
            url: '<?php echo admin_url("Knowledge_base/upload_dir_data"); ?>',
            type: 'POST',
            data: formData,
            processData: false, // Prevent jQuery from processing the data
            contentType: false, // Prevent jQuery from setting contentType
            success: function(response) {
                let data = JSON.parse(response);
                if (data.status == 1) {
                    alert_float('success', data.message);
                }
                $(".close-modal").trigger("click");
                save_and_show_folder(1, index);
                hide_loader();
            }
        });
    }


    function save_and_show_folder_update() {
        show_loader();
        let index = $('#foldersGroup .breadcrumb li.breadcrumb-item.active').attr("data-id");
        // Get the file input element
        var input = $('#upload_file')[0];
        var current_dir = $('.current_dir').text();
        // Create a new FormData object
        var formData = new FormData();
        // Loop through each file in the input element
        for (var i = 0; i < input.files.length; i++) {
            // Append each file to the FormData object
            formData.append('files[]', input.files[i]);
        }
        formData.append('index', index);
        formData.append('current_dir', current_dir);
        formData.append('csrf_token_name', $("input[name='csrf_token_name']").val());

        $.ajax({
            url: '<?php echo admin_url("Knowledge_base/upload_dir_data"); ?>',
            type: 'POST',
            data: formData,
            processData: false, // Prevent jQuery from processing the data
            contentType: false, // Prevent jQuery from setting contentType
            success: function(response) {
                let data = JSON.parse(response);
                if (data.success == 1) {
                    alert_float('success', "Files upload successfully");
                }
                $(".close-modal").trigger("click");
                save_and_show_folder(1, index);
                hide_loader();
            },
            error: function() {
                hide_loader();
                // Handle error case here, such as displaying an error message to the user
                alert_float('error', 'An error occurred while processing your request.');
            }
        });
    }


    function save_and_show_folder(status = '', index = '') {
        show_loader();
        if (status == 1) {

            $.ajax({
                url: '<?php echo admin_url("Knowledge_base/create_folder"); ?>',
                type: 'POST',
                data: {
                    show_folder: 1,
                    index: index
                },
                success: function(response) {
                    $(".close-modal").trigger("click");
                    let data = JSON.parse(response);
                    if (data.folder != undefined) {
                        if (data.folder.length > 0) {
                            set_folder(data.folder);
                            hide_loader();
                        } else {
                            hide_loader();
                            $("#main-folders").html('');
                        }
                    }

                    if (data.files != undefined) {

                        if (data.files.length > 0) {
                            set_files(data.files);
                            hide_loader();
                        } else {
                            hide_loader();
                            $("#main-files").html('');
                        }
                    }
                    hide_loader();
                },
                error: function() {
                    hide_loader();
                    // Handle error case here, such as displaying an error message to the user
                    alert_float('error', 'An error occurred while processing your request.');
                }
            });
        } else {
            var folderName = $('#folderName').val();
            var folder_id = $('#folder_id').val();
            var group_id = $('#group_id').val();
            var current_dir = $('.current_dir').text();
            let index = $('#foldersGroup .breadcrumb li.breadcrumb-item.active').attr("data-id");

            if (!folderName) {
                $('#folderName').focus();
                hide_loader();
                return false;
            }
            if (group_id.length <= 0) {
                $('#group_id').focus();
                hide_loader();
                return false;
            }


            $.ajax({
                url: '<?php echo admin_url("Knowledge_base/create_folder"); ?>',
                type: 'POST',
                data: {
                    folder_names: current_dir + folderName,
                    group_id: group_id,
                    index: index,
                    folder_id: folder_id
                },
                success: function(response) {
                    $(".close-modal").trigger("click");
                    let data = JSON.parse(response);
                    if (data.success == 1 && folder_id != '') {
                        alert_float('success', "Folder update successfully");
                    } else if (data.success == 1) {
                        alert_float('success', "Folder create successfully");
                    }
                    if (data.folder != undefined) {

                        if (data.folder.length > 0) {
                            set_folder(data.folder);
                        } else {
                            hide_loader();
                            $("#main-folders").html('');
                        }
                    }
                    hide_loader();
                },
                error: function() {
                    hide_loader();
                    // Handle error case here, such as displaying an error message to the user
                    alert_float('error', 'An error occurred while processing your request.');
                }
            });
        }
    }

    $(document).ready(function() {
        $('#createFolderBtn').click(function() {
            show_loader();
            save_and_show_folder();
        });
        $('#uploadFolderBtn').click(function() {
            show_loader();
            save_and_show_files();
        });

    });

    save_and_show_folder(1, 0);

    $('#btn-list').on('click', function() {
        $('#main-folders').addClass('flex-column');
        $('#btn-grid').removeClass('active')
        $(this).addClass('active')
    });
    $('#btn-grid').on('click', function() {
        $('#main-folders').removeClass('flex-column');
        $('#btn-list').removeClass('active')
        $(this).addClass('active')
    });
    $('#btn-list').on('click', function() {
        $('#main-files').addClass('flex-column');
        $('#btn-grid').removeClass('active')
        $(this).addClass('active')
    });
    $('#btn-grid').on('click', function() {
        $('#main-files').removeClass('flex-column');
        $('#btn-list').removeClass('active')
        $(this).addClass('active')
    });

    function breadcrumb_click(obj) {
        let index = $(obj).attr("data-id");
        let activeIndex = $('#foldersGroup .breadcrumb li.breadcrumb-item.active').index();

        // Check if the clicked item is already active
        if (activeIndex === index) {
            return; // Do nothing if already active
        }

        // Remove all items after the clicked item
        $(obj).nextAll().remove();

        // Call the function to show folders with the specified index
        save_and_show_folder(1, index);
        hide_loader();
    }


    // Open folder and see files
    function folder_click(obj) {
        let folderName = $(obj).find(".folder-name").text();
        let index = $(obj).attr("data-id");



        $(".breadcrumb-item").removeClass("active");
        let breadcrumbItem = $('<li onclick="breadcrumb_click(this)" class="breadcrumb-item active" data-id="' + index + '"></li>').text(folderName);
        $('#foldersGroup .breadcrumb').append(breadcrumbItem);
        save_and_show_folder(1, index);
        var textAfterFirstOccurrence = "";

        $("ol li").slice(1).each(function() {
            var text = $(this).text().trim();
            console.log(text);
            if (text != undefined && text != "") {
                textAfterFirstOccurrence += text + "/";
            }
        });

        console.log(textAfterFirstOccurrence);

        $(".current_dir").text(textAfterFirstOccurrence);
    }
</script>


</body>

</html>