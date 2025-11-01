<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();
date_default_timezone_set("Asia/Manila");

$action = $_GET['action'];
include 'admin_class.php';
include 'db_connect.php';
$crud = new Action();

switch($action) {
	case 'login':
	$login = $crud->login();
	if($login)
		echo $login;
		break;

	case 'login2':
	$login = $crud->login2();
	if($login)
		echo $login;
		break;

	case 'logout':
	$logout = $crud->logout();
	if($logout)
		echo $logout;
		break;

	case 'logout2':
	$logout = $crud->logout2();
	if($logout)
		echo $logout;
		break;

	case 'signup':
	$save = $crud->signup();
	if($save)
		echo $save;
		break;

	case 'save_user':
	$save = $crud->save_user();
	if($save)
		echo $save;
		break;

	case 'update_user':
	$save = $crud->update_user();
	if($save)
		echo $save;
		break;

	case 'delete_user':
	$save = $crud->delete_user();
	if($save)
		echo $save;
		break;

	case 'save_branch':
	$save = $crud->save_branch();
	if($save)
		echo $save;
		break;

	case 'delete_branch':
	$save = $crud->delete_branch();
	if($save)
		echo $save;
		break;

	case 'save_parcel':
	$save = $crud->save_parcel();
	if($save)
		echo $save;
		break;

	case 'delete_parcel':
	$save = $crud->delete_parcel();
	if($save)
		echo $save;
		break;

	case 'update_parcel':
	$save = $crud->update_parcel();
	if($save)
		echo $save;
		break;

	case 'get_parcel_heistory':
	$get = $crud->get_parcel_heistory();
	if($get)
		echo $get;
		break;

	case 'get_report':
	$get = $crud->get_report();
	if($get)
		echo $get;
		break;

	case 'save_payment':
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
		$branch_check = $conn->query("SELECT id FROM branches WHERE id = " . (int)$branch_id);
		if($branch_check->num_rows == 0) {
			echo "Invalid branch assignment";
			exit;
		}

		try {
			// Sanitize inputs
			$parcel_id = (int)$_POST['parcel_id'];
			$amount = (float)$_POST['amount'];
			$payment_status = (int)$_POST['payment_status'];
			
			// Verify parcel belongs to staff's branch
			$parcel_check = $conn->query("SELECT id, price FROM parcels 
				WHERE id = $parcel_id 
				AND (from_branch_id = $branch_id OR to_branch_id = $branch_id)")->fetch_assoc();
				
			if(!$parcel_check){
				echo "Invalid parcel";
				exit;
			}
			
			// Validate amount
			if (!is_numeric($_POST['amount']) || $_POST['amount'] <= 0) {
				echo 'invalid_amount';
				exit;
			}
			
			// Validate status
			$valid_statuses = array(0, 1, 2);
			if (!in_array($_POST['payment_status'], $valid_statuses)) {
				echo 'invalid_status';
				exit;
			}
			
			// Generate reference if not provided
			$reference = !empty($_POST['reference_number']) ? 
						mysqli_real_escape_string($conn, $_POST['reference_number']) : 
						'PAY-' . date('Ymd') . '-' . sprintf("%06d", mt_rand(1, 999999));
			
			$payment_date = !empty($_POST['payment_date']) ? 
						   mysqli_real_escape_string($conn, $_POST['payment_date']) : 
						   date('Y-m-d H:i:s');
			
			// Escape all inputs
			$parcel_id = mysqli_real_escape_string($conn, $_POST['parcel_id']);
			$amount = mysqli_real_escape_string($conn, $_POST['amount']);
			$payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
			$payment_status = mysqli_real_escape_string($conn, $_POST['payment_status']);
			$remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
			
			// Check if payment already exists for this parcel
			$payment_exists = $conn->query("SELECT id FROM payments 
				WHERE parcel_id = $parcel_id 
				AND payment_status = 1")->num_rows;

			if($payment_exists > 0) {
				echo "Payment already exists for this parcel";
				exit;
			}
			
			if(empty($_POST['id'])){
				// Insert new payment
				$query = "INSERT INTO payments (
					parcel_id, amount, payment_method, reference_number, 
					payment_date, payment_status, remarks, date_created
				) VALUES (
					'$parcel_id', '$amount', '$payment_method', '$reference',
					'$payment_date', '$payment_status', '$remarks', NOW()
				)";
			} else {
				$id = mysqli_real_escape_string($conn, $_POST['id']);
				// Update existing payment
				$query = "UPDATE payments SET 
					parcel_id = '$parcel_id',
					amount = '$amount',
					payment_method = '$payment_method',
					reference_number = '$reference',
					payment_date = '$payment_date',
					payment_status = '$payment_status',
					remarks = '$remarks'
					WHERE id = '$id'";
			}
			
			$save = $conn->query($query);
			
			if($save){
				echo 1;
			} else {
				echo "Query Error: " . $conn->error;
			}
		} catch (Exception $e) {
			echo "Error: " . $e->getMessage();
		}
		break;

	case 'delete_payment':
		// Check if user is staff
		if(!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 2){
			echo "Access denied";
			exit;
		}

		$branch_id = $_SESSION['login_branch_id'];
		
		// Verify payment belongs to staff's branch
		$payment_check = $conn->query("SELECT p.id FROM payments p 
			INNER JOIN parcels pc ON p.parcel_id = pc.id 
			WHERE p.id = '".$_POST['id']."' 
			AND (pc.from_branch_id = '$branch_id' OR pc.to_branch_id = '$branch_id')")->num_rows;
		
		if($payment_check > 0){
			$delete = $conn->query("DELETE FROM payments where id = ".$_POST['id']);
			if($delete)
				echo 1;
		} else {
			echo "Access denied";
		}
		break;

	case 'branch_report':
		$from_date = mysqli_real_escape_string($conn, $_POST['from_date']);
		$to_date = mysqli_real_escape_string($conn, $_POST['to_date']);
		$from_city = mysqli_real_escape_string($conn, $_POST['from_city']);
		$to_city = mysqli_real_escape_string($conn, $_POST['to_city']);
		
		$query = "SELECT COUNT(*) as total_parcels, 
				 b1.branch_code as from_branch, 
				 b2.branch_code as to_branch,
				 b1.city as from_city,
				 b2.city as to_city
				 FROM parcels p
				 INNER JOIN branches b1 ON p.from_branch_id = b1.id
				 INNER JOIN branches b2 ON p.to_branch_id = b2.id
				 WHERE b1.city = '$from_city' 
				 AND b2.city = '$to_city'
				 AND p.date_created BETWEEN '$from_date' AND '$to_date'
				 GROUP BY b1.branch_code, b2.branch_code";
				 
		$result = $conn->query($query);
		
		if($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			echo json_encode([
				'status' => 'success',
				'total_parcels' => $row['total_parcels'],
				'from_branch' => $row['from_branch'],
				'to_branch' => $row['to_branch'],
				'from_city' => $row['from_city'],
				'to_city' => $row['to_city']
			]);
		} else {
			echo json_encode([
				'status' => 'error',
				'message' => 'No parcels found for the given criteria'
			]);
		}
		break;
}

ob_end_flush();
?>
