<?php
class Parser_Provider_Image {

    /**
	 * Get image size by reading specific parts of image - meta data, falls back to getimagesize php method which downloads all image
	 * @param string $imageUrl path to image being reading
	 * @return array
	 */
   public function getImageSize($imageUrl) {

	    $fileInfo = pathinfo($imageUrl);

	    if (!empty($fileInfo['extension']) && (strtolower($fileInfo['extension']) == 'jpg' || strtolower($fileInfo['extension']) == 'jpeg')) {
            $sizes =  $this->getJpegSize($imageUrl);

	    } else if (!empty($fileInfo['extension']) && strtolower($fileInfo['extension']) == 'png') {
            $sizes =  $this->getPngSize($imageUrl);

		} else if (!empty($fileInfo['extension']) && strtolower($fileInfo['extension']) == 'gif') {
            $sizes =  $this->getGifSize($imageUrl);
		}

	    if (empty($sizes[0]) || empty($sizes[1])) {
	        return @getimagesize($imageUrl);
	    } else {
	        return $sizes;
	    }

   }

  /**
   *
   * Parse jpeg file and get file size dimensions
   * @param string $imgLocation
   * @return mixed array|boolean
   */
  protected function getJpegSize($imgLocation) {

    $handle    = @fopen($imgLocation, "rb");
    $new_block = null;

    if ($handle && !feof($handle)) {
        $new_block = fread($handle, 32);
        $i = 0;

        if ($new_block[$i]=="\xFF" && $new_block[$i+1]=="\xD8" && $new_block[$i+2]=="\xFF" && $new_block[$i+3]=="\xE0") {
            $i += 4;
            if ($new_block[$i+2]=="\x4A" && $new_block[$i+3]=="\x46" && $new_block[$i+4]=="\x49" && $new_block[$i+5]=="\x46" && $new_block[$i+6]=="\x00") {
                // Read block size and skip ahead to begin cycling through blocks in search of SOF marker
                $block_size = unpack("H*", $new_block[$i] . $new_block[$i+1]);
                $block_size = hexdec($block_size[1]);

                while(!feof($handle)) {
                    $i += $block_size;
                    $new_block .= fread($handle, $block_size);
                    if (!empty($new_block[$i]) && $new_block[$i] == "\xFF") {
                        // New block detected, check for SOF marker
                        $sof_marker = array("\xC0", "\xC1", "\xC2", "\xC3", "\xC5", "\xC6", "\xC7", "\xC8", "\xC9", "\xCA", "\xCB", "\xCD", "\xCE", "\xCF");
                        if (in_array($new_block[$i+1], $sof_marker)) {
                            // SOF marker detected. Width and height information is contained in bytes 4-7 after this byte.
                            $size_data = $new_block[$i+2] . $new_block[$i+3] . $new_block[$i+4] . $new_block[$i+5] . $new_block[$i+6] . $new_block[$i+7] . $new_block[$i+8];
                            $unpacked = unpack("H*", $size_data);
                            $unpacked = $unpacked[1];
                            $height = hexdec($unpacked[6] . $unpacked[7] . $unpacked[8] . $unpacked[9]);
                            $width  = hexdec($unpacked[10] . $unpacked[11] . $unpacked[12] . $unpacked[13]);
                            return array($width, $height);

                        } else {
                            // Skip block marker and read block size
                            $i += 2;
                            $block_size = unpack("H*", $new_block[$i] . $new_block[$i+1]);
                            $block_size = hexdec($block_size[1]);
                        }
                    } else {
                        return false;
                    }
                }
            }
        }
    }
    return false;
  }

  /**
   *
   * Get png file size from meta
   * @param string $imgLocation location to img
   * @return mixed array|boolean
   */
  protected function getPngSize($imgLocation) {
    	$handle = @fopen($imgLocation, "rb");
        $img    = NULL;

        if (!feof($handle)) {
            $img = fread($handle, 24);

            $widthUppack = unpack('H*',$img[16] . $img[17] . $img[18] . $img[19]);
            $width = hexdec($widthUppack[1]);

            $heightUppack = unpack('H*',$img[20] . $img[21] . $img[22] . $img[23]);
    	    $height = hexdec($heightUppack[1]);

            return array($width, $height);

        } else {
            return false;
        }
   }

   /**
    * Get gif size from meta
    * @param string $imgLocation
    * @return mixed array|boolean
    */
   protected function getGifSize($imgLocation) {
    	$handle = @fopen($imgLocation, "rb");
        $img = NULL;
        if (!feof($handle)) {
            $img = fread($handle, 13);

            $width = unpack('S', $img[6] . $img[7]);
   	        $height = unpack('S', $img[8] . $img[9]);

            return array($width[1],  $height[1]);

        }
    }

}