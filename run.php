<?php

/**
 * Thanks To : Janu Yoga & Aan Ahmad
 * Date Share : 27-03-2019 (First Share)
 * Date Updated V.6 : 30-Agustus-2019
 * Created By : Will Pratama - facebook.com/yaelahhwil
**/

date_default_timezone_set("Asia/Jakarta");
class Marlboro extends modules
{
	public $fileCookies = "cookiesMarlboro.txt";
	protected $cookie;
	protected $modules;

	private function cookiesAkun()
	{
		$file = $this->fileCookies;
		foreach(explode("\n", str_replace("\r", "", file_get_contents($file))) as $a => $data)
		{
			$pecah = explode("|", trim($data));
			return array("decide_session" => $pecah[0], "email" => $pecah[1], "password" => $pecah[2], "device_id" => $pecah[3]);
		}
	}

	public function generateData($cookies = false)
	{
		$url = 'https://www.marlboro.id/auth/login';
		if($cookies == "true")
		{
			$headers = explode("\n", "Host: www.marlboro.id\nSec-Fetch-Mode: navigate\nSec-Fetch-User: ?1\nSec-Fetch-Site: same-origin\nCookie: deviceId=".$this->cookiesAkun()['device_id']."; decide_session=".$this->cookiesAkun()['decide_session']);
		}else{
			$headers = explode("\n", "Host: www.marlboro.id\nSec-Fetch-Mode: navigate\nSec-Fetch-User: ?1\nSec-Fetch-Site: same-origin");
		}

		$generateData = $this->request($url, null, $headers, 'GET');
		$decide_session = $this->fetchCookies($generateData[1])['decide_session'];
		$decide_csrf = $this->getStr($generateData[0], '<input type="hidden" name="decide_csrf" value="', '"', 1, 0);
		@$device_id = $this->fetchCookies($generateData[1])['deviceId'];
		return array(
			trim($decide_session),
			trim($decide_csrf),
			trim($device_id),
		);
	}

	public function login($email, $password)
	{
		if(@file_exists($this->fileCookies) == true)
		{
			@unlink($this->fileCookies);
		}

		$generateData = $this->generateData();

		$url = "https://www.marlboro.id/auth/login";
		$headers = explode("\n","Host: www.marlboro.id\nX-Requested-With: XMLHttpRequest\nSec-Fetch-Mode: cors\nContent-Type: application/x-www-form-urlencoded; charset=UTF-8\nSec-Fetch-Site: same-origin\nCookie: decide_session=".$generateData[0]);
		$post = 'email='.trim($email).'&password='.trim($password).'&ref_uri=/&decide_csrf='.$generateData[1].'&param=&exception_redirect=false';
		$login = $this->request($url, $post, $headers);
		if(strpos($login[0], '"message":"success"'))
		{
	   		$decide_session = $this->fetchCookies($login[1])['decide_session'];
			$device_id = $this->fetchCookies($login[1])['deviceId'];
	    	$this->fwrite($this->fileCookies, trim($decide_session)."|".$email."|".$password."|".trim($device_id));
		}

		return $login;
	}

	private function idVidio()
	{
		$url = 'https://www.marlboro.id';
		$headers = explode("\n", "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\nCookie: deviceId=".$this->cookiesAkun()['device_id']."; decide_session=".$this->cookiesAkun()['decide_session']."\nHost: www.marlboro.id");
		$listIdVidio = $this->request($url, null, $headers, 'GET');

		return $listIdVidio[0];
	}

	public function view($idVidio)
	{
		$generateData = $this->generateData("true");

		$url = "https://www.marlboro.id/article/video-play/".$idVidio;
		$headers = explode("\n", "Host: www.marlboro.id\nUpgrade-Insecure-Requests: 1\nSec-Fetch-Mode: navigate\nSec-Fetch-User: ?1\nSec-Fetch-Site: cross-site\nCookie: deviceId=".$generateData[2]."; decide_session=".$generateData[0]);
		$post = 'decide_csrf='.$generateData[1].'&log_id=false&duration=0.012&total_duration=0&fetch=1&g-recaptcha-response=';
		
		$view = $this->request($url, $post, $headers);
		return $view;
	}

	protected function update($idVidio, $log_id)
	{
		$generateData = $this->generateData("true");

		$url = "https://www.marlboro.id/article/video-play/".$idVidio;
		$headers = explode("\n", "Host: www.marlboro.id\nUpgrade-Insecure-Requests: 1\nSec-Fetch-Mode: navigate\nSec-Fetch-User: ?1\nSec-Fetch-Site: cross-site\nCookie: deviceId=".$generateData[2]."; decide_session=".$generateData[0]);
		$post = 'decide_csrf='.$generateData[1].'&log_id='.$log_id.'&duration=11.052&total_duration=5&fetch=2&g-recaptcha-response';
		
		$update = $this->request($url, $post, $headers);
		return $update[0];
	}

