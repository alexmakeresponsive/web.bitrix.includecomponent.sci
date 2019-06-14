<?php

use Amr\Main\Classes\Tools\CUtil;


function GetPagePath($page=false, $get_index_page=null)
{
	if (null === $get_index_page)
	{
		if (defined('BX_DISABLE_INDEX_PAGE'))
			$get_index_page = !BX_DISABLE_INDEX_PAGE;
		else
			$get_index_page = true;
	}

	if($page===false && $_SERVER["REQUEST_URI"]<>"")
		$page = $_SERVER["REQUEST_URI"];
	if($page===false)
		$page = $_SERVER["SCRIPT_NAME"];

	$sPath = $page;

	static $terminate = array("?", "#");
	foreach($terminate as $term)
	{
		if(($found = strpos($sPath, $term)) !== false)
		{
			$sPath = substr($sPath, 0, $found);
		}
	}

	//nginx fix
	$sPath = preg_replace("/%+[0-9a-f]{0,1}$/i", "", $sPath);

	$sPath = urldecode($sPath);

	//Decoding UTF uri
	$sPath = CUtil::ConvertToLangCharset($sPath);

	if(substr($sPath, -1, 1) == "/" && $get_index_page)
	{
		$sPath .= GetDirectoryIndex($sPath);
	}

	$sPath = Rel2Abs("/", $sPath);

	static $aSearch = array("<", ">", "\"", "'", "%", "\r", "\n", "\t", "\\");
	static $aReplace = array("&lt;", "&gt;", "&quot;", "&#039;", "%25", "%0d", "%0a", "%09", "%5C");
	$sPath = str_replace($aSearch, $aReplace, $sPath);

	return $sPath;
}

function GetDirPath($sPath)
{
	if(strlen($sPath))
	{
		$p = strrpos($sPath, "/");
		if($p === false)
			return '/';
		else
			return substr($sPath, 0, $p+1);
	}
	else
	{
		return '/';
	}
}

function GetDirectoryIndex($path, $strDirIndex=false)
{
	return GetDirIndex($path, $strDirIndex);
}

function GetDirIndex($path, $strDirIndex=false)
{
	$doc_root = ($_SERVER["DOCUMENT_ROOT"] <> ''? $_SERVER["DOCUMENT_ROOT"] : $GLOBALS["DOCUMENT_ROOT"]);
	$dir = GetDirPath($path);
	$arrDirIndex = GetDirIndexArray($strDirIndex);
	if(is_array($arrDirIndex) && !empty($arrDirIndex))
	{
		foreach($arrDirIndex as $page_index)
			if(file_exists($doc_root.$dir.$page_index))
				return $page_index;
	}
	return "index.php";
}

function GetDirIndexArray($strDirIndex=false)
{
	static $arDefault = array("index.php", "index.html", "index.htm", "index.phtml", "default.html", "index.php3");

	if($strDirIndex === false && !defined("DIRECTORY_INDEX"))
		return $arDefault;

	if($strDirIndex === false && defined("DIRECTORY_INDEX"))
		$strDirIndex = DIRECTORY_INDEX;

	$arrRes = array();
	$arr = explode(" ", $strDirIndex);
	foreach($arr as $page_index)
	{
		$page_index = trim($page_index);
		if($page_index <> '')
			$arrRes[] = $page_index;
	}
	return $arrRes;
}

function Rel2Abs($curdir, $relpath)
{
	if($relpath == "")
		return false;

	if(substr($relpath, 0, 1) == "/" || preg_match("#^[a-z]:/#i", $relpath))
	{
		$res = $relpath;
	}
	else
	{
		if(substr($curdir, 0, 1) != "/" && !preg_match("#^[a-z]:/#i", $curdir))
			$curdir = "/".$curdir;
		if(substr($curdir, -1) != "/")
			$curdir .= "/";
		$res = $curdir.$relpath;
	}

	if(($p = strpos($res, "\0")) !== false)
		$res = substr($res, 0, $p);

	$res = _normalizePath($res);

	if(substr($res, 0, 1) !== "/" && !preg_match("#^[a-z]:/#i", $res))
		$res = "/".$res;

	$res = rtrim($res, ".\\+ ");

	return $res;
}

function _normalizePath($strPath)
{
	$strResult = '';
	if($strPath <> '')
	{
		if(strncasecmp(PHP_OS, "WIN", 3) == 0)
		{
			//slashes doesn't matter for Windows
			$strPath = str_replace("\\", "/", $strPath);
		}

		$arPath = explode('/', $strPath);
		$nPath = count($arPath);
		$pathStack = array();

		for ($i = 0; $i < $nPath; $i++)
		{
			if ($arPath[$i] === ".")
				continue;
			if (($arPath[$i] === '') && ($i !== ($nPath - 1)) && ($i !== 0))
				continue;

			if ($arPath[$i] === "..")
				array_pop($pathStack);
			else
				array_push($pathStack, $arPath[$i]);
		}

		$strResult = implode("/", $pathStack);
	}
	return $strResult;
}

function getLocalPath($path, $baseFolder = "/amr")
{
	$root = rtrim($_SERVER["DOCUMENT_ROOT"], "\\/");

	static $hasLocalDir = null;
	if($hasLocalDir === null)
	{
		$hasLocalDir = is_dir($root."/local");
	}

	if($hasLocalDir && file_exists($root."/local/".$path))
	{
		return "/local/".$path;
	}
	elseif(file_exists($root.$baseFolder."/".$path))
	{
		return $baseFolder."/".$path;
	}
	return false;
}
