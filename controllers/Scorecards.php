<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Scorecards extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('company_recapitulation_model');
        $this->load->model('clients_recapitulation_model');
        $this->load->model('tasks_duration_model');
        $this->load->model('tasks_model');
        $this->load->model('projects_model');
        $this->load->model('staff_model');
    }

    /* Get all scorecards in case user go on index page */
    public function task_duration($id = '')
    {
        if (!has_permission('scorecards', '', 'view')) {
            access_denied('scorecards');
        }
        $data = [];

        $data['members']  = $this->staff_model->get();
        $data['years']    = $this->tasks_model->get_distinct_tasks_years(($this->input->post('month_from') ? $this->input->post('month_from') : 'startdate'));
        
        $has_permission_create = has_permission('tasks', '', 'create');
        $has_permission_view   = has_permission('tasks', '', 'view');
        
        if (!$has_permission_view) {
            $staff_id = get_staff_user_id();
        } elseif ($this->input->post('member')) {
            $staff_id = $this->input->post('member');
        } else {
            $staff_id = '';
        }

        $data['staff_id'] = $staff_id;
        $task_duration_filter['staff_id'] = $staff_id;

        if(!is_null($this->input->post('member'))){
            $data['staff_id'] = $task_duration_filter['member'] = $this->input->post('member');
        }

        $data['month'] = $task_duration_filter['month'] = date('m');

        if(!is_null($this->input->post('month'))){
            $data['month'] = $task_duration_filter['month'] = $this->input->post('month');
        }

        $data['year'] = $task_duration_filter['year'] = date('Y');

        if(!is_null($this->input->post('year'))){
            $data['year'] = $task_duration_filter['year'] = $this->input->post('year');
        }

        $this->session->set_userdata('task_duration_filter', $task_duration_filter);

        if(is_numeric($id)){
            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data(module_views_path('scorecards', 'admin/tables/small_table'));
            }

            $task_duration = $this->tasks_duration_model->get($id);
            //if(empty($task_duration)) goto end;

            $data['task_duration'] = $task_duration;
            $data['task_duration_id']            = $id;
            $data['title']                 = _l('task_duration_preview');

            $this->load->view('admin/tasks_duration/task_duration_preview', $data);
            
        }
        else{
            //end:
            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data(module_views_path('scorecards', 'admin/tables/table'));
            }
            
            $data['title']                 = _l('scorecards_tracking');
                
            $this->load->view('admin/tasks_duration/manage', $data);
            //$this->load->view('admin/scorecards/draft', $data);
        }
    }


    /* Get all scorecards in case user go on index page */
    public function task_history($task_id = '')
    {
        if (!has_permission('scorecards', '', 'view')) {
            access_denied('scorecards');
        }
        $data = [];

        $data['members']  = $this->staff_model->get();
        $data['years']    = $this->tasks_model->get_distinct_tasks_years(($this->input->post('month_from') ? $this->input->post('month_from') : 'startdate'));
        
        $has_permission_create = has_permission('tasks', '', 'create');
        $has_permission_view   = has_permission('tasks', '', 'view');
        
        if (!$has_permission_view) {
            $staff_id = get_staff_user_id();
        } elseif ($this->input->post('member')) {
            $staff_id = $this->input->post('member');
        } else {
            $staff_id = '';
        }

        $task_history_filter = $this->session->userdata('task_history_filter');

        if(isset($task_history_filter['member'])){
            $staff_id = $task_history_filter['member'];
        }
        
        $data['staff_id'] = $staff_id;

        if(!is_null($this->input->post('member')) && ($staff_id != $this->input->post('member'))){
            $data['staff_id'] = $this->input->post('member');
            $task_history_filter['member'] = $this->input->post('member');
        }

        $data['month'] = isset($task_history_filter['month']) ? $task_history_filter['month'] : date('m');
        if(!is_null($this->input->post('month')) && ($data['month'] != $this->input->post('month'))){
            $data['month'] = $this->input->post('month');
            $task_history_filter['month'] = $this->input->post('month');
        }

        $this->session->set_userdata('task_history_filter', $task_history_filter);

        if(is_numeric($task_id)){
            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data(module_views_path('scorecards', 'admin/tables/task_history__small_table'));
            }

            $task_history = $this->tasks_history_model->get_history_by_task_id($task_id);
            $task = $this->tasks_model->get($task_id);
            //if(empty($task_history)) goto end;

            $data['task'] = $task;
            $data['task_history'] = $task_history;
            $data['task_history_task_id']            = $task_id;
            $data['title']                 = _l('task_history_preview');

            $this->load->view('admin/tasks_history/task_history_preview', $data);
            
        }
        else{
            //end:
            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data(module_views_path('scorecards', 'admin/tables/task_history__table'));
            }
            
            $data['title']                 = _l('tasks_history_tracking');
                
            $this->load->view('admin/tasks_history/manage', $data);
            //$this->load->view('admin/scorecards/draft', $data);
        }
    }

    /* Get all scorecards in case user go on index page */
    public function task_import($id = '')
    {
        if (!has_permission('scorecards', '', 'view')) {
            access_denied('scorecards');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('scorecards', 'admin/tables/table_import'));
        }

        if ($this->input->post('scorecards_action')) {
            $action = $this->input->post('scorecards_action');
            /*
             * TODO some actions
             */
            $data = $this->tasks_duration_model->get_importable_tasks();
            if(!empty($data)){
                if($this->db->insert_batch(db_prefix() . 'scorecards_tasks_duration',$data)){
                    log_activity('Import scorecard data has successfully');
                }
                else{
                    log_activity('Import scorecard data has failed');
                }
            }
        }

        $data = [];
        $data['tasks'] = $this->tasks_duration_model->get_importable_tasks();
        $data['inspectionid']            = $id;
        $data['title']                 = _l('scorecards_tracking');
        $this->load->view('admin/scorecards/table_import', $data);
        //$this->load->view('admin/scorecards/draft', $data);
    }

    /* Get all scorecards in case user go on index page */
    public function task_recapitulation($id = '')
    {
        if (!has_permission('scorecards', '', 'view')) {
            access_denied('scorecards');
        }
        
        //$data['tasks'] = $this->tasks_duration_model->get_tasks_duration_by_staff();
        $data['title']                 = _l('scorecards_task_recapitulation');
        //$this->load->view('widgets/tasks_duration_by_staff', $data);
        $this->load->view('admin/scorecards/task_recapitulation', $data);
    }

    /* Get all scorecards in case user go on index page */
    public function company_recapitulation($id = '')
    {
        if (!has_permission('scorecards', '', 'view')) {
            access_denied('scorecards');
        }
        $data['years']    = $this->tasks_model->get_distinct_tasks_years(($this->input->post('month_from') ? $this->input->post('month_from') : 'startdate'));

        $task_history_filter = $this->session->userdata('task_history_filter');

        if(isset($task_history_filter['member'])){
            $staff_id = $task_history_filter['member'];
        }

        $data['month'] = isset($task_history_filter['month']) ? $task_history_filter['month'] : date('m');
        if(!is_null($this->input->post('month')) && ($data['month'] != $this->input->post('month'))){
            $data['month'] = $this->input->post('month');
            $task_history_filter['month'] = $this->input->post('month');
        }

        $this->session->set_userdata('task_history_filter', $task_history_filter);

        $data['atd'] = $this->company_recapitulation_model->get_average_tasks_duration();
        $data['mtd'] = $this->company_recapitulation_model->get_maximum_tasks_duration();
        $data['ctd'] = $this->company_recapitulation_model->get_count_tasks_by_duration();


                
        $data['title']                 = _l('scorecards_company_recapitulation');
        $this->load->view('admin/scorecards/company_recapitulation', $data);
    }
    
    /* Get all scorecards in case user go on index page */
    public function clients_recapitulation($id = '')
    {
        if (!has_permission('scorecards', '', 'view')) {
            access_denied('scorecards');
        }
        $data['years']    = $this->tasks_model->get_distinct_tasks_years(($this->input->post('month_from') ? $this->input->post('month_from') : 'startdate'));

        $task_history_filter = $this->session->userdata('task_history_filter');

        if(isset($task_history_filter['member'])){
            $staff_id = $task_history_filter['member'];
        }

        $data['month'] = isset($task_history_filter['month']) ? $task_history_filter['month'] : date('m');
        if(!is_null($this->input->post('month')) && ($data['month'] != $this->input->post('month'))){
            $data['month'] = $this->input->post('month');
            $task_history_filter['month'] = $this->input->post('month');
        }

        $this->session->set_userdata('task_history_filter', $task_history_filter);

        $data['scorecards'] = $this->clients_recapitulation_model->get_client_progress();
                
        $data['title']                 = _l('scorecards_clients_recapitulation');
        $this->load->view('admin/scorecards/clients_recapitulation', $data);
    }

    /* Get all scorecards in case user go on index page */
    public function client_recapitulation_today($id = '')
    {
        if (!has_permission('scorecards', '', 'view')) {
            access_denied('scorecards');
        }
        
        $client_recapitulation_today['recapitulation_date'] = date('Y-m-d');
        
        if ($this->input->post()) {
            $input_post = $this->input->post();
            $client_recapitulation_today = $this->input->post();
        }

        if(isset($client_recapitulation_today['member'])){
            $staff_id = $client_recapitulation_today['member'];
        }

        $this->session->set_userdata('client_recapitulation_today', $client_recapitulation_today);
        $data['client_recapitulation_today'] = $client_recapitulation_today;
        $data['scorecards'] = $this->clients_recapitulation_model->get_client_recapitulation_today($client_recapitulation_today['recapitulation_date']);
        $data['staffs'] = $this->clients_recapitulation_model->get_staff_grouped_today($client_recapitulation_today['recapitulation_date']);
        
        $data['title']                 = _l('scorecards_today');
        $this->load->view('admin/scorecards/clients_recapitulation_today', $data);
    }


    /* Get all scorecards in case user go on index page */
    public function client_recapitulation_this_week($id = '')
    {
        if (!has_permission('scorecards', '', 'view')) {
            access_denied('scorecards');
        }
        $data['years']    = $this->tasks_model->get_distinct_tasks_years(($this->input->post('month_from') ? $this->input->post('month_from') : 'startdate'));

        $task_history_filter = $this->session->userdata('task_history_filter');

        if(isset($task_history_filter['member'])){
            $staff_id = $task_history_filter['member'];
        }

        $data['month'] = isset($task_history_filter['month']) ? $task_history_filter['month'] : date('m');
        if(!is_null($this->input->post('month')) && ($data['month'] != $this->input->post('month'))){
            $data['month'] = $this->input->post('month');
            $task_history_filter['month'] = $this->input->post('month');
        }

        $this->session->set_userdata('task_history_filter', $task_history_filter);

        $data['scorecards'] = $this->clients_recapitulation_model->get_client_recapitulation_this_week();
        
        $data['title']                 = _l('scorecards_week') . ' - '. date("W", time()); 
        $this->load->view('admin/scorecards/clients_recapitulation_this_week', $data);
    }

    /* Get all scorecards in case user go on index page */
    public function _clients_recapitulation($id = '')
    {
        if (!has_permission('scorecards', '', 'view')) {
            access_denied('scorecards');
        }
        $data['atd']   = $this->clients_recapitulation_model->get_average_tasks_duration();
        //$data['atd']   = $this->clients_recapitulation_model->get_average_tasks_duration();

        $data['mtd']   = $this->clients_recapitulation_model->get_maximum_tasks_duration();
        $data['title']                 = _l('scorecards_clients_recapitulation');
        //$this->load->view('widgets/tasks_duration_by_staff', $data);
        $this->load->view('admin/scorecards/clients_recapitulation', $data);
    }


    /* Generates scorecard PDF and senting to email  */
    public function pdf( )
    {

        $this_week_number = date("W", time());

        $scorecard        = $this->clients_recapitulation_model->get_client_recapitulation_this_week();
        $staffs        = $this->clients_recapitulation_model->get_staff_grouped_this_week();
        
        /*
        $scorecard->assigned_path = FCPATH . get_scorecard_upload_path('scorecard').$scorecard->id.'/assigned-'.$scorecard_number.'.png';
        $scorecard->acceptance_path = FCPATH . get_scorecard_upload_path('scorecard').$scorecard->id .'/'.$scorecard->signature;
        $scorecard->client_company = $this->clients_model->get($scorecard->clientid)->company;
        $scorecard->acceptance_date_string = _dt($scorecard->acceptance_date);
        */

        try {
            $pdf = scorecard_this_week_pdf($scorecard, $staffs);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $file_name = slug_it('scorecard-week-' . $this_week_number);
        $fileName = mb_strtolower($file_name) . '.pdf';

        $pdf->Output($fileName, $type);

    }


    /* Generates scorecard PDF and senting to email  */
    public function client_today( )
    {

        $client_recapitulation_today = $this->session->userdata('client_recapitulation_today');

        log_activity('client_today '. json_encode($client_recapitulation_today));

        $today_number = date("z", time());
        $month_number = date("m", time());

        $scorecard        = $this->clients_recapitulation_model->get_client_recapitulation_today($client_recapitulation_today['recapitulation_date']);
        $staffs        = $this->clients_recapitulation_model->get_staff_grouped_today($client_recapitulation_today['recapitulation_date']);
        
        /*
        $scorecard->assigned_path = FCPATH . get_scorecard_upload_path('scorecard').$scorecard->id.'/assigned-'.$scorecard_number.'.png';
        $scorecard->acceptance_path = FCPATH . get_scorecard_upload_path('scorecard').$scorecard->id .'/'.$scorecard->signature;
        $scorecard->client_company = $this->clients_model->get($scorecard->clientid)->company;
        $scorecard->acceptance_date_string = _dt($scorecard->acceptance_date);
        */

        try {
            $pdf = scorecard_client_today_pdf($scorecard, $staffs, $client_recapitulation_today);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $file_name = slug_it('scorecard-day-' . $today_number);
        $fileName = mb_strtolower($file_name) . '.pdf';

        _maybe_create_upload_path('uploads/scorecards');
        _maybe_create_upload_path('uploads/scorecards/'.$month_number);

        if($type == 'F'){
            $fileName = FCPATH . get_scorecard_upload_path('scorecard').$month_number.'/'.$fileName;
        }
        

        $pdf->Output($fileName, $type);

    }

}