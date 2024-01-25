<?php

class Dir
{
    //Modified 2021.07.19

    public $ext;
    
    public function scandir (string $dir, bool $directories = false, bool $files = true) {
        //some paths contain trailing '/' and some don't
		//remove and add trailing '/'

		$dir = rtrim($dir,'/') . '/';
		
		$result = [];

		if ($scandir = @array_diff(scandir($dir), ['.','..'])) foreach ($scandir as $file) {
            
            $file = $dir . $file;

            if ($directories) if (is_dir($file)) $result[] = $file;

            if ($files) if (is_file($file)) if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $this->ext)) $result[] = $file;
        
		}

        return $result;
        
    }

    public function scan ($dir) {
        
        $cmd = __DIR__ . '\\scandir.cmd "' . realpath($dir) . '" *.' . implode(' *.', $this->ext);

        $response = `$cmd`;

        $files = [];

        foreach (explode("\n", $response) as $file) {
            
            if ($file = str_replace('\\','/', $file)) $files[] = $file;
            //if ($file = str_replace('\\','/', str_replace(realpath($dir),$dir,trim($file)) )) $files[] = $file;
        
        }

        //print_r($files);

        return $files;

    }
    
    
}
class Finder
{

    /** @var Dir, filemanager */
    private $fm;

    /** @var bool, read sub directories */
    public $recursive;

    /** @var string, path */
    public $path;

    public function __construct (string $path = '', bool $recursive = false) {

        $this->fm = new Dir;

        $this->path = $path;

        $this->recursive = $recursive;

    }

    private function opendir () {

        return $this->recursive ? $this->fm->scan($this->path) : $this->fm->scandir($this->path);

    }

    
    public function images (string $path = '') {

        if ($path) $this->path = $path;

        $this->fm->ext = ['jpg','jpeg','png','webp','gif'];

        return $this->opendir();

    }

    public function videos (string $path = '') {

        if ($path) $this->path = $path;
        
        $this->fm->ext = ['mp4','mkv','m4v','webm','avi','mpg','ts'];

        return $this->opendir();

    }


    public function gFiles (array $ext = [], string $path = '') {

        if ($path) $this->path = $path;
        
        $this->fm->ext = $ext;

        return $this->opendir();

    }

    public function folders (string $path = '') {

        if ($path) $this->path = $path;

        return $this->fm->scandir($this->path, true, false);

    }

}
?>