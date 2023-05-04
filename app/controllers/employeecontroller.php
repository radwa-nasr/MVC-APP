<?php

namespace PROJECT\CONTROLLERS;

use PROJECT\MODELS\EmployeeModel;

class EmployeeController extends AbstractController
{
  public function defaultAction()
  {
    var_dump(EmployeeModel::getAll());
    $this->_view();
  }
}
