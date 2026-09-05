<?php

declare(strict_types=1);

use Yiisoft\Html\Html;

/**
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Router\CurrentRoute $currentRoute
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Page not found');
?>
<section class="not-found">
    <p class="eyebrow">404</p>
    <h1>This page is offside.</h1>
    <p>We could not find <strong><?= Html::encode($currentRoute->getUri()?->getPath() ?? 'this page') ?></strong>.</p>
    <a class="btn btn-primary" href="<?= Html::encode($urlGenerator->generate('home')) ?>">Back to home</a>
</section>
