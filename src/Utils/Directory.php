<?php

namespace duncan3dc\Sonos\Utils;

use duncan3dc\Sonos\Interfaces\Utils\DirectoryInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;

/**
 * Represents a shared directory.
 */
final class Directory implements DirectoryInterface
{
    /**
     * @var FilesystemInterface $filesystem The full path to the share on the local filesystem.
     */
    private $filesystem;

    /**
     * @var string $share The full path to the share (including the hostname).
     */
    private $share;

    /**
     * @var string $directory The name of the directory (to be appended to both $filesystem and $share).
     */
    private $directory;


    /**
     * Create a Directory instance to represent a file share.
     *
     * @param FilesystemInterface|string $filesystem A Filesystem instance or the full path to the share on the local filesystem.
     * @param string $share The full path to the share (including the hostname).
     * @param string $directory The name of the directory (to be appended to both $filesystem and $share).
     */
    public function __construct($filesystem, string $share, string $directory)
    {
        # If a string was passed then convert it to a Filesystem instance
        if (is_string($filesystem)) {
            $adapter = new Local($filesystem);
            $filesystem = new Filesystem($adapter);
        }

        # Ensure we got a Filesystem instance
        if (!$filesystem instanceof FilesystemInterface) {
            throw new \InvalidArgumentException("Invalid filesystem, must be an instance of " . FilesystemInterface::class . " or a string containing a local path");
        }

        $this->filesystem = $filesystem;
        $this->share = rtrim($share, "/");
        $this->directory = trim($directory, "/");
    }


    /**
     * Get the full path to the directory on the file share.
     *
     * @return string
     */
    public function getSharePath(): string
    {
        return "{$this->share}/{$this->directory}";
    }


    /**
     * Check if a file exists.
     *
     * @param string $file The path to the file.
     *
     * @return bool
     */
    public function has(string $file): bool
    {
        return $this->filesystem->has("{$this->directory}/{$file}");
    }


    /**
     * Write data to a file.
     *
     * @param string $file The path to the file
     * @param string $contents The contents to write to the file
     *
     * @return $this
     */
    public function write(string $file, string $contents): DirectoryInterface
    {
        $this->filesystem->write("{$this->directory}/{$file}", $contents);

        return $this;
    }
}
