<?php

namespace FileScanner\SourceTypes;
/**
 * Created by PhpStorm.
 * User: asp
 * Date: 09.12.2018
 * Time: 21:51
 */

class FTPSource extends AbstractSource implements SourceInterface
{
    /** @var  resource */
    private $conn_id = null;
    private $login;
    private $pass;
    private $server_ip;
    private $tmp_dir = 'tmp';
    /** @var  resource */
    private $file_id;
    private $tmp_file_name;

    /**
     * @param $server_ip
     * @param $login
     * @param $pass
     */
    public function authData($server_ip, $login, $pass)
    {
        $this->server_ip = $server_ip;
        $this->login = $login;
        $this->pass = $pass;
    }

    /**
     * @throws \Exception
     */
    public function setConnection()
    {
        $this->tmp_file_name = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR . $this->tmp_dir . DIRECTORY_SEPARATOR . md5(time()) . '.txt';
        $this->conn_id = ftp_connect($this->server_ip);
        $login_result = ftp_login($this->conn_id, $this->login, $this->pass);
        if ($login_result === false)
            throw new \Exception('Не удалось авторизоваться. Возможно неверный логин пароль');
    }

    /**
     * @throws \Exception
     */
    public function getContent()
    {
        $this->file_id = fopen($this->tmp_file_name, 'w+');
        $res = ftp_get($this->conn_id, $this->tmp_file_name, $this->path, FTP_BINARY);
        if ($res === false)
            throw new \Exception('Ошибка при передаче файла');
        $this->checkFileSize($this->tmp_file_name);
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

    /**
     * Закрывает все соединения и удаляет файл
     */
    public function closeSource()
    {
        ftp_close($this->conn_id);
        fclose($this->file_id);
        unlink($this->tmp_file_name);
    }
}