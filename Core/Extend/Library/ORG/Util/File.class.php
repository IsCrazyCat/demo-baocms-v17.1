<?php



class File{
	public $files = array();
	public $error;
	
	//获取目录下的所有文件和目录
	//使用$path = 'a/x/s/';
	
	public function getFiles($path){
		if(is_dir($path)){
			$path = dirname($path) . '/' . basename($path) . '/';
			$file = dir($path);
			while (false !== ($entry = $file->read())){
				if ($entry !== '.' && $entry !== '..'){
					$cur = $path . $entry;
					if (is_dir($cur)) {
						$subPath = $cur . '/';
						$this->getFiles($subPath);
					}
					$this->files[] = $cur;
				}
			}
			$file->close();
			return $this->files;
		} else {
			$this->error = $path . 'not a dir';
			return $this->error;
		}
	}
	
	
	//删除目录下的文件
	//使用$path = 'a/x/s/';
	
	public function rmFiles($path){
		$ret = '删除缓存成功';
		$files = $this->getFiles($path);
		if (!is_array($files)) {
			$ret = $files;
		} elseif (empty($files)) {
			$ret = '目录下没有文件或目录';
		} else {
			foreach ($files as $item => $file) {
				if (is_dir($file)) {
					rmdir($file);
				} elseif (is_file($file)) {
					unlink($file);
				}
			}
		}
		return $ret;
	}
}