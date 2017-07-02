<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Halal Checker</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<style>html,body,h1,h2,h3,h4,h5,h6 {font-family: "Raleway", sans-serif}</style>
</header>
<body class=" w3-blue">
	<!-- Top container -->
	<div class="w3-bar w3-left w3-top w3-white w3-large" style="z-index:5">
 	 <h5 class="w3-margin-left">Halal Checker</h5>
	</div>
	
	<div class="w3-container w3-center w3-margin" style="padding-top:52px">
		<text class="w3-left">Metode Pencarian:</text>
		<select id="search-by" class="w3-white w3-input w3-round w3-margin-bottom" required>
			<option value="nama_produk">Nama Produk</option>
			<option value="nama_produsen">Nama Produsen</option>
			<option value="nomor_sertifikat">Nomor Sertifikat</option>
		</select>
		<input id="query" type="search" class="w3-input w3-round" placeholder="Search by name..."></input>
		<p class="w3-small w3-left">*semua data diambil dari database MUI</p>
		<div class="w3-container w3-blue"><button id="btn" class="w3-button w3-left w3-round w3-green" style="width:120px;"><i class="fa fa-search" ></i></button></div>
		<div id="loading" class="w3-container w3-white w3-margin-top w3-round" style="width:50%;"><img style="width:20%;height;20%;" src="assets/loading.gif"></i></div>
		<div id="result" class="w3-container w3-margin-top w3-round w3-white">
			
		</div>
		<div class="w3-container w3-margin-top">
			<button id="next-page" class="w3-button w3-green w3-right w3-round w3-margin-bottom" style="width:80px"><i class="fa fa-arrow-right"></i></button>
			<span id="page" class="w3-center"></span>
			<button id="prev-page" class="w3-button w3-green w3-left w3-round w3-margin-bottom" style="width:80px"><i class="fa fa-arrow-left"></i></button>
		</div>
	</div>
	<script>
		$(function(){
			try{
			var page = 0;
			var decimalPage;
			var pesan;
			var temp;
			$('#loading').hide();
			$('#result').hide();
			$("#prev-page").hide();
			$("#next-page").hide();
			
			$("#query").keyup(function(event){
    			if(event.keyCode == 13){
    				$('#query').blur();
        			$('#btn').click();
    			}
			});
			
			$('#btn').click(function(){
				page = 0;
				update();
			});

			function update(){
				$('#result').hide();
				var query_value = $('#query').val();
				query_value = query_value.replace(" ","+");
				var search_method = $('#search-by').val();
				$.ajax({
					type:"GET",
					url:"api/index.php",
					dataType:"JSON",
					data:"menu="+search_method+"&query="+query_value+"&page="+page,
					beforeSend: function(){
						$("#prev-page").hide();
						$("#next-page").hide();
						$('#page').hide();
						$('#loading').show();
					},
					complete: function(){// Once Request is complete, Remove the Loader
   					 $("#loading").hide();
   				 },
					success: function(data){
						$("#prev-page").hide();
						$("#next-page").hide();
						$('#page').hide();
						if(data.status != "error"){
						//var res = JSON.stringify(data);
						$('#result').show();
						if(data.this_page !== 0){
							$("#prev-page").show();
						}else{
							$("#prev-page").hide();
						}
						if(!data.next_page){
							$("#next-page").hide();
						}else{
							$("#next-page").show();
						}
						temp = data.this_page+10;
						decimalPage = temp.toString().replace("0", "");
						$('#page').html(decimalPage);
						$('#page').show();
						$("#loading").hide();
						show(data);
						}else{
							$("#prev-page").hide();
							$("#next-page").hide();
							$('#page').hide();
							$('#result').show();
							pesan = (data.pesan == undefined)? "": "<p>"+data.pesan+"</p>";
							$('#result').html("<p>Tidak dapat menemukan produk</p>"+pesan);
						}
					},
					error: function (xhr, ajaxOptions, thrownError){// Error Logger
   					 $('#result').text(xhr+"<br>"+ajaxOptions+"<br>"+thrownError);
   					 $("#loading").hide();
   				 }   
				});
			}
			
			function show(data){
				var prev_page = "";
				var htmlString = "";
				var scriptString = "";
				var length = Object.keys(data.data).length;
				for(var i = 0;i < length;i++){
					htmlString += "<div class=\" w3-padding w3-blue w3-round w3-margin-bottom w3-panel\" style=\"height:60%;\"><table class=\"w3-table w3-bordered\"><tr><td>Nama Produk: </td></tr><tr class=\"w3-white w3-round\"><td>"+data.data[i].nama_produk+"</td></tr>"
					htmlString += "<tr><td>Nomor Sertifikat: </td></tr><tr class=\"w3-white w3-round\"><td>"+data.data[i].nomor_sertifikat+"</td></tr>";
					htmlString += "<tr><td>Nama Produsen: </td></tr><tr class=\"w3-white w3-round\"><td>"+data.data[i].nama_produsen+"</td></tr>";
					htmlString += "<tr><td>Berlaku Hingga: </td></tr><tr class=\"w3-white w3-round\"><td>"+data.data[i].berlaku_hingga+"</td></tr>";
					htmlString += "</table></div>";
				}
				$('#result').html(htmlString);
			}
			
			$("#next-page").click(function(){
				page += 10;
				$('#result').html("");
				update();
			});

			$("#prev-page").click(function(){
				page -= 10;
				$('#result').html("");
				update();
			});
			
			}catch(err){
				$('#result').html(err);
			}
		});
		
	</script>
</body>
</html>