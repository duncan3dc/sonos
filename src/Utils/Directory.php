<?php

namespace duncan3dc\Sonos\Utils;

use duncan3dc\Sonos\Interfaces\Utils\DirectoryInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * Represents a shared directory.
 */
final class Directory implements DirectoryInterface
{
    private FilesystemOperator $filesystem;

    /**
     * @var string $share The full path to the share (including the hostname).
     */
    private string $share;

    /**
     * @var string $directory The name of the directory (to be appended to both $filesystem and $share).
     */
    private string $directory;


    /**
     * Create a Directory instance to represent a file share.
     *
     * @param FilesystemOperator|string $filesystem The full path to the share on the local filesystem.
     * @param string $share The full path to the share (including the hostname).
     * @param string $directory The name of the directory (to be appended to both $filesystem and $share).
     */
    public function __construct(FilesystemOperator|string $filesystem, string $share, string $directory)
    {
        if (is_string($filesystem)) {
            $adapter = new LocalFilesystemAdapter($filesystem);
            $filesystem = new Filesystem($adapter);
        }

        $this->filesystem = $filesystem;
        $this->share = rtrim($share, "/");
        $this->directory = trim($directory, "/");
    }


    /**
     * Get the full path to the directory on the file share.
     */
    public function getSharePath(): string
    {
        return "{$this->share}/{$this->directory}";
    }


    /**
     * Check if a file exists.
     */
    public function has(string $file): bool
    {
        return $this->filesystem->fileExists("{$this->directory}/{$file}");
    }


    /**
     * Write data to a file.
     *
     * @param string $file The path to the file
     * @param string $contents The contents to write to the file
     */
    public function write(string $file, string $contents): DirectoryInterface
    {
        $this->filesystem->write("{$this->directory}/{$file}", $contents);

        return $this;
    }
}
