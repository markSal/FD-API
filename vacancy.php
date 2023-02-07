<?php
	require_once('config.php');
	
	// Set page title and body class
	$page_title = "Schedule Vacancies";
	$body_class = "schedule-vacancies";
	
	// If this is a download request don't output the header
	if(!isset($_GET['download']) || $_GET['download'] != 1){
		
		// Include global site header
		include('head.php');
	}
	
	
	// Setup dates
	date_default_timezone_set('America/Chicago');
	$date_start = date('Y-m-d');
	
	// Get Platoon
	$plt = outputPlatoon(new DateTime($date_start));
	
	// Set date range for schedule
	$start_date = date(DATE_ISO8601, strtotime($date_start . ' 07:00:00'));
	$end_date = date(DATE_ISO8601, strtotime(date('Y-m-d',(strtotime ('+1 day' ,strtotime ($date_start)))) . ' 06:59:59'));

	// Get schedule info
	$schedule = getScheduleInfo($auth, $start_date, $end_date);

	$assignments = $schedule[0]->assignments;
	$schedule_info = array();
	$i = 0;
	
	
	// Loop through assignments on schedule
	foreach($assignments as $assignment){
		$assignment_name = $assignment->name;
		$assignment_hours = $assignment->duration_hours;
		
		// Loop through positions for assignments
		foreach($assignment->positions as $position){
			$assignment_vacancy = $position->is_vacant;
			
			// Check if position is vacant
			if(empty($position->work_shifts[0]->work_type->name) && $assignment_vacancy == 1){
				
				// If vacant
				
				// Store assignment and position information
				$schedule_info[$i]['assignment'] = $assignment_name;
			
				// If no district is listed default to "Staff" for district
				$district = array_key_exists($assignment_name, $company_district_ref) ? $company_district_ref[$assignment_name] : 'Staff';
				$schedule_info[$i]['district'] = $district;
				
				$schedule_info[$i]['rank'] = $position->qualifier->name;
				$schedule_info[$i]['hours'] = $assignment_hours;
				
				// Return blanks for assigned person's information since this is a vacancy 
				$schedule_info[$i]['last_name'] = '';
				$schedule_info[$i]['first_name'] = '';
				$schedule_info[$i]['full_name'] = '';
				$schedule_info[$i]['work_type'] = '';
				$schedule_info[$i]['work_sub_type'] = '';
				$schedule_info[$i]['phone'] = '';
				$i++;
			}else{
				
				// If not vacant skip this record and go to the next
				continue;
			}
		}
	}
	
	// Output CSV file of schedule data if download has been requested
	if($_GET['download'] == 1){
		output_csv($date_start . '-nofd-schedule-vacancies.csv', $schedule_info);
		die();
	}
	
	?>
	<div class="page-title">
		<h1>NOFD Schedule Vacancies<span class="smaller"><?php echo date('l, F j, Y', strtotime($date_start));?> </span></h1>
		
		<div class="actions">
			<a href="<?php echo $current_url; ?>?download=1" title="Download CSV File" id="download"><i class="bi bi-file-earmark-arrow-down"></i>Download</a>
		</div>
		
	</div>
	
	<div class="staffing-dashboard">
		<?php 
		// This will probably get moved to config.php
		$plt = outputPlatoon(new DateTime($date_start)); 
		?>
		
		<div class="plt"><strong>Platoon:</strong> <span class="<?php echo $plt; ?>"><?php echo $plt; ?></span></div>
	</div>
	
	<div id="schedule-info">
		<table class="table table-bordered table-striped member-list">
			<thead>
				<tr>
					<th scope="col" class="name">Assignment</th>
					<th scope="col" class="district">Group</th>
					<th scope="col" class="rank">Rank</th>
					<th scope="col" class="type">Work Type</th>
					<th scope="col" class="type">Subtype</th>
					<th scope="col" class="member">Member</th>
					<th scope="col" class="hours">Hrs</th>
				</tr>
			</thead>
			
			<tbody>
				<?php
				// Display schedule information in table
				foreach($schedule_info as $assignment){
					?>
					<tr>
						<td><?php echo $assignment['assignment']; ?></td>
						<td><?php echo $assignment['district']; ?></td>
						<td><?php echo $assignment['rank']; ?></td>
						<td><?php echo $assignment['work_type']; ?></td>
						<td><?php echo $assignment['work_sub_type']; ?></td>
						<td><?php echo $assignment['full_name']; ?></td>
						<td><?php echo $assignment['hours']; ?></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	</div>
	
	<?php
	include('footer.php');
?>
