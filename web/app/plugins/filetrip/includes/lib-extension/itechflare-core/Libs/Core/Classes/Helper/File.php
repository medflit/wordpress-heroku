<?php
/**
 * Extends WordPress FileSystem Direct
 */
namespace iTechFlare\WP\Plugin\FileTrip\Core\Helper;

/**
 * Class File
 * @package iTechFlare\WP\Plugin\FileTrip\Core\Helper
 * @final
 */
final class File
{
    /**
     * @var File
     */
    private static $instance;

    /**
     * @var \WP_Filesystem_Direct
     */
    private $file_system;

    /**
     * PathHelper constructor.
     */
    public function __construct()
    {
        if (!isset(self::$instance)) {
            if (!class_exists('WP_Filesystem_Direct')) {
                ! class_exists('WP_Filesystem_Base')
                    && require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
                require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
            }
            $this->file_system = new \WP_Filesystem_Direct(null);
            self::$instance = $this;
        }
    }

    /**
     * Instantiate
     *
     * @return File
     */
    public static function getInstance()
    {
        ! isset(self::$instance) && new self();

        return self::$instance;
    }

    /**
     * Cleaning path for window directory separator and trim right the separator '/'
     *
     * @param  mixed $path path
     * @return string       cleaned path
     */
    public function cleanPath($path)
    {
        $instance = self::getInstance();

        if (empty($path)) {
            return $path;
        }
        if (is_array($path)) {
            foreach ($path as $key => $value) {
                $path[$key] = $instance->cleanPath($value);
            }
        } elseif (is_object($path)) {
            foreach (get_object_vars($path) as $key => $value) {
                $path->$key = $instance->cleanPath($value);
            }
        } elseif (is_string($path)) {
            $path = rtrim($instance->cleanSlashed($path), '/');
        }

        return $path;
    }

    /**
     * Clan Invalid Slashed to be only one slashed on separate
     *
     * @param  mixed $path  path to be cleaned
     * @return string
     */
    public function cleanSlashed($path)
    {
        $instance = self::getInstance();

        if (is_array($path)) {
            foreach ($path as $key => $value) {
                $path[$key] = $instance->cleanSlashed($value);
            }
        }
        if (is_object($path)) {
            foreach (get_object_vars($path) as $key => $value) {
                $path->{$key} = $instance->cleanSlashed($value);
            }
        }
        if (is_string($path)) {
            static $path_tmp = array();
            $path_tmp[$path] = isset($path_tmp[$path])
                ? $path_tmp[$path]
                : preg_replace('/(\\\|\/)+/', '/', $path);
            return $path_tmp[$path];
        }
        return $path;
    }

    /* --------------------------------------------------------------------------------*
     |                              Path & File Helpers                                |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Read Directory Nested
     *
     * @param  string  $path            Path directory to be scan
     * @param  integer $directory_depth directory depth of nested to be scanned
     * @param  boolean $hidden          true if want to show hidden content
     * @return array                    path trees
     */
    public function readDirList($path, $directory_depth = 0, $hidden = false)
    {
        $file_data = false;
        if ($this->isDir($path) && function_exists('opendir') && $fp = @opendir($path)) {
            $new_depth  = $directory_depth - 1;
            $path = $this->cleanPath($path) . '/';
            while (false !== ($file = @readdir($fp))) {
                // Remove '.', '..', and hidden files [optional]
                if ($file === '.' || $file === '..' || ($hidden === false && $file[0] === '.')) {
                    continue;
                }

                $this->isDir($path . $file) && $path .= '/';

                if (($directory_depth < 1 || $new_depth > 0) &&  $this->isDir($path . $file)) {
                    $file_data[$file] = $this->readDirList($path . $file, $new_depth, $hidden);
                } else {
                    $file_data[] = $file;
                }
            }

            // close resource
            @closedir($fp);
        }

        return $file_data;
    }

    /**
     * Check if path is file
     *
     * @param  string  $path path to be check
     * @return boolean       true if it is file
     */
    public function isFile($path)
    {
        $instance = self::getInstance();
        return $instance->file_system->is_file($path);
    }

