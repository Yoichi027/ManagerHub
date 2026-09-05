<?php

declare(strict_types=1);

use App\Domain\Identity\User;
use Yiisoft\Html\Html;
use Yiisoft\Yii\View\Renderer\Csrf;
use Yiisoft\View\WebView;

/**
 * @var Csrf $csrf
 * @var User $user
 * @var string|null $emailError
 * @var string|null $passwordError
 * @var string|null $deactivateError
 * @var string|null $success
 * @var WebView $this
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Account');
?>

<section class="account-page">
    <header class="account-page__header"><p class="eyebrow">Account</p><h1>Account settings</h1><p>Manage the details that keep your account secure.</p></header>
    <?php if ($success !== null): ?><div class="alert alert-success" role="status"><?= Html::encode($success) ?></div><?php endif ?>
    <div class="account-grid">
        <section class="account-section"><div><h2>Profile</h2><p>Your username is fixed after registration.</p></div><dl class="account-summary"><dt>Username</dt><dd><?= Html::encode($user->username->value) ?></dd></dl>
            <form id="account-email-form" method="post" action="<?= Html::encode($urlGenerator->generate('account.email')) ?>" novalidate><?= $csrf->hiddenInput()->render() ?><label class="form-label" for="account-email">Email</label><input class="form-control" id="account-email" name="email" type="email" value="<?= Html::encode($user->email->value) ?>" autocomplete="email" required><?php if ($emailError !== null): ?><div class="invalid-feedback d-block" role="alert"><?= Html::encode($emailError) ?></div><?php endif ?><button class="button button--primary" type="submit">Save email</button></form>
        </section>
        <section class="account-section"><div><h2>Password</h2><p>Choose a strong password you do not use elsewhere.</p></div>
            <form id="account-password-form" method="post" action="<?= Html::encode($urlGenerator->generate('account.password')) ?>" novalidate><?= $csrf->hiddenInput()->render() ?><label class="form-label" for="current-password">Current password</label><input class="form-control" id="current-password" name="current_password" type="password" autocomplete="current-password" required><label class="form-label" for="new-password">New password</label><input class="form-control" id="new-password" name="new_password" type="password" autocomplete="new-password" minlength="8" required><div class="form-text">8–128 characters, with an uppercase letter, number and symbol.</div><?php if ($passwordError !== null): ?><div class="invalid-feedback d-block" role="alert"><?= Html::encode($passwordError) ?></div><?php endif ?><button class="button button--primary" type="submit">Update password</button></form>
        </section>
        <section class="account-section account-section--danger"><div><h2>Deactivate account</h2><p>Your career data is preserved, but you will be signed out and unable to log in.</p></div>
            <form id="account-deactivate-form" method="post" action="<?= Html::encode($urlGenerator->generate('account.deactivate')) ?>" novalidate><?= $csrf->hiddenInput()->render() ?><label class="form-label" for="deactivate-password">Confirm your password</label><input class="form-control" id="deactivate-password" name="password" type="password" autocomplete="current-password" required><?php if ($deactivateError !== null): ?><div class="invalid-feedback d-block" role="alert"><?= Html::encode($deactivateError) ?></div><?php endif ?><button class="button button--danger" type="submit">Deactivate account</button></form>
        </section>
    </div>
</section>
