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

<section class="register-page row justify-content-center">
    <div class="col-lg-7 col-xl-6">
        <div class="register-card">
            <p class="eyebrow">Welcome back</p>
            <h1>Log in to Manager Hub</h1>
            <p class="register-card__intro">Continue planning your next season.</p>

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
                    <input class="form-control" id="login-password" name="password" type="password" autocomplete="current-password" required>
                    <?php foreach ($form->errorsFor('password') as $error): ?>
                        <div class="invalid-feedback d-block" role="alert"><?= Html::encode($error) ?></div>
                    <?php endforeach ?>
                </div>

                <button class="btn btn-primary w-100" type="submit">Log in</button>
            </form>
        </div>
    </div>
</section>
