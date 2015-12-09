<?php
/**
 * This class checks the strength of a MD5 password by trying to decrypt it.
 *
 *@Author Rochak Chauhan
 */
class PhpMd5Decrypter{
	
	function decrypt($md5){
		$md5=trim($md5);
		if(strlen($md5)!=32){ die("Invalid MD5 Hash");}
		$parameters="hash=$md5";
		$res=$this->postDataViaCurl($parameters);
		
		$returnArray=array();
		$pattern='/Normal Text:(.*)\<br\/\>/Uis';
		preg_match_all($pattern, $res, $returnArray, PREG_SET_ORDER);
		$nt=strip_tags(@$returnArray[0][1]);
		$nt=trim($nt);
		if(empty($nt)){return false;}
		return $nt;
	}
	
	/**
	 *Function to post variables to a remote file using cURL
	 *
	 *@author Rochak Chauhan
	 *@param string $url
	 *
	 *@return string
	 */
	private function postDataViaCurl($parameters){
		$url="http://www.md5decrypter.com/index.php";
		$ch = curl_init() or die("Sorry you need to have cURL extension Enabled");		
		$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
		$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[] = "Cache-Control: max-age=0";
		$header[] = "Connection: keep-alive";
		$header[] = "Keep-Alive: 300";
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$header[] = "Accept-Language: en-us,en;q=0.5";
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.12) Gecko/20080201 Firefox/2.0.0.12");
		curl_setopt($ch, CURLOPT_REFERER, $url);
		curl_setopt($ch, CURLOPT_POST, 1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$postResult = curl_exec($ch);
		if (curl_errno($ch)) {
			return false;
		}
		curl_close($ch);
		return $postResult;
	}
}
?>