<?php

namespace Amr\Main\Lib\Data;

interface ICacheEngineStat
{
	public function getReadBytes();
	public function getWrittenBytes();
	public function getCachePath();
}
