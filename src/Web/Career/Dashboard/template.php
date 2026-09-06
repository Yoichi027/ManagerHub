<?php
declare(strict_types=1);
use App\Domain\Career\Career; use App\Domain\Season\Season; use Yiisoft\Html\Html;
/** @var Career $career */ /** @var Season $season */ /** @var string|null $clubLogoUrl */
$this->setTitle($career->name->value);
?>
<section class="career-dashboard"><a class="career-dashboard__back" href="<?= Html::encode($urlGenerator->generate('dashboard')) ?>">← All careers</a><header class="career-dashboard__header"><?php if ($clubLogoUrl !== null): ?><img src="<?= Html::encode($clubLogoUrl) ?>" alt=""><?php endif ?><div><p class="eyebrow">Current career</p><h1><?= Html::encode($season->managedClub->name) ?></h1><p><?= Html::encode($career->name->value) ?> · <?= Html::encode($career->gameEdition->value) ?></p></div></header><dl class="career-dashboard__facts"><div><dt>Manager</dt><dd><?= Html::encode($career->managerName->value) ?></dd></div><div><dt>Current season</dt><dd><?= Html::encode($season->label->value) ?></dd></div><div><dt>League</dt><dd><?= Html::encode($season->managedLeague->name) ?></dd></div></dl><section class="career-dashboard__next"><p class="eyebrow">Next step</p><h2>Build your squad.</h2><p>Add the players from your save to begin tracking this season.</p></section></section>
