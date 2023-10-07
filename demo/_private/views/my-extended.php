<?php

class MyExtendedView extends View {
  function __invoke($app, $data=NULL) {
    $this->_init($app, $data);
    $this->setMainTemplate("extended-template");
    $this->render();
  }
}
