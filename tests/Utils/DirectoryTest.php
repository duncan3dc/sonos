<?php

namespace duncan3dc\SonosTests\Utils;

use duncan3dc\ObjectIntruder\Intruder;
use duncan3dc\Sonos\Utils\Directory;
use League\Flysystem\FilesystemOperator;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class DirectoryTest extends TestCase
{
    /** @var FilesystemOperator|MockInterface */
    private $filesystem;


    protected function setUp(): void
    {
        $this->filesystem = Mockery::mock(FilesystemOperator::class);
    }


    protected function tearDown(): void
    {
        Mockery::close();
    }


    public function testConstructor1(): void
    {
        $directory = new Directory(sys_get_temp_dir(), "share", "directory");
        $intruder = new Intruder($directory);
        $this->assertInstanceOf(FilesystemOperator::class, $intruder->filesystem);
    }


    public function testGetSharePath(): void
    {
        $directory = new Directory($this->filesystem, "share/", "directory/");
        $this->assertSame("share/directory", $directory->getSharePath());
    }


    public function testHas1(): void
    {
        $directory = new Directory($this->filesystem, "share/", "directory/");
        $this->filesystem->shouldReceive("fileExists")->once()->with("directory/file.txt")->andReturn(true);
        $this->assertTrue($directory->has("file.txt"));
    }
    public function testHas2(): void
    {
        $directory = new Directory($this->filesystem, "share/", "directory/");
        $this->filesystem->shouldReceive("fileExists")->once()->with("directory/stuff.txt")->andReturn(false);
        $this->assertFalse($directory->has("stuff.txt"));
    }


    public function testWrite(): void
    {
        $directory = new Directory($this->filesystem, "share/", "directory/");
        $this->filesystem->shouldReceive("write")->once()->with("directory/file.txt", "ok");
        $this->assertSame($directory, $directory->write("file.txt", "ok"));
    }
}
