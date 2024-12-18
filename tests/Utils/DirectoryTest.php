<?php

namespace duncan3dc\SonosTests\Utils;

use duncan3dc\ObjectIntruder\Intruder;
use duncan3dc\Sonos\Utils\Directory;
use League\Flysystem\FilesystemInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class DirectoryTest extends TestCase
{
    /** @var FilesystemInterface|MockInterface */
    private $filesystem;


    protected function setUp(): void
    {
        $this->filesystem = Mockery::mock(FilesystemInterface::class);
    }


    protected function tearDown(): void
    {
        Mockery::close();
    }


    public function testConstructor1(): void
    {
        $directory = new Directory(sys_get_temp_dir(), "share", "directory");
        $intruder = new Intruder($directory);
        $this->assertInstanceOf(FilesystemInterface::class, $intruder->filesystem);
    }
    public function testConstructor2(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid filesystem");
        new Directory(44, "share", "directory");
    }
    public function testConstructor3(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid filesystem");
        new Directory(new \DateTime(), "share", "directory");
    }


    public function testGetSharePath(): void
    {
        $directory = new Directory($this->filesystem, "share/", "directory/");
        $this->assertSame("share/directory", $directory->getSharePath());
    }


    public function testHas1(): void
    {
        $directory = new Directory($this->filesystem, "share/", "directory/");
        $this->filesystem->shouldReceive("has")->once()->with("directory/file.txt")->andReturn(true);
        $this->assertTrue($directory->has("file.txt"));
    }
    public function testHas2(): void
    {
        $directory = new Directory($this->filesystem, "share/", "directory/");
        $this->filesystem->shouldReceive("has")->once()->with("directory/stuff.txt")->andReturn(false);
        $this->assertFalse($directory->has("stuff.txt"));
    }


    public function testWrite(): void
    {
        $directory = new Directory($this->filesystem, "share/", "directory/");
        $this->filesystem->shouldReceive("write")->once()->with("directory/file.txt", "ok");
        $this->assertSame($directory, $directory->write("file.txt", "ok"));
    }
}
