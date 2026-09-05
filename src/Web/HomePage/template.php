<?php

declare(strict_types=1);

use App\Shared\ApplicationParams;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\WebView;

/**
 * @var ApplicationParams $applicationParams
 * @var UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\User\CurrentUser $currentUser
 * @var WebView $this
 */

$this->setTitle($applicationParams->name);
?>
<?php if ($currentUser->isGuest()): ?>
    <section class="landing-hero">
        <div class="landing-hero__copy">
            <p class="eyebrow">The career workspace</p>
            <h1>Run every season with a clearer plan.</h1>
            <p>Manager Hub gives your career mode the structure it deserves — squads, transfers, tactical choices and season history, kept together and easy to use.</p>
            <div class="hero-actions">
                <a class="button button--primary" href="<?= Html::encode($urlGenerator->generate('register')) ?>">Create your account</a>
                <a class="button button--text" href="<?= Html::encode($urlGenerator->generate('login')) ?>">Log in <span aria-hidden="true">→</span></a>
            </div>
        </div>
        <div class="landing-hero__mark" aria-hidden="true"><img src="<?= Html::encode($aliases->get('@baseUrl/logo_no_background.png')) ?>" alt=""></div>
    </section>
    <section class="landing-principles" aria-label="What Manager Hub is for">
        <div><span class="principle-index">01</span><h2>Keep the context</h2><p>Every decision stays connected to the season and squad that shaped it.</p></div>
        <div><span class="principle-index">02</span><h2>Make decisions faster</h2><p>A focused workspace for the parts of your career that matter.</p></div>
        <div><span class="principle-index">03</span><h2>Preserve the story</h2><p>Build a history you can return to, season after season.</p></div>
    </section>
<?php else: ?>
    <section class="workspace-welcome">
        <p class="eyebrow">Manager Hub</p>
        <h1>Your career workspace.</h1>
        <p>Everything in one place, designed to keep your decisions clear as your career takes shape.</p>
    </section>
<?php endif ?>
