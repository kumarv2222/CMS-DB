<?php include('db_connect.php');?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <b>Branch Parcel Report</b>
            </div>
            <div class="card-body">
                <form id="branch_report_form">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>From City</label>
                                <input type="text" name="from_city" class="form-control" value="Kankanadi" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>To City</label>
                                <input type="text" name="to_city" class="form-control" value="Mumbai" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>From Date</label>
                                <input type="date" name="from_date" class="form-control" value="2024-01-01" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>To Date</label>
                                <input type="date" name="to_date" class="form-control" value="2024-12-30" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Generate Report</button>
                        </div>
                    </div>
                </form>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div id="report_result" style="display:none;">
                            <h4>Report Results</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th>From Branch</th>
                                    <th>To Branch</th>
                                    <th>Total Parcels</th>
                                </tr>
                                <tr>
                                    <td id="from_branch"></td>
                                    <td id="to_branch"></td>
                                    <td id="total_parcels"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#branch_report_form').submit(function(e){
        e.preventDefault();
        $.ajax({
            url: 'ajax.php?action=branch_report',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response){
                if(response.status == 'success'){
                    $('#report_result').show();
                    $('#from_branch').text(response.from_branch + ' (' + response.from_city + ')');
                    $('#to_branch').text(response.to_branch + ' (' + response.to_city + ')');
                    $('#total_parcels').text(response.total_parcels);
                } else {
                    alert(response.message);
                }
            },
            error: function(err){
                console.log(err);
                alert('An error occurred while generating the report');
            }
        });
    });
});
</script> 