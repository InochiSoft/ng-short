<?php
class Helper{
    protected $config;
    protected $session;
    
    public function __construct() {
        $this->session = new \NG\Session;
        $this->config = \NG\Registry::get('config');
    }
    
    public function getArrayValue($array = null, $key = null, $default = ""){
        $result = $default;
        if ($array !== null){
            if (is_array($array)){
                if (array_key_exists($key, $array)){
                    $result = $array[$key];
                }
            }
        }
        return $result;
    }
    
    public function removeWhiteSpace($text){
        $text = preg_replace('/[\t\n\r\0\x0B]/', '', $text);
        $text = preg_replace('/([\s])\1+/', ' ', $text);
        $text = str_replace(array('{ ', ' {', ' { '), '{', $text);
        $text = str_replace(array(' }', '} ', ' { '), '}', $text);
        $text = str_replace(array(' =', '= ', ' = '), '=}', $text);
        $text = str_replace(array(' ,', ', ', ' , '), ',', $text);
        $text = str_replace(array(' :', ': ', ' : '), ':', $text);
        $text = str_replace(array(' ;', '; ', ' ; '), ';', $text);
        $text = trim($text);
        return $text;
    }
    
	public function getSecureQuestion(){
        $number1 = mt_rand(100, 500);
        $number2 = mt_rand(1, 100);
        $arrOperator = array("+", "-", "x", "ditambah", "dikurangi", "dikali");
        
        $key = array_rand($arrOperator);
        $operator = $arrOperator[$key];
        $operatorKey = "";
        
        $oprRand = mt_rand(0, 1);
        $oprNumber1 = mt_rand(0, 1);
        $oprNumber2 = mt_rand(0, 1);
        
        //$arrOperatorKey = array($operator, $operatorKey);
        
        $arrNumber1Text = array($number1, $this->terbilang($number1));
        $arrNumber2Text = array($number2, $this->terbilang($number2));
        
        $question = $arrNumber1Text[$oprNumber1] . " " . $operator . " " . $arrNumber2Text[$oprNumber2];
        $answer = 0;
        
        switch ($operator){
            case "+":
            case "ditambah":
                $answer = $number1 + $number2;
            break;
            case "-":
            case "dikurangi":
                $answer = $number1 - $number2;
            break;
            case "x":
            case "dikali":
                $answer = $number1 * $number2;
            break;
        }
        
		$arrQuestion = 
            array(
                'question' => $question,
				'answer' => $answer
			);
		
		$result = $arrQuestion;
		
		return $result;
	}
    
    public function terbilang($satuan) {
        $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        if ($satuan < 12)
            return " " . $huruf[$satuan];
        elseif ($satuan < 20)
            return $this->terbilang($satuan - 10) . "belas";
        elseif ($satuan < 100)
            return $this->terbilang($satuan / 10) . " puluh" . $this->terbilang($satuan % 10);
        elseif ($satuan < 200)
            return " seratus" . $this->terbilang($satuan - 100);
        elseif ($satuan < 1000)
            return $this->terbilang($satuan / 100) . " ratus" . $this->terbilang($satuan % 100);
        elseif ($satuan < 2000)
            return " seribu" . $this->terbilang($satuan - 1000);
        elseif ($satuan < 1000000)
            return $this->terbilang($satuan / 1000) . " ribu" . $this->terbilang($satuan % 1000);
        elseif ($satuan < 1000000000)
            return $this->terbilang($satuan / 1000000) . " juta" . $this->terbilang($satuan % 1000000);
        elseif ($satuan <= 1000000000)
            return "";
    }
    
    public function validateUrl($url) {
        $path = parse_url($url, PHP_URL_PATH);
        $encoded_path = array_map('urlencode', explode('/', $path));
        $url = str_replace($path, implode('/', $encoded_path), $url);

        return filter_var($url, FILTER_VALIDATE_URL) ? true : false;
    }
}