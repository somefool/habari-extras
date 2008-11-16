<?php

/**
 * ClickHeat : Maps generation class
 */

class Heatmap
{
	// integer $memory Memory limit
	public $memory = 8388608;

	// integer $step Pixels grouping
	public $step = 5;

	// integer $dot Heat dots size
	public $dot = 19;

	// boolean $heatmap Show as heatmap
	public $heatmap = true;

	// boolean $palette Correction for palette (in case of red squares)
	public $palette = false;

	// boolean $rainbow Show rainbow (click count in top-left of image)
	public $rainbow = true;

	// boolean Show copyleft
	public $copyleft = true;

	// string $file Image filename (including %d)
	public $file;

	// string $path Image path
	public $path;

	// string $cache Cache path
	public $cache;

	// string $error Error
	public $error;

	// integer $width Image width
	protected $width;

	// integer $height Image height
	protected $height;

	// integer $maxClicks Maximum clicks (on 1 pixel)
	protected $maxClicks;

	// integer $maxY Maximum height (lowest point)
	protected $maxY;

	// resource $image Image resource
	protected $image;

	// integer $startStep
	protected $startStep;

	// array $__colors Gradient levels (from 0 to 127)
	private $__colors = array(50, 70, 90, 110, 120);

	// integer $color_low Lower RGB level of color
	private $color_low = 0;

	// integer $color_high Higher RGB level of color
	private $color_high = 255;

	// integer $color_grey Grey level (color of no-click)
	private $color_grey = 240;


