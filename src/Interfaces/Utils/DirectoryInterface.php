<?php

namespace duncan3dc\Sonos\Interfaces\Utils;

/**
 * Represents a shared directory.
 */
interface DirectoryInterface
{
    /**
     * Get the full path to the directory on the file share.
     *
     * @return string
     */
    public function getSharePath(): string;


    /**
     * Check if a file exists.
     *
     * @param string $file The path to the file.
     *
     * @return bool
     */
    public function has(string $file): bool;


    /**
     * Write data to a file.
     *
     * @param string $file The path to the file
     * @param string $contents The contents to write to the file
     *
     * @return $this
     */
    public function write(string $file, string $contents): DirectoryInterface;
}
