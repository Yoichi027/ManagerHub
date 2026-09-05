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

<section class="register-page">
    <h1>Create an account</h1>

    <?php foreach ($form->errorsFor('general') as $error): ?>
        <p role="alert"><?= Html::encode($error) ?></p>
    <?php endforeach ?>

    <form id="register-form" method="post" action="<?= Html::encode($urlGenerator->generate('register.submit')) ?>">
        <?= $csrf->hiddenInput()->render() ?>

        <div>
            <label for="register-username">Username</label>
            <input id="register-username" name="username" type="text" value="<?= Html::encode($form->username) ?>" autocomplete="username" required>
            <?php foreach ($form->errorsFor('username') as $error): ?>
                <p role="alert"><?= Html::encode($error) ?></p>
            <?php endforeach ?>
        </div>

        <div>
            <label for="register-email">Email</label>
            <input id="register-email" name="email" type="email" value="<?= Html::encode($form->email) ?>" autocomplete="email" required>
            <?php foreach ($form->errorsFor('email') as $error): ?>
                <p role="alert"><?= Html::encode($error) ?></p>
            <?php endforeach ?>
        </div>

        <div>
            <label for="register-password">Password</label>
            <input id="register-password" name="password" type="password" autocomplete="new-password" required>
            <?php foreach ($form->errorsFor('password') as $error): ?>
                <p role="alert"><?= Html::encode($error) ?></p>
            <?php endforeach ?>
        </div>

        <button type="submit">Create account</button>
    </form>
</section>
