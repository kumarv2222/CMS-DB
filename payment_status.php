<?php include('db_connect.php');?>

<div class="container-fluid">
    <div class="col-lg-12">
        <!-- Payment Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <?php 
                        $total = $conn->query("SELECT COUNT(*) as total FROM payments")->fetch_assoc()['total'];
                        ?>
                        <h4><b>Total Payments</b></h4>
                        <h1><?php echo number_format($total) ?></h1>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <?php 
                        $paid = $conn->query("SELECT COUNT(*) as total FROM payments WHERE payment_status = 1")->fetch_assoc()['total'];
                        ?>
                        <h4><b>Paid</b></h4>
                        <h1><?php echo number_format($paid) ?></h1>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <?php 
                        $pending = $conn->query("SELECT COUNT(*) as total FROM payments WHERE payment_status = 0")->fetch_assoc()['total'];
                        ?>
                        <h4><b>Pending</b></h4>
                        <h1><?php echo number_format($pending) ?></h1>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <?php 
                        $refunded = $conn->query("SELECT COUNT(*) as total FROM payments WHERE payment_status = 2")->fetch_assoc()['total'];
                        ?>
                        <h4><b>Refunded</b></h4>
                        <h1><?php echo number_format($refunded) ?></h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment List -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <b>Payment Status Overview</b>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Branch</th>
                                    <th>Parcel Ref</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $i = 1;
                                $payments = $conn->query("SELECT p.*, pl.reference_number as ref_no, b.branch_code, b.city 
                                    FROM payments p 
                                    INNER JOIN parcels pl ON p.parcel_id = pl.id 
                                    LEFT JOIN branches b ON pl.from_branch_id = b.id 
                                    ORDER BY p.date_created DESC");
                                while($row = $payments->fetch_assoc()):
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $i++; ?></td>
                                    <td>
                                        <p class="m-0"><?php echo $row['branch_code']?></p>
                                        <small><i><?php echo $row['city']?></i></small>
                                    </td>
                                    <td><?php echo $row['ref_no'] ?></td>
                                    <td>₱ <?php echo number_format($row['amount'],2) ?></td>
                                    <td><?php echo $row['payment_method'] ?></td>
                                    <td>
                                        <?php 
                                        switch($row['payment_status']){
                                            case 0:
                                                echo '<span class="badge badge-warning">Pending</span>';
                                                break;
                                            case 1:
                                                echo '<span class="badge badge-success">Paid</span>';
                                                break;
                                            case 2:
                                                echo '<span class="badge badge-danger">Refunded</span>';
                                                break;
                                            default:
                                                echo '<span class="badge badge-secondary">Unknown</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo date('M d, Y g:i A',strtotime($row['date_created'])) ?></td>
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
    $('.table').DataTable({
        "order": [[ 6, "desc" ]],  // Sort by date column by default
        "pageLength": 25           // Show 25 entries per page
    });
});
</script> 