<?php

namespace Amr\Main\Classes\Tools;

class CUtil
{
	protected static $alreadyDecodedRequest = false;

	public static function addslashes($s)
	{
		static $aSearch = array("\\", "\"", "'");
		static $aReplace = array("\\\\", '\\"', "\\'");
		return str_replace($aSearch, $aReplace, $s);
	}

	public static function closetags($html)
	{
		preg_match_all("#<([a-z0-9]+)([^>]*)(?<!/)>#i".BX_UTF_PCRE_MODIFIER, $html, $result);
		$openedtags = $result[1];

		preg_match_all("#</([a-z0-9]+)>#i".BX_UTF_PCRE_MODIFIER, $html, $result);
		$closedtags = $result[1];
		$len_opened = count($openedtags);

		if(count($closedtags) == $len_opened)
			return $html;

		$openedtags = array_reverse($openedtags);

		for($i = 0; $i < $len_opened; $i++)
		{
			if (!in_array($openedtags[$i], $closedtags))
				$html .= '</'.$openedtags[$i].'>';
			else
				unset($closedtags[array_search($openedtags[$i], $closedtags)]);
		}

		return $html;
	}

	public static function JSEscape($s)
	{
		static $aSearch = array("\xe2\x80\xa9", "\\", "'", "\"", "\r\n", "\r", "\n", "\xe2\x80\xa8", "*/", "</");
		static $aReplace = array(" ", "\\\\", "\\'", '\\"', "\n", "\n", "\\n", "\\n", "*\\/", "<\\/");
		$val = str_replace($aSearch, $aReplace, $s);
		return $val;
	}

	public static function JSUrlEscape($s)
	{
		static $aSearch = array("%27", "%5C", "%0A", "%0D", "%", "&#039;", "&#39;", "&#x27;", "&apos;");
		static $aReplace = array("\\'", "\\\\", "\\n", "\\r", "%25", "\\'", "\\'", "\\'", "\\'");
		return str_replace($aSearch, $aReplace, $s);
	}

	public static function PhpToJSObject($arData, $bWS = false, $bSkipTilda = false, $bExtType = false)
	{
		static $use_bx_encode = null;
		if (!isset($use_bx_encode))
			$use_bx_encode = function_exists('bx_js_encode');
		if ($use_bx_encode)
			return bx_js_encode($arData, $bWS, $bSkipTilda, $bExtType);

		switch(gettype($arData))
		{
		case "string":
			if(preg_match("#['\"\\n\\r<\\\\\x80]#", $arData))
				return "'".CUtil::JSEscape($arData)."'";
			else
				return "'".$arData."'";
		case "array":
			$i = -1;
			$j = -1;
			foreach($arData as $j => $temp)
			{
				$i++;
				if ($j !== $i)
					break;
			}

			if($j === $i)
			{
				$res = '[';
				$first = true;
				foreach($arData as $key => $value)
				{
					if($first)
						$first = false;
					else
						$res .= ',';

					switch(gettype($value))
					{
					case "string":
						if(preg_match("#['\"\\n\\r<\\\\\x80]#", $value))
							$res .= "'".CUtil::JSEscape($value)."'";
						else
							$res .= "'".$value."'";
						break;
					case "array":
						$res .= CUtil::PhpToJSObject($value, $bWS, $bSkipTilda, $bExtType);
						break;
					case "boolean":
						if($value === true)
							$res .= 'true';
						else
							$res .= 'false';
						break;
					case "integer":
						if ($bExtType)
							$res .= $value;
						else
							$res .= "'".$value."'";
						break;
					case "double":
						if ($bExtType)
							$res .= is_finite($value) ? $value : "Infinity";
						else
							$res .= "'".$value."'";
						break;
					default:
						if(preg_match("#['\"\\n\\r<\\\\\x80]#", $value))
							$res .= "'".CUtil::JSEscape($value)."'";
						else
							$res .= "'".$value."'";
						break;
					}
				}
				$res .= ']';
				return $res;
			}

			$sWS = ','.($bWS ? "\n" : '');
			$res = ($bWS ? "\n" : '').'{';
			$first = true;
			foreach($arData as $key => $value)
			{
				if ($bSkipTilda && substr($key, 0, 1) == '~')
					continue;

				if($first)
					$first = false;
				else
					$res .= $sWS;

				if(preg_match("#['\"\\n\\r<\\\\\x80]#", $key))
					$res .= "'".CUtil::JSEscape($key)."':";
				else
					$res .= "'".$key."':";

				switch(gettype($value))
				{
				case "string":
					if(preg_match("#['\"\\n\\r<\\\\\x80]#", $value))
						$res .= "'".CUtil::JSEscape($value)."'";
					else
						$res .= "'".$value."'";
					break;
				case "array":
					$res .= CUtil::PhpToJSObject($value, $bWS, $bSkipTilda, $bExtType);
					break;
				case "boolean":
					if($value === true)
						$res .= 'true';
					else
						$res .= 'false';
					break;
				case "integer":
					if ($bExtType)
						$res .= $value;
					else
						$res .= "'".$value."'";
					break;
				case "double":
					if ($bExtType)
						$res .= is_finite($value) ? $value : "Infinity";
					else
						$res .= "'".$value."'";
					break;
				default:
					if(preg_match("#['\"\\n\\r<\\\\\x80]#", $value))
						$res .= "'".CUtil::JSEscape($value)."'";
					else
						$res .= "'".$value."'";
					break;
				}
			}
			$res .= ($bWS ? "\n" : '').'}';
			return $res;
		case "boolean":
			if($arData === true)
				return 'true';
			else
				return 'false';
		case "integer":
			if ($bExtType)
				return $arData;
			else
				return "'".$arData."'";
		case "double":
			if ($bExtType)
				return is_finite($arData) ? $arData : "Infinity";
			else
				return "'".$arData."'";
		default:
			if(preg_match("#['\"\\n\\r<\\\\\x80]#", $arData))
				return "'".CUtil::JSEscape($arData)."'";
			else
				return "'".$arData."'";
		}
	}