	public function getPoint()
	{
		$url = "https://www.marlboro.id/profile";
		$headers = explode("\n", "Host: www.marlboro.id\nUpgrade-Insecure-Requests: 1\nSec-Fetch-Mode: navigate\nSec-Fetch-User: ?1\nSec-Fetch-Site: cross-site\nCookie: deviceId=".$this->cookiesAkun()['device_id']."; decide_session=".$this->cookiesAkun()['decide_session']);
		$get = $this->request($url, null, $headers, 'GET');
		$points = $this->getStr($get[0], '<img class="icon-point" src="/assets/images/icon-point.svg"/><div class="point">', '<span>', 1, 0);
		return trim($points);
	}

	public function execute_login($email, $password)
	{
		for($o=1;$o<=10;$o++)
		{
			@$pointAwal = $this->getPoint();
			$login = $this->login($email, $password);
			if(strpos($login[0], '"code":200,"message":"success"'))
			{
				if(@$this->getPoint() == $pointAwal)
				{
					print PHP_EOL."Success Login!, Limit Get Point Login... Done : ".$this->getPoint()." Pts";
					return false;
				}else{
					print PHP_EOL."Success Login!, Point Anda : ".$this->getPoint()." Pts";
				}
			}elseif(strpos($login[0], '"message":"Please Accept GoAheadPeople T&C"')){
				if(@file_exists($this->fileCookies) == true)
				{
					@unlink($this->fileCookies);
				}

				print PHP_EOL."Failed Login!, Message : Please Accept GoAheadPeople T&C.. Retry!";
			}elseif(strpos($login[0], '"message":"Email atau password yang lo masukan salah."')){
				if(@file_exists($this->fileCookies) == true)
				{
					@unlink($this->fileCookies);
				}

				print PHP_EOL."Email atau password yang lo masukan salah.";
				return false;
			}elseif(strpos($login[0], '"message":"Action is not allowed"')){
				if(@file_exists($this->fileCookies) == true)
				{
					@unlink($this->fileCookies);
				}

				print PHP_EOL."Action is not allowed";
				return false;
			}elseif(strpos($login[0], 'Akun lo telah dikunci')){
				print PHP_EOL."[RESET PASSWORD] Akun lo telah dikunci karena gagal login berturut-turut.";
				return false;	
			}else{
				if(@file_exists($this->fileCookies) == true)
				{
					@unlink($this->fileCookies);
				}
				print PHP_EOL."Failed Login\n".$login[0];
				return false;
			}
		}
	}

	public function execute_nonton($email)
	{
		if(@file_exists($this->fileCookies) == false){
			return false;
		}

		$ya = rand(1,27);
		for($b = $ya; $b <= ($ya + 10); $b++)
		{	
			if($b % 2 == 0)
			{	
				continue;
			}	

			@$pointAwal = $this->getPoint();
			$idVidio = $this->getStr($this->idVidio(), 'data-ref="https://www.marlboro.id/maze-of-decision/article/', '"', $b, 0);
			if(!empty($idVidio))
			{
				$view = $this->view($idVidio);
				$log_id = $this->getStr($view[0], '"log_id":"', '"', 1, 0);
				if(strpos($view[0], '"message":"Success to store log play video."'))
				{
					$update = $this->update($idVidio, $log_id);
					if(strpos($update, '"finished":true'))
					{
						if(@$this->getPoint() == @$pointAwal)
						{
							if(strpos($update, '"fifteen":true'))
							{
								print PHP_EOL."Limit Get Point Nonton!,  Done : ".$email." | ".$this->getPoint()." Pts";
								return false;
							}else{
								continue;
							}	
						}else{	
							print PHP_EOL."Success Menonton!, Point anda : ".$this->getPoint()." Pts";
						}	
					}else{
						print PHP_EOL."Failed!, Point Anda : ".$this->getPoint().PHP_EOL.$update;
					}
				}elseif(strpos($view[0], '"message":"Action is not allowed"')){
					print PHP_EOL."Failed Menonton Vidio!, Message : Action is not allowed..";
					return false;
				}else{
					print PHP_EOL."Gagal Menonton ".$view[0];
				}	
			}else{
				print PHP_EOL."ID Vidio Tidak Ditemukan..";
				return false;
			}
		}	
	}

	public function bonusPoints()
	{
		if(@file_exists($this->fileCookies) == false){
			return false;
		}

		$generateData = $this->generateData("true");
		@$pointAwal = $this->getPoint();
		$url = 'https://www.marlboro.id/auth/update-profile';
		$headers = explode("\n","Host: www.marlboro.id\nContent-Type: application/x-www-form-urlencoded; charset=UTF-8\nX-Requested-With: XMLHttpRequest\nCookie: decide_session=".$generateData[0].";");
		$post = 'decide_csrf='.$generateData[1].'&email=&password=&phone_number=0&city=99&address=jalan+kangen+nomor+rindu+opit+kapan+bagi+ladang&old_password_chg=&new_password_chg=&confirm_password_chg=&security_question=500001002&security_answer=anjinsg&fav_brand1=500019562&fav_brand2=500019457&interest_raw=Visual&province=6&postalcode=0&interest=Visual&stop_subscribe_email_promo=false';
		$bonusPoints = $this->request($url, $post, $headers);
		if(strpos($bonusPoints[0], '"message":"Update profile Success"'))
		{
			if($pointAwal == $this->getPoint())
			{
				print PHP_EOL."Gagal Mendapatkan Bonus Point.. ".$this->getPoint()." Pts";
			}else{
				print PHP_EOL."Sukses Mendapatkan Bonus Point.. ".$this->getPoint()." Pts";
			}
		}else{
			print PHP_EOL."Gagals Mendapatkan Bonus Point.. ".$this->getPoint()." Pts";
		}	
	}

