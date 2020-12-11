<?php

use Nette\Utils\Image,
	Nette\Utils\Strings,
	Nette\Utils\Validators;

/**
 * Image generator
 */
class ImageGenerator
{
	use \Nette\SmartObject;
	
	/** @var string temp dir */
	protected $tempDir;

	/** @var string */
	protected $placeholder;

	/** @var string */
	protected $url;

	/** @var boolean */
	protected $crop;
	
	/** @var int wight thumbnail */
	protected $w;
	
	/** @var int height thumbnail */
	protected $h;
	
	/** @var int id */
	protected $id;
	
	/** @var string folder */
	protected $folder;
	
	/**
	 * @param array $settings
	 */
	public function __construct()
	{
		$this->tempDir = '/data/temp/'; //folder in WWW_DIR
		$this->placeholder = WWW_DIR.'/data/not-found.png';
	}


	/**
	 * Create thumbnail with params
	 */
	public function getUrlThumb(){
		//distribution of the filename and extension
		$url = explode('/', $this->url);
		$url = explode('.', $url[count($url)-1]);

		//create new filename
		$filename = $url[0] . ($this->crop===null ? '-null' : ($this->crop ? '-crop' : '')) . '-' . (int)$this->w . '-' . (int)$this->h;
		$suffix = isset($url[1]) ? '.'.$url[1] : '.jpg';
		$origFilename = $url[0] . $suffix;
		$thumbFilename = $filename . $suffix;

		//get absolute path
		$urlExplode = explode('www', $this->url);
		
		if (array_key_exists(1, $urlExplode)) {
			$parts = $urlExplode;
			unset($parts[0]);
			$sourcePathImage = WWW_DIR.implode('www', $parts);
		}				
		$returnUrl = ($_SERVER["REMOTE_ADDR"]=='127.0.0.1' ? $urlExplode[0].'www'.$this->tempDir : $this->tempDir);
		$returnUrl = str_replace('//', '/', $returnUrl);


		if (strpos($this->url, 'www') == false) {
			$sourcePathImage = WWW_DIR.$this->url;
		}

		//create temp folder and set permissions
		if (!file_exists(WWW_DIR.$this->tempDir.$this->folder)) {
			//mkdir(WWW_DIR.$this->tempDir, 0777, true);
			Nette\Utils\FileSystem::createDir(WWW_DIR.$this->tempDir.$this->folder); //vytvoříme složku pokud neexistuje
		}

		/*
			If exist resize image
		*/
		if(file_exists(WWW_DIR.$this->tempDir.$this->folder.$thumbFilename)){
			if($_SERVER["REMOTE_ADDR"]=='127.0.0.1'){
				return $returnUrl.$this->folder.$thumbFilename;
			}else{
				return $this->tempDir.$this->folder.$thumbFilename;
			}
		}

		/*
            If dont exist source image
        */
        if(!is_file($sourcePathImage) || ((int) @filesize($sourcePathImage))/1024/1024>9){
            //if exist thumbnail with size
            return $this->createNonImage($returnUrl);
        }else{
            list($width, $height) = getimagesize($sourcePathImage);
            if($width+$height>12000){
                return $this->createNonImage($returnUrl);   
            }
        }

		/* 
			Source dont image
		*/
		try {
			// image
			$image = Image::fromFile($sourcePathImage);
		} catch (\Nette\Utils\UnknownImageFileException $e) {
			//file
			if(!file_exists(WWW_DIR.$this->tempDir.'no-image-'.$this->w.'-'.$this->h.'.png')){
				$image = Image::fromBlank($this->w, $this->h, Image::rgb(246, 247, 247));

				if(file_exists($this->placeholder)){
					$placeholder = Image::fromFile($this->placeholder);
					$placeholder->resize($this->w/2, $this->h/2, Image::SHRINK_ONLY);
					$image->place($placeholder, '50%', '50%', '10%');
				}else{
					$black = Image::rgb(246, 247, 247);
					$image->line(0, 1, $this->w, $this->h, $black);
					$image->line(0, $this->h, $this->w, 0, $black);
				}

				$image->save(WWW_DIR.$this->tempDir.'no-image-'.$this->w.'-'.$this->h.'.png');
			}
			return $returnUrl.'no-image-'.$this->w.'-'.$this->h.'.png';
		}


		/*
			Resizing and cropping image
		*/
		if($this->crop===null){
            /*
                create thumbnail with exact dimensions and center original image
            */
            try {
				$blank = Image::fromFile($sourcePathImage);
	            $blank->resize($this->w, $this->h);

	            $image = Image::fromBlank($this->w, $this->h, Image::rgb(255, 255, 255, 127));
	            $image->place($blank, '50%', '50%');

	            $image->save(WWW_DIR.$this->tempDir.$this->folder.$thumbFilename);
			} catch (Exception $e) {
				return $this->createNonImage($returnUrl);
			}
        }else if($this->crop===true){

			/*
				create thumbnail with exact dimensions
			*/
			try {
				$image = Image::fromFile($sourcePathImage);

				if( round($image->width/$image->height, 1)==round($this->w/$this->h, 1) ){
					$image->resize($this->w, $this->h);
				}else{
					$image->resize($this->w, $this->h, Image::FILL);
				}

				$image->resize($this->w, $this->h, Image::EXACT);

				$image->crop('50%', '50%', $this->w, $this->h);
				$image->save(WWW_DIR.$this->tempDir.$this->folder.$thumbFilename);
			} catch (Exception $e) {
				return $this->createNonImage($returnUrl);
			}
		}else{
			/*	
				Resizing image and create thumbnail
			*/
			try {
				$image = Image::fromFile($sourcePathImage);
				$image->resize($this->w, $this->h);
				$image->save(WWW_DIR.$this->tempDir.$this->folder.$thumbFilename);
			} catch (Exception $e) {
				return $this->createNonImage($returnUrl);
			}
		}

		return $returnUrl.$this->folder.$thumbFilename;
	}

