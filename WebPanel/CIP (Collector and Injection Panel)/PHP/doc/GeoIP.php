<?php 

	class GeoIP{
	
		function getIP(){
			return $_SERVER['REMOTE_ADDR'];
		}
	
		function toUpperText($str){
			$table = array(
			"�"=>"�", "�"=>"�", "�"=>"�", "�"=>"�",
			"�"=>"�", "�"=>"�", "�"=>"�", "�"=>"�",
			"�"=>"�", "�"=>"�", "�"=>"�", "�"=>"�", 
			"�"=>"�", "�"=>"�", "�"=>"�", "�"=>"�", 
			"�"=>"�", "�"=>"�", "�"=>"�", "�"=>"�",
			"�"=>"�", "�"=>"�", "�"=>"�", "�"=>"�", 
			"�"=>"�", "�"=>"�", "�"=>"�", "�"=>"�",
			"�"=>"�", "�"=>"�", "�"=>"�", "�"=>"�", 
			"�"=>"�");
			$values = array_values($table);
			$keys = array_keys($table);
			
			for($i=0; $i<count($values); $i++){
				$str = str_replace($values[$i], $keys[$i], $str);
			}
			
			return strtoupper($str);
		}
	
		function toPlainText($str){
			$table = array(
			"�"=>"&Agrave;", "�"=>"&agrave;", "�"=>"&Aacute;", 
			"�"=>"&aacute;", "�"=>"&Acirc;", "�"=>"&acirc;", 
			"�"=>"&Atilde;", "�"=>"&atilde;", "�"=>"&Auml;", 
			"�"=>"&auml;", "�"=>"&Aring;", "�"=>"&aring;", 
			"�"=>"&AElig;", "�"=>"&aelig;", "�"=>"&Ccedil;", 
			"�"=>"&ccedil;", "�"=>"&ETH;", "�"=>"&eth;", 
			"�"=>"&Egrave;", "�"=>"&egrave;", "�"=>"&Eacute;", 
			"�"=>"&eacute;", "�"=>"&Ecirc;", "�"=>"&ecirc;", 
			"�"=>"&Euml;", "�"=>"&euml;", "�"=>"&Igrave;", 
			"�"=>"&igrave;", "�"=>"&Iacute;", "�"=>"&iacute;", 
			"�"=>"&Icirc;", "�"=>"&icirc;", "�"=>"&Iuml;", 
			"�"=>"&iuml;", "�"=>"&Ntilde;", "�"=>"&ntilde;", 
			"�"=>"&Ograve;", "�"=>"&ograve;", "�"=>"&Oacute;", 
			"�"=>"&oacute;", "�"=>"&Ocirc;", "�"=>"&ocirc;", 
			"�"=>"&Otilde;", "�"=>"&otilde;", "�"=>"&Ouml;", 
			"�"=>"&ouml;", "�"=>"&Oslash;", "�"=>"&oslash;", 
			"�"=>"&OElig;", "�"=>"&oelig;", "�"=>"&szlig;", 
			"�"=>"&THORN;", "�"=>"&thorn;", "�"=>"&Ugrave;", 
			"�"=>"&ugrave;", "�"=>"&Uacute;", "�"=>"&uacute;", 
			"�"=>"&Ucirc;", "�"=>"&ucirc;", "�"=>"&Uuml;", 
			"�"=>"&uuml;", "�"=>"&Yacute;", "�"=>"&yacute;", 
			"�"=>"&Yuml;", "�"=>"&yuml;");
			$values = array_values($table);
			$keys = array_keys($table);
			
			$str = str_replace("&amp;", "&", $str);
			
			for($i=0; $i<count($values); $i++){
				$str = str_replace($values[$i], $keys[$i], $str);
			}
			return $str;
		}
	
		function getTag($source, $tag, $decode){
			$rsp = "";
			$size = strlen("<".$tag.">");
			$init = strrpos($source,"<".$tag.">");
			$end = strrpos($source,"</".$tag.">");
			if(!is_bool($init) && !is_bool($end)){
				$add = $end - ($init + $size);
				$rsp = trim(@substr($source, $init + $size, $add));
				if($decode) {
					$rsp = $this->toPlainText($rsp);//strtoupper
					$rsp = $this->toUpperText($rsp);
				}
			}
			return $rsp;
		}
	
		function getLocation($ip){
			
			$array = array();
			
			$xml = false;
			
			if(!$xml){
				$url = "http://www.geoplugin.net/php.gp?ip=" . $ip;
                                $contents = @file_get_contents($url);
				$contents = @unserialize($contents); //var_export(
				
				$array["city"] = utf8_decode($contents["geoplugin_city"]); //$this->toPlainText
				$array["region"] = utf8_decode($contents["geoplugin_regionName"]);//geoplugin_region
				$array["country"] = utf8_decode($contents["geoplugin_countryName"]);
				$array["latitude"] = $contents["geoplugin_latitude"];
				$array["longitude"] = $contents["geoplugin_longitude"];
				
				//print_r ($array);
				
				return $array;
			}
			else{
				$url = "http://www.geoplugin.net/xml.gp?ip=" . $ip;			
				$s = @file_get_contents($url);			
				if(!strpos($s, "<geoPlugin>")) return $array;			
				$s = str_replace(base64_decode(base64_encode("<?xml version=\"1.0\" encoding=\"UTF-8\"?>")), '', $s);
				
				$s = str_replace("<geoPlugin>", '', $s);
				$s = str_replace("</geoPlugin>", '', $s);
				$s = str_replace("\r\n\r\n", '', $s);
				$s = str_replace("\t", '', $s);
				$s = trim($s);
				
				$decode = true;
				
				$array["city"] = $this->getTag($s, "geoplugin_city", $decode);
				$array["region"] = $this->getTag($s, "geoplugin_regionName", $decode);
				$array["country"] = $this->getTag($s, "geoplugin_countryName", $decode);
				$array["latitude"] = $this->getTag($s, "geoplugin_latitude", $decode);
				$array["longitude"] = $this->getTag($s, "geoplugin_longitude", $decode);
				
				//print_r ($array);
				//var myObject = eval('(' + myJSONtext + ')');
				
				return $array;
			}
			
			return null;
		}
	
	}

?>