<?php

declare(strict_types=1);

use App\Web\Identity\Reactivate\ReactivationForm;
use Yiisoft\Html\Html;
use Yiisoft\Yii\View\Renderer\Csrf;
use Yiisoft\View\WebView;

/**
 * @var Csrf $csrf
 * @var ReactivationForm $form
 * @var WebView $this
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Reactivate account');
?>

<section class="auth-page">
    <div class="auth-card">
        <div class="auth-card__intro">
            <p class="eyebrow">Restore access</p>
            <h1>Reactivate your account</h1>
            <p>Confirm your credentials to restore your account and career data.</p>
        </div>

        <?php foreach ($form->errorsFor('general') as $error): ?>
            <div class="alert alert-danger" role="alert"><?= Html::encode($error) ?></div>
        <?php endforeach ?>

        <form id="reactivate-form" method="post" action="<?= Html::encode($urlGenerator->generate('reactivate.submit')) ?>" novalidate>
            <?= $csrf->hiddenInput()->render() ?>
            <div class="mb-3">
                <label class="form-label" for="reactivate-identifier">Username or email</label>
                <input class="form-control" id="reactivate-identifier" name="identifier" type="text" value="<?= Html::encode($form->identifier) ?>" autocomplete="username" maxlength="255" required>
                <?php foreach ($form->errorsFor('identifier') as $error): ?>
                    <div class="invalid-feedback d-block" role="alert"><?= Html::encode($error) ?></div>
                <?php endforeach ?>
            </div>
            <div class="mb-4">
                <label class="form-label" for="reactivate-password">Password</label>
                <div class="password-field"><input class="form-control" id="reactivate-password" name="password" type="password" autocomplete="current-password" required><button class="password-toggle" type="button" data-password-toggle aria-controls="reactivate-password" aria-pressed="false">Show</button></div>
                <?php foreach ($form->errorsFor('password') as $error): ?>
                    <div class="invalid-feedback d-block" role="alert"><?= Html::encode($error) ?></div>
                <?php endforeach ?>
            </div>
            <button class="button button--primary button--block" type="submit">Reactivate account</button>
        </form>
        <p class="auth-card__footer"><a href="<?= Html::encode($urlGenerator->generate('login')) ?>">Back to log in</a></p>
    </div>
</section>
