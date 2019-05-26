<?php

class IndexController extends NG\Controller {
    protected $config;
    protected $cache;
    protected $session;
    protected $cookie;
    protected $helper;
    protected $shortener;
    
    public function init() {
        $this->config = $this->view->config = \NG\Registry::get('config');
        $this->session = $this->view->session = new \NG\Session();
        $this->cookie = $this->view->cookie = new \NG\Cookie();
        $this->cache = $this->view->cache = new \NG\Cache();
        $this->helper = $this->view->helper = new Helper();
        $this->shortener = $this->view->shortener = new Shortener();
    }
    
    public function IndexAction() {
        $requests = \NG\Route::getRequests();
        
        $shortener = $this->shortener;
        $session = $this->session;
        $cookie = $this->cookie;
        $cache = $this->cache;
        $helper = $this->helper;
        
        $param1 = '';
        
        $viewAlert = null;
        $viewCaptcha = $session->get('captcha');
        
        if (!$viewCaptcha){
            $viewCaptcha = $helper->getSecureQuestion();
            $session->set('captcha', $viewCaptcha);
        }
        
        $viewLink = '';
        $viewAnswer = '';
        $viewResult = '';
        
        if ($requests){
            if (isset($requests['param1'])){
                $param1 = $requests['param1'];
                if ($param1){
                    switch($param1){
                        case "page":
                            
                        break;
                        default:
                            $data = $shortener->getKey($param1);
                            if ($data){
                                $link = $helper->getArrayValue($data, 'link');
                                if ($link){
                                    \NG\Route::redirect($link);
                                }
                            }
                            print_r($data);
                        break;
                    }
                }
            }
        }
        
        if (isset($_POST)){
            if (isset($_POST["create"])){
                $viewLink = isset($_POST["link"]) ? $_POST["link"] : '';
                $viewAnswer = isset($_POST["captcha"]) ? $_POST["captcha"] : '';
                
                if ($viewCaptcha){
                    $answer = $helper->getArrayValue($viewCaptcha, 'answer');
                }
                if (!$viewLink){
                    $viewAlert = array(
                        'type' => 'danger',
                        'title' => 'Kesalahan!',
                        'message' => 'Silakan isi Tautan!',
                    );
                } else {
                    if (!$viewAnswer){
                        $viewAlert = array(
                            'type' => 'danger',
                            'title' => 'Kesalahan!',
                            'message' => 'Silakan isikan jawaban keamanan!',
                        );
                    } else {
                        if ($answer == $viewAnswer){
                            $isValid = $helper->validateUrl($viewLink);
                            if ($isValid){
                                $date = time();
                                
                                $dataLink = $shortener->getLink($viewLink);
                                if (!$dataLink){
                                    $dataKey = array('date' => $date, 'link' => $viewLink);
                                    $viewResult = $shortener->setKey($dataKey);
                                    
                                    $dataLink = array('date' => $date, 'key' => $viewResult);
                                    $shortener->setLink($viewLink, $dataLink);
                                } else {
                                    $viewResult = $helper->getArrayValue($dataLink, 'key');
                                }
                                
                                if ($viewCaptcha){
                                    $session->set('captcha', null);
                                    $viewCaptcha = $helper->getSecureQuestion();
                                    $session->set('captcha', $viewCaptcha);
                                    $viewAnswer = '';
                                }
                            } else {
                                $viewAlert = array(
                                    'type' => 'danger',
                                    'title' => 'Kesalahan!',
                                    'message' => 'Tautan yang Anda masukkan tidak valid!',
                                );
                            }
                        } else {
                            if ($viewCaptcha){
                                $session->set('captcha', null);
                                $viewCaptcha = $helper->getSecureQuestion();
                                $session->set('captcha', $viewCaptcha);
                                $viewAnswer = '';
                            }
                            $viewAlert = array(
                                'type' => 'danger',
                                'title' => 'Kesalahan!',
                                'message' => 'Jawaban yang Anda masukkan salah!',
                            );
                        }
                    }
                }
            }
        }
        
        $this->view->viewTitle = 'DEL.ONE';
        $this->view->viewDescription = 'DEL.ONE adalah situs penyingkat tautan (URL shortener)';
        $this->view->viewKeywords = 'URL, shortener, short, tautan, link, singkat';
        
        $this->view->viewCaptcha = $viewCaptcha;
        $this->view->viewAlert = $viewAlert;
        $this->view->viewLink = $viewLink;
        $this->view->viewAnswer = $viewAnswer;
        $this->view->viewResult = $viewResult;
    }
}
?>
