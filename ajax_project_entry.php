<?php

header('Content-Type: application/json');
if ($_REQUEST && !empty($_REQUEST['project_id'])) {
    require_once( 'connection.php' );
    $range = Harvest_Range::thisMonth(); //by default entry will show of current month if user not select any date

    if ($_REQUEST['start_date'] != "" && $_REQUEST['end_date'] != "") {
        $from_d = explode('-', $_REQUEST['start_date']);
        $from = $from_d[2] . $from_d[0] . $from_d[1];

        $to_d = explode('-', $_REQUEST['end_date']);
        $to = $to_d[2] . $to_d[0] . $to_d[1];

        $range = new Harvest_Range($from, $to);
    }

    $project_id = $_REQUEST['project_id']; //posted value of project dropdown
    $member_id = $_REQUEST['member_id']; //posted value of member dropdown

    $array = array();

    if ($project_id == "all" && $member_id != "") {
        $member_entry = $api->getUserEntries($member_id, $range);
        if ($member_entry->isSuccess()) {
            $totalHour = 0;
            $billableHour = 0;
            $unBillableHour = 0;
            $array['entry_type'] = "member"; //find all user entry on different projects
            $projectIds = array();
            foreach ($member_entry->data as $row) {
                /*
                  [id] => 459695350
                  [notes] => Php coading
                  [spent-at] => 2016-05-04
                  [hours] => 2.02
                  [user-id] => 1276189
                  [project-id] => 10656608
                  [task-id] => 5933717
                  [created-at] => 2016-05-04T16:36:52Z
                  [updated-at] => 2016-05-04T16:38:05Z
                  [adjustment-record] => false
                  [timer-started-at] =>
                  [is-closed] => false
                  [is-billed] => false */
                $totalHour = $totalHour + $row->hours;

                $p_id = $row->get('project-id');

                if (!in_array($p_id, $projectIds)) {

                    $projectIds[] = $p_id;

                    $project_name = $api->getProject($p_id);
                    $projectname = $project_name->data->name;

                    if ($project_name->data->billable == 'true') {
                        $billableHour = $billableHour + $row->hours;
                    } else {
                        $unBillableHour = $unBillableHour + $row->hours;
                    }

                    $client_id = $project_name->data->get('client-id');
                    $client_data = $api->getClient($client_id);
                    $client_name = $client_data->data->name;

                    $projectname_Cache[$p_id] = $projectname;
                    $projectBillable_Cache[$p_id] = $project_name->data->billable;
                    $clientname_Cache[$p_id] = $client_name;
                } else {
                    if ($projectBillable_Cache[$p_id] == 'true') {
                        $billableHour = $billableHour + $row->hours;
                    } else {
                        $unBillableHour = $unBillableHour + $row->hours;
                    }
                    $projectname = $projectname_Cache[$p_id];
                    $client_name = $clientname_Cache[$p_id];
                }
                $array['data'][] = array('name' => $projectname, 'client' => $client_name, 'hours' => $row->hours, 'date' => date('d-F-Y', strtotime($row->get('spent-at'))), 'note' => $row->notes);
            }
            $array['totalhour'] = $totalHour;
            $array['billablehour'] = $billableHour;
            $array['unbillablehour'] = $unBillableHour;
        }
    } else if ($project_id != "all") {
        if ($member_id == "") {
            $member_id = null;  //if we not choose any member from member list dropdown then member condition will not apply and so we are passing here null   
        }
        //echo $member_id; die('mog');
        //if we pass member id in below function then its will give only project entry of that perticuler user. 
        $project = $api->getProjectEntries($project_id, $range, $member_id);
        if ($project->isSuccess()) {
            $array['entry_type'] = "project"; //find all project entry on different users
            $totalHour = 0;
            $billableHour = 0;
            $unBillableHour = 0;
            $userIds = array();
            foreach ($project->data as $row) {
                /*
                  [id] => 458587213
                  [notes] => design work
                  [spent-at] => 2016-05-02
                  [hours] => 2.3
                  [user-id] => 1276189
                  [project-id] => 10656579
                  [task-id] => 5933716
                  [created-at] => 2016-05-02T17:05:12Z
                  [updated-at] => 2016-05-02T17:05:12Z
                  [adjustment-record] => false
                  [timer-started-at] =>
                  [is-closed] => false
                  [is-billed] => false */ 
                $totalHour = $totalHour + $row->hours;

                $u_id = $row->get('user-id');
                if (!in_array($u_id, $userIds)) {
                    $userIds[] = $u_id;
                    $user = $api->getUser($u_id);
                    $username = $user->data->get('first-name') . ' ' . $user->data->get('last-name');
                    $username_Cache[$u_id] = $username;
                } else {
                    $username = $username_Cache[$u_id];
                }
                $array['data'][] = array('name' => $username, 'hours' => $row->hours, 'date' => date('d-F-Y', strtotime($row->get('spent-at'))), 'note' => $row->notes);
            }
            $array['totalhour'] = $totalHour;
            $array['billablehour'] = $totalHour;
            $array['unbillablehour'] = $unBillableHour;
        }
    }
    //echo "<pre>";    print_r($array);    die;
    echo json_encode($array);
}
?>