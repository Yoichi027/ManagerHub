<?php

declare(strict_types=1);

use App\Web\Shared\Layout\Main\MainAsset;
use Yiisoft\Html\Html;
use Yiisoft\Yii\View\Renderer\Csrf;

/**
 * @var App\Shared\ApplicationParams $applicationParams
 * @var Yiisoft\Aliases\Aliases $aliases
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $content
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\User\CurrentUser $currentUser
 * @var Csrf $csrf
 */

$assetManager->register(MainAsset::class);
$this->addCssFiles($assetManager->getCssFiles());
$this->addCssStrings($assetManager->getCssStrings());
$this->addJsFiles($assetManager->getJsFiles());
$this->addJsStrings($assetManager->getJsStrings());
$this->addJsVars($assetManager->getJsVars());
$this->beginPage()
?>
<!DOCTYPE html>
<html lang="<?= Html::encode($applicationParams->locale) ?>">
<head>
    <meta charset="<?= Html::encode($applicationParams->charset) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?= $aliases->get('@baseUrl/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <title><?= Html::encode($this->getTitle()) ?></title>
    <?php $this->head() ?>
</head>
<body class="<?= $currentUser->isGuest() ? 'app-shell app-shell--guest' : 'app-shell app-shell--workspace' ?>">
<?php $this->beginBody() ?>
<?php if ($currentUser->isGuest()): ?>
    <header class="site-header">
        <div class="container site-header__inner">
            <a class="brand" href="<?= Html::encode($urlGenerator->generate('home')) ?>" aria-label="Manager Hub home">
                <img class="brand__symbol" src="<?= Html::encode($aliases->get('@baseUrl/logo.png')) ?>" alt="">
                <span>Manager Hub</span>
            </a>
            <nav class="site-nav" aria-label="Primary navigation">
                <a class="site-nav__link" href="<?= Html::encode($urlGenerator->generate('login')) ?>">Log in</a>
                <a class="button button--quiet" href="<?= Html::encode($urlGenerator->generate('register')) ?>">Create account</a>
            </nav>
        </div>
    </header>
    <main class="site-main"><div class="container"><?= $content ?></div></main>
    <footer class="site-footer"><div class="container"><span>© <?= date('Y') ?> <?= Html::encode($applicationParams->name) ?></span><span>A clearer way to run a career.</span></div></footer>
<?php else: ?>
    <div class="workspace" data-workspace>
        <aside class="workspace-sidebar" id="workspace-sidebar">
            <div class="workspace-sidebar__top">
                <a class="workspace-brand" href="<?= Html::encode($urlGenerator->generate('dashboard')) ?>" aria-label="Manager Hub workspace">
                    <img class="workspace-brand__symbol" src="<?= Html::encode($aliases->get('@baseUrl/logo.png')) ?>" alt="">
                    <span class="workspace-brand__name">Manager Hub</span>
                </a>
                <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="workspace-sidebar" aria-expanded="true">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5l7 7-7 7"/></svg><span class="visually-hidden">Collapse navigation</span>
                </button>
            </div>
            <nav class="workspace-nav" aria-label="Workspace navigation">
                <a class="workspace-nav__item workspace-nav__item--active" href="<?= Html::encode($urlGenerator->generate('dashboard')) ?>" aria-current="page">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 10.5L12 4l8 6.5v8.25a1.25 1.25 0 0 1-1.25 1.25H5.25A1.25 1.25 0 0 1 4 18.75V10.5zM9 20v-6h6v6"/></svg><span>Workspace</span>
                </a>
                <a class="workspace-nav__item" href="<?= Html::encode($urlGenerator->generate('career.create')) ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg><span>New career</span>
                </a>
                <a class="workspace-nav__item" href="<?= Html::encode($urlGenerator->generate('account')) ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg><span>Account</span>
                </a>
            </nav>
            <div class="workspace-sidebar__bottom"><span class="sidebar-status"><i></i><span>Signed in</span></span><form id="logout-form" method="post" action="<?= Html::encode($urlGenerator->generate('logout')) ?>"><?= $csrf->hiddenInput()->render() ?><button class="workspace-logout" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M21 19V5a1 1 0 0 0-1-1h-6"/></svg><span>Log out</span></button></form></div>
        </aside>
        <main class="workspace-main"><div class="workspace-main__content"><?= $content ?></div></main>
    </div>
<?php endif ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
