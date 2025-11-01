<?php 
include 'db_connect.php';

// Initialize session variables if not set
if(!isset($_SESSION['login_type'])) {
    echo "Please log in first";
    exit;
}

$login_type = $_SESSION['login_type'];
$login_branch_id = isset($_SESSION['login_branch_id']) ? $_SESSION['login_branch_id'] : 0;

// Build the SQL query based on user type
if($login_type == 1) { // Admin
    $sql = "SELECT p.*, 
            b1.branch_code as from_branch,
            b2.branch_code as to_branch 
            FROM parcels p 
            LEFT JOIN branches b1 ON p.from_branch_id = b1.id 
            LEFT JOIN branches b2 ON p.to_branch_id = b2.id 
            ORDER BY unix_timestamp(p.date_created) DESC";
} else { // Staff
    if(empty($login_branch_id)) {
        // Check if user actually has a branch assigned
        $user_id = $_SESSION['login_id'];
        $branch_check = $conn->query("SELECT branch_id, b.branch_code 
                                    FROM users u 
                                    LEFT JOIN branches b ON u.branch_id = b.id 
                                    WHERE u.id = '$user_id'");
        if($branch_check->num_rows > 0) {
            $branch_data = $branch_check->fetch_assoc();
            if(!empty($branch_data['branch_id'])) {
                $_SESSION['login_branch_id'] = $branch_data['branch_id'];
                $login_branch_id = $branch_data['branch_id'];
            }
        }
        
        if(empty($login_branch_id)) {
            echo "No branch assigned. Please contact administrator.";
            exit;
        }
    }
    
    $sql = "SELECT p.*, 
            b1.branch_code as from_branch,
            b2.branch_code as to_branch 
            FROM parcels p 
            LEFT JOIN branches b1 ON p.from_branch_id = b1.id 
            LEFT JOIN branches b2 ON p.to_branch_id = b2.id 
            WHERE p.from_branch_id = '$login_branch_id' 
            OR p.to_branch_id = '$login_branch_id' 
            ORDER BY unix_timestamp(p.date_created) DESC";
}

$qry = $conn->query($sql);
if(!$qry) {
    echo "Error: " . $conn->error;
    exit;
}
?>
<div class="col-lg-12">
	<div class="card card-outline card-primary">
		<div class="card-header">
			<div class="card-tools">
				<a class="btn btn-block btn-sm btn-default btn-flat border-primary" href="./index.php?page=new_parcel"><i class="fa fa-plus"></i> Add New</a>
			</div>
		</div>
		<div class="card-body">
			<table class="table tabe-hover table-bordered" id="list">
				<thead>
					<tr>
						<th class="text-center">#</th>
						<th>Reference Number</th>
						<th>Sender Name</th>
						<th>Recipient Name</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$i = 1;
					while($row = $qry->fetch_assoc()):
					?>
					<tr>
						<td class="text-center"><?php echo $i++ ?></td>
						<td><b><?php echo ($row['reference_number']) ?></b></td>
						<td><b><?php echo ucwords($row['sender_name']) ?></b></td>
						<td><b><?php echo ucwords($row['recipient_name']) ?></b></td>
						<td class="text-center">
							<?php 
							switch ($row['status']) {
								case '1':
									echo "<span class='badge badge-pill badge-info'>Collected</span>";
									break;
								case '2':
									echo "<span class='badge badge-pill badge-info'>Shipped</span>";
									break;
								case '3':
									echo "<span class='badge badge-pill badge-primary'>In-Transit</span>";
									break;
								case '4':
									echo "<span class='badge badge-pill badge-primary'>Arrived At Destination</span>";
									break;
								case '5':
									echo "<span class='badge badge-pill badge-primary'>Out for Delivery</span>";
									break;
								case '6':
									echo "<span class='badge badge-pill badge-primary'>Ready to Pickup</span>";
									break;
								case '7':
									echo "<span class='badge badge-pill badge-success'>Delivered</span>";
									break;
								case '8':
									echo "<span class='badge badge-pill badge-success'>Picked-up</span>";
									break;
								case '9':
									echo "<span class='badge badge-pill badge-danger'>Unsuccessful Delivery Attempt</span>";
									break;
								default:
									echo "<span class='badge badge-pill badge-info'>Item Accepted by Courier</span>";
									break;
							}
							?>
						</td>
						<td class="text-center">
							<div class="btn-group">
								<button type="button" class="btn btn-info btn-flat view_parcel" data-id="<?php echo $row['id'] ?>">
									<i class="fas fa-eye"></i>
								</button>
								<a href="index.php?page=edit_parcel&id=<?php echo $row['id'] ?>" class="btn btn-primary btn-flat">
									<i class="fas fa-edit"></i>
								</a>
								<button type="button" class="btn btn-danger btn-flat delete_parcel" data-id="<?php echo $row['id'] ?>">
									<i class="fas fa-trash"></i>
								</button>
							</div>
						</td>
					</tr>	
					<?php endwhile; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<script>
	$(document).ready(function(){
		$('#list').dataTable()
		$('.view_parcel').click(function(){
			uni_modal("Parcel's Details","view_parcel.php?id="+$(this).attr('data-id'),"large")
		})
		$('.delete_parcel').click(function(){
			_conf("Are you sure to delete this parcel?","delete_parcel",[$(this).attr('data-id')])
		})
	})
	function delete_parcel($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_parcel',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully deleted",'success')
					setTimeout(function(){
						location.reload()
					},1500)
				}
			}
		})
	}
</script>