	public function tryOut()
	{
		if(@file_exists($this->fileCookies) == false){
			return false;
		}

		$pointAwal = $this->getPoint();
		$headers = explode("\n", "Sec-Fetch-Mode: navigate\nHost: www.marlboro.id\nSec-Fetch-User: ?1\nSec-Fetch-Site: same-origin\nCookie: deviceId=".$this->cookiesAkun()['device_id']."; token=rAYvNP0MGas4CyNDkqMyMd8qE33osGZS; decide_session=".$this->cookiesAkun()['decide_session']);
		$url = "https://www.marlboro.id/auth/login?ref_uri=/maze-of-decision/online-maze-result/&result=getter";
		$try = $this->request($url, null, $headers, 'GET');
		if(strpos($try[0], '<title>Login</title>'))
		{
			if($pointAwal == $this->getPoint())
			{
				print PHP_EOL."Gagal Try Out!.. ".$this->getPoint()." Pts";
			}else{
				print PHP_EOL."Sukses Try Out!.. ".$this->getPoint()." Pts";
			}
		}else{
			print PHP_EOL."Gagals Try Out!.. ".$this->getPoint()." Pts\n".$try[0];
		}
	}

}

class modules 
{
	public function request($url, $param, $headers, $request = 'POST') 
	{
		$ch = curl_init();
		$data = array(
				CURLOPT_URL				=> $url,
				CURLOPT_POSTFIELDS		=> $param,
				CURLOPT_HTTPHEADER 		=> $headers,
				CURLOPT_CUSTOMREQUEST 	=> $request,
				CURLOPT_HEADER 			=> true,
				CURLOPT_RETURNTRANSFER	=> true,
				CURLOPT_FOLLOWLOCATION 	=> true,
				CURLOPT_SSL_VERIFYPEER	=> false
			);
		curl_setopt_array($ch, $data);
		$execute = curl_exec($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($execute, 0, $header_size);
		$body = substr($execute, $header_size);
		curl_close($ch);
		return [$body, $header];
	}

	public function getStr($page, $str1, $str2, $line_str2, $line)
	{
		$get = explode($str1, $page);
		$get2 = explode($str2, $get[$line_str2]);
		return $get2[$line];
	}

	public function fetchCookies($source) 
	{
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $source, $matches);
		$cookies = array();
		foreach($matches[1] as $item) 
		{
			parse_str($item, $cookie);
			$cookies = array_merge($cookies, $cookie);
		}

		return $cookies;
	}

	public function fwrite($namafile, $data)
	{
		$fh = fopen($namafile, "a");
		fwrite($fh, $data);
		fclose($fh);  
	}
}	

$modules = new modules();
$marlboro = new marlboro();

print "\n[!] Script Created By: Will Pratama";
print "\n[!] Note: Jangan Run Lebih Dari 1 Terminal, Kecuali File Beda Folder!";
print "\n[!] Note: Diusahakan menggunakan IP Indonesia";
print "\n[!] @Version: V.6\n\n";

print "Bonus 250 Points? (Khusus Akun Baru) y/n : ";
$bonus = trim(fgets(STDIN));

awal:
echo "Input FIle Akun Marlboro (Email|Pass) : ";
@$fileakun = trim(fgets(STDIN));

if(empty(@file_get_contents($fileakun)))
{
	print PHP_EOL."File Akun Tidak Ditemukan.. Silahkan Input Ulang".PHP_EOL;
	goto awal;
}

print PHP_EOL."Total Ada : ".count(explode("\n", str_replace("\r","",@file_get_contents($fileakun))))." Akun, Letsgo..";

while(true)
{
	echo PHP_EOL."Start Date : ".date("Y-m-d H:i:s");
	foreach(explode("\n", str_replace("\r", "", @file_get_contents($fileakun))) as $c => $akon)
	{	
		$pecah = explode("|", trim($akon));
		$email = trim($pecah[0]);
		$password = trim($pecah[1]);
		echo PHP_EOL.PHP_EOL.PHP_EOL."Ekse Akun : ".$email.PHP_EOL;

		print $marlboro->execute_login($email, $password);
		if(@$bonus == "y" or @$bonus == "Y")
		{
			print $marlboro->bonusPoints();
		}
		print $marlboro->tryOut();
		print $marlboro->execute_nonton($email);
	}
	
	echo PHP_EOL.PHP_EOL."Sleep Time : ".date("Y-m-d H:i:s");
	print PHP_EOL."All Done Run!, Sleep 24 Hours";
	print PHP_EOL."Start Besok : ".date('Y-m-d H:i:s', time() + (60 * 60 * 24));
	sleep(86400);
}

?>