<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\ORM\Entity $post
 */
?>
<?php echo $this->Stars->linkIcon('Posts', $post->id, (bool)$post->starred);?> <?php echo h($post->title); ?>
