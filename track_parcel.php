<?php include 'db_connect.php' ?>
<div class="container-fluid">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				Track Parcel
			</div>
			<div class="card-body">
				<form action="" id="track-parcel">
					<div class="form-group">
						<label for="ref_no">Enter Reference Number</label>
						<input type="text" name="ref_no" id="ref_no" class="form-control form-control-sm" required>
					</div>
				</form>
			</div>
		</div>
		<div class="row">
			<div class="col-md-8 offset-md-2">
				<div class="timeline" id="parcel_history">
					
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	$('#track-parcel').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url:'ajax.php?action=get_parcel_heistory',
			method:'POST',
			data:$(this).serialize(),
			error:function(err){
				console.log(err)
				alert_toast("An error occurred",'error')
				end_load()
			},
			success:function(resp){
				if(resp){
					resp = JSON.parse(resp)
					if(resp.length <= 0){
						alert_toast("Unknown Reference Number.",'error')
					}else{
						$('#parcel_history').html('')
						Object.keys(resp).map(function(k){
							var tl = $('<div class="timeline-item">')
							var div = $('<div class="timeline-item-content">')
							tl.append(div)
							div.append('<span class="tag" style="background: #28a745">'+resp[k].status+'</span>')
							div.append('<time>'+resp[k].date_created+'</time>')
							div.append('<p>'+resp[k].status+'</p>')
							
							$('#parcel_history').append(tl)
						})
					}
				}
			},
			complete:function(){
				end_load()
			}
		})
	})
</script>

<style>
.timeline {
    margin: 0 auto;
    max-width: 750px;
    padding: 25px;
    display: grid;
    grid-row-gap: 25px;
}

.timeline-item {
    padding-left: 30px;
    position: relative;
}

.timeline-item::before {
    content: '';
    background: #28a745;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    position: absolute;
    left: -10px;
    top: 0;
}

.timeline-item::after {
    content: '';
    background: #28a745;
    width: 2px;
    height: 100%;
    position: absolute;
    left: -1px;
    top: 20px;
}

.timeline-item:last-child::after {
    display: none;
}

.timeline-item-content {
    background: white;
    border-radius: 5px;
    padding: 15px;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
}

.tag {
    color: white;
    font-size: 12px;
    padding: 5px 10px;
    border-radius: 20px;
    margin-bottom: 10px;
    display: inline-block;
}

time {
    color: #777;
    font-size: 12px;
}
</style> 