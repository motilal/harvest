<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>Members</title>

        <!-- Bootstrap -->
        <link href="css/bootstrap.min.css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="js/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="js/bootstrap.min.js"></script>
        <script src="js/datetimepicker/moment-with-locales.js"></script> 
        <script src="js/datetimepicker/bootstrap-datetimepicker.min.js"></script> 
        <link rel="stylesheet" href="js/datetimepicker/bootstrap-datetimepicker.css" type="text/css" media="screen" /> 

    </head>
    <body>
        <?php require_once( 'connection.php' ); ?>
        <div class="container"> 
            <div class="row">
                <div class="page-header">
                    <!--                <h1>All Project Member</h1>-->   
                </div>


                <form class="form-inline" action="ajax_project_entry.php" method="post" id="pr_entry_form">
                    <div class="col-lg-2">
                        <?php $clients = $api->getClients(); ?>
                        <select class="form-control" name="clients" id="clients"> 
                            <option value="all">All Clients</option>
                            <?php
                            foreach ($clients->data as $row) {
                                if ($clients->isSuccess()) {
                                    if ($row->active == 'true') {
                                        echo "<option value='{$row->id}'>{$row->name}</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-lg-2">                         
                        <?php $projects = $api->getProjects(); ?>
                        <select class="form-control" name="project_id" id="project" required="">
                            <option value="all">All Projects</option>
                            <?php
                            foreach ($projects->data as $row) {
                                if ($projects->isSuccess()) {
                                    if ($row->active == 'true') {
                                        echo "<option value='{$row->id}'>{$row->name}</option>";
                                    }
                                }
                            }
                            ?>
                        </select>                         
                    </div>

                    <div class="col-lg-2">
                        <?php $member = $api->getUsers(); ?>
                        <select class="form-control" name="member_id" id="member">
                            <option value="">Select Member</option> 
                            <?php
                            foreach ($member->data as $row) {
                                if ($member->isSuccess()) {
                                    if ($row->get('is-active') == 'true') {
                                        $memberName = $row->get('first-name') . ' ' . $row->get('last-name');
                                        echo "<option value='{$row->id}'>{$memberName}</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-lg-2">
                        <input type="text" class="form-control" id="start_date" name="start_date" placeholder="Date From" value="<?php echo date('m-01-Y'); ?>">
                    </div>

                    <div class="col-lg-2">
                        <input type="text" class="form-control" id="end_date" name="end_date" placeholder="Date To">
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-primary">Run Report</button>
                        <img src="img/ajax-loader.gif" class="ajax_loader" style="display: none;">
                    </div>
                </form>


                <div class="col-lg-12" style="margin-top: 20px;" id="project-entry">
                    <div class="panel panel-default">
                        <table class="table table-striped listing-table">
                            <thead>  </thead>
                            <tbody>  <tr><td colspan="4" align="center" id="no-entry">No results found</td></tr> </tbody>
                        </table>
                    </div> 
                </div>
            </div>


        </div> 
        <script>
            $(document).ready(function() {
                $('#clients').on('change', function() {
                    if ($(this).val() != "") {
                        $('#project').html('<option value="">Loading Projects...</option>');
                        $.post('ajax_client_project.php', {client_id: $(this).val()}, function(result) {
                            $("#project").empty();
                            $.each(result, function(index, val) {
                                var newOption = "<option value='" + val.id + "'>" + val.name + "</option>";
                                $("#project").append(newOption);
                            });
                        }, "json");
                    }
                });

                $('#pr_entry_form').on('submit', function(e) {
                    e.preventDefault();
                    $('.ajax_loader').show();
                    $.post('ajax_project_entry.php', $(this).serialize(), function(result) {
                        $('.ajax_loader').hide();
                        $("table.listing-table > tbody").empty();

                        if (typeof result.data === 'undefined') {
                            $("table.listing-table > tbody").html('<tr><td colspan="4" align="center" id="no-entry">No results</td></tr>');
                        } else {
                            if (result.entry_type == 'member') {
                                $('table.listing-table > thead').html('<tr>'
                                        + '<th>Project</th>'
                                        + '<th>Client</th>'
                                        + '<th>Hours</th>'
                                        + '<th>Date</th>'
                                        + '<th>Detail</th>'
                                        + '</tr>');
                                $.each(result.data, function(index, val) {
                                    var newOption = "<tr><td>" + val.name + "</td><td>" + val.client + "</td><td>" + val.hours + "</td><td>" + val.date + "</td><td>" + val.note + "</td></tr>";
                                    $("table.listing-table > tbody").append(newOption);
                                });
                                $("table.listing-table > tbody").append("<tr><td></td><td></td><th>Billable : " + result.billablehour + " Hour</th><th>Non Billable : " + result.unbillablehour + "</th><th>Total : " + result.totalhour + " Hour</th></tr>");
                            } else {
                                $('table.listing-table > thead').html('<tr>'
                                        + '<th>Member</th>'
                                        + '<th>Hours</th>'
                                        + '<th>Date</th>'
                                        + '<th>Detail</th>'
                                        + '</tr>');
                                $.each(result.data, function(index, val) {
                                    var newOption = "<tr><td>" + val.name + "</td><td>" + val.hours + "</td><td>" + val.date + "</td><td>" + val.note + "</td></tr>";
                                    $("table.listing-table > tbody").append(newOption);
                                });
                                $("table.listing-table > tbody").append("<tr><td></td><th>Billable : " + result.billablehour + " Hour</th><th>Non Billable : " + result.unbillablehour + "</th><th>Total : " + result.totalhour + " Hour</th></tr>");
                            }


                        }
                    }, "json");

                });

                $('#start_date').datetimepicker({
                    format: 'MM-DD-YYYY'
                });
                $('#end_date').datetimepicker({
                    useCurrent: false,
                    format: 'MM-DD-YYYY'
                });
                $("#start_date").on("dp.change", function(e) {
                    $('#end_date').data("DateTimePicker").minDate(e.date);
                });


            });

        </script>
        <style>
            .form-control{
                width: 100% !important; 
            }
        </style>
    </body>
</html>