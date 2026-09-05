<?php

declare(strict_types=1);

use App\Web\Identity\Login\LoginForm;
use Yiisoft\Html\Html;
use Yiisoft\Yii\View\Renderer\Csrf;
use Yiisoft\View\WebView;

/**
 * @var Csrf $csrf
 * @var LoginForm $form
 * @var WebView $this
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Log in');
?>

<section class="auth-page">
    <div class="auth-card">
        <div class="auth-card__intro">
            <p class="eyebrow">Welcome back</p>
            <h1>Log in to Manager Hub</h1>
            <p>Pick up your career right where you left it.</p>
        </div>

            <?php foreach ($form->errorsFor('general') as $error): ?>
                <div class="alert alert-danger" role="alert"><?= Html::encode($error) ?></div>
            <?php endforeach ?>

            <form id="login-form" method="post" action="<?= Html::encode($urlGenerator->generate('login.submit')) ?>" novalidate>
                <?= $csrf->hiddenInput()->render() ?>

                <div class="mb-3">
                    <label class="form-label" for="login-username">Username</label>
                    <input class="form-control" id="login-username" name="username" type="text" value="<?= Html::encode($form->username) ?>" autocomplete="username" maxlength="20" required>
                    <?php foreach ($form->errorsFor('username') as $error): ?>
                        <div class="invalid-feedback d-block" role="alert"><?= Html::encode($error) ?></div>
                    <?php endforeach ?>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="login-password">Password</label>
                    <div class="password-field"><input class="form-control" id="login-password" name="password" type="password" autocomplete="current-password" required><button class="password-toggle" type="button" data-password-toggle aria-controls="login-password" aria-pressed="false">Show</button></div>
                    <?php foreach ($form->errorsFor('password') as $error): ?>
                        <div class="invalid-feedback d-block" role="alert"><?= Html::encode($error) ?></div>
                    <?php endforeach ?>
                </div>

                <button class="button button--primary button--block" type="submit">Log in</button>
            </form>
            <p class="auth-card__footer">New to Manager Hub? <a href="<?= Html::encode($urlGenerator->generate('register')) ?>">Create an account</a></p>
        </div>
</section>
