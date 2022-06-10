    <?php
    defined('BASEPATH') or exit('No direct script access allowed');

    class Clients_recapitulation_model extends App_Model
    {
        public function __construct()
        {
            parent::__construct();

            $this->load->model('clients_model');
            $this->load->model('projects_model');
            $this->load->model('staff_model');
        }

    public function get_client_progress(){
        $this->db->select([
           db_prefix().'clients.company',
           db_prefix().'projects.name AS project_name',
           db_prefix().'projects.start_date',
           db_prefix().'tags.name AS tag_name',
           'count('. db_prefix().'tasks.id) AS "task"',
           'COUNT(IF(  '.db_prefix().'tasks.status = 1, 1, NULL )) task_status_1',
           'COUNT(IF(  '.db_prefix().'tasks.status = 4, 1, NULL )) task_status_4',
           'COUNT(IF(  '.db_prefix().'tasks.status = 3, 1, NULL )) task_status_3',
           'COUNT(IF(  '.db_prefix().'tasks.status = 2, 1, NULL )) task_status_2',
           'COUNT(IF(  '.db_prefix().'tasks.status = 5, 1, NULL )) task_status_5',  
        ]);

        $this->db->join(db_prefix() . 'projects', db_prefix() . 'projects.id = ' . db_prefix() . 'tasks.rel_id');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'projects.clientid');
        $this->db->join(db_prefix() . 'taggables', db_prefix() . 'taggables.rel_id = ' . db_prefix() . 'tasks.id');
        $this->db->join(db_prefix() . 'tags', db_prefix() . 'tags.id = ' . db_prefix() . 'taggables.tag_id');
    
        $this->db->group_by(['company',db_prefix().'tags.name']);
        $this->db->where(db_prefix() . 'projects.status !=', '5');
        $this->db->order_by('start_date', 'DESC');

        //return $this->db->get_compiled_select(db_prefix() . 'tasks');

        $scorecards =  $this->db->get(db_prefix() . 'tasks')->result();

        return $scorecards;

    }

    public function get_daily_count_update_status(){
        $this->db->select(['DATE(dateadded) AS date_added',  
            'COUNT(IF( STATUS = 1, 1, NULL )) task_status_1',
            'COUNT(IF( STATUS = 4, 1, NULL )) task_status_4',
            'COUNT(IF( STATUS = 3, 1, NULL )) task_status_3',
            'COUNT(IF( STATUS = 2, 1, NULL )) task_status_2',
            'COUNT(IF( STATUS = 5, 1, NULL )) task_status_5',
        ]);
        $this->db->group_by('date_added');
        $this->db->order_by('date_added', 'DESC');

        $this->db->join(db_prefix() . 'task_assigned',db_prefix() . 'task_assigned.taskid = ' . db_prefix() . 'scorecards_clients_history.task_id');
    
        //return $this->db->get_compiled_select(db_prefix() . 'scorecards_clients_history');

        $last_updated =  $this->db->get(db_prefix() . 'scorecards_clients_history')->result();

        return $last_updated;
    }


    }