    /**
     * Check if current path is directory
     *
     * @param  string  $path directory
     * @return boolean      true if is directory
     */
    public function isDir($path)
    {
        $instance = self::getInstance();
        return $instance->file_system->is_dir($path);
    }

    /**
     * Check if current path is writable
     *
     * @param  string  $path path to be check
     * @return boolean       true if writable
     */
    public function isWritable($path)
    {
        $instance = self::getInstance();
        return $instance->file_system->is_writable($path);
    }

    /**
     * Check if path is readable
     *
     * @param string $file
     *
     * @return bool
     */
    public function isReadable($file)
    {
        $instance = self::getInstance();
        return $instance->file_system->is_readable($file);
    }

    /**
     * Get File Contents
     *
     * @param string $file
     *
     * @return bool|string
     */
    public function getContents($file)
    {
        $instance = self::getInstance();
        if ($instance->isFile($file) && $instance->isReadable($file)) {
            return $instance->file_system->get_contents($file);
        }

        return false;
    }

    /**
     * Reads entire file into an array
     *
     * @access public
     *
     * @param string $file Path to the file.
     * @return array|bool the file contents in an array or false on failure.
     */
    public function getContentsArray($file)
    {
        $instance = self::getInstance();
        if (function_exists('file') && $instance->isFile($file) && $instance->isReadable($file)) {
            return $instance->file_system->get_contents_array($file);
        }

        return false;
    }

    /**
     * Write a string to a file
     *
     * @access public
     *
     * @param string   $file     Remote path to the file where to write the data.
     * @param string   $contents The data to write.
     * @param int|bool $mode     Optional. The file permissions as octal number, usually 0644.
     *                         Default false.
     * @return bool False upon failure, true otherwise.
     */
    public function putContents($file, $contents, $mode = false)
    {
        $instance = self::getInstance();
        if (is_string($file)) {
            $instance->file_system->put_contents($file, $contents, $mode);
        }

        return false;
    }

    /**
     * Chmod Function to set permission of files
     *
     * @param  string          $path path to be set
     * @param  integer|boolean $mode chmod mode
     * @param  boolean         $recursive recursive
     * @return boolean
     */
    public function chmod($path, $mode = false, $recursive = false)
    {
        if (function_exists('chmod') && $this->isWritable($path)) {
            return $this->file_system->chmod($path, $mode, $recursive);
        }

        return false;
    }

    /**
     * Gets the current working directory
     *
     * @access public
     *
     * @return string|bool the current working directory on success, or false on failure.
     */
    public function cwd()
    {
        $instance = self::getInstance();
        if (function_exists('cwd')) {
            return $instance->file_system->cwd();
        }

        return false;
    }

    /**
     * Change directory
     *
     * @access public
     *
     * @param string $dir The new current directory.
     * @return bool Returns true on success or false on failure.
     */
    public function chdir($dir)
    {
        if (function_exists('chdir') && $this->isDir($dir)) {
            return $this->file_system->chdir($dir);
        }

        return false;
    }

    /**
     * Changes file group
     *
     * @access public
     *
     * @param string $file      Path to the file.
     * @param mixed  $group     A group name or number.
     * @param bool   $recursive Optional. If set True changes file group recursively. Default false.
     * @return bool Returns true on success or false on failure.
     */
    public function chgrp($file, $group, $recursive = false)
    {
        if (function_exists('chgrp') && $this->exists($file)) {
            return $this->file_system->chgrp($file, $group, $recursive);
        }

        return false;
    }

    /**
     * Changes file owner
     *
     * @access public
     *
     * @param string $file      Path to the file.
     * @param mixed  $owner     A user name or number.
     * @param bool   $recursive Optional. If set True changes file owner recursively.
     *                          Default false.
     * @return bool Returns true on success or false on failure.
     */
    public function chown($file, $owner, $recursive = false)
    {
        if (function_exists('chown') && $this->exists($file)) {
            return $this->file_system->chown($file, $owner, $recursive);
        }
        return false;
    }

