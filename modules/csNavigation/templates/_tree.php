<?php if (isset($title) && $title): ?>
	<b><?php echo $title ?></b>
<?php endif ?>
<?php if (count($items) > 0): ?>
<ul class="<?php echo $class ?>" id="<?php echo $id ?>">
<?php foreach ($items as $item): ?>
<?php if ($item->isAuthenticated()): ?>
	 <?php if ($item->isActive()): ?>
	   <li class="active"><a href="#"><?php echo $item->getName() ?></a>
	 <?php elseif ($item->isActiveBranch()): ?>
	   <li class="active parent"><?php echo link_to($item->getName(), $item->getRoute()) ?></a>
	 <?php elseif($item->getRoute()): ?>
		 <li><?php echo link_to($item->getName(), $item->getRoute()) ?>
	 <?php else: ?>
		 <li><?php echo $item->getName() ?>
	<?php endif ?>
	<?php if ($item->hasItems() && ($item->isExpanded() || ($max_level && $item->level <= $max_level))): ?>
			<?php include_component('csNavigation', 'tree', array('items' => $item->getItems(), 'iterations' => $iterations)) ?>
  <?php endif ?>
	</li>
<?php endif; ?>	
<?php endforeach ?>
</ul>
<?php endif ?>