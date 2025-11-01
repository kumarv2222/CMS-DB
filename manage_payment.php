<?php 
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

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
$branch_id = $_SESSION['login_branch_id'] ?? 0;
if(!$branch_id) {
    echo "No branch assigned. Please contact administrator.";
    exit;
}

// Verify branch exists
$branch_check = $conn->query("SELECT id FROM branches WHERE id = " . (int)$branch_id);
if($branch_check->num_rows == 0) {
    echo "Invalid branch assignment. Please contact administrator.";
    exit;
}

// For editing existing payment
if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
    $qry = $conn->query("SELECT p.*, pl.price as parcel_price 
        FROM payments p 
        INNER JOIN parcels pl ON p.parcel_id = pl.id
        WHERE p.id = $id 
        AND (pl.from_branch_id = '$branch_id' OR pl.to_branch_id = '$branch_id')");
    
    if($qry->num_rows > 0){
        $row = $qry->fetch_assoc();
        foreach($row as $k => $v){
            $$k = $v;
        }
    }
}
?>

<div class="container-fluid">
	<form action="" id="manage-payment">
		<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
		<div class="form-group">
			<label for="">Parcel</label>
			<select name="parcel_id" id="parcel_id" class="custom-select select2" required>
				<option value=""></option>
				<?php 
				$parcel = $conn->query("SELECT p.* 
					FROM parcels p 
					LEFT JOIN payments pay ON p.id = pay.parcel_id AND pay.payment_status = 1
					WHERE (p.from_branch_id = '$branch_id' OR p.to_branch_id = '$branch_id')
					AND (pay.id IS NULL OR pay.payment_status != 1)
					ORDER BY p.reference_number ASC");
				while($row=$parcel->fetch_assoc()):
				?>
				<option value="<?php echo $row['id'] ?>" 
					data-price="<?php echo $row['price'] ?>"
					<?php echo isset($parcel_id) && $parcel_id == $row['id'] ? 'selected' : '' ?>>
					<?php echo $row['reference_number'] ?> - ₱<?php echo number_format($row['price'], 2) ?>
				</option>
				<?php endwhile; ?>
			</select>
		</div>
		<div class="form-group">
			<label for="">Amount</label>
			<input type="number" step="any" class="form-control" name="amount" value="<?php echo isset($amount) ? $amount : '' ?>" required>
		</div>
		<div class="form-group">
			<label for="">Payment Method</label>
			<select name="payment_method" class="custom-select" required>
				<option value="Cash" <?php echo isset($payment_method) && $payment_method == 'Cash' ? 'selected' : '' ?>>Cash</option>
				<option value="Credit Card" <?php echo isset($payment_method) && $payment_method == 'Credit Card' ? 'selected' : '' ?>>Credit Card</option>
				<option value="Bank Transfer" <?php echo isset($payment_method) && $payment_method == 'Bank Transfer' ? 'selected' : '' ?>>Bank Transfer</option>
				<option value="Digital Wallet" <?php echo isset($payment_method) && $payment_method == 'Digital Wallet' ? 'selected' : '' ?>>Digital Wallet</option>
			</select>
		</div>
		<div class="form-group">
			<label for="">Reference Number</label>
			<input type="text" class="form-control" name="reference_number" value="<?php echo isset($reference_number) ? $reference_number : '' ?>">
		</div>
		<div class="form-group">
			<label for="">Status</label>
			<select name="payment_status" id="payment_status" class="custom-select" required>
				<option value="0" <?php echo isset($payment_status) && $payment_status == 0 ? 'selected' : '' ?>>Pending</option>
				<option value="1" <?php echo isset($payment_status) && $payment_status == 1 ? 'selected' : '' ?>>Paid</option>
				<option value="2" <?php echo isset($payment_status) && $payment_status == 2 ? 'selected' : '' ?>>Refunded</option>
			</select>
		</div>
		<div class="form-group">
			<label for="">Remarks</label>
			<textarea name="remarks" class="form-control"><?php echo isset($remarks) ? $remarks : '' ?></textarea>
		</div>
	</form>
</div>

<script>
	$(document).ready(function(){
		$('.select2').select2({
			placeholder: "Please select here",
			width: "100%"
		});

		// Auto-fill amount when parcel is selected
		$('#parcel_id').change(function(){
			var price = $(this).find(':selected').data('price');
			if(price){
				$('[name="amount"]').val(price);
			}
		});

		$('#manage-payment').submit(function(e){
			e.preventDefault();
			start_load();
			$.ajax({
				url: 'ajax.php?action=save_payment',
				method: 'POST',
				data: $(this).serialize(),
				success: function(resp){
					if(resp == 1){
						alert_toast("Payment successfully saved", 'success');
						setTimeout(function(){
							location.reload();
						}, 1500);
					} else {
						alert_toast("Error: " + resp, 'error');
						end_load();
					}
				},
				error: function(xhr, status, error){
					alert_toast("An error occurred", 'error');
					end_load();
				}
			});
		});
	});
</script> 