	//$data must be in LANG_CHARSET encoding
	public static function JsObjectToPhp($data, $bSkipNative=false)
	{
		$arResult = array();

		$bSkipNative |= !function_exists('json_decode');

		if(!$bSkipNative)
		{
			// php > 5.2.0 + php_json
			/** @global CMain $APPLICATION */
			global $APPLICATION;

			$bUtf = defined("BX_UTF");
			$dataUTF = ($bUtf? $data : $APPLICATION->ConvertCharset($data, LANG_CHARSET, 'UTF-8'));

			// json_decode recognize only UTF strings
			// the name and value must be enclosed in double quotes
			// single quotes are not valid
			$arResult = json_decode($dataUTF, true);

			if($arResult === null)
				$bSkipNative = true;
			elseif(!$bUtf)
				$arResult = $APPLICATION->ConvertCharsetArray($arResult, 'UTF-8', LANG_CHARSET);
		}

		if ($bSkipNative)
		{
			$data = preg_replace('/[\s]*([{}\[\]\"])[\s]*/', '\1', $data);
			$data = trim($data);

			if (substr($data, 0, 1) == '{') // object
			{
				$arResult = array();

				$depth = 0;
				$end_pos = 0;
				$arCommaPos = array();
				$bStringStarted = false;
				$prev_symbol = "";

				$string_delimiter = '';
				for ($i = 1, $len = strlen($data); $i < $len; $i++)
				{
					$cur_symbol = substr($data, $i, 1);
					if ($cur_symbol == '"' || $cur_symbol == "'")
					{
						if (
							$prev_symbol != '\\' && (
								!$string_delimiter || $string_delimiter == $cur_symbol
							)
						)
						{
							if ($bStringStarted = !$bStringStarted)
								$string_delimiter = $cur_symbol;
							else
								$string_delimiter = '';

						}
					}

					elseif ($cur_symbol == '{' || $cur_symbol == '[')
						$depth++;
					elseif ($cur_symbol == ']')
						$depth--;
					elseif ($cur_symbol == '}')
					{
						if ($depth == 0)
						{
							$end_pos = $i;
							break;
						}
						else
						{
							$depth--;
						}
					}
					elseif ($cur_symbol == ',' && $depth == 0 && !$bStringStarted)
					{
						$arCommaPos[] = $i;
					}
					$prev_symbol = $cur_symbol;
				}

				if ($end_pos == 0)
					return false;

				$token = substr($data, 1, $end_pos-1);

				$arTokens = array();
				if (count($arCommaPos) > 0)
				{
					$prev_index = 0;
					foreach ($arCommaPos as $pos)
					{
						$arTokens[] = substr($token, $prev_index, $pos - $prev_index - 1);
						$prev_index = $pos;
					}
					$arTokens[] = substr($token, $prev_index);
				}
				else
				{
					$arTokens[] = $token;
				}

				foreach ($arTokens as $token)
				{
					$arTokenData = explode(":", $token, 2);

					$q = substr($arTokenData[0], 0, 1);
					if ($q == '"' || $q == '"')
						$arTokenData[0] = substr($arTokenData[0], 1, -1);
					$arResult[CUtil::JsObjectToPhp($arTokenData[0], true)] = CUtil::JsObjectToPhp($arTokenData[1], true);
				}
			}
			elseif (substr($data, 0, 1) == '[') // array
			{
				$arResult = array();

				$depth = 0;
				$end_pos = 0;
				$arCommaPos = array();
				$bStringStarted = false;
				$prev_symbol = "";
				$string_delimiter = "";

				for ($i = 1, $len = strlen($data); $i < $len; $i++)
				{
					$cur_symbol = substr($data, $i, 1);
					if ($cur_symbol == '"' || $cur_symbol == "'")
					{
						if (
							$prev_symbol != '\\' && (
								!$string_delimiter || $string_delimiter == $cur_symbol
							)
						)
						{
							if ($bStringStarted = !$bStringStarted)
								$string_delimiter = $cur_symbol;
							else
								$string_delimiter = '';

						}
					}
					elseif ($cur_symbol == '{' || $cur_symbol == '[')
						$depth++;
					elseif ($cur_symbol == '}')
						$depth--;
					elseif ($cur_symbol == ']')
					{
						if ($depth == 0)
						{
							$end_pos = $i;
							break;
						}
						else
						{
							$depth--;
						}
					}
					elseif ($cur_symbol == ',' && $depth == 0 && !$bStringStarted)
					{
						$arCommaPos[] = $i;
					}
					$prev_symbol = $cur_symbol;
				}

				if ($end_pos == 0)
					return false;

				$token = substr($data, 1, $end_pos-1);

				if (count($arCommaPos) > 0)
				{
					$prev_index = 0;
					foreach ($arCommaPos as $pos)
					{
						$arResult[] = CUtil::JsObjectToPhp(substr($token, $prev_index, $pos - $prev_index - 1), true);
						$prev_index = $pos;
					}
					$r = CUtil::JsObjectToPhp(substr($token, $prev_index), true);
					if (isset($r))
						$arResult[] = $r;
				}
				else
				{
					$r = CUtil::JsObjectToPhp($token, true);
					if (isset($r))
						$arResult[] = $r;
				}
			}
			elseif ($data === "")
			{
				return null;
			}
			else // scalar
			{
				$q = substr($data, 0, 1);
				if ($q == '"' || $q == "'")
					$data = substr($data, 1, -1);

				//\u0412\u0430\u0434\u0438\u043c
				if(strpos($data, '\u') !== false)
					$data = preg_replace_callback("/\\\u([0-9A-F]{2})([0-9A-F]{2})/i", array('CUtil', 'DecodeUtf16'), $data);

				$arResult = $data;
			}
		}

		return $arResult;
	}

