<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\ORM\Entity $post
 */
?>
<?php echo $this->Likes->widget('Posts', $post->id, $post->liked ? $post->liked->value : null);?> <?php echo h($post->title); ?>
