<?php

namespace Picqer\Barcode;

use Picqer\Barcode\Exceptions\BarcodeException;

class BarcodeGeneratorPNG extends BarcodeGenerator
{

    /**
     * Return a PNG image representation of barcode (requires GD or Imagick library).
     *
     * @param string $code code to print
     * @param string $type type of barcode:
     * @param int $widthFactor Width of a single bar element in pixels.
     * @param int $totalHeight Height of a single bar element in pixels.
     * @param array $color RGB (0-255) foreground color for bar elements (background is transparent).
     * @return string image data or false in case of error.
     * @public
     */
    public function getBarcode($code, $text, $type, $widthFactor = 2, $totalHeight = 30, $font_size = 9, $color = array(0, 0, 0))
    {
        $barcodeData = $this->getBarcodeData($code, $type);

        // calculate image size
        $width = ($barcodeData['maxWidth'] * $widthFactor);
        $height = $totalHeight + 11;

        if (function_exists('imagecreate')) {
            // GD library
            $imagick = false;
            $png = imagecreate($width, $height);
            $colorBackground = imagecolorallocate($png, 255, 255, 255);
            imagecolortransparent($png, $colorBackground);
            $colorForeground = imagecolorallocate($png, $color[0], $color[1], $color[2]);
        } else {
            throw new BarcodeException('No GD Lib');
        }

        // print bars
        $positionHorizontal = 0;
        foreach ($barcodeData['bars'] as $bar) {
            $bw = round(($bar['width'] * $widthFactor), 3);
            $bh = round(($bar['height'] * $totalHeight / $barcodeData['maxHeight']), 3);
            if ($bar['drawBar']) {
                $y = round(($bar['positionVertical'] * $totalHeight / $barcodeData['maxHeight']), 3);
		            // draw a vertical bar
		                imagefilledrectangle($png, $positionHorizontal, $y, ($positionHorizontal + $bw) - 1, ($y + $bh),
		                    $colorForeground);
            }
            $positionHorizontal += $bw;
        }

			  // Set Path to Font File
			  $font = APPPATH."libraries/php-barcode-generator/font/Arial.ttf";
				$angle = 0;
				
				$text_box = imagettfbbox($font_size,$angle,$font,$text);
				// Get your Text Width and Height
				$text_width = $text_box[2]-$text_box[0];

				// Calculate coordinates of the text
				$x = ($width/2) - ($text_width/2);


				// Add the text
				imagettftext($png, $font_size, 0, $x, $height, $colorForeground, $font, $text);
				
        ob_start();
        imagepng($png);
        imagedestroy($png);
        $image = ob_get_clean();

        return $image;
    }
}