<?php

defined('BASEPATH') or exit('No direct script access allowed');

function app_init_admin_sidebar_menu_items()
{
        $CI = &get_instance();

        $CI->app_menu->add_sidebar_menu_item('dashboard', [
                'name'     => _l('als_dashboard'),
                'href'     => admin_url(),
                'position' => 1,
                'icon'     => 'fa fa-home',
        ]);

        // if (
        //         has_permission('customers', '', 'view')
        //         || (have_assigned_customers()
        //                 || (!have_assigned_customers() && has_permission('customers', '', 'create')))
        // ) {
        //         $CI->app_menu->add_sidebar_menu_item('customers', [
        //                 'name'     => _l('als_clients'),
        //                 'href'     => admin_url('clients'),
        //                 'position' => 5,
        //                 'icon'     => 'fa fa-user-o',
        //         ]);
        // }

        if (has_permission('fees_structure', '', 'create') || get_staff_user_id() == 311) {

                $CI->app_menu->add_sidebar_menu_item('fees_structure', [
                        'collapse' => true,
                        'name'     => "Fees Structure",
                        'position' => 5,
                        'icon'     => 'fa fa-user-o',
                ]);


                // Pending Customers (MBBS Abroad)
                $CI->app_menu->add_sidebar_children_item('fees_structure', [
                        'slug'     => 'Fees/company',
                        'icon'     => 'fa fa-user-times',
                        'name'     => "EV Fees Structures",
                        'href'     => admin_url('Fees/company'),
                        'position' => 1,
                ]);


                // $CI->app_menu->add_sidebar_children_item('fees_structure', [
                //         'slug'     => 'Fees/partner',
                //         'icon'     => 'fa fa-user-times',
                //         'name'     => "Partner Fees Structures",
                //         'href'     => admin_url('Fees/partner'),
                //         'position' => 1,
                // ]);
        }


        if (
                has_permission('customers', '', 'view')
                || (
                        have_assigned_customers()
                        || (!have_assigned_customers() && has_permission('customers', '', 'create'))
                ) || has_permission('customers', '', 'applicant_view_document')
        ) {
                $CI->app_menu->add_sidebar_menu_item('customers', [
                        'collapse' => true,
                        'name'     => _l('als_clients'),
                        'position' => 5,
                        'icon'     => 'fa fa-user-o',
                ]);


                // Pending Customers (MBBS Abroad)
                $CI->app_menu->add_sidebar_children_item('customers', [
                        'slug'     => 'mbbs_abroad/customers',
                        'icon'     => 'fa fa-user-times',
                        'name'     => "Pend. Customers",
                        'href'     => admin_url('clients/customers/mbbs_abroad'),
                        'position' => 2,
                ]);

                // MBBS Abroad Applicant
                $CI->app_menu->add_sidebar_children_item('customers', [
                        'slug'     => 'mbbs_abroad',
                        'icon'     => 'fa fa-users',
                        'name'     => "MA Applicant",
                        'href'     => admin_url('clients/mbbs_abroad'),
                        'position' => 3,
                ]);



                // Study Abroad Applicant
                $CI->app_menu->add_sidebar_children_item('customers', [
                        'slug'     => 'study_abroad',
                        'icon'     => 'fa fa-users',
                        'name'     => "SA Applicant",
                        'href'     => admin_url('clients/study_abroad'),
                        'position' => 4,
                ]);
                if (has_permission('external_visa', '', 'view') || has_permission('external_visa', '', 'view_own')) {
                        $CI->app_menu->add_sidebar_children_item('customers', [
                                'slug'     => 'visa_details',
                                'icon'     => 'fa fa-cc-visa',
                                'name'     => "Ext Visa Data",
                                'href'     => admin_url('clients/visa_details'),
                                'position' => 5,
                        ]);
                }
                if (has_permission('external_ticket', '', 'view') || has_permission('external_ticket', '', 'view_own')) {

                        $CI->app_menu->add_sidebar_children_item('customers', [
                                'slug'     => 'ticket_details',
                                'icon'     => 'fa fa-ticket',
                                'name'     => "Ext Ticket Data",
                                'href'     => admin_url('clients/ticket_details'),
                                'position' => 5,
                        ]);
                }
        }


        if (has_permission('hostel_management', '', 'view') || has_permission('hostel_management', '', 'view_own')) {

                $CI->app_menu->add_sidebar_menu_item('hostel_management', [
                        'collapse' => true,
                        'icon'     => 'fa fa-hotel',
                        'name'     => "Hostel MS",
                        'href'     => admin_url('hostel_management'),
                        'position' => 5,
                ]);

                $CI->app_menu->add_sidebar_children_item('hostel_management', [
                        'slug'     => 'hostel_management/hostel_georgia',
                        'icon'     => 'fa fa-hotel',
                        'name'     => "Georgia Hostel",
                        'href'     => admin_url('hostel_management/manage/georgia'),
                        'position' => 5,
                ]);

                $CI->app_menu->add_sidebar_children_item('hostel_management', [
                        'slug'     => 'hostel_management/hostel_georgia',
                        'icon'     => 'fa fa-hotel',
                        'name'     => "Russia Hostel",
                        'href'     => admin_url('hostel_management/manage/russia'),
                        'position' => 5,
                ]);
        }

        if (has_permission('quotation', '', 'view') || has_permission('quotation', '', 'view_own')) {
                $CI->app_menu->add_sidebar_menu_item('quotation', [
                        'collapse' => true,
                        'name'     => "Quotations",
                        'position' => 6,
                        'icon'     => 'fa fa-user-o',
                ]);

                $CI->app_menu->add_sidebar_children_item('quotation', [
                        'slug'     => 'quotations/universities',
                        'icon'     => 'fa fa-user-o',
                        'name'     => "Universities",
                        'href'     => admin_url('quotations/mbbs_abroad'),
                        'position' => 1,
                ]);
        }


        if (has_permission('fly_batch', '', 'view_own') || has_permission('fly_batch', '', 'view') || has_permission('exam_batch', '', 'view_own') || has_permission('exam_batch', '', 'view')) {
                $CI->app_menu->add_sidebar_menu_item('batch_create', [
                        'collapse' => true,
                        'name'     => "Batch Create",
                        'position' => 7,
                        'icon'     => 'fa fa-database',
                ]);
        }



        // Fly Batch
        if (has_permission('fly_batch', '', 'view_own') || has_permission('fly_batch', '', 'view')) {
                $CI->app_menu->add_sidebar_children_item('batch_create', [
                        'href'     => admin_url('fly_batch'),
                        'slug'     => 'fly_batch',
                        'name'     => "Fly Batch",
                        'icon'     => 'fa fa-ticket',
                        'position' => 200,
                ]);
        }
        // Exam Batch
        if (has_permission('exam_batch', '', 'view_own') || has_permission('exam_batch', '', 'view')) {
                $CI->app_menu->add_sidebar_children_item('batch_create', [
                        'href'     => admin_url('exam_batch'),
                        'slug'     => 'exam_batch',
                        'name'     => "Exam Batch",
                        'icon'     => 'fa fa-book',
                        'position' => 199,
                ]);
        }




        if (has_permission('fly_batch', '', 'departure_create')) {
                $CI->app_menu->add_sidebar_children_item('batch_create', [
                        'href'     => admin_url('fly_batch/departure'),
                        'slug'     => 'fly_departure',
                        'name'     => "Fly Departure",
                        'icon'     => 'fa fa-book',
                        'position' => 200,
                ]);
        }




        // 'href'     => admin_url('clients'),




        // $CI->app_menu->add_sidebar_menu_item('sales', [
        //         'collapse' => true,
        //         'name'     => _l('als_sales'),
        //         'position' => 10,
        //         'icon'     => 'fa fa-balance-scale',
        // ]);

        if ((has_permission('proposals', '', 'view') || has_permission('proposals', '', 'view_own'))
                || (staff_has_assigned_proposals() && get_option('allow_staff_view_proposals_assigned') == 1)
        ) {
                $CI->app_menu->add_sidebar_children_item('sales', [
                        'slug'     => 'proposals',
                        'name'     => _l('proposals'),
                        'href'     => admin_url('proposals'),
                        'position' => 5,
                ]);
        }

        if ((has_permission('estimates', '', 'view') || has_permission('estimates', '', 'view_own'))
                || (staff_has_assigned_estimates() && get_option('allow_staff_view_estimates_assigned') == 1)
        ) {
                $CI->app_menu->add_sidebar_children_item('sales', [
                        'slug'     => 'estimates',
                        'name'     => _l('estimates'),
                        'href'     => admin_url('estimates'),
                        'position' => 10,
                ]);
        }

        if ((has_permission('invoices', '', 'view') || has_permission('invoices', '', 'view_own'))
                || (staff_has_assigned_invoices() && get_option('allow_staff_view_invoices_assigned') == 1)
        ) {
                $CI->app_menu->add_sidebar_children_item('sales', [
                        'slug'     => 'invoices',
                        'name'     => _l('invoices'),
                        'href'     => admin_url('invoices'),
                        'position' => 15,
                ]);
        }

        if (
                has_permission('payments', '', 'view') || has_permission('invoices', '', 'view_own')
                || (get_option('allow_staff_view_invoices_assigned') == 1 && staff_has_assigned_invoices())
        ) {
                $CI->app_menu->add_sidebar_children_item('sales', [
                        'slug'     => 'payments',
                        'name'     => _l('payments'),
                        'href'     => admin_url('payments'),
                        'position' => 20,
                ]);
        }

        if (has_permission('credit_notes', '', 'view') || has_permission('credit_notes', '', 'view_own')) {
                $CI->app_menu->add_sidebar_children_item('sales', [
                        'slug'     => 'credit_notes',
                        'name'     => _l('credit_notes'),
                        'href'     => admin_url('credit_notes'),
                        'position' => 25,
                ]);
        }

        if (has_permission('items', '', 'view')) {
                $CI->app_menu->add_sidebar_children_item('sales', [
                        'slug'     => 'items',
                        'name'     => _l('items'),
                        'href'     => admin_url('invoice_items'),
                        'position' => 30,
                ]);
        }

        // if (has_permission('subscriptions', '', 'view') || has_permission('subscriptions', '', 'view_own')) {
        //         $CI->app_menu->add_sidebar_menu_item('subscriptions', [
        //                 'name'     => _l('subscriptions'),
        //                 'href'     => admin_url('subscriptions'),
        //                 'icon'     => 'fa fa-repeat',
        //                 'position' => 15,
        //         ]);
        // }



        // if (has_permission('expenses', '', 'view') || has_permission('expenses', '', 'view_own')) {
        //         $CI->app_menu->add_sidebar_menu_item('expenses', [
        //                 'name'     => _l('expenses'),
        //                 'href'     => admin_url('expenses'),
        //                 'icon'     => 'fa fa-file-text-o',
        //                 'position' => 20,
        //         ]);
        // }

        // if (has_permission('contracts', '', 'view') || has_permission('contracts', '', 'view_own')) {
        //         $CI->app_menu->add_sidebar_menu_item('contracts', [
        //                 'name'     => _l('contracts'),
        //                 'href'     => admin_url('contracts'),
        //                 'icon'     => 'fa fa-file',
        //                 'position' => 25,
        //         ]);
        // }

        // $CI->app_menu->add_sidebar_menu_item('projects', [
        //         'name'     => _l('projects'),
        //         'href'     => admin_url('projects'),
        //         'icon'     => 'fa fa-bars',
        //         'position' => 30,
        // ]);





        // $CI->app_menu->add_sidebar_menu_item('tasks', [
        //         'name'     => _l('als_tasks'),
        //         'href'     => admin_url('tasks'),
        //         'icon'     => 'fa fa-tasks',
        //         'position' => 35,
        // ]);



        // if ((!is_staff_member() && get_option('access_tickets_to_none_staff_members') == 1) || is_staff_member()) {
        //         $CI->app_menu->add_sidebar_menu_item('support', [
        //                 'name'     => _l('support'),
        //                 'href'     => admin_url('tickets'),
        //                 'icon'     => 'fa fa-ticket',
        //                 'position' => 40,
        //         ]);
        // }

        if (is_staff_member()) {
                $CI->app_menu->add_sidebar_menu_item('leads', [
                        'name'     => _l('als_leads'),
                        'href'     => admin_url('leads'),
                        'icon'     => 'fa fa-tty',
                        'position' => 2,
                ]);
        }
        if (is_staff_member()) {
                $CI->app_menu->add_sidebar_menu_item('visitor_leads', [
                        'name'     => _l('Visit Logs'),
                        'href'     => admin_url('leads/lead_visitor_request'),
                        'icon'     => 'fa fa-tty',
                        'position' => 2,
                ]);
        }

        if (has_permission('knowledge_base', '', 'view') || has_permission('knowledge_base', '', 'view_own') || staff_has_assigned_knowledge_base()) {
                $CI->app_menu->add_sidebar_menu_item('knowledge-base', [
                        'name'     => _l('als_kb'),
                        'href'     => admin_url('knowledge_base'),
                        'icon'     => 'fa fa-folder-open-o',
                        'position' => 50,
                ]);
        }

        // Utilities
        $CI->app_menu->add_sidebar_menu_item('utilities', [
                'collapse' => true,
                'name'     => _l('als_utilities'),
                'position' => 55,
                'icon'     => 'fa fa-cogs',
        ]);

        $CI->app_menu->add_sidebar_children_item('utilities', [
                'slug'     => 'media',
                'name'     => _l('als_media'),
                'href'     => admin_url('utilities/media'),
                'position' => 5,
        ]);

        if (has_permission('bulk_pdf_exporter', '', 'view')) {
                $CI->app_menu->add_sidebar_children_item('utilities', [
                        'slug'     => 'bulk-pdf-exporter',
                        'name'     => _l('bulk_pdf_exporter'),
                        'href'     => admin_url('utilities/bulk_pdf_exporter'),
                        'position' => 10,
                ]);
        }

        $CI->app_menu->add_sidebar_children_item('utilities', [
                'slug'     => 'calendar',
                'name'     => _l('als_calendar_submenu'),
                'href'     => admin_url('utilities/calendar'),
                'position' => 15,
        ]);


        if (is_admin()) {
                $CI->app_menu->add_sidebar_children_item('utilities', [
                        'slug'     => 'announcements',
                        'name'     => _l('als_announcements_submenu'),
                        'href'     => admin_url('announcements'),
                        'position' => 20,
                ]);

                $CI->app_menu->add_sidebar_children_item('utilities', [
                        'slug'     => 'activity-log',
                        'name'     => _l('als_activity_log_submenu'),
                        'href'     => admin_url('utilities/activity_log'),
                        'position' => 25,
                ]);

                $CI->app_menu->add_sidebar_children_item('utilities', [
                        'slug'     => 'ticket-pipe-log',
                        'name'     => _l('ticket_pipe_log'),
                        'href'     => admin_url('utilities/pipe_log'),
                        'position' => 30,
                ]);
        }

        if (has_permission('reports', '', 'view')) {
                $CI->app_menu->add_sidebar_menu_item('reports', [
                        'collapse' => true,
                        'name'     => _l('als_reports'),
                        'href'     => admin_url('reports'),
                        'icon'     => 'fa fa-area-chart',
                        'position' => 60,
                ]);
                $CI->app_menu->add_sidebar_children_item('reports', [
                        'slug'     => 'sales-reports',
                        'name'     => _l('als_reports_sales_submenu'),
                        'href'     => admin_url('reports/sales'),
                        'position' => 5,
                ]);
                $CI->app_menu->add_sidebar_children_item('reports', [
                        'slug'     => 'expenses-reports',
                        'name'     => _l('als_reports_expenses'),
                        'href'     => admin_url('reports/expenses'),
                        'position' => 10,
                ]);
                $CI->app_menu->add_sidebar_children_item('reports', [
                        'slug'     => 'expenses-vs-income-reports',
                        'name'     => _l('als_expenses_vs_income'),
                        'href'     => admin_url('reports/expenses_vs_income'),
                        'position' => 15,
                ]);
                $CI->app_menu->add_sidebar_children_item('reports', [
                        'slug'     => 'leads-reports',
                        'name'     => _l('als_reports_leads_submenu'),
                        'href'     => admin_url('reports/leads'),
                        'position' => 20,
                ]);

                if (is_admin()) {
                        $CI->app_menu->add_sidebar_children_item('reports', [
                                'slug'     => 'leads-connect',
                                'name'     => "Leads connect",
                                'href'     => admin_url('reports/leads_connect'),
                                'position' => 20,
                        ]);
                }

                $CI->app_menu->add_sidebar_children_item('reports', [
                        'slug'     => 'leads-performance-reports',
                        'name'     => 'Leads Performance',
                        'href'     => admin_url('reports/performance_leads'),
                        'position' => 21,
                ]);


                if (is_admin()) {
                        $CI->app_menu->add_sidebar_children_item('reports', [
                                'slug'     => 'timesheets-reports',
                                'name'     => _l('timesheets_overview'),
                                'href'     => admin_url('staff/timesheets?view=all'),
                                'position' => 25,
                        ]);
                }

                $CI->app_menu->add_sidebar_children_item('reports', [
                        'slug'     => 'knowledge-base-reports',
                        'name'     => _l('als_kb_articles_submenu'),
                        'href'     => admin_url('reports/knowledge_base_articles'),
                        'position' => 30,
                ]);
        }




        if (has_permission('partners', '', 'view')) {
                // if (has_permission('academic', '', 'view')) {
                $CI->app_menu->add_sidebar_menu_item('academic', [
                        'collapse' => true,
                        'icon'     => 'fa fa-user-o',
                        'name'     => "Back-end Data",
                        'position' => 25,
                ]);
                $CI->app_menu->add_sidebar_children_item('academic', [
                        'slug'     => 'universities',
                        'icon'     => 'fa fa-university',
                        'name'     => "Universities",
                        'href'     => admin_url('academic/university'),
                        'position' => 5,
                ]);
                $CI->app_menu->add_sidebar_children_item('academic', [
                        'slug'     => 'courses',
                        'icon'     => 'fa fa-graduation-cap',
                        'name'     => "Courses",
                        'href'     => admin_url('academic/courses'),
                        'position' => 5,
                ]);
        }

        if (has_permission('partners', '', 'view')) {

                $CI->app_menu->add_sidebar_children_item('academic', [
                        'slug'     => 'university',
                        'icon'     => 'fa fa-university',
                        'name'     => "University Partner",
                        'href'     => admin_url('partner/university'),
                        'position' => 5,
                ]);
                $CI->app_menu->add_sidebar_children_item('academic', [
                        'slug'     => 'ev_partner',
                        'icon'     => 'fa fa-handshake-o',
                        'name'     => "EV Partner",
                        'href'     => admin_url('partner/ev_partner'),
                        'position' => 5,
                ]);
        }

        if (has_permission('school_board', '', 'view') || has_permission('school_board', '', 'view_own')) {
                $CI->app_menu->add_sidebar_children_item('academic', [
                        'slug'     => 'school_board',
                        'name'     => "School Board",
                        'href'     => admin_url('school_board'),
                        'icon'     => 'fa fa-home',
                        'position' => 15,
                ]);
        }
        // }

        // Setup menu
        if (has_permission('staff', '', 'view')) {
                $CI->app_menu->add_setup_menu_item('staff', [
                        'name'     => _l('als_staff'),
                        'href'     => admin_url('staff'),
                        'position' => 5,
                ]);
        }


        if (has_permission('hostel_management', '', 'backend')) {

                if (has_permission('hostel_management', '', 'view') || has_permission('hostel_management', '', 'view_own')) {
                        $CI->app_menu->add_setup_menu_item('hms_backend', [
                                'name'     => "HMS Backend",
                                'collapse' => true,
                                'position' => 5,
                        ]);
                }

                $CI->app_menu->add_setup_children_item('hms_backend', [
                        'slug'     => 'hrms-hostel',
                        'name'     => "Hostel",
                        'href'     => admin_url('hostel_management/hostel_management'),
                        'position' => 1,
                ]);
                $CI->app_menu->add_setup_children_item('hms_backend', [
                        'slug'     => 'hrms-rental-georgia',
                        'name'     => "Rental Georgia",
                        'href'     => admin_url('hostel_management/rental/georgia'),
                        'position' => 2,
                ]);
                $CI->app_menu->add_setup_children_item('hms_backend', [
                        'slug'     => 'hrms-rental-russia',
                        'name'     => "Rental Russia",
                        'href'     => admin_url('hostel_management/rental/russia'),
                        'position' => 3,
                ]);
        }




        if (is_admin()) {
                $CI->app_menu->add_setup_menu_item('customers', [
                        'collapse' => true,
                        'name'     => _l('clients'),
                        'position' => 10,
                ]);

                $CI->app_menu->add_setup_children_item('customers', [
                        'slug'     => 'customer-groups',
                        'name'     => _l('customer_groups'),
                        'href'     => admin_url('clients/groups'),
                        'position' => 5,
                ]);
                $CI->app_menu->add_setup_menu_item('support', [
                        'collapse' => true,
                        'name'     => _l('support'),
                        'position' => 15,
                ]);

                $CI->app_menu->add_setup_children_item('support', [
                        'slug'     => 'departments',
                        'name'     => _l('acs_departments'),
                        'href'     => admin_url('departments'),
                        'position' => 5,
                ]);
                $CI->app_menu->add_setup_children_item('support', [
                        'slug'     => 'tickets-predefined-replies',
                        'name'     => _l('acs_ticket_predefined_replies_submenu'),
                        'href'     => admin_url('tickets/predefined_replies'),
                        'position' => 10,
                ]);
                $CI->app_menu->add_setup_children_item('support', [
                        'slug'     => 'tickets-priorities',
                        'name'     => _l('acs_ticket_priority_submenu'),
                        'href'     => admin_url('tickets/priorities'),
                        'position' => 15,
                ]);
                $CI->app_menu->add_setup_children_item('support', [
                        'slug'     => 'tickets-statuses',
                        'name'     => _l('acs_ticket_statuses_submenu'),
                        'href'     => admin_url('tickets/statuses'),
                        'position' => 20,
                ]);

                $CI->app_menu->add_setup_children_item('support', [
                        'slug'     => 'tickets-services',
                        'name'     => _l('acs_ticket_services_submenu'),
                        'href'     => admin_url('tickets/services'),
                        'position' => 25,
                ]);
                $CI->app_menu->add_setup_children_item('support', [
                        'slug'     => 'tickets-spam-filters',
                        'name'     => _l('spam_filters'),
                        'href'     => admin_url('spam_filters/view/tickets'),
                        'position' => 30,
                ]);

                $CI->app_menu->add_setup_menu_item('leads', [
                        'collapse' => true,
                        'name'     => _l('acs_leads'),
                        'position' => 20,
                ]);
                $CI->app_menu->add_setup_children_item('leads', [
                        'slug'     => 'leads-sources',
                        'name'     => _l('acs_leads_sources_submenu'),
                        'href'     => admin_url('leads/sources'),
                        'position' => 5,
                ]);
                $CI->app_menu->add_setup_children_item('leads', [
                        'slug'     => 'leads-statuses',
                        'name'     => _l('acs_leads_statuses_submenu'),
                        'href'     => admin_url('leads/statuses'),
                        'position' => 10,
                ]);
                $CI->app_menu->add_setup_children_item('leads', [
                        'slug'     => 'leads-email-integration',
                        'name'     => _l('leads_email_integration'),
                        'href'     => admin_url('leads/email_integration'),
                        'position' => 15,
                ]);
                $CI->app_menu->add_setup_children_item('leads', [
                        'slug'     => 'leads-facebook-ads-name',
                        'name'     => _l('leads_fb_ads_name'),
                        'href'     => admin_url('leads/leads_fb_ads_name'),
                        'position' => 15,
                ]);
                $CI->app_menu->add_setup_children_item('leads', [
                        'slug'     => 'web-to-lead',
                        'name'     => _l('web_to_lead'),
                        'href'     => admin_url('leads/forms'),
                        'position' => 20,
                ]);




                $CI->app_menu->add_setup_menu_item('finance', [
                        'collapse' => true,
                        'name'     => _l('acs_finance'),
                        'position' => 25,
                ]);
                $CI->app_menu->add_setup_children_item('finance', [
                        'slug'     => 'taxes',
                        'name'     => _l('acs_sales_taxes_submenu'),
                        'href'     => admin_url('taxes'),
                        'position' => 5,
                ]);
                $CI->app_menu->add_setup_children_item('finance', [
                        'slug'     => 'currencies',
                        'name'     => _l('acs_sales_currencies_submenu'),
                        'href'     => admin_url('currencies'),
                        'position' => 10,
                ]);
                $CI->app_menu->add_setup_children_item('finance', [
                        'slug'     => 'payment-modes',
                        'name'     => _l('acs_sales_payment_modes_submenu'),
                        'href'     => admin_url('paymentmodes'),
                        'position' => 15,
                ]);
                $CI->app_menu->add_setup_children_item('finance', [
                        'slug'     => 'expenses-categories',
                        'name'     => _l('acs_expense_categories'),
                        'href'     => admin_url('expenses/categories'),
                        'position' => 20,
                ]);

                $CI->app_menu->add_setup_menu_item('contracts', [
                        'collapse' => true,
                        'name'     => _l('acs_contracts'),
                        'position' => 30,
                ]);
                $CI->app_menu->add_setup_children_item('contracts', [
                        'slug'     => 'contracts-types',
                        'name'     => _l('acs_contract_types'),
                        'href'     => admin_url('contracts/types'),
                        'position' => 5,
                ]);

                // $CI->app_menu->add_setup_menu_item('vendors_module', [
                //         'collapse' => true,
                //         'name'     => _l('Vendor'),
                //         'position' => 0,
                // ]);
                // $CI->app_menu->add_setup_children_item('vendors_module', [
                //         'slug'     => 'visa-vendors',
                //         'name'     => _l('visa_vendors'),
                //         'href'     => admin_url('vendors/visa'),
                //         'position' => 5,
                // ]);
                // $CI->app_menu->add_setup_children_item('vendors_module', [
                //         'slug'     => 'accommodation-vendors',
                //         'name'     => _l('accommodation_vendors'),
                //         'href'     => admin_url('vendors/accommodation'),
                //         'position' => 10,
                // ]);
                // $CI->app_menu->add_setup_children_item('vendors_module', [
                //         'slug'     => 'applicant-vendors',
                //         'name'     => _l('applicant_vendors'),
                //         'href'     => admin_url('vendors/applicant'),
                //         'position' => 15,
                // ]);


                $CI->app_menu->add_setup_menu_item('vendor', [
                        'collapse' => true,
                        'name'     => _l('vendors'),
                        'position' => 25,
                ]);

                $CI->app_menu->add_setup_children_item('vendor', [
                        'slug'     => 'ma_vendors',
                        'name'     => _l('MA Vendors'),
                        'href'     => admin_url('vendor/ma_vendor'),
                        'position' => 5,
                ]);


                //   $CI->app_menu->add_setup_children_item('vendor', [
                //         'slug'     => 'apostile',
                //         'name'     => _l('apostile_vendors'),
                //         'href'     => admin_url('vendor/apostile'),
                //         'position' => 5,
                // ]);
                //   $CI->app_menu->add_setup_children_item('vendor', [
                //         'slug'     => 'apostile',
                //         'name'     => _l('apostile_vendors'),
                //         'href'     => admin_url('vendor/apostile'),
                //         'position' => 5,
                // ]);

                // $CI->app_menu->add_setup_children_item('vendor', [
                //         'slug'     => 'visa',
                //         'name'     => _l('visa_vendors'),
                //         'href'     => admin_url('vendor/visa'),
                //         'position' => 5,
                // ]);
                // $CI->app_menu->add_setup_children_item('vendor', [
                //         'slug'     => 'accommodation',
                //         'name'     => _l('accommodation_vendors'),
                //         'href'     => admin_url('vendor/accommodation'),
                //         'position' => 10,
                // ]);
                // $CI->app_menu->add_setup_children_item('vendor', [
                //         'slug'     => 'applicant',
                //         'name'     => _l('applicant_vendors'),
                //         'href'     => admin_url('vendor/applicant'),
                //         'position' => 15,
                // ]);


                $modules_name = _l('modules');

                if ($modulesNeedsUpgrade = $CI->app_modules->number_of_modules_that_require_database_upgrade()) {
                        $modules_name .= '<span class="badge menu-badge bg-warning">' . $modulesNeedsUpgrade . '</span>';
                }
                /*
        $CI->app_menu->add_setup_menu_item('modules', [
                    'href'     => admin_url('modules'),
                    'name'     => $modules_name,
                    'position' => 35,
            ]);
        */

                $CI->app_menu->add_setup_menu_item('custom-fields', [
                        'href'     => admin_url('custom_fields'),
                        'name'     => _l('asc_custom_fields'),
                        'position' => 45,
                ]);

                $CI->app_menu->add_setup_menu_item('gdpr', [
                        'href'     => admin_url('gdpr'),
                        'name'     => _l('gdpr_short'),
                        'position' => 50,
                ]);

                $CI->app_menu->add_setup_menu_item('roles', [
                        'href'     => admin_url('roles'),
                        'name'     => _l('acs_roles'),
                        'position' => 55,
                ]);

                $CI->app_menu->add_setup_menu_item('app-config', [
                        'href'     => admin_url('app_config'),
                        'name'     => _l('app_config   '),
                        'position' => 45,
                ]);

                /*             $CI->app_menu->add_setup_menu_item('api', [
                          'href'     => admin_url('api'),
                          'name'     => 'API',
                          'position' => 65,
                  ]);*/

                $CI->app_menu->add_setup_menu_item('excel', [
                        'name'     => "Excel",
                        'href'     => admin_url('excel'),
                        'position' => 15,
                ]);
        }


        if (has_permission('settings', '', 'view')) {
                $CI->app_menu->add_setup_menu_item('settings', [
                        'href'     => admin_url('settings'),
                        'name'     => _l('acs_settings'),
                        'position' => 200,
                ]);
        }

        if (has_permission('email_templates', '', 'view')) {
                $CI->app_menu->add_setup_menu_item('email-templates', [
                        'href'     => admin_url('emails'),
                        'name'     => _l('acs_email_templates'),
                        'position' => 40,
                ]);
        }
}
