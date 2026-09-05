<?php

declare(strict_types=1);

use App\Web\Shared\Layout\Main\MainAsset;
use Yiisoft\Html\Html;

/**
 * @var App\Shared\ApplicationParams $applicationParams
 * @var Yiisoft\Aliases\Aliases $aliases
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $content
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\User\CurrentUser $currentUser
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
<body>
<?php $this->beginBody() ?>
<header class="site-header">
    <div class="container site-header__inner">
        <a class="brand" href="<?= Html::encode($urlGenerator->generate('home')) ?>" aria-label="Manager Hub home">
            <span class="brand__mark" aria-hidden="true">M</span><span>Manager Hub</span>
        </a>
        <nav aria-label="Primary navigation">
            <?php if ($currentUser->isGuest()): ?>
                <a class="site-nav__link" href="<?= Html::encode($urlGenerator->generate('login')) ?>">Log in</a>
                <a class="site-nav__link" href="<?= Html::encode($urlGenerator->generate('register')) ?>">Create account</a>
            <?php else: ?>
                <span class="site-nav__link">Signed in</span>
            <?php endif ?>
        </nav>
    </div>
</header>
<main class="site-main"><div class="container"><?= $content ?></div></main>
<footer class="site-footer"><div class="container"><span>© <?= date('Y') ?> <?= Html::encode($applicationParams->name) ?></span><span>Built for better career mode seasons.</span></div></footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
