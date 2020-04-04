<?php

class IndependentView {
  function __invoke($app, $data=null) {
    echo '<h2>Template: Independent View</h2><a href="/">Home</a>';
  }
}