	/**
	 * Generate the image
	 */
	public function generate($width, $height = 0)
	{
		// First check paths
		$this->path = rtrim($this->path, '/').'/';
		$this->cache = rtrim($this->cache, '/').'/';
		$this->file = str_replace('/', '', $this->file);

		if (!is_dir($this->path) || $this->path === '/')
			return $this->raiseError('path = "' . $this->path . '" is not a directory or is "/"');

		if (!is_dir($this->cache) || $this->cache === '/')
			return $this->raiseError('cache = "' . $this->cache . '" is not a directory or is "/"');

		if (strpos($this->file, '%d') === false)
			return $this->raiseError('file = "' . $this->file . '" doesn\'t include a \'%d\' for image number');

		$files = array('filenames' => array(), 'absolutes' => array()); // Generated files list
		$this->startStep = (int) floor(($this->step - 1) / 2);
		$nbOfImages = 1; // Will be modified after the first image is created
		$this->maxClicks = 1; // Must not be zero for divisions
		$this->maxY = 0;

		/**
		 * Memory consumption :
		 * imagecreate	: about 200,000 + 5 * $width * $height bytes
		 * dots		: about 6,000 + 360 * DOT_WIDTH bytes each (100 dots)
		 * imagepng	: about 4 * $width * $height bytes
		 * So a rough idea of the memory is 10 * $width * $height + 500,000 (2 images) + 100 * (DOT_WIDTH * 360 + 6000)
		**/
		$this->width = (int) abs($width);
		if ($this->width === 0)
			return $this->raiseError(_t("Width can't be 0"));

		$height = (int) abs($height);
		if ($height === 0) {
			// Calculating height from memory consumption, and add a 100% security margin : 10 => 20
			$this->height = floor(($this->memory - 500000 - 100 * ($this->dot * 360 + 6000)) / (20 * $width));

			// Limit height to 1000px max, with a modulo of 10
			$this->height = (int) max(100, min(1000, $this->height - $this->height % 10));

		} else {
			// Force height
			$this->height = $height;
		}

		// Startup tasks
		if ($this->startDrawing() === false)
			return false;

		$files['width'] = $this->width;
		$files['height'] = $this->height;

		for ($image = 0; $image < $nbOfImages; $image++)
		{
			// Image creation
			$this->image = imagecreatetruecolor($this->width, $this->height);
			if ( ! $this->heatmap ) {
				$grey = imagecolorallocate($this->image, $this->color_grey, $this->color_grey, $this->color_grey);
				imagefill($this->image, 0, 0, $grey);
			} else {
				// Image is filled in the color "0", which means 0 click
				imagefill($this->image, 0, 0, 0);
			}

			// Draw next pixels for this image
			if ($this->drawPixels($image) === false)
				return false;

			if ($image === 0) {
				if ($this->maxY === 0)
					if (defined('LANG_ERROR_DATA') === true)
						return $this->raiseError(_t("No logs for the selected period (first think removing filters: browser, screensize)."));
					else
						$this->maxY = 1;

				$nbOfImages = (int) ceil($this->maxY / $this->height);
				$files['count'] = $nbOfImages;
			}

			if ($this->heatmap) {
				imagepng($this->image, sprintf($this->cache . $this->file . '_temp', $image));

			} else {
				// "No clicks under this line" message */
				if ($image === $nbOfImages - 1) {
					$black = imagecolorallocate($this->image, 0, 0, 0);
					imageline($this->image, 0, $this->height - 1, $this->width, $this->height - 1, $black);
					imagestring($this->image, 1, 1, $this->height - 9, _t("No clicks recorded beneath this line"), $black);
				}
				imagepng($this->image, sprintf($this->path . $this->file, $image));
			}
			imagedestroy($this->image);

			// Result files
			$files['filenames'][] = sprintf($this->file, $image);
			$files['absolutes'][] = sprintf($this->path . $this->file, $image);
		}

		// End tasks
		if ($this->finishDrawing() === false)
			return false;

		if ( ! $this->heatmap )
			return $files;

		/**
		 * Now, our image is a direct representation of
		 * the clicks on each pixel, so create some fuzzy
		 * dots to put a nice blur effect if user asked for a heatmap
		 */
		for ($i = 0; $i < 128; $i++) {
			$dots[$i] = imagecreatetruecolor($this->dot, $this->dot);
			imagealphablending($dots[$i], false);
		}

		for ($x = 0; $x < $this->dot; $x++) {
			for ($y = 0; $y < $this->dot; $y++) {
				$sinX = sin($x * pi() / $this->dot);
				$sinY = sin($y * pi() / $this->dot);

				for ($i = 0; $i < 128; $i++) {
					$alpha = 127 - $i * $sinX * $sinY * $sinX * $sinY;
					imagesetpixel($dots[$i], $x, $y, ((int) $alpha) * 16777216);
				}
			}
		}

		/**
		 * Colors creation :
		 * grey	 | deep blue (rgB)    | light blue (rGB)   | green (rGb)        | yellow (RGb)       | red (Rgb)
		 * 0     | $this->__colors[0] | $this->__colors[1] | $this->__colors[2] | $this->__colors[3] | 128
		 */
		sort($this->__colors);
		$colors = array();
		for ($i = 0; $i < 128; $i++)
		{
			// Red
			if ($i < $this->__colors[0])
			{
				$colors[$i][0] = $this->color_grey + ($this->color_low - $this->color_grey) * $i / $this->__colors[0];
			}
			elseif ($i < $this->__colors[2])
			{
				$colors[$i][0] = $this->color_low;
			}
			elseif ($i < $this->__colors[3])
			{
				$colors[$i][0] = $this->color_low + ($this->color_high - $this->color_low) * ($i - $this->__colors[2]) / ($this->__colors[3] - $this->__colors[2]);
			}
			else
			{
				$colors[$i][0] = $this->color_high;
			}

			// Green
			if ($i < $this->__colors[0])
				$colors[$i][1] = $this->color_grey + ($this->color_low - $this->color_grey) * $i / $this->__colors[0];

			elseif ($i < $this->__colors[1])
				$colors[$i][1] = $this->color_low + ($this->color_high - $this->color_low) * ($i - $this->__colors[0]) / ($this->__colors[1] - $this->__colors[0]);

			elseif ($i < $this->__colors[3])
				$colors[$i][1] = $this->color_high;
			else
				$colors[$i][1] = $this->color_high - ($this->color_high - $this->color_low) * ($i - $this->__colors[3]) / (127 - $this->__colors[3]);

			// Blue
			if ($i < $this->__colors[0])
				$colors[$i][2] = $this->color_grey + ($this->color_high - $this->color_grey) * $i / $this->__colors[0];

			elseif ($i < $this->__colors[1])
				$colors[$i][2] = $this->color_high;

			elseif ($i < $this->__colors[2])
				$colors[$i][2] = $this->color_high - ($this->color_high - $this->color_low) * ($i - $this->__colors[1]) / ($this->__colors[2] - $this->__colors[1]);

			else
				$colors[$i][2] = $this->color_low;
		}

		for ($image = 0; $image < $nbOfImages; $image++)
		{
			$img = imagecreatetruecolor($this->width, $this->height);
			$white = imagecolorallocate($img, 255, 255, 255);
			imagefilledrectangle($img, 0, 0, $this->width - 1, $this->height - 1, $white);
			imagealphablending($img, true);

			$imgSrc = @imagecreatefrompng(sprintf($this->cache.$this->file.'_temp', $image));
			@unlink(sprintf($this->cache.$this->file.'_temp', $image));
			if ($imgSrc === false)
				return $this->raiseError('::MEMORY_OVERFLOW::');

			for ($x = $this->startStep; $x < $this->width; $x += $this->step) {
				for ($y = $this->startStep; $y < $this->height; $y += $this->step) {
					$dot = (int) ceil(imagecolorat($imgSrc, $x, $y) / $this->maxClicks * 100);
					if ($dot !== 0)
						imagecopy($img, $dots[$dot], ceil($x - $this->dot / 2), ceil($y - $this->dot / 2), 0, 0, $this->dot, $this->dot);
				}
			}

			// Destroy image source
			imagedestroy($imgSrc);

			// Rainbow
			if ($image === 0 && $this->rainbow === true) {
				for ($i = 1; $i < 128; $i += 2) {
					/**
					 * Erase previous alpha channel so that clicks don't
					 * change the heatmap by combining their alpha */
					imageline($img, ceil($i/2), 0, ceil($i/2), 10, 16777215);

					// Then put our alpha
					imageline($img, ceil($i/2), 0, ceil($i/2), 10, (127 - $i) * 16777216);
				}
			}

			/**
			 * Some version of imagetruecolortopalette()
			 * don't transform alpha value to non alpha
			 */
			if ($this->palette === true) {
				for ($x = 0; $x < $this->width; $x++) {
					for ($y = 0; $y < $this->height; $y++) {
						/**
						 * Get Alpha value (0->127) and transform
						 * it to red (divide color by 16777216 and
						 * multiply by 65536 * 2 (red is 0->255),
						 * so divide it by 128) */
						imagesetpixel($img, $x, $y, (imagecolorat($img, $x, $y) & 0x7F000000) / 128);
					}
				}
			}

			/**
			 * Change true color image to palette then change palette colors
			 */
			imagetruecolortopalette($img, false, 127);
			for ($i = 0, $max = imagecolorstotal($img); $i < $max; $i++) {
				$color = imagecolorsforindex($img, $i);
				imagecolorset($img, $i, $colors[floor(127 - $color['red'] / 2)][0], $colors[floor(127 - $color['red'] / 2)][1], $colors[floor(127 - $color['red'] / 2)][2]);
			}

			$grey = imagecolorallocate($img, $this->color_grey, $this->color_grey, $this->color_grey);
			$gray = imagecolorallocate($img, ceil($this->color_grey / 2), ceil($this->color_grey / 2), ceil($this->color_grey / 2));
			$white = imagecolorallocate($img, 255, 255, 255);
			$black = imagecolorallocate($img, 0, 0, 0);

			// maxClicks
			if ($image === 0 && $this->rainbow === true) {
				imagerectangle($img, 0, 0, 65, 11, $white);
				imagefilledrectangle($img, 0, 11, 65, 18, $white);
				imagestring($img, 1, 0, 11, '0', $black);
				$right = 66 - strlen($this->maxClicks) * 5;
				imagestring($img, 1, $right, 11, $this->maxClicks, $black);
				imagestring($img, 1, floor($right / 2) - 12, 11, 'clicks', $black);
			}

			if ($image === $nbOfImages - 1) {
				// "No clicks under this line" message
				imageline($img, 0, $this->height - 1, $this->width, $this->height - 1, $gray);
				imagestring($img, 1, 1, $this->height - 9, _t("No clicks recorded beneath this line"), $gray);

				// Copyleft
				if ($this->copyleft === true) {
					imagestring($img, 1, $this->width - 160, $this->height - 9, 'Open source heatmap by ClickHeat', $grey);
					imagestring($img, 1, $this->width - 161, $this->height - 9, 'Open source heatmap by ClickHeat', $gray);
				}
			}

			// Save PNG file
			imagepng($img, sprintf($this->path.$this->file, $image));
			imagedestroy($img);
		}
		for ($i = 0; $i < 100; $i++)
		{
			imagedestroy($dots[$i]);
		}
		return $files;
	}

	/**
	 * Return an error
	 */
	private function raiseError($error)
	{
		$this->error = $error;
		return false;
	}
}
?>
