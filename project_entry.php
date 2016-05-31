<?php
    require_once( 'connection.php' );
    $range = Harvest_Range::thisMonth();
    
//    if($_POST['start_date']!="" && $_POST['end_date']!=""){
//        $from = $_POST['start_date'];
//        $to = $_POST['end_date'];
//        $range = new Harvest_Range(date('Ymd', strtotime($from)), date('Ymd', strtotime($to)));
//    }
    
    $project_id = $_GET['project_id']; 
    $project = $api->getProjectEntries($project_id, $range); 
    $array = array();
    if ($project->isSuccess()) {        
        foreach ($project->data as $row) {
            $user = $api->getUser($row->get('user-id'));
            $username = $user->data->get('first-name') . ' ' . $user->data->get('last-name');
            $array[] = array('username' => $username, 'hours' => $row->hours, 'date' => date('d-F-Y',  strtotime($row->get('spent-at'))), 'note' => $row->notes);
        }        
    }
    echo "<pre>";    
    print_r($array);
    
?>