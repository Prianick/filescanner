<?php
/**
 * Created by PhpStorm.
 * User: asp
 * Date: 09.12.2018
 * Time: 20:41
 */

namespace FileScanner\SourceTypes;


class AbstractSource
{
    public $path;
    public $max_file_size;

    public function __construct($max_file_size)
    {
        $this->max_file_size = $max_file_size;
    }

    /**
     * Путь к файлу
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Проверяет максимальный допустимый размер файла
     * @param $path
     * @throws \Exception
     */
    protected function checkFileSize($path)
    {
        if ($this->max_file_size < filesize($path))
            throw new \Exception('Превышен максимальный допустимый размер файла(' . $this->max_file_size . ')');
    }
}