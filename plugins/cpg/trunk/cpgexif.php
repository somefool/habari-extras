<?php

class CpgExif
{ 
  private static $exifDbMap = array(
    "make",
    "model",
    "lens",
    "size",
    "width",
    "height",
    "fnumber",
    "exposure",
    "iso",
    "dateTimeOriginal",
    "focalLength",
    "keywords",
    "focalLengthIn35mmFilm",
    "artist",
    "copyright",
    "description"
  );

  public static function getDbMap()
  {
    return self::$exifDbMap;  
  }
     
  function getImageData($src)
  {
    $data = array();
    $data['src'] = $src;
    
    if (function_exists(exif_read_data))
    {
      $exif = @exif_read_data($src, 0, true);
      if (isset($exif))
      {
        $make = $exif['IFD0']['Make'];
        if (isset($make))
        {
          $data['make'] = $make;
        }

        $model = $exif['IFD0']['Model'];
        if (isset($model))
        {
          $data['model'] = $model;
        }

        $lens = $exif['XMP']['Lens'];
        if (isset($lens))
        {
          $data['lens'] = $lens;
        }
        
        $size = $exif['FILE']['FileSize'];
        if (isset($size))
        {
          $data['size'] = $size; 
        }  

        $width = $exif['COMPUTED']['Width'];
        if (isset($width))
        {
          $data['width'] = $width; 
        }  

        $height = $exif['COMPUTED']['Height'];
        if (isset($height))
        {
          $data['height'] = $height; 
        }  

        $fnumbers = $exif['EXIF']['FNumber'];
        if (isset($fnumbers))
        {
          $posslash = strpos($exif['EXIF']['FNumber'], "/");
          $fval = substr($exif['EXIF']['FNumber'],0,$posslash)/substr($exif['EXIF']['FNumber'],$posslash+1,(strlen($exif['EXIF']['FNumber'])));
          $data['fnumber'] = $fval ;
        }

        $exptime = $exif['EXIF']['ExposureTime'];
        if (isset($exptime))
        {
          $posslash = strpos($exptime, "/");
          if ($posslash == "1")
            $data['exposure'] = substr($exptime,0,1)."/".substr($exptime,$posslash+1,(strlen($exptime)-2));
          else if ($posslash == "2")
             $data['exposure'] = substr($exptime,0,1)."/".substr($exptime,$posslash+1,(strlen($exptime)-4));
          else if ($posslash == "3")
             $data['exposure'] = substr($exptime,0,1)."/".substr($exptime,$posslash+1,(strlen($exptime)-6));
          else
            $data['exposure'] = $exptime;
        }

        $iso = $exif['EXIF']['ISOSpeedRatings'];
        if (isset($iso))
        {
          $data['iso'] = $iso;
        }

        $taken = $exif['EXIF']['DateTimeOriginal'];
        if (isset($taken))
        {
          preg_match("/(\d+):(\d+):(\d+)\s+(\d+):(\d+):(\d+)/", $taken, $m);
          $data['taken'] = date('Y-m-d H:i:s', mktime($m[4], $m[5], $m[6], $m[2], $m[3], $m[1]));
        }

        $flengths = $exif['EXIF']['FocalLength'];
        if (isset($flengths))
        {
          $posslash = strpos($exif['EXIF']['FocalLength'], "/");
          if ($posslash)
          {
          $fval = substr($exif['EXIF']['FocalLength'],0,$posslash)/substr($exif['EXIF']['FocalLength'],$posslash+1,(strlen($exif['EXIF']['FocalLength'])));
          $data['flength'] = $fval;
          }
          else
            $data['flength'] = $flengths;
        }

        if (isset($exif['EXIF']['FocalLengthIn35mmFilm']))
        {
          $data['flength35mm'] = $exif['EXIF']['FocalLengthIn35mmFilm'];
        }

        if (isset($exif['IFD0']['Artist']))
        {
          $data['artist'] = $exif['IFD0']['Artist'];
        }

        if (isset($exif['IFD0']['Copyright']))
        {
          $data['copyright'] = $exif['IFD0']['Copyright'];
        }
      }
      
      $source = file_get_contents($src);
      if (isset($source))
      {
        $xmpdata_start = strpos($source,"<x:xmpmeta");
        $xmpdata_end = strpos($source,"</x:xmpmeta>");
        $xmplength = $xmpdata_end-$xmpdata_start;
        $xmpdata = substr($source,$xmpdata_start,$xmplength+12);
        
        unset($r);
        preg_match ("/<aux:Lens>.+<\/aux:Lens>/", $xmpdata, $r);
        $xmp_item = "";
        $xmp_item = @$r[0];
        if (isset($xmp_item))
        {
          $data['lens'] = $xmp_item;
        }
        unset($source);
      }            
    }
    
    return $data;
  }
}

  
?>