	public static function DecodeUtf16($ch)
	{
		$res = chr(hexdec($ch[2])).chr(hexdec($ch[1]));
		return \Bitrix\Main\Text\Encoding::convertEncoding($res, "UTF-16", LANG_CHARSET);
	}

	public static function JSPostUnescape()
	{
	    if(!static::$alreadyDecodedRequest)
	    {
		    static::$alreadyDecodedRequest = true;
		    CUtil::decodeURIComponent($_POST);
		    CUtil::decodeURIComponent($_REQUEST);
	    }
	}

	public static function decodeURIComponent(&$item)
	{
		if(defined("BX_UTF"))
		{
			return;
		}
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if(is_array($item))
		{
			array_walk($item, array('CUtil', 'decodeURIComponent'));
		}
		else
		{
			$item = $APPLICATION->ConvertCharset($item, "UTF-8", LANG_CHARSET);
		}
	}

	public static function DetectUTF8($string)
	{
		//http://mail.nl.linux.org/linux-utf8/1999-09/msg00110.html

		if(preg_match_all("/(?:%)([0-9A-F]{2})/i", $string, $match))
		{
			$string = pack("H*", strtr(implode('', $match[1]), 'abcdef', 'ABCDEF'));
		}

		//valid UTF-8 octet sequences
		//0xxxxxxx
		//110xxxxx 10xxxxxx
		//1110xxxx 10xxxxxx 10xxxxxx
		//11110xxx 10xxxxxx 10xxxxxx 10xxxxxx

		$prevBits8and7 = 0;
		$is_utf = 0;
		foreach(unpack("C*", $string) as $byte)
		{
			$hiBits8and7 = $byte & 0xC0;
			if ($hiBits8and7 == 0x80)
			{
				if ($prevBits8and7 == 0xC0)
					$is_utf++;
				elseif (($prevBits8and7 & 0x80) == 0x00)
					$is_utf--;
			}
			elseif ($prevBits8and7 == 0xC0)
			{
					$is_utf--;
			}
			$prevBits8and7 = $hiBits8and7;
		}
		return ($is_utf > 0);
	}

