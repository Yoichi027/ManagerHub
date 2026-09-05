<?php

declare(strict_types=1);

use App\Web\Identity\Register\RegisterForm;
use Yiisoft\Html\Html;
use Yiisoft\Yii\View\Renderer\Csrf;
use Yiisoft\View\WebView;

/**
 * @var Csrf $csrf
 * @var RegisterForm $form
 * @var WebView $this
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Create an account');
?>

<section class="register-page row justify-content-center">
    <div class="col-lg-7 col-xl-6">
        <div class="register-card">
            <p class="eyebrow">Start your career</p>
            <h1>Create your account</h1>
            <p class="register-card__intro">Keep every season, squad decision and transfer in one place.</p>

            <?php foreach ($form->errorsFor('general') as $error): ?>
                <div class="alert alert-danger" role="alert"><?= Html::encode($error) ?></div>
            <?php endforeach ?>

            <form id="register-form" method="post" action="<?= Html::encode($urlGenerator->generate('register.submit')) ?>" novalidate>
                <?= $csrf->hiddenInput()->render() ?>

                <div class="mb-3">
                    <label class="form-label" for="register-username">Username</label>
                    <input class="form-control" id="register-username" name="username" type="text" value="<?= Html::encode($form->username) ?>" autocomplete="username" maxlength="20" required>
                    <div class="form-text">Up to 20 letters and numbers.</div>
                    <?php foreach ($form->errorsFor('username') as $error): ?>
                        <div class="invalid-feedback d-block" role="alert"><?= Html::encode($error) ?></div>
                    <?php endforeach ?>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="register-email">Email</label>
                    <input class="form-control" id="register-email" name="email" type="email" value="<?= Html::encode($form->email) ?>" autocomplete="email" required>
                    <?php foreach ($form->errorsFor('email') as $error): ?>
                        <div class="invalid-feedback d-block" role="alert"><?= Html::encode($error) ?></div>
                    <?php endforeach ?>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="register-password">Password</label>
                    <input class="form-control" id="register-password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                    <div class="form-text">At least 8 characters, including an uppercase letter, a number and a symbol.</div>
                    <?php foreach ($form->errorsFor('password') as $error): ?>
                        <div class="invalid-feedback d-block" role="alert"><?= Html::encode($error) ?></div>
                    <?php endforeach ?>
                </div>

                <button class="btn btn-primary w-100" type="submit">Create account</button>
            </form>
        </div>
    </div>
</section>
