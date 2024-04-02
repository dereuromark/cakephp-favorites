<?php
/**
 * @var \App\View\AppView $this
 * @var array<string, int> $models
 */
?>
<nav class="actions large-3 medium-4 columns col-sm-4 col-xs-12" id="actions-sidebar">
    <ul class="side-nav nav nav-pills flex-column">
        <li class="nav-item heading"><?= __('Actions') ?></li>
        <li class="nav-item">
        </li>
    </ul>
</nav>
<div class="favorites index content large-9 medium-8 columns col-sm-8 col-12">

    <h2><?= __('Favorites') ?></h2>

    <ul>
		<?php foreach ($models as $model => $count): ?>
		<li>
			<?php echo h($model); ?>: <?php echo $count; ?>x
		</li>
		<?php endforeach; ?>
	</ul>

	<p><?= $this->Html->link(__('Details'), ['action' => 'listing'], ['class' => '']) ?></p>

</div>