	public static function ConvertToLangCharset($string)
	{
		$bUTF = CUtil::DetectUTF8($string);

		$fromCP = $toCP = false;
		if(defined("BX_UTF") && !$bUTF)
		{
			$fromCP = (defined("BX_DEFAULT_CHARSET")? BX_DEFAULT_CHARSET : "Windows-1251");
			$toCP = "UTF-8";
		}
		elseif(!defined("BX_UTF") && $bUTF)
		{
			$fromCP = "UTF-8";
			$toCP = (defined("LANG_CHARSET")? LANG_CHARSET : (defined("BX_DEFAULT_CHARSET")? BX_DEFAULT_CHARSET : "Windows-1251"));
		}

		if($fromCP !== false)
			$string = \Bitrix\Main\Text\Encoding::convertEncoding($string, $fromCP, $toCP);

		return $string;
	}

	public static function GetAdditionalFileURL($file, $bSkipCheck=false)
	{
		$filePath = $_SERVER['DOCUMENT_ROOT'].$file;
		if($bSkipCheck || file_exists($filePath))
			return $file.'?'.filemtime($filePath).filesize($filePath);
		else
			return $file;
	}

	public static function InitJSCore($arExt = array(), $bReturn = false)
	{

		return CJSCore::Init($arExt, $bReturn);
	}

	public static function GetPopupSize($resize_id, $arDefaults = array())
	{
		if ($resize_id)
		{
			return CUserOptions::GetOption(
				'BX.WindowManager.9.5',
				'size_'.$resize_id,
				array(
					'width' => isset($arDefaults['width'])? $arDefaults['width']: null,
					'height' => isset($arDefaults['height'])? $arDefaults['height']: null,
				)
			);
		}
		else
			return false;
	}

	public static function GetPopupOptions($wnd_id)
	{
		if ($wnd_id)
		{
			return CUserOptions::GetOption(
				'BX.WindowManager.9.5',
				'options_'.$wnd_id
			);
		}
		else
		{
			return false;
		}
	}

	public static function SetPopupOptions($wnd_id, $arOptions)
	{
		if ($wnd_id)
		{
			CUserOptions::SetOption(
				'BX.WindowManager.9.5',
				'options_'.$wnd_id,
				$arOptions
			);
		}
	}

