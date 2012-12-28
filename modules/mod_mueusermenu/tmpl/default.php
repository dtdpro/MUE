<?php

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');

?>

<?php if ($user->id) : ?>

	
	
		<ul class="<?php echo $params->get('menu_class', 'menu menu-line'); ?>">
			<li class="level1">
				<a href="<?php echo JRoute::_('index.php?option=com_mue&view=user&layout=profile'); ?>" class="level1"><?php echo JText::_('MOD_MUEUSERMENU_PROFILE'); ?></a>
			</li>
			<li class="level1">
				<a href="<?php echo JRoute::_('index.php?option=com_mue&view=login&layout=logout'); ?>" class="level1"><?php echo JText::_('MOD_MUEUSERMENU_LOGOUT'); ?></a>
			</li>

		</ul>
		

<?php else : ?>

	
		
		<ul class="<?php echo $params->get('menu_class', 'menu menu-line'); ?>">
			<li class="level1">
				<a href="<?php echo JRoute::_('index.php?option=com_mue&view=login&layout=login'); ?>" class="level1"><?php echo JText::_('MOD_MUEUSERMENU_LOGIN'); ?></a>
			</li>
			<?php
			$usersConfig = JComponentHelper::getParams('com_users');
			if ($usersConfig->get('allowUserRegistration')) : ?>
			<li class="level1">
				<a href="<?php echo JRoute::_('index.php?option=com_mue&view=userreg'); ?>" class="level1"><?php echo JText::_('MOD_MUEUSERMENU_REGISTER'); ?></a>
			</li>
			<?php endif; ?>
		</ul>
		
		
	
<?php endif; ?>