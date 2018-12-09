<?php

namespace FileScanner\Tests;

use FileScanner\FileScanner;
use function foo\func;

/**
 * Created by PhpStorm.
 * User: asp
 * Date: 09.12.2018
 * Time: 20:58
 */
class FileScannerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers FileScanner::findStrPosition();
     */
    public function testFindStrPosition()
    {
        // чтобы протестировать соединение по ftp, нужен ftp сервер. см config.example.php
        if (!empty($GLOBALS['config']['server_ip'])) {
            $path = "buffer_for_ftp/readme.txt";
            $needle = 'Подключиться к сфинксу';
            $object = new FileScanner($path, FileScanner::ST_FTP);
            $object->setAdditionalAuthData(
                $GLOBALS['config']['server_ip'], $GLOBALS['config']['login'], $GLOBALS['config']['pass']);
            $object->getFileContent();
            $result = $object->findStrPosition($needle);
            $this->assertEquals(['line_number' => 12, 'character_position' => 0], $result, 'Неправильная позиция строки');
        }

        // строка для сравнения
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'test_file.txt';
        $needle = 'lorem ipsum';
        $object = new FileScanner($path, FileScanner::ST_DISK);
        $object->getFileContent();
        $result = $object->findStrPosition($needle);
        $this->assertEquals(['line_number' => 10, 'character_position' => 7], $result, 'Неправильная позиция строки');

        // callback функция для сравнения
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'test_file.txt';
        $needle = md5('lorem ipsum');
        $needle_func = function ($str) use ($needle) {
            if (strpos(md5(trim($str)), $needle) !== false) return true;
            else return false;
        };
        $object = new FileScanner($path, FileScanner::ST_DISK);
        $object->getFileContent();
        $result = $object->findStrPosition($needle_func);
        $this->assertEquals(['line_number' => 11, 'character_position' => true], $result, 'Неправильная позиция строки');

        // проверяем что нельзя обрабатывать недопустимый формат
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'not_text.exe';
        $exception_happened = false;
        try {
            $object = new FileScanner($path, FileScanner::ST_DISK);
        } catch (\Exception $exception) {
            $exception_happened = true;
        }
        $this->assertTrue($exception_happened, 'Был обработан недопустимый формат');

        // проверяем размер файла(protected-свойство)
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'test_file.txt';
        $object = new FileScanner($path, FileScanner::ST_DISK);
        $object_reflector = new \ReflectionClass($object);
        $property = $object_reflector->getProperty('max_file_size');
        $property->setAccessible(true);
        $property->setValue($object,10);
        $exception_happened = false;
        try {
            $object->getFileContent();
        } catch (\Exception $exception) {
            $exception_happened = true;
        }
        $this->assertTrue($exception_happened, 'Необработано превышение максимального размера файла');
    }
}