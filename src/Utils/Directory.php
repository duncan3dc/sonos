<?php

namespace duncan3dc\Sonos\Utils;

use duncan3dc\Sonos\Interfaces\Utils\DirectoryInterface;
use League\Flysystem\Filesystem;

/**
 * Represents a shared directory.
 */
final class Directory implements DirectoryInterface
{
    /**
     * @var object $filesystem The full path to the share on the local filesystem.
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
     * @param object|string $filesystem The full path to the share on the local filesystem.
     * @param string $share The full path to the share (including the hostname).
     * @param string $directory The name of the directory (to be appended to both $filesystem and $share).
     */
    public function __construct($filesystem, string $share, string $directory)
    {
        # If a string was passed then convert it to a Filesystem instance
        if (is_string($filesystem)) {
            $filesystem = $this->createFilesystem($filesystem);
        }

        # Ensure we got a Filesystem instance
        if (!$this->isFilesystem($filesystem)) {
            $error = "Invalid filesystem,";
            $error .= " must be an instance of a Flysystem filesystem";
            $error .= " or a string containing a local path";
            throw new \InvalidArgumentException($error);
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
        $path = "{$this->directory}/{$file}";

        if (class_exists(\League\Flysystem\FilesystemOperator::class)
            && $this->filesystem instanceof \League\Flysystem\FilesystemOperator) {
            return $this->filesystem->fileExists($path);
        }

        return $this->filesystem->has($path);
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

    private function createFilesystem(string $path): object
    {
        if (class_exists(\League\Flysystem\Adapter\Local::class)) {
            $adapter = new \League\Flysystem\Adapter\Local($path);

            return new Filesystem($adapter);
        }

        $adapter = new \League\Flysystem\Local\LocalFilesystemAdapter($path);

        return new Filesystem($adapter);
    }

    private function isFilesystem(object $filesystem): bool
    {
        if (class_exists(\League\Flysystem\FilesystemInterface::class)
            && $filesystem instanceof \League\Flysystem\FilesystemInterface) {
            return true;
        }

        return class_exists(\League\Flysystem\FilesystemOperator::class)
            && $filesystem instanceof \League\Flysystem\FilesystemOperator;
    }
}
