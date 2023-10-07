<h1>Welcome</h1>

<p>Configured routes:</p>
<ul>
<?php foreach ($_routes as $name => $config): ?>
  <li>
    <a href="<?= $config["path"] ?>"<?= $name === $_routeName ? ' class="active"' : '' ?>>
      <?= $config["title"] ?>
    </a>
  </li>
<?php endforeach; ?>
