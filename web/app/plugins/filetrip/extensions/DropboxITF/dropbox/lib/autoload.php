<?php
namespace DropboxITF;

// The Dropbox SDK autoloader.  You probably shouldn't be using this.  Instead,
// use a global autoloader, like the Composer autoloader.
//
// But if you really don't want to use a global autoloader, do this:
//
//     require_once "<path-to-here>/Dropbox/autoload.php"

/**
 * @internal
 */
function Filetrip_Dropbox_autoload($name)
{
    // If the name doesn't start with "DropboxITF\", then its not once of our classes.
    if (\substr_compare($name, "DropboxITF\\", 0, 8) !== 0) return;

    // Take the "DropboxITF\" prefix off.
    $stem = \substr($name, strlen('DropboxITF\\'));

    // Convert "\" and "_" to path separators.
    $pathified_stem = \str_replace(array("\\", "_"), '/', $stem);

    $path = __DIR__ . "/" . $pathified_stem . ".php";
    
    if (\is_file($path)) {
        require_once $path;
    }
}

\spl_autoload_register('DropboxITF\Filetrip_Dropbox_autoload');
