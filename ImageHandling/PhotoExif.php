<?php

namespace NetteLab\ImageHandling;

class PhotoExif
{
	protected $file;
	
	protected $exifData = null;
	
	public function __construct($file)
	{
		$this->file = $file;
		$this->loadData();
	}
	
	protected function loadData()
	{
		if(is_null($this->exifData))
		{
			$this->exifData = exif_read_data($this->file, 0, true);
		}
	}
	
	public function getCameraFullName()
	{
		return trim($this->getCameraBrand().' '.$this->getCameraModel());
	}
	
	public function getCameraModel()
	{
		if(isset($this->exifData['IFD0']['Model']))
			return $this->exifData['IFD0']['Model'];
	}
	
	public function getCameraBrand()
	{
		if(isset($this->exifData['IFD0']['Make']))
			return $this->exifData['IFD0']['Make'];
	}
	
	public function getMakerNote()
	{
		if(isset($this->exifData['EXIF']['MakerNote']))
			return $this->exifData['EXIF']['MakerNote'];
	}
	
	public function getXResolution()
	{
		if(isset($this->exifData['FILE']['XResolution']))
			return $this->exifData['FILE']['XResolution'];
	}
	
	public function getYResolution()
	{
		if(isset($this->exifData['FILE']['YResolution']))
			return $this->exifData['FILE']['YResolution'];
	}
	
	public function getFileName()
	{
		if(isset($this->exifData['FILE']['FileName']))
			return $this->exifData['FILE']['FileName'];
	}
	
	public function getFileFileType()
	{
		if(isset($this->exifData['FILE']['FileType']))
			return $this->exifData['FILE']['FileType'];
	}
	
	public function getFileMimeType()
	{
		if(isset($this->exifData['FILE']['MimeType']))
			return $this->exifData['FILE']['MimeType'];
	}
	
	public function getFileDateTime()
	{
		if(isset($this->exifData['FILE']['FileDateTime']))
			return $this->exifData['FILE']['FileDateTime'];
	}

	public function getWidth()
	{
		if(isset($this->exifData['COMPUTED']['Width']))
			return $this->exifData['COMPUTED']['Width'];
	}
	
	public function getHeight()
	{
		if(isset($this->exifData['COMPUTED']['Height']))
			return $this->exifData['COMPUTED']['Height'];
	}
	
	public function isColor()
	{
		if(isset($this->exifData['COMPUTED']['IsColor']))
			return $this->exifData['COMPUTED']['IsColor'];
	}
	
	public function getShutter()
	{
		if(isset($this->exifData['EXIF']['ExposureTime']))
			return $this->exifData['EXIF']['ExposureTime'];
	}
	
	public function getAperture()
	{
		if(isset($this->exifData['EXIF']['FNumber']))
		{
			$tmp = explode("/", $this->exifData['EXIF']['FNumber']);
			return $tmp[0] / $tmp[1];
		}
	}

	public function getIso()
	{
		if(isset($this->exifData['EXIF']['ISOSpeedRatings']))
			return $this->exifData['EXIF']['ISOSpeedRatings'];
	}

	public function isFlash()
	{
		if(isset($this->exifData['EXIF']['Flash']))
			return $this->exifData['EXIF']['Flash'];
	}
	
	public function getFocalLength()
	{
		if(isset($this->exifData['EXIF']['FocalLength']))
		{
			$tmp = explode("/", $this->exifData['EXIF']['FocalLength']);
			return $tmp[0] / $tmp[1];
		}
	}
	
	public function getUserComment()
	{
		if(isset($this->exifData['COMPUTED']['UserComment']))
		{
			return $this->exifData['COMPUTED']['UserComment'];
		}
	}

	public function getUserCopyright()
	{
		if(isset($this->exifData['COMPUTED']['Copyright']))
		{
			return $this->exifData['COMPUTED']['Copyright'];
		}
	}
	
	public function getUserCopyrightPhotographer()
	{
		if(isset($this->exifData['COMPUTED']['Copyright']['Photographer']))
		{
			return $this->exifData['COMPUTED']['Copyright']['Photographer'];
		}
	}

	public function getUserCopyrightEditor()
	{
		if(isset($this->exifData['COMPUTED']['Copyright']['Editor']))
		{
			return $this->exifData['COMPUTED']['Copyright']['Editor'];
		}
	}
	
	public function varDump()
	{
		if(!is_array($this->exifData))
		{
			return;
		}
		
		foreach ($this->exifData as $key => $section)
		{
			foreach ($section as $name => $val)
			{
				echo "$key.$name: $val<br />\n";
			}
		}
	}

}