    /**
     * Gets file owner
     *
     * @access public
     *
     * @param string $file Path to the file.
     * @return string|bool Username of the user or false on error.
     */
    public function owner($file)
    {
        if (function_exists('fileowner') && $this->exists($file)) {
            return $this->file_system->owner($file);
        }

        return false;
    }

    /**
     * Gets file permissions
     *
     * @access public
     *
     * @param string $file Path to the file.
     * @return string|bool Mode of the file (last 3 digits).
     */
    public function getchmod($file)
    {
        if (function_exists('fileperms') && $this->exists($file)) {
            return $this->file_system->getchmod($file);
        }

        return false;
    }

    /**
     * @access public
     *
     * @param string $file
     * @return string|false
     */
    public function group($file)
    {
        if (function_exists('filegroup') && $this->exists($file)) {
            return $this->file_system->group($file);
        }

        return false;
    }

    /**
     * @access public
     *
     * @param string   $source
     * @param string   $destination
     * @param bool     $overwrite
     * @param int|bool $mode  bool false as default
     * @return bool
     */
    public function copy($source, $destination, $overwrite = false, $mode = false)
    {
        if (function_exists('copy') && $this->exists($source)) {
            return $this->file_system->copy($source, $destination, $overwrite, $mode);
        }

        return false;
    }

    /**
     * @access public
     *
     * @param string $source
     * @param string $destination
     * @param bool $overwrite
     * @return bool
     */
    public function move($source, $destination, $overwrite = false)
    {
        if (function_exists('move') && $this->exists($source)) {
            return $this->file_system->move($source, $destination, $overwrite);
        }

        return false;
    }

    /**
     * @access public
     *
     * @param string      $file
     * @param bool        $recursive
     * @param string|bool $type
     * @return bool
     */
    public function delete($file, $recursive = false, $type = false)
    {
        if (is_string($file) && $this->exists($file)) {
            return $this->file_system->move($file, $recursive, $type);
        }

        return false;
    }

    /**
     * @access public
     *
     * @param string $file
     * @return bool|null
     */
    public function exists($file)
    {
        if (is_string($file)) {
            $instance = self::getInstance();
            return $instance->file_system->exists($file);
        }

        return null;
    }

    /**
     * @access public
     *
     * @param string $file
     * @return int|bool
     */
    public function atime($file)
    {
        if ($this->exists($file)) {
            return $this->file_system->atime($file);
        }

        return false;
    }

    /**
     * @access public
     *
     * @param string $file
     * @return int|bool
     */
    public function mtime($file)
    {
        if ($this->exists($file)) {
            return $this->file_system->mtime($file);
        }

        return false;
    }

    /**
     * @access public
     *
     * @param string $file
     * @return int|bool
     */
    public function size($file)
    {
        if ($this->isFile($file)) {
            return $this->file_system->size($file);
        }

        return false;
    }

    /**
     * @access public
     *
     * @param string $file
     * @param int $time
     * @param int $atime
     * @return bool
     */
    public function touch($file, $time = 0, $atime = 0)
    {
        if (is_string($file)) {
            $instance = self::getInstance();
            return $instance->file_system->touch($file, $time, $atime);
        }

        return false;
    }

    /**
     * @access public
     *
     * @param string $path
     * @param mixed  $chmod
     * @param mixed  $chown
     * @param mixed  $chgrp
     * @return bool
     */
    public function mkdir($path, $chmod = false, $chown = false, $chgrp = false)
    {
        if (is_string($path)) {
            $instance = self::getInstance();
            return $instance->file_system->mkdir($path, $chmod, $chown, $chgrp);
        }

        return false;
    }

    /**
     * @access public
     *
     * @param string $path
     * @param bool $recursive
     * @return bool
     */
    public function rmdir($path, $recursive = false)
    {
        return $this->delete($path, $recursive);
    }

    /**
     * @access public
     *
     * @param string $path
     * @param bool $include_hidden
     * @param bool $recursive
     * @return bool|array
     */
    public function dirlist($path, $include_hidden = true, $recursive = false)
    {
        if ($this->exists($path)) {
            return $this->file_system->dirlist($path, $include_hidden, $recursive);
        }

        return false;
    }
}
