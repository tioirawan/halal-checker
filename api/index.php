<?php

			/*###########################
			MENU (string) ada 5 yaitu: 
			1. nama_produk
			2. nama_produsen
			3. nomor_sertifikat
			4. a.kategori_id
			5. semua_kategori (tanpa QUERY dan PAGE)
			##############################
			
			
			##########################################
			QUERY (string) sesuaikan dengan nama menu.
			##########################################
			

			PAGE (integer) default = 0. 
			no halaman selanjutnya kelipatan 10. 
			0, 10, 20 dst..
			
			
			Example http://anu.kom/index.php?menu=nama_produk&query=yakult&page=0
			##########################################*/

// Menyembunyikan pesan error karena proses DOM yang tidak sempurna 
error_reporting(0);

if(!empty($_GET['menu'])){
	
	if($_GET['menu'] == "semua_kategori"){

		// Menampilkan semua kategori
		echo halal_kategori_mui();

	}else if($_GET['menu'] == "nama_produk" || $_GET['menu'] == "nama_produsen" || $_GET['menu'] == "nomor_sertifikat" || $_GET['menu'] == "a.kategori_id"){

		if(!empty($_GET['query'])){
			
			$page = (empty($_GET['page']) ? 0 : $_GET['page']);

			// Search produk..
			// mui('MENU', 'QUERY', PAGE);
			echo halal_produk_mui($_GET['menu'], $_GET['query'], $page);
			
		}else{
			echo jsonin(array("status" => "error", "pesan" => "query kosong"));
		}
		
	}else {
		echo jsonin(array("status" => "error", "pesan" => "nama menu salah"));
	}

}else{
	echo jsonin(array("status" => "error", "pesan" => "menu kosong"));
}

// Function scrapping website halalmui
function halal_produk_mui($menu, $query, $page) {
	
	//########### Menggunakan fungsi file_get_contents() #########################################################
    $html = file_get_contents('http://www.halalmui.org/mui14/index.php/main/produk_halal_detail/'.$menu.'/'.rawurlencode($query).'/Y/'.$page);
	//###############################################################################################
	
	
	/*######### Atau agan mau pake fungsi cURL ######################################################
	$chp = curl_init();
    curl_setopt($chp, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($chp, CURLOPT_URL, 'http://www.halalmui.org/mui14/index.php/main/produk_halal_detail/'.$menu.'/'.rawurlencode($query).'/Y/'.$page);
    $html = curl_exec($chp);
    curl_close($chp);
	################################################################################################*/
	
		
	//####################################### START DOM ##############################################
    $dom = new DOMDocument;
    $dom->loadHTML($html);
	
	// Initial ARRAY yang nanti akan dijadikan JSON
	$rows = array();	
		
	// Memasukan nama menu kedalam array
	$rows["menu"] = $menu;	
		
	// Memasukan data query kedalam array
	$rows["query"] = $query;
	
	// Proses untuk mengecek data yang akan di scrapping ada atau tidak ada
	// Kalo ada JSON status success, jika tidak ada JSON status error
	if(!preg_match("/no result found/", $html)){
		
		// Ternyata data ada makan kita masukan status => success ke dalam array
		$rows["status"] = "success";
		
		// Proses untuk mendapatkan data produk halal dengan cara memecah tag tr
		$i = 0;
		foreach ($dom->getElementsByTagName('tr') as $tr) {
			// Proses untuk tidak mengambil data yang ada di tag tr paling atas karena hanya judul
			if($i != 0){
				// Memecah tag td
				foreach ($tr->getElementsByTagName('td') as $td) {
					$cells2 = array();
					// Memecah tag span yaitu untuk mendapatkan data yang kita butuhkan
					foreach ($td->getElementsByTagName('span') as $span) {
						$cells2[] = $span->nodeValue;
					}
					$nomor_sertifikat = explode(" : ", $cells2[1]);
					$produsen = explode(" : ", $cells2[2]);
					// Memasukan data yang kita butuhkan kedalam ARRAY satu persatu
					$cells = array(
								   'nama_produk' => $cells2[0],
								   'nomor_sertifikat' => $nomor_sertifikat[1],
								   'nama_produsen' => str_replace("  Berlaku hingga", "", $produsen[1]),
								   'berlaku_hingga' => $produsen[2]
								);
				}
				// Memasukan semua data yang kita butuhkan kedalam ARRAY data
				$rows["data"][] = $cells;
			}
			$i++;
		}		
		//######################################## END DOM ################################################
		
		// Memasukan data pagging kedalam array
		$rows["this_page"] = intval($page);
		// Memasukan data pagging sebelumnya yitu dengan cara dikurang 10 (kelipatan sepuluh)
		$rows["prev_page"] = ($page == 0 ? null : $page - 10);
		// Memasukan data pagging selanjutnya yitu dengan cara ditambah 10 (kelipatan sepuluh)
		$next_page = (count($rows["data"]) < 10 ? null : $page + 10);
		// Memasukan data next_page kedalam ARRAY
		$rows["next_page"] = $next_page;
		
	}else{
		
		// Ternyata data tidak ada, makan kita masukan status => error ke dalam array
		// Untuk memberi tahu, sehingga data produk tidak usah di tampilkan
		$rows["status"] = "error";
	}
	
	// ARRAY to JSON
	return jsonin($rows);

}

function halal_kategori_mui(){
	//########### Menggunakan fungsi file_get_contents() #########################################################
    $html = file_get_contents('http://www.halalmui.org/mui14/index.php/main/produk_halal_masuk');
	//###############################################################################################
	
	
	/*######### Atau agan mau pake fungsi cURL ######################################################
	$chp = curl_init();
    curl_setopt($chp, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($chp, CURLOPT_URL, 'http://www.halalmui.org/mui14/index.php/main/produk_halal_masuk');
    $html = curl_exec($chp);
    curl_close($chp);
	################################################################################################*/
	
	
	//####################################### START DOM ##############################################
    $dom = new DOMDocument;
    $dom->loadHTML($html);	
	
	// Initial ARRAY yang nanti akan dijadikan JSON
	$rows = array();	
	$i = 0;
	foreach ($dom->getElementsByTagName('a') as $href) {
		if($i != 0 && $i != 1){
			$kategori_id = $i - 1;
			$enol = ($kategori_id < 10 ? 0 : "");
			$cells = array(
						   'nama' => $href->nodeValue,
						   'kategori_id' => $enol.$kategori_id
						);
			$rows["semua_kategori"][] = $cells;
		}
		$i++;
	}
	return jsonin($rows);
	
}

// Function untuk merubah data ARRAY menjadi JSON
function jsonin($array){
	header('Content-Type: application/json');
	return json_encode($array, JSON_PRETTY_PRINT);
}
	
?> 																		
