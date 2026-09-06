<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\Yii\View\Renderer\Csrf;
use Yiisoft\View\WebView;

/** @var WebView $this */ /** @var Csrf $csrf */ /** @var list<array{career_id: string, career_name: string, manager_name: string, game_edition: string, club_name: string, club_logo_url: ?string, season_label: string}> $careers */ /** @var string|null $success */ /** @var string|null $deleteError */

$this->setTitle('Workspace');
?>

<section class="workspace-welcome workspace-careers">
    <div><p class="eyebrow">Workspace</p><h1>Your careers.</h1><p>Set up a career, then build its squad from the players in your save.</p></div>
    <a class="button button--primary" href="<?= Html::encode($urlGenerator->generate('career.create')) ?>">Create career</a>
    <?php if ($success !== null): ?><div class="alert alert-success" role="status"><?= Html::encode($success) ?></div><?php endif ?>
    <?php if ($deleteError !== null): ?><div class="alert alert-danger" role="alert"><?= Html::encode($deleteError) ?></div><?php endif ?>
    <?php if ($careers === []): ?>
        <div class="workspace-empty"><p>No careers yet.</p><span>Start with the club, league and first season from your save.</span></div>
    <?php else: ?>
        <div class="career-list" aria-label="Your careers"><?php foreach ($careers as $career): ?><article class="career-list__item"><div class="career-list__club"><?php if ($career['club_logo_url'] !== null): ?><img src="<?= Html::encode($career['club_logo_url']) ?>" alt=""><?php endif ?><div><strong><?= Html::encode($career['club_name']) ?></strong><span><?= Html::encode($career['career_name']) ?></span></div></div><dl><div><dt>Manager</dt><dd><?= Html::encode($career['manager_name']) ?></dd></div><div><dt>Season</dt><dd><?= Html::encode($career['season_label']) ?></dd></div><div><dt>Game</dt><dd><?= Html::encode($career['game_edition']) ?></dd></div></dl><details class="career-delete"><summary aria-label="Delete career"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M9 7l1-2h4l1 2M7 7l1 13h8l1-13"/></svg><span class="visually-hidden">Delete career</span></summary><form method="post" action="<?= Html::encode($urlGenerator->generate('career.delete', ['id' => $career['career_id']])) ?>"><?= $csrf->hiddenInput()->render() ?><p>This removes the career from Manager Hub. You cannot undo this action.</p><label><input type="checkbox" name="confirm_delete" value="1" required> I understand this cannot be undone.</label><button class="button button--danger" type="submit">Delete career</button></form></details></article><?php endforeach ?></div>
    <?php endif ?>
</section>
