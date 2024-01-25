<?php
namespace Scan;

abstract class ImageElement
{

	public $id, $path, $owner;

	public function __construct (string $path, int $width, int $height) {

		$this->path = $path;
		$this->width = $width;
		$this->height = $height;

		$this->id = $this->prefix . '_' . sha1($path);

	}

}
class Poster extends ImageElement
{

	protected $prefix = 'ps';

}
class Banner extends ImageElement
{

	protected $prefix = 'bn';

}
?>