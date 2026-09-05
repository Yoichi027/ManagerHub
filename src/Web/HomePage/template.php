<?php

declare(strict_types=1);

use App\Shared\ApplicationParams;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\WebView;

/**
 * @var ApplicationParams $applicationParams
 * @var UrlGeneratorInterface $urlGenerator
 * @var WebView $this
 */

$this->setTitle($applicationParams->name);
?>
<section class="hero">
    <div class="hero__content">
        <p class="eyebrow">Career mode, under control</p>
        <h1>Plan every season with confidence.</h1>
        <p class="hero__lead">Manager Hub keeps your squads, transfers, tactics and season history together — so every decision has context.</p>
        <a class="btn btn-primary btn-lg" href="<?= Html::encode($urlGenerator->generate('register')) ?>">Create your account</a>
    </div>
    <div class="hero__panel" aria-hidden="true">
        <div class="season-card">
            <div class="season-card__header"><span>Active season</span><strong>2026 / 27</strong></div>
            <div class="season-card__line"></div>
            <div class="season-card__stat"><span>Squad planning</span><strong>In progress</strong></div>
            <div class="season-card__stat"><span>Transfer window</span><strong>Open</strong></div>
        </div>
    </div>
</section>
<section class="feature-grid" aria-label="Manager Hub features">
    <article class="feature-card"><span class="feature-card__number">01</span><h2>Keep the history</h2><p>Preserve each season as your career evolves, without overwriting the decisions that got you there.</p></article>
    <article class="feature-card"><span class="feature-card__number">02</span><h2>Build with intent</h2><p>See the squad, tactical choices and player progression in one place before you make the next move.</p></article>
    <article class="feature-card"><span class="feature-card__number">03</span><h2>Own every save</h2><p>Each career stays private to its manager, ready to pick up whenever the next season begins.</p></article>
</section>
