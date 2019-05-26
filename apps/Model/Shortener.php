<?php
class Shortener {
    protected $instance = null;
    protected $dataDir;
    
    private function validateDataDir($dir) {
        if (!is_dir($dir)):
            mkdir($dir, 0755, true);
        endif;
    }
    
    private function linkFile($key) {
        $keyFilename = sha1($key);
        $subdir = DS . substr($keyFilename, 0, 3);
        $dataDir = ROOT . DS . DATA . DS . DATA_LINK;
        $this->validateDataDir($dataDir . $subdir);
        return sprintf("%s/%s", $dataDir . $subdir, sha1($key));
    }
    
    private function keyFile($key) {
        $keyFilename = sha1($key);
        $subdir = DS . substr($keyFilename, 0, 3);
        $dataDir = ROOT . DS . DATA . DS . DATA_KEY;
        $this->validateDataDir($dataDir . $subdir);
        return sprintf("%s/%s", $dataDir . $subdir, sha1($key));
    }
    
    public function setKey($data, $len = 6) {
        $code = $this->createKey($len);
        if ($code){
            $keyFilePath = $this->keyFile($code);
            if (!$fp = fopen($keyFilePath, 'wb')):
                return false;
            endif;
            if (flock($fp, LOCK_EX)):
                fwrite($fp, serialize($data));
                flock($fp, LOCK_UN);
            else:
                return false;
            endif;
            fclose($fp);
        }
        return $code;
    }
    
    public function getKey($key) {
        $keyFilePath = $this->keyFile($key);
        if (!@file_exists($keyFilePath)):
            return false;
        endif;
        if (!$fp = @fopen($keyFilePath, 'rb')):
            return false;
        endif;
        flock($fp, LOCK_SH);
        $data = unserialize(fread($fp, filesize($keyFilePath)));
        flock($fp, LOCK_UN);
        fclose($fp);
        return $data;
    }

    public function deleteKey($key) {
        $keyFilePath = $this->keyFile($key);
        if (file_exists($keyFilePath)):
            unlink($keyFilePath);
            return true;
        endif;
        return false;
    }
    
    public function setLink($link, $data) {
        $linkFilePath = $this->linkFile($link);
        if (!$fp = fopen($linkFilePath, 'wb')):
            return false;
        endif;
        if (flock($fp, LOCK_EX)):
            fwrite($fp, serialize($data));
            flock($fp, LOCK_UN);
        else:
            return false;
        endif;
        fclose($fp);
        return true;
    }
    
    public function getLink($link) {
        $linkFilePath = $this->linkFile($link);
        if (!@file_exists($linkFilePath)):
            return false;
        endif;
        if (!$fp = @fopen($linkFilePath, 'rb')):
            return false;
        endif;
        flock($fp, LOCK_SH);
        $data = unserialize(fread($fp, filesize($linkFilePath)));
        flock($fp, LOCK_UN);
        fclose($fp);
        return $data;
    }

    public function deleteLink($link) {
        $linkFilePath = $this->linkFile($link);
        if (file_exists($linkFilePath)):
            unlink($linkFilePath);
            return true;
        endif;
        return false;
    }
    
    public function createKey($len = 6){
        $i = 0;
        $chars = null;
        
        foreach (range('a', 'z') as $char) {
            $chars[] = ord($char);
            $i++;
        }
        
        foreach (range('A', 'Z') as $char) {
            $chars[] = ord($char);
            $i++;
        }
        
        foreach (range('0', '9') as $char) {
            $chars[] = ord($char);
            $i++;
        }
        
        $idxchar = 0;
        $code = '';
        $lenchars = count($chars);
        
        for ($i = 0; $i < $len; $i++){
            $idxchar = mt_rand(0, $lenchars - 1);
            $char = $chars[$idxchar];
            $code .= chr($char);
        }
        
        $check = $this->getKey($code);
        if ($check){
            $code = $this->createKey($len);
        }
        
        return $code;
    }
}