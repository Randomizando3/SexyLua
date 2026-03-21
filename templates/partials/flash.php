<?php $flashMessages = $flash_messages ?? $app->flash->consume(); ?>
<?php if ($flashMessages !== []) : ?>
    <div class="flash-stack">
        <?php foreach ($flashMessages as $flash) : ?>
            <div class="flash flash-<?= e($flash['type']) ?>" data-flash>
                <span><?= e($flash['message']) ?></span>
                <button type="button" class="flash-close" data-dismiss-flash>Fechar</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
