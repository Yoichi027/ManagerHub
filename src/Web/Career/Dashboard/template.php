<?php
declare(strict_types=1);

use App\Application\Squad\Dashboard\SquadOverview;
use App\Domain\Career\Career;
use App\Domain\Season\Season;
use App\Domain\Squad\SquadPlayer;
use Yiisoft\Html\Html;

/** @var Career $career */ /** @var Season $season */ /** @var string|null $clubLogoUrl */ /** @var SquadOverview $squadOverview */
$this->setTitle($career->name->value);
$formatValue = static function (int $cents): string {
    $value = $cents / 100;
    if ($value >= 1000000) { return '€' . number_format($value / 1000000, 1) . 'M'; }
    if ($value >= 1000) { return '€' . number_format($value / 1000, 1) . 'K'; }
    return '€' . number_format($value, 0);
};
$valueInCents = static function (SquadPlayer $player): int {
    [$whole, $fraction] = array_pad(explode('.', $player->valueFinal ?? $player->valueInitial, 2), 2, '');
    return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
};
$currentOverall = static fn (SquadPlayer $player): int => $player->overallFinal ?? $player->overallInitial;
$currentPotential = static fn (SquadPlayer $player): int => $player->potentialFinal ?? $player->potentialInitial;
?>
<section class="career-dashboard" data-career-dashboard>
    <a class="career-dashboard__back" href="<?= Html::encode($urlGenerator->generate('dashboard')) ?>">← All careers</a>
    <header class="career-dashboard__header">
        <?php if ($clubLogoUrl !== null): ?><img src="<?= Html::encode($clubLogoUrl) ?>" alt=""><?php endif ?>
        <div><p class="eyebrow">Current career</p><h1><?= Html::encode($season->managedClub->name) ?></h1><p><?= Html::encode($career->name->value) ?> · <?= Html::encode($career->gameEdition->value) ?></p></div>
    </header>
    <dl class="career-dashboard__facts"><div><dt>Manager</dt><dd><?= Html::encode($career->managerName->value) ?></dd></div><div><dt>Current season</dt><dd><?= Html::encode($season->label->value) ?></dd></div><div><dt>League</dt><dd><?= Html::encode($season->managedLeague->name) ?></dd></div></dl>

    <div class="career-dashboard__tabs" role="tablist" aria-label="Career dashboard">
        <button type="button" role="tab" aria-selected="true" aria-controls="squad-overview" id="squad-overview-tab" data-dashboard-tab="squad-overview">Squad overview</button>
        <button type="button" role="tab" aria-selected="false" aria-controls="player-highlights" id="player-highlights-tab" data-dashboard-tab="player-highlights">Player highlights</button>
    </div>
    <section class="career-dashboard__panel" id="squad-overview" role="tabpanel" aria-labelledby="squad-overview-tab" data-dashboard-panel>
        <?php if ($squadOverview->totalPlayers === 0): ?>
            <div class="career-dashboard__empty"><p>No players in this squad yet.</p><a class="button button--primary" href="<?= Html::encode($urlGenerator->generate('career.squad', ['id' => $career->id->toString()])) ?>">Build squad</a></div>
        <?php else: ?>
            <div class="squad-overview"><dl><div><dt>Total players</dt><dd><?= $squadOverview->totalPlayers ?></dd></div><div><dt>Starters</dt><dd><?= $squadOverview->starters ?></dd></div><div><dt>Substitutes</dt><dd><?= $squadOverview->substitutes ?></dd></div><div><dt>On loan</dt><dd><?= $squadOverview->loanedOut ?></dd></div><div><dt>Average overall</dt><dd><?= number_format($squadOverview->averageOverall ?? 0, 1) ?></dd></div><div><dt>Total value</dt><dd><?= $formatValue($squadOverview->totalValueInCents) ?></dd></div></dl><a class="career-dashboard__squad-link" href="<?= Html::encode($urlGenerator->generate('career.squad', ['id' => $career->id->toString()])) ?>">Open squad <span aria-hidden="true">→</span></a></div>
        <?php endif ?>
    </section>
    <section class="career-dashboard__panel" id="player-highlights" role="tabpanel" aria-labelledby="player-highlights-tab" data-dashboard-panel hidden>
        <?php if ($squadOverview->totalPlayers === 0): ?>
            <div class="career-dashboard__empty"><p>Add players to surface your squad highlights.</p><a class="button button--primary" href="<?= Html::encode($urlGenerator->generate('career.squad', ['id' => $career->id->toString()])) ?>">Build squad</a></div>
        <?php else: ?>
            <div class="player-highlights"><article><p>Most valuable</p><h2><?= Html::encode($squadOverview->mostValuablePlayer?->playerName ?? '') ?></h2><span><?= $formatValue($valueInCents($squadOverview->mostValuablePlayer)) ?></span></article><article><p>Highest potential</p><h2><?= Html::encode($squadOverview->highestPotentialPlayer?->playerName ?? '') ?></h2><span><?= $currentPotential($squadOverview->highestPotentialPlayer) ?> POT</span></article><article><p>Highest overall</p><h2><?= Html::encode($squadOverview->highestOverallPlayer?->playerName ?? '') ?></h2><span><?= $currentOverall($squadOverview->highestOverallPlayer) ?> OVR</span></article></div>
        <?php endif ?>
    </section>
</section>
<script>
document.querySelectorAll('[data-career-dashboard]').forEach((dashboard) => {
    const tabs = dashboard.querySelectorAll('[data-dashboard-tab]');
    const panels = dashboard.querySelectorAll('[data-dashboard-panel]');
    tabs.forEach((tab) => tab.addEventListener('click', () => {
        tabs.forEach((item) => item.setAttribute('aria-selected', String(item === tab)));
        panels.forEach((panel) => { panel.hidden = panel.id !== tab.dataset.dashboardTab; });
    }));
    tabs.forEach((tab, index) => tab.addEventListener('keydown', (event) => {
        const direction = event.key === 'ArrowRight' ? 1 : event.key === 'ArrowLeft' ? -1 : 0;
        if (event.key === 'Home' || event.key === 'End' || direction !== 0) {
            event.preventDefault();
            const next = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 : (index + direction + tabs.length) % tabs.length;
            tabs[next].focus();
            tabs[next].click();
        }
    }));
});
</script>