	public static function translit($str, $lang, $params = array())
	{
		static $search = array();

		if(!isset($search[$lang]))
		{
			$mess = IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/js_core_translit.php", $lang, true);
			$trans_from = explode(",", $mess["TRANS_FROM"]);
			$trans_to = explode(",", $mess["TRANS_TO"]);
			foreach($trans_from as $i => $from)
				$search[$lang][$from] = $trans_to[$i];
		}

		$defaultParams = array(
			"max_len" => 100,
			"change_case" => 'L', // 'L' - toLower, 'U' - toUpper, false - do not change
			"replace_space" => '_',
			"replace_other" => '_',
			"delete_repeat_replace" => true,
			"safe_chars" => '',
		);
		foreach($defaultParams as $key => $value)
			if(!array_key_exists($key, $params))
				$params[$key] = $value;

		$len = strlen($str);
		$str_new = '';
		$last_chr_new = '';

		for($i = 0; $i < $len; $i++)
		{
			$chr = substr($str, $i, 1);

			if(preg_match("/[a-zA-Z0-9]/".BX_UTF_PCRE_MODIFIER, $chr) || strpos($params["safe_chars"], $chr)!==false)
			{
				$chr_new = $chr;
			}
			elseif(preg_match("/\\s/".BX_UTF_PCRE_MODIFIER, $chr))
			{
				if (
					!$params["delete_repeat_replace"]
					||
					($i > 0 && $last_chr_new != $params["replace_space"])
				)
					$chr_new = $params["replace_space"];
				else
					$chr_new = '';
			}
			else
			{
				if(array_key_exists($chr, $search[$lang]))
				{
					$chr_new = $search[$lang][$chr];
				}
				else
				{
					if (
						!$params["delete_repeat_replace"]
						||
						($i > 0 && $i != $len-1 && $last_chr_new != $params["replace_other"])
					)
						$chr_new = $params["replace_other"];
					else
						$chr_new = '';
				}
			}

			if(strlen($chr_new))
			{
				if($params["change_case"] == "L" || $params["change_case"] == "l")
					$chr_new = ToLower($chr_new);
				elseif($params["change_case"] == "U" || $params["change_case"] == "u")
					$chr_new = ToUpper($chr_new);

				$str_new .= $chr_new;
				$last_chr_new = $chr_new;
			}

			if (strlen($str_new) >= $params["max_len"])
				break;
		}

		return $str_new;
	}

	public static function BinStrlen($buf)
	{
		return (function_exists('mb_strlen')? mb_strlen($buf, 'latin1') : strlen($buf));
	}

	public static function BinSubstr($buf, $start)
	{
		$length = (func_num_args() > 2? func_get_arg(2) : self::BinStrlen($buf));
		return (function_exists('mb_substr')? mb_substr($buf, $start, $length, 'latin1') : substr($buf, $start, $length));
	}

	public static function BinStrpos($haystack, $needle, $offset = 0)
	{
		if (defined("BX_UTF"))
		{
			if (function_exists('mb_orig_strpos'))
			{
				return mb_orig_strpos($haystack, $needle, $offset);
			}
			return mb_strpos($haystack, $needle, $offset, 'latin1');
		}
		return strpos($haystack, $needle, $offset);
	}

	/**
	* Convert shorthand notation to integer equivalent
	* @param string $str
	* @return int
	*
	*/
	public static function Unformat($str)
	{
		$str = strtolower($str);
		$res = intval($str);
		$suffix = substr($str, -1);
		if($suffix == "k")
			$res *= 1024;
		elseif($suffix == "m")
			$res *= 1048576;
		elseif($suffix == "g")
			$res *= 1048576*1024;
		elseif($suffix == "b")
			$res = self::Unformat(substr($str,0,-1));
		return $res;
	}

	/**
	 * Adjust php pcre.backtrack_limit
	 * @param int $val
	 * @return void
	 *
	 */
	public static function AdjustPcreBacktrackLimit($val)
	{
		$val = intval($val);
		if($val <=0 )
			return;

		$pcreBacktrackLimit = self::Unformat(ini_get("pcre.backtrack_limit"));
		if($pcreBacktrackLimit < $val)
			@ini_set("pcre.backtrack_limit", $val);
	}
}
