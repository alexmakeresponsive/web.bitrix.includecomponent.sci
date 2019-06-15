<?php

namespace Amr\Main\Lib\Data;

interface ICacheEngine
{
	public function isAvailable();
	public function clean($baseDir, $initDir = false, $filename = false);
	public function read(&$allVars, $baseDir, $initDir, $filename, $TTL);
	public function write($allVars, $baseDir, $initDir, $filename, $TTL);
	public function isCacheExpired($path);
}
