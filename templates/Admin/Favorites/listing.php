<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Favorites\Model\Entity\Favorite> $favorites
 */
?>
<nav class="actions large-3 medium-4 columns col-sm-4 col-xs-12" id="actions-sidebar">
    <ul class="side-nav nav nav-pills flex-column">
        <li class="nav-item heading"><?= __('Actions') ?></li>
        <li class="nav-item">
			<?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'nav-link']) ?>
        </li>
    </ul>
</nav>
<div class="favorites index content large-9 medium-8 columns col-sm-8 col-12">

    <h2><?= __('Favorites') ?></h2>

    <div class="">
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('model') ?></th>
                    <th><?= $this->Paginator->sort('foreign_key') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('created', null, ['direction' => 'desc']) ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($favorites as $comment): ?>
                <tr>
                    <td><?= h($comment->model) ?></td>
                    <td><?= $this->Number->format($comment->foreign_key) ?></td>
                    <td><?= $comment->hasValue('user') ? $this->Html->link($comment->user->username, ['controller' => 'Users', 'action' => 'view', $comment->user->id]) : '' ?></td>
                    <td><?= $this->Time->nice($comment->created) ?></td>
                    <td class="actions">
                        <?php
						$label = __('Delete');
						if ($this->helpers()->has('Icon')) {
							$label = $this->Icon->render('delete');
						}
						echo $this->Form->postLink($label, ['action' => 'delete', $comment->id], ['escapeTitle' => false, 'confirm' => __('Are you sure you want to delete # {0}?', $comment->id)]);
						?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php echo $this->element('Favorites.pagination'); ?>
</div>
