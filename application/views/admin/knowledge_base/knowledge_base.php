<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$has_permission_edit = has_permission('knowledge_base', '', 'edit');
$has_permission_create = has_permission('knowledge_base', '', 'create');
$has_permission_delete = has_permission('knowledge_base', '', 'delete');
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


    .no-data {
        text-align: center;
        color: #888;
        font-size: 16px;
        padding: 20px;
    }

    .dx-theme-generic-typography a {
        color: unset !important;
    }

    .card-folders .card-body>.breadcrumb {
        margin-left: -1.25em;
        margin-right: -1.25em;
        margin-top: -1.25em;
        border-radius: 0;
    }

    .folder-container {
        text-align: center;
        /* margin-left: 1rem;
        margin-right: 1rem;
        margin-bottom: 1.5rem; */
        width: 100%;
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

    /* .edit_folder_data {
        position: relative;
        left: 100%;
        bottom: 25%;
        font-size: large;
        cursor: pointer;
    }

    .delete_folder_data {
        position: relative;
        left: 0%;
        bottom: 0%;
        font-size: large;
        cursor: pointer;
    } */

    i.images-list {
        cursor: pointer;
        font-size: 20px !important;
    }



    /* .hover-show-edit:hover>i.fa {
        cursor: pointer;
        display: block !important;
    } */

    .hover-show-edit {
        display: flow !important;
        cursor: pointer;
    }

    .dx-datagrid-content .dx-datagrid-table .dx-row>td,
    .dx-datagrid-content .dx-datagrid-table .dx-row>tr>td {
        overflow: hidden !important;
        width: -webkit-fill-available;
    }

    .dx-filemanager .dx-filemanager-files-view.dx-filemanager-details .dx-filemanager-details-item-thumbnail {
        float: right;
    }

    .dx-freespace-row {
        display: none !important;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s mtop5">
                    <div class="panel-body">

                        <div class="dx-viewport demo-container">
                            <div id="file-manager"></div>
                        </div>


                        <div class="_buttons">
                            <?php if ($has_permission_create) { ?>
                                <!-- <a href="<?php echo admin_url('knowledge_base/create_knowledge_base'); ?>" class="btn btn-info mright5"><?php echo _l('kb_new_knowledge'); ?></a> -->
                            <?php } ?>
                            <?php if ($has_permission_create) { ?>
                                <!-- <a href="<?php echo admin_url('knowledge_base/manage_knowledge_groups'); ?>" class="btn btn-info mright5"><?php echo _l('kb_knowledge_group'); ?></a>
                                <a href="#" onclick="set_modal('folder')" data-toggle="modal" data-target="#create_dir" class="btn btn-info mright5"><i class="fa fa-folder"></i> <?php echo _l('create_dir'); ?></a>
                                <a href="#" onclick="set_modal('upload')" data-toggle="modal" data-target="#create_dir" class="btn btn-info mright5"><i class="fa fa-upload"></i> <?php echo _l('upload_dir_files'); ?></a> -->
                            <?php } ?>


                        </div>
                        <hr class="hr-panel-heading" />

                        <!-- <div class="container">

                            <div class="card card-folders">
                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col col-md-6 mr-auto">
                                            <h4 class="card-title m-0">Folders</h4>
                                        </div>
                                        <div class="col col-md-6 text-right col-auto pr-2 ">
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

                        </div> -->

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
                        <button type="button" id="createFolderBtn" class="btn btn-primary">Create</button>
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
<link rel="stylesheet" href="<?= base_url() ?>assets/css/dx.common.css">
<link rel="stylesheet" href="<?= base_url() ?>assets/css/dx.light.css">
<script src="<?= base_url() ?>assets/js/dx.all.js"></script>
<!-- <link rel="stylesheet" href="https://cdn3.devexpress.com/jslib/21.2.5/css/dx.common.css">
<link rel="stylesheet" href="https://cdn3.devexpress.com/jslib/21.2.5/css/dx.light.css">
<script src="https://cdn3.devexpress.com/jslib/21.2.5/js/dx.all.js"></script> -->


<script>
    var current_user = "<?= get_staff_user_id() ?>";
    var is_admin = "<?= is_admin() ?>";
    var pathInfo_info = [];
    var pathInfo_info_ = [];

    function set_modal(target) {
        // Hide all modal forms
        $("#folder_id").val("");
        $(".modal-form").hide();
        $("#createFolderBtn").text("Create");


        // Clear input fields in the modal body
        $(".modal-body input").val('');

        // Reset selectpicker values in the modal body
        $(".modal-body select").selectpicker('val', '');

        // Show the modal form based on the target
        $(".modal-form-" + target).show();

    }

    function edit_folder(id = "", name = "", group_ids = "") {
        // console.log("edit-folder");
        set_modal("folder");
        let group_id = group_ids.split(",");
        $("#folder_id").val(id);
        $("#folderName").val(name);
        $('#group_id').selectpicker('val', group_id);
        $("#createFolderBtn").text("Update");
    }

    function delete_(id, type) {
        if (confirm("Are you sure you want to delete this " + type + "?")) {
            var formData = new FormData();
            formData.append('id', id);
            formData.append('type', type);
            formData.append('csrf_token_name', $("input[name='csrf_token_name']").val());

            $.ajax({
                url: '<?php echo admin_url("Knowledge_base/delete"); ?>',
                type: 'POST',
                data: formData,
                processData: false, // Prevent jQuery from processing the data
                contentType: false, // Prevent jQuery from setting contentType
                success: function(response) {
                    let data = JSON.parse(response);
                    if (data.status == 1) {
                        alert_float('success', data.message);
                    }
                    // $(".close-modal").trigger("click");
                    $(".dx-toolbar-items-container .dx-filemanager-i-refresh").trigger("dxclick");
                    // save_and_show_folder(1, index);
                    hide_loader();
                },
                error: function() {
                    hide_loader();
                    // Handle error case here, such as displaying an error message to the user
                    alert_float('danger', 'An error occurred while processing your request.');
                },
            });
        }
    }

    // function set_folder(folder_data) {

    //     if (folder_data.length > 0) {
    //         let html = "";
    //         for (i = 0; i < folder_data.length; i++) {
    //             let html_edit = ``;
    //             <?php if ($has_permission_edit || is_admin()) {

                        //             
                    ?>
    //                 if ((folder_data[i].edit == 1 || is_admin == 1) && (is_admin == 1 || current_user == [i].created_by)) {
    //                     html_edit = "<i class='fa fa-edit edit_folder_data' data-toggle='modal' data-target='#create_dir' onclick=\"edit_folder('" + folder_data[i].id + "','" + folder_data[i].folder_name + "','" + folder_data[i].group_ids + "')\"></i>";
    //                 }
    //             <?php } ?>

    //             <?php if ($has_permission_delete || is_admin()) {

                        //             
                    ?>
    //                 if (folder_data[i].created_by == current_user || is_admin == 1) {
    //                     html_edit += "<i class='fa text-danger fa-trash delete_folder_data'  onclick=\"delete_folder('" + folder_data[i].id + "')\"></i>";
    //                 }
    //             <?php } ?>
    //             html += `<div class="d-inline-flex">
    //             ` + html_edit + `
    //                     <button class="folder-container" onclick="folder_click(this)" data-id="` + folder_data[i].id + `" >
    //                         <div class="folder-icon">
    //                             <i class="fa fa-folder folder-icon-color"></i>
    //                         </div>
    //                         <div class="folder-name">` + folder_data[i].folder_name + `</div>
    //                     </button>
    //                 </div>`;
    //         }
    //         $("#main-folders").html(html); // Use html() instead of appendTo()
    //         hide_loader();

    //         // set_functionality();
    //     }
    // }

    // function set_files(files) {

    //     if (files.length > 0) {
    //         let html = "";
    //         for (i = 0; i < files.length; i++) {
    //             let html_edit = ``;

    //             html += `<div class="d-inline-flex">
    //             ` + html_edit + `
    //             <a href='` + files[i].path + `' target='_blank'>
    //                                         <button class="folder-container">
    //                                             <div class="folder-icon">
    //                                                 <i class="fa fa-file file-icon-color"></i>
    //                                                 <i class="fa hide fa-filetype-` + (files[i].type).toLowerCase() + `"></i>
    //                                             </div>
    //                                             <div class="folder-name">` + files[i].file_name + `</div>
    //                                         </button>
    //                                         </a>
    //                                     </div>`;
    //         }

    //         $("#main-files").html(html); // Use html() instead of appendTo()
    //         // set_functionality();
    //         hide_loader();
    //     }
    // }

    function save_and_show_files() {
        show_loader();
        let index = pathInfo_info.length > 0 ? pathInfo_info[pathInfo_info.length - 1].key : 0;
        // Get the file input element
        var input = $('#upload_file')[0];
        var elements = $(".dx-menu-item-text").map(function() {
            return $(this).text();
        }).get();

        // Remove the first element
        var valuesAfterFirst = elements.slice(1);

        // Join the remaining elements with "/"
        var joinedValues = valuesAfterFirst.join("/");

        // Remove trailing "/" if it exists
        if (joinedValues.length > 0 && joinedValues.slice(-1) === "/") {
            joinedValues = joinedValues.slice(0, -1);
        }
        var current_dir = joinedValues;
        // Create a new For mData object
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
                $(".dx-toolbar-items-container .dx-filemanager-i-refresh").trigger("dxclick");

                // save_and_show_folder(1, index);
                hide_loader();
            },
            error: function() {
                hide_loader();
                // Handle error case here, such as displaying an error message to the user
                alert_float('danger', 'An error occurred while processing your request.');
            },
        });
    }


    function save_and_show_folder_update() {
        show_loader();
        let index = $('#foldersGroup .breadcrumb li.breadcrumb-item.active').attr("data-id");
        // Get the file input element
        var input = $('#upload_file')[0];
        var elements = $(".dx-menu-item-text").map(function() {
            return $(this).text();
        }).get();

        // Remove the first element
        var valuesAfterFirst = elements.slice(1);

        // Join the remaining elements with "/"
        var joinedValues = valuesAfterFirst.join("/");

        // Remove trailing "/" if it exists
        if (joinedValues.length > 0 && joinedValues.slice(-1) === "/") {
            joinedValues = joinedValues.slice(0, -1);
        }
        var current_dir = joinedValues;
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
                // save_and_show_folder(1, index);
                $(".dx-toolbar-items-container .dx-filemanager-i-refresh").trigger("dxclick");

                hide_loader();
            },
            error: function() {
                hide_loader();
                // Handle error case here, such as displaying an error message to the user
                alert_float('danger', 'An error occurred while processing your request.');
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

                    // if (data.result != undefined && data.result.length > 0) {
                    //     var fileManagerItems = [{
                    //             "id": 1,
                    //             "name": "Folder 1",
                    //             "isDirectory": true,
                    //             "parentId": "",
                    //             "size": 0,
                    //             "file_type": "Folder",
                    //             "modify_date": "2024-05-20",
                    //             "permissions": "read_write",
                    //             "owner": "John Doe"
                    //         },
                    //         {
                    //             "id": 2,
                    //             "name": "File 1",
                    //             "isDirectory": false,
                    //             "parentId": "1",
                    //             "size": 1024,
                    //             "file_type": "Text File",
                    //             "modify_date": "2024-05-21",
                    //             "permissions": "read_only",
                    //             "owner": "Jane Smith"
                    //         },
                    //         {
                    //             "id": 3,
                    //             "name": "File 2",
                    //             "isDirectory": false,
                    //             "parentId": "1",
                    //             "size": 2048,
                    //             "file_type": "Image File",
                    //             "modify_date": "2024-05-22",
                    //             "permissions": "read_write",
                    //             "owner": "John Doe"
                    //         }
                    //     ];

                    //     $("#file-manager").dxFileManager({
                    //         name: "fileManager",
                    //         // fileProvider: customProvider,
                    //         fileSystemProvider: {
                    //             type: "custom",
                    //             getItems: function(parentDir) {
                    //                 return $.Deferred().resolve(fileManagerItems).promise();
                    //             },
                    //             createDirectory: function(parentDir, name) {
                    //                 // Implement logic to create a directory
                    //             },
                    //             uploadFileChunk: function(fileData, chunksInfo, destinationDir) {
                    //                 // Implement logic to upload file chunks
                    //             },
                    //             permissions: {
                    //                 // Set permissions for various operations
                    //                 download: true, // Enable download
                    //                 create: true, // Enable create (to show the create button)
                    //                 copy: true, // Enable copy (to show the copy/paste buttons)
                    //                 move: true, // Enable move (to show the move button)
                    //                 remove: true, // Enable remove (to show the delete button)
                    //                 rename: true, // Enable rename
                    //                 upload: true // Enable upload (to show the upload button)
                    //             }
                    //         }
                    //     });
                    // }
                    hide_loader();
                    $(".dx-toolbar-items-container .dx-filemanager-i-refresh").trigger("dxclick");

                    // if (data.folder != undefined) {
                    //     if (data.folder.length > 0) {
                    //         set_folder(data.folder);
                    //         hide_loader();
                    //     } else {
                    //         hide_loader();
                    //         $("#main-folders").html('');
                    //     }
                    // }

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

            var elements = $(".dx-menu-item-text").map(function() {
                return $(this).text();
            }).get();

            // Remove the first element
            var valuesAfterFirst = elements.slice(1);

            // Join the remaining elements with "/"
            var joinedValues = valuesAfterFirst.join("/");

            // Remove trailing "/" if it exists
            if (joinedValues.length > 0 && joinedValues.slice(-1) === "/") {
                joinedValues = joinedValues.slice(0, -1);
            }

            var current_dir = joinedValues;
            var folder_name = valuesAfterFirst.pop();
            var index = pathInfo_info.length > 0 ? pathInfo_info[pathInfo_info.length - 1].key : 0;



            var folderName = $('#folderName').val();
            var folder_id = $("#folder_id").val();
            var group_id = $('#group_id').val();


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
                    folder_names: current_dir + "/" + folderName,
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

                        // if (data.folder.length > 0) {
                        //     set_folder(data.folder);
                        // } else {
                        //     hide_loader();
                        //     $("#main-folders").html('');
                        // }

                        $(".dx-toolbar-items-container .dx-filemanager-i-refresh").trigger("dxclick");

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

    // save_and_show_folder(1, 0);

    // $('#btn-list').on('click', function() {
    //     $('#main-folders').addClass('flex-column');
    //     $('#btn-grid').removeClass('active')
    //     $(this).addClass('active')
    // });
    // $('#btn-grid').on('click', function() {
    //     $('#main-folders').removeClass('flex-column');
    //     $('#btn-list').removeClass('active')
    //     $(this).addClass('active')
    // });
    // $('#btn-list').on('click', function() {
    //     $('#main-files').addClass('flex-column');
    //     $('#btn-grid').removeClass('active')
    //     $(this).addClass('active')
    // });
    // $('#btn-grid').on('click', function() {
    //     $('#main-files').removeClass('flex-column');
    //     $('#btn-list').removeClass('active')
    //     $(this).addClass('active')
    // });

    // function breadcrumb_click(obj) {
    //     let index = $(obj).attr("data-id");
    //     let activeIndex = $('#foldersGroup .breadcrumb li.breadcrumb-item.active').index();

    //     // Check if the clicked item is already active
    //     if (activeIndex === index) {
    //         return; // Do nothing if already active
    //     }

    //     // Remove all items after the clicked item
    //     $(obj).nextAll().remove();

    //     // Call the function to show folders with the specified index
    //     save_and_show_folder(1, index);
    //     hide_loader();
    // }


    // Open folder and see files
    // function folder_click(obj) {
    //     let folderName = $(obj).find(".folder-name").text();
    //     let index = $(obj).attr("data-id");



    //     $(".breadcrumb-item").removeClass("active");
    //     let breadcrumbItem = $('<li onclick="breadcrumb_click(this)" class="breadcrumb-item active" data-id="' + index + '"></li>').text(folderName);
    //     $('#foldersGroup .breadcrumb').append(breadcrumbItem);
    //     save_and_show_folder(1, index);
    //     var textAfterFirstOccurrence = "";

    //     $("ol li").slice(1).each(function() {
    //         var text = $(this).text().trim();
    // console.log(text);
    //         if (text != undefined && text != "") {
    //             textAfterFirstOccurrence += text + "/";
    //         }
    //     });

    // console.log(textAfterFirstOccurrence);

    //     $(".current_dir").text(textAfterFirstOccurrence);
    // }

    // Function to fetch data from the API
    // function fetchDataFromAPI(index = 0) {
    //     // Return a Promise to handle the asynchronous nature of the AJAX request
    //     return new Promise(function(resolve, reject) {
    //         $.ajax({
    //             url: '<?php echo admin_url("Knowledge_base/create_folder"); ?>',
    //             type: 'POST',
    //             data: {
    //                 show_folder: 1,
    //                 index: 0
    //             },
    //             success: function(response) {
    //                 let jsonData = JSON.parse(response);
    //                 if (jsonData.result.length > 0) {
    //                     resolve(jsonData.result);
    //                 } else {
    //                     resolve([]); // Resolve the Promise with an empty array if there's no data
    //                 }
    //             },
    //             error: function(xhr, status, error) {
    //                 reject(error); // Reject the Promise if there's an error
    //             }
    //         });
    //     });
    // }

    // Initialize the DevExpress FileManager widget

    // Define an asynchronous function to initialize the DevExpress FileManager

    const fileManager = "";
    var permissions = {
        create: false,
        copy: false,
        move: false,
        delete: false,
        rename: false,
        upload: false,
        download: false
    };

    <?php if (is_Admin()) { ?>
        permissions = {
            create: false,
            copy: true,
            move: true,
            delete: true,
            rename: true,
            upload: false,
            download: false
        };
    <?php } ?>


    function addUniqueValue(value) {
        if (!pathInfo_info_.includes(value)) {
            pathInfo_info_.push(value);
        }
    }
    var uniqueParam = new Date().getTime();
    async function initializeFileManager(index = 0) {

        var remoteProvider = new DevExpress.fileProviders.Remote({
            endpointUrl: `<?php echo admin_url("Knowledge_base/get_knowledge_base_dir"); ?>?timestamp=${uniqueParam}`,
        });
        var customProvider = new DevExpress.fileProviders.Custom({

            getItems: pathInfo => {
                return remoteProvider.getItems(pathInfo)
                    .then(function(result) {
                        pathInfo_info = pathInfo;
                        console.log(pathInfo);
                        if (pathInfo.length > 0) {
                            for (i = 0; i < pathInfo.length; i++) {
                                if (pathInfo[i].key != undefined && pathInfo[i].name != undefined) {
                                    addUniqueValue(pathInfo[i].key + "_" + pathInfo[i].name);
                                }
                            }
                        }
                        let new_array = [];
                        result.forEach(function(item) {
                            new_array.push(item.dataItem); // Push the current item into the new array
                        });
                        checkEmptyDirectory();
                        set_();
                        // Handle the result as needed
                        return new_array; // Make sure to return the result to continue the promise chain
                    })
                    .catch(function(error) {
                        console.error('Error fetching items:', error);
                        throw error; // Rethrow the error to propagate it to the caller
                    });
            },

            // createDirectory: (parentDir, name) => remoteProvider.createFolder(parentDir, name),
            renameItem: (item, name) => remoteProvider.renameItem(item, name),
            deleteItem: item => remoteProvider.deleteItems([item]),
            copyItem: (item, destDir) => remoteProvider.copyItems([item], destDir),
            moveItem: (item, destDir) => remoteProvider.moveItems([item], destDir),
            uploadFileChunk: (fileData, uploadInfo, destDirectory) => {
                return remoteProvider.uploadFileChunk(fileData, uploadInfo, destDirectory);
            },
            downloadItems: items => remoteProvider.downloadItems(items),
            uploadChunkSize: 100000000000
        });

        var fileManager_ = $("#file-manager").dxFileManager({
            name: "fileManager",
            fileProvider: customProvider,
            customizeDetailColumns: function(columns) {
                // console.log(options);
                // Add new custom columns
                columns.splice(0, columns.length);

                columns.push({
                    dataField: 'show_name',
                    wordWrapEnabled: true,
                    caption: "Name",
                    width: "250px",
                    cellTemplate: function(container, options) {
                        console.log("hhhhhh");
                        let iconClass = "";
                        let fileItem = options.data.fileItem.dataItem;
                        let fileName = fileItem.name;
                        let fileType = fileItem._type;
                        let file_type = fileItem.file_type;
                        let id = fileItem.key;
                        let group_id = fileItem.group_ids;
                        let file_path = fileItem.file_path;
                        let editHtml = "";
                        let check_ = "";
                        let elements = $(".dx-menu-item-text").map(function() {
                            return $(this).text();
                        }).get();

                        // Remove the first element
                        let valuesAfterFirst = elements.slice(1);


                        // Join the remaining elements with "/"
                        let joinedValues = valuesAfterFirst

                        if (joinedValues.length > 0) {
                            check_ = joinedValues[joinedValues.length - 1];
                        }

                        console.log(check_);

                        // Generate edit HTML if user is admin


                        // Determine the icon class based on the file type
                        switch (fileType) {
                            case "folder":
                            case "null":
                                iconClass = "folder";
                                break;
                            case "pdf":
                                iconClass = "pdffile";
                                break;
                            case "png":
                            case "jpg":
                            case "jpeg":
                            case "webp":
                            case "gif":
                            case "svg":
                                iconClass = "image";
                                break;
                            case "docx":
                            case "doc":
                                iconClass = "docfile";
                                break;
                            case "xlsx":
                            case "xls":
                                iconClass = "xlsfile";
                                break;
                            case "txt":
                                iconClass = "txtfile";
                                break;
                            default:
                                // Handle other file types
                                iconClass = fileType + "file";
                                break;
                        }
                        let check_dir = pathInfo_info ? pathInfo_info[pathInfo_info.length - 1] : [];


                        // Append icon, edit button, and name elements to the container
                        if (pathInfo_info_.includes(id + "_" + fileName) && check_ == fileName) {
                            container.append(`<div class='hide-tr'> <i class="images-list dx-icon-${iconClass}">...</div>`);

                        } else {
                            container.append(`<div class='hover-show-edit'> <i class="images-list dx-icon-${iconClass}"></i> ${fileName}</div>`);
                        }
                    }
                }, {
                    dataField: 'creationBy',
                    wordWrapEnabled: true,
                    caption: "Created By",
                    width: "200px",
                    cellTemplate: function(container, options) {
                        let fileItem = options.data.fileItem.dataItem;
                        let id = fileItem.key;
                        let fileName = fileItem.name;
                        let creationBy = fileItem.creationBy;
                        let check_dir = pathInfo_info ? pathInfo_info[pathInfo_info.length - 1] : [];
                        let elements = $(".dx-menu-item-text").map(function() {
                            return $(this).text();
                        }).get();

                        // Remove the first element
                        let valuesAfterFirst = elements.slice(1);

                        // Join the remaining elements with "/"
                        let joinedValues = valuesAfterFirst

                        if (joinedValues.length > 0) {
                            check_ = joinedValues[joinedValues.length - 1];
                        }
                        if (pathInfo_info_.includes(id + "_" + fileName) && check_ == fileName) {} else {
                            container.append(`<div class='hover-show-edit'>${creationBy}</div>`);

                        }
                    }
                }, {
                    dataField: 'creationDate',
                    wordWrapEnabled: true,
                    caption: "Created Date",
                    width: "200px",
                    cellTemplate: function(container, options) {
                        let fileItem = options.data.fileItem.dataItem;
                        let id = fileItem.key;
                        let fileName = fileItem.name;
                        let date = fileItem.creationDate;
                        let check_dir = pathInfo_info ? pathInfo_info[pathInfo_info.length - 1] : [];
                        let elements = $(".dx-menu-item-text").map(function() {
                            return $(this).text();
                        }).get();

                        // Remove the first element
                        let valuesAfterFirst = elements.slice(1);

                        // Join the remaining elements with "/"
                        let joinedValues = valuesAfterFirst

                        if (joinedValues.length > 0) {
                            check_ = joinedValues[joinedValues.length - 1];
                        }
                        if (pathInfo_info_.includes(id + "_" + fileName) && check_ == fileName) {} else {
                            container.append(`<div class='hover-show-edit'>${date}</div>`);
                        }
                    }
                }, {
                    dataField: '',
                    wordWrapEnabled: true,
                    caption: "Action",
                    width: "150px",
                    cellTemplate: function(container, options) {
                        let fileItem = options.data.fileItem.dataItem;
                        let fileName = fileItem.name;
                        let filePath = fileItem.file_path;
                        let id = fileItem.key;
                        let groupId = fileItem.group_ids;
                        let fileType = fileItem._type;
                        let file_type = fileItem.file_type;
                        let editHtml = "";
                        let elements = $(".dx-menu-item-text").map(function() {
                            return $(this).text();
                        }).get();

                        // Remove the first element
                        let valuesAfterFirst = elements.slice(1);

                        // Join the remaining elements with "/"
                        let joinedValues = valuesAfterFirst

                        if (joinedValues.length > 0) {
                            check_ = joinedValues[joinedValues.length - 1];
                        }

                        <?php if (is_admin()) { ?>
                            if (fileType === "folder") {
                                editHtml = `<i class='fa fa-edit show-hover' data-toggle="modal" data-target="#create_dir" onclick="edit_folder(${id},'${fileName}','${groupId}')"></i>`;
                            }
                            editHtml += `&nbsp;<i class='fa fa-trash text-danger show-hover' onclick="delete_(${id},'${file_type}')"></i>`;
                        <?php } ?>

                        let check_dir = pathInfo_info ? pathInfo_info[pathInfo_info.length - 1] : [];
                        if (pathInfo_info_.includes(id + "_" + fileName) && check_ == fileName) {} else {
                            if (filePath && filePath !== "") {
                                container.append(`<div class='hover-show-edit'>${editHtml} &nbsp; <i class="images-list dx-icon dx-icon-download" onclick="download_file('${filePath}','${fileName}')"></i></div>`);
                            } else {
                                container.append(`<div class='hover-show-edit'>${editHtml}</div>`);
                            }
                        }
                    }

                });
                return columns;
            },
            allowedFileExtensions: [],
            height: 500,
            permissions

        });

    }




    function checkEmptyDirectory() {
        // setTimeout(() => {
        //     var elements = $(".dx-menu-item-text").map(function() {
        //         return $(this).text();
        //     }).get();

        //     // Remove the first element
        //     var valuesAfterFirst = elements.slice(1);
        //     var joinedValues = valuesAfterFirst.join("/");

        //     // Remove trailing "/" if it exists
        //     if (joinedValues.length > 0 && joinedValues.slice(-1) === "/") {
        //         joinedValues = joinedValues.slice(0, -1);
        //     }

        // console.log(joinedValues);
        //     if (joinedValues == "") {

        //     } else {
        //         $(".dx-datagrid-table").find("tbody tr.dx-data-row").first().remove();
        //     }
        //     // var currentDir = fileManagerInstance.getCurrentDirectory();

        //     if ($(".dx-datagrid-table").find("tbody tr.dx-data-row").length === 0) {
        //         $(".dx-filemanager-files-view .dx-datagrid-rowsview .dx-scrollable-content").append('<div class="no-data">No Data</div>');
        //     } else {
        //         $(".no-data").remove();
        //     }
        // }, 500);

    }

    function set_() {
        setTimeout(() => {
            if ($(".external-block").length > 0) {} else {
                $(".dx-filemanager-toolbar > .dx-toolbar > .dx-toolbar-items-container > .dx-toolbar-before").append(`<div  class='external-block'><a href="<?php echo admin_url('knowledge_base/manage_knowledge_groups'); ?>" class="btn btn-default mright5"><?php echo _l('kb_knowledge_group'); ?></a>
                                <a href="#" onclick="set_modal('folder')" data-toggle="modal" data-target="#create_dir" class="btn btn-default mright5"><i class="fa fa-folder"></i> <?php echo _l('create_dir'); ?></a>
                                <a href="#" onclick="set_modal('upload')" data-toggle="modal" data-target="#create_dir" class="btn btn-default mright5"><i class="fa fa-upload"></i> <?php echo _l('upload_dir_files'); ?></a></div>`);
            }

            // setTimeout(() => {
            //     // $('.dx-toolbar-items-container .dx-filemanager-i-refresh').trigger('dxclick');
            //     var elements = $(".dx-menu-item-text").map(function() {
            //         return $(this).text();
            //     }).get();

            //     // Remove the first element
            //     var valuesAfterFirst = elements.slice(1);

            //     // Join the remaining elements with "/"
            //     var joinedValues = valuesAfterFirst;
            //     console.log(pathInfo_info);

            //     if (joinedValues.length > 0) {
            //         pathInfo_info.slice(1)
            //     } else {
            //         pathInfo_info = [];
            //     }
            //     console.log(pathInfo_info);
            //     $(".dx-icon.dx-icon-arrowup").attr("onclick", "refresh()");
            //     $(".hide-tr").parents("tr").attr("onclick", "refresh()");
            //     $(".dx-menu-item-text").attr("onclick", "refresh()");

            // }, 300);

            // checkEmptyDirectory();
        }, 300);



    }


    function refresh() {
        setTimeout(() => {
            $('.dx-toolbar-items-container .dx-filemanager-i-refresh').trigger('dxclick');
        }, 100);
    }
    // Call the async function to initialize the DevExpress FileManager
    initializeFileManager();
    set_();


    function download_file(url, fileName) {
        // Fetch the file from the provided URL
        fetch(url)
            .then(response => {
                // Check if the response is successful
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.blob(); // Return the response body as a blob
            })
            .then(blob => {
                // Create a URL for the blob data
                const blobUrl = window.URL.createObjectURL(blob);

                // Create a temporary anchor element
                const a = document.createElement('a');
                a.href = blobUrl;
                a.download = fileName; // Set the download attribute to the file name
                document.body.appendChild(a);

                // Programmatically trigger a click event on the anchor element to start the download
                a.click();

                // Cleanup: remove the temporary anchor element and revoke the blob URL
                document.body.removeChild(a);
                window.URL.revokeObjectURL(blobUrl);
            })
            .catch(error => {
                // Handle any errors that occur during the fetch process
                console.error('Error downloading file:', error);
            });
    }


    // setTimeout(() => {
    //     $(".dx-filemanager-toolbar > .dx-toolbar > .dx-toolbar-items-container > .dx-toolbar-before").append(`<a href="<?php echo admin_url('knowledge_base/manage_knowledge_groups'); ?>" class="btn btn-default mright5"><?php echo _l('kb_knowledge_group'); ?></a>
    //                             <a href="#" onclick="set_modal('folder')" data-toggle="modal" data-target="#create_dir" class="btn btn-default mright5"><i class="fa fa-folder"></i> <?php echo _l('create_dir'); ?></a>
    //                             <a href="#" onclick="set_modal('upload')" data-toggle="modal" data-target="#create_dir" class="btn btn-default mright5"><i class="fa fa-upload"></i> <?php echo _l('upload_dir_files'); ?></a>`);

    // }, 2000);
</script>


</body>

</html>