<?php

namespace Noking50\Modules\Required\Controllers;

use Illuminate\Routing\Controller;
//
use Request;
use Route;

abstract class BaseController extends Controller {

    /**
     * 此頁的response，
     * 如果是ajax，array json
     * 否則  \Illuminate\View\View
     * 
     * @var array|\Illuminate\View\View
     */
    protected $response;

    /**
     * 此頁的view所在的資料夾view路徑
     *
     * @var string 
     */
    protected $view_folder = '';

    /**
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct() {
        
    }

    public function setResponse($view_folder) {
        $this->view_folder = $view_folder;
        if (Request::ajax()) {
            $this->response = [
                'result' => 'ok',
                'msg' => '',
                'detail' => [],
                'data' => []
            ];
        } else {
            $action_method = Route::current()->getActionMethod();
            $view_path = $view_folder . '::' . $action_method;
            $this->setViewPath($view_path);
        }
    }

    public function setViewPath($path) {
        if (view()->exists($path)) {
            if (!is_object($this->response) || !($this->response instanceof Illuminate\View\View)) {
                $this->response = view($path);
            } else {
                $this->response->setPath($path);
            }
            
            $this->response->with('_view_folder', $this->view_folder);
        }
    }

}
