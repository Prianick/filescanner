<?php

namespace FileScanner;

use FileScanner\SourceTypes\DiskSource;
use FileScanner\SourceTypes\FTPSource;
use FileScanner\SourceTypes\SourceInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Created by PhpStorm.
 * User: asp
 * Date: 09.12.2018
 * Time: 20:20
 */
class FileScanner
{
    const ST_DISK = 'disk';
    const ST_FTP = 'ftp';

    /** @var  SourceInterface */
    protected $file_source;
    /** @var string */
    protected $source_type;
    /** @var string - путь к файлу */
    protected $path;

    protected $login;
    protected $pass;
    protected $ip;

    protected $max_file_size = '12MB';
    protected $mime_types = "txt,php";

    /**
     * FileScanner constructor.
     * @param string $path
     * @param string $source_type - file|ftp
     */
    public function __construct(string $path, string $source_type)
    {
        $config = Yaml::parseFile(__DIR__ . '/config.yaml');
        $this->max_file_size = $config['max_file_size'];
        $this->mime_types = $config['mime_types'];
        $this->source_type = $source_type;
        $this->path = $path;
        $this->checkMimeType();
    }

    /**
     * Проверяет можно ли обрабатывать файл текущего типа
     * @throws \Exception
     */
    private function checkMimeType()
    {
        $file_extension = explode(".", $this->path);
        $file_extension = $file_extension[count($file_extension) - 1];
        if (mb_strpos($this->mime_types, $file_extension) === false)
            throw new \Exception('Недопустимое расширение файла, можно использовать только ' . $this->mime_types);
    }

    /**
     * Если необходимо установить авторизованное соединение. Например через ftp
     *
     * @param $login
     * @param $pass
     * @param $ip
     */
    public function setAdditionalAuthData($ip, $login, $pass)
    {
        $this->login = $login;
        $this->pass = $pass;
        $this->ip = $ip;
    }

    /**
     * Выбираем класс, для получения данных
     * @throws \Exception
     */
    public function getFileContent()
    {
        switch ($this->source_type) {
            case self::ST_DISK:
                $this->file_source = new DiskSource($this->max_file_size);
                $this->file_source->setPath($this->path);
                break;
            case self::ST_FTP:
                $this->file_source = new FTPSource($this->max_file_size);
                $this->file_source->setPath($this->path);
                $this->file_source->authData($this->ip, $this->login, $this->pass);
                $this->file_source->setConnection();
                break;
            default:
                throw new \Exception('Необходимо указать тип соединения');
        }
        $this->file_source->getContent();
    }

    /**
     * Находит номер строки и номер позиции строки в файле. Если не нашел, тогда возвращает false
     *
     * @param $needle
     * @return array|bool
     */
    public function findStrPosition($needle)
    {
        $result = false;
        /** @var array $str_data */
        foreach ($this->file_source->getStrData() as $str_data) {
            $line_number = $str_data['line_number'];

            if (is_string($needle)) $position = mb_strpos($str_data['content'], $needle);
            else $position = $needle($str_data['content']);

            if ($position === false) continue;
            else {
                $result = ['line_number' => $line_number, 'character_position' => $position];
                break;
            }
        }
        $this->file_source->closeSource();
        return $result;
    }
}