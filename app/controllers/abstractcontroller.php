<?php

namespace PROJECT\CONTROLLERS;

use PROJECT\LIB\FrontController;

class AbstractController
{
  protected $_controller;
  protected $_action;
  protected $_params;

  public function notFoundAction()
  {
    echo "Sorry this page doesn't exist";
  }

  public function setController($controllerName)
  {
    $this->_controller = $controllerName;
  }

  public function setAction($actionName)
  {
    $this->_action = $actionName;
  }

  public function setParams($paramsName)
  {
    $this->_params = $paramsName;
  }

  protected function _view()
  {
    if ($this->_action == FrontController::NOT_FOUND_ACTION) {
      require_once VIEWS_PATH . 'notfound' . DS . 'notfound.view.php';
    } else {
      $view = VIEWS_PATH . $this->_controller . DS . $this->_action . '.view.php';
      if (file_exists($view)) {
        require_once $view;
      } else {
        require_once VIEWS_PATH . 'notfound' . DS . 'noview.view.php';
      }
    }
  }
}
