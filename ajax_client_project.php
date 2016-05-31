<?php

header('Content-Type: application/json');
if ($_POST && !empty($_POST['client_id'])) {
    require_once( 'connection.php' );
    if ($_POST['client_id'] == "all") {
        $project = $api->getProjects(); //get all prject listing 
    } else {
        $project = $api->getClientProjects($_POST['client_id']); //get specific client listing 
    }
    if ($project->isSuccess()) {         
        $projectArray = array(array('id' => 'all', 'name' => 'All Projects'));
        foreach ($project->data as $row) { 
            if($row->active=='true'){
                $projectArray[] = array('id' => $row->id, 'name' => $row->name);
            }
        }
    }
    echo json_encode($projectArray);
}
?>