<?php

class IndependentView {
  function __invoke($app, $data=NULL) {
    echo '<h2>Template: Independent View</h2><a href="/">Home</a>';
  }
}
