<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('db_connect.php');

// Debug session
if(isset($_GET['debug'])) {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    exit;
}

// Check if user is logged in
if(!isset($_SESSION['login_id'])) {
    echo "Please log in first";
    exit;
}

// Check if user is staff
if($_SESSION['login_type'] != 2) {
    echo "Access denied. Staff only area.";
    exit;
}

// Get and verify branch ID
$branch_id = isset($_SESSION['login_branch_id']) ? $_SESSION['login_branch_id'] : 0;

// For staff users, verify branch assignment
if($_SESSION['login_type'] == 2) {
    if(empty($branch_id)) {
        echo "No branch assigned. Please contact administrator.";
        exit;
    }
    
    // Verify branch exists
    $branch_qry = $conn->query("SELECT * FROM branches WHERE id = " . (int)$branch_id);
    if($branch_qry->num_rows == 0) {
        echo "Invalid branch assignment. Please contact administrator.";
        exit;
    }
    $branch = $branch_qry->fetch_assoc();
}

?>

<div class="container-fluid">
	<div class="col-lg-12">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<b>List of Payments</b>
						<span class="float:right"><a class="btn btn-primary btn-sm col-sm-3 float-right" href="javascript:void(0)" id="new_payment">
							<i class="fa fa-plus"></i> New Payment
						</a></span>
					</div>
					<div class="card-body">
						<!-- Add search form -->
						<div class="row mb-3">
							<div class="col-md-4">
								<div class="input-group">
									<input type="text" class="form-control" id="search" placeholder="Search reference number...">
									<div class="input-group-append">
										<button class="btn btn-outline-secondary" type="button" id="search_btn">
											<i class="fa fa-search"></i>
										</button>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<select class="form-control" id="status_filter">
									<option value="">All Status</option>
									<option value="0">Pending</option>
									<option value="1">Paid</option>
									<option value="2">Refunded</option>
								</select>
							</div>
							<div class="col-md-3">
								<input type="date" class="form-control" id="date_filter">
							</div>
							<div class="col-md-2">
								<button class="btn btn-secondary" id="reset_filter">Reset</button>
							</div>
						</div>
						
						<table class="table table-bordered table-condensed">
							<thead>
								<tr>
									<th class="text-center">#</th>
									<th class="">Parcel Reference</th>
									<th class="">Amount</th>
									<th class="">Payment Method</th>
									<th class="">Status</th>
									<th class="">Date</th>
									<th class="text-center">Action</th>
								</tr>
							</thead>
							<tbody id="payment_list">
								<?php 
								$i = 1;
								$payments = $conn->query("SELECT DISTINCT p.*, pl.reference_number as ref_no, pl.price as parcel_price 
									FROM payments p 
									INNER JOIN parcels pl ON pl.id = p.parcel_id 
									WHERE pl.from_branch_id = '$branch_id' 
										OR pl.to_branch_id = '$branch_id'
									GROUP BY p.id
									ORDER BY p.date_created DESC");
								while($row=$payments->fetch_assoc()):
								?>
								<tr>
									<td class="text-center"><?php echo $i++ ?></td>
									<td class=""><?php echo $row['ref_no'] ?></td>
									<td class="">₱ <?php echo number_format($row['amount'],2) ?></td>
									<td class=""><?php echo $row['payment_method'] ?></td>
									<td class="">
										<?php 
										switch ($row['payment_status']) {
											case '0':
												echo "<span class='badge badge-warning'>Pending</span>";
												break;
											case '1':
												echo "<span class='badge badge-success'>Paid</span>";
												break;
											case '2':
												echo "<span class='badge badge-danger'>Refunded</span>";
												break;
											default:
												echo "<span class='badge badge-info'>Unknown</span>";
												break;
										}
										?>
									</td>
									<td class=""><?php echo date('M d, Y',strtotime($row['date_created'])) ?></td>
									<td class="text-center">
										<button class="btn btn-sm btn-outline-primary edit_payment" type="button" data-id="<?php echo $row['id'] ?>">Edit</button>
										<button class="btn btn-sm btn-outline-danger delete_payment" type="button" data-id="<?php echo $row['id'] ?>">Delete</button>
									</td>
								</tr>
								<?php endwhile; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function(){
		$('#new_payment').click(function(){
			uni_modal("New Payment","manage_payment.php","mid-large");
		});
		
		// Add this to handle form submission from the modal
		$('#uni_modal').on('shown.bs.modal', function(){
			if($('#manage-payment').length > 0){
				$('#uni_modal .modal-footer').html('<button type="button" class="btn btn-primary" form="manage-payment">Save</button><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>');
			}
		});
		
		$('#uni_modal').on('click', '.modal-footer .btn-primary', function(){
			$('#manage-payment').submit();
		});
		
		$('.edit_payment').click(function(){
			uni_modal("Edit Payment","manage_payment.php?id="+$(this).attr('data-id'),"mid-large");
		});
		
		$('.delete_payment').click(function(){
			_conf("Are you sure to delete this payment?","delete_payment",[$(this).attr('data-id')]);
		});

		// Search functionality
		function loadPayments(search = '', status = '', date = '') {
			$.ajax({
				url: 'ajax.php?action=search_payments',
				method: 'POST',
				data: {
					search: search,
					status: status,
					date: date
				},
				success: function(response) {
					$('#payment_list').html(response);
				}
			});
		}

		$('#search_btn').click(function() {
			var search = $('#search').val();
			var status = $('#status_filter').val();
			var date = $('#date_filter').val();
			loadPayments(search, status, date);
		});

		$('#search').keypress(function(e) {
			if(e.which == 13) {
				$('#search_btn').click();
			}
		});

		$('#status_filter').change(function() {
			$('#search_btn').click();
		});

		$('#date_filter').change(function() {
			$('#search_btn').click();
		});

		$('#reset_filter').click(function() {
			$('#search').val('');
			$('#status_filter').val('');
			$('#date_filter').val('');
			loadPayments();
		});
	});

	function delete_payment($id){
		start_load();
		$.ajax({
			url:'ajax.php?action=delete_payment',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully deleted",'success');
					setTimeout(function(){
						location.reload();
					},1500);
				}
			}
		});
	}
</script> 