<?php
/**
 * Created by PhpStorm.
 * User: asp
 * Date: 09.12.2018
 * Time: 20:37
 */

namespace FileScanner\SourceTypes;

interface SourceInterface
{

    public function setPath($path);

    public function getContent();

    public function getStrData();

    public function closeSource();
}