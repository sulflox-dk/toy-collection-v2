<h1>Manufacturers</h1>

<ul>
    <?php foreach ($manufacturers as $m): ?>
        <li>
            <a href="manufacturer/<?= $m['id'] ?>"><?= $e($m['name']) ?></a>
            <span class="badge"><?= $e($m['slug']) ?></span>
        </li>
    <?php endforeach; ?>
</ul>
