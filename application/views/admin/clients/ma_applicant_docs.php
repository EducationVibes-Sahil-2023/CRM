<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
init_head();
// ---- Preselect the FIRST 5 documents on first load ----
$preselectedDocs = [];
$i = 0;
foreach ($clientsDocuments as $doc) {
    if ($i++ >= 5) break;
    $preselectedDocs[] = is_array($doc) ? $doc['id'] : $doc->id;
}
$clientsDocuments[] =array("id"=>"invitation_letter","name"=>"Invitation Letter");

$application_stage = get_applicant_stage_mbbs();
$application_sub_stage_mbbs = $this->clients_model->get_application_sub_stage_mbbs();

$filter_data = filter_country_university_array(2);
$university_list = $filter_data['universities'];
$country_list = $filter_data['countries'];
$staff = $filter_data['counselor'];
$sources = $filter_data['source'];
$statuses = get_applicant_statuses();
$sessionArray = [];

$startYear = 2023;
$endYear = date('Y') + 2;

for ($year = $startYear; $year <= $endYear; $year++) {
    $sessionArray[] = array("id"=>$year,"name"=>$year);
}


$client_type = [
   ["id" => "1", "name" => "EV"],
   ["id" => "2", "name" => "EVP"],

];

?>
<style>
    .margin-top
    {
        margin-top: 20px;
    }
