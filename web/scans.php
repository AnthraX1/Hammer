<?php
require_once('common.php');

//  check login first
if (!already_login()) {
	error_jump();
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">
		<link rel="icon" href="images/favicon.ico">

		<title>Hammer</title>
		<!-- Custom styles for this template -->
		<link href="css/jumbotron.css" rel="stylesheet">
		<!-- Custom styles for this template -->
		<link href="css/dashboard.css" rel="stylesheet">
		<!-- jquery -->
		<!-- <script type="text/javascript" charset="utf8" src="http://code.jquery.com/jquery-1.10.2.min.js"></script> -->
		<script src="http://cdn.bootcss.com/jquery/1.11.1/jquery.min.js"></script>

		<script>
		// 对Date的扩展，将 Date 转化为指定格式的String
		// 月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符， 
		// 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字) 
		// 例子： 
		// (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423 
		// (new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18 
		Date.prototype.Format = function (fmt) { //author: meizz 
				var o = {
						"M+": this.getMonth() + 1, //月份 
						"d+": this.getDate(), //日 
						"h+": this.getHours(), //小时 
						"m+": this.getMinutes(), //分 
						"s+": this.getSeconds(), //秒 
						"q+": Math.floor((this.getMonth() + 3) / 3), //季度 
						"S": this.getMilliseconds() //毫秒 
				};
				if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
				for (var k in o)
				if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
				return fmt;
		}

		$(document).ready(function () {
			//  hide plugin_code div
			$('#code').hide('fast');
			$('#plugins').show('fast');
			//  snippet
			$("pre.python").snippet("python",{style:"vim",menu:false,showNum:true});

			// datatime
			$('#datetimepicker').datetimepicker();

			//  plugin table
			$('#plugins_table').DataTable({
				// "ajax": "./datatable.json",
				"ajax": "./scans_search.php",
				// "paging":   false,
				"lengthChange": false, //改变每页显示数据数量
				"pageLength": 15,
				// "info":     false,
				"filter":   false,
				// "ordering": false,
				"order":    [2, "desc" ],
				"columnDefs": [
					{
						"targets": [ 0 ],
						"visible": false,
						"searchable": false
					},
					{
						"targets": [1],
						"render": function ( data, type, full, meta ) {
								// return "<a class=\"plugin\" href='search.php?name="+encodeURI(data)+"'>"+data+"</a>";
								return "<a class=\"plugin\" href=# id=\""+full[0]+"\">"+data+"</a>";
						}
					},
					{
						"targets":[2],
						 "render": function ( data, type, full, meta ) {
								var d = new Date();
								d.setTime(parseInt(data)*1000);
								// alert(d.toString());
								return d.Format("yyyy-MM-dd hh:mm:ss");
						}
					},
					{
						"targets":[3],
						"render": function ( data, type, full, meta ) {
							var startTime = parseInt(full[2]);
							var endTime = parseInt(data);
							if (!endTime) {
									return '';
							};
							time = endTime - startTime;
							var hour = parseInt(time/3600);
							var min = parseInt(time/60)%60;
							var sec = time%60;
							var ret = '';
							if(hour){
									ret+=hour+'h,'+min+'m,'+sec+'s';
							}
							else{
										if (min) {
												ret+=min+'m,'+sec+'s';
										}
										else{
												ret+=sec+'s';
										}
							}
							return ret;
						},
					},
					{
						"targets":[4],
						 "render": function ( data, type, full, meta ) {
								switch(data){
									case '1':
										return 'info';
									case '2':
										return 'low';
									case '3':
										return 'medium';
									case '4':
										return 'high';
									default:
										return data;
								}
						}
					},
					{
						"targets": [ 6 ],
						"visible": false,
						"searchable": false
					},
				 ]
			});

			//  <a> links in tables
			$('#plugins_table').DataTable().on('draw.dt', function () {
				$('.plugin').bind("click",function() {
					var scanID= $(this).attr("id");
					// alert(scanID);
					$.get("scans_search.php",{scanid: scanID},function(data){
						$('#plugins').hide('slow');
						$('#code').show('slow');
						// $('#plugin_code').html(data);
						$('#scan_results').empty();
						$('#scan_title').empty();
						//
						var json = jQuery.parseJSON(data);
						$.each(json,function(i,n){
							var ipurl = i;
							var html="<div><blockquote><h3>"+ipurl+"</h3></blockquote><ul>"
							$.each(n,function(i2,n2){
								var plugin = n2[0];
								var content = n2[1];
								var level = n2[2];
								html += "<li>";
								var color = "text-muted";
								switch(level){
									case '1':
										color = "text-success";
										break
									case '2':
										color = "text-info";
										break;
									case '3':
										color = "text-warning";
										break;
									case '4':
										color = "text-danger";
										break;
									default:
										color = "text-muted";
								}
								html += "<h4 class=\""+color+"\">"+plugin+"</h4>";
								html += "<ul><li>";
								html+= content;
								html += "</li></ul>";
								html += "</li>";
							})
							html +="</ul></div>"
							$('#scan_results').append(html);
						})
					});
				});
			});

			$('#plugin_goback').click(function(){
				$('#plugins').show('slow');
				$('#code').hide('slow');
			});

			//  search button click
			$('#search').click(function() {
				/* Act on the event */
				var ajax_url = "./scans_search.php?level="+$('#level').val()+"&keyword="+$('#keyword').val();
				$('#plugins_table').DataTable().ajax.url(ajax_url).load();
			});

		});
		</script>
	</head>

	<body>
		<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar">1</span>
						<span class="icon-bar">2</span>
						<span class="icon-bar">3</span>
					</button>
					<a class="navbar-brand" href="#">Hammer</a>
				</div>
				<div class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<li><a href="index.php">Home</a></li>
						<?php if (already_login()) {echo '<li class="active"><a href="scans.php">Scans</a></li>';}?>
						<li><a href="plugins.php">Plugins</a></li>
						<li><a href="documents.php">Documents</a></li>
						<li><a href="about.php">About</a></li>
					</ul>
<?php
if (already_login()) {
echo <<<EOF
					<div class="navbar-form navbar-right" role="form">
						<span class="label label-default">welcome</span>
						<a href="logout.php" class="btn btn-warning">Sign out</a>
					</div>
EOF;
}
else{
echo <<<EOF
					<form class="navbar-form navbar-right" role="form" action="login.php" method="post">
						<div class="form-group">
							<input type="text" placeholder="Name" class="form-control" name="username" id="username">
						</div>
						<div class="form-group">
							<input type="password" placeholder="Password" class="form-control" name="password" id="password">
						</div>
						<button type="submit" class="btn btn-success">Sign in</button>
					</form>
EOF;
}
?>
				</div><!--/.navbar-collapse -->
			</div>
		</div>

		<!-- Main jumbotron for a primary marketing message or call to action -->
		<div class="container">
			<div class="row" id="plugins">
				<div class="container" >
						<h2 class="page-header">Scans</h2>
						<div class="form-inline">
<!-- 							<div class="form-group">
								<input type="text" class="form-control" value="2012-05-15" id="datetimepicker" data-date-format="yyyy-mm-dd">
							</div> -->
							<div class="btn-group">
								<select class="form-control" name="level" id="level">
									<option value="0">All Level</option>
									<option value="1">Informational</option>
									<option value="2">Low</option>
									<option value="3">Medium</option>
									<option value="4">High</option>
								</select>
							</div>
							<div class="form-group">
								<input type="text" class="form-control" id="keyword" placeholder="Keyword" name="keyword">
							</div>
							<button id="search" class="btn btn-default">Search</button>
						</div>
					<div class="table-responsive">
						<table id="plugins_table" class="table table-striped">
								<thead>
										<tr>
												<th>ID</th>
												<th>URL/IP</th>
												<th>StartTime</th>
												<th>CostTime</th>
												<th>Level</th>
												<th>Arguments</th>
												<th>User_Name</th>
										</tr>
								</thead>
						</table>
					</div>
				</div>
			</div>
			<div class="row" id="code" hidden="true">
			<div class="container" >
				<h1>
					<a class="glyphicon glyphicon-circle-arrow-left" id="plugin_goback"></a>&nbsp;
					<small>Scan Results</small>
				</h1>
				<!-- <pre class="python" id="plugin_code"></pre> -->
				<div class="panel" id="scan_title">
				</div>
				<div class="panel" id="scan_results">
				</div>
			</div>
			</div>
		</div>

		<!-- ================================================== -->
		<!-- Placed at the end of the document so the pages load faster -->
		<!-- Bootstrap core JavaScript -->
		<link href="js/bootstrap.min.css" rel="stylesheet">
		<script type="text/javascript" src="js/bootstrap.min.js"></script>
		<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->

		<!-- DataTables -->
		<link rel="stylesheet" type="text/css" href="http://cdn.datatables.net/1.10.2/css/jquery.dataTables.css">
		<script type="text/javascript" charset="utf8" src="http://cdn.datatables.net/1.10.2/js/jquery.dataTables.js"></script>

		<!-- datatimepicker -->
		<link rel="stylesheet" type="text/css" href="css/bootstrap-datetimepicker.min.css">
		<script type="text/javascript" charset="utf8" src="js/bootstrap-datetimepicker.min.js"></script>

		<!-- snippet -->
		<link rel="stylesheet" type="text/css" href="js/jquery.snippet.min.css">
		<script type="text/javascript" charset="utf8" src="js/jquery.snippet.min.js"></script>

	</body>
</html>