	private function createNonImage($returnUrl){
		$this->w = $this->w>0 ? $this->w : $this->h;
		$this->h = $this->h>0 ? $this->h : $this->w;

		if(!file_exists(WWW_DIR.$this->tempDir.'no-image-'.$this->w.'-'.$this->h.'.png')){
			$image = Image::fromBlank($this->w, $this->h, Image::rgb(246, 247, 247));

			if(file_exists($this->placeholder)){
				$placeholder = Image::fromFile($this->placeholder);
				$placeholder->resize($this->w/2, $this->h/2, Image::SHRINK_ONLY);
				$image->place($placeholder, '50%', '50%', '10%');
			}else{
				$black = Image::rgb(246, 247, 247);
				$image->line(0, 1, $this->w, $this->h, $black);
				$image->line(0, $this->h, $this->w, 0, $black);
			}

			$image->save(WWW_DIR.$this->tempDir.'no-image-'.$this->w.'-'.$this->h.'.png');
		}
		return $returnUrl.'no-image-'.$this->w.'-'.$this->h.'.png';
	}

	/**
	  * Set crop image
	  * @param  boolean
	  * @return self
	  */
	public function setCropImage($crop)
	{
		$this->crop = $crop;
		return $this;
	}

	/**
	  * Set width thumbnail
	  * @param  int
	  * @return self
	  */
	public function setWidth($w)
	{
		$this->w = $w;
		return $this;
	}

	/**
	  * Set height thumbnail
	  * @param  int
	  * @return self
	  */
	public function setHeight($h)
	{
		$this->h = $h;
		return $this;
	}

	/**
	  * Set id thumbnail
	  * @param  int
	  * @return self
	  */
	public function setId($id)
	{
		$this->id = $id;
		$this->folder = implode('/', str_split($this->id)).'/';
		return $this;
	}

	/**
	  * Set url
	  * @param  string
	  * @return self
	  */
	public function setUrl($url)
	{
		if(substr($url, -1)=='/'){
			$this->url = $url.'/not-found.jpg';
		}else{
			$this->url = $url;
		}
		return $this;
	}
	
	
	public function downloadImageFromUrl($url, $path)
	{
		$ch = curl_init($url);
		$fp = fopen($path, 'wb');
		
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
	}
	
}