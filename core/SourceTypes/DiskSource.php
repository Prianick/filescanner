<?php
/**
 * Created by PhpStorm.
 * User: asp
 * Date: 09.12.2018
 * Time: 20:36
 */

namespace FileScanner\SourceTypes;


class DiskSource extends AbstractSource implements SourceInterface
{
    /** @var  resource */
    protected $file_id;

    /**
     * @throws \Exception
     */
    public function getContent()
    {
        $this->checkFileSize($this->path);
        $this->file_id = fopen($this->path, 'r+');
        if ($this->file_id == false) {
            throw new \Exception('Проблемы при чтении файла. Путь к файлу ' . $this->path);
        }
    }

    /**
     * @return \Generator
     */
    public function getStrData()
    {
        $i = 0;
        while (($buffer = fgets($this->file_id)) !== false) {
            $i++;
            yield ['line_number' => $i, 'content' => $buffer];
        }
    }

    public function closeSource()
    {
        fclose($this->file_id);
    }

}