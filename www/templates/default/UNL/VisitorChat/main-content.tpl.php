<div class="dcf-bleed dcf-pb-8">
    <?php if (!($context->actionable->getRawObject() instanceof UNL\VisitorChat\Manage\View)): ?>
        <div class="dcf-wrapper dcf-pt-0">
    <?php endif; ?>
        <div id="visitorChat_container">
            <?php
            echo $savvy->render($context->actionable);
            ?>
        </div>

        <div id='visitorChat_sound_container'></div>

        <div id="visitorChat_brightBox">
            <p>Hello all!</p>
        </div>
    <?php if (!($context->actionable->getRawObject() instanceof UNL\VisitorChat\Manage\View)): ?>
        </div>
    <?php endif; ?>
</div>
