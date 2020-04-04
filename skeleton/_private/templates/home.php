<h1>Welcome</h1>

<p>Available routes:</p>
<ul>
<?php foreach ($_routes as $name => $config): ?>
  <li>
    <a href="<?php echo $config["path"]; ?>"<?php echo $route === $_routeName ? ' class="active"' : ''; ?>>
      <?php echo $config["title"]; ?>
    </a>
  </li>
<?php endforeach; ?>