</style>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <?php
         if (!is_admin() && !get_staff_user_id() == 309) { ?>
            <div class="col-md-12">
               <div class="panel_s">
                  <div class="panel-body">
                     <h4 class="text-center">You don't have access to the Applicant list. Contact your admin for more info.</h4>
                  </div>
               </div>
            </div>
      </div>
   </div>
</div>
<?php
            init_tail();
            echo '</body></html>';
            exit;
         } ?>
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div id="filterArea" class="hidden-xs">
                     <div class="row">
                        <div class="col-md-12">
                           <p class="bold"><?php echo _l('filter_by'); ?></p>
                        </div>
                        <div class="row col-md-12">
                            <div class="col-md-2 margin-top leads-filter-column filter_reset">
                           <?php echo render_select(
                               'documents[]',
                               $clientsDocuments,
                               array('id', 'name'),
                               '',
                               $preselectedDocs,
                               array(
                                   'data-width' => '100%',
                                   'data-none-selected-text' => 'Documents',
                                   'multiple' => true,
                                   'data-actions-box' => true
                               ),
                               array(), 'no-mbot', '', false, 'documents'
                           ); ?>
                        </div>
                        
                         <div class="col-md-2  margin-top leads-filter-column">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('university[]', $university_list, array('university_name', 'university_name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Primary University", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "university");
                                 echo '</div>';
                                 ?>
                              </div>
                              <div class="col-md-2  margin-top leads-filter-column">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('country[]', $country_list, array('country_name', 'country_name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Country", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "country");
                                 echo '</div>';
                                 ?>
                              </div>
                          
                               <div class="col-md-2  margin-top leads-filter-column">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('acadmic_year', $sessionArray, array('id','name'), '', '2026', array('data-width' => '100%', 'data-none-selected-text' => "Acadmic Year"), array(), 'no-mbot', '', false, "acadmic_year");
                                 echo '</div>';
                                 ?>
                              </div>
                              
                               <div class="col-md-2  margin-top leads-filter-column">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('client_type[]', $client_type, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Client type", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "client_type");
                                 echo '</div>';
                                 ?>
                              </div>
                                  
                              
                                  <div class="col-md-2 margin-top leads-filter-column">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('status_[]', $statuses, array('id', 'name'), '', array(1), array('data-width' => '100%', 'data-none-selected-text' => "Applicant Status", 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false, "status");
                                 echo '</div>';
                                 ?>
                              </div>
                        </div>
                          <div class="row col-md-12">

 <div class="col-md-2  margin-top leads-filter-column ">
                                 <?php
                                 array_unshift($application_stage, array());
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('view_application_stage', $application_stage, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('applicant_name_table'), 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_application_stage");
                                 echo '</div>';
                                 ?>
                              </div>
                              <div class="col-md-2  margin-top leads-filter-column ">
                                 <?php
                                 echo '<div id="leads-filter-source">';
                                 echo render_select('view_application_sub_stage', [], array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('Application Sub Stage'), 'data-actions-box' => true), array(), 'no-mbot', '', false, "view_application_sub_stage");
                                 echo '</div>';
                                 ?>
                              </div>
                              
                        <div class="col-md-3 margin-top">
                           <div class="form-group">
                              <button type="button" class="btn btn-primary" onclick="downloadSelectedDocs()" id="download">
                                 <i class="fa fa-file-archive-o"> Download </i> 
                              </button>
                              <button type="button" class="btn btn-primary" onclick="apply_filter()" id="apply_filter_">Apply <i class="fa fa-refresh"></i> </button>
                              <button type="button" class="btn btn-default" onclick="window.location.reload();">Reset</button>
                           </div>
                        </div>
                        </div>
                     </div>
                  </div>
                  <div class="clearfix mtop20"></div>
                  <table id="dynamicTable" class="table table-clients-docs sticky-header" style="width:100%">
                     <thead></thead>
                     <tbody></tbody>
                  </table>
               </div>
            </div>
         </div>
      </div><!-- /.row -->
   </div><!-- /.content -->
</div><!-- /#wrapper -->

<!-- Floating progress panel -->
<div id="downloadProgress"
     style="display:none; position:fixed; bottom:20px; right:20px; z-index:9999;
            background:#fff; border:1px solid #ddd; border-radius:6px;
            box-shadow:0 2px 12px rgba(0,0,0,.15); padding:15px 20px; width:300px;">
   <div style="margin-bottom:8px;">
      <strong>Downloading documents</strong>
      <span id="progressText" class="pull-right">0/0</span>
   </div>
   <progress id="progressBar" value="0" max="100" style="width:100%;"></progress>
   <div id="progressPercent" style="margin-top:4px;">0%</div>
</div>

<?php
init_tail();
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script>
   var tbllead_performance_column  = window.tbllead_performance_column || [];
   var default_columns             = window.default_columns || {};
   var column_names                = window.column_names || {};
   var columnHeaders               = [];
   var group_selection             = [];
   var selected_performance_column = window.selected_performance_column || [];
   var CustomersServerParams       = window.CustomersServerParams || {};
   var applicant_table             = null;
 var sub_category = <?= !empty($application_sub_stage_mbbs) ? json_encode($application_sub_stage_mbbs) : '[]' ?>;
   $(document).ready(function() {
      setTimeout(() => {
         updateColumns($("[name='documents[]']"));
         set_column_table();
         set_table();
      }, 500);
   });
   
   
     $('#view_application_stage').on('changed.bs.select', function(event, clickedIndex, newValue, oldValue) {
         let selectedValue = $(this).val();
         populateChildDropdown(selectedValue);
      });
      
   function populateChildDropdown(parentValue) {
      let childDropdown = $('#view_application_sub_stage');
      childDropdown.empty().append($('<option>', {
         value: '',
         text: ''
      }));

      let childOptions = sub_category.filter(item => item.application_tracker === parentValue);
      if (childOptions.length > 0) {
         childOptions.forEach(option => {
            childDropdown.append($('<option>', {
               value: option.id,
               text: option.name
            }));
         });
      }

      childDropdown.selectpicker('refresh');
   }


   function apply_filter() {
      updateColumns($("[name='documents[]']"));
      set_column_table();
      set_table();
   }

   function updateColumns(element) {
      enabled_column();
      tbllead_performance_column = [];
      group_selection = [];

      // Column 1: mass-select checkbox
      tbllead_performance_column.push({
         tbl_column_name: "",
         label_name: '<div class="checkbox mass_select_all_wrap">' +
                     '<input type="checkbox" id="mass_select_all" onchange="massDocSelection()">' +
                     '<label for="mass_select_all"></label>' +
                     '</div>'
      });

      // Column 2: Student Name — always present
      tbllead_performance_column.push({
         tbl_column_name: "student_name",
         label_name: 'Student Name'
      });
       tbllead_performance_column.push({
         tbl_column_name: "status_name",
         label_name: 'Status'
      });
        tbllead_performance_column.push({
         tbl_column_name: "client_type",
         label_name: 'Client Type'
      });
        tbllead_performance_column.push({
         tbl_column_name: "primary_university",
         label_name: 'Primary University'
      });
        tbllead_performance_column.push({
         tbl_column_name: "primary_country",
         label_name: 'Primary Country'
      });
           tbllead_performance_column.push({
         tbl_column_name: "application_stage",
         label_name: 'App Stage'
      });
           tbllead_performance_column.push({
         tbl_column_name: "application_sub_stage",
         label_name: 'App Sub Stage'
      });
      

      // Columns 3+: selected documents
      let selectedOptions = element.find('option:selected');
      selectedOptions.each(function() {
         let value = $(this).val();
         let additional_columns = $(this).attr("data-columns") || "";
         if (additional_columns !== "") {
            additional_columns.split(",").forEach(function(key) {
               let columnValue = key.trim();
               if (columnValue && default_columns[columnValue]) {
                  addColumn(columnValue, default_columns[columnValue].label_name);
                  group_selection.push(columnValue);
               }
            });
         } else {
            addColumn(value, $(this).text().trim());
         }
      });

      disabled_column();
   }

   function addColumn(tbl_column_name, label_name) {
      let exists = tbllead_performance_column.some(function(c) {
         return c.tbl_column_name === tbl_column_name;
      });
      if (!exists) {
         tbllead_performance_column.push({ tbl_column_name, label_name });
      }
   }

   function set_column_table() {
      columnHeaders = [];
      if (!Array.isArray(tbllead_performance_column) || tbllead_performance_column.length === 0) {
         return;
      }
      tbllead_performance_column.forEach(column => {
         let label = column.label_name && column.label_name.trim() !== ""
            ? column.label_name
            : column.tbl_column_name.replace(".", "_");
         column_names[column.tbl_column_name] = label.replace(/ /g, "_");
         columnHeaders.push({
            title: label,
            data: label.toLowerCase().replace(/ /g, "_")
         });
      });
      let thead = "<tr>";
      columnHeaders.forEach(header => {
         thead += "<th>" + header.title + "</th>";
      });
      thead += "</tr>";
      $("#dynamicTable thead").html(thead);
   }

   function enabled_column() {
      let columnSelect = $("[name='column_show[]']");
      if (columnSelect.length === 0) return;
      columnSelect.find("option").prop("disabled", false);
      columnSelect.selectpicker("refresh");
   }

   function disabled_column() {
      let columnSelect = $("[name='column_show[]']");
      if (columnSelect.length === 0) return;
      columnSelect.find("option").each(function() {
         $(this).prop("disabled", selected_performance_column.includes($(this).val()));
      });
      columnSelect.selectpicker("refresh");
   }

   function set_table() {
      enabled_column();
      if ($.fn.DataTable.isDataTable('.table-clients-docs')) {
         $('.table-clients-docs').DataTable().clear().destroy();
      }
      $('.table-clients-docs tbody').empty();
      $('._hidden_inputs._filters input').each(function() {
         CustomersServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
      });
      CustomersServerParams['documents'] = '[name="documents[]"]';
      CustomersServerParams['university'] = '[name="university[]"]';
      CustomersServerParams['country'] = '[name="country[]"]';
      CustomersServerParams['acadmic_year'] = '[name="acadmic_year"]';
      CustomersServerParams['client_type'] = '[name="client_type[]"]';
       CustomersServerParams['view_application_stage'] = '[name="view_application_stage"]';
       CustomersServerParams['view_application_sub_stage'] = '[name="view_application_sub_stage"]';
       CustomersServerParams['statuses'] = '[name="status_[]"]';
      applicant_table = initDataTable(
         '.table-clients-docs',
         admin_url + 'clients/ma_applicant_docs',
         [0],
         [0],
         CustomersServerParams,
         [1, "ASC"]           // sort by Student Name, not the checkbox column
      );
   }

   function massDocSelection() {
      $(".table-clients-docs tbody input[type='checkbox']")
         .prop("checked", $("#mass_select_all").is(":checked"));
   }

   // =====================================================================
   // DOWNLOAD SELECTED ROWS AS ZIP (client-side, JSZip)
   // =====================================================================

   function sanitizeName(s) {
      return String(s).replace(/[\\\/:*?"<>|]/g, "_").trim();
   }

   /**
    * Collect checked rows from the table and build:
    *   { "10th Marksheet": [ {student_name, url}, ... ], "Photograph": [...] }
    * Document name comes from the column header; URL from the cell's view link.
    */
   function downloadSelectedDocs() {
      const checkedRows = $('.table-clients-docs tbody tr').filter(function() {
         return $(this).find('td:first input[type="checkbox"]').is(':checked');
      });

      if (checkedRows.length === 0) {
         alert_float('warning', 'Select at least one student first');
         return;
      }

      const data = {};
      checkedRows.each(function() {
         const $tds        = $(this).find('td');
         const studentName = $tds.eq(1).text().trim();

         $tds.each(function(idx) {
            if (idx < 2) return;                       // skip checkbox + name
            const header = columnHeaders[idx] ? columnHeaders[idx].title : ('Document_' + idx);
            const link   = $(this).find('a[href]').first();   // the "view" anchor
            if (!link.length) return;                  // '-' cell → no document

            if (!data[header]) data[header] = [];
            data[header].push({ student_name: studentName, url: link.attr('href') });
         });
      });

      if (Object.keys(data).length === 0) {
         alert_float('warning', 'No documents found in the selected rows');
         return;
      }

      downloadAllDocuments(data);
   }

   async function downloadAllDocuments(data) {
      const zip   = new JSZip();
      const queue = [];
      for (const [docName, students] of Object.entries(data)) {
         students.forEach(student => queue.push({ docName, student }));
      }

      const total  = queue.length;
      let completed = 0;
      let failed    = 0;

      $("#download").prop('disabled', true);
      $("#downloadProgress").show();
      $("#progressBar").val(0);
      $("#progressPercent").text("0%");
      $("#progressText").text("0/" + total);

      // Fetch up to 6 files in parallel instead of one-by-one — much faster
      const CONCURRENCY = 6;

      async function worker() {
         while (queue.length > 0) {
            const item = queue.shift();
            try {
               const response = await fetch(item.student.url, { credentials: "include" });
               if (!response.ok) throw new Error("HTTP " + response.status);
               const blob = await response.blob();

               const ext      = (item.student.url.split(".").pop() || "pdf").split("?")[0];
               const folder   = zip.folder(sanitizeName(item.docName));
               const fileName = sanitizeName(item.student.student_name + "_" + item.docName) + "." + ext;
               folder.file(fileName, blob);
            } catch (e) {
               console.error("Failed:", item.student.url, e);
               failed++;
            }
            completed++;
            const percent = Math.round((completed / total) * 100);
            $("#progressBar").val(percent);
            $("#progressPercent").text(percent + "%");
            $("#progressText").text(completed + "/" + total);
         }
      }

      await Promise.all(
         Array.from({ length: Math.min(CONCURRENCY, total) }, () => worker())
      );

      const blob = await zip.generateAsync({ type: "blob" }, function(metadata) {
         $("#progressBar").val(metadata.percent);
         $("#progressPercent").text("Creating ZIP... " + Math.round(metadata.percent) + "%");
      });

      saveAs(blob, "Student_Documents.zip");

      $("#progressPercent").text(
         failed > 0 ? "Completed with " + failed + " failed file(s)" : "Completed ✔"
      );
      $("#download").prop('disabled', false);
      setTimeout(() => { $("#downloadProgress").hide(); }, 3000);
   }
</script>