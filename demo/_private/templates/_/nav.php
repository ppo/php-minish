<?php foreach ($_routes as $route => $config): ?>
  <a href="<?php echo $config["path"]; ?>"<?php echo $route === $_routeName ? ' class="active"' : ''; ?>>
    <?php echo $config["title"]; ?>
  </a>&nbsp;
<?php endforeach; ?>
<a href="/path/not/found">Error 404